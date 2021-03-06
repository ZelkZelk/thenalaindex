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
        
        App::import('Model','DataFile');
        $DataFile = new DataFile();
        $DataFile->shiftSchema('checksum');
        $data_file_id = $MetaDataFile->Data()->read('data_file_id');
        
        if($DataFile->loadFromId($data_file_id) === false){
            throw new NotFoundException("DataFile@id:{$data_file_id} not found");
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
        $meta = $MetaDataFile->Data()->dump();
        $meta['checksum'] = $DataFile->Data()->read('checksum');
        
        App::import('Model','HtmldocNotableWord');
        $HtmldocNotableWord = new HtmldocNotableWord();
        
        App::import('Model','HtmldocEmotionalScore');
        $HtmldocEmotionalScore = new HtmldocEmotionalScore();
        
        $output = [
            'link' => $link,
            'analysis' => [
                'wc' => $HtmldocNotableWord->retrieveAnalysis($data_file_id),
                'emo' => $HtmldocEmotionalScore->retrieveAnalysis($data_file_id)
            ],
            'target' => $Target->Data()->dump(),
            'meta' => $meta,
            'url' => $Url->Data()->dump()
        ];
        
        $this->Controller->pushOutput($output);
    }
    
    /**
     * Realiza una busqueda de texto completo en el indice de texto completo 
     * segun el termino de busqueda 'q' ingresado.
     */
    
    public function search(){
        App::import('Model','HtmldocFullText');
        $FullText = new HtmldocFullText();
        
        Configure::load('fts');
        $limit = Configure::read('FTS.limit');
        
        $page = $this->Controller->getWebserviceData('page');
        $offset = ($page - 1) * $limit;
        
        $q = $this->Controller->getWebserviceData('q');
        $qterm = preg_replace('/-/', ' ', $q);
        $results = $FullText->searchAll($qterm,$limit,$offset);
        
        $output = [
            'term' => $qterm,
            'results' => $results,
            'page' => $page
        ];
        
        $this->Controller->pushOutput($output);
    }
}