<?php

App::import('Model','DataFile');
App::uses('LinkAnalyzerComponent', 'Controller/Component');
App::uses('CrawlerUtilityComponent', 'Controller/Component');

/* Este componente se encarga del analisis de los documentos html para 
 * segregar adecuadamente la inforamcion necesaria de acuerdo a los diversos
 * casos de uso a implementarse
 **/

class ScrapperComponent extends CrawlerUtilityComponent{
    
    /* Objeto de clase DOMDocument, utilidad para realizar queries tipo CSSPATH
     * en el Dom del Documento HTML a procesar. */
    
    private $Dom;
    
    /* Llamar al inicializador antes de empezar el proceso */
    
    public function init(CrawlerLog $log,$logFunction) {  
        $this->setLogFunction($logFunction);
        $this->setCrawlerLog($log);
    }    
    
    /* Funcion que llama a logcat, util para agregar info extra a todos los 
     * mensajes.
     */
    
    private static $TAG = '[SCRAPPER]';
    
    private function logScrapper($message){
        $full = self::$TAG . ' ' . $message;
        $this->logInfo($full);
    }
    
    /* URL Scrapper.
     * 
     * Esta funcion busca urls en los siguientes TAG:
     * 
     *      * A HREF=$url
     *      * LINK HREF=$url
     *      * SCRIPT SRC=$url
     *      * IMG SRC=$url
     * 
     * Extraccion y almacenamiento
     *      * A = $links
     *      * link = $stylesheets
     *      * script = $scripts
     *      *.img = $images
     */
    
    private $links = [];
    private $linkNodes = [];
    
    public function getLinks(){
        return $this->links;
    }
    
    private $stylesheets = [];
    private $stylesheetNodes = [];
    
    public function getStylesheets(){
        return $this->stylesheets;
    }
    
    private $scripts = [];
    private $scriptNodes = [];
    
    public function getScripts(){
        return $this->scripts;
    }
    
    private $images = [];
    private $imageNodes = [];
    
    public function getImages(){
        return $this->images;
    }
    
    /* Obtiene las URLS usando CSSPATH queries */
    
    public function scrapUrls($textHtml){
        libxml_use_internal_errors(true);
        $this->Dom = new DOMDocument();
        
        if(@$this->Dom->loadHTML($textHtml)){        
            $this->scrapLinks();   
            $this->scrapStylesheets();   
            $this->scrapScripts();   
            $this->scrapImages();
        }
        else{
            $this->logInfo('Invalid HTML');
        }
        
        libxml_use_internal_errors(false);
        libxml_use_internal_errors(true);
    }
    
    /* 
     * Obtiene todas las URL de stylesheets
     */
    
    private function scrapStylesheets(){
        $this->scrapLinkTags();
        $this->scrapStyleTags();
    }
    
    /* Obtiene los documentos de los tags <STYLE>
     * 
     * Se extraen las sentencias @import url($STYLESHEET);
     * 
     **/
    
    private static $STYLE_INCLUDE_REGEX = '/\@import\s+url\((\"|\')(.*)(\"|\')\)\;*\s*(\/\*(.*?)\*\/)?/';
    public static $URL_SEPARATOR = '@@@$$$@@@';
    
    private function scrapStyleTags(){
        $stylesheets = $this->Dom->getElementsByTagName('style');
        
        foreach($stylesheets as $stylesheet){
            $content = $stylesheet->textContent;
            $regex = self::$STYLE_INCLUDE_REGEX;
            $matches = [];
            
            preg_match_all($regex, $content, $matches);    
            
            $urls = isset($matches[2]) ? $matches[2] : [];
            $nalaProtocol = isset($matches[5]) ? $matches[5] : [];
            
            foreach($nalaProtocol as $i => $protocol){
                $urlProto = $this->extractUrlProtocol($protocol);
                
                if($urlProto){
                    $urls[$i] .= self::$URL_SEPARATOR . $urlProto;
                }
            }
            
            foreach($urls as $url){
                $this->stylesheets[] = $url;
                $this->stylesheetNodes[] = $stylesheet;
            }
        }        
    }
    
    /* Extrae la URL del procotolo Nala */
    
    private static $PROTOCOL_URL_REGEX = '/%s=(.*?)\s/';

    private function extractUrlProtocol($protocol){
        $regex = sprintf(self::$PROTOCOL_URL_REGEX, LinkAnalyzerComponent::$DATA_NALA_SOURCE);
        $matches = [];
        $url = false;
        
        preg_match($regex, $protocol, $matches);
        
        if(isset($matches[1])){
            $url = trim($matches[1]);
        }
        
        if($url === ''){
            $url = false;
        }
        
        return $url;
    }
    
    /* Obtiene los documentos de los tags <LINK> */
    
    private function scrapLinkTags(){
        $stylesheets = $this->Dom->getElementsByTagName('link');
        
        foreach($stylesheets as $stylesheet){
            $valid = $this->isStylesheet($stylesheet);
            
            if($valid === false){
                continue;
            }
            
            $url = $this->extractDomUrl($stylesheet,'href');
            $acceptable = $url !== false;     
                
            if($acceptable){
                $this->stylesheets[] = $url;
                $this->stylesheetNodes[] = $stylesheet;
            }
        }        
    }
    
    /* Determina si un <LINK> es un stylesshet para eso revisa el atributo
     * rel, el cual debe ser stylesheet.
     */
    
    private static $REL_STYLESHEET = 'stylesheet';
    private static $REL = 'rel';
    
    private function isStylesheet($stylesheet){
        $response = false;

        foreach($stylesheet->attributes as $attr){
            $isRel = $attr->nodeName === self::$REL; 
            
            if($isRel){
                $value = $attr->nodeValue;
                $response = $value === self::$REL_STYLESHEET;
                break;
            }
        }

        return $response;
    }
    
    /* 
     * Obtiene todas las URL de los Scripts
     */
    
    private function scrapScripts(){
        $scripts = $this->Dom->getElementsByTagName('script');
        
        foreach($scripts as $script){
            $url = $this->extractDomUrl($script,'src');
            $acceptable = $url !== false;            
                
            if($acceptable){
                $this->scripts[] = $url;
                $this->scriptNodes[] = $script;
            }
        }        
    }
    
    /* 
     * Obtiene todas las URL de imagenes
     */
    
    private function scrapImages(){
        $imgs = $this->Dom->getElementsByTagName('img');
        
        foreach($imgs as $img){
            $url = $this->extractDomUrl($img,'src');
            $acceptable = $url !== false;
            
            if($acceptable){
                $this->images[] = $url;
                $this->imageNodes[] = $img;
            }
        }
    }
    
    /* Obtiene todos los hipervinuclos, chequea que no posee el atributo
     * rel="nofollow"
     */
    
    private function scrapLinks(){
        $links = $this->Dom->getElementsByTagName('a');
        
        foreach($links as $link){
            $url = $this->extractDomUrl($link,'href');
            $acceptable = $url !== false;
                
            if($acceptable){
                $this->links[] = $url;
                $this->linkNodes[] = $link;
            }
        }
    } 
    
    /* Extra la URL del nodo especificado, tiene como mayor prioridad si existe
     * el data-nalasource
     */
    
    private function extractDomUrl($link,$from){
        $url = false;

        foreach($link->attributes as $attr){
            if($attr->nodeName === 'rel' && $attr->nodeValue === 'nofollow'){
                return false;
            }

            $isNala = $attr->nodeName === LinkAnalyzerComponent::$DATA_NALA_SOURCE; 
            $isFrom = $attr->nodeName === $from; 
            $extract = $isNala || $isFrom;
            
            if($extract){
                $url = $this->extractUrl($attr);
            } 
            
            if($this->isHyperlink($link)){
                $url = $this->isValidUrl($url) ? $url : false;
            }
            
            if($isNala && $url !== false){
                break;
            }
        }

        return $url;
    }
    
    /* Determina si el Nodo es un hipervinculo <A> */
    
    private function isHyperlink($node){
        $response = false;
        
        if($node->tagName === 'a'){
            $response = true;
        }
        
        return $response;
    }
    
    /* Extrae la URL del atributo, hace algunas validaciones */
    
    private function extractUrl(DomAttr $attr){
        $url = $attr->nodeValue;
        $response = $url;
        return $response;
    }
    
    /* Compueba que la URL es valida */
    
    private function isValidUrl($url){
        $response = true;
        
        if(preg_match('/^javascript:.*$/i', $url)){
            $this->logScrapper("INVALID<$url,javascript>");
            return false;
        }
        
        if(preg_match('/^mailto:.*$/i', $url)){
            $this->logScrapper("INVALID<$url,mailto>");
            return false;
        }
                
        if(preg_match('/^#.*$/i', $url)){
            $this->logScrapper("INVALID<$url,#>");
            return false;
        }
        
        return $response;
    }
    
    /* Libera la memoria */
    
    public function clear(){
        $this->links = [];
        $this->linkNodes = [];
        $this->images = [];
        $this->imageNodes = [];
        $this->stylesheets = [];
        $this->stylesheetNodes = [];
        $this->scripts = [];
        $this->scriptNodes = [];
        $this->Dom = null;
        $this->h1 = null;
        $this->title = null;
        $this->text = null;
    }
    
    /* Obtiene todos los links que no son de hipervinculos <A> */
    
    public function getAssets(){
        $urls = [];
        $urls += $this->getStylesheets();
        $urls += $this->getScripts();
        $urls += $this->getImages();
        
        return $urls;
    }
    
    function getLinkNodes() {
        return $this->linkNodes;
    }

    function getStylesheetNodes() {
        return $this->stylesheetNodes;
    }

    function getScriptNodes() {
        return $this->scriptNodes;
    }

    function getImageNodes() {
        return $this->imageNodes;
    }

    /* Obtiene el HTML RAW del objeto DOM */
    
    public function getHtml(){
        return $this->Dom->saveHTML();
    }
    
    /**
     * Realiza el scrapping de texto completo.
     * 
     *      * Carga el HTML en memoria
     *      * Scrap del H1
     *      * Scrap del Titulo
     *      * Scrap del Texto
     */
    
    private $h1 = null;
    private $title = null;
    private $text = null;
    
    public function scrapFullText($textHtml){
        libxml_use_internal_errors(true);
        $this->Dom = new DOMDocument();
        
        if(@$this->Dom->loadHTML($textHtml)){        
            $this->scrapH1();   
            $this->scrapTitle();   
            $this->scrapText();   
        }
        else{
            $this->logInfo('Invalid HTML');
        }
        
        libxml_use_internal_errors(false);
        libxml_use_internal_errors(true);
    }
    
    private function scrapH1(){
        $h1 = $this->Dom->getElementsByTagName('h1');
        $h1node = $h1->item(0);
        
        if(is_null($h1node) === true){
            return;
        }
        
        $this->h1 = $h1node->textContent;
    }
    
    private function scrapTitle(){
        $title = $this->Dom->getElementsByTagName('title');
        $titleNode = $title->item(0);
        
        if(is_null($titleNode) === true){
            return;
        }
        
        $this->title = $titleNode->textContent;
        
    }
    
    private function scrapText(){
        
    }
    
    function getH1() {
        return $this->h1;
    }

    function getTitle() {
        return $this->title;
    }

    function getText() {
        return $this->text;
    }
}