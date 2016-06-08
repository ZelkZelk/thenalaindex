<?php

App::uses('ComponentCollection', 'Controller');

App::import('Model', 'CrawlerLog');
App::import('Model', 'MetaDataFile');

/* 
 * Dado el TARGET, hace las revisiones de Mutex adecuadas.
 * 
 * Si obtiene MUTEX crea el CrawlerLog y comienza el proceso Crawling.
 * 
 */

class RootHashShell extends AppShell {    
    public function main(){
        $CrawlerLog = new CrawlerLog();
        $MetaDataFile = new MetaDataFile();
        
        $unRooted = $CrawlerLog->findUnRooted();
        
        foreach($unRooted as $log){
            $CrawlerLog->loadArray($log['CrawlerLog']);
            $hash = $MetaDataFile->getFirstHash($CrawlerLog);
            
            echo "LOG: {$CrawlerLog->id} \n";
            
            if($hash === false){
                echo "\t HASH: false \n";
                continue;
            }
            
            echo "\t HASH: {$hash} \n";
            
            $CrawlerLog->setRootHash($hash);
            $CrawlerLog->store();
        }
    }
}