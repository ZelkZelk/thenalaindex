<?php

App::uses('BackendAppController', 'Controller');

class BackendController extends BackendAppController {
    private static $loginDoneString = 'Bienvenido a The Nala Index';  
    private static $logoutDoneString = 'Sesion finalizada satisfactoriamente';  
    public $uses = [ 'Administrator' ];
    public $components = [ 'Login', 'Session' ];

    public function index(){
        
    }
    
    public function login(){
        $this->layout = 'login';
        
        Configure::load('login');
        $loginField = Configure::read('Login.loginField');
        $passwordField = Configure::read('Login.passwordField');
        $loginURL = Configure::read('Login.url');
        
        if($this->request->is('POST')){
            if($this->Login->auth($this->request)){
                $this->done(self::$loginDoneString);
                $this->redirect($this->Login->getIntentUrl());
            }
            else{
                $this->fail($this->Login->getLastError());
            }
        }
        
        $this->set('loginURL',$loginURL);
        $this->set('passwordField',$passwordField);
        $this->set('loginField',$loginField);
        $this->set('Admin',$this->Administrator);
    }
    
    public function logout(){
        $this->Login->unregisterLogin();
        $this->done(self::$logoutDoneString);
        $this->Login->redirect();
    }
}
