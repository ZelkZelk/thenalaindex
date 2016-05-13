<?php

App::uses('CrawlerUtilityComponent', 'Controller/Component');
App::import('Model', 'CrawlerLog');

/* Este componente se encarga de la conexión HTTP, solicitando la URL indicada */

class HttpClientComponent extends CrawlerUtilityComponent{
    private static $TAG = '[HTTP]';
    
    /* A veces los navegadores hacen redirecciones */
    
    private $effectiveUrl;
    
    public function getEffectiveUrl(){
        return $this->effectiveUrl;
    }
        
     
    /* Override de la funcion generica para agregar el TAG http
     */
    
    private function logHttp($message){
        $full = self::$TAG . ' ' . $message;
        $this->logInfo($full);
    }
    
    /* Llamar al inicializador antes de empezar el proceso */
    
    public function init(CrawlerLog $log,$logFunction) {  
        $this->setLogFunction($logFunction);
        $this->setCrawlerLog($log);
        
        Configure::load('http_client');
        $this->verbose = Configure::read('Http.verbose');
        $this->timeout = Configure::read('Http.timeout');
        $this->connectionTimeout = Configure::read('Http.connection_timeout');
        $this->userAgent = Configure::read('Http.user_agent');
    }
    
    /* Indica el nivel de verbosidad del CURL.
     * Conf@http_client:Http.verbose */
    
    private $verbose;
    
    /* GET 
     * 
     * Realiza la peticion GET HTTP de la URL especificada.
     * Por el momento solo se soporta GET.
     */
    
    private $Curl;
    
    /* Timeout de las funciones CURL*/
    
    private $timeout;
    
    /* Timeout de la conexion */
    
    private $connectionTimeout;
    
    /* User Agente que utilizara el CURL para realizar las conexiones */
    
    private $userAgent;
    
    public function get($url){
        $this->logHttp('GET:' . $url);
        $this->clear();
        
        $this->Curl = curl_init($url);
        curl_setopt($this->Curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->Curl, CURLOPT_VERBOSE, $this->verbose);
        curl_setopt($this->Curl, CURLOPT_HEADERFUNCTION, [ $this, 'headerInfo' ]);
        curl_setopt($this->Curl, CURLOPT_CONNECTTIMEOUT,$this->connectionTimeout); 
        curl_setopt($this->Curl, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->Curl, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($this->Curl, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($this->Curl, CURLOPT_FOLLOWLOCATION, 1);
                
        $this->response = curl_exec($this->Curl);        
        $this->status = curl_getinfo($this->Curl, CURLINFO_HTTP_CODE);
        $this->time = curl_getinfo($this->Curl, CURLINFO_TOTAL_TIME);
        $this->size = curl_getinfo($this->Curl, CURLINFO_SIZE_DOWNLOAD);
        $this->error = curl_errno($this->Curl);        
        $this->effectiveUrl = curl_getinfo($this->Curl, CURLINFO_EFFECTIVE_URL);
        
        $this->logConnection();
        $this->logCommit();
    }
    
    /* Tiempo total de conexion */
    
    private $time;
    
    function getTime() {
        return $this->time;
    }
    
    /* Tamanho de la descarga*/
    
    private $size;

    function getSize() {
        return $this->size;
    } 
    
    /* HTTP Status Code*/
    
    private $status;

    function getStatus() {
        return $this->status;
    }    
    
    /* Cuerpo de la Respuesta */
    
    private $response;
    
    public function getResponse(){
        return $this->response;
    }   
    
    /* Error de la Conexion */
    
    private $error;
    
    public function getError(){
        return $this->error;
    }   
    
    public function hasError(){
        if($this->error > 0){
            return true;
        }
        
        return false;
    }
    
    /* Limpia las variables de respuesta del componente */
    
    public function clear(){
        $this->time = null;
        $this->size = null;
        $this->status = null;
        $this->response = null;
        $this->error = 0;
        $this->headers = [];
    }
    
    /* Envia al log los datos de la conexion realizada */
    
    private function logConnection(){
        $this->logHttp("\t | STATUS = {$this->status}");
        $this->logHttp("\t | TIME = {$this->time}");
        $this->logHttp("\t | SIZE = {$this->size}");
        
        if($this->hasError()){
            $this->logHttp("\t | ERROR = {$this->error}");
        }
    }
    
    private $headers = [];
 
    /* Determina si una cabecera existe en la respuesta obtenida. */
    
    public function checkHeader($key){
        $response = false;
        
        if(isset($this->headers[$key])){
            $response = true;
        }
        
        return $response;
    }
    
    /* Devuelve la el valor de la cabecera correspondiente a su nombre, si existe.
     * NULL si no existe.
     */
    
    public function readHeader($key){
        $header = null;
        
        if($this->checkHeader($key)){
            $header = trim($this->headers[$key]);
        }
        
        return $header;
    }
    
    /* Recibe las cabeceras de respuesta de la conexion HTTP, puede ser
     * util en el futuro con propositos de DEBUG. */
    
    public function headerInfo($curl,$header){
        @list($key,$val) = explode(':', $header, 2);        
        $this->headers[$key] = $val;        
        return strlen($header); 
    }
    
    /* Genera un print_r de las cabeceras y el cuerpo de la respuesta HTTP */
    
    public function debug(){
        print_r($this->headers);
        print_r($this->response);
    }
    
    /* Determina si la respuesta HTTP es aceptable (sin errores de CURL y status 200) */
    
    public function isAcceptable(){
        $response = true;
        
        if($this->hasError()){
            $response = false;
        }
        else if($this->getStatus() !== 200){
            $response = false;
        }
        
        return $response;
    }
}