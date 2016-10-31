<?php

App::uses('ComponentCollection', 'Controller');
App::uses('EmotionalScoreAnalyzerComponent', 'Controller/Component');

App::import('Model', 'CrawlerLog');
App::import('Model', 'Target');


class EmotionalScoreShell extends AppShell {    
    public function main(){
        $this->initCrawlerLog();
        $this->initEmotionalScoreAnalyzerLog();
        $this->initEmotionalScoreAnalyzer();
        $this->emotionalScoreAnalysis();
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
    
    private function emotionalScoreAnalysis(){    
        $id = $this->params['crawler_log_id'];
        $this->EmotionalScoreAnalyzer->scanner($id);
    }
    
    /* Incia el log del componente EmotionalScoreAnalyzer, cada Target tiene su archivo separado
     * de log.
     */
    
    private function initEmotionalScoreAnalyzerLog(){
        $name = $this->Target->Data()->read('name');
        $file = 'emo-' . strtolower($name);
        
        CakeLog::config($file, array(
            'engine' => 'FileLog',
            'types' => [ 'info' ],
            'scopes' => [ 'emo' ],
            'file' => $file
        ));    
    }
    
    /* Inicia el componente EmotionalScoreAnalyzer con sus valores por defecto.
     */
    
    private $EmotionalScoreAnalyzer;
    
    private function initEmotionalScoreAnalyzer(){        
        $collection = new ComponentCollection();
        $this->EmotionalScoreAnalyzer = new EmotionalScoreAnalyzerComponent($collection);
        
        $this->EmotionalScoreAnalyzer->init($this->CrawlerLog,function($message){
            CakeLog::write('info', $message, 'emo');
        });        
    }
    
    /* El Crawler Log ID se envia en el paráemtro -c */
    
    public function getOptionParser() {
        $parser = parent::getOptionParser();
        $parser->addOption('crawler_log_id', [ 'short' => 'c', 'help' => 'CRAWLER LOG ID' ]);
        return $parser;
    }    
}