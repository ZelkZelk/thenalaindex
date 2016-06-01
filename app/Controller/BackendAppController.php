<?php

App::uses('AppController', 'Controller');

class BackendAppController extends AppController {
    
    public function beforeFilter() {
        parent::beforeFilter();
        
        $this->layout = 'backend';
    }
    
    /* Mostramos las pantalls de error personalizadas */
    
    public function beforeRender() {        
        parent::beforeRender();
        
        if($this->layout == 'backend'){
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
}
