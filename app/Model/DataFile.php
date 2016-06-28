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
    
    private $checksumSchema = [
        'checksum' => array(
            'type' => 'text',
            'required' => true,
            'unique' => true,
            'label' => 'Checksum',
            'writable' => false,
            'readable' => true,
        ),
    ];
    
    public function shiftSchema($which){
        $this->schema = $which;
    }

    public function getSchema() {
        switch($this->schema){
            case 'default': 
                $schema = $this->defaultSchema;
                $this->labelField = 'meta_data_file_id';
                return $schema;
            case 'checksum': 
                $schema = $this->checksumSchema;
                $this->labelField = 'checksum';
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
     *      * file: representacion binaria del documento a ser almacenado, proviene
     *          del cuerpo de la respuesta HTTP realizada
     *      * checksum : hash unico por archivo (ahorro de espacio de almacenamiento)
     * 
     * Si el checksum ya se encuentra almacenado, carga el modelo con este registro.
     */
    
    public function createData(HttpClientComponent $Http){
        $response = false;
        
        $data = [];
        $data['file'] = $Http->getResponse();
        $data['id'] = null;
        
        $this->id = null;
        $this->loadArray($data);
        
        $checksum = $this->getChecksum($Http->getSize());
        $this->Data()->write('checksum',$checksum);
        
        if($this->loadChecksum($checksum)){
            $this->Data()->write('id',$this->id);
            $response = true;
        }
        else if($this->store()){
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
    
    /* Obtiene el checksum del documento almacenado.
     * La resolucion del checksum es: md5 del archivo concatenando con la entropia
     * para evitar colisiones, la implementacion de este modelo incluyo el size
     * del archivo como entropia.
     *  */
    
    public function getChecksum($entropy){
        $bin = $this->Data()->read('file');
        $hash = md5($bin) . $entropy;
        
        return $hash;        
    }
    
    /* Obtiene el archivo almacenado */
    
    public function getFile(){
        $bin = $this->Data()->read('file');
        $file = $bin;
        
        return $file;
    }
    
    /* 
     * Recupera el archivo de un metadata.
     * Esta funcion se deja por motivos de legacy, pero se acopla a la nueva
     * especificacion de BD para ahorro de almacenamiento.
     **/
    
    public function loadFromMeta($meta_data_id){
        $MetaDataFile = new MetaDataFile();
        
        if($MetaDataFile->loadFromId($meta_data_id) === false){
            return false;
        }
        
        $data_file_id = $MetaDataFile->Data()->read('data_file_id');
        return $this->loadFromId($data_file_id);
    }
    
    /* Recupera un archivo por su checksum */
    
    public function loadChecksum($checksum){
        $cnd = [];
        $cnd['DataFile.checksum'] = $checksum;
        
        $data = $this->find('first', [
            'conditions' => $cnd
        ]);
        
        if($data){
            $alias = $this->alias;
            $blob = $data[$alias];
            $this->loadArray($blob);
            return true;
        }
        
        return false;
    }
    
    /* Almacena el checksum del campo data recien almacenado en el modelo DataFile,
     * este modelo se pasa por parametro. El checksum se consigue ejectuando
     * la funcion DataFile::getChecksum() */
    
    public function bindChecksum(DataFile $dataFile){
        $checksum = $dataFile->getChecksum();
        $this->Data()->write('checksum',$checksum);
        $response = false;
        
        if($this->store()){
            $response = true;
        }
        
        return $response;
    }
}