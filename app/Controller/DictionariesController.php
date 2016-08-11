<?php

App::uses('BackendAppController', 'Controller');

class DictionariesController extends BackendAppController {
    public $uses = [ 'NotableWord' ];
    public $components = [ 'React', 'Login','Session' ];
    public $helpers = [ 'Scaffold' ];
    
    public function emotional(){
        $this->css(Router::url('/css/dictionaries/main.css',true),[
            'rel' => 'stylesheet',
            'type' => 'text/css'
        ]);
        
        Configure::load('react_api');
        $api = Configure::read('ReactApi.dictionaries.emotional');
        $this->set('api',$api);
        $this->React->load();
    }
    
    public function api(){
        header('Content-Type: application/json');
        $this->autoRender = false;
        $word = false;
        $id = false;
        
        if($this->NotableWord->loadPendingEmotional()){
            $blob = $this->NotableWord->Data()->dump();
            $word = $blob['word'];
            $id = $blob['id'];
        }
        
        $module = $this->params['module'];
        $options = $this->NotableWord->resolvDictionaryOptions($module);
        
        $data = [
            'id' => $id,
            'word' => $word,
            'options' => $options,
            'reference' => $this->resolvReference($word)
        ];
        
        ob_start();
        echo json_encode($data);
        ob_flush();
        exit;
    }
    
    private function resolvReference($word){
        Configure::load('dictionary');
        $url = sprintf(Configure::read('Dictionary.url'),$word);
        return $url;
    }
}