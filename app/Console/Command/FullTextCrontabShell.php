<?php

App::uses('ComponentCollection', 'Controller');
App::uses('FullTextAnalyzerComponent', 'Controller/Component');

App::import('Model', 'CrawlerLog');
App::import('Model', 'Target');


class FullTextCrontabShell extends AppShell {    
    private static $mutexKey = 'Nala::FullTextContrab::mainMutex(%d)';
    
    function main(){
        $this->setupLog('fts-cron.log', [ 'fts_cron' ]);
        $this->mainImpl();
    }
    
    /**
     * Obtiene el key para mutex del crawler_log_id especificado.
     */
    
    private function getMutexKey($crawler_log_id){
        return sprintf(self::$mutexKey, $crawler_log_id);
    }
    
    /* Busca el primer crawler log finalizado correctamente que aun no esten
     * flagueado con full_text_analyzed.
     * 
     * Luego ejecuta dentro de un mutex por crawler_log_id el analizador.
     * 
     * */
    
    private $CrawlerLog = null;
    
    private function mainImpl(){
        $this->CrawlerLog = new CrawlerLog();
        
        if($this->CrawlerLog->getFullTextPending()){
            $id = $this->CrawlerLog->id;
            $key = $this->getMutexKey($id);
            
            $this->mutex($key, function(){
                $id = $this->CrawlerLog->id;
                
                $this->logcat("MUTEX@$id",'fts_cron');
                $this->parallelFullText($id);
                $this->logcat("UNMUTEX@$id",'fts_cron');
            });
        }
    }
    
    /* Se encarga de ejecutar el analizador de full text en otro thread.
     */
    
    private function parallelFullText($id){
        $path = dirname(__FILE__);
        $app = $path . '/../..';
        
        $file = "/tmp/nala.fts{$id}." . date('dmY') . '.log';
        exec("($app/Console/cake full_text -c $id) > $file &");
    }
}