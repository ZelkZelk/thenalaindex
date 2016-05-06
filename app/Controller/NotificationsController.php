<?php

App::uses('AppController', 'Controller');

class NotificationsController extends AppController {
    public $uses = array('Notification');
    public $components = array('React');
    public $helpers = array('Scaffold');
    
    public function email(){
        $links = $this->Notification->fetchLinks();
        $this->set('links',$links);
        
        Configure::load('react_api');
        $abm = Configure::read('ReactApi.notifications.email');
        $this->set('abm',$abm);
        
        $this->React->load();
    }
}