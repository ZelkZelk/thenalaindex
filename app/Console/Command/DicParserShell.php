<?php

App::uses('ComponentCollection', 'Controller');
App::uses('WordCountAnalyzerComponent', 'Controller/Component');

App::import('Model', 'NotableWord');


class DicParserShell extends AppShell {  
    private $File = false;
    
    public function main(){
        $this->NotableWord = new NotableWord();
        $file = SHELL_CURRENT_PATH . DIRECTORY_SEPARATOR . "../../../dictionaries/spanish_openoffice.txt";
        
        if($this->loadDictionary($file) === false){
            throw new Exception("Cannot read $file\n");
        }
        
        $this->parseDictionary();
        $this->closeDictionary();
    } 
    
    private function parseDictionary(){
         while(($line = fgets($this->File)) !== false){
             $word = $this->filterWord($line);
             
             if($word !== false){
                 $this->storeWord($word);
             }
        }
    }
    
    private function storeWord($word){
        echo "Found '$word'...\n";
        
        if($this->NotableWord->existsWord($word)){
            $id = $this->NotableWord->id;
            echo "\tRepeated @{$id}!\n";
        }
        else if($this->NotableWord->pushWord($word)){
            $id = $this->NotableWord->id;
            echo "\tStored @{$id}!\n";
        }
        else{
            echo "\tFailure!";
        }
    }
    
    private function filterWord($line){
        $regex = '/^([a-zA-ZáéíóúÁÉÍÓÚÑñ]+)\|/';
        $matches = [];
        
        $match = preg_match($regex, $line, $matches);
        
        if($match){
            if(isset($matches[1])){
                return $matches[1];
            }
        }
        
        return false;
    }
    
    private function closeDictionary(){
        fclose($this->File);
    }
    
    private function loadDictionary($file){
        if(file_exists($file) === false){
            return false;
        }
        
        if(is_readable($file) === false){
            return false;
        }
        
        $fp = fopen($file,'r');
        
        if($fp === false){
            return false;
        }
        
        $this->File = $fp;
        return true;
    }
}