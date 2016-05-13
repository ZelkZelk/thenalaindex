<?php

App::uses('CrawlerUtilityComponent', 'Controller/Component');

/* Este componente se encarga de evitar que una URL fuera del dominio del Target
 * ingrese a la cola, para evitar recolectar toda la Internet.
 **/

class UrlNormalizerComponent extends CrawlerUtilityComponent{
    
    /* Root es un Array que contiene el dominio valido para crawlear. */
    
    private $Root = false;
    
    /* Root es deducido de la URL del Target */
    
    private $RootUrl;
    
    /* Url reducida en componentes */
    
    private $Chunks;
    
    /* Override de la funcion generica para agregar el TAG NORMALIZER
     */
    
    private static $TAG = '[NORMALIZER]';
    
    private function logNormalizer($message){
        $full = self::$TAG . ' ' . $message;
        $this->logInfo($full);
    }
    
    /* Llamar al inicializador antes de empezar el proceso */
    
    public function init($rootUrl,CrawlerLog $log,$logFunction) {  
        $this->setLogFunction($logFunction);
        $this->setCrawlerLog($log);
        $this->RootUrl = $rootUrl;
        $this->calculateRoot();
    }    
    
    /* Esta funcion deduce el Root Domain del Target especifico */
    
    private function calculateRoot(){
        $url = $this->RootUrl;
        $chunks = parse_url($url);
        $this->Root = $this->getDefaultRoot($chunks);
        
        if(isset($this->Root['host'])){
            $domain = $this->Root['host'];            
            $this->logNormalizer("CALC ROOT<$url@$domain>");
        }
        else{
            $this->Root = false;
            $this->logNormalizer("CALC ROOT FAILED<$url>");
        }
    }
    
    /* Determina los componentes default de la URL de no estar establecidos
     * 
     * port = 80
     * scheme = http
     */
    
    private static $DEFAULT_URL_HTTPS_SCHEME = 'https';
    private static $DEFAULT_URL_HTTP_SCHEME = 'http';
    private static $DEFAULT_URL_HTTPS_PORT = 443;
    private static $DEFAULT_URL_HTTP_PORT = 80;
    private static $DEFAULT_URL_PATH = '/';
    
    private function getDefaultRoot($chunks){
        if(isset($chunks['host']) === false){
            if(isset($chunks['path']) === true){
                $chunks['host'] = $chunks['path'];
                $chunks['path'] = self::$DEFAULT_URL_PATH;
            }
        }
        
        if(isset($chunks['scheme']) === false){
            $chunks['scheme'] = self::$DEFAULT_URL_HTTP_SCHEME;
        }
        
        if(isset($chunks['port']) === false){
            $chunks['port'] = $this->getDefaultPort($chunks['scheme']);
        }
        
        return $chunks;
    }
    
    /* Determina el puerto http(s) de la url, dependiendo del esquema */
    
    private function getDefaultPort($scheme){
        $port = self::$DEFAULT_URL_HTTP_PORT;  
        
        if($scheme === self::$DEFAULT_URL_HTTPS_SCHEME){
            $port = self::$DEFAULT_URL_HTTPS_PORT;                
        }
        
        return $port;
    }
    
    /* Determina si la Url es suceptible de ser encolada */
    
    public function isAllowed(){
        $response = false;
        
        if($this->Root !== false){
            $domain = $this->Chunks['host'];
            
            if($domain === $this->Root['host']){
                $response = true;
            }
        }
        
        if($response){
            $this->logNormalizer("ALLOWED<{$this->TargetUrl}>");
        }
        else{
            $this->logNormalizer("NOT ALLOWED<{$this->TargetUrl}>");            
        }
        
        return $response;
    }
    
    /* Obtiene la URL completa para el encolado */
    
    public function getNormalizedUrl(){
        $scheme = $this->Chunks['scheme'];
        $host = $this->Chunks['host'];
        $port = $this->Chunks['port'];
        $path = $this->Chunks['path'];
        
        $url = "{$scheme}://{$host}:{$port}{$path}";
        
        if(isset($this->Chunks['query'])){
            $query = $this->Chunks['query'];
            $url .= "?{$query}";
        }
        
        return $url;
    }
    
    /* Normaliza la URL en sus componentes, el dominio, schema y puerto se deducen
     * del Target, de no estar establecidos. */
    
    private $TargetUrl;
    
    public function normalize($url,$referer = null){
        $absoluteUrl = $this->absolutize($url,$referer);
        $this->TargetUrl = $absoluteUrl;
        $chunks = parse_url($absoluteUrl);
        
        if($chunks === false){
            $chunks = [];
        }
            
        if(isset($chunks['scheme']) === false){
            $chunks['scheme'] = $this->Root['scheme'];
        }

        if(isset($chunks['port']) === false){
            $chunks['port'] = $this->getDefaultPort($chunks['scheme']);
        }
        
        if(isset($chunks['host']) === false){
            $chunks['host'] = $this->Root['host'];
        }
        
        if(isset($chunks['path']) === false){
            $chunks['path'] = self::$DEFAULT_URL_PATH;
        }
        else if( ! preg_match('/^\//', $chunks['path'])){
            $chunks['path'] = '/' . $chunks['path'];
        }
        
        $this->Chunks = $chunks;
    }
    
    /* Convierte la Url a una Url absoluta */
    
    private function absolutize($url,$referer = null){        
        if($this->isRelative($url)){
            $refererPath = $this->getBaseUrl($referer);
            $url = $refererPath . $url;
        }
        
        $final = $this->slashDotsRemover($url);
        return $final;
    }
    
    /* Se asume que si la url es /../../../loquesea en realidad quiere decir 
     * /loquesea directamente. */
    
    private function slashDotsRemover($url){   
        $chunks = parse_url($url);
        $path = '';
        
        if(isset($chunks['path'])){
            $path = $chunks['path'];
        }
        
        $pathChunks = explode('/', $path);
        
        foreach($pathChunks as $i => $pathChunk){
            switch ($pathChunk){
                case '.':
                    unset($pathChunks[$i]);
                    break;
                case '..':
                    unset($pathChunks[$i]);
                    
                    if(isset($pathChunks[$i - 1])){
                        unset($pathChunks[$i - 1]);
                    }
                    
                    break;
            }
        }
        
        
        $chunks['path'] = implode('/', $pathChunks);
        return $this->rebuildUrl($chunks);
    }
    
    /* Construye la URL a partir de los pedazos creados por la funcion parse_url() */
    
    private function rebuildUrl($chunks){
        $url = '';
        
        if(isset($chunks['scheme'])){
            $url .= $chunks['scheme'] . '://';
        }
        
        if(isset($chunks['host'])){
            $url .= $chunks['host'];
        }
        
        if(isset($chunks['port'])){
            $url .= ':' . $chunks['port'];
        }
        
        if(isset($chunks['path'])){
            $url .= $chunks['path'];
        }
        
        if(isset($chunks['query'])){
            $url .= '?' . $chunks['query'];
        }
        
        if(isset($chunks['fragment'])){
            $url .= '#' . $chunks['fragment'];
        }
        
        return $url;
    }
    
    /* Determina si la URL es absoluta */
    
    private function isRelative($url){      
        $first = substr($url,0,1);
        
        if($first === '/'){
            return false;
        }
        
        $chunks = parse_url($url);
        $result = true;
        
        if(isset($chunks['host'])){
            $result = false;
        }
        
        return $result;
    }
    
    /* Determina la Base URL (es decir elimina lo que esta despues del ultimo slash) */
    
    private function getBaseUrl($url){
        $index = strrpos($url, '/');
        $base = substr($url,0,$index + 1);
        return $base;
    }
} 