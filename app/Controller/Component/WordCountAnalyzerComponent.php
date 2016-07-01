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
App::import('Model', 'NotableWord');

/* Este componente se encarga de recolectar y contar las palabras encontradas
 * en el texto de cada pagina.
 **/

class WordCountAnalyzerComponent extends CrawlerUtilityComponent{
    private $MetaDataFile;
    private $DataFile;
    private $CrawlerLog;
    private $HtmldocFullText;
    private $HtmldocNotableWord;
    private $NotableWord;
    
    /* Override de la funcion generica para agregar el TAG WC
     */
    
    private static $TAG = '[WC]';
    
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
        $this->NotableWord = new NotableWord();
        
        $this->setInflectorRules();
    }   
    
    /**
     * Setea el inflector para spanish
     */
    
    public function setInflectorRules(){
        Inflector::rules('plural', array(
            'rules' => array(
              '/^(.*)z$/i'=>'\1ces',
                '/^(.*[a|e|i|o|u])$/i' => '\1s', 
                '/^(.*)$/i' => '\1es'
                )
        ));
 
        Inflector::rules('singular', array(
            'rules' => array(
                '/^(.*)ces$/i' => '\1z',
                '/^(.*[l|y|d|r|s|on])es$/i' => '\1',
                '/^(.*)s$/i' => '\1', 
                )
        ));
    }
    
    
    /* Se encarga de ciclar todos los metadatas en busca de documentos HTML,
     * scrapea el texto de dichos documentos para realizar el analisis
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
        $this->CrawlerLog->Data()->write('word_count_analyzed',$now);
        
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
        $this->limit = Configure::read('Analysis.word_count_page_limit');  
        
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
                $this->wordCountScan();
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
    
    private function wordCountScan(){
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
            
            $this->scrapWords();
            $this->filterWords();
            $this->storeWords();
            $this->setAnalyzed();
        }
        
        return true;
    }
    
    /**
     * Se encarga de analizar el texto del documento HTML.
     */
    
    private function scrapWords(){
        $this->Scrapper->scrapWords($this->DataFile->getFile());
    }
    
    private $Words = [];
    
    /**
     * Filtra las palabras y determina si son relevantes para el contador.
     */
    
    private function filterWords(){
        $text = $this->Scrapper->getText();
        $pseudoWords = preg_split('/\s+/',$text);
        $dicPseudoWords = [];
        $words = [];
        
        foreach($pseudoWords as $word){
            if(isset($dicPseudoWords[$word]) === true){
                $dicPseudoWords[$word]++;
                continue;
            }
            
            $dicPseudoWords[$word] = 1;
        }
        
        foreach($dicPseudoWords as $word => $freq){
            $w1 = $this->clearWord($word);
            $w2 = $this->filterSymbols($w1);
            $w3 = $this->filterCompounds($w2);
            $w = Inflector::singularize(strtolower($w3));
            
            if($w === false){
                continue;
            }
            
            if(isset($words[$w]) === true){
                $words[$w] += $freq;
                continue;
            }
            
            $words[$w] = $freq;
        }
        
        $this->Words = $words;
    }
    
    
    /**
     * Filtra palabras camelcase, solo son validas palabras sencillas
     */
    
    private function filterCompounds($w){
        if($w === false){
            return false;
        }
        
        if(preg_match('/^.+[A-ZÁÉÍÓÚÑ]/',$w)){
            return false;
        }

        $c1 = strlen($w);
        $regex = '/[A-ZÁÉÍÓÚÑ]+/';
        $c2 = strlen(preg_replace($regex, '', $w));
        
        if($c1 - $c2 > 1){
            return false;
        }
        
        return $w;
    }
    
    /**
     * Limpia signos de puntuacion al final de las palabras
     */
    
    private function clearWord($w){
        if($w === false){
            return false;
        }

        $regex = '/[,.;:]$/';
        return preg_replace($regex, '', $w);
    }
    
    /**
     * Filtra las palabras para incluir solo aquellas que tengan caracteres
     * latinos validos
     */
    
    private function filterSymbols($w){
        if($w === false){
            return false;
        }
        
        $regex = '/^([A-Za-zAÁÉÍÓÚáéíóúñÑ])+$/';
        $match = preg_match($regex, $w);
        
        if($match === false || $match === 0){
            return false;
        }
        
        return $w;
        
    }
    
    /**
     * Se encarga de almacenar adecuadamente los campos del Full Text.
     * Si el Full Text para el MetaDataFile actual existe, se actualiza.
     * 
     * [FLUSH] Scrapper, $this, DataFile
     */
    
    private function storeWords(){
        $this->Scrapper->clear();
        
        foreach($this->Words as $word => $freq){
            $this->logAnalyzer("INTENT<word:$word>");
            
            if($this->NotableWord->existsWord($word) === false){
                $this->logAnalyzer("FAIL<word:$word,due:not in my dictionary>");
                continue;
            }
            
            if($this->HtmldocNotableWord->pushWord($this->DataFile,$this->NotableWord,$freq) === false){
                $this->logAnalyzer("FAIL<word:$word,due:pushWord() returned false>");
                continue;
            }
            
            $id = $this->HtmldocNotableWord->id;
            $this->logAnalyzer("DONE<word:$word,bound@$id>");
        }
        
        $this->DataFile->clearFields();
        $this->clear();
    }
    
    /*
     * Limpia las palabras encontradas para una nueva iteracion
     */
    
    private function clear(){
        $this->Words = [];
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