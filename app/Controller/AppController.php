<?php

App::uses('Controller', 'Controller');

class AppController extends Controller {
    public $components = [ 'Login', 'Session' ];
    public $helpers = [ 'Head' ];
    
    /* Obtiene las acciones disponibles del administrador en el menu contextual 
     * de la barra superior de la pagina.
     */
    
    protected function getAdminActions(){
        Configure::load('login');
        $logoutURL = Configure::read('Logout.url');
        $logoutData = $this->getActionData($logoutURL['controller'], $logoutURL['action']);
        
        return [
            $logoutData
        ];
    }
    
    /* Obtiene los metadatos del sitemap de la accion especificada */
    
    public function getActionData($ctrl,$actn){
        Configure::load('sitemap');            
        $data = Configure::read("{$ctrl}.{$actn}");
        $data['controller'] = $ctrl;
        $data['action'] = $actn;
        return $data;
    }
    
    /* Almacena la configuracion del sitemap de esta accion */
    
    protected $Conf = [];
    
    /* Antes de cada accion vamos a preguntar si es necesario estar logueado
     * y redirigir a la pantalla si es que no tenemos un administrador logueado.
     */
    
    public function beforeFilter() { 
        $this->loadConf();
        
        if($this->Login->isRequired($this->request)){
            $this->fail($this->Login->getLastError());
            $this->Login->redirect();
        }
        
        parent::beforeFilter();
    }
    
    /* Carga la configuracion del sitemap del par controlador-accion actual */
    
    private function loadConf(){        
        $ctrl = $this->params['controller'];
        $actn = $this->params['action'];
        
        Configure::load('sitemap');
        $this->Conf = Configure::read("{$ctrl}.{$actn}");
    }
    
    /* Agrega un css al HEAD */
    
    private $css = [];
    
    public function css($path,$params = []){
        $this->css[$path] = $params;
    }
    
    public function getCss(){
        return $this->css;
    }
    
    /* Agrega un js al HEAD */
    
    private $js = [];
    
    public function js($path,$params = []){
        $this->js[$path] = $params;
    }
    
    public function getJs(){
        return $this->js;
    }
    
    /* Agrega una variable global js al HEAD */
    
    private $jsVars = [];
    
    public function jsVar($name,$data){
        $this->jsVars[$name] = $data;
    }
    
    public function getJsVars(){
        return $this->jsVars;
    }
    
    public function beforeRender() {        
        if ($this->name == 'CakeError') { 
            $this->layout = 'error';
        }
        
        $this->set('CUSTOM_CSS',$this->getCss());
        $this->set('CUSTOM_JS',$this->getJs());
        $this->set('CUSTOM_JS_VARS',$this->getJsVars());
    }
}