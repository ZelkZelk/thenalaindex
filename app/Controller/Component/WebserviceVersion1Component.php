<?php

class WebserviceVersion1Component extends Component{
    private $Controller;
    
    public function initialize(\Controller $controller) {
        $this->Controller = $controller;
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
            
            $output = [];
            
            foreach($logs as $log){
                $output[] = $log[$alias];
            }
            
            $this->Controller->pushOutput($output);
        }
        else{
            throw new NotFoundException("Target@{$id} not found");
        }
    }
}