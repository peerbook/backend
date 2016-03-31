<?php
/**
 * Basic interface actions
 * @author Han
 *
 */
interface API {

	const ACTION_READ = 'read';
	const ACTION_ADD = 'add';
	const ACTION_DELETE = 'delete';
	const ACTION_BROWSE = 'browse';
	const ACTION_EDIT = 'edit';
	
	const ACTION_ACCEPT = 'accept';
	const ACTION_DECLINE = 'decline';
	const ACTION_ASK_BACK = 'ask_back';
	const ACTION_GIVE_BACK = 'give_back';
	const ACTION_CONFIRM_BACK = 'confirm_back';

	const ACTION_REQUEST = 'request';
	
	const ACTION_ACCESS_CHILDREN = 'sub_action_access_children';
	
	const ACTION_REGISTER_REQUEST_ID = 'register_req_id';
	
	const ACTION_INVITES = 'invites';
	
	const ACTION_KICK = 'kick';
        
	const ACTION_BOUGHT = 'bought';
	const ACTION_SOLD = 'sold';
        
	const ACTION_TREE_ACCESS = 'tree_access';
	
	/**
	 * Can i share in this group permission
	 * @var string
	 */
	const ACTION_SHARE = 'share';
    
	const ACTION_READ_BOOKS = 'read_books';
	
	const TYPE_SELL = 'sell';
	const TYPE_LOAN = 'loan';
	
}