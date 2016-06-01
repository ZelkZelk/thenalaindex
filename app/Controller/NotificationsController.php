<?php

App::uses('BackendAppController', 'Controller');

class NotificationsController extends BackendAppController {
    public $uses = [ 'Notification' ];
    public $components = [ 'React', 'Login','Session' ];
    public $helpers = [ 'Scaffold' ];
    
    public function email(){
        $links = $this->Notification->fetchLinks();
        $this->set('links',$links);
        
        Configure::load('react_api');
        $abm = Configure::read('ReactApi.notifications.email');
        $this->set('abm',$abm);
        
        $this->React->load();
    }
}