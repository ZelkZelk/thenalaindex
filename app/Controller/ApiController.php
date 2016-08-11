<?php

App::uses('AppController', 'Controller');

class ApiController extends AppController {    
    /* Configuracion del Api se basa en el archivo app/Config/react_api.php,
     * este controlador ofrece funciones basicas de ABM y lectura de datos
     * para interfaces React usando formato JSON.
     */
    
    private $Config;
    
    /* Modelo a utilizar por el API, lo carga a partir del campo model de la
     * Configuración, si no implementa ReactApiModel desencadena un error.
     */
    
    private $Model;
    
    /* Singleton que contiene los metodos para setear y errores y datos. */
    
    private $Api;
    
    /* La Configuracion del API se carga de la misma forma para todo servicio. */
    
    public function beforeFilter() {
        parent::beforeFilter();
        
        $this->loadApiConfig();
        $this->jsonHeaders();
        $this->autoRender = false;
    }
    
    /* Inyecta las cabeceras apropiadas para que le navegador detecte el
     * formato JSON
     */
    
    private function jsonHeaders(){
        header('Content-Type: application/json');
    }
    
    /* Se basa en los keys del array asociativo de la configuracion par
     * Controlador/Accion, comunmente este par será el mismo que solicita
     * el servicio. Y se puede inferir en la ruta. Parametros ctrl y actn, 
     * respectivamente.
     */
    
    private function loadApiConfig(){
        $ctrl = $this->params['ctrl'];
        $actn = $this->params['actn'];
        $key = "ReactApi.{$ctrl}.{$actn}";
        
        Configure::load('react_api');
        
        $this->Config = Configure::read($key);
        
        if($this->Config){
            $class = $this->Config['model'];
            
            App::import('Model',$class);
            $this->Model = new $class();
            
            if($this->Model instanceof ReactApiModel){                
                $api = new ApiStatus();
                $api->setError = $this->setError;
                $api->setData = $this->setData;                
                $this->Api = $api;
            }
            else{
                throw new InternalErrorException("Modelo '$class' no implementa ReactApiModel");                
            }
        }
        else{
            throw new InternalErrorException("$key no configurada en ReactApi");
        }
    }
    
    /* Cada método convocara a su método análogo de la interface ReactApi. */
    
    public function push(){        
        $this->Model->push($this->Api,$this->data);
    }
    
    public function edit(){
        $this->Model->edit($this->Api,$this->data); 
    }
    
    public function drop(){
        $this->Model->drop($this->Api,$this->data);    
    }
    
    public function feed(){
        $this->Model->feed($this->Api,$this->data);     
    }
    
    /* Luego de ejecutar las funciones se envia la respuesta al navegador,
     * esta funcion se ejecuta en comun sea cual sea el metodo invocado.
     */
    
    public function afterFilter() {
        $this->output();
    }
    
    /* Decide que respuesta devuelve al navegador, dependiendo si error esta
     * seteado o no devuelve http 406 mas el mensaje del error.
     * 
     * Caso contrario devuelve http 200 mas la data en formato JSON.
     */
    
    private function output(){
        if(is_null($this->Api->getError())){
            $this->outputData();
        }
        else{
            $this->outputError();
        }
        
        exit;
    }
    
    /* Despliega la data en formato JSON */
    
    private function outputData(){
        echo json_encode($this->Api->getData());
    }
    
    /* Desencadena un error e imprime el mensaje */
    
    private function outputError(){
        header('HTTP/ 406 Not Acceptable');
        echo $this->Api->getError();
    }
}

class ApiStatus {
    
    /* Setea el error, notese que el error es chequeado antes de producir el
     * output y de no ser null desencadena un error HTTP 406.
     */
    
    private $error = null;
    
    public function setError($error){
        $this->error = $error;
    }
    
    public function getError(){
        return $this->error;
    }
    
    /* Establece la data que debe enviarse al navegador si la respuesta es valida,
     * notese que una llamada posterior a setError tiene mayor prioridad, y por tanto,
     * desencadenará el error.
     */
    
    private $data = [];
    
    public function setData($data){
        $this->data = $data;
    }    
    
    public function getData(){
        return $this->data;
    }
}