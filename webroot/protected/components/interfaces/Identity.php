<?php
abstract class Identity extends CUserIdentity {
	abstract public function getInformation();
		
	abstract public function lastUpdated();
	
	abstract public function type();
	
	public function command($command) {
		return null;
	}
	
}