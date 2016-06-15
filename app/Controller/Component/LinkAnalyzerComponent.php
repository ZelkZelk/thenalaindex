<?php

App::uses('CrawlerUtilityComponent', 'Controller/Component');
App::uses('ScrapperComponent', 'Controller/Component');
App::uses('UrlNormalizerComponent', 'Controller/Component');
App::uses('ComponentCollection', 'Controller');

App::import('Model', 'MetaDataFile');
App::import('Model', 'DataFile');
App::import('Model', 'DataFile');
App::import('Model', 'HtmldocLink');
App::import('Model', 'Url');
App::import('Model', 'Target');

/* Este componente se encarga de recolectar los hashes de cada link dentro de
 * cada documento HTML crawleado, con el proposito de construir su array de hashes
 * ademas de agregar los atributos data-nalaid="$hash" y modificar el contenido
 * de las URLs para llamar al WebService que proporcione los documentos 
 * almacenados.
 **/

class LinkAnalyzerComponent extends CrawlerUtilityComponent{
    
    /* Listado de Hashes unicos encontrados */
    
    private $Hashes = [];
    
    public function getHashes(){
        return $this->Hashes;
    }
    
    public function addHash($hash){
        if(in_array($hash, $this->Hashes) === false){
            $this->Hashes[] = $hash;
        }
    }
    
    /* Modelos a Utilizar. */
    
    private $MetaDataFile;
    private $DataFile;
    private $HtmldocLink;
    private $CrawlerLog;
    private $Url;
    private $Target;
    
    /* Necesitamos nuevamente al Scrapper para esta vez hacer el reemplazo de URLs */
    
    private $Scrapper;
    
    /* Necesitamos nuevamente el Normalizer para buscar en BD la URL normalizada */
    
    private $Normalizer;
    
    /* Override de la funcion generica para agregar el TAG NORMALIZER
     */
    
    private static $TAG = '[LINK]';
    
    private function logAnalyzer($message){
        $full = self::$TAG . ' ' . $message;
        $this->logInfo($full);
    }
    
    /* Llamar al inicializador antes de empezar el proceso */
    
    public function init(CrawlerLog $log,$logFunction) {  
        $this->setLogFunction($logFunction);
        $this->setCrawlerLog($log);
        
        $this->MetaDataFile = new MetaDataFile();
        $this->DataFile = new DataFile();
        $this->HtmldocLink = new HtmldocLink();
        $this->CrawlerLog = new CrawlerLog();
        $this->Url = new Url();
        $this->Target = new Target();
    }   
    
    /* Carga el modelo Target desde el CrawlerLog relacionado*/

    public function loadTarget() {
        $target_id = $this->CrawlerLog->Data()->read('target_id');
        
        if( ! $this->Target->loadFromId($target_id)){
            $this->logAnalyzer("TARGET<$target_id,NOT FOUND>");
        }
    }
    
    /* Inicializa el Normalizer */
    
    private function initNormalizer(){
        $collection = new ComponentCollection();
        $this->Normalizer = new UrlNormalizerComponent($collection);
        
        CakeLog::config('analyzer-normalizer', array(
            'engine' => 'FileLog',
            'types' => [ 'info' ],
            'scopes' => [ 'analyzer-normalizer' ],
            'file' => 'analyzer-normalizer'
        ));    
        
        $url = $this->Target->Data()->read('url');
        
        $this->Normalizer->init($url,$this->getCrawlerLog(),function($message){
            CakeLog::write('info', $message, 'analyzer-normalizer');
        });        
    }
    
    /* Inicializa el Scrapper */
    
    private function initScrapper(){
        $collection = new ComponentCollection();
        $this->Scrapper = new ScrapperComponent($collection);
        
        CakeLog::config('analyzer-scrapper', array(
            'engine' => 'FileLog',
            'types' => [ 'info' ],
            'scopes' => [ 'analyzer-scrapper' ],
            'file' => 'analyzer-scrapper'
        ));    
        
        $this->Scrapper->init($this->getCrawlerLog(),function($message){
            CakeLog::write('info', $message, 'analyzer-scrapper');
        });        
    }
    
    /* Se encarga de ciclar todos los metadatas en busca de documentos HTML,
     * luego scrapea el documento en busca de hipervinculos, imagenes, scripts,
     * u hojas de estilo, busca el hash correspondiente y hace reemplazo de atributos y
     * valores para alimentar el contenido desde el cdn.
     */
    
    public function scanner($id){
        if($this->CrawlerLog->loadFromId($id)){
            $this->scannerSetup();
            $this->pagedScanner($id);
        }
        else{
            $this->logAnalyzer("CRAWLER<$id,NOT FOUND>");
        }
    }
    
    /* Carga los modelos e inicializa los componentes requeridos para el analisis */
    
    private function scannerSetup(){
        Configure::load('analysis');
        $this->limit = Configure::read('Analysis.link_analyzer_page_limit');
        
        $this->loadTarget();
        $this->initScrapper();
        $this->initNormalizer();        
    }
    
    /* Realiza el Scan paginando la info para evitar ocupar mucha memoria */
    
    private $limit;
    
    private function pagedScanner($id){
        $alias = $this->MetaDataFile->alias;
        $limit = $this->limit;
        $offset = 0;
        
        do{
            $data = $this->MetaDataFile->getHtmldocCrawled($id, $limit, $offset);
            $count = count($data);
            $offset += $count;
            
            foreach($data as $metaData){
                $blob = $metaData[$alias];
                $this->MetaDataFile->loadArray($blob);
                $this->urlScan();
            }
            
        } while($count === $limit);
    }
    
    /* Scrapea todas las URLs del documento HTML, */
    
    private function urlScan(){
        $id = $this->MetaDataFile->id;
        
        if($this->MetaDataFile->isHtml()){
            if($this->loadDataFile() === false){
                $this->logAnalyzer("DATAFILE<$id,NOT FOUND>");
                return false;
            }
            
            $this->replaceLinks();
            $this->replaceStylesheets();
            $this->replaceScripts();
            $this->replaceImages();
            $this->updateFile();

            if($this->HtmldocLink->loadFromMeta($this->MetaDataFile) === false){
                $this->logAnalyzer("HTMLDOCLINK<$id,NOT FOUND>");
                return false;
            }
            else{
                $hashes = $this->getHashes();
                $this->HtmldocLink->updateHashes($hashes);
            }
            
            $this->Scrapper->clear();
            $this->flushHashes();
        }
        
        return true;
    }
    
    /* Se borran los hashes */
    
    public function flushHashes(){
        $this->Hashes = [];
    }
    
    /* Carga el archivo HTML en memoria */
    
    private function loadDataFile(){
        $id = $this->MetaDataFile->id;
        $response = true;
        
        if($this->loadReferer() === false){
            return false;
        }
        
        if($this->DataFile->loadFromMeta($id) === false){
            return false;
        }
        
        $file = $this->DataFile->getFile();
        $this->Scrapper->scrapUrls($file);
        return $response;
    }
    
    /* Carga el referer en memoria */
    
    private $Referer = null;
    
    private function loadReferer(){
        if(is_null($this->Referer)){
            $this->Referer = new Url();
        }
        
        $urlId = $this->MetaDataFile->Data()->read('url_id');
        $response = true;
        
        if($this->Referer->loadFromId($urlId) === false){
            return false;
        }
        
        return $response;
    }
        
    
    /* Actualiza el documento HTML con los reemplazos de URL crawleadas para ser
     * servidas via cdn.
     */
    
    private function updateFile(){        
        $newFile = $this->Scrapper->getHtml();
        $id = $this->DataFile->id;

        if($this->DataFile->updateFile($newFile) === false){
            $this->logAnalyzer("DATAFILE<$id,CANNOT SAVE>");            
        }
    }
    
    /* Reemplaza el atributo especificado por el valor del webservice seguido del 
     * hash del recurso. Ademas agrega los atributos
     * 
     *      *   data-nalaid="$hash"
     *      *   data-nalasource="$url"
     * 
     * Si la etiqueta es A (hipevinculo) ademas se agrega:
     * 
     *      * data-ishtml="true|false" , este atributo determina si el hipervinculo debe
     *          seguirse (contiene un doc html) o bien descargarse (otro tipo de documento).
     * 
     * URL del webservice feeder (el webservice que transforma hash en recurso almacenado)
     * Config@analysis:Analysis.cdn_webservice
     */
    
    private function replaceAttributes($nodes,$urls){
        foreach($nodes as $i => $node){
            $url = $urls[$i];
            $hash = $this->getUrlHash($url);
            $replace_url = $this->getReplaceUrl($url,$hash);
            
            if($replace_url === false){
                continue;
            }
            
            $this->replaceUrl($node,$replace_url,$url,$hash);
            $this->addHash($hash);
            
            if(strtolower($node->tagName) !== 'style'){
                $node->setAttribute(self::$DATA_NALA_ID,$hash);
                $node->setAttribute(self::$DATA_NALA_SOURCE,$url);
            }
            
            if(strtolower($node->tagName) === 'a'){
                $isHtml = $this->MetaDataFile->isHtml();
                $node->setAttribute(self::$DATA_IS_HTML,$isHtml);
            }
        }
    }
    
    public static $DATA_NALA_SOURCE = 'data-nalasource';
    public static $DATA_NALA_ID = 'data-nalaid';
    public static $DATA_IS_HTML = 'data-ishtml';
    
    /* Reemplaza la url del nodo dependiendo del TAG */
    
    private function replaceUrl($node,$replace_url,$raw_url,$hash){
        switch($node->tagName){
            case 'a':
                $this->replaceATag($node,$replace_url);
                break;
            case 'link':
                $this->replaceLinkTag($node,$replace_url);
                break;
            case 'style':
                $this->replaceStyleInclude($node,$replace_url,$raw_url,$hash);
                break;
            case 'script':
                $this->replaceScriptTag($node,$replace_url);
                break;
            case 'img':
                $this->replaceImgTag($node,$replace_url);
                break;
        }
    }
    
    /* Reemplaza el attributo SRC del elemento <IMG> con la URL especificada */
    
    private function replaceImgTag($node,$url){
        $node->setAttribute('src',$url);
    }
    
    /* Reemplaza el attributo SRC del elemento <SCRIPT> con la URL especificada */
    
    private function replaceScriptTag($node,$url){
        $node->setAttribute('src',$url);
    }
    
    /* Reemplaza las inclusion CSS de Stylesheets */
    
    private static $STYLE_INCLUDE_REGEX = '/\@import\s+url\((\"|\')(%s)(\"|\')\)\;*/';
    
    private function replaceStyleInclude($node,$url,$raw_url,$hash){
        $id = self::$DATA_NALA_ID;
        $source = self::$DATA_NALA_SOURCE;  
        
        if($this->hasNalaUrl($raw_url)){
            list($replace_url,$raw_url) = $this->nalaFragments($raw_url);
        }
        else{
            $replace_url = $raw_url;
        }
                       
        $regex = sprintf(self::$STYLE_INCLUDE_REGEX,  preg_quote($replace_url,'/'));
        $replacement = "@import url(\"{$url}\"); /* {$source}={$raw_url} {$id}={$hash} */"; 
        
        $text = $node->textContent;
        $replaceText = preg_replace($regex, $replacement, $text);
        $node->nodeValue = $replaceText;
    }
    
    /* Divide el nala protocol en CDN y RAW URLs */
    
    private function nalaFragments($protocol){
        return explode(ScrapperComponent::$URL_SEPARATOR, $protocol);
    }
    
    /* Determina si la URL es del CDN de Nala o es la Original*/
    
    private function hasNalaUrl($url){
        $response = false;
        
        if(strstr($url, ScrapperComponent::$URL_SEPARATOR)){
            $response = true;
        }
        
        return $response;
    }
    
    /* Reemplaza el attributo HREF del elemente <LINK> con la URL especificada */
    
    private function replaceLinkTag($node,$url){
        $node->setAttribute('href',$url);
    }
    
    /* Reemplaza el attributo HREF del elemente <A> con la URL especificada */
    
    private function replaceATag($node,$url){
        $node->setAttribute('href',$url);
    }
    
    /* Obtiene la URL de reemplazo, siempre es normalizada */
    
    private function getReplaceUrl($url,$hash = false){
        Configure::load('analysis');
        $cdn = Configure::read('Analysis.cdn_webservice');
        
        if($hash === false){
            $hash = $this->getUrlHash($url);
        }
                
        if($hash === false){
            return false;
        }
        
        $replace_url = $cdn . $hash;
        return $replace_url;
    }
    
    /* Obtiene el hash de la URL */
    
    private function getUrlHash($url){
        $finalUrl = $url;
        
        if($this->Test){
            return $this->getTestUrlHash();
        }
        
        if($this->hasNalaUrl($finalUrl)){
            $fragments = $this->nalaFragments($finalUrl);
            $finalUrl = $fragments[1];
        }
        
        $id = $this->CrawlerLog->id;
        $referer = $this->Referer->Data()->read('full_url');
        
        $this->Normalizer->normalize($finalUrl,$referer);
        $normalized = $this->Normalizer->getNormalizedUrl();            
        $url_id = $this->Url->getUrlId($normalized);

        if($url_id === false){
            $this->logAnalyzer("URL<$normalized,NOT FOUND>");
            return false;
        }

        $hash = $this->MetaDataFile->getUrlHash($id,$url_id);

        if($hash == false){
            $this->logAnalyzer("METADATA<$id,$url_id,NOT FOUND>");
            return false;
        }

        $this->logAnalyzer("METADATA<$hash,$normalized,GATHER>");
        
        return $hash;
    }
    
    private function getTestUrlHash(){
        return time() + rand(1,40300);
    }
    
    /* Hace reemplazo de los hipervinculos por la url configurada como feed en el
     * webservice, ademas agrega los atributos
     * 
     *      *   data-nalaid="$hash" 
     *      *   data-nalasource="$url" 
     */
    
    private function replaceLinks(){
        $urls = $this->Scrapper->getLinks();
        $nodes = $this->Scrapper->getLinkNodes();        
        $this->replaceAttributes($nodes, $urls);
    }

    /* Hace reemplazo de las urls de hojas de estilo asociadas a los tags <LINK>
     * o <STYLE>, ademas agrega los atributos
     * 
     *      *   data-nalaid="$hash" 
     *      *   data-nalasource="$url" 
     */
    
    private function replaceStylesheets(){
        $urls = $this->Scrapper->getStylesheets();
        $nodes = $this->Scrapper->getStylesheetNodes();        
        $this->replaceAttributes($nodes, $urls);
    }

    /* Hace reemplazo de las urls de scripts asociados a tags <SCRIPT>,
     * ademas agrega los atributos
     * 
     *      *   data-nalaid="$hash" 
     *      *   data-nalasource="$url" 
     */
    
    private function replaceScripts(){
        $urls = $this->Scrapper->getScripts();
        $nodes = $this->Scrapper->getScriptNodes();        
        $this->replaceAttributes($nodes, $urls);
    }

    /* Hace reemplazo de las urls de imagenes asociadas a los tags <IMG>,
     * ademas agrega los atributos
     * 
     *      *   data-nalaid="$hash" 
     *      *   data-nalasource="$url" 
     */
    
    private function replaceImages(){
        $urls = $this->Scrapper->getImages();
        $nodes = $this->Scrapper->getImageNodes();        
        $this->replaceAttributes($nodes, $urls);
    }
    
    /* Test sobre un archivo */
    
    private $Test = false;
    
    public function test($file){
        $this->initScrapper();
        $this->initNormalizer();
        
        $this->Test = true;
        $this->Scrapper->scrapUrls($file);
        $this->replaceLinks();
        $this->replaceStylesheets();
        $this->replaceScripts();
        $this->replaceImages();
        $newFile = $this->Scrapper->getHtml();
        echo $newFile;
    }
}