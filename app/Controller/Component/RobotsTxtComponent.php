<?php

App::uses('CrawlerUtilityComponent', 'Controller/Component');
App::uses('HttpClientComponent', 'Controller/Component');
App::import('Model', 'CrawlerLog');

/* Este componente se encarga de parsear el robots.txt con lo cual se
 * determina si una URL esta habilitada para ser explorada por el creador
 * del sitio Target. Es impotante respetar estas reglas para evitar baneos.
 *  */

class RobotsTxtComponent extends CrawlerUtilityComponent{
    private static $TAG = '[ROBOTS]';
    
    /* Llamar al inicializador antes de empezar el proceso */
    
    public function init(CrawlerLog $log,$logFunction) {  
        $this->setLogFunction($logFunction);
        $this->setCrawlerLog($log);
        
        Configure::load('http_client');
        $this->userAgent = Configure::read('Http.user_agent');
    }    
    
    /* El User Agent que utiliza el Crawler para sus peticiones */
    
    private $userAgent;
    
    /* Determina si la URL especificada esta permitida por robots.txt
     * Hace una llamada lazy a parse(), si es que el flag $parsed aun no 
     * se enciende. */
    
    private $parsed = false;
    
    public function isAllowed($url){
        $allowed = true;
        $logic = true;
        $path = $this->getPath($url);
        
        if( ! $this->parsed){
            $this->parse($url);
        }
        
        foreach($this->rules as $rule){
            list($lexeme,$value) = $rule;
            
            switch ($lexeme){
                case self::$ALLOW:
                    $logic = true;
                    break;
                case self::$DISALLOW:
                    $logic = false;
                    break;
            }
            
            $allowed = $this->isMatch($path,$value) ? $logic : $allowed;
        }
        
        if( ! $allowed ){
            $this->logRobots("Disallow: {$url}");
        }
        
        return $allowed;
    }
    
    /* Funcion generica para logueo, en caso de que se quiera agregar TAGS u
     * otra info generica para todos los mensajes.
     */
    
    private function logRobots($message){
        $full = self::$TAG . ' ' . $message;
        $this->logInfo($full);
    }
    
    /* Determina si el Path matchea con el valor */
    
    private function isMatch($path,$value){
        $fixed = $this->getRegex($value);        
        $regex = "/^{$fixed}/";
        $response = false;
        
        if(preg_match($regex, $path)){
            $response = true;
        }
        
        return $response;
    }
    
    /* Obtiene el path de la URL especificada */
    
    private function getPath($url){
        $data = parse_url($url);
        $path = '/';
        
        if(isset($data['path'])){
            if(isset($data['host'])){
                $path = $data['path'];
            }
        }
        
        return $path;
    }
    
    /* Parsea el archivo robots.txt ubicado en el root de la url especificada.
     * El briefing de la sintaxis utilizado: https://support.google.com/webmasters/answer/6062596?hl=en
     **/
    
    
    public function parse($url){
        $robopath = $this->getRoboPath($url);
        
        if($robopath){
            $this->fetchRobots($robopath);
        }
        
        $this->filterUserAgent();
        $this->cleanLexemes();
        $this->parsed = true;
    }
    
    /* Borra los lexemas para liberar memoria */
    
    private function cleanLexemes(){
        $this->lexemes = [];
    }
    
    /* Filtra los lexemas para hacer match con nuestro UserAgent.
     * Si hay match de UserAgent el resto de lexemas Allow/Disallow
     * se introducen al array $rules */
    
    private $rules = [];
    
    private function filterUserAgent(){
        $matched = false;
        
        foreach($this->lexemes as $data){
            $lexeme = $data[0];
            $value = $data[1];
            
            if($lexeme === self::$USER_AGENT){
                $matched = $this->matchUserAgent($value);
            }
            else if($matched){
                $this->rules[] = $data;
            }
        }
    }
    
    /* Testea el valor del lexema UserAgent del archivo robots, si coincide
     * con nuestro UserAgent devuelve TRUE.
     */
    
    private function matchUserAgent($value){
        $fixed = $this->getRegex($value);        
        $regex = "/^{$fixed}/";
        $response = false;
        
        if(preg_match($regex, $this->userAgent)){
            $response = true;
        }
        
        return $response;
    }
    
    /* Arregla los valores de la instruccion para convertirlo a Regex */
    
    private function getRegex($value){
        $slashed = preg_quote($value,'/');
        $asterisked = str_replace('\\*', '.*', $slashed);
        $ended = str_replace('\\$', '$', $asterisked);
        return $ended;
    }
    
    /* Descarga el robots.txt si ocurre algun error se asume que no hay restricciones
     * para nuestro Crawler.
     */
    
    private function fetchRobots($robopath){
        $this->initHttp();
        $this->Http->get($robopath);
        
        $accept = $this->Http->isAcceptable();
        
        if($accept){
            $this->parseLexemes();
        }
    }
    
    /* Parsea el archivo robots.txt linea por linea, en busca de Tokens validos */
    
    private static $ALLOW = 'Allow';
    private static $DISALLOW = 'Disallow';
    private static $USER_AGENT = 'UserAgent';
    
    private $lexemes = [];
    
    private function parseLexemes(){
        $response = $this->Http->getResponse();
        $lines = explode("\n", $response);
        
        foreach($lines as $line){
            
            if($this->isUserAgentLexeme($line)){
                $value = $this->getValueLexeme($line);
                $this->lexemes[] = [ self::$USER_AGENT, $value ];
            }            
            else if($this->isAllowLexeme($line)){
                $value = $this->getValueLexeme($line);
                $this->lexemes[] = [ self::$ALLOW, $value ];
            }
            else if($this->isDisallowLexeme($line)){
                $value = $this->getValueLexeme($line);
                $this->lexemes[] = [ self::$DISALLOW, $value ];
            }
        }
    }
    
    
    /* Detecta si en la linea hay un lexema de tipo User Agent */
    
    public static $USER_AGENT_LEXEME = 'User-agent:';
    
    private function isUserAgentLexeme($line){
        $response = false;
        $lexeme = self::$USER_AGENT_LEXEME;
        $regex = "/^\s*{$lexeme}\s*/";
        
        if(preg_match($regex, $line)){
            $response = true;
        }
        
        return $response;
    }
    
    /* Detecta si en la linea hay un lexema de tipo Allowed */
    
    public static $ALLOW_LEXEME = 'Allow:';
    
    private function isAllowLexeme($line){
        $response = false;
        $lexeme = self::$ALLOW_LEXEME;
        $regex = "/^\s*{$lexeme}\s*/";
        
        if(preg_match($regex, $line)){
            $response = true;
        }
        
        return $response;
        
    }
    /* Detecta si en la linea hay un lexema de tipo Disallowed */
    
    public static $DISALLOW_LEXEME = 'Disallow:';
    
    private function isDisallowLexeme($line){        
        $response = false;
        $lexeme = self::$DISALLOW_LEXEME;
        $regex = "/^\s*{$lexeme}\s*/";
        
        if(preg_match($regex, $line)){
            $response = true;
        }
        
        return $response;
    }
    
    /* Inicializa el componente HTTP con el cual bajaremos el robots.txt */
    
    private function initHttp(){          
        $collection = new ComponentCollection();
        $this->Http = new HttpClientComponent($collection);
        
        CakeLog::config('robots-http', array(
            'engine' => 'FileLog',
            'types' => [ 'info' ],
            'scopes' => [ 'robots-http' ],
            'file' => 'robots-http'
        ));    
        
        $this->Http->init($this->getCrawlerLog(),function($message){
            CakeLog::write('info', $message, 'robots-http');
        });        
    }
    
    /* Obtiene el valor de un lexema UserAgent, Disallowed, Allowed */
    
    private function getValueLexeme($line){
        list($lexeme,$value) = explode(':',$line,2);
        return trim($value);
    }
    
    /* Obtiene la ruta absoluta a robots.txt dada la URL */
    
    private function getRoboPath($url){
        $chunks = parse_url($url);
        
        if($chunks !== false){
            $path = '';
            
            if(isset($chunks['scheme'])){
                $path .= $chunks['scheme'] . '://';
            }
                
            if(isset($chunks['host'])){
                $path .= $chunks['host'];
            }            
            else if(isset($chunks['path'])){
                $path .= $chunks['path'];                
            }
                        
            if(isset($chunks['port'])){
                $path .=  ':' . $chunks['port'];
            }
            
            $path .= '/robots.txt';            
            return $path;
        }
        
        return false;
    }
}