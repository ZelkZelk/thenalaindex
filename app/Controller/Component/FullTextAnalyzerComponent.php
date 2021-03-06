<?php

App::uses('CrawlerUtilityComponent', 'Controller/Component');
App::uses('ScrapperComponent', 'Controller/Component');
App::uses('UrlNormalizerComponent', 'Controller/Component');
App::uses('ComponentCollection', 'Controller');

App::import('Model', 'CrawlerLog');
App::import('Model', 'MetaDataFile');
App::import('Model', 'DataFile');
App::import('Model', 'HtmldocFullText');

/* Este componente se encarga de recolectar informacion de Full Text Search
 * de los documents HTML almacenados
 **/

class FullTextAnalyzerComponent extends CrawlerUtilityComponent{
    private $MetaDataFile;
    private $DataFile;
    private $CrawlerLog;
    private $HtmldocFullText;
    
    
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
        $this->HtmldocFullText = new HtmldocFullText();
    }   
    
    
    /* Se encarga de ciclar todos los metadatas en busca de documentos HTML,
     * luego busca el titulo, h1 y desnuda el HTML para almacenar solo las porciones
     * de texto relevantes.
     */
    
    public function scanner($id){
        if($this->CrawlerLog->loadFromId($id)){
            $this->scannerSetup();
            $this->pagedScanner($id);
            $this->flagCrawlerLog($id);
        }
        else{
            $this->logAnalyzer("CRAWLER<$id,NOT FOUND>");
        }
    }
    
    /*
     * Popula el flag del crawler log para establecer que ya se realizo el 
     * analisis correspondiente.
     */
    
    private function flagCrawlerLog(){
        $now = date('Y-m-d H:i:s');
        $this->CrawlerLog->Data()->write('full_text_analyzed',$now);
        
        if($this->CrawlerLog->store()){
            $this->logAnalyzer("FLAG-CRAWLER<$now,DONE>");
        }
        else{
            $this->logAnalyzer("FLAG-CRAWLER<$now,FAIL>");
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
     * Determina si es requerido analizar el DataFile actual
     */
    
    private $analyzed;
    
    private function analysisNeeded(){
        $dataFileId = $this->MetaDataFile->Data()->read('data_file_id');
        $key = $this->getAnalyzedKey($dataFileId);
        
        if(isset($this->analyzed[$key]) === true){
            return false;
        }
        
        return true;
    }
    
    /**
     * Obtiene el key que popula el array $analyzed, desde el dataFileId 
     * proporcionado.
     */
    
    private function getAnalyzedKey($dataFileId){
        return "df$dataFileId";
    }
    
    /**
     * Establece el DataFile actual como analizado.
     */
    
    private function setAnalyzed(){
        $dataFileId = $this->MetaDataFile->Data()->read('data_file_id');
        $key = $this->getAnalyzedKey($dataFileId);
        $this->analyzed[$key] = true;
    }
    
    /**
     * Realiza el scaneo de texto completo del documento HTML
     */
    
    private function fullTextScan(){
        $id = $this->MetaDataFile->id;
        
        if($this->MetaDataFile->isHtml()){
            if($this->analysisNeeded() === false){
                $this->logAnalyzer("DATAFILE<$id,ALREADY ANALYZED>");
                return false;
            }
            
            if($this->loadDataFile() === false){
                $this->logAnalyzer("DATAFILE<$id,NOT FOUND>");
                return false;
            }
            
            $this->scrapFullText();
            $this->populateFullText();
            $this->storeFullText();
            $this->setAnalyzed();
        }
        
        return true;
    }
    
    /**
     * Se encarga de analizar el texto del documento HTML.
     * 
     * [FLUSH] DataFile
     */
    
    private function scrapFullText(){
        $this->Scrapper->scrapFullText($this->DataFile->getFile());
        $this->DataFile->clearFields();
    }
    
    /**
     * Translitera una cadena a Latin UTF-8
     */
    
    public function transliterate($text){
        $transliteration = transliterator_transliterate('Any-Latin; Latin-ASCII',$text);
        $ascii = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", $transliteration);
        return $ascii;
    }
    
    /**
     * Se encarga de almacenar adecuadamente los campos del Full Text.
     * Si el Full Text para el MetaDataFile actual existe, se actualiza.
     * 
     * [FLUSH] Scrapper
     */
    
    private function populateFullText(){
        $dataFileId = $this->MetaDataFile->Data()->read('data_file_id');
        $metaDataId = $this->MetaDataFile->id;
        $h1 = $this->transliterate($this->Scrapper->getH1());
        $title = $this->transliterate($this->Scrapper->getTitle());
        $text = $this->transliterate($this->Scrapper->getText());
        
        if($this->HtmldocFullText->loadFile($dataFileId)){
            $this->logAnalyzer("UPDATING<META:$metaDataId>");
        }
        else{
            $this->HtmldocFullText->id = null;
            $this->HtmldocFullText->Data()->write('data_file_id',$dataFileId);
            $this->logAnalyzer("CREATING<META:$metaDataId>");
        }
        
        if(strlen($text) > 20){
            $logText = substr($text,0,20) . '...';
        }
        else{
            $logText = $text;
        }
        
        $this->logAnalyzer("POPULATING<META:$metaDataId,H1:$h1>");
        $this->logAnalyzer("POPULATING<META:$metaDataId>,TITLE:$title");
        $this->logAnalyzer("POPULATING<META:$metaDataId>,TEXT:$logText");
        
        $this->HtmldocFullText->Data()->write('h1',$h1);
        $this->HtmldocFullText->Data()->write('title',$title);
        $this->HtmldocFullText->Data()->write('doctext',$text);
        $this->Scrapper->clear();
    }
    
    /**
     * Se encarga de almacenar el Full Text.
     * Tambien popula el TSV.
     * 
     * [FLUSH] HtmldocFullText
     */
    
    private function storeFullText(){
        $metaDataId = $this->MetaDataFile->id;
        
        if($this->HtmldocFullText->store()){
            $this->logAnalyzer("STORE<META:$metaDataId,DONE>");
            
            if($this->HtmldocFullText->updateTsv()){
                $this->logAnalyzer("TSV<META:$metaDataId,DONE>");
            }
            else{
                $this->logAnalyzer("TSV<META:$metaDataId,FAIL>");
            }
        }
        else{
            $this->logAnalyzer("STORE<META:$metaDataId,FAIL>");
        }
        
        $this->HtmldocFullText->clearFields();
    }
    
    /* Carga el archivo HTML en memoria */
    
    private function loadDataFile(){
        $dataFileId = $this->MetaDataFile->Data()->read('data_file_id');
        $response = true;
        
        if($this->DataFile->loadFromId($dataFileId) === false){
            $response = false;
        }
        
        return $response;
    }
}