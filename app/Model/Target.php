<?php

class Target extends AppModel {    
    public $useDbConfig = 'backend';
    private $schema = 'default';    
    private $defaultSchema = [
        'name' => array(
            'required' => true,
            'type' => 'text',
            'unique' => true,
            'label' => 'Nombre Comun',
            'writable' => true,
            'readable' => true,
            'maxlength' => 30,
        ),
        'url' => array(
            'required' => true,
            'type' => 'url',
            'unique' => true,
            'label' => 'URL',
            'writable' => true,
            'readable' => true,
            'maxlength' => 200,
        ),
        'exploration_frequency' => array(
            'type' => 'int',
            'label' => 'Frec. Exploracion (dias)',
            'required' => true,
            'writable' => true,
            'readable' => true,
        ),
        'last_crawl' => array(
            'type' => 'date',
            'label' => 'Ultima Exploracion',
            'writable' => false,
            'readable' => true,
            'null' => 'No se ha explorado'
        ),
    ];

    public function getSchema() {
        switch($this->schema){
            case 'default': 
                $schema = $this->defaultSchema;
                $this->labelField = 'name';
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

    private static $logicalField = 'status';
    
    public function getLogicalField() {
        return self::$logicalField;
    }

    public function isPhysicalDeletor() {
        return false;
    }
    
    /* Obtiene los Targets disponibles para crawling. */
    
    public function findAvailableTargets(){        
        $sql = 'SELECT * from backend.targets AS Target '
            . 'WHERE '
                . '( '
                    . '(Target.last_crawl + Target.exploration_frequency) <= current_date '
                    . 'OR '
                    . 'Target.last_crawl IS NULL'
                . ') '
            . 'AND '
                . 'Target.status = true';

        $rawdata = $this->query($sql);
        $targets = [];
        
        foreach ($rawdata as $blob){
            $targets[] = [
                'Target' => $blob[0]
            ];
        }
        
        return $targets;
    }
    /* Obtiene los Targets habilitados. */
    
    public function findTargets(){
        $cnd = [];
        
        $joins = [];
        $joins[] = 'INNER JOIN crawler.crawler_logs AS "CrawlerLog" ON target_id = "Target".id AND "CrawlerLog".status = \'done\'';
        
        $group = 'Target.id';
        $fields = 'Target.*, count(*) as histories';
        
        $targets = $this->find('all',[
            'order' => 'Target.name',
            'conditions' => $cnd,
            'joins' => $joins,
            'group' => $group,
            'fields' => $fields
        ]);
        
        return $targets;
    }
    
    /* Inicializa el Crawler, setea las fecha correctas para last_crawl y first_crawl */
    
    public function setCrawling(){
        $now = date('Y-m-d H:i:s');
        
        if(is_null($this->Data()->read('first_crawl'))){
            $this->Data()->write('first_crawl',$now);
        }
        
        $this->Data()->write('last_crawl',$now);
        return $this->store();
    }
}