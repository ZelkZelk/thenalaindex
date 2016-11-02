<?php

App::uses('CrawlerUtilityComponent', 'Controller/Component');
App::uses('ScrapperComponent', 'Controller/Component');
App::uses('UrlNormalizerComponent', 'Controller/Component');
App::uses('ComponentCollection', 'Controller');

App::import('Model', 'CrawlerLog');
App::import('Model', 'MetaDataFile');
App::import('Model', 'DataFile');
App::import('Model', 'HtmldocFullText');
App::import('Model', 'HtmldocNotableWord');
App::import('Model', 'HtmldocEmotionalScore');
App::import('Model', 'NotableWord');

/* Este componente se encarga de generar la puntuacion emocional de cada pagina
 * crawleada basandose en la cantidad de palabras analizada posteriormente. Por
 * ende, este componente requiere que la pagina sea analizada anteriormente por
 * el contador de palabras.
 **/

class EmotionalScoreAnalyzerComponent extends CrawlerUtilityComponent{
    private $MetaDataFile;
    private $DataFile;
    private $CrawlerLog;
    private $HtmldocNotableWord;
    private $HtmldocEmotionalScore;
    private $NotableWord;
    
    /* Override de la funcion generica para agregar el TAG EMO
     */
    
    private static $TAG = '[EMO]';
    
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
        $this->HtmldocNotableWord = new HtmldocNotableWord();
        $this->HtmldocEmotionalScore = new HtmldocEmotionalScore();
        $this->NotableWord = new NotableWord();
    }
    
    
    /* Se encarga de ciclar todos los metadatas en busca de documentos HTML.
     * Luego, para cada, documento HTML busca el registro de conteo de palabras
     * para generar el puntaje emocional.
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
        $this->CrawlerLog->Data()->write('word_emotional_analyzed',$now);
        
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
        $this->limit = Configure::read('Analysis.emotional_score_page_limit');  
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
                $this->emotionalScoreAnalysis();
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
     * Realiza el scaneo de palabras de todo el texto del documento HTML
     */
    
    private function emotionalScoreAnalysis(){
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
            
            $ev = $this->gatherScoreValues();
            $this->storeEmotionalScore($ev);
            $this->setAnalyzed();
        }
        
        return true;
    }
    
    /*
     * Busca el score de cada palabra encontrada por el contador de palabras
     * y acumula el puntuja segun la frecuencia de cada palabra.
     */
    
    private function gatherScoreValues(){
        $alias = $this->HtmldocNotableWord->alias;
        $limit = $this->limit;
        $offset = 0;
        $ev = 0;
        
        do{
            $data = $this->HtmldocNotableWord->fetchPage($this->DataFile,$offset,$limit);
            $count = count($data);
            $offset += $count;
            
            foreach($data as $notable){
                $blob = $notable[$alias];
                $this->HtmldocNotableWord->loadArray($blob);
                
                $wordId = $notable[$alias]['notable_word_id'];
                $quantity = $notable[$alias]['quantity'];
                $score = (int) $this->NotableWord->getScore($wordId);
                $ev += $quantity * $score;
            }
            
        } while($count === $limit);
        
        return $ev;
    }
    
    /* Almacena el score final */
     
    private function storeEmotionalScore($ev){
        return $this->HtmldocEmotionalScore->pushScore($this->DataFile, $ev);
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