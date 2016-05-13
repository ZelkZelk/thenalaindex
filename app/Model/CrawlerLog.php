<?php

App::uses('Model','Target');

class CrawlerLog extends AppModel {    
    public $useDbConfig = 'crawler';
    private $schema = 'default';    
    
    private $defaultSchema = [
        'target_id' => array(
            'type' => 'foreign',
            'required' => true,
            'unique' => true,
            'label' => 'Sitio',
            'writable' => false,
            'readable' => true,
            'class' => 'Target'
        ),
        'status' => array(
            'type' => 'options',
            'options' => [
                'execution' => 'En Ejecucion',
                'done' => 'Finalizado',
                'failed' => 'Fallido',
            ],
            'label' => 'Estado',
            'writable' => false,
            'readable' => true,
            'default' => 'execution'
        ),
        'starting' => array(
            'type' => 'datetime',
            'label' => 'Comienzo',
            'writable' => false,
            'readable' => true,
        ),
        'ending' => array(
            'type' => 'datetime',
            'label' => 'Fin',
            'writable' => false,
            'readable' => true,
        ),
        'http_petitions' => array(
            'type' => 'int',
            'label' => 'Peticiones',
            'writable' => false,
            'readable' => true,
            'default' => 0
        ),
        'http_errors' => array(
            'type' => 'int',
            'label' => 'Errores',
            'writable' => false,
            'readable' => true,
            'default' => 0
        ),
        'html_crawled' => array(
            'type' => 'int',
            'label' => 'Paginas',
            'writable' => false,
            'readable' => true,
            'default' => 0
        ),
        'js_crawled' => array(
            'type' => 'int',
            'label' => 'Scripts',
            'writable' => false,
            'readable' => true,
            'default' => 0
        ),
        'css_crawled' => array(
            'type' => 'int',
            'label' => 'Hojas de Estilo',
            'writable' => false,
            'readable' => true,
            'default' => 0
        ),
        'img_crawled' => array(
            'type' => 'int',
            'label' => 'Imagenes',
            'writable' => false,
            'readable' => true,
            'default' => 0
        ),
        'log' => array(
            'type' => 'text',
            'label' => 'Traza',
            'writable' => false,
            'readable' => false,
        ),
    ];

    public function getSchema() {
        switch($this->schema){
            case 'default': 
                $schema = $this->defaultSchema;
                $this->labelField = 'target_id';
                return $schema;
        }
        
        return array();
    }
    
    private $labelField;

    public function getLabelField(){
        return $this->labelField;
    }
    
    public function getIcon(){
        return 'calendar';
    }

    public function getLogicalField() {
        return null;
    }

    public function isPhysicalDeletor() {
        return true;
    }
        
    /* Crea un Log por defecto. */
    
    public function createEmpty(Target $target){
        $array = [];
        
        foreach($this->getSchema() as $field => $meta){
            if(isset($meta['default'])){
                $array[$field] = $meta['default'];
            }
        }
        
        $array['http_petitions'] = 0;
        $array['http_errors'] = 0;
        $array['html_crawled'] = 0;
        $array['js_crawled'] = 0;
        $array['css_crawled'] = 0;
        $array['img_crawled'] = 0;
        $array['target_id'] = $target->id;
        $array['starting'] = date('Y-m-d H:i:s');
        $this->loadArray($array);
        $this->id = null;
        
        if($this->store()){
            $this->loadForeign();
            return true;
        }
        
        return false;
    }
    
    /* Establece el Log como fallido */
    
    public function failed(){
        $this->Data()->write('status','failed');
        $this->Data()->write('ending',date('Y-m-d H:i:s'));
        return $this->store();
    }
    
    /* Establece el Log como finalizado*/
    
    public function finished(){
        $this->Data()->write('status','done');
        $this->Data()->write('ending',date('Y-m-d H:i:s'));
        return $this->store();
    }
    
    
    /* Un array que mantiene la traza del log */
    
    private $trace = [];
    
    public function appendTrace($message){
        $this->trace($message);
        $this->commit();
    }
    
    public function trace($message){
        $this->trace[] = $message;
    }
    
    public function commit(){
        $this->Data()->write('log',serialize($this->trace));
    }
    
    /* Crea un archivo temporal conteniendo el array $trace, una linea por elemento. */
    
    public function getTempTrace(){
        $file = '/tmp/' . uniqid() . '.txt';
        $fd = fopen($file, 'w');
        
        if($fd){        
            foreach ($this->trace as $str){
                fwrite($fd, $str . "\n");
            }
            
            return $file;
        }
        
        return false;
    }
    
    /* Elimina el archivo temporal */
    
    public function deleteTempTrace($file){
        return unlink($file);
    }
    
    /* Aumenta el contador del campo indicado */
    
    public function increment($field){
        $value = (int) $this->Data()->read($field);
        $value += 1;
        
        $this->Data()->write($field,$value);
    }
}