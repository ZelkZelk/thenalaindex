<?php

App::uses('Model','Target');

class Url extends AppModel {    
    public $useDbConfig = 'crawler';
    private $schema = 'default';    
    
    private $defaultSchema = [
        'full_url' => array(
            'type' => 'text',
            'required' => true,
            'unique' => true,
            'label' => 'URL',
            'writable' => false,
            'readable' => true,
        ),
        'target_id' => array(
            'type' => 'foreign',
            'required' => true,
            'unique' => false,
            'label' => 'Sitio',
            'writable' => false,
            'readable' => true,
            'class' => 'Target'
        ),
    ];

    public function getSchema() {
        switch($this->schema){
            case 'default': 
                $schema = $this->defaultSchema;
                $this->labelField = 'url';
                return $schema;
        }
        
        return array();
    }
    
    private $labelField;

    public function getLabelField(){
        return $this->labelField;
    }
    
    public function getIcon(){
        return 'link';
    }

    public function getLogicalField() {
        return null;
    }

    public function isPhysicalDeletor() {
        return true;
    }
        
    /* Intenta crear una url, o si existe, llena el modelo con sus datos. */
    
    public function alloc(Target $target,$url){
        $this->id = $this->getUrlId($url);
        
        if($this->id === false){
            $this->id = $this->insert($target,$url);
        }
    }
    
    /* Inserta una nueva URL a la BD */
    
    private static $FULL_URL_LEN = 2000;
    
    private function insert(Target $target,$url){
        $data = [
            'full_url' => substr($url,0,self::$FULL_URL_LEN),
            'target_id' => $target->id,
            'id' => null
        ];
        
        $this->id = null;
        $this->loadArray($data);
        
        if($this->store()){
            return $this->id;
        }
        
        return false;
    }
    
    /* Devuelve el ID de una URL */
    
    public function getUrlId($url){
        $cnd = [];
        $cnd['Url.full_url'] = $url;
        
        $exists = $this->find('first',[ 
            'conditions' => $cnd,
            'fields' => 'Url.id'
        ]);
        
        if($exists){
            $this->loadArray($exists['Url']);
            return $this->id;
        }
        
        return false;
    }
}