<?php

App::uses('AppController', 'Controller');

class AssetsController extends AppController {
    public $uses = [ 'DataFile', 'MetaDataFile' ];
    
    public function beforeFilter() {
        parent::beforeFilter();
    }
    
    public function index(){
        $hash = $this->resolvHash();
        
        if($this->MetaDataFile->loadHash($hash) === false){
            throw new NotFoundException();
        }
        
        $id = $this->MetaDataFile->id;
        
        if($this->DataFile->loadFromMeta($id) === false){
            throw new NotFoundException();
        }
        
        ob_start();
        ob_clean();
        echo $this->DataFile->getFile();
        ob_flush();
        exit;
    }
    
    private function resolvHash(){
        if(isset($this->params['hash']) === false){
            throw new BadRequestException();
        }
        
        $hash = $this->params['hash'];
        
        if($hash === ''){
            throw new BadRequestException();
        }
        
        return $hash;
    }
} 