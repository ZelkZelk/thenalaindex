<?php

App::uses('Model', 'MetaDataFile');
App::uses('HttpClientComponent', 'Controller/Component');

class DataFile extends AppModel {    
    public $useDbConfig = 'crawler';
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
        'file' => array(
            'type' => 'bytea',
            'required' => true,
            'unique' => false,
            'label' => 'Archivo',
            'writable' => false,
            'readable' => true,
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
        return 'file-o';
    }

    public function getLogicalField() {
        return null;
    }

    public function isPhysicalDeletor() {
        return true;
    }
    
    /* Crea el registro DataFile con los siguientes campos:
     * 
     *      * meta_data_file_id : Id del modelo MetaDataFile enviado como parametro
     *      * file: representacion binaria del documento a ser almacenado, proviene
     *          del cuerpo de la respuesta HTTP realizada
     */
    
    public function createData(MetaDataFile $MetaData, HttpClientComponent $Http){
        $response = false;
        
        $data = [];
        $data['meta_data_file_id'] = $MetaData->id;
        $data['file'] = $Http->getResponse();
        $data['id'] = null;
        
        $this->id = null;
        $this->loadArray($data);
        
        if($this->store()){
            $this->Data()->write('id',$this->id);
            $response = true;
        }
        
        return $response;
    }
    
    /* Actualiza el File contenido en el Modelo */
    
    public function updateFile($file){
        $response = false;
        $this->Data()->write('file',$file);
        
        if($this->store()){
            $this->Data()->write('id',$this->id);
            $response = true;
        }
        
        return $response;
    }
    
    /* Obtiene el checksum del documento almacenado */
    
    public function getChecksum(){
        $bin = $this->Data()->read('file');
        $hash = md5($bin);
        
        return $hash;        
    }
    
    /* Obtiene el archivo almacenado */
    
    public function getFile(){
        $bin = $this->Data()->read('file');
        $file = $bin;
        
        return $file;
    }
    
    /* Recupera el archivo de un metadata */
    
    public function loadFromMeta($meta_data_id){
        $cnd = [];
        $cnd['DataFile.meta_data_file_id'] = $meta_data_id;
        
        $data = $this->find('first', [
            'conditions' => $cnd
        ]);
        
        if($data){
            $alias = $this->alias;
            $blob = $data[$alias];
            $this->loadArray($blob);
        }
        
        return $data;
    }
}