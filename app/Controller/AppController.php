<?php

App::uses('Controller', 'Controller');

class AppController extends Controller {
    public $components = [ 'Login', 'Session' ];
    public $helpers = [ 'Head' ];
    
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
    
    /* Obtiene las acciones disponibles del administrador en el menu contextual 
     * de la barra superior de la pagina.
     */
    
    private function getAdminActions(){
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
    
    /* Mostramos las pantalls de error personalizadas */
    
    public function beforeRender() {        
        if ($this->name == 'CakeError') { 
            $this->layout = 'error';
        }
        
        $this->set('CUSTOM_CSS',$this->css);
        $this->set('CUSTOM_JS',$this->js);
        $this->set('CUSTOM_JS_VARS',$this->jsVars);
        
        if($this->layout == 'default'){
            $this->set('AdminActions',$this->getAdminActions());
            $this->set('Admin',$this->Login->Admin());
            $this->set('Action',$this->getActionData($this->params['controller'], $this->params['action']));
            
            $Root = Configure::read('App.rootUrl');
            $this->set('Root',$this->getActionData($Root['controller'], $Root['action']));
            $this->set('Menu',$this->getMenu());
        }
    }
    
    /* Obtiene las acciones del menu lateral. */
    
    private function getMenu(){
        Configure::load('menu');        
        Configure::load('sitemap');        
        $menu = Configure::read('menu');
        
        foreach($menu as $id => $conf){
            foreach($conf['actions'] as $i => $action){
                $ctrl = $action[0];
                $actn = $action[1];
                $menu[$id]['actions'][$i] = $this->getActionData($ctrl, $actn);
            }
        }
        
        return $menu;
    }
    
    /* Presente un mensaje con informacion emergente al usuario, acerca de un
     * evento satisfactorio. */
    
    protected function done($message){
        $this->Session->setFlash($message,'flash/popup_done');
    }
    
    /* Presente un mensaje con informacion emergente al usuario, acerca de una
     * advertencia que debe tener en cuenta. */
    
    protected function warning($message){
        $this->Session->setFlash($message,'flash/popup_warning');
    }
    
    /* Presente un mensaje con informacion emergente al usuario, acerca de informacion
     * que debe tener en cuenta. */
    
    protected function info($message){
        $this->Session->setFlash($message,'flash/popup_info');
    }
    
    /* Presente un mensaje con informacion emergente al usuario, acerca de un fallo
     * ocurrido en el sistema, o por otros motivos que desencadenen un error. */
    
    protected function fail($message){
        $this->Session->setFlash($message,'flash/popup_fail');
    }
}
