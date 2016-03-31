<?php

class BolComponent extends CApplicationComponent implements ExternalSource {

    public $key;
    public $secret;
    public $siteId;
    public $config = array(
        'format' => 'json'
    );

    public function __construct() {
        Yii::import('ext.curl.Curl');
        Yii::import('ext.bol.*');
    }

    public function basket($offer_id, $return_url = '') {
        if (empty($return_url))
            $return_url = 'http://spull.nl';

        $basket_url = 'https://checkout.bol.com/additems.html?url=' . urlencode($return_url) . '&name=' . urlencode(Yii::app()->name) . '&siteId=' . $this->siteId . '&offers=' . $offer_id;

        return $basket_url;
    }

    public function referer($id, $url) {
        $url = rtrim($url, '/') . '/prijsoverzicht/?sort=price&sortOrder=asc&filter=all'; // BUG BOL referer, duidelijk testen
        return 'http://partnerprogramma.bol.com/click/click?p=1&t=url&s=' . $this->siteId . '&url=' . urlencode($url) . '&subid=' . $id . '&name=' . urlencode(Yii::app()->name) . '&f=API';
    }

    public function get($source, $params = array()) {

        // check limits of bol
        $bol = BolStats::get();
        if (!$bol->isUsable()) {
            return array();
        } else {
            $bol->up();
        }

        // start quering
        $prms = $params + array(
            'apikey' => $this->key,
            'format' => $this->config['format'],
            'includeattributes' => true,
            'sort' => 'rankasc'
        );

        $url = 'https://api.bol.com/catalog/v4/' . $source . '?' . http_build_query($prms);

        try {
            $curl = new Curl();
            $result = $curl->get($url);

            $objects = json_decode($result, true);

            if (isset($params['raw']) && $params['raw']) {
                return $objects;
            } else {
                $ret = array();
                if (isset($objects['products'])) {
                    foreach ($objects['products'] as $p) {
                        $ret[] = new BolProduct($p);
                    }
                }
                return $ret;
            }
        } catch (CurlException $e) {
            return isset($params['raw']) && $params['raw'] ? null : [];
        }
    }

    public function search($q) {
        return $this->get('search', array(
            'q' => $q
        ));
    }

    public function findByEan($ean) {

        $result = $this->get('search', array(
            'q' => $ean
        ));

        if (isset($result[0])) {
            return $result[0];
        }
        return null;
    }

    public function find(array $params) {
        $action = $params['action'];
        unset($params['action']);
        return $this->get($action, $params);
    }

}
