<?php
class InteractionMessageController extends APIChildController {
    
    // post a message
    public function add() {
        
        $message = $this->getApiRequest()->getDataByKey('message');
        
        if(!$message) {
            throw new CHttpException(400, 'Message is required');
        }
        $interaction = $this->getContextRecord();       
        
        $result = $interaction->addMessage($message);
        
        if($result) {
            return $result;
        } else
            throw new CHttpException(500, 'Message could not be added');
    }
    
    // show messages
    public function browse() {
        return $this->getContextRecord()->getRepresentation('messages');
    }
    
}
