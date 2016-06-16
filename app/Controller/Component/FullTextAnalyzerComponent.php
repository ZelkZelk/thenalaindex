<?php

App::uses('CrawlerUtilityComponent', 'Controller/Component');
App::uses('ScrapperComponent', 'Controller/Component');
App::uses('UrlNormalizerComponent', 'Controller/Component');
App::uses('ComponentCollection', 'Controller');

App::import('Model', 'CrawlerLog');
App::import('Model', 'MetaDataFile');
App::import('Model', 'DataFile');

/* Este componente se encarga de recolectar informacion de Full Text Search
 * de los documents HTML almacenados
 **/

class FullTextAnalyzerComponent extends CrawlerUtilityComponent{
    private $MetaDataFile;
    private $DataFile;
    private $CrawlerLog;
    
    
    /* Override de la funcion generica para agregar el TAG FTS
     */
    
    private static $TAG = '[FTS]';
    
    private function logAnalyzer($message){
        $full = self::$TAG . ' ' . $message;
        $this->logInfo($full);
    }
    
    /* Llamar al inicializador antes de empezar el proceso */
    
    public function init(CrawlerLog $log,$logFunction) {  
        $this->setLogFunction($logFunction);
        $this->setCrawlerLog($log);
        
        $this->MetaDataFile = new MetaDataFile();
        $this->DataFile = new DataFile();
        $this->CrawlerLog = new CrawlerLog();
    }   
    
    
    /* Se encarga de ciclar todos los metadatas en busca de documentos HTML,
     * luego busca el titulo, h1 y desnuda el HTML para almacenar solo las porciones
     * de texto relevantes.
     */
    
    public function scanner($id){
        if($this->CrawlerLog->loadFromId($id)){
            $this->scannerSetup();
            $this->pagedScanner($id);
        }
        else{
            $this->logAnalyzer("CRAWLER<$id,NOT FOUND>");
        }
    }
    
    /* Carga los modelos e inicializa los componentes requeridos para el analisis */
    
    private function scannerSetup(){
        Configure::load('analysis');
        $this->limit = Configure::read('Analysis.full_text_page_limit');  
        
        $this->initScrapper();
    }
    
    
    /* Inicializa el Scrapper */
    
    private $Scrapper;

    private function initScrapper(){
        $collection = new ComponentCollection();
        $this->Scrapper = new ScrapperComponent($collection);
        
        CakeLog::config('fts-scrapper', array(
            'engine' => 'FileLog',
            'types' => [ 'info' ],
            'scopes' => [ 'analyzer-scrapper' ],
            'file' => 'analyzer-scrapper'
        ));    
        
        $this->Scrapper->init($this->getCrawlerLog(),function($message){
            CakeLog::write('info', $message, 'fts-scrapper');
        });        
    }
    
    /* Realiza el Scan paginando la info para evitar ocupar mucha memoria */
    
    private $limit;
    
    private function pagedScanner($id){
        $alias = $this->MetaDataFile->alias;
        $limit = $this->limit;
        $offset = 0;
        
        do{
            $data = $this->MetaDataFile->getHtmldocCrawled($id, $limit, $offset);
            $count = count($data);
            $offset += $count;
            
            foreach($data as $metaData){
                $blob = $metaData[$alias];
                $this->MetaDataFile->loadArray($blob);
                $this->fullTextScan();
            }
            
        } while($count === $limit);
    }
    
    /**
     * Realiza el scaneo de texto completo del documento HTML
     */
    
    private function fullTextScan(){
        $id = $this->MetaDataFile->id;
        
        if($this->MetaDataFile->isHtml()){
            if($this->loadDataFile() === false){
                $this->logAnalyzer("DATAFILE<$id,NOT FOUND>");
                return false;
            }
            
            echo "{$this->Scrapper->getTitle()} {$this->Scrapper->getH1()}\n";
            $this->Scrapper->clear();
        }
        
        return true;
    }
    
    /* Carga el archivo HTML en memoria */
    
    private function loadDataFile(){
        $id = $this->MetaDataFile->id;
        $response = true;
        
        if($this->DataFile->loadFromMeta($id) === false){
            $response = false;
        }
        
        return $response;
    }
}