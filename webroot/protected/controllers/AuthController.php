<?php
class AuthController extends APIChildController {
    
    public function hasAccess() {
        return true;
    }
    
    private function getUser() {
        $token = $this->getApiRequest()->getToken();
        if(empty($token))
            throw new CHttpException(403, 'Token is required');
        
        $user = User::model()->findOne(array('token' => $token));        
        if($user == null)
            throw new CHttpException(404, 'Token is not valid or not existing');
        
        return $user;
    }
    
    public function add() {
        
        $request = $this->getApiRequest();
        
        $uid = $request->getDataByKey('uid');
        $device_user = $request->getDataByKey('device_user');
        
        if($uid == null)
            throw new CHttpException(403);
        
        $user = User::model()->findOne(array(
            'uid' => $uid
        ));        
        
        if($user == null) {
            
            $user = new User;
            
            $user->attributes = array(
                'uid' => $uid
            ) + ($device_user != null ? $device_user : []);
            
            if(!$user->save()) {
                throw new ModelException($user);
            }
        }
        
        return $user->getRepresentation(APIElement::REP_FULL);
    }
    
    public function browse() {   
        $user = $this->getUser();     
        return $user->getRepresentation(APIElement::REP_ITEM);
    }
    
    public function delete($id) {
        $user = $this->getUser();     
        $user->token = null;
        $user->update(['token']);
        return $user->getRepresentation(APIElement::REP_ITEM);
    }
    
}
