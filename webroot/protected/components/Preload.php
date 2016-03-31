<?php
class Preload extends CApplicationComponent {
	
	public function init() {
		
		header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
		
		Yii::app()->session->open();
		
		function mongoId($id) {
			if($id instanceof MongoId) {
				return $id->__toString();
			} else if($id instanceof EMongoDocument && isset($id->_id) ) {
				return $id->_id->__toString();
			} else if(isset($id['$id'])) {
				return $id['$id']->__toString();
			} else if(isset($id['_id'])) {
				return $id['_id']->__toString();
			} else if(isset($id->_id)) {
				return $id->_id->__toString();
			} else if(isset($id['id'])) {
				return $id['id']->__toString();
			} else {
                                return (string) $id;
			}
		}
		
		function mongoIdArray($data) {
			$ret = array();
			foreach($data as $item) {
				$ret[] = mongoId($item);
			}
			return $ret;
		}
		
		function mongoIdInArray($data) {
			$ret = array();
			foreach($data as $item) {
				$ret[] = new MongoId(mongoId($item));
			}
			return $ret;
		}
		
		function mongoDate($date) {
			// 2011-10-05T09:00Z
			if($date instanceof MongoDate) {
				$sec = $date->sec;
			} else {
				$sec = strtotime($date);
			}
			
			return date('c', $sec);
		}
		
		/**
		 * A binary search on a sorted array 
		 * 
		 * @param mixed $needle When a string the function strcmp is used
		 * @param array $a The sorted array $a
		 * @return false on not found or the item
		*/
		function in_array_binary_search($needle, array $a) {
			$lo = 0;
			$hi = count($a) - 1;
			
			if(is_string($needle)) {
				$compare = 'strcmp';
			} else {
				$compare = function ($a, $b) {
					return ($a < $b) ? -1 : (($a > $b) ? 1 : 0);
				};
			}
			
			while ($lo <= $hi) {
				$mid = (int)(($hi - $lo) / 2) + $lo;
				$cmp = call_user_func($compare, $a[$mid], $needle);
				
				if ($cmp < 0) {
					$lo = ++$mid;
				} elseif ($cmp > 0) {
					$hi = --$mid;
				} else {
					return $mid;
				}
			}
			return false;
		}	
		
		parent::init();
	}
	
}