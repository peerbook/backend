<?php
class LocationController extends APIChildController {
    
    public function add() {
        
        $position = $this->getApiRequest()->getDataByKey('position');
        
        if(empty($position))
            throw new CHttpException(400);
        
        $location = Location::convert($position);
        
        return Location::latlngTo($location['coordinates'][1], $location['coordinates'][0])->toArray();
    }
    
}
