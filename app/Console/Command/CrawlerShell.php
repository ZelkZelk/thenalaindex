<?php

App::uses('ComponentCollection', 'Controller');
App::uses('CrawlerComponent', 'Controller/Component');

/* 
 * Dado el TARGET, hace las revisiones de Mutex adecuadas.
 * 
 * Si obtiene MUTEX crea el CrawlerLog y comienza el proceso Crawling.
 * 
 */

class CrawlerShell extends AppShell {
    private static $mutexKey = 'Nala::CrawlerShell::mainMutex(%d)';
    private static $logSeparatorStart = '<<<####################################';
    private static $logSeparatorEnd = '####################################>>>';
    
    /* Componente Crawler, encierra toda la logica de exploracion. */
    
    private $Crawler;
    
    /* Esta funcion se asegura de obtener el semafaoro para el Target especifico. 
     * Luego pasa el flujo al Componente Crawler.
     */
    
    function main(){
        $this->setupLog('crawl.log', [ 'crawl' ]);
        $target_id = $this->params['target'];
        
        if($this->Target->loadFromId($target_id)){
            $key = $this->resolvMutexKey();
            
            $this->mutex($key, function(){
                $target_id = $this->Target->id;
                
                $this->logcat(self::$logSeparatorStart,'crawl');
                $this->logcat("MUTEX@$target_id",'crawl');
                $this->mainImpl();
                $this->logcat("UNMUTEX@$target_id",'crawl');
                $this->logcat(self::$logSeparatorEnd,'crawl');
            });
        }        
    }
    
    /* Invoca al componente crawler para comenzar el proceso de Crawling del
     * Target especifico. */
    
    private function mainImpl(){
        $collection = new ComponentCollection();
        $this->Crawler = new CrawlerComponent($collection);
        
        $this->Crawler->init($this->Target,function($message){
            $this->logcat($message, 'crawl');
        });
        
        $this->Crawler->main();
    }
    
    /* Resuelve el key del mutex a partir del Target resuelto */
    
    private function resolvMutexKey(){
        $target_id = $this->Target->id;
        $key = sprintf(self::$mutexKey, $target_id);
        return $key;
    }
    
    /* El Target ID se envia en el paráemtro -t */
    
    public function getOptionParser() {
        $parser = parent::getOptionParser();
        $parser->addOption('target', [ 'short' => 't', 'help' => 'TARGET ID' ]);
        return $parser;
    }    
}