<?php

class PeerbookUser extends CWebUser {
    
    private $_me;
    
    public function getMe() {
        
        if($this->_me == null) {
        
            $token = null;

            // TODO Via register/request object on global app scale
            
            if(isset($_GET['token']))
                $token = (string) $_GET['token'];
            
            if(isset($_SERVER['HTTP_PEERBOOKTOKEN']))
                $token = (string) $_SERVER['HTTP_PEERBOOKTOKEN'];

            $this->_me = User::getUserByToken($token);
        
        }
        
        return $this->_me;
    }
    
}
