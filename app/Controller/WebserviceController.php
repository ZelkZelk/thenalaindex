<?php

App::uses('Controller', 'Controller');

class WebserviceController extends Controller {    
    private $output = [];
    
    public function deleteOutput($key = null){
        if(is_null($key)){
            $this->output = [];
            return;
        }
        
        unset($this->output[$key]);
    }
    
    public function pushOutput($data, $key = null){
        if(is_null($key)){
            $this->output = $data;
            return;
        }
        
        $this->output[$key] = $data;
    }
    
    /* Este webservice devuelve siempre un JSON */
    
    public function beforeRender() {
        header('Content-Type: application/json');
        
        ob_clean();
        echo json_encode($this->output);
        ob_flush();
        
        exit;
    }
    
    /* Basado en los parametros enrutados redirige el flujo al metodo
     * 
     * $version = $this->version['version'];
     * $webservice = $this->version['webservice'];
     * 
     * {$webservice}_v{$version}
     * 
     * Si dicho metodo no existe se devuelve un status code 404
     */
    
    private $Webservice;
    
    public function index(){
        $this->version = $this->params['version'];
        $this->webservice = $this->params['webservice'];
        $this->extra = $this->params['extra'];
        
        $component = "WebserviceVersion{$this->version}Component";
        $method = "{$this->webservice}";
        
        App::import('Controller/Component',$component);
        
        if(class_exists($component) === false){
            throw new NotFoundException("Class $component not found");
        }
        
        $collection = new ComponentCollection();
        $this->Webservice = new $component($collection);
        $this->Webservice->initialize($this);
        
        if(method_exists($this->Webservice, $method)){
            if($this->parseConf()){
                $this->exec($method);
            }
            else{
                throw new ForbiddenException();
            }
        }
        else{
            throw new NotFoundException("$method() not found");
        }
    }
    
    private function exec($method){
        try{
            $this->Webservice->$method();
        }
        catch(Exception $e){
            header("X-WebService-Exception: {$e->getMessage()}");
            throw $e;
        }
    }
    
    /** Parsea el archivo de configuracion y determina si los parametros pasados 
     * son validos.
     */
    
    private $get = false;
    private $post = false;
    private $schema = [];
    
    private function parseConf(){
        Configure::load("webservice_v{$this->version}");
        $key = "Webservice.{$this->webservice}";
        $conf = Configure::read($key);
        
        if($conf){
            $this->get = (boolean) @$conf['get'];
            $this->post = (boolean) @$conf['post'];
            $this->schema = (array) @$conf['data'];
        }
        
        return $this->analyzeConf();
    }
    
    /** Analiza la configuracion y determina si es valida. */
    
    private function analyzeConf(){
        $isGet = $this->request->is('get');
        $isPost = $this->request->is('post');
        
        if($isGet){
            return $this->analyzeGet();
        }
        
        if($isPost){
            return $this->analyzePost();
        }
        
        return false;
    }
    
    /** Analiza la peticion GET */
    
    private function analyzeGet(){
        if($this->get){
            $this->webserviceData = $this->params->query;
            return $this->analyzeData($this->data);            
        }
        
        return false;
    }
    
    /** Analiza la peticion POST */
    
    private function analyzePost(){
        if($this->post){
            $this->webserviceData = $this->data;
            return $this->analyzeData();
        }
        
        return false;
    }
    
    /* Analiza los datos enviados */
    
    private $webserviceData = [];
    
    private function analyzeData(){
        foreach($this->schema as $key => $data){
            $exists = false;
            $required = (boolean) @$data['required'];
            $readonly = (boolean) @$data['readonly'];
            $type = (string) @$data['type'];
            $value = @$data['default'];
            
            if(isset($this->webserviceData[$key]) && $readonly === false){
                $value = $this->webserviceData[$key];
                $exists = true;
            }
            
            if($required && $exists === false){
                throw new BadRequestException("Debe especificarse parametro $key");
            }
            
            list($newValue,$typeResult) = $this->analyzeType($data,$value,$type);
            
            if($typeResult == false){
                throw new BadRequestException("Parametro $key debe ser $type");
            }
            
            $this->webserviceData[$key] = $newValue;
        }
        
        return true;
    }
    
    /** Analiza el tipo de dato y devuelve un flag que determina si es correcto o no */
    
    private function analyzeType($data,$value,$type){
        $result = true;
        
        switch($type){
            case 'int':
                $result = $this->analyzeInt($data,$value);
                $value = (int) $value;
                break;
        }
        
        return [ $value, $result ];
    }
    
    /** Analiza si el valor encierra un entero valido */
    
    private function analyzeInt($data,$value){
        if(isset($data['range'])){
            $min = $data['range'][0];
            $max = $data['range'][1];
            
            if(is_null($min) === false && $value < $min){
                return false;
            }
            
            if(is_null($max) === false && $value > $max){
                return false;
            }
            
//            return false;
        }
        
        return preg_match('/^(\d)+$/',$value);
    }
    
    public function getWebserviceData($key = null){
        if(is_null($key)){
            return $this->webserviceData;
        }
        
        if(isset($this->webserviceData[$key])){
            return $this->webserviceData[$key];
        }
        
        return null;
    }
}