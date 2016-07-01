<?php

/* Este componente se encarga de la conexión HTTP, solicitando la URL indicada */

class CrawlerUtilityComponent extends Component{
    public function __call($method, $args){
        if(is_callable(array($this, $method))) {  
            if(call_user_func_array($this->$method, $args) === false){
                echo get_called_class() . '::' . $method . "\n";
            }
        }
    }       
    
    /* Log del Crawler, especificarlo para logueos a la BD */
    
    private $CrawlerLog = null;
    
    public function setCrawlerLog(CrawlerLog $log){
        $this->CrawlerLog = $log;
    }
    
    public function getCrawlerLog(){
        return $this->CrawlerLog;
    }
    
    /* Funciona gestora de logueo a archivo.log */
    
    public function setLogFunction($fn){
        $this->logFunction = $fn;
        
    }
    
    /* Funcion generica de log.
     * En caso de que se quiera agregar un LEGEND, TAGS, INFO extra, etc. 
     * concatenar aqui.
     */
    
    protected function logcat($message){
        $this->logFunction($message);
    }
    
    /* Funcion que utiliza la funcion de loggin.
     * Agrega un mensaje no muy urgente (no se comitea al instante)
     *  */
    
    protected function logInfo($message){
        $this->logcat($message);
    }
    
    /* Funcion que utiliza la funcion de loggin 
     * Agrega un mensaje importante (se comitea al instante)*/
    
    protected function logCritical($message){
        $this->logcat($message);
    }
    
    /* Comitea los mensajes existentes en memoria a la BD */
    
    protected function logCommit(){
        if( ! is_null($this->CrawlerLog)){
            $this->CrawlerLog->commit();
        }
    }
}