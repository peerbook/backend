<?php

class BookComponent extends CApplicationComponent {
    
    public $oauth_token;
    
    public function init() {
        Yii::import('ext.curl.Curl');
        Yii::import('ext.google.*');
    }
    
    private function get($q, $params = array()) {
        $url = 'https://www.googleapis.com/books/v1/volumes?q=' . urlencode($q);

        try {
            $curl = new Curl();
            $result = $curl->get($url);

            $objects = json_decode($result, true);

            
            
            if (isset($params['raw']) && $params['raw']) {
                return $objects;
            } else {
                $ret = array();
                
                if(isset($objects['items'])) {
                    foreach($objects['items'] as $item) {
                        $ret[] = new GoogleBook($item);
                    }
                }
                
                return $ret;
            }
        } catch (CurlException $e) {
            var_dump($e); die;
            return isset($params['raw']) && $params['raw'] ? null : [];
        }
    }

    public function search($q) {
        return $this->get($q);
    }
    
}