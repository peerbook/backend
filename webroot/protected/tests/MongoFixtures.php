<?php

class MongoFixtures extends CComponent {
    
    public $directory;
    
    public $collections;
    
    public $connection = 'mongodb';
    
    public function init() {
        $this->directory = dirname(__FILE__).'/fixtures';
        
        foreach(glob($this->directory.'/*') as $filename) {
            $collection = substr(basename($filename), 0, -4);
            $this->collections[$collection] = $filename;
        }
    }
    
    public function load($collection, $clean = true) {        
        $connId = $this->connection;
        
        if(!isset($this->collections[$collection]) || !isset(Yii::app()->$connId)) 
            return false;
        
        $mongoCollection = Yii::app()->$connId->selectCollection($collection);
        
        if($clean) {
            $mongoCollection->remove();
            foreach(require($this->collections[$collection]) as $row) {
                $mongoCollection->insert($row);
            }
        }
        
        return true;
    }
    
    public function getCollections() {
        return array_keys($this->collections);
    }
    
    public function clean($collection) {
        
        if(!isset($this->collections[$collection]) || !isset(Yii::app()->$connId)) 
            return false;
        
        $mongoCollection = Yii::app()->$connId->selectCollection($collection);
        $mongoCollection->remove();
    }
    
}
    