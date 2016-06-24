<?php

App::uses('Model', 'MetaDataFile');
App::uses('Model', 'DataFile');


class HtmldocFullText extends AppModel {    
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
     * Carga el Full Text del Data File Id proporcionado.
     */
    
    public function loadFile($dataFileId){
        $cnd = [];
        $cnd['HtmldocFullText.data_file_id'] = $dataFileId;
        
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
     
        SELECT data_file_id
        FROM analysis.htmldoc_full_texts, plainto_tsquery('animal') AS q
        WHERE (tsv @@ q) ORDER BY ts_rank_cd(tsv, plainto_tsquery('segundo post')) DESC 
        LIMIT 20 OFFSET 0;
     
        Devuelve solo el ID de los data_file_id.
     */
    
    public function searchAll($term,$offset = 0){
        Configure::load('fts');
        $limit = Configure::read('FTS.limit');
        
        $db = $this->getDataSource();
        $sql = "SELECT data_file_id, h1, title, doctext
                FROM analysis.htmldoc_full_texts, plainto_tsquery(?) AS q
                WHERE (tsv @@ q) ORDER BY ts_rank_cd(tsv, plainto_tsquery(?)) DESC
                LIMIT ? OFFSET ?";
        
        return $db->fetchAll($sql,[ $term, $term, $limit, $offset ]);
    }
}