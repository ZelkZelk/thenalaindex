<?php

class Administrator extends AppModel {    
    public $useDbConfig = 'auth';
    private $schema = 'default';    
    private $defaultSchema = [
        'user_name' => array(
            'required' => true,
            'type' => 'text',
            'unique' => true,
            'label' => 'Nombre de Usuario',
            'writable' => true,
            'readable' => true,
            'maxlength' => 30,
        ),
        'password' => array(
            'required' => true,
            'type' => 'password',
            'unique' => false,
            'label' => 'Password',
            'writable' => true,
            'readable' => false,
            'minlength' => 5
        ),
        'last_login' => array(
            'type' => 'datetime',
            'label' => 'Ultimo Ingreso',
            'writable' => false,
            'readable' => true,
            'null' => 'No ha ingresado'
        ),
        'last_login_ip' => array(
            'type' => 'text',
            'label' => 'Dir. IP Ultimo Ingreso',
            'writable' => false,
            'readable' => true,
            'null' => 'No ha ingresado'
        ),
        'login_attempts' => array(
            'writable' => false,
            'readable' => false
        ),
        'last_login_attempt' => array(
            'writable' => false,
            'readable' => false
        ),
    ];

    public function getSchema() {
        switch($this->schema){
            case 'default': 
                $schema = $this->defaultSchema;
                $this->labelField = 'user_name';
                
                Configure::load('login');
                $schema['password']['crypto'] = Configure::read('Login.crypto');
                $schema['password']['salt'] = Configure::read('Login.salt');
                
                return $schema;
        }
        
        return array();
    }
    
    private $labelField;
    
    public function getLabelField(){
        return $this->labelField;
    }
    
    public function getIcon(){
        return 'user';
    }

    public function getLogicalField() {
        return null;
    }

    public function isPhysicalDeletor() {
        return true;
    }
}