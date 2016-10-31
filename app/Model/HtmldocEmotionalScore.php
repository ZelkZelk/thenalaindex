<?php

App::uses('Model', 'NotableWord');
App::uses('Model', 'DataFile');

class HtmldocEmotionalScore extends AppModel {    
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
        'score' => array(
            'required' => false,
            'type' => 'int',
            'label' => 'Valor Emocional',
            'writable' => false,
            'readable' => true,
        ),
    ];

    public function getSchema() {
        switch($this->schema){
            case 'default': 
                $schema = $this->defaultSchema;
                $this->labelField = 'data_file_id';
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
     * Crea un HtmldocEmotionalScore relacionando al DataFile
     */
    
    public function pushScore(DataFile $dataFile,$score){
        if($this->existsScore($dataFile) === false){
            $this->id = null;
            $this->loadArray([
                'data_file_id' => $dataFile->id,
                'score' => $score,
                'id' => null
            ]);
            
            try{
                return $this->store();
            }
            catch (Exception $e){
                error_log("HtmldocEmotionalScore::pushScore() - {$e->getMessage()}");
                return false;
            }
        }
        
        return true;
    }
    
    public function existsScore(DataFile $dataFile){
        $cnd = [];
        $cnd['HtmldocEmotionalScore.data_file_id'] = $dataFile->id;
        
        $opts = [];
        $opts['conditions'] = $cnd;
        $data = $this->find('first',$opts);
        
        if($data){
            $alias = $this->alias;
            $blob = $data[$alias];
            $this->loadArray($blob);
            return true;
        }
        
        return false;
    }
}