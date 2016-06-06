<?php

App::uses('ComponentCollection', 'Controller');
App::uses('CrawlerComponent', 'Controller/Component');

/* 
 * Dado el Crawler Log especificado, genera un dump de su log.
 */

class CrawlerLogShell extends AppShell {
    public $uses = [ 'CrawlerLog' ];
    
    public function main(){
        $id = $this->params['log'];
        
        if($this->CrawlerLog->loadFromId($id)){
            $data = $this->CrawlerLog->Data()->dump();
            $log = $data['log'];
            
            unset($data['log']);
            
            print_r($data);
            
            print_r(unserialize($log));
        }
    }
    
    public function getOptionParser() {
        $parser = parent::getOptionParser();
        $parser->addOption('log', [ 'short' => 'c', 'help' => 'CRAWLER LOG ID' ]);
        return $parser;
    }    
}