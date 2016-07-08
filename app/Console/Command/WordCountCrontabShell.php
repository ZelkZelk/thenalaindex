<?php

App::uses('ComponentCollection', 'Controller');
App::uses('WordCountAnalyzerComponent', 'Controller/Component');

App::import('Model', 'CrawlerLog');
App::import('Model', 'Target');


class WordCountCrontabShell extends AppShell {    
    private static $mutexKey = 'Nala::WordCountCrontab::mainMutex(%d)';
    
    function main(){
        $this->setupLog('wc-cron.log', [ 'wc_cron' ]);
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
        
        if($this->CrawlerLog->getWordCountPending()){
            $id = $this->CrawlerLog->id;
            $key = $this->getMutexKey($id);
            
            $this->mutex($key, function(){
                $id = $this->CrawlerLog->id;
                
                $this->logcat("MUTEX@$id",'wc_cron');
                $this->parallelWordCount($id);
                $this->logcat("UNMUTEX@$id",'wc_cron');
            });
        }
    }
    
    /* Se encarga de ejecutar el analizador de full text en otro thread.
     */
    
    private function parallelWordCount($id){
        $path = dirname(__FILE__);
        $app = $path . '/../..';
        
        $file = "/tmp/nala.wc{$id}." . date('dmY') . '.log';
        exec("($app/Console/cake word_count -c $id) > $file &");
    }
}