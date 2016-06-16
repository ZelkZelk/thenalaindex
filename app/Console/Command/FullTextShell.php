<?php

App::uses('ComponentCollection', 'Controller');
App::uses('FullTextAnalyzerComponent', 'Controller/Component');

App::import('Model', 'CrawlerLog');
App::import('Model', 'Target');


class FullTextShell extends AppShell {    
    public function main(){
        $this->initCrawlerLog();
        $this->initFullTextAnalyzerLog();
        $this->initFullTextAnalyzer();
        $this->fullTextAnalysis();
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
    
    /* Realiza el analisis de texto completo de cada documento HTML almacenado */
    
    private function fullTextAnalysis(){    
        $id = $this->params['crawler_log_id'];
        $this->FullTextAnalyzer->scanner($id);
    }
    
    /* Incia el log del componente FullText, cada Target tiene su archivo separado
     * de log.
     */
    
    private function initFullTextAnalyzerLog(){
        $name = $this->Target->Data()->read('name');
        $file = 'fts-' . strtolower($name);
        
        CakeLog::config($file, array(
            'engine' => 'FileLog',
            'types' => [ 'info' ],
            'scopes' => [ 'fts' ],
            'file' => $file
        ));    
    }
    
    /* Inicia el componente LinkAnalyzer con sus valores por defecto y los necesarios
     * para el escaneo de urls y reemplazo de atributos en documentos HTML.
     */
    
    private $FullTextAnalyzer;
    
    private function initFullTextAnalyzer(){        
        $collection = new ComponentCollection();
        $this->FullTextAnalyzer = new FullTextAnalyzerComponent($collection);
        
        $this->FullTextAnalyzer->init($this->CrawlerLog,function($message){
            CakeLog::write('info', $message, 'fts');
        });        
    }
    
    /* El Crawler Log ID se envia en el paráemtro -c */
    
    public function getOptionParser() {
        $parser = parent::getOptionParser();
        $parser->addOption('crawler_log_id', [ 'short' => 'c', 'help' => 'CRAWLER LOG ID' ]);
        return $parser;
    }    
}