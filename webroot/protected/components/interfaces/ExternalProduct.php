<?php
abstract class ExternalProduct extends CComponent implements APIElement {
	
	private $order_i;
	
	abstract function getTitle();
	
	abstract function getEan();
	
	abstract function getImage();
	
	abstract function source();
	
	abstract function type();
	
	abstract function getAttributes();
	
	abstract function getSourceId();
	
	public function getId() {
		return $this->getEan();
	}
	
	public function hasAccess($action, $context = null) {
		return true;
	}
	
	public function __isset($item) {
		return property_exists($this, $item) || method_exists($this, 'get'.ucfirst($item)) || parent::__isset($item); 
	}
	
	public function setOrder($order_i) {
		$this->order_i = $order_i;
	}
	
	public function getOrder() {
		return $this->order_i;
	}
}