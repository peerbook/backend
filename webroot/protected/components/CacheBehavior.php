<?php

/**
 * Cache
 * @author Han
 *
 */
class CacheBehavior extends CBehavior {

    /**
     * Default lifetime
     * @var int in seconds
     */
    const LIFETIME = 1800;

    private $_delayed_push = array();

    public function __construct() {
        register_shutdown_function(array($this, 'delayed_push_send'));
    }

	public function dirty($key) {
		Yii::app()->cache->delete($key);
	}
	
    /**
     * Automaticly retrieves and stores the result of the callback
     * @param string $key
     * @param Closure $_callback_on_failure
     */
    public function retrieve($key, Closure $_callback_on_failure, $lifetime = 600) {
        $cache = Yii::app()->cache;

        $data = $cache->get($key);
        if ($data !== false && $lifetime !== false) {
            return $data;
        } else {
            $result = $_callback_on_failure();
            $cache->set($key, $result, $lifetime);
            return $result;
        }
    }

    /**
     * push to cache
     * @param str $key
     * @param mixed $data
     * @return boolean
     */
    public function push($key, $data) {

        $cache = Yii::app()->cache;
        $_list = $cache->get($key);

        if ($_list === false) {
            $_list = array();
        }

        array_push($_list, $data);

        $cache->set($key, $_list, CacheBehavior::LIFETIME);

        return $_list;
    }

    /**
     * Push to variable and send to server when request is done
     * @param str $key
     * @param mixed $data
     */
    public function delayed_push($key, $data) {

        if (!isset($this->_delayed_push[$key])) {
            $this->_delayed_push[$key] = array();
        }

        array_push($this->_delayed_push[$key], $data);

        return true;
    }

    /**
     * Send at the end of the request!
     */
    public function delayed_push_send() {

        if (count($this->_delayed_push) > 0) {

            $cache = Yii::app()->cache;

            foreach ($this->_delayed_push as $key => $list) {

                $get = $cache->get($key);
                if ($get === false) {
                    $get = array();
                }

                $list = array_merge($get, $list);
                $cache->set($key, array_unique($list), CacheBehavior::LIFETIME);
            }
        }
    }

    /**
     * pop from cache
     * @param str $key
     * @return mixed
     */
    public function pop($key) {

        $cache = Yii::app()->cache;
        $_list = $cache->get($key);

        if ($_list !== false && count($_list) > 0) {
            $result = array_pop($_list);

            $cache->set($key, $_list, CacheBehavior::LIFETIME);

            return $result;
        } else
            return false;
    }

    /**
     * Retrieve a range from a key
     * @param string $key
     * @param string $len
     * @return array
     */
    public function range($key, $len = null) {

        $cache = Yii::app()->cache;
        $_list = $cache->get($key);

        if ($_list !== false) {

            $new_list = array_slice($_list, 0, $len);

            $cache->set($key, array_slice($_list, $len), CacheBehavior::LIFETIME);

            return $new_list;
        } else
            return false;
    }

}
