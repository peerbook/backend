<?php

class Route extends EMongoDocument {

    public $name;
    public $url;

    public static function model($className = __CLASS__) {
	return parent::model($className);
    }

    public function collectionName() {
	return 'routes';
    }

    public function rules() {
	return array(
	    array('name, url', 'required')
	);
    }

    public function attributeLabels() {
	return array(
	    'name' => 'Naam',
	    'url' => 'Url'
	);
    }

}
