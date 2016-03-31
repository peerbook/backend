<?php

class ChatController extends APIChildController {
     
    public function browse() {
             
        $copies = Copy::model()->find(['isAdmin' => true]);         
        
        $ret = [];
        
        foreach($copies as $copy) {
            $ret[] = $copy->getRepresentation(APIElement::REP_ITEM);
        }
        
        return $ret;
    }
    
}