<?php

App::uses('AppController', 'Controller');

class FrontendController extends AppController {
    public $components = array('React');
    
    public function beforeFilter() {
        parent::beforeFilter();
        $this->layout = 'frontend';
    }
    
    public function index(){
        $this->loadHeaderConfig();
        $this->loadParams();
        $this->loadConfig();
        
        $this->React->load();
    }
    
    private function loadHeaderConfig(){
        $header = [];
        $header['mainUrl'] = Router::url([ 'controller' => 'frontend' , 'action' => 'index'],true);
        $header['logoUrl'] = Router::url('/',true) . 'img/logo.png';

        $this->set('header',$header);
        return $header;
    }
    
    private function loadConfig(){
        Configure::load('frontend');
        $config = Configure::read('Frontend');
        
        $this->set('config',$config);
        return $config;
    }
    
    private function loadParams(){        
        $params = [];
        
        foreach($this->params->params as $name => $val){
            if(is_array($val) === false){
                $params[$name] = $val;
            }
        }
        
        $params['module'] = $this->getModule();
        unset($params['plugin']);
        unset($params['controller']);
        unset($params['action']);
        
        $this->set('params', $params);
        return $params;
    }
    
    private function getModule(){
        $module = 'index';
        
        if(isset($this->params['module'])){
            $module = $this->params['module'];
        }
        
        return $module;
    }
}