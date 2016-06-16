<?php

App::uses('Model', 'MetaDataFile');

class HtmldocFullText extends AppModel {    
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
        'h1' => array(
            'required' => false,
            'type' => 'text',
            'label' => 'Encabezado',
            'writable' => false,
            'readable' => true,
        ),
        'title' => array(
            'required' => false,
            'type' => 'text',
            'label' => 'Titulo',
            'writable' => false,
            'readable' => true,
        ),
        'doctext' => array(
            'required' => false,
            'type' => 'text',
            'label' => 'Texto',
            'writable' => false,
            'readable' => true,
        ),
    ];

    public function getSchema() {
        switch($this->schema){
            case 'default': 
                $schema = $this->defaultSchema;
                $this->labelField = 'h1';
                return $schema;
        }
        
        return array();
    }
    
    private $labelField;

    public function getLabelField(){
        return $this->labelField;
    }
    
    public function getIcon(){
        return 'search';
    }

    public function getLogicalField() {
        return null;
    }

    public function isPhysicalDeletor() {
        return true;
    }
    
    /**
     * Carga el Full Text del meta_data_file_id proporcionado
     */
    
    public function loadMeta($metaDataId){
        $cnd = [];
        $cnd['HtmldocFullText.meta_data_file_id'] = $metaDataId;
        
        $data = $this->find('first',[
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
    
    /** Update TSV
     
        UPDATE analysis.htmldoc_full_texts SET tsv = setweight(to_tsvector(coalesce(h1,'')), 'A') || setweight(to_tsvector(coalesce(title,'')), 'B') || setweight(to_tsvector(coalesce(doctext,'')), 'C');

    */
    
    public function updateTsv(){
        $db = $this->getDataSource();
        $sql = "UPDATE analysis.htmldoc_full_texts
                    SET tsv =
                           setweight(to_tsvector(coalesce(h1,'')), 'A') || 
                           setweight(to_tsvector(coalesce(title,'')), 'B') ||
                           setweight(to_tsvector(coalesce(doctext,'')), 'C') 
                   WHERE id = ? ";
        
        $res =  $db->fetchAll($sql,[ $this->id ]);
        return  $res === false ? false : true;
    }
    
    /** FTS Query
     
        SELECT id, title, h1, tsv
        FROM analysis.htmldoc_full_texts, plainto_tsquery('animal') AS q
        WHERE (tsv @@ q) ORDER BY ts_rank_cd(tsv, plainto_tsquery('segundo post')) DESC LIMIT 20;
     
     */
    
    public function searchAll($term){
        $db = $this->getDataSource();
        $sql = "SELECT id, meta_data_file_id, title, h1, tsv
                FROM analysis.htmldoc_full_texts, plainto_tsquery(?) AS q
                WHERE (tsv @@ q) ORDER BY ts_rank_cd(tsv, plainto_tsquery(?)) DESC LIMIT 20";
        
        return $db->fetchAll($sql,[ $term, $term ]);
    }
}