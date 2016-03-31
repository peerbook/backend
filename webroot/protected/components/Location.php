<?php

class Location {
    
    public static function distanceGeoJSON($from, $to) {
        return self::distance($from['coordinates'][1], $from['coordinates'][0], $to['coordinates'][1], $to['coordinates'][0]);
    }
    
    public static function distance($lat1, $lon1, $lat2, $lon2) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        return $miles * 1.609344;
    }

    /**
     * 
     * @param mixed $location 
     * 
     * @return GeoJSON
     */
    public static function convert($location) {
        $lat = 0;
        $long = 0;
        
        if(isset($location['type'], $location['coordinates']))
            return $location;
        
        if(isset($location['latitude'], $location['longitude'])) {
            $lat = $location['latitude'];
            $long = $location['longitude'];
        }
        
        if(is_array($location) && count($location) == 2) {
            $long = $location[0];
            $lat = $location[1];
        }
        
        if(is_string($location)) {
            $ex = explode(',', $location);
            $long = (double) $ex[0];
            $lat = (double) $ex[1];
        }
        
        return array(
            'type' => 'Point',
            'coordinates' => [$long, $lat] // longitude, latitude
        );
        
    }
    
    public static function isValidGeoJSON($obj) {
        return isset($obj['type'], $obj['coordinates']) && is_array($obj['coordinates']) && count($obj['coordinates']) == 2 && $obj['coordinates'][0] != 0;
    }
    
    /**
     * Convert location to adres spec
     * @param type $lat
     * @param type $lng
     * @return \Geocoder\Model\Address;
     */
    public static function latlngTo($lat, $lng) {
        $curl     = new \Ivory\HttpAdapter\CurlHttpAdapter();
        $geocoder = new \Geocoder\Provider\GoogleMaps($curl);
                    
        $address = $geocoder->reverse($lat, $lng);
        return $address->first();
    }
    
    public static function latlngToCity($lat, $lng) {        
       return self::latlngTo($lat, $lng)->getLocality();
    }
    
}
