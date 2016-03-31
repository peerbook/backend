<?php
class MongoTestCase extends CTestCase {
 
    private $fixturesManager;
    
    public $fixtures = array();
    
    public function __construct() {
        $this->fixturesManager = new MongoFixtures();
        $this->fixturesManager->init();
    }
    
    public function setUp() {
        parent::setUp();
        
        if($this->fixtures && is_array($this->fixtures)) {
            
            foreach($this->fixtures as $collection) {
                if(!$this->fixturesManager->load($collection)) {
                    throw new CException('Collection "'.$collection.'" does not exists in fixtures list');
                }
            }
        }
    }
   
    public function tearDown() {
        
    }
}