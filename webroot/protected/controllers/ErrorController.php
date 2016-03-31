<?php
class ErrorController extends APIBaseController {
    
    public function hasAccess() {
        return true;
    }
    
    public function add() {
        
        $data = $this->getApiRequest()->getData();
        
        
        if($data != null) {
            
            $error = new Error;
            $error->data = $data;
            
            if($error->save()) {
                return $error->getRepresentation(APIElement::REP_ITEM);
            }
        }
        
        throw new CHttpException(400, 'Error could not be saved');
    }
    
}

