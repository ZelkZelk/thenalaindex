<?php

App::uses('Model', 'CrawlerLog');
App::uses('Model', 'DataFile');
App::uses('Model', 'Url');

App::uses('HttpClientComponent', 'Controller/Component');

class MetaDataFile extends AppModel {    
    public $useDbConfig = 'crawler';
    private $schema = 'default';    
    
    private $defaultSchema = [
        'url_id' => array(
            'type' => 'foreign',
            'required' => true,
            'unique' => false,
            'label' => 'URL',
            'writable' => false,
            'readable' => true,
            'class' => 'Url'
        ),
        'crawler_log_id' => array(
            'type' => 'foreign',
            'required' => true,
            'unique' => false,
            'label' => 'Crawler Log',
            'writable' => false,
            'readable' => true,
            'class' => 'CrawlerLog'
        ),
        'mime' => array(
            'type' => 'text',
            'required' => true,
            'unique' => false,
            'label' => 'MIME',
            'writable' => false,
            'readable' => true,
        ),
        'size' => array(
            'type' => 'int',
            'required' => true,
            'unique' => false,
            'label' => 'Bytes',
            'writable' => false,
            'readable' => true,
        ),
        'hash' => array(
            'type' => 'text',
            'required' => false,
            'unique' => true,
            'label' => 'Hash',
            'writable' => false,
            'readable' => true,
        ),
        'checksum' => array(
            'type' => 'text',
            'required' => true,
            'unique' => false,
            'label' => 'Checksum',
            'writable' => false,
            'readable' => true,
        ),
        'last_modified' => array(
            'type' => 'datetime',
            'required' => false,
            'unique' => false,
            'label' => 'Fecha Documento',
            'writable' => false,
            'readable' => true,
        ),
        'created' => array(
            'type' => 'datetime',
            'required' => true,
            'unique' => false,
            'label' => 'Fecha Crawling',
            'writable' => false,
            'readable' => true,
        ),
    ];

    public function getSchema() {
        switch($this->schema){
            case 'default': 
                $schema = $this->defaultSchema;
                $this->labelField = 'url';
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
    
    /* Analiza la metadata del componente HTTP pasado crea un array hash con
     * valores obtenidos del analisis de cabeceras de dicho componente.
     * 
     * Los valores que setea son mime, size y last_modified
     * 
     *      * mime: extraccion directa de la cabecera Content-Type
     *      * size: extraccion directa de la cabecera Content-Length, tambien
     *          puede usarse HTTP::getSize() en caso de falla, ambos deberian
     *          coincidir siempre.
     *      * last_modified: se extrae de la cabecera Last-Modified, sino existe
     *          se utiliza Date, si no existe, se establece como NULL
     *      * url_id: es el id del modelo $Url para tener en cuenta la referencia
     *          de las cabeceras a cual URL corresponde.
     */
    
    public function headerAnalysis(Url $Url,HttpClientComponent $Http){
        $headerData = [];
        $headerData['mime'] = $this->extractMime($Http);
        $headerData['size'] = $this->extractSize($Http);
        $headerData['last_modified'] = $this->extractLastModified($Http);
        $headerData['url_id'] = $Url->id;
        
        return $headerData;
    }
    
    /* Extrae el Content-Type de las cabeceras de respuesta HTTP, si no existe 
     * se usa el valor por defecto application/octet-stream.
     */
    
    private static $DEFAULT_MIME = 'application/octet-stream';
    private static $CONTENT_TYPE = 'Content-Type';
    private static $CONTENT_TYPE_LEN = 100;

    private function extractMime(HttpClientComponent $Http){
        $contentType = self::$DEFAULT_MIME;
        
        if($Http->checkHeader(self::$CONTENT_TYPE)){
            $contentType = substr($Http->readHeader(self::$CONTENT_TYPE),0, self::$CONTENT_TYPE_LEN);
        }
        
        return $contentType;
    }
    
    /* Extrae el Content-Length de las cabeceras de respuesta HTTP, si no existe
     * se llama a Http::getSize()
     */
    
    private static $CONTENT_LENGTH = 'Content-Length';
    
    private function extractSize(HttpClientComponent $Http){
        $size = $Http->getSize();
        
        if($Http->checkHeader(self::$CONTENT_LENGTH)){
            $size = $Http->readHeader(self::$CONTENT_LENGTH);
        }
        
        return $size;        
    }
    
    /* Extra la cabecera Last-Modified, si no existe extrae Date, si no existiese,
     * se carga con la fecha actual por defecto.
     */
    private static $LAST_MODIFIED = 'Last-Modified';
    private static $DATE = 'Date';
    
    private function extractLastModified(HttpClientComponent $Http){
        $timestamp = time();
        
        if($Http->checkHeader(self::$LAST_MODIFIED)){
            $timestamp = strtotime($Http->readHeader(self::$LAST_MODIFIED));
        }
        else if($Http->checkHeader(self::$DATE)){
            $timestamp = strtotime($Http->readHeader(self::$DATE));
        }
        
        $lastModified = date('Y-m-d H:i:s',$timestamp);
        return $lastModified;        
    }
    
    /* Crea un metadata nuevo, requiere la info analizada desde las cabeceras
     * ademas del DataFile.
     * 
     *      * mime: se extrae de la info de cabeceras
     *      * size: idem
     *      * last_modified: idem
     *      * created: se setea la fecha actual
     *      * hash: es creado automaticamente por la BD, notese que aunque 
     *          el esquema indica required => false, en realidad la BD especifica
     *          un CONSTRAINT NOT NULL. 
     *      * checksum: se incorpora el checksum pendiente default, notese que luego sera
     *          actualizado al almacenar el DataFile, ver MetaDataFile::bindChecksum()
     * 
     *  La siguiente funcion alimenta al campo hash.
     *  PSQL: crawler.crawler_hash_generator()
     * 
        DECLARE
            our_epoch bigint := 1314220021721;
            seq_id bigint;
            now_millis bigint;
        BEGIN
            SELECT nextval('crawler.crawler_hash_seq') % 1024 INTO seq_id;

        SELECT FLOOR(EXTRACT(EPOCH FROM clock_timestamp()) * 1000) INTO now_millis;
            result := (now_millis - our_epoch) << 23;
            result := result | (table_id << 10);
            result := result | (seq_id);
        END;
     * 
     */
    
    public function createMetaData(CrawlerLog $Log,$headerData = []){
        if($this->loadHash($headerData['hash']) === true){
            return true;
        }
        
        if($this->load($Log->id,$headerData['url_id']) === true){
            return true;
        }
        
        $data = $headerData;
        $data['crawler_log_id'] = $Log->id;
        $data['created'] = date('Y-m-d H:i:s');
        $data['id'] = null;
        $this->id = null;
        
        $this->loadArray($data);
        return $this->store();
    }
    
    /* Carga el registro segun la tupla Crawler:Url */
    
    private function load($crawler_log_id,$url_id){
        $cnd = [];
        $cnd['MetaDataFile.crawler_log_id'] = $crawler_log_id;
        $cnd['MetaDataFile.url_id'] = $url_id;
        
        $row = $this->find('first',[
            'conditions' => $cnd
        ]);
        
        if($row){
            $blob = $row['MetaDataFile'];
            $this->loadArray($blob);
            return true;
        }
        
        return false;
    }
    
    /* Relaciona el meta data file con el archivo almacenado en  DataFile. */
    
    public function bindFile(DataFile $dataFile){
        $dataFileId = $dataFile->id;
        $this->Data()->write('data_file_id',$dataFileId);
        $response = false;
        
        if($this->store()){
            $response = true;
        }
        
        return $response;
    }
    
    /* Determina si es un documento HTML basado en el analisis del campo mime.
     * Debe contener text/html para ser considerado. */
    
    private static $HTML_MIME = 'text/html';
    
    public function isHtml(){
        $mime = $this->Data()->read('mime');
        $response = true;
        
        if(strstr($mime, self::$HTML_MIME) === false){
            $response = false;
        }
        
        return $response;
    }
    
    /* Determina si es una imagen en el analisis del campo mime.
     * Debe contener image/ para ser considerado. */
    
    private static $IMAGE_MIME = 'image/';
    
    public function isImage(){
        $mime = $this->Data()->read('mime');
        $response = true;
        
        if(strstr($mime, self::$IMAGE_MIME) === false){
            $response = false;
        }
        
        return $response;
    }
    
    /* Determina si es un javascript basado en el analisis del campo mime.
     * Debe contener text/javascript para ser considerado. */
    
    private static $JS_MIME = 'text/javascript';
    
    public function isScript(){
        $mime = $this->Data()->read('mime');
        $response = true;
        
        if(strstr($mime, self::$JS_MIME) === false){
            $response = false;
        }
        
        return $response;
    }
    
    /* Determina si es un css basado en el analisis del campo mime.
     * Debe contener text/javascript para ser considerado. */
    
    private static $CSS_MIME = 'text/css';
    
    public function isStylesheet(){
        $mime = $this->Data()->read('mime');
        $response = true;
        
        if(strstr($mime, self::$CSS_MIME) === false){
            $response = false;
        }
        
        return $response;
    }
    
    /* Obtiene los Meta Datos de un determinado proceso de Crawling */
    
    public function getHtmldocCrawled($crawler_log_id,$limit = 100,$pivot = 0){
        $cnd = [];
        $cnd['MetaDataFile.crawler_log_id'] = $crawler_log_id;
        $cnd['MetaDataFile.id > '] = $pivot;
        
        $opts = [];
        $opts['conditions'] = $cnd;
        $opts['limit'] = $limit;
        $opts['order'] = 'MetaDataFile.id asc';
        
        return $this->find('all',$opts);
    }
    
    /* Obtiene el hash de un par url/crawler */
    
    public function getUrlHash($crawler_log_id,$url_id){
        $alias = $this->alias;
            
        $cnd = [];
        $cnd['MetaDataFile.crawler_log_id'] = $crawler_log_id;
        $cnd['MetaDataFile.url_id'] = $url_id;
        
        $data = $this->find('first',[
            'conditions' => $cnd
        ]);
            
        if(isset($data[$alias]['hash'])){
            $hash = trim($data[$alias]['hash']);
            return $hash;
        }
        
        return false;
    }
    
    /* Carga un Meta Data desde su Hash */
    
    public function loadHash($hash){
        $cnd = [];
        $cnd['MetaDataFile.hash'] = $hash;
        
        $data = $this->find('first', [
            'conditions' => $cnd
        ]);
        
        if($data){
            $alias = $this->alias;
            $blob = $data[$alias];
            $this->loadArray($blob);
        }
        
        return $data ? true : false;
    }
    
    /**
     * Obtiene el hash del meta data file
     */
    
    public function getHash(){
        $cnd = [];
        $cnd['MetaDataFile.id'] = $this->id;
        
        $data = $this->find('first',[
            'conditions' => $cnd,
            'fields' => 'MetaDataFile.hash'
        ]);
        
        if($data){
            $hash = $data['MetaDataFile']['hash'];
            return $hash;
        }
        
        return false;
    }
    /**
     * Obtiene el primer hash relacionado al CrawlerLog
     */
    
    public function getFirstHash(CrawlerLog $Log){
        $cnd = [];
        $cnd['MetaDataFile.crawler_log_id'] = $Log->id;
        
        $data = $this->find('first',[
            'conditions' => $cnd,
            'fields' => 'MetaDataFile.hash',
            'order' => 'MetaDataFile.id asc'
        ]);
        
        if($data){
            $hash = $data['MetaDataFile']['hash'];
            return $hash;
        }
        
        return false;
    }
    
    /* Dado el resultado de HtmldocFullText.searchAll busca el MetaData mas
     * reciente para cada item encontrado.
     * 
     * Ver HtmldocFullText.searchAll
     */
    
    public function findByFts($ids){
        $results = [];
        
        if(is_array($ids) === false){
            return $results;
        }
        
        foreach($ids as $blob){
            $id = $blob[0]['data_file_id'];
            
            if($this->loadRecentFile($id)){
                $meta = $this->Data()->dump();
                $results[] = array_merge($meta,$blob[0]);
            }
        }
        
        return $results;
    }
    
    /* Carga el MetaData mas reciente que coincida con el DataFile.id */
    
    private function loadRecentFile($id){
        $cnd = [];
        $cnd['MetaDataFile.data_file_id'] = $id;
        
        $joins = [];
        $joins[] = 'INNER JOIN crawler.urls AS "Url" ON "Url".id="MetaDataFile".url_id';
        $joins[] = 'INNER JOIN backend.targets AS "Target" ON "Target".id="Url".target_id';
        
        $meta = $this->find('first',[
            'conditions' => $cnd,
            'joins' => $joins,
            'fields' => 'MetaDataFile.*, Url.full_url, Target.id, Target.name'
        ]);
        
        if($meta){
            $alias = $this->alias;
            $blob = $meta[$alias];
            $this->loadArray($blob);
            
            $full_url = $meta['Url']['full_url'];
            $target_id = $meta['Target']['id'];
            $target = $meta['Target']['name'];
            $this->Data()->write('full_url',$full_url);
            $this->Data()->write('target_id',$target_id);
            $this->Data()->write('target',$target);
            return true;
        }
        
        return false;
    }
}