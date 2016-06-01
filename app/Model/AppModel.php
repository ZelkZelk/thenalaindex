<?php

App::uses('Model', 'Model');

abstract class AppModel extends Model  {    
    private $Data;
    
    /* Carga los datos del esquema cuyo tipo sea foreign */
    
    public function loadForeign(){
        foreach($this->getSchema() as $field => $meta){
            if($this->isForeign($field) === true){
                $this->Data()->loadForeign($field,$meta);
            }
        }
    }
    
    public function isForeign($field){
        $schema = $this->getSchema();
        
        if(isset($schema[$field])){
            if(isset($schema[$field]['type'])){
                return (bool) ($schema[$field]['type'] === 'foreign');
            }
        }
        
        return null;        
    }
    
    /* Devuelve el LABEL<String> del campo NULL si no existe */
    
    public function getLabel($field){
        $schema = $this->getSchema();
        
        if(isset($schema[$field])){
            if(isset($schema[$field]['label'])){
                return $schema[$field]['label'] . '';
            }
        }
        
        return null;
    }
    
    /* Determina<Bool> si un campo es READABLE, NULL si no está determinado */
    
    public function isReadable($field){
        $schema = $this->getSchema();
        
        if(isset($schema[$field])){
            if(isset($schema[$field]['readable'])){
                return (bool) $schema[$field]['readable'];
            }
        }
        
        return null;
    }
    
    /* Obtiene el listado de acciones relacionadas al objeto del modelo. */

    public function getLinks() {
        $ctrl = strtolower(Inflector::pluralize($this->alias));
        $links = [];
        
        Configure::load('sitemap');
        $map = Configure::read($ctrl);
        
        foreach($map as $actn => $conf){
            if( ! isset($conf['single']) || ! $conf['single']){
                continue;
            }
            
            if( ! $this->matchStatefulAction($conf)){
                continue;
            }
            
            $route = isset($conf['routemap']) && is_array($conf['routemap']) ?
                    $conf['routemap'] : [];

            $link = $this->getLink($ctrl,$actn,$route);
            $links[] = $link;
        }
        
        return $links;
    }
    
    /* Obtiene el link a la accion relacioanda al objeto del modelo */
    
    public function getLink($ctrl,$actn,$route = []){
        Configure::load('sitemap');
        $map = Configure::read($ctrl);
        
        $link = [
            'controller' => $ctrl,
            'action' => $actn
        ];
        
        foreach($route as $key => $field){
            $rawVal = $this->Data()->read($field);
            
            switch ($key){
                case 'slug':
                    $routeVal = $this->getSlug($rawVal);
                    break;
                default:
                    $routeVal = $rawVal;
            }
                    
            $link[$key] = $routeVal;
        }
        
        return [
            'url' => Router::url($link),
            'icon' => $map[$actn]['icon'],
            'title' => $map[$actn]['title']
        ];
    }

    /* En el constructor creamos un objeto de clase ModelData, una clase dummy en 
     * la cual guardaremos los valores del modelo aprovechando que php permiten 
     * asignar atrtibutos on the fly a los objetos.
     */
    
    public function __construct($id = false, $table = null, $ds = null) {
        parent::__construct($id, $table, $ds);
        
        $this->Data = new ModelData();
    }    
    
    /* Declaramos una funcion abstracta la cual debe devolver la metadata actual
     * de los modelos, dicha metadata es un descripcion formal de cada uno de los
     * campos.
     * 
     * required : campo requerido
     * unique : campo es unico
     * writable : campo puede escribirse
     * readable : campo puede leerse
     * type : tipo de dato
     * label : nombre formal del campo
     */
    
    abstract function getSchema();
    
    /* Lee de BD un registro segun su id y lo carga en su ModelData */

    public function loadFromId($id,$foreign = true){
        $rawdata = $this->findById($id);
        
        if($rawdata){
            $class = $this->alias;
            $blob = $rawdata[$class];            
            $this->loadArray($blob);
            $this->id = $id;
            
            if($foreign){
                $this->loadForeign();
            }
        
            return true;
        }
        
        return false;
    }
    
    /* Carga el hashmap especificado en el ModelData del modelo  */
    
    public function loadArray(array $data){        
        foreach($data as $field => $rawval){
            $this->Data->{$field} = $rawval;
            
            if($field === $this->primaryKey){
                $this->id = $rawval;
            }
        }
    }
    
    /* Obtiene el ModelData */
    
    public function Data(){
        return $this->Data;
    }
    
    /* Esta function guarda en BD el contenido de ModelData.
     *  NO HACE VALIDACIONES. Se basa en el ESQUEMA actual del modelo para
     * popular los campos.
     */
    
    public function store(){
        $response = false;
        $data = $this->Data->dump();
        $class = get_class($this);
        $store = [ $class => $data ];
        
        if($this->save($store, false)){
            $this->Data->write('id', $this->id);
            $response = true;
        }
        
        return $response;
    }
    
    /* Establece un bloqueo de proceso segun la llave especificada. */
    
    public function block($key,$timeout = 10){
        Configure::load('shared_memory');
        SharedMemory::start(Configure::read('SharedMemory.host'),Configure::read('SharedMemory.port'));        
        $mutexKey = "Mutex@$key";
        $stop = time() + $timeout;
        
        do{
            $current = time();            
            
            if($current < $stop){
                $data = SharedMemory::read($mutexKey);
                $exp = (int) @$data['expires'];
                $acquire = $exp < $current;

                if($acquire){
                    $expires = $current + $timeout;

                    $data = [
                        'timestamp' => $current,
                        'expires' => $expires
                    ];

                    SharedMemory::write($mutexKey,$data,uniqid(),$timeout);
                    return true;
                }
            
                sleep(1);
            }
        } while($current < $stop);
        
        throw new InternalErrorException('Proceso Bloqueado');
    }
    
    /* Desbloquea el proceso segun la llave especificada */
    
    public function unblock($key){        
        Configure::load('shared_memory');
        SharedMemory::start(Configure::read('SharedMemory.host'),Configure::read('SharedMemory.port'));
        
        $mutexKey = "Mutex@$key";
        $password = @SharedMemory::$register[$mutexKey];
        $cachedPassword = SharedMemory::password($mutexKey);   
        
        if($password == $cachedPassword){
            if(SharedMemory::delete($mutexKey)){
                unset(SharedMemory::$register[$mutexKey]);
                return true;
            }
        }
        
        return false;
    }
    
    /* Obtiene la URL configurada del modelo basandonse en la configuracion routemap
     * del sitemap correspondiente a la accion proporcionada. */
    
    public function getUrl($ctrl,$actn){
        Configure::load('sitemap');
        $conf = Configure::read("{$ctrl}.{$actn}");
        $url = [ 'controller' => $ctrl, 'action' => $actn ];
        
        foreach($conf['routemap'] as $param => $field){
            $val = '';
            
            switch($field){
                case 'id':
                    $val = $this->id;
                    break;
                default:
                    $val = $this->Data()->read($field);
                    break;
            }
            
            switch($param){
                case 'slug':
                    $val = $this->getSlug($val);
                    break;
            }
            
            
            $url[$param] = $val;
        }
        
        return $url;
    }
    
    /* Obtiene el slug del valor especificado */

    public function getSlug($val) {    
        $text = Inflector::slug($val);
        return $text;    
    }

    /* Obtiene el valor del ModelData pero formateado para vistas, dependiendo del
     * tipo de dato segun esquema.
     */
    
    public function getViewValue($field){
        $schema = $this->getSchema();
        $metadata = $schema[$field];
        $text = $this->Data()->read($field);
        
        if( is_null($text) ){
            return $this->getNullValue($field);
        }
        
        switch($metadata['type']){
            case 'datetime': return $this->getDatetimeText($field);
            case 'date': return $this->getDateText($field);
            case 'text': return $this->getViewText($field);
            case 'password': return $this->getViewPassword($field);
            case 'foreign': return $this->getViewForeign($field);
            case 'options': return $this->getViewOptions($field);
            default: return $this->Data()->read($field);
        }
    }
    
    private function getViewOptions($field){
        $schema = $this->getSchema();
        $options = $schema[$field]['options'];
        $value = $this->Data()->read($field);
        return $options[$value];
    }
    
    private function getViewForeign($field){
        $schema = $this->getSchema();
        $class = $schema[$field]['class'];
        return $this->Data()->read($class);
    }
    
    private function getViewText($field){
        return $this->Data()->read($field);
    }
    
    private function getDateText($field){
        $date = $this->Data()->read($field);
        $time = strtotime($date);
        
        return date('d/m/Y',$time);
    }
    
    private function getDatetimeText($field){
        $datetime = $this->Data()->read($field);
        $time = strtotime($datetime);
        
        return date('d/m/Y H:i:s',$time);
    }
    
    private function getNullValue($field){
        $schema = $this->getSchema();
        $metadata = $schema[$field];
        
        if(isset($metadata['null'])){
            return $metadata['null'];
        }
        
        
        return '<i>No definido</i>';
    }
    
    
    private function getViewPassword($field){
        return $this->getViewText($field);
    }
    
    /* Genera las reglas de validacion a partir del esquema del modelo y lo pone
     * a prueba con los datos actuales.
     */
    
    public function validates($opts = array()){
        if(empty($this->validate)){
            $this->generateValidations();
        }
        
        return parent::validates($opts);
    }
    
    /* Genera las validaciones a partir del esquema actual del modelo. */
    
    private function generateValidations(){   
        foreach($this->getSchema() as $field => $data){
            if(@$data['writable']){
                $this->validationsByType($field,$data);
            }
        }
    }
    
    /* Genera validaciones por tipo de dato */
    
    private function validationsByType($field,$data){
        $this->requiredValidation($field,$data);
        $this->uniqueValidation($field,$data);
        
        switch(@$data['type']){
            case 'text':
                $this->maxlengthValidation($field,$data);
                $this->minlengthValidation($field,$data);
                break;
            case 'password':
                $this->maxlengthValidation($field,$data);
                $this->minlengthValidation($field,$data);
                break;
            case 'int':
                $this->integerValidation($field,$data);
                break;
            case 'url':
                $this->maxlengthValidation($field,$data);
                $this->minlengthValidation($field,$data);
                $this->urlValidation($field,$data);
                break;
        }
    }
        
    /* Genera validación de URL valida */
    
    private function urlValidation($field,$data){
        $this->validate[$field]['url_validation'] = array(
            'rule' => 'url',
            'message' => 'Debe especificar una URL valida'
        );
    }
    
    /* Genera validación de requerimiento obligatorio */
    
    private function requiredValidation($field,$data){
        if(@$data['required']){            
            if(isset($data['required-message'])){
                $message = $data['required-message'];
            }
            else{
                $message = 'Campo obligatorio';
            }
            
            $this->validate[$field]['required_validation'] = array(
                'rule' => 'notEmpty',
                'required' => true,
                'message' => $message
            );
        }
    }
    
    /* Genera validación de requerimiento unicidad */
    
    private function uniqueValidation($field,$data){
        if(@$data['unique']){              
            $message = 'Valor ya existente, no puede repetirse.';
            $rule = array('isUniqueCheck',$field);
            
            $this->validate[$field]['unique_validation'] = array(
                'rule' => $rule,
                'message' => $message
            );     
        }
    }
    
    /* Busca en BD si existe un registro que ya contenga el valor deseado */
    
    public function isUniqueCheck($data, $field) {
        $class = $this->alias;
        $v = $data[$field];
        $exists = [];
        $id = 0;
        $unique = false;
                
        if( ! is_null($v)){
            $exists = $this->findByField($field,$v);
            
            if(isset($exists[$class]['id'])){
                $id = $exists[$class]['id'];
            }
        }

        if($this->id == $id){
            $exists = [];
        }
        
        if(empty($exists)){
            $unique = true;
        }
        
        return $unique;
    }
    
    /* Busca un registro por valor unico. */
    
    public function findByField($field,$value){
        $class = $this->alias;

        $cnd = [
            "$class.$field" => $value
        ];
        
        return $this->find('first',array( 'conditions' => $cnd ));
    }
    
    /* Genera validación por maxlength */
    
    private function maxlengthValidation($field,$data){
        if(isset($data['maxlength'])){
            $this->validate[$field]['maxlength_validation'] = array(
                'rule' => array('maxLength',$data['maxlength']),
                'message' => 'No debe superar los ' . $data['maxlength'] . ' caracteres'
            );
        }
    }
    
    /* Genera validación por minlength */
    
    private function minlengthValidation($field,$data){
        if(isset($data['minlength'])){
            $this->validate[$field]['minlength_validation'] = array(
                'rule' => array('minlength',$data['minlength']),
                'message' => 'Debe tener al menos ' . $data['minlength'] . ' caracteres'
            );
        }
    }
    /* Validación para entero */
    
    private function integerValidation($field,$data){        
        $this->validate[$field]['integer_validation'] = array(
            'rule' => '/^[0-9]*$/i',            
            'message' => 'Debe contener un entero positivo válido'
        );
    }
    
    /* Devuelve el campo que representa el LABEL del modelo. */
    
    abstract function getLabelField();
    
    /* Devuelve el nombre del icono que representa graficamente al modelo. */
    
    abstract function getIcon();
    
    /* Determina si la eliminacion del modelo es fisica o logica */
    
    private $deleteMethod;
    
    /* Determina<BOOL> si el modelo posee un metodo de eliminacion fisico.
     * En caso de devolver FALSE se asume que es un modelo logico.
     */
    
    abstract function isPhysicalDeletor(); 
    
    /* Devuelve el campo<STRING> que determina el estado actual del Modelo.
     * Esta campo idealmente debe almacenar informacion booleana indicando si el
     * modelo esta eliminado (logicamente) o no.
     * 
     * Si el modelo es fisico su implementacion es irrelevante.
     */
    
    abstract function getLogicalField();
    
    /* Callback comun a todos los modelos para interceptar los find queries. */

    public function beforeFind($query) {
        $query = $this->logicalFind($query);        
        return $query;
    }

    /* Se asegura de respetar la eliminacion logica de un registro, asegurando 
     * que el campo logico es considerado en las condiciones del FIND.
     * 
     * Si no existe el campo logico, procede a agregarlo matcheando el valor
     * de la variable de instancia $logicalFind actuak
     */
    
    public function logicalFind($query) {
        if( ! $this->isPhysicalDeletor()){
            if( ! isset($query['conditions'])){
                $query['conditions'] = [];
            }

            $cnd = $this->getLogicalCondition();

            if( ! isset($query['conditions'][$cnd])){
                $query['conditions'][$cnd] = $this->getLogicalFind();
            }
        }      
        
        return $query;
    }
    
    /* Determina si estamos manipulando objetos que existen o no, por defecto
     * siempre manejamos objetos existentes, pero para algunas funciones puede
     * ser util buscar registros logicos no existentes (archivados)     */
    
    private $logicalFind = true;
    
    function getLogicalFind() {
        return $this->logicalFind;
    }

    function setLogicalFind($logicalFind) {
        $this->logicalFind = $logicalFind;
    }
    
    /* Devuelve la condicion<STRING> para evaluar si el modelo logico existe o no 
     * dentro de un FIND
     */

    public function getLogicalCondition() {
        $class = $this->alias;
        $field = $this->getLogicalField();
        $cnd = "{$class}.{$field}";
        return $cnd;
    }

    /* Determina<BOOL> si la accion actual posee un requerimiento de estado de eliminacion
     * (para Modelo logicos) y chquea que se respete este requerimiento. Si el modelo
     * es fisico devuelve siempre TRUE.
     */
    
    public function matchStatefulAction($conf){
        $required = true;
        $status = true;
        
        if(isset($conf['status'])){
            $required = (bool) $conf['status'];
        }
        
        if( ! $this->isPhysicalDeletor()){
            $status = $this->getLogicalStatus();
        }
        
        return $required === $status;
    }

    /* Obtiene<BOOL> el estado de eliminacion logico del modelo actual. */
    
    public function getLogicalStatus(){
        $field = $this->getLogicalField();
        return $this->Data()->read($field);
    }
    
    /* Elimina los datos del modelo */
    
    public function clearFields(){
        $this->id = null;
        $this->Data()->flush();
    }
}

/* Interface para React API Models.
 * 
 * Cada servicio del ReactApi convoca a uno de estos metodos que debera
 * implementar cada Modelo, recibe como parámetro el Controlador de API y
 * los parámetros POST enviados desde el navegador.
 * 
 * El API permite convocar a los metodos:
 * 
 *      * setError($message) : para especificar un mensaje de error simple.
 *      * setData($array) : para para especificar la data que se devuelve al navegador.
 * 
 * Notese que invocar a setError desencadenará un error 406 HTTP.
 * 
 *  */

interface ReactApiModel {
    function drop($api,$post);
    function edit($api,$post);
    function push($api,$post);
    function feed($api,$post);
};

/*  Esta es una clase Dummy donde almacenaremos los datos asociados al modelo,
 *  para evitar usar los CakePHP array que son tan tediosos.
 * 
 *  */
    
class ModelData{    
    
    /* Devuelve en forma de array los datos contenidos . */
    
    public function dump(){
        $data = [];
        
        foreach(get_object_vars($this) as $field => $meta){
            $data[$field] = $this->read($field);
        }
        
        return $data;
    }
    
    /* Lee un campo del Objeto */
    
    public function read($field){
        if(isset($this->{$field})){
            return $this->{$field};
        }
        
        return null;
    }
    
    /* Inyecta un campo al objeto */
    
    public function write($field,$val){
        $this->{$field} = $val;        
    }    
    
    /* Carga un campo tipo foreign al objeto */
    
    public function loadForeign($field,$meta){
        $id = $this->read($field);    
                
        if($id){
            $class = $meta['class'];

            App::import('Model', $class);
            $Model = new $class();

            if($Model->loadFromId($id)){
                $label = $Model->getLabelField();
                $this->{$class} = $Model->Data()->read($label);
            }
        }
    }
    
    /* Elimina los datos almacenados */
    
    public function flush(){
        foreach(get_object_vars($this) as $field => $meta){
            $this->{$field} = null;
        }
    }
}