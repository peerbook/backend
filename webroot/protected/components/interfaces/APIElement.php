<?php
/**
 * An interface for representation of an Element in the API
 * @author Han
 */
interface APIElement extends API {
	
	const REP_FULL = 'full';
	const REP_EMBED = 'embed';
	const REP_CACHE = 'cache';
	const REP_ITEM = 'item';
	
	/**
	 * Get the representation of an Object
	 * @return array
	 */
	public function getRepresentation($type = APIElement::REP_FULL);
	
	/**
	 * Check whether an action is allowed
	 * Action must be read as: can the current user do $action on $this object?
	 * 
	 * @param string $action Can i do $action
	 * @param mixed $context Context can be used, for example can i detele this user in this group (context)
	 * @return boolean
	 */
	public function hasAccess($action, $context = null);
	
	/**
	 * Get the identifier of the object
	 * @return string
	 */
	public function getId();
}