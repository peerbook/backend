<?php

class ApiResponse extends CComponent {

	private $httpCode;
	
	private $result;
	
	public function behaviors() {
		return array('APILog');
	}
	
	public function __construct($code, $result) {
		$this->httpCode = $code;
		
		// for accessing an array, wrap it in 
		if(!is_array($result)) {
			$result = array('result' => $result);
		}
		
		$this->result = $result;
		
		$this->attachBehaviors($this->behaviors());
	}
	
	public function getResult() {
		return $this->result;
	}
	
	public function attachDebugInformation() {
		
		$result = $this->result;
		
		if(count($result) > 0) {
			$logs = $this->logs(Yii::getLogger()->getLogs('', array(), array()));
			if(array_key_exists(0, $result)) {
				$result[count($result)-1]['__logs'] = $logs;
			} else {
				$result['__logs'] = $logs;
			}
		}
		
		$this->result = $result;
	}
	
	private function getHttpResponseName() {
		$codes = array(
			200 => 'OK',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
		);
		
		return isset($codes[$this->httpCode]) ? $codes[$this->httpCode] : '';
	}

	public function encode() {
		return function_exists('json_encode') ? json_encode($this->result) : CJavaScript::jsonEncode($this->result);
	}
	
	/**
	 * Encode the API Response to an http json packet with code
	 * @return string
	 */
	public function sendResponse() {	
		
		$encoded = $this->encode();
		
		if(!headers_sent()) {
			header('HTTP/1.1 ' . $this->httpCode . ' ' . $this->getHttpResponseName() );
			header('Content-type: application/json');
	
			echo $encoded;
			
			Yii::app()->end();
		} else
			echo $encoded;
	}
	
}
