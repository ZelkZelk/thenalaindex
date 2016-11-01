<?php

App::uses('ComponentCollection', 'Controller');
App::uses('WordEmotionalAnalyzerComponent', 'Controller/Component');

App::import('Model', 'CrawlerLog');
App::import('Model', 'Target');


class EmotionalScoreCrontabShell extends AppShell {    
    private static $mutexKey = 'Nala::EmotionalScoreCrontabShell::mainMutex(%d)';
    
    function main(){
        $this->setupLog('emo-cron.log', [ 'emo_cron' ]);
        $this->mainImpl();
    }
    
    /**
     * Obtiene el key para mutex del crawler_log_id especificado.
     */
    
    private function getMutexKey($crawler_log_id){
        return sprintf(self::$mutexKey, $crawler_log_id);
    }
    
    /* Busca el primer crawler log finalizado correctamente que aun no esten
     * flagueado con word_emotional_analyzed.
     * 
     * Luego ejecuta dentro de un mutex por crawler_log_id el analizador.
     * 
     * */
    
    private $CrawlerLog = null;
    
    private function mainImpl(){
        $this->CrawlerLog = new CrawlerLog();
        
        if($this->CrawlerLog->getEmotionalScorePending()){
            $id = $this->CrawlerLog->id;
            $key = $this->getMutexKey($id);
            
            $this->mutex($key, function(){
                $id = $this->CrawlerLog->id;
                
                $this->logcat("MUTEX@$id",'emo_cron');
                $this->parallelEmotionalScore($id);
                $this->logcat("UNMUTEX@$id",'emo_cron');
            });
        }
    }
    
    /* Se encarga de ejecutar el analizador de valoracion emocional en un thread
     * aparte.
     */
    
    private function parallelEmotionalScore($id){
        $path = dirname(__FILE__);
        $app = $path . '/../..';
        
        $file = "/tmp/nala.emo{$id}." . date('dmY') . '.log';
        exec("($app/Console/cake emotional_score -c $id) > $file &");
    }
}