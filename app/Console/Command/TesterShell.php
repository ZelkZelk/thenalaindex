<?php

App::import('Model', 'CrawlerLog');
App::import('Model', 'MetaDataFile');
App::import('Model', 'DataFile');
App::import('Model', 'Url');
App::import('Model', 'Target');

App::uses('ComponentCollection', 'Controller');
App::uses('QueueComponent', 'Controller/Component');
App::uses('HttpClientComponent', 'Controller/Component');
App::uses('RobotsTxtComponent', 'Controller/Component');
App::uses('ScrapperComponent', 'Controller/Component');
App::uses('UrlNormalizerComponent', 'Controller/Component');
App::uses('LinkAnalyzerComponent', 'Controller/Component');
App::uses('FullTextAnalyzerComponent', 'Controller/Component');

/* 
 * 
 * UnitTest Varios
 * 
 */

class TesterShell extends AppShell {
    public function queue(){
        $collection = new ComponentCollection();
        $this->Queue = new QueueComponent($collection);
        $this->Queue->init(new CrawlerLog(),function($message){
            echo $message . "\n";
        });
        
        $this->Queue->push('http://www.pol.una.py:80/');
        $this->Queue->done('http://www.pol.una.py:80/');
        $this->Queue->push('http://www.pol.una.py:80/');
        $this->Queue->push('http://www.pol.una.py:80/');
        $this->Queue->debug();
    }
    
    public function memoryReport(){
        $usage = memory_get_usage();
        $real = memory_get_usage(true);
        
        echo "USAGE=<$usage> REAL=<$real>\n";
    }
    
    public function hashing(){
        $hash = "1314951058904057255             ";
        $Meta = new MetaDataFile();
        
        if($Meta->loadHash(trim($hash))){
            echo "loaded: $hash\n";
        }
    }
    
    public function memory(){
        $this->memoryReport();
        $this->DataFile = new DataFile();        
        $this->DataFile->loadFromId(1);      
        $this->memoryReport();
        
        $this->DataFile->clearFields();
        $this->memoryReport();
    }
    
    public function memory2(){
        $this->memoryReport();
        $this->Dom = new DOMDocument();
        $this->Dom->load('http://www.pol.una.py');
        $this->memoryReport();
        
        unset($this->Dom);
        $this->memoryReport();
    }
    
    public function queue2(){
        $collection = new ComponentCollection();
        $this->Queue = new QueueComponent($collection);
        $this->Queue->init(new CrawlerLog(),function($message){
            echo $message . "\n";
        });
        
        $c = $this->Queue->failureIncrement('www.google.com.py');
        
        if($c === false){        
            $this->Queue->push('www.google.com.py');
        }
        
        $this->Queue->failureIncrement('www.google.com.py');
        $this->Queue->push('www.abc.com.py');
        $this->Queue->failureIncrement('www.google.com.py');
        $this->Queue->robotsDisallow('www.abc.com.py');
        $this->Queue->debug();
    }
    
    public function http(){
        $collection = new ComponentCollection();
        $this->Http = new HttpClientComponent($collection);
        $this->Http->init(new CrawlerLog(),function($message){
            echo $message . "\n";
        });
        
        $this->Http->get('www.pol.una.py');
        $this->Http->debug();        
    }
    
    public function robots(){
        $collection = new ComponentCollection();
        $this->Robots = new RobotsTxtComponent($collection);
        $this->Robots->init(new CrawlerLog(),function($message){
            echo $message . "\n";
        });
        
        $this->robotsDebug('http://backend.nala:80/hola');
        $this->robotsDebug('http://backend.nala:80/chau/tarola.html');
    }
        
    private function robotsDebug($url){
        if($this->Robots->isAllowed($url)){
            echo "Allowed:$url\n";
        }
        else{
            echo "Disallowed:$url\n";
        }
    }
    
    public function metadata(){
        $Url = new Url();
        $Url->loadFromId(1);        
        $this->MetaData = new MetaDataFile();
        
        $collection = new ComponentCollection();
        $this->Http = new HttpClientComponent($collection);
        $this->Http->init(new CrawlerLog(),function($message){
            echo $message . "\n";
        });
        
        $url = $Url->Data()->read('full_url');
        $this->Http->get($url);
        
        $headerData = $this->MetaData->headerAnalysis($Url,$this->Http);
        $this->MetaData->createMetaData(new CrawlerLog(),$headerData);
        
        print_r($this->MetaData->Data()->dump()); 
    }
    
    public function datafile(){
        $this->metadata();
        
        $this->DataFile = new DataFile();        
        $this->DataFile->createData($this->MetaData,$this->Http);      
        
        $Reader = new DataFile();
        $Reader->loadFromId($this->DataFile->id);
        $file = $Reader->getFile();
        
        $this->route = '/tmp/nala_datafile_dump';
        file_put_contents($this->route, $file);
        
        passthru("xdg-open {$this->route} 1> /dev/null 2> /dev/null &");
    }
    
    public function dumpdatahash(){
        $Meta = new MetaDataFile();
        $hash = '1238470639145190408';
        
        if($Meta->loadHash($hash)){        
            $Reader = new DataFile();
            $id = $Meta->id;
            
            if($Reader->loadFromMeta($id)){
                $file = $Reader->getFile();
                echo $file;            
            }
            else{
                echo "DataFile for {$id} NOT FOUND\N";
            }
        }
        else{            
            echo "MetaDataFile for {$hash} NOT FOUND\N";
        }
    }
    
    public function dumpdatafile(){
        $Reader = new DataFile();
        $Reader->loadFromId(10128);
        $file = $Reader->getFile();
        
        echo $file;
    }
    
    public function checksum(){
        $this->datafile();        
        $this->MetaData->bindChecksum($this->DataFile);
        echo "Checksum Generado: {$this->MetaData->Data()->read('checksum')}\n";
        echo "Checksum Esperado: ";
        
        passthru("md5sum {$this->route} | awk '{ print $1}' ");
    }
    
    public function file_scrapper(){        
        $collection = new ComponentCollection();

        $this->Scrapper = new ScrapperComponent($collection);
        $this->Scrapper->init(new CrawlerLog(),function($message){
            echo $message . "\n";
        });
        
        $this->Normalizer = new UrlNormalizerComponent($collection);
        $this->Normalizer->init('127.0.0.1',new CrawlerLog(),function($message){
            echo "$message\n";
        });            

        $filename = '/var/www/html/nalatarget/v2/v2.1/test.php';
        $file = file_get_contents($filename);
        $this->Scrapper->scrapUrls($file);
        $links = $this->Scrapper->getLinks();
        $assets = $this->Scrapper->getAssets();
        
        foreach($links as $link){
            $this->Normalizer->normalize($link);
            
            if($this->Normalizer->isAllowed()){
                echo "ALLOWED<$link>\n";
            }
            else{
                echo "NOTALLOWED<$link>\n";                
            }
        }
        
        foreach($assets as $asset){
            $this->Normalizer->normalize($asset);
            echo "ASSET<$asset>\n";
        }
    }
    
    public function scrapper(){
        $this->metadata();        
        
        $this->DataFile = new DataFile();        
        $this->DataFile->createData($this->MetaData,$this->Http);     
        $this->MetaData->bindChecksum($this->DataFile);
        
        $this->Target = new Target();
        $this->Target->loadFromId(1);
        $url = $this->Target->Data()->read('url');
        
        if($this->MetaData->isHtml()){            
            $file = $this->DataFile->getFile();
            $collection = new ComponentCollection();
            
            $this->Scrapper = new ScrapperComponent($collection);
            $this->Scrapper->init(new CrawlerLog(),function($message){
                echo $message . "\n";
            });
            
            $this->Normalizer = new UrlNormalizerComponent($collection);
            $this->Normalizer->init($url,new CrawlerLog(),function($message){
                echo "$message\n";
            });
            
            $this->Scrapper->scrapUrls($file);
            
            $links = $this->Scrapper->getLinks();
            $linkCount = count($links);
            echo "LINKS<{$linkCount}>\n";
            
            foreach($links as $link){
                $this->testNormalizer($link);
            }
            
            $stylesheets = $this->Scrapper->getStylesheets();
            $stylesheetCount = count($stylesheets);
            echo "STYLESHEETS<{$stylesheetCount}>\n";
            
            foreach($stylesheets as $stylesheet){
                $this->testNormalizer($stylesheet);
            }
            
            $images = $this->Scrapper->getImages();
            $imageCount = count($images);
            echo "IMAGES<{$imageCount}>\n";
            
            foreach($images as $image){
                $this->testNormalizer($image);
            }
            
            $scripts = $this->Scrapper->getScripts();
            $scriptCount = count($scripts);
            echo "SCRIPTS<{$scriptCount}>\n";
            
            foreach($scripts as $script){
                $this->testNormalizer($script);
            }
        }
        else{
            echo "No es un documento HTML\n";
        }
    }
    
    public function normalizer(){
        $this->Target = new Target();
        $this->Target->loadFromId(1);
        $url = $this->Target->Data()->read('url');
        
        $collection = new ComponentCollection();
        $this->Normalizer = new UrlNormalizerComponent($collection);
        $this->Normalizer->init($url,new CrawlerLog(),function($message){
            echo "$message\n";
        });
        
//        $this->testNormalizer('#test');
//        $this->testNormalizer('/?q=node/1&page=2');
//
//        $this->testNormalizer('http://www.una.py/index.php/unidades-academicas/biblioteca-central');
//        $this->testNormalizer('/?q=node/1&page=3');
//        $this->testNormalizer('http://www.pol.una.py/?q=horario_clases');
        $this->testNormalizer('../../css/style.css','http://www.test.com/v2/v2.1/');
//        $this->testNormalizer('/../../css/style.css','http://www.test.com/');
    }
    
    private function testNormalizer($url,$referer = null){   
        $this->Normalizer->normalize($url,$referer);
        
        if($this->Normalizer->isAllowed()){            
            echo "URL<$url> ALLOWED\n";
        }
        else{
            echo "URL<$url> NOT ALLOWED\n";
        }        
        
        $normalized = $this->Normalizer->getNormalizedUrl();
        echo "\tURL NORMALIZED<$normalized>\n";
    }
    
    public function link_analysis(){
        $collection = new ComponentCollection();
        $this->LinkAnalyzerComponent = new LinkAnalyzerComponent($collection);
        $this->LinkAnalyzerComponent->init(new CrawlerLog(),function($message){
            echo "$message\n";
        });
        
        $file = file_get_contents('/tmp/nalatest.html');
        $this->LinkAnalyzerComponent->test($file);
    }
    
    public function transliterate(){
        $collection = new ComponentCollection();
        $this->FullTextAnalyzer = new FullTextAnalyzerComponent($collection);
        $this->CrawlerLog = new CrawlerLog();
        
        $this->FullTextAnalyzer->init($this->CrawlerLog,function($message){
            CakeLog::write('info', $message, 'fts');
        });        
        
        echo $this->FullTextAnalyzer->transliterate('Facultad Politécnica') . "\n";
        echo $this->FullTextAnalyzer->transliterate('Muh cañerías') . "\n";
    }
}