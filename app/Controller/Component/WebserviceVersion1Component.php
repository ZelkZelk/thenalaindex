<?php

class WebserviceVersion1Component extends Component{
    private $Controller;
    
    public function initialize(\Controller $controller) {
        $this->Controller = $controller;
//        throw new BadRequestException();
        return parent::initialize($controller);
    }
    
    /** Obtiene los Targets habilitados, ordenados por nombre */
    
    public function targets(){
        App::import('Model','Target');
        $Target = new Target();
        $targets = $Target->findTargets();
        $alias = $Target->alias;
        $output = [];
        
        foreach($targets as $target){
            $output[] = [
                'id' => $target[$alias]['id'],
                'url' => $target[$alias]['url'],
                'name' => $target[$alias]['name'],
                'first_crawl' => $target[$alias]['first_crawl'],
                'last_crawl' => $target[$alias]['last_crawl'],
                'histories' => $target[0]['histories'],
            ];
        }
                
        $this->Controller->pushOutput($output);
    }
    
    /** 
     * Obtiene los historiales por sitio.
     **/
    
    public function histories(){
        App::import('Model','Target');
        $Target = new Target();
        $id = $this->Controller->getWebserviceData('target_id');
        
        if($Target->loadFromId($id)){
            $limit = $this->Controller->getWebserviceData('limit');
            $page = $this->Controller->getWebserviceData('page');
        
            App::import('Model','CrawlerLog');
            $CrawlerLog = new CrawlerLog();
            $logs = $CrawlerLog->findPagedByTarget($id,$page,$limit);
            $alias = $CrawlerLog->alias;
            
            $histories = [];
            
            foreach($logs as $i => $log){
                $blob = $log[$alias];
                $blob['index'] = ($page - 1) * $limit + ($i + 1);
                $blob['root_hash'] = trim($blob['root_hash']);
                $histories[] = $blob;
            }
            
            $output = [
                'histories' => $histories,
                'target' => $Target->Data()->dump(),
                'page' => $page
            ];
            
            $this->Controller->pushOutput($output);
        }
        else{
            throw new NotFoundException("Target@{$id} not found");
        }
    }
    
    /**
     * Obtiene la info de exploracion.
     * 
     * Incluye:
     * 
     *      * CrawlerLog Data
     *      * Link a la vista de pagina
     *      * Data de analisis
     */
    
    public function exploration(){
        App::import('Model','Target');
        $Target = new Target();
        $id = $this->Controller->getWebserviceData('target_id');
        
        if($Target->loadFromId($id) === false){
            throw new NotFoundException("Target@{$id} not found");
        }
        
        App::import('Model','MetaDataFile');
        $MetaDataFile = new MetaDataFile();
        $hash = $this->Controller->getWebserviceData('hash');
        
        if($MetaDataFile->loadHash($hash) === false){
            throw new NotFoundException("MetaDataFile@hash:{$hash} not found");
        }
        
        App::import('Model','Url');
        $Url = new Url();
        $url_id = $MetaDataFile->Data()->read('url_id');
        
        if($Url->loadFromId($url_id) === false){
            throw new NotFoundException("Url@:{$url_id} not found");
        }
        
        App::import('Model','CrawlerLog');
        $CrawlerLog = new CrawlerLog();
        $crawler_log_id = $MetaDataFile->Data()->read('crawler_log_id');
        
        if($CrawlerLog->loadId($crawler_log_id) === false){
            throw new NotFoundException("CrawlerLog:{$crawler_log_id} not found");
        }
        
        Configure::load('analysis');
        $link = Configure::read('Analysis.cdn_webservice') . $hash;
        
        $output = [
            'link' => $link,
            'analysis' => [],
            'target' => $Target->Data()->dump(),
            'meta' => $MetaDataFile->Data()->dump(),
            'url' => $Url->Data()->dump()
        ];
        
        $this->Controller->pushOutput($output);
    }
}