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
//        throw new BadRequestException();
        header('Content-Type: application/json');
        $this->autoRender = false;
        
        if($this->request->is('POST')){
            $this->apiPost();
        }
        
        $data = $this->apiGet();
        
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

    private function apiPost() {
        $module = $this->params['module'];
        $id = $this->data['id'];
        $value = $this->data['value'];
        $this->NotableWord->updatePending($module,$id,$value);
    }

    private function apiGet() {
        $word = false;
        $id = false;
        $module = $this->params['module'];
        
        if($this->NotableWord->loadPending($module)){
            $blob = $this->NotableWord->Data()->dump();
            $word = $blob['word'];
            $id = $blob['id'];
        }
        
        $options = $this->NotableWord->resolvDictionaryOptions($module);
        
        $data = [
            'id' => $id,
            'word' => $word,
            'options' => $options,
            'reference' => $this->resolvReference($word)
        ];
        
        return $data;
    }
}