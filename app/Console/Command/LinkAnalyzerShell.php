<?php

App::uses('ComponentCollection', 'Controller');
App::uses('LinkAnalyzerComponent', 'Controller/Component');

App::import('Model', 'CrawlerLog');

/* 
 * Dado el TARGET, hace las revisiones de Mutex adecuadas.
 * 
 * Si obtiene MUTEX crea el CrawlerLog y comienza el proceso Crawling.
 * 
 */

class LinkAnalyzerShell extends AppShell {    
    public function main(){
        $this->initCrawlerLog();
        $this->initLinkAnalyzerLog();
        $this->initLinkAnalyzer();
        $this->linkAnalysis();
    } 
    
    /* Obtiene el Crawler Log de BD */
    
    private function initCrawlerLog(){
        $id = $this->params['crawler_log_id'];
        $this->CrawlerLog = new CrawlerLog();
        $this->CrawlerLog->loadFromId($id);
    }
    
    /* Realiza el analisis de hipervinculos de cada documento HTML almacenado */
    
    private function linkAnalysis(){    
        $id = $this->params['crawler_log_id'];
        $this->LinkAnalyzer->scanner($id);
    }
    
    /* Incia el log del componente LinkAnalyzer, cada Target tiene su archivo separado
     * de log.
     */
    
    private function initLinkAnalyzerLog(){
        $name = $this->Target->Data()->read('name');
        $file = 'link-' . strtolower($name);
        
        CakeLog::config($file, array(
            'engine' => 'FileLog',
            'types' => [ 'info' ],
            'scopes' => [ 'link' ],
            'file' => $file
        ));    
    }
    
    /* Inicia el componente LinkAnalyzer con sus valores por defecto y los necesarios
     * para el escaneo de urls y reemplazo de atributos en documentos HTML.
     */
    
    private $LinkAnalyzer;
    
    private function initLinkAnalyzer(){        
        $collection = new ComponentCollection();
        $this->LinkAnalyzer = new LinkAnalyzerComponent($collection);
        
        $this->LinkAnalyzer->init($this->CrawlerLog,function($message){
            CakeLog::write('info', $message, 'link');
        });        
    }
    
    /* El Crawler Log ID se envia en el paráemtro -c */
    
    public function getOptionParser() {
        $parser = parent::getOptionParser();
        $parser->addOption('crawler_log_id', [ 'short' => 'c', 'help' => 'CRAWLER LOG ID' ]);
        return $parser;
    }    
}