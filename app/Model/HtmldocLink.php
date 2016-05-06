<?php

App::uses('Model', 'MetaDataFile');
App::uses('Model', 'DataFile');

class HtmldocLink extends AppModel {    
    public $useDbConfig = 'analysis';
    private $schema = 'default';    
    
    private $defaultSchema = [
        'meta_data_file_id' => array(
            'type' => 'foreign',
            'required' => true,
            'unique' => false,
            'label' => 'Meta Data',
            'writable' => false,
            'readable' => true,
            'class' => 'MetaDataFile'
        ),
        'urls' => array(
            'required' => true,
            'type' => 'text',
            'csv' => true,
            'label' => 'URLs',
            'writable' => true,
            'readable' => true,
            'maxlength' => 2000,
        ),
        'hashes' => array(
            'required' => true,
            'type' => 'text',
            'csv' => true,
            'label' => 'Hashes',
            'writable' => true,
            'readable' => true,
            'maxlength' => 32,
        ),
    ];

    public function getSchema() {
        switch($this->schema){
            case 'default': 
                $schema = $this->defaultSchema;
                $this->labelField = 'meta_data_file_id';
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
    
    /* Crea el listado de Links del MetaDataFile explorado, el array de hashes
     * se cargar en la fase final de analisis de htmldoclink */
    
    public function createDoclink(MetaDataFile $MetaData){
        $data = [];
        $data['meta_data_file_id'] = $MetaData->id;
        $data['id'] = null;
        
        $this->id = null;
        $this->loadArray($data);
        return $this->store();
    }
}