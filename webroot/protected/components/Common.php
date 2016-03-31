<?php

class Common {

    public static function in_array($needle, $haystack) {
	if (is_callable($needle)) {
	    $find = false;
	    foreach ($haystack as $item) {
		if (call_user_func($needle, $item)) {
		    $find = true;
		    break;
		}
	    }
	    return $find;
	} else
	    return in_array($needle, $haystack);
    }

    public static function facebookAppId() {

	$type = $_SERVER['HTTP_HOST'] == 'spull.eu' ? 'dev' : 'live';

	return Yii::app()->params['facebook_app_ids'][$type];
    }

    public static function slug($str) {
	setlocale(LC_ALL, 'nl_NL.UTF8');
	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_|+ -]+/", '-', $clean);

	return $clean;
    }

    public static function cacheKey() {

	return Yii::app()->cache->retrieve('current_cache_key', function () {
		    $file = $_SERVER['DOCUMENT_ROOT'] . '/../cachekey';
		    if (file_exists($file)) {
			return trim(file_get_contents($file));
		    }
		    return '';
		}, 60);
    }

    public static function path($dir = null) {
	if (defined('YII_DEBUG_USE_CACHE') && !YII_DEBUG_USE_CACHE) {
	    return $dir != null ? $dir : '';
	} else {
	    return '/static/' . Common::cacheKey() . ($dir != null ? $dir : '');
	}
    }

    public static function randomHash() {
	return bin2hex(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM));
    }

    /**
     * Number format a number
     * @param float $money
     * @return string
     */
    public static function money($money) {
	return number_format($money, 2, ',', '.');
    }

    /**
     * Str Insert is a function which given a string an data set inserts the data at the right place
     * 
     * Example: {{this}} is a sentence which is {{cool}}
     * data : array('this' => 'Deze zin', 'cool' => 'stupid')
     * => Deze zin is a sentence which is stupid
     * 
     * @param string $str
     * @param array $replace
     * @return string
     */
    public static function str_insert($str, $replace) {

	$_ret = array();
	foreach ($replace as $key => $value) {
	    $_ret['{' . $key . '}'] = $value;
	}

	return strtr($str, $_ret);
    }

    /**
     * Generate unique hash of a object
     * @param mixed $data
     * @return string
     */
    public static function hash($data) {
	return md5(serialize($data));
    }

    /**
     * Assumes that the input of the sorting are two assoc arrays
     * @param string $attr
     * @return function
     */
    public static function sortByAttribute($attr, $desc = true) {
	return function ($a, $b) use ($attr, $desc) {

	    if ($a[$attr] == $b[$attr])
		return 0;

	    return ($desc ? 1 : -1) * ($a[$attr] < $b[$attr] ? 1 : -1);
	};
    }

    public static function sortByFriends() {

	$meFriends = User::model()->resetScope()->findByPk(Yii::app()->user->_id);
	$friends = $meFriends->friends();
	$groupCount = [];
	foreach ($friends as $friend) {
	    foreach ($friend->member_of as $grp) {
		if (!isset($groupCount[mongoId($grp)]))
		    $groupCount[mongoId($grp)] = 1;
		else
		    $groupCount[mongoId($grp)] ++;
	    }
	}

	return function ($a, $b) use ($groupCount) {

	    $ra = isset($groupCount[mongoId($a)]) ? $groupCount[mongoId($a)] : 0;
	    $rb = isset($groupCount[mongoId($b)]) ? $groupCount[mongoId($b)] : 0;

	    if ($ra == $rb)
		return 0;

	    return $ra < $rb ? 1 : -1;
	};
    }

    /**
     * Converts anything to a date
     * @param mixed $data
     * @return string
     */
    public static function datetime($data) {

	$t = $data;
	if ($data instanceof MongoDate) {
	    $t = $data->sec;
	}

	if (is_numeric($t)) {
	    return date('Y-m-d H:i:s', $t);
	}
	return '';
    }

    public static function log($message, $obj) {
	Yii::log($message . ' :: ' . serialize($obj), 'info');
    }

}
