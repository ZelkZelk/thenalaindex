<?php

/* Este componente se encarga de la logica comun al hacer operaciones de ABM. */

/* Constantes que representan el estado de las operaciones. */

define('SCAFFOLD_OK',0x0);
define('SCAFFOLD_FAIL',0x1);
define('SCAFFOLD_NOT_VALID',0x2);
define('SCAFFOLD_EMPTY',0x3);
define('SCAFFOLD_NO_INPUT',0x4);

class ScaffoldComponent extends Component{
    private $Controller;
    private $input;
    private $output;
    private $Model;
    private $conditions = [];
    private $page = 0;
    private $count = 0;
    private $limit = 10;
    private $view;
    private $icon;
    private $button;
    private $emptyMessage;
    private $renderActions = true;
    private $links = [];
    private $order = 'id DESC';
        
    public function initialize(\Controller $controller) {
        $this->Controller = $controller;
        $this->Controller->set('Scaffold',$this);
        
        return parent::initialize($controller);
    }
    
    /* Obtiene el parámetro de página para urls con paginador. */

    public function getPagedParam(){
        $routemap = $this->getRouteMap();
        return $routemap['current'];
    }
    
    /* Obtiene la ruta actual en formato array. */
    
    public function getCurrentRoute(){
        $route = [];
        $route['controller'] = $this->Controller->params['controller'];
        $route['action'] = $this->Controller->params['action'];
        
        return $route;
    }
    
    /* Agrega un link a la accion */
    
    public function addLink($ctrl,$actn,$single = true){
        $this->links[$ctrl][$actn] = $single;
    }
    
    /* Determina si se deben renderear las acciones adjuntas a la vista. */
    
    public function setRenderActions($r){
        $this->renderActions = $r;
    }
    
    /* Setea el mensaje de error cuando no hay objetos que desplegar. */
    
    public function setEmptyMessage($m){
        $this->emptyMessage = $m;
    }
    
    /* Setea el icono a utilizar */
    
    public function setIcon($i){
        $this->icon = $i;
    }
    
    /* Setea el label del boton a usar */
    
    public function setButton($b){
        $this->button = $b;
    }
    
    /* Rendere la vista relacionada a la ultima accion ejecutada. */
    
    public function render(){
        $view = "../Scaffold/actions/{$this->view}";
        $this->Controller->render($view);
    }
    
    /* Inicializa la data a almacenar */
    
    public function setInput(array $data){
        $this->input = $data;
    }
    
    /* Inicializa el modelo */
    
    public function setModel(AppModel $Model){
        $this->Model = $Model;
    }
    
    /* Establece la pagina a desplegar el paginador */
    
    public function setPage($page){
        if($page == 0){
            return;
        }

        $this->page = abs($page);
    }    
    
    /* Cantidad de objetos por pagina */
    
    public function setLimit($limit){
        if($limit == 0){
            return;
        }
        
        $this->limit = abs($limit);
    }
    
    public function setConditions(array $cnd){
        $this->conditions = $cnd;
    }
    
    /* Agrega a BD un registro, se basa en el Modelo actual y los datos proporcionados
     * para preparar la query.
     */
    
    public function add(){
        $this->setView('add');
        
        if( ! empty($this->input)){
            $this->Model->id = null;
            $this->Model->set($this->input);
            $this->Model->loadArray($this->input);

            if( ! $this->Model->validates()){
                return SCAFFOLD_NOT_VALID;
            }

            if($this->Model->save($this->getSaveData(),false)){            
                return SCAFFOLD_OK;
            }
            else{                       
                return SCAFFOLD_FAIL; 
            }
        }
        
        return SCAFFOLD_NO_INPUT;
    }
    
    /* Edita de BD un registro, se basa en el Modelo actual y los datos proporcionados
     * para preparar la query.
     */
    
    public function edit(){        
        if( ! $this->scanRouted()){
            throw new NotFoundException();
        }
        
        $this->setView('edit');
        
        if( ! empty($this->input)){
            $this->Model->set($this->input);
            $this->Model->loadArray($this->input);

            if( ! $this->Model->validates()){
                return SCAFFOLD_NOT_VALID;
            }

            if($this->Model->save($this->getSaveData(),false)){            
                return SCAFFOLD_OK;
            }
            else{                       
                return SCAFFOLD_FAIL; 
            }
        }
        
        return SCAFFOLD_NO_INPUT;
    }
    
    /* Elimina de BD un registro, se basa en el Modelo actual y los datos proporcionados
     * para preparar la query.
     */
    
    public function delete(){        
        if( ! $this->scanRouted()){
            throw new NotFoundException();
        }
        
        $this->setView('delete');
        
        if($this->Controller->params->is('POST')){
            if($this->Model->isPhysicalDeletor()){
                return $this->physicalDelete();
            }
            else{
                return $this->logicalDelete();
            }
        }
        
        return SCAFFOLD_NO_INPUT;
    }
    
    /* Eliminacion fisica del registro */
    
    private function physicalDelete(){     
        if($this->Model->delete()){            
            return SCAFFOLD_OK;
        }
        else{                       
            return SCAFFOLD_FAIL; 
        }
    }
    
    /* Eliminacion logica del registro */
    
    private function logicalDelete(){     
        $field = $this->Model->getLogicalField();
        
        if($this->Model->saveField($field,false)){            
            return SCAFFOLD_OK;
        }
        else{                       
            return SCAFFOLD_FAIL; 
        }
    }
    
    
    /* Deselimina de BD un registro, se basa en el Modelo actual y los datos proporcionados
     * para preparar la query. Solo es utilizable con Modelo cuyo metodo de eliminacion sea
     * logico. Pues una eliminacion fisica implica eliminar fisicamente el registro, lo
     * cual lo hace irreversible.
     */
    
    public function undelete(){        
        $this->Model->setLogicalFind(false);
        
        if( ! $this->scanRouted()){
            throw new NotFoundException();
        }
        
        $this->setView('undelete');
        
        if($this->Controller->params->is('POST')){
            $field = $this->Model->getLogicalField();

            if($this->Model->saveField($field,true)){            
                return SCAFFOLD_OK;
            }
            else{                       
                return SCAFFOLD_FAIL; 
            }
        }
        
        return SCAFFOLD_NO_INPUT;
    }
    
    /* Obtiene la URL<String> actual. Especifica $full<BOOL> si necesitas el protocolo
     * y dominio como parte de la respuesta. */
    
    public function getCurrentURL($full = false){
        $here = $this->Controller->here;
        
        if($full){
            $url = Router::url($here, true);
        }
        else{            
            $url = $here;
        }
        
        return $url;
    }
    
    /* Obtiene la pagina actual basandose en la configuracion de rutas paginadas. */
    
    public function getCurrentPage(){
        $pagedParam = $this->getPagedParam();
        $page = 1;
        
        if(isset($this->Controller->params[$pagedParam])){
            $page = (int) $this->Controller->params[$pagedParam];        
        
            if($page < 1){
                $page = 1;
            }
        }
        
        return $page;
    }
    
    /* Muestra el listado de objetos de la pagina especificada. */
    
    public function show(){
        $this->setView('show');
        
        if( ! $this->page){
            $this->page = $this->getCurrentPage();
        }
        
        $opts = [];
        $opts['conditions'] = $this->conditions;
        $opts['limit'] = $this->limit;
        $opts['order'] = $this->order;
        $opts['offset'] = ($this->page - 1) * $this->limit;
        
        $rawdata = $this->Model->find('all',$opts);
        
        if($rawdata){
            unset($opts['order']);
            unset($opts['limit']);
            unset($opts['offset']);
            
            $this->setCount($this->Model->find('count',$opts));
            $this->setOutput($rawdata);
            return SCAFFOLD_OK;
        }
        else{
            return SCAFFOLD_EMPTY;
        }
    }
    
    /* Muestra el listado eliminado logicamente del Modelo especificado */
    
    public function archive(){
        $this->Model->setLogicalFind(false);
        $this->show();
    }
        
    /* Carga el nombre de la vista a ser rendereada */
    
    private function setView($v){
        $this->view = $v;
    }
    
    /* Carga el contador de paginado */
    
    private function setCount($count){
        $this->count = $count;
    }
    
    /* Carga el output */
    
    private function setOutput($data){
        $this->output = $data;
    }
    
    /* Getters */
    
    public function getLinks(){
        return $this->links;
    }
    
    public function getOutput() {
        return $this->output;
    }

    public function Model() {
        return $this->Model;
    }

    public function getPage() {
        return $this->page;
    }

    public function getLimit() {
        return $this->limit;
    }

    public function getCount(){
        return $this->count;
    }
    
    public function getIcon() {
        return $this->icon;
    }

    public function getButton() {
        return $this->button;
    }
    
    public function getEmptyMessage() {
        return $this->emptyMessage;
    }
    
    public function getRenderActions() {
        return $this->renderActions;
    }
    
    /* Obtiene la informacion que va a guardarse, llama a distinto callbacks,
     * dependiendo del tipo de dato, particularmente importante, crypto para passwords.
     */

    public function getSaveData() {
        $data = $this->input;
        
        foreach($this->Model->getSchema() as $field => $meta){
            switch(@$meta['type']){
                case 'password':
                    $data[$field] = $this->crypto($this->input[$field],$meta);
                    break;
            }
        }
        
        $statefulData = $this->getStatefulData($data);
        
        return $statefulData;
    }
    
    /* Agrega a la data el campo de estado logico, de ser necesario dependiendo
     * del metodo de eliminacion que aplica el Modelo.
     */
    
    private function getStatefulData($data,$status = true){
        if( ! $this->Model->isPhysicalDeletor()){
            $field = $this->Model->getLogicalField();
            $data[$field] = $status;
        }
        
        return $data;
    }

    /* Encripta el string proporcionado usando el algoritmo y la sal del esquema */
    
    private function crypto($val,$meta){
        $crypto = $meta['crypto'];
        $salt = $meta['salt'];
        
        switch($crypto){
            case 'md5':
                $hashed = md5($val . $salt);
                break;
            default:
                $hashed = $val;
        }  
        
        return $hashed;
    }
    
    /* Dado un modelo, escánea la URL en busca de los datos almacenados para 
     * desplegarlos en pantalla. Adicionalmente crea las URL necesarias para
     * ir fácilmente a otras acciones relacionadas. 
     * 
     * 404 si no encuentra los datos.
     */
    
    public function detail(){        
        $this->setView('detail');
        
        if( ! $this->scanRouted()){
            throw new NotFoundException;
        }
    }
    
    /* Busca en la URL el registro asociado a la BD según los parámetros enrutados
     * configurados para el modelo y controlador actuales. Se asegura que se respete
     * la existencia logica del Modelo de implementarse metodo de eliminacion logico.
     */
    
    private function scanRouted(){
        $routemap = $this->getRouteMap();
        $idParam = array_search('id',$routemap);
        $id = $this->Controller->params[$idParam];
        
        return $this->Model->loadFromId($id);
    }

    /* Obtiene la configuracion de la ruta actual. */
    
    public function getRouteMap() {        
        Configure::load('sitemap');
        $route = $this->getCurrentRoute();
        $actn = $route['action'];
        $ctrl = $route['controller'];
        
        $actions = Configure::read($ctrl);
        $action = $actions[$actn];
        $routemap = $action['routemap'];
        return $routemap;
    }
}