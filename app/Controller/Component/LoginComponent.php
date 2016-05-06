<?php

class LoginComponent extends Component{
    private static $SessionKey = 'Backend.Administrator.Login';
    public $components = [ 'Session' ];
    private $Admin;
    private $Controller;
    private $lastError;
    private static $loginFailUnmatched = 'Datos incorrectos!';
    private static $loginMutexKey = 'Login.Mutex';
    private static $bannedAccountString = 'Cuenta bloqueada durante %d minuto(s) %d segundo(s)';
    private static $intentUrlKey = 'Login.intent';
    private static $intentLoginRequired = 'Se require autenticacion';

    public function initialize(\Controller $controller) {
        $this->Controller = $controller;
        
        return parent::initialize($controller);
    }
    
    /* CU-01. Dado un Request, determina si los datos introducidos por POST validan el
     * login de algun Adminsitrador, de ocurrir algun error se registra en la
     * variable $lastError.
     * 
     * Al fallar debe registrarse la IP y el ultimo intento de acceso, ademas
     * se aumenta el contador de fallos.
     */
    
    public function auth(CakeRequest $request){
        App::import('Model','Administrator');
        
        $Administrator = new Administrator();
        $opts = $this->getAuthOpts($request);
        $rawdata = $Administrator->find('first', $opts); 
        
        if($rawdata){
            $Administrator->loadArray($rawdata['Administrator']);
            
            if($this->isBanned($Administrator)){                
                $this->lastError = self::$bannedAccountString;  
                return false;
            }
            else if($this->passwordMatch($Administrator,$request)){
                $this->registerLogin($Administrator);
                return true;
            }
        
            $this->registerFailedAttempt($Administrator);  
        } 
        
        $this->lastError = self::$loginFailUnmatched;    
        return false;
    }    
    
    /* Determina si la cuenta esta baneada, ese decir si el contador de fallos llego a
     * 3 y el ultimo intento fue hace menos de 1 hora. Si se detecta que ya paso
     * 1 hora se resetea el contador a 0.
     */
    
    private function isBanned(Administrator $Admin){
        Configure::load('login');   
        $lastLoginAttemptField = Configure::read('Login.lastLoginAttempField');  
        $loginAttemptsField = Configure::read('Login.loginAttemptsField');  
        $maxAttempts = Configure::read('Login.maxAttempts');  
        $banTime = Configure::read('Login.banTime');  
        
        $loginAttempts = $Admin->Data()->read($loginAttemptsField);
        
        if($loginAttempts >= $maxAttempts){
            $current = time();
            $lastAttempt = strtotime($Admin->Data()->read($lastLoginAttemptField));
            
            if($lastAttempt + $banTime > $current){
                $unban = $lastAttempt + $banTime - $current;                
                $min = floor($unban / 60);
                $sec = $unban - ($min * 60);                
                self::$bannedAccountString = sprintf(self::$bannedAccountString,$min,$sec);
                
                return true;
            }
            else{                
                $Admin->block(self::$loginMutexKey);
                $Admin->Data()->write($loginAttemptsField,0);  
                $Admin->store();
                $Admin->unblock(self::$loginMutexKey);     
            }
        }   
        
        return false;
    }
    
    /* Registra el intento fallido de Login en el Administrator proporcionado,
     * guarda la IP, aumenta el contador de intentos fallidos y el timestamp
     * del intento.
     */
    
    private function registerFailedAttempt(Administrator $Admin){
        Configure::load('login');
        $lastLoginIpField = Configure::read('Login.lastLoginIpField');   
        $lastLoginAttemptField = Configure::read('Login.lastLoginAttempField');  
        $loginAttemptsField = Configure::read('Login.loginAttemptsField');  
        
        $Admin->block(self::$loginMutexKey);
        $Admin->Data()->write($lastLoginIpField,$_SERVER['REMOTE_ADDR']);
        $Admin->Data()->write($lastLoginAttemptField,date('Y-m-d H:i:s'));
        $counter = $Admin->Data()->read($loginAttemptsField);
        $Admin->Data()->write($loginAttemptsField,++$counter);  
        $Admin->store();
        $Admin->unblock(self::$loginMutexKey);      
    }
    
    /* Verifica que el password coincida con el Administrador proporcionado, los datos
     * se obtienen del POST */
    
    private function passwordMatch(Administrator $Admin,  CakeRequest $request){
        Configure::load('login');
        $passwordField = Configure::read('Login.passwordField');   
        $password = $this->getCrypto($request->data['Administrator'][$passwordField]);
        return $Admin->Data()->read($passwordField) === $password;
    }
    
    /* Dado un request genera las opciones necesarias para generar la query que
     * obtendra al Administrador de la BD, si coinciden los datos enviados desde
     * POST.
     */

    private function getAuthOpts(CakeRequest $request) {    
        Configure::load('login'); 
        $loginField = Configure::read('Login.loginField');
        $opts = [];
        $opts["Administrator.{$loginField}"] = $request->data['Administrator'][$loginField];
                
        return $opts;
    }
    
    /* Encripta el password proporcionado teniendo en cuenta el algoritmo de hasheo
     * configurado y la sal a aplicar.
     */
    
    public function getCrypto($plainPassword) {    
        Configure::load('login');
        $crypto = Configure::read('Login.crypto');    
        $salt = Configure::read('Login.salt');
        
        switch($crypto){
            case 'md5':
                $cryptoPassword = md5($plainPassword . $salt);
                break;
            default:
                $cryptoPassword = $plainPassword;
        }        
        
        return $cryptoPassword;
    }
    
    
    /* Dado un Request devuelve un boolean segun las siguientes reglas.
     * 
     * true : si el campo de configuracion "private" es true y no hay sesion activa
     * false : cualquier otro caso
     */
    
    public function isRequired(CakeRequest $request){
        Configure::load('sitemap');
        $actn = $request->params['action'];
        $ctrl = $request->params['controller'];
        
        $conf = Configure::read("{$ctrl}.{$actn}");
        $private = (bool)@$conf['private'];
        
        if($private){
            if($this->isAdminSession() == false){
                $this->lastError = self::$intentLoginRequired;
                $this->storeIntentUrl();
                return true;
            }
        }
        
        return false;
    }
    
    /* Determina si existe una sesion de administrator activa.
     */
    
    public function isAdminSession(){
        if($this->Session->check(self::$SessionKey)){
            $adminId = $this->Session->read(self::$SessionKey);
            
            if($this->loadAdmin($adminId)){
                return true;
            }
        }
        
        return false;
    }
    
    /* Carga el administrador desde el ID especificado, devuelve un booleano que
     * determina el exito de la operacion. Asigna al atributo Admin el administrador
     * leido (de existir).
     */
    
    public function loadAdmin($id){
        App::import('Model','Administrator');
        $Administrator = new Administrator();

        if($Administrator->loadFromId($id)){
            $this->Admin = $Administrator;
            return true;
        }
        
        return false;
    }
    
    /* Obtiene el Administrador Actualmente logueado en el sistema */
    
    public function Admin(){
        return $this->Admin;
    }
    
    /* Redirige al navegador a la url de Login configurada */
    
    public function redirect(){
        Configure::load('login');
        $url = Configure::read('Login.url');
        $this->Controller->redirect($url);
        exit;
    }
    
    /* Obtiene el ultimo mensaje de error */
    
    public function getLastError(){
        return $this->lastError;
    }
    
    /* Guarda el login satisfactorio en la BD, limpia los contadores de intentos
     * fallidos.
     */

    public function registerLogin(Administrator $Admin) {
        Configure::load('login');
        $lastLoginIpField = Configure::read('Login.lastLoginIpField');   
        $lastLoginField = Configure::read('Login.lastLoginField');  
        $loginAttemptsField = Configure::read('Login.loginAttemptsField');  
        
        $Admin->block(self::$loginMutexKey);
        $Admin->Data()->write($lastLoginIpField,$_SERVER['REMOTE_ADDR']);
        $Admin->Data()->write($lastLoginField,date('Y-m-d H:i:s'));
        $Admin->Data()->write($loginAttemptsField,0);  
        $Admin->store();
        $Admin->unblock(self::$loginMutexKey);   
        
        $this->Session->renew();
        $this->Session->write(self::$SessionKey,$Admin->id);
    }
    
    /* Elimina la sesion actual del Administrador. */

    public function unregisterLogin() {
        $this->Session->renew();
        $this->Session->delete(self::$SessionKey);
    }
    
    /* Obtiene la URL a la cual redireccionar el navegador al loguearse correctamente.
     * Si no existe se envia al / directamente. */

    public function getIntentUrl() {
        if($this->Session->check(self::$intentUrlKey)){
            $url = $this->Session->read(self::$intentUrlKey);
            return $url;
        }
        
        return '/';
    }
    
    /* Guarda la URL a la que se intenta acceder sin tener login correcto. */

    public function storeIntentUrl() {
        $url = $this->Controller->request->here;        
        $this->Session->write(self::$intentUrlKey,$url);
    }

}