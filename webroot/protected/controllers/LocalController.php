<?php

class LocalController extends APIChildController {
    
    public function children() {
        return ['stats', 'book'];
    }  
    
    public function getLocation($id = null) {
        
        $def = array(
            'type' => 'Point',
            'coordinates' => [6.895097, 52.220887] // Enschede
        );
        
        if($id == null || $id === 'enschede') {
            return $def;
        } else if($id === 'userPosition' && isset(Yii::app()->user->me) ) {
            $userPos = Yii::app()->user->me->position;git dif
            if($userPos)
                return $userPos;
            
            return $def;
        } else {
            return Location::convert($id);
        }
    }
    
    public function getLocalCriteria($id = null) {
        
        $maxDistance = 10000; // 5km
                     
        return array(
            'position' => array(
                '$near' => array(
                    '$geometry' => $this->getLocation($id),
                    '$maxDistance' => $maxDistance,
                    '$spherical' => true
                )
            )
        );
        
    }
    
}

