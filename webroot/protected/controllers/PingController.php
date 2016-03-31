<?php
class PingController extends APIBaseController {
    
    public function hasAccess() {
        return true;
    }
    
    public function browse() {
        return 'pong';
    }
    
}

