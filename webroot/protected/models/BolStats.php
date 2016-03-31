<?php
/**
 * Object for storage of statistisc of Bol
 * @author Han
 */
class BolStats extends EMongoDocument {
	
	const THRESHOLD = 900;
	
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function collectionName()
	{
		return 'bolstats';
	}
	
	public function isUsable() {
		return $this->count < self::THRESHOLD;
	}
	
	public static function get() {
		$model = BolStats::model()->findOne(array(
			'hour' => date('H'),
			'date' => date('Y-m-d')
		));
		
		if($model == null) {
			$model = new BolStats();
			$model->hour = date('H');
			$model->date = date('Y-m-d');
			$model->count = 1;
			$model->save(false);
		}
		
		return $model;
	}
	
	public function up() {
		$this->count = $this->count + 1;
		$this->update(array('count'));
	}
}