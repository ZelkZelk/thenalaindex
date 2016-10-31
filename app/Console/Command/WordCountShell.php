<?php

App::uses('ComponentCollection', 'Controller');
App::uses('WordCountAnalyzerComponent', 'Controller/Component');

App::import('Model', 'CrawlerLog');
App::import('Model', 'Target');


class WordCountShell extends AppShell {    
    public function main(){
        $this->initCrawlerLog();
        $this->initWordCountAnalyzerLog();
        $this->initWordCountAnalyzer();
        $this->wordCountAnalysis();
    } 
    
    public function inflector(){  
        $collection = new ComponentCollection();
        $this->WordCountAnalyzer = new WordCountAnalyzerComponent($collection);
        
        $this->WordCountAnalyzer->init(new CrawlerLog(),function($message){
            CakeLog::write('info', $message, 'wc');
        });        
        
        $words = [ 
            'peces', 'aves','monos', 'arboles','materiales', 'mamones', 
            'caramelos','perdices','reyes','rey','mano', 'tomates', 'helechos',
            'camiones', 'anises', 'viernes' ];
        
        foreach($words as $word){
            echo Inflector::singularize($word) . "\n";
        }
    }
    
    /* Obtiene el Crawler Log de BD */
    
    private function initCrawlerLog(){
        $id = $this->params['crawler_log_id'];
        $this->CrawlerLog = new CrawlerLog();
        $this->CrawlerLog->loadFromId($id);
        
        $target_id = $this->CrawlerLog->Data()->read('target_id');
        $this->Target = new Target();
        $this->Target->loadFromId($target_id);
    }
    
    /* Realiza el analisis de conteo de palabras de cada documento HTML almacenado
     **/
    
    private function wordCountAnalysis(){    
        $id = $this->params['crawler_log_id'];
        $this->WordCountAnalyzer->scanner($id);
    }
    
    /* Incia el log del componente WordCountAnalyzer, cada Target tiene su archivo separado
     * de log.
     */
    
    private function initWordCountAnalyzerLog(){
        $name = $this->Target->Data()->read('name');
        $file = 'wc-' . strtolower($name);
        
        CakeLog::config($file, array(
            'engine' => 'FileLog',
            'types' => [ 'info' ],
            'scopes' => [ 'wc' ],
            'file' => $file
        ));    
    }
    
    /* Inicia el componente WordCountAnalyzer con sus valores por defecto.
     */
    
    private $WordCountAnalyzer;
    
    private function initWordCountAnalyzer(){        
        $collection = new ComponentCollection();
        $this->WordCountAnalyzer = new WordCountAnalyzerComponent($collection);
        
        $this->WordCountAnalyzer->init($this->CrawlerLog,function($message){
            CakeLog::write('info', $message, 'wc');
        });        
    }
    
    /* El Crawler Log ID se envia en el paráemtro -c */
    
    public function getOptionParser() {
        $parser = parent::getOptionParser();
        $parser->addOption('crawler_log_id', [ 'short' => 'c', 'help' => 'CRAWLER LOG ID' ]);
        return $parser;
    }    
}