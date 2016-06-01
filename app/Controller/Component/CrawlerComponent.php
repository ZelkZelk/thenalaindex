<?php

App::import('Model', 'Target');
App::import('Model', 'CrawlerLog');
App::import('Model', 'Notification');
App::import('Model', 'Url');
App::import('Model', 'MetaDataFile');
App::import('Model', 'DataFile');
App::import('Model', 'HtmldocLink');

App::uses('ComponentCollection', 'Controller');
App::uses('HttpClientComponent', 'Controller/Component');
App::uses('QueueComponent', 'Controller/Component');
App::uses('RobotsTxtComponent', 'Controller/Component');
App::uses('ScrapperComponent', 'Controller/Component');
App::uses('UrlNormalizerComponent', 'Controller/Component');
App::uses('LinkAnalyzerComponent', 'Controller/Component');

/* Este componente se encarga de la exploracion del Target especifico */

class CrawlerComponent extends Component{
    public function __call($method, $args){
        if(is_callable(array($this, $method))) {
            return call_user_func_array($this->$method, $args);
        }
    }       
    
    /* El Target a ser Crawleado. */
    
    private $Target;
    
    /* La funcion para loggin */
    
    private $logFunction;
    
    /* El Crawler Log */
    
    private $CrawlerLog;
    
    /* Modelo para las Notificaciones */
    
    private $Notification;
    
    /* Tiempo en milisegundos de espera entre conexiones HTTP */
    
    private $httpWait = 0;
    
    /* Cantidad de fallos tolerables antes de desencolar la URL con error */
    
    private $failureLimit;
    
    /* Funcion que utiliza la funcion de loggin */
    
    private function logcat($message){
        $this->logFunction($message);
        $this->CrawlerLog->appendTrace($message);
    }
    
    /* Llamar al inicializador antes de empezar el proceso */
    
    public function init(Target $target,$logFunction) {  
        $this->logFunction = $logFunction;
        $this->Target = $target;
        $this->CrawlerLog = new CrawlerLog();
        $this->Notification = new Notification();
        $this->MetaDataFile = new MetaDataFile();
        $this->DataFile = new DataFile();
        
        Configure::load('crawler');
        $this->httpWait = Configure::read('Crawler.http_wait');
        $this->failureLimit = Configure::read('Crawler.failure_limit');
        ini_set('memory_limit',Configure::read('Crawler.max_memory'));
        register_shutdown_function('deadpitbull');
    }
    
    /* Punto de partida para el proceso de crawling.
     * 
     * Ejecuta 3 grandes funciones:
     * 
     *      * Start : setea el Target con las fecha indicadas, notifica
     *                  a la lista START via email, setea un CrawlerLog
     *                  vacio y listo para empezar el proceso de recoleccion.
     *      * Crawl : explora la URL principal del sitio, recolectar los LINKS
     *                  que encuentre tanto de paginas, como imagenes, css y
     *                  scripts. Colocandolos en la cola de exploracion.
     *                  Luego continua con este proceso hasta que la cola de 
     *                      exploracion se vacie, el proceso se encarga de 
     *                      evitar duplicados.
     *      * Finish : termina con el proceso, notificando la finalizacion a la
     *                  lista DONE via email.
     */
    
    public function main(){
        $target_id = $this->Target->id;
        
        try{
            $this->logcat("START@$target_id");
            $this->start();
            
            $this->logcat("CRAWL@$target_id");
            $this->crawl();
            
            $this->logcat("FINISH@$target_id");
            $this->finish();
        }
        catch(Exception $e){
            $this->trace($e);        
            $this->logcat("FAILED@$target_id");
            $this->failed();    
        }
    }
    
    /* Establece el Crawler Log como fallido y realiza las notificaciones
     * a la lista FAIL */
    
    private function failed(){
        $this->CrawlerLog->failed();
        $this->Notification->fail($this->CrawlerLog);
    }
    
    /* Manda al log la excepcion y su traza */
    
    private function trace(Exception $e){        
        $this->logcat("EXCEPTION:{$e->getMessage()}");
        
        foreach($e->getTrace() as $trace){
            $dump = $trace['file']
                    . ':'
                    . $trace['line'];
            
            $this->logcat("EXCEPTION-TRACE:{$dump}");            
        }
    }
    
    /* COMIENZO.
     * Establece las fecha del Target last_crawl y first_crawl.
     * Crea un CrawlLog vacio listo para su uso por el proceso.
     * Envia por email la notificacion del evento START.
     **/
    
    private function start(){
        if( ! $this->CrawlerLog->createEmpty($this->Target)){
            throw new Exception('CrawlerLog::createEmpty() = false');
        }
        
        if( ! $this->Target->setCrawling()){
            throw new Exception('Target::setCrawling() = false');
        }
        
        $this->Notification->start($this->CrawlerLog);
    }
    
    /* PROCESO
     * 
     * Notese que las conexiones son logueadas en un archivo de log diferente:
     * "http-" . strtolower(Target::$name)
     * 
     * Notese que el encolado es logueado en un archivo de log diferente:
     * "queue-" . strtolower(Target::$name)
     * 
     *      1. Inicia el log para el cliente HTTP
     *      2. Inicia el cliente HTTP
     *      3. Inicia el log para el Encolador de URLs
     *      4. Inicia el Encolador de URLs
     *      5. Inicia el log para el Robot Parser
     *      6. Incia el Robot Parser
     *      7. Incia el log para el Scrapper
     *      8. Incia el HTML Scrapper
     *      9. Inicia el log para el Url Normalizer
     *      10. Inicia el Url Normalizer
     *      11. Inicia el log para el Analizador de Vinculos
     *      12. Inicia el Analizador de Vinculos
     *      13. Encola la URL raiz
     *      14. Parsea el robots.txt de la URL raiz
     *      15. Comienza a procesar la cola de exploracion
     *      16. Realiza el escaneo y analisis de hipervinculos en documentos HTML
     * */
    
    private function crawl(){
        $this->initHttpLog();
        $this->initHttp();
        $this->initQueueLog();
        $this->initQueue();
        $this->initRobotsLog();
        $this->initRobots();
        $this->initScrapperLog();
        $this->initScrapper();
        $this->initUrlNormalizerLog();
        $this->initUrlNormalizer();
        $this->initLinkAnalyzerLog();
        $this->initLinkAnalyzer();
        $this->enqueueRoot();
        $this->fetchRootRobots();
        $this->processQueue();
        $this->linkAnalysis();
    }
    
    /* Realiza el analisis de hipervinculos de cada documento HTML almacenado */
    
    private function linkAnalysis(){    
        $id = $this->CrawlerLog->id;
        $this->LinkAnalyzer->scanner($id);
    }
    
    /* Incia el log del componente LinkAnalyzer, cada Target tiene su archivo separado
     * de log.
     */
    
    private function initLinkAnalyzerLog(){
        $name = $this->Target->Data()->read('name');
        $file = 'link-' . strtolower($name);
        
        CakeLog::config($file, array(
            'engine' => 'FileLog',
            'types' => [ 'info' ],
            'scopes' => [ 'link' ],
            'file' => $file
        ));    
    }
    
    /* Inicia el componente LinkAnalyzer con sus valores por defecto y los necesarios
     * para el escaneo de urls y reemplazo de atributos en documentos HTML.
     */
    
    private $LinkAnalyzer;
    
    private function initLinkAnalyzer(){        
        $collection = new ComponentCollection();
        $this->LinkAnalyzer = new LinkAnalyzerComponent($collection);
        
        $this->LinkAnalyzer->init($this->CrawlerLog,function($message){
            CakeLog::write('info', $message, 'link');
        });        
    }
        
    /* Descarga y parsea las reglas de robots.txt */
    
    private function fetchRootRobots(){
        $url = $this->Normalizer->getNormalizedUrl();
        $this->Robots->parse($url);
    }
    
    /* Incia el log del componente Normalizer, cada Target tiene su archivo separado
     * de log.
     */
    
    private function initUrlNormalizerLog(){
        $name = $this->Target->Data()->read('name');
        $file = 'normalizer-' . strtolower($name);
        
        CakeLog::config($file, array(
            'engine' => 'FileLog',
            'types' => [ 'info' ],
            'scopes' => [ 'normalizer' ],
            'file' => $file
        ));
    }
    
    /* Inicia el componente Normalizer con sus valores por defecto y los necesarios
     * para comenzar la exploracion.
     */
    
    private $Normalizer;
    
    private function initUrlNormalizer(){        
        $collection = new ComponentCollection();
        $this->Normalizer = new UrlNormalizerComponent($collection);
        $url = $this->Target->Data()->read('url');
        
        $this->Normalizer->init($url,$this->CrawlerLog,function($message){
            CakeLog::write('info', $message, 'normalizer');
        });        
    }
    
    /* Incia el log del componente Queue, cada Target tiene su archivo separado
     * de log.
     */
    
    private function initRobotsLog(){
        $name = $this->Target->Data()->read('name');
        $file = 'robots-' . strtolower($name);
        
        CakeLog::config($file, array(
            'engine' => 'FileLog',
            'types' => [ 'info' ],
            'scopes' => [ 'robots' ],
            'file' => $file
        ));
    }
    
    /* Inicia el componente Robots con sus valores por defecto y los necesarios
     * para comenzar la exploracion.
     */
    
    private $Robots;
    
    private function initRobots(){        
        $collection = new ComponentCollection();
        $this->Robots = new RobotsTxtComponent($collection);
        
        $this->Robots->init($this->CrawlerLog,function($message){
            CakeLog::write('info', $message, 'robots');
        });        
    }
    
    /* Incia el log del componente Queue, cada Target tiene su archivo separado
     * de log.
     */
    
    private function initQueueLog(){
        $name = $this->Target->Data()->read('name');
        $file = 'queue-' . strtolower($name);
        
        CakeLog::config($file, array(
            'engine' => 'FileLog',
            'types' => [ 'info' ],
            'scopes' => [ 'queue' ],
            'file' => $file
        ));
    }
    
    /* Inicia el componente Queue con sus valores por defecto y los necesarios
     * para comenzar la exploracion.
     */
    
    private $Queue;
    
    private function initQueue(){        
        $collection = new ComponentCollection();
        $this->Queue = new QueueComponent($collection);
        
        $this->Queue->init($this->CrawlerLog,function($message){
            CakeLog::write('info', $message, 'queue');
        });        
    }
    
    /* Encola la URL raiz del Target */
    
    private function enqueueRoot(){
        $root = $this->Target->Data()->read('url');        
        $this->Normalizer->normalize($root);
        
        if($this->Normalizer->isAllowed()){
            $url = $this->Normalizer->getNormalizedUrl();
            $this->Queue->push($url);
        }
    }
    
    /* Procesa la cola de exploracion.
     * 
     *      REGLAS DE ENCOLADO
     * 
     *      1.      Se asume que el crawler no encolara links <A> fuera del par 
     *              url:puerto raiz. ATENCION css/js e imagenes si seran encolados.
     *      2.      No se encolaran aquellos links <A> que tengan el attributo
     *              nofollow
     * 
     *      ALGORITMO
     * 
     *      1. Se cicla el Queue obteniendo siempre la cabeza.
     *      2. Se determina si la URL esta permitida por robots.txt
     *      3. Se realiza la conexion HTTP
     *          3.1 Si hay algun fallo se suma al contador de fallos
     *          3.2 Si es existosa la conexion, se procede a guardar en BD 
     *              la respuesta dependiendo de su MIME para determinar 
     *              el tipo de tratamiento.
     *          3.3 Si la respuesta es un HTML se encolan todas las urls de css,
     *              js, imagenes y links que puedan tener. Teniendo encuenta las
     *              REGLAS DE ENCOLADO
     *      
     * Limite de fallos HTTP 
     * Config@crawler:Crawler.failure_limit
     * 
     * Tiempo de Espera entre peticiones HTTP (en milis)
     * Config@crawler:Crawler.http_wait
     * 
     **/
    
    private function processQueue(){
        $this->initMemoryLogs();
            
        while($url = $this->Queue->fetch()){ 
            $this->memoryProfiler('HEAD');
            
            if($this->Robots->isAllowed($url) === false){
                $this->onRobotsDisallowed($url);
                continue;
            }
            
            $this->sleepCrawl();
            $this->Http->get($url);
            $this->CrawlerLog->increment('http_petitions');
            $this->memoryProfiler('GET');
            
            if($this->Http->isAcceptable() === false){
                $this->CrawlerLog->increment('http_errors');
                $this->onHttpInacceptable($url);
            }
            else{
                $this->onHttpAcceptable($url);
            }
            
            $this->CrawlerLog->store();
            $this->memoryProfiler('TAIL');
        } 
        
        $this->memorySummary();
    }
    
    /* Con esta funcion reportamos el uso pico de memoria*/
    
    private function memorySummary(){
        $enabled = Configure::read('Crawler.memory_profiler');
        
        if($enabled){
            $emalloc = memory_get_peak_usage();
            $real = memory_get_peak_usage(true);            
            $this->memoryLog("<PEAK:EMALLOC={$emalloc},PEAK:REAL={$real}>");
        }
    }
    
    /* Con esta funcion reportamos el uso de memoria */
    
    private function memoryProfiler($tag){
        $enabled = Configure::read('Crawler.memory_profiler');
        
        if($enabled){
            $emalloc = memory_get_usage();
            $real = memory_get_usage(true);            
            $this->memoryLog("[$tag] <EMALLOC={$emalloc},REAL={$real}>");
        }
    }
    
    /* Envia una entrada al log de memoria */
    
    private static $MEMTAG = '[MEM]';
    
    private function memoryLog($message){
        $full = self::$MEMTAG . ' ' . $message;
        CakeLog::info($full, [ 'mem' ]);
    }
    
    /* Incia el log del Memory Profiler, cada Target tiene su propio log
     */
    
    private function initMemoryLogs(){
        $enabled = Configure::read('Crawler.memory_profiler');
        
        if($enabled){
            $name = $this->Target->Data()->read('name');
            $file = 'memory-' . strtolower($name);

            CakeLog::config($file, array(
                'engine' => 'FileLog',
                'types' => [ 'info' ],
                'scopes' => [ 'mem' ],
                'file' => $file
            ));
        }
    }
    
    /* Se ejecuta cuando la respuesta HTTP es completa y valida.
     * Desencola la url con status de exito. 
     * 
     *      1. Crea el URL en la BD para la url actual. Si ya existe lo utiliza.
     *      2. Analiza los Meta Datos y crea un nuevo registro
     *      3. Almacena el Archivo de Datos relacionado al Meta Dato
     *      4. Realiza el analisis del arbol DOM en busca de mas urls para encolar
     *      5. Establece la url como explorada satisfactoriamente, la saca de la
     *          cola. Si hubo algun redirect por parte del server dicha URL tambien
     *          debe establecerse como explorada.
     *      6. Actualiza el root hash de la exploracion, el root hash es el primer
     *          hash creado.
     **/
    
    private $Url = null;
    
    private function onHttpAcceptable($url){        
        $this->loadReferer();
        $this->createUrl();
        $this->createMetaData();
        $this->createDataFile();
        $this->createHtmldocLink();
        $this->setRootHash();
        
        if($this->Referer !== $url){
            $this->logcat("MOVED URL:<{$url},{$this->Referer}");
            $this->Queue->push($this->Referer);      
            $this->Queue->done($this->Referer);        
        }
        
        $last = substr($url,-1);
        
        if($last == '/'){
            $count = strlen($url);
            $slashless = substr($url,0,$count - 1);
            $this->logcat("TRAILLING SLASH URL:<{$url},{$slashless}");
            $this->Queue->push($slashless);      
            $this->Queue->done($slashless);        
        }
        
        $this->Queue->done($url);        
    }
    
    /**
     * Actualiza el root hash de la exploracion actual, el root hash siempre
     * sera el primer hash creado.
     */
    
    private $rootHashed = false;
    
    private function setRootHash(){
        if($this->rootHashed){
            return;
        }
        
        $this->rootHashed = true;
        $hash = $this->MetaDataFile->getHash();
        
        if($this->CrawlerLog->setRootHash($hash) === false){
            throw new Exception('CrawlerLog::setRootHash() === false');
        }
    }
    
    /* Carga la URL referer */
    
    private $Referer;
    
    private function loadReferer(){
        $effectiveUrl = $this->Http->getEffectiveUrl();
        $this->Normalizer->normalize($effectiveUrl);
        $this->Referer = $this->Normalizer->getNormalizedUrl();
    }
    
    /* Crea el HtmldocLink de la URL explorada. Almacena tanto en BD como
     * en la Cola de exploracion las URLs encontradas por el Scrapper.
     * 
     * Solo tiene sentido cuando el documento adjunto es
     * de tipo text/html. Si lo es realiza los siguientes procesos:
     *  
     *          1. Incializa el HtmldocLink para esta exploracion
     *          2. Inicializa el Scrapper
     *          3. Extrae las URLs
     *          4. Encolas las URLs
     * 
     * NOTA: a partir de este momento el Scrapper se recicla, al igual que  los
     * componentes con data pesada se reciclan.
     * 
     * NOTA: los demas analisis se realizan en otro proceso debido a la complejidad
     * de los mismos.
     * 
     * [FREE]
     */
    
    private $HtmldocLink;
    
    private function createHtmldocLink(){        
        if($this->MetaDataFile->isHtml()){
            $this->HtmldocLink = new HtmldocLink();
            $this->HtmldocLink->createDoclink($this->MetaDataFile);
            
            $this->scrapUrls();
            $this->enqueueUrls();
            $this->Scrapper->clear();
        }
    }
    
    /* Incia el log del componente Scrapper, cada Target tiene su archivo separado
     * de log.
     */
    
    private function initScrapperLog(){
        $name = $this->Target->Data()->read('name');
        $file = 'scrapper-' . strtolower($name);
        
        CakeLog::config($file, array(
            'engine' => 'FileLog',
            'types' => [ 'info' ],
            'scopes' => [ 'scrapper' ],
            'file' => $file
        ));
    }
    
    /* Inicia el Scrapper enviando el DataFile para obtener el documento HTML
     * a analizar.
     */
    
    private $Scrapper;
    
    private function initScrapper(){
        $collection = new ComponentCollection();
        $this->Scrapper = new ScrapperComponent($collection);
        $this->Scrapper->init($this->CrawlerLog,function($message){
            CakeLog::write('info', $message, 'scrapper');
        });
    }
    
    /* Realiza el analisis de urls dentro del documento html para enviar a la 
     * cola de exploracion.
     * 
     * NOTA: a partir de este punto DataFile se recicla.
     * 
     * [FREE]
     */
    
    private function scrapUrls(){
        $file = $this->DataFile->getFile();
        $this->Scrapper->scrapUrls($file);
        $this->DataFile->clearFields();
    }
    
    /* Encola las Urls recolectadas en la cola de exploracion.
     * No todas las Urls deben pushearse, ya que si por algun motivo alguna
     * URL sale del dominio que estamos explorando tendriamos que explorar toda la
     * internet.
     * 
     * Para ellos se consulta al Componente Normalizer para ver si la URL es 
     * realmente suceptible de ser Scrapeada.
     */
    
    private function enqueueUrls(){
        $this->enqueueAssets();
        $this->enqueueHyperlinks();
    }
    
    /* Encola las URL de hipervinculos encontrados, los hipervinculos reciben
     * un tratamiento especial por el normalizador, con el objetivo de no 
     * crawlear toda la internet y hacer que el proceso sea practicamente infinito,
     * solo se permiten hipervinculos dentro del mismo dominio.
     */
    
    private function enqueueHyperlinks(){
        $links = $this->Scrapper->getLinks();
        $referer = $this->Referer;
        
        foreach($links as $link){
            $this->Normalizer->normalize($link, $referer);
            
            if($this->Normalizer->isAllowed()){
                $url = $this->Normalizer->getNormalizedUrl();
                $this->Queue->push($url);
            }
        }        
    }
    
    /* Encola los assets encontrados (imagens, estilos, scripts, etc), estas URLs
     * a diferencia de los hipervinculos son encolados directamente pues es muy
     * comun usar un CDN externo, ademas al no contener HTML, no se vuelven a 
     * Scrapear.
     */
    
    private function enqueueAssets(){
        $assets = $this->Scrapper->getAssets();
        $referer = $this->Referer;
                
        foreach($assets as $asset){
            $this->Normalizer->normalize($asset,$referer);
            $url = $this->Normalizer->getNormalizedUrl();
            $this->Queue->push($url);
        }        
    }
    
    /* Incia el Escaneo de Meta Datos, extraidos de las cabeceras de la conexion
     * HTTP realizada, y la posterior creacion del registro en BD */
    
    private function createMetaData(){
        $headerData = $this->MetaDataFile->headerAnalysis($this->Url,$this->Http);
        $this->MetaDataFile->createMetaData($this->CrawlerLog,$headerData);
                
        if($this->MetaDataFile->isHtml()){
            $this->CrawlerLog->increment('html_crawled');
        }
        else if($this->MetaDataFile->isImage()){
            $this->CrawlerLog->increment('img_crawled');            
        }
        else if($this->MetaDataFile->isStylesheet()){
            $this->CrawlerLog->increment('css_crawled');            
        }
        else if($this->MetaDataFile->isScript()){
            $this->CrawlerLog->increment('js_crawled');            
        }
    }
    
    /* Almacena en BD el cuerpo de la conexion HTTP realzida relacionandolo
     * al Meta Data creado con anterioridad. Ademas actualiza el MetaDataFile
     * para crear su Checksum real! 
     * 
     * NOTA: A partir de este punto Http se recicla
     * 
     * [FREE]
     */
    
    private function createDataFile(){
        $this->DataFile->createData($this->MetaDataFile,$this->Http);  
        $this->MetaDataFile->bindChecksum($this->DataFile);
        $this->Http->clear();   
    }
    
    /* Inicia el modelo Url de forma lazy, luego intenta crear la url pasada 
     * como parametro. */
    
    private function createUrl(){
        if(is_null($this->Url)){
            $this->Url = new Url();
        }
        
        $this->Url->alloc($this->Target,$this->Referer);
    }
    
    /* Se echa a dormir si el tiempo configurado (en milisegundos) es mayor a 0 */
    
    private $firstSleep = false;
    
    private function sleepCrawl(){
        if($this->firstSleep){
            $ms = $this->httpWait;

            if($ms > 0){
                usleep($ms * 1000); // usleep() utiliza microsegundos
            }
        }
        else{
            $this->firstSleep = true;
        }
    }
    
    /* Se ejecutacuando Robots no permite explorar la url.
     * Tipicamente desencola la url con status de robots disallow. */
    
    private function onRobotsDisallowed($url){
        $this->Queue->robotsDisallow($url);
    }
    
    /* Se ejecutacuando la respuesta HTTP esta incompleta o es invalida.
     * Se incrementa el contador de fallos de la url y se compara si ya llego
     * al limite de fallos, en tal caso se desencola con status de fallo. */
    
    private function onHttpInacceptable($url){
        $counter = $this->Queue->failureIncrement($url);
        $this->Http->clear();   
        
        if($counter >= $this->failureLimit){
            $this->Queue->fail($url);
        }
    }
    
    /* Incia el log del componente HTTP, cada Target tiene su archivo separado
     * de log.
     */
    
    private function initHttpLog(){
        $name = $this->Target->Data()->read('name');
        $file = 'http-' . strtolower($name);
        
        CakeLog::config($file, array(
            'engine' => 'FileLog',
            'types' => [ 'info' ],
            'scopes' => [ 'http' ],
            'file' => $file
        ));
    }
    
    /* Inicia el componente HTTP con sus valores por defecto y los necesarios
     * para el comienzo de las conexiones.
     */
    
    private $Http;
    
    private function initHttp(){        
        $collection = new ComponentCollection();
        $this->Http = new HttpClientComponent($collection);
        
        $this->Http->init($this->CrawlerLog,function($message){
            CakeLog::write('info', $message, 'http');
        });        
    }
    
    /* FINALIZACION 
     * Setea el CrawerLog como finalizado.
     * Envia por email la notificacion del evento DONE.
     */
    
    private function finish(){      
        if( ! $this->CrawlerLog->finished()){
            throw new Exception('CrawlerLog::finished() = false');
        }
        
        $this->Notification->done($this->CrawlerLog);
    }
}

function deadpitbull() {
    $tmp = '/tmp/nalacrash-' . time() . '.txt';
    
    $message  = [
        'error' => error_get_last(),
        'conf' => ini_get_all()
    ];
    
    file_put_contents($tmp, json_encode($message));
}