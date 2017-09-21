<?php

namespace Instructor;

class Auto extends Instructor{
    
    public function getInstructors($where, $limit = 50) {
        $where['automatic'] = 1;
        return parent::getInstructors($where, $limit);
    }
    
    public function findClosestInstructors($postcode, $limit = 50) {
        $maps = new GoogleMapsGeocoder($postcode.', UK', 'xml');
        $maps->geocode();
        if($maps->getLatitude()){
            return $this->listInstructors(self::$db->query("SELECT *, (3959 * acos(cos(radians('".$maps->getLatitude()."')) * cos(radians(lat)) * cos(radians(lng) - radians('".$maps->getLongitude()."')) + sin(radians('".$maps->getLatitude()."')) * sin(radians(lat)))) AS `distance` FROM `".self::INST_TABLE."` WHERE `active` = 1 AND `automatic` = 1 AND `postcodes` LIKE '%,".smallPostcode($postcode).",%' HAVING `distance` < '100' ORDER BY `distance` LIMIT ".$limit.";"));
        }
        return false;
    }
}
