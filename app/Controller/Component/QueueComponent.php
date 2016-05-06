<?php

App::uses('CrawlerUtilityComponent', 'Controller/Component');
App::import('Model', 'CrawlerLog');

/* Este componente maneja el encolado de las URLs a ser exploradas.
 * Asegura que las URL no se repetiran.
 * 
 * A bajo nivel mantiene dos estructuras:
 * 
 *      * La cola de pendientes: donde se almacenan las URL aun sin procesar
 *      * El deposito de URLs ya exploradas: donde se almacenan aquellas URLS
 *          que ya han sido procesadas o alcanzado un limite de error. */

class QueueComponent extends CrawlerUtilityComponent{
    private static $FAIL = 0;
    private static $DONE = 1;
    private static $ROBOTS_TXT_DISALLOW = 2;
    
    /* Llamar al inicializador antes de empezar el proceso */
    
    public function init(CrawlerLog $log,$logFunction) {  
        $this->setLogFunction($logFunction);
        $this->setCrawlerLog($log);
        
        Configure::load('queue');
        $this->salt = Configure::read('Queue.salt');
        $this->collisionable = Configure::read('Queue.collisionable');
    }    
    
    /* Cola de URLs Pendientes.
     * 
     * Array Hash indexado por md5($url)
     * 
     * [ '000aaa000aaa000aaa000bbbcccaaa11' ] = []
     * 
     * Incluye un array 'urls' con las urls requeridas
     * 
     *  'urls' =>
     *      [ www.abc.com.py, www.abc.com.de ]
     * 
     * Incluye un array 'failure' con la cantidad de fallos cada url.
     * Los indices son respectivos a las urls dentro de 'urls'.
     * 
     *  'failure' =>
     *      [ 0, 2 ]
     * 
     * El limite de fallos esta configurado en 
     * Conf@queue:Queue.failure_limit
     * 
     * Una vez que se llega al limite de fallos se manda al Deposito con flag
     * failure.
     *      
     *  */
    
    private $Queue = [];
    
    /* Marca una URL Como procesada correctamente */
    
    public function done($url){
        if( ! $this->pop($url)){
            return false;
        }
        
        if( ! $this->deposit($url,self::$DONE)){
            return false;
        }        
        
        $this->logQueue('Done:' . $url);
        return true;
    }
    
    /* Marca una URL Como fallida */
    
    public function fail($url){
        if( ! $this->pop($url)){
            return false;            
        }
        
        if( ! $this->deposit($url,self::$FAIL)){
            return false;
        }        
        
        $this->logQueue('Fail:' . $url);
        return true;
    }
    
    /* Marca una URL Como denegada por el ROBOTS.TXT */
    
    public function robotsDisallow($url){
        if( ! $this->pop($url)){
            return false;            
        }
        
        if( ! $this->deposit($url,self::$ROBOTS_TXT_DISALLOW)){
            return false;
        }        
        
        $this->logQueue('RobotDisallow:' . $url);
        return true;
    }
    
    /* Incrementa el contador de fallos de la URL */
    
    public function failureIncrement($url){ 
        if($this->enqueued($url) === false){
            return false;
        }
        
        $key = $this->key($url);        
        $position = $this->urlPosition($key, $url, $this->Queue);
        
        if($position === false){
            return false;
        }
        
        $counter = $this->Queue[$key]['failure'][$position];
        $counter++;
        
        $this->Queue[$key]['failure'][$position] = $counter;        
        $this->logQueue('FailureIncrement:' . $url .' <Fail:' . $counter . '>');
        
        return $counter;
    }
    
    /* */
    
    /* Saca una URL de la Cola y lo transporta al Deposito. */
    
    private function pop($url){
        if( ! $this->enqueued($url)){
            return false;
        }
        
        return $this->popQueue($url);
    }
    
    /* Elimina del array 'urls' del key Queue la url especifica, si el
     * array urls queda vacio, elimina el key del Queue.
     */
    
    private function popQueue($url){     
        $key = $this->key($url);
        $position = $this->urlPosition($key,$url,$this->Queue);
        
        if($position !== false){
            unset($this->Queue[$key]['urls'][$position]);
            $this->Queue[$key]['urls'] = array_values($this->Queue[$key]['urls']);
            
            $failures = $this->Queue[$key]['failure'][$position];
            unset($this->Queue[$key]['failure'][$position]);
            $this->Queue[$key]['failure'] = array_values($this->Queue[$key]['failure']);
            
            if(empty($this->Queue[$key]['urls'])){
                unset($this->Queue[$key]);
            }
            
            $this->logQueue('Pop:' . $url . ' <Fail:' . $failures . '>');
            return true;
        }
        
        return false;
    }
    
    /* Agrega una nuevo URL a la Cola, revisa que no exista tanto en la Cola
     * como en el Deposito.   
     **/
    
    public function push($url){
        if($this->enqueued($url)){
            return false;
        }
        
        if($this->deposited($url)){
            return false;
        }
        
        $this->pushQueue($url);        
        return true;
    }
    
    /* Agrega la url a la cola, en el key adecuado */
    
    private function pushQueue($url){        
        $key = $this->key($url);
        
        if( ! $this->queueExists($key)){
            $this->Queue[$key] = [
                'urls' => [],
                'failure' => []
            ];
        }
        
        $this->pushDefaultQueue($key,$url);
        $this->logQueue('Push:' . $url);
    }
    
    /* Agrega al array 'urls' del hash key Queue la URL en la ultima posicion del array,
     * analogamente agrega una entrada al array 'failure' y para otra informacion
     * futura.
     */
    
    private function pushDefaultQueue($key,$url){
        $index = count($this->Queue[$key]['urls']);
        $this->Queue[$key]['urls'][$index] = $url;
        $this->Queue[$key]['failure'][$index] = 0;
    }
    
    /* Determina si la URL se encuentra en cola.
     * Para ello se analiza primero si existe el Key en el hash Queue
     * Luego se analiza si existe la URL en el array 'urls' dentro del Queue */
    
    private function enqueued($url){
        $key = $this->key($url);
        $enqueued = false;
        
        if($this->queueExists($key)){
            if($this->urlQueueExists($key, $url)){
                $enqueued = true;
            }
        }
        
        return $enqueued;
    }
    
    /* Determina si la URL se encuentra en el Deposito */
    
    private function deposited($url){        
        $key = $this->key($url);
        $deposited = false;
        
        if($this->depositExists($key)){
            if($this->urlDepositExists($key, $url)){
                $deposited = true;
            }
        }
        
        return $deposited;
    }
        
    
    /* Determina si existe el key en el Array Hash Queue */
    
    private function queueExists($key){
        $exists = $this->keyExists($key,$this->Queue);
        return $exists;
    }
    
    /* Determina si existe el key en el Array Hash Deposit */
    
    private function depositExists($key){
        $exists = $this->keyExists($key,$this->Deposit);
        return $exists;
    }
    
    /* Determina si existe el key en el Array Hash especificado */
    
    private function keyExists($key,array $array = []){
        $exists = false;
        
        if(isset($array[$key])){
            $exists = true;
        }
        
        return $exists;
        
    }
    
    /* Determina si existe una url en el array urls del hash key Queue */
    
    private function urlQueueExists($key,$url){
        $exists = $this->urlExists($key, $url, $this->Queue);
        return $exists;
    }
    
    /* Determina si existe una url en el array urls del hash key Deposit */
    
    private function urlDepositExists($key,$url){
        $exists = $this->urlExists($key, $url, $this->Deposit);
        return $exists;
    }
    
    /* Determina si existe la url en array urls de la estrucutra de datos especificada */
    
    private function urlExists($key,$url,array $array = []){
        $exists = false;
        
        if($this->urlPosition($key, $url, $array) !== false){
            $exists = true;
        }
        
        return $exists;        
    }
    
    /* Determina la posicion de una url en el array urls de la estrucutra de datos especificada */
    
    private function urlPosition($key,$url,array $array = []){
        $blob = $array[$key];
        $urls = $blob['urls'];
        
        return array_search($url, $urls);        
    }
    
    
    /* Funcion que obtiene el hash key tanto para la Cola como para el Deposito,
     * basado unicamente en la URL a ser utilizada.
     * 
     * genera un MD5($url) sin embargo eso no es suficiente chequeo debido a 
     * las colisiones;
     * 
     * Se chequea luego el array interno "urls" para comprobar una colision real,
     * url por url.
     * 
     * La sal del md5
     * Config@queue:Queue.salt
     */
    
    private $salt;
    private $collisionable;
    
    /* Si collisionable esta habilitado se utiliza la ultima letra de la url
     * como key. Esto con propositos de DEBUG, desactivar en produccion.
     * 
     * Collisionable se configura
     * Config@queue:Queue.collisionable
     */
    
    private function key($url){
        if($this->collisionable){
            return $this->collisionKey($url);
        }
        
        $full = $url . $this->salt;
        $key = md5($full);
        return $key;
    }
    
    /* Devuelve la ultima letra de la URL, para usarla a mode de key DEBUG
     * cuando el flag collisionable esta activo.
     */
    
    private function collisionKey($url){
        $key = substr($url, -1);
        return $key;
    }
    
    /* Deposito de URLs Procesadas.
     * 
     * Array Hash indexado por md5($url)
     * 
     * [ '000aaa000aaa000aaa000bbbcccaaa11' ] = []
     * 
     * Incluye un array 'urls' con las urls procesadas
     * 
     *  'urls' =>
     *      [ www.abc.com.py, www.abc.com.de, www.hola.com ]
     * 
     * Incluye un array 'status' con el estado de cada rl.
     * 
     * FAIL = 0
     * DONE = 1
     * 
     *  'status' =>
     *      [ 0, 1, 0 ]
     * 
     *      
     *  */
    
    private $Deposit = [];
    
    /* Deposita una URL en el Deposito, analogamente a Queue, cada key contiene 
     * su array 'urls', mientras que el array 'status' se popula segun el
     * valor pasado como parametro.
     * 
     * A diferencia de Queue no es necesario hacer chequeos de unicidad.
     */
    
    private function deposit($url,$status){
        $key = $this->key($url);
        
        if( ! isset($this->Deposit[$key])){
            $this->Deposit[$key] = [
                'urls' => [],
                'status' => []
            ];
        }
        
        if( ! in_array($url, $this->Deposit[$key]['urls'])){
            $index = count($this->Deposit[$key]['urls']);
            $this->Deposit[$key]['urls'][$index] = $url;
            $this->Deposit[$key]['status'][$index] = $status;
            return true;
        }
        
        return false;
    }
    
    /* Funcion que llama a logcat, util para agregar info extra a todos los 
     * mensajes.
     */
    
    private static $TAG = '[QUEUE]';
    
    private function logQueue($message){
        $full = self::$TAG . ' ' . $message;
        $this->logInfo($full);
    }
    
    /* Funcion que hace print_r en caliente del QUEUE y el DEPOSIT */
    
    public function debug(){
        print_r($this->Queue);
        print_r($this->Deposit);
    }
    
    /* Lee la cabeza de la Cola.
     * Devuelve false si la cola esta vacia.
     */
    
    public function fetch(){
        if( $this->isEmpty()){
            return false;
        }
        
        $data = reset($this->Queue);
        $urls = $data['urls'];
        
        if( ! isset($urls[0])){        
            return false;
        }
        
        $url = $urls[0];
        return $url;
    }
    
    /* Determina si la Cola esta vacia */
    
    private function isEmpty(){
        $response = false;
        
        if(empty($this->Queue)){
            $response = true;
        }
        
        return $response;
    }
}