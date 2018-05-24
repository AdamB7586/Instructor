<?php

namespace Instructor;

Use GoogleMapsGeocoder;

class Auto extends Instructor{
    /**
     * Get automatic instructors from the database
     * @param array $where This should be the queries that need to be matched
     * @param int $limit The maximum number of instructors to display
     * @param boolean $active If you only wish to retrieve the active instructors set this to true else for all instructors set to false
     * @return array|false Returns all of the instructors which match the criteria given if any exists else if no instructors exist will return false
     */
    public function getInstructors($where, $limit = 50, $active = true) {
        $where['automatic'] = 1;
        return parent::getInstructors($where, $limit, $active);
    }
    
    /**
     * Find all of the closest instructors to the given postcode
     * @param string $postcode This should be the postcode that you want to find the closest instructors to
     * @param int $limit The maximum number of instructors to display
     * @param boolean $cover If the search is only postcodes set this to true to only display instructors who have this listed as an area they cover
     * @param boolean $hasOffer If you want to prioritise those with an offer first set this to true
     * @return array|false If any instructors cover the area will return an array of their details else will return false 
     */
    public function findClosestInstructors($postcode, $limit = 50, $cover = true, $hasOffer = false) {
        $maps = new GoogleMapsGeocoder($postcode.', UK', 'xml');
        if($this->getAPIKey() !== false){$maps->setApiKey($this->getAPIKey());}
        $maps->geocode();
        if($maps->getLatitude()){
            if($cover === true || preg_match('/([A-Z]\S\d?\d)/', $this->smallPostcode($postcode)) !== false){
                $coverSQL = " AND `postcodes` LIKE '%,".$this->smallPostcode($postcode).",%'";
                $distance = 100;
            }
            else{
                $coverSQL = "";
                $distance = 15;
            }
            return $this->listInstructors($this->db->query("SELECT *, (3959 * acos(cos(radians('{$maps->getLatitude()}')) * cos(radians(lat)) * cos(radians(lng) - radians('{$maps->getLongitude()}')) + sin(radians('{$maps->getLatitude()}')) * sin(radians(lat)))) AS `distance` FROM `{$this->instructor_table}` WHERE `isactive` >= 1 AND `automatic` = 1{$coverSQL} HAVING `distance` < {$distance} ORDER BY".($hasOffer !== false ? " `offer` DESC," : "")." `distance` LIMIT ".$limit.";"));
        }
        return $this->findInstructorsByPostcode($postcode, $limit, $hasOffer);
    }
    
    /**
     * Returns a list of instructors covering a given postcode area
     * @param string $postcode This should be the postcode area
     * @param int $limit The maximum number of instructors to display
     * @param boolean $hasOffer If you want to prioritise those with an offer first set this to true
     * @return array|false If any instructors exist they will be returned as an array else will return false
     */
    public function findInstructorsByPostcode($postcode, $limit = 50, $hasOffer = false){
        return $this->listInstructors($this->db->query("SELECT * FROM `{$this->instructor_table}` WHERE `isactive` >= 1 AND `automatic` = 1 AND `postcodes` LIKE '%,".$this->smallPostcode($postcode).",%' ORDER BY".($hasOffer !== false ? " `offer` DESC," : "")." `priority` DESC, RAND() LIMIT {$limit};"));
    }
}
