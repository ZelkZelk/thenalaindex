<?php

App::uses('Model', 'NotableWord');


class NotableWord extends AppModel {    
    public $useDbConfig = 'dictionary';
    private $schema = 'default';    
    
    private $defaultSchema = [
        'word' => array(
            'required' => false,
            'type' => 'text',
            'label' => 'Palabra',
            'writable' => false,
            'readable' => true,
        ),
    ];

    public function getSchema() {
        switch($this->schema){
            case 'default': 
                $schema = $this->defaultSchema;
                $this->labelField = 'word';
                return $schema;
        }
        
        return array();
    }
    
    private $labelField;

    public function getLabelField(){
        return $this->labelField;
    }
    
    public function getIcon(){
        return 'letter';
    }

    public function getLogicalField() {
        return null;
    }

    public function isPhysicalDeletor() {
        return true;
    }
    
    /* Determina si la palabra ya existe en el diccionario. Las palabras se 
     * almacenana en minusculas.
     *  */
    
    public function existsWord($word){
        $w = strtolower($word);
        
        $cnd = [];
        $cnd['NotableWord.word'] = $w;
        
        $opts = [];
        $opts['conditions'] = $cnd;
        $data = $this->find('first', $opts);
        
        if($data){
            $alias = $this->alias;
            $blob = $data[$alias];
            $this->loadArray($blob);
            return true;
        }
        
        return false;
    }
    
    /** 
     * Almacena una nueva entrada en el diccionario
     */
    
    public function pushWord($word){
        $w = strtolower($word);
        
        $this->id = null;
        $this->Data()->write('id',null);
        $this->Data()->write('word',$w);
        
        if($this->store()){
            return true;
        }
        
        return false;
    }
}