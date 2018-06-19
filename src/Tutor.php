<?php

namespace Instructor;

class Tutor extends Instructor {
    
    
    /**
     * Get a list of all of the tutors 
     * @param int $active If set to a number should be the active value else should be set to false for all instructors
     * @return array|false Should return an array of all existing instructors or if no values exist will return false
     */
    public function getAllTutors($active = 1) {
        return $this->db->selectAll($this->instructor_table, ['tutor' => 1, 'isactive' => $active], '*', ['id' => 'DESC']);
    }
    
    /**
     * Returns the information for a individual tutors
     * @param int $id This should be the franchise number of the instructor
     * @return array|false This should be an array of the instructor information if the id exists else will be false
     */
    public function getTutorInfo($id) {
        return $this->getInstructorInfo($id);
    }
    
    /**
     * Gets a list of all of the instructors matching the given criteria
     * @param array $where This should be the criteria that the database query needs to match
     * @param int $limit This should be the maximum number of instructors to display
     * @param boolean $active If you only wish to retrieve the active instructors set this to true else for all instructors set to false
     * @return array|false Will return a list of instructors if any match the criteria else will return false
     */
    public function getTutors($where, $limit = 50, $active = true, $order = false) {
        $where['tutor'] = 1;
        if($active === true) {
            $where['isactive'] = ['>=', 1];
        }
        return $this->listInstructors($this->db->selectAll($this->instructor_table, $where, '*', (is_array($order) ? $order : ['priority' => 'DESC', 'RAND()']), $limit));
    }
    
    /**
     * Find the closest tutors to the given postcode
     * @param string $postcode This should be the postcode that you wish to find the closest instructor to
     * @param int $limit The maximum number of instructors to display
     * @param boolean $cover If the search is only postcodes set this to true to only display instructors who have this listed as an area they cover
     * @param boolean $hasOffer If you want to prioritise those with an offer first set this to true
     * @return array|boolean If any instructors exist they will be returned as an array else will return false
     */
    public function findClosestTutors($postcode, $limit = 50, $cover = true, $hasOffer = false) {
        $maps = new GoogleMapsGeocoder($postcode.', UK', 'xml');
        if($this->getAPIKey() !== false) {$maps->setApiKey($this->getAPIKey());}
        $maps->geocode();
        if($maps->getLatitude()) {
            if($cover === true || preg_match('/([A-Z]\S\d?\d)/', $this->smallPostcode($postcode)) === true) {
                $coverSQL = " AND `postcodes` LIKE '%,".$this->smallPostcode($postcode).",%'";
                $distance = 100;
            }
            else{
                $coverSQL = "";
                $distance = 15;
            }
            return $this->listInstructors($this->db->query("SELECT *, (3959 * acos(cos(radians('{$maps->getLatitude()}')) * cos(radians(lat)) * cos(radians(lng) - radians('{$maps->getLongitude()}')) + sin(radians('{$maps->getLatitude()}')) * sin(radians(lat)))) AS `distance` FROM `{$this->instructor_table}` WHERE `tutor` = 1 AND `isactive` >= 1{$coverSQL} HAVING `distance` < {$distance} ORDER BY".($hasOffer !== false ? " `offer` DESC," : "")." `priority` DESC, `distance` ASC LIMIT {$limit};"));
        }
        return $this->findTutorsByPostcode($postcode, $limit, $hasOffer);
    }

    /**
     * Returns a list of tutors covering a given postcode area
     * @param string $postcode This should be the postcode area
     * @param int $limit The maximum number of instructors to display
     * @param boolean $hasOffer If you want to prioritise those with an offer first set this to true
     * @return array|false If any instructors exist they will be returned as an array else will return false
     */
    public function findTutorsByPostcode($postcode, $limit = 50, $hasOffer = false) {
        return $this->listInstructors($this->db->query("SELECT * FROM `{$this->instructor_table}` WHERE `tutor` = 1 AND `isactive` >= 1 AND `postcodes` LIKE '%,".$this->smallPostcode($postcode).",%' ORDER BY".($hasOffer !== false ? " `offer` DESC," : "")." `priority` DESC, RAND() LIMIT {$limit};"));
    }
}
