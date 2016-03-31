<?php

/**
 * This API is for retrieving existing books and take action on that book
 */
class LocalStatsController extends APIChildController {
    
    public function browse() {
              
        $users = User::model()->find($this->getContextClass()->getLocalCriteria($this->getContext()));
        
        $ret = array();
        
        foreach($users as $user) {
            $ret[] = $user->getRepresentation(APIElement::REP_ITEM);
        }
        
        usort($ret, function ($a, $b) {
            
            if($a['bookCount'] == $b['bookCount'])
                return 0;
            
            return $a['bookCount'] > $b['bookCount'] ? -1 : 1;
        });
        
        return $ret;
        /*
         * db.<collection>.find( { <location field> :
                         { $near :
                           { $geometry :
                              { type : "Point" ,
                                coordinates : [ <longitude> , <latitude> ] } ,
                             $maxDistance : <distance in meters>,
         *                  $spherical: true
                      } } } )
         */
    }
    
}