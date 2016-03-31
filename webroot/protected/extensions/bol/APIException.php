<?php
/**
 * Exception for API's
 * Die van BOL of van Facebook of wat dan ook
 * 
 * @author Han
 *
 */
class APIException extends Exception {
	
	/**
	 * Which api
	 * @var string
	 */
	private $api;
	
	/**
	 * @var array
	 */
	private $obj;
	
	/**
	 * Init exception
	 * @param string $message Basic message
	 * @param string $api API Code
	 * @param string $url API Called URL
	 */
	public function __construct($message, $api, $url, $obj) {
		parent::__construct($message, 0);
		
		$this->api = $api;
		$this->obj = $obj;
	}
	
	/**
	 * Overwrite getMessage with more specified message
	 * @return string
	 */
	public function getMsg() {
		return $this->api .': '. $this->getMessage() .' { '.serialize($this->obj) . ' }';
	}
	
	public function __toString() {
		return $this->getMsg();
	}
}