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
    
    private static $DEFAULT_CHECKSUM = '<DataFile::pending>';
    
    public function createMetaData(CrawlerLog $Log,$headerData = []){
        $data = $headerData;
        $data['created'] = date('Y-m-d H:i:s');
        $data['checksum'] = self::$DEFAULT_CHECKSUM;
        $data['crawler_log_id'] = $Log->id;
        $data['id'] = null;
        
        $this->id = null;
        $this->loadArray($data);
        return $this->store();
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
    
    /* Obtiene los Meta Datos de un determinado proceso de Crawling */
    
    public function getHtmldocCrawled($crawler_log_id,$limit = 100,$offset = 0){
        $cnd = [];
        $cnd['MetaDataFile.crawler_log_id'] = $crawler_log_id;
        
        $opts = [];
        $opts['conditions'] = $cnd;
        $opts['limit'] = $limit;
        $opts['offset'] = $offset;
        
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
}