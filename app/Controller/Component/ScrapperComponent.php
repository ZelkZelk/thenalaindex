<?php

App::import('Model','DataFile');
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
    
    private static $STYLE_INCLUDE_REGEX = '/\@import\s+url\(\"(.*)\"\)\;*/';
    
    private function scrapStyleTags(){
        $stylesheets = $this->Dom->getElementsByTagName('style');
        
        foreach($stylesheets as $stylesheet){
            $content = $stylesheet->textContent;
            $regex = self::$STYLE_INCLUDE_REGEX;
            $matches = [];
            
            preg_match_all($regex, $content, $matches);            
            $urls = isset($matches[1]) ? $matches[1] : [];
            
            foreach($urls as $url){
                $this->stylesheets[] = $url;
                $this->stylesheetNodes[] = $stylesheet;
            }
        }        
    }
    
    /* Obtiene los documentos de los tags <LINK> */
    
    private function scrapLinkTags(){
        $stylesheets = $this->Dom->getElementsByTagName('link');
        
        foreach($stylesheets as $stylesheet){
            $acceptable = false;
            
            foreach($stylesheet->attributes as $attr){
                $isHref = $attr->nodeName === 'href';                
                $url = ($isHref) ? $this->extractUrl($attr) : false;
                
                if($url){
                    $acceptable = true;
                    break;
                }
            }
                
            if($acceptable){
                $this->stylesheets[] = $url;
                $this->stylesheetNodes[] = $stylesheet;
            }
        }        
    }
    
    /* 
     * Obtiene todas las URL de los Scripts
     */
    
    private function scrapScripts(){
        $scripts = $this->Dom->getElementsByTagName('script');
        
        foreach($scripts as $script){
            $acceptable = false;
            
            foreach($script->attributes as $attr){
                $isSrc = $attr->nodeName === 'src';                
                $url = ($isSrc) ? $this->extractUrl($attr) : false;
                
                if($url){
                    $acceptable = true;
                    break;
                }
            }
                
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
            $acceptable = false;
            
            foreach($img->attributes as $attr){
                $isSrc = $attr->nodeName === 'src';                
                $url = ($isSrc) ? $this->extractUrl($attr) : false;
                
                if($url){
                    $acceptable = true;
                    break;
                }
            }
                
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
            $ignore = false;
            $url = false;
            
            foreach($link->attributes as $attr){
                if($attr->nodeName === 'rel' && $attr->nodeValue === 'nofollow'){
                    $ignore = true;
                    break;
                }
                
                $isHref = $attr->nodeName === 'href';                
                $url = ($isHref) ? $this->extractUrl($attr) : $url;
            }
            
            $acceptable = ! $ignore && $url !== false;
                
            if($acceptable){
                $this->links[] = $url;
                $this->linkNodes[] = $link;
            }
        }
    } 
    
    /* Extrae la URL del atributo, hace algunas validaciones */
    
    private function extractUrl(DomAttr $attr){
        $url = $attr->nodeValue;
        $response = false;
        
        if($this->isValidUrl($url)){
            $response = $url;
        }
        
        return $response;
    }
    
    /* Compueba que la URL es valida */
    
    private function isValidUrl($url){
        $response = true;
        
        if(preg_match('/^javascript:.*$/i', $url)){
            return false;
        }
        
        if(preg_match('/^mailto:.*$/i', $url)){
            return false;
        }
                
        if(preg_match('/^#.*$/i', $url)){
            return false;
        }
        
        return $response;
    }
    
    /* Libera la memoria */
    
    public function clear(){
        $this->links = [];
        $this->Dom = null;
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
}