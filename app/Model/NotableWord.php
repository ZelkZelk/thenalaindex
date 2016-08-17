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
        'emotional_value' => array(
            'required' => true,
            'type' => 'options',
            'label' => 'Valor Emocional',
            'writable' => true,
            'readable' => true,
            'options' => [
                'unset' => 'No Establecido',
                'positive' => 'Positivo',
                'negative' => 'Negativo',
                'neutral' => 'Neutral'
            ]
        )
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
    
    /* Busca una palabra que este pendiente de acuerdo al modulo de diccionario */
    
    public function loadPending($module){
        switch ($module){
            case 'emotional':
                return $this->loadPendingEmotional();
        }
        
        return false;
    }
    
    /* Actualiza la palabra pendiente segun modulo de diccionario */
    
    public function updatePending($module,$id,$value){
        switch ($module){
            case 'emotional':
                return $this->updateEmotional($id,$value);
        }
        
        return false;
    }
    
    /* Actualiza el valor emocionalde la palabra */
    
    private function updateEmotional($id,$value){
        if($this->loadFromId($id)){
            $this->id = $id;
            $this->Data()->write('emotional_value',$value);
            return $this->store();
        }
        
        return false;
    }
    
    /* Busca una palabra que este pendiente de analisis emocional */
    
    public function loadPendingEmotional(){
        $cnd = [];
        $cnd['NotableWord.emotional_value'] = 'unset';
        
        $row = $this->find('first',[
            'conditions' => $cnd,
            'order' => 'random()'
        ]);
        
        if($row){
            $blob = $row['NotableWord'];
            $this->loadArray($blob);
            return true;
        }
        
        return false;
    }
    
    /* Resuelve las opciones a setear por interface de diccionario */
    
    public function resolvDictionaryOptions($module){
        $options = [];
        
        switch($module){
            case 'emotional':
                $options = $this->getSchema()['emotional_value']['options'];
                unset($options['unset']);
                break;
        }
        
        return $options;
    }
}