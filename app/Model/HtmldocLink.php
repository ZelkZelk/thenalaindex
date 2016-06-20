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
     * se cargar en la fase final de analisis de htmldoclink.
     * 
     * Funcion legacy, para ahorro de espacio de almacenamiento ahora este
     * modelo se relaciona directamente con DataFile.
     * 
     * Si ya existe el data_file_id lo carga, en vez de crear uno nuevo.
     **/
    
    public function createDoclink(MetaDataFile $MetaData){
        $dataFileId = $MetaData->Data()->read('data_file_id');
        
        if($this->loadDataFile($dataFileId)){
            return true;
        }
        
        $data = [];
        $data['data_file_id'] = $dataFileId;
        $data['id'] = null;
        
        $this->id = null;
        $this->loadArray($data);
        return $this->store();
    }
    
    /**
     * Carga el modelo por DataFileId
     */
    
    public function loadDataFile($dataFileId){
        $cnd = [];
        $cnd['HtmldocLink.data_file_id'] = $dataFileId;
        $response = false;
        
        $data = $this->find('first',[
            'conditions' => $cnd
        ]);
        
        if($data){
            $alias = $this->alias;
            $blob = $data[$alias];
            $this->loadArray($blob);
            $response = true;
        }
        
        return $response;
    }
    
    /* Carga el modelo basado en el MetaDataFile.
     * Funcion legacy, para ahorro de espacio de almacenamiento ahora este
     * modelo se relaciona directamente con DataFile
     **/
    
    public function loadFromMeta(MetaDataFile $MetaData){
        $dataFileId = $MetaData->Data()->read('data_file_id');
        return $this->loadDataFile($dataFileId);
    }
    
    /* Actualiza los hashes del registro actual */
    
    public function updateHashes(array $hashes = []){
        $this->setHashes($hashes);        
        return $this->store();
    }
    
    /* Setea la lista de hashes desde un array simple */
    
    protected function setHashes(array $hashes = []){        
        if(empty($hashes)){
            $rawHashes = '{}';
        }
        else{            
            $csvHashes = implode(',', $hashes);  
            $fixHashes = preg_replace('/{|}/','',$csvHashes);   
            $trueCsvHashes = preg_replace('/^,/','',$fixHashes);   
            $rawHashes = '{' . $trueCsvHashes . '}';
        }
        
        $this->Data()->write('hashes',$rawHashes);
    }
}