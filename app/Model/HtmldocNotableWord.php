<?php

App::uses('Model', 'NotableWord');
App::uses('Model', 'DataFile');

class HtmldocNotableWord extends AppModel {    
    public $useDbConfig = 'analysis';
    private $schema = 'default';    
    
    private $defaultSchema = [
        'data_file_id' => array(
            'type' => 'foreign',
            'required' => true,
            'unique' => false,
            'label' => 'Archivo',
            'writable' => false,
            'readable' => true,
            'class' => 'DataFile'
        ),
        'notable_word_id' => array(
            'type' => 'foreign',
            'required' => true,
            'unique' => false,
            'label' => 'Palabra',
            'writable' => false,
            'readable' => true,
            'class' => 'NotableWord'
        ),
        'quantity' => array(
            'required' => false,
            'type' => 'int',
            'label' => 'Frecuencia',
            'writable' => false,
            'readable' => true,
        ),
    ];

    public function getSchema() {
        switch($this->schema){
            case 'default': 
                $schema = $this->defaultSchema;
                $this->labelField = 'notable_word_id';
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
    
    /**
     * Crea un HtmldocNotableWord relacionando NotableWord y DataFile
     */
    
    public function pushWord(DataFile $dataFile,NotableWord $word, $freq){
        if($this->existsWord($dataFile, $word) === false){
            $this->id = null;
            $this->loadArray([
                'data_file_id' => $dataFile->id,
                'notable_word_id' => $word->id,
                'quantity' => $freq,
                'id' => null
            ]);
            
            try{
                return $this->store();
            }
            catch (Exception $e){
                error_log("Exception@HtmldocNotableWord::pushWord() - {$e->getMessage()}");
                return false;
            }
        }
        
        return true;
    }
    
    public function existsWord(DataFile $dataFile,NotableWord $word){
        $cnd = [];
        $cnd['HtmldocNotableWord.data_file_id'] = $dataFile->id;
        $cnd['HtmldocNotableWord.notable_word_id'] = $word->id;
        
        $opts = [];
        $opts['conditions'] = $cnd;
        $data = $this->find('first',$opts);
        
        if($data){
            $alias = $this->alias;
            $blob = $data[$alias];
            $this->loadArray($blob);
            return true;
        }
        Inflector::singularize($word);
        return false;
    }
}