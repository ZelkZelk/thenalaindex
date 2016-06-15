<?php

App::uses('ComponentCollection', 'Controller');
App::uses('LinkAnalyzerComponent', 'Controller/Component');

App::import('Model', 'CrawlerLog');

/* 
 * Vuelve a realizar los analisis sobre todos los archivos de exploracion
 * de todos los crawlers logs validos.
 * 
 */

class AnalyzerShell extends AppShell {    
    public function main(){
        $this->initCrawlerLog();
        $this->initLinkAnalyzerLog();
        $this->initLinkAnalyzer();
        $this->linkAnalysis();
    } 
    
    /* Obtiene el Crawler Log de BD */
    
    private function initCrawlerLog(){
        $this->CrawlerLog = new CrawlerLog();
    }
    
    /* Realiza el analisis de hipervinculos de cada documento HTML almacenado */
    
    private function linkAnalysis(){    
        $logs = $this->CrawlerLog->findDone();
        
        foreach($logs as $log){
            $id = $log['CrawlerLog']['id'];
            $this->LinkAnalyzer->scanner($id);
        }
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
}