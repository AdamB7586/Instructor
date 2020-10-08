<?php

namespace Instructor;

class Auto extends Instructor
{
    /**
     * Get automatic instructors from the database
     * @param array $where This should be the queries that need to be matched
     * @param int $limit The maximum number of instructors to display
     * @param boolean $active If you only wish to retrieve the active instructors set this to true else for all instructors set to false
     * @param array|false Order the instructors by a given field and direction as array value or set to false
     * @return array|false Returns all of the instructors which match the criteria given if any exists else if no instructors exist will return false
     */
    public function getInstructors($where, $limit = 50, $active = true, $order = false)
    {
        $where['automatic'] = 1;
        return parent::getInstructors($where, $limit, $active, $order);
    }
    
    /**
     * Find all of the closest instructors to the given postcode
     * @param string $postcode This should be the postcode that you want to find the closest instructors to
     * @param int $limit The maximum number of instructors to display
     * @param boolean $cover If the search is only postcodes set this to true to only display instructors who have this listed as an area they cover
     * @param boolean $hasOffer If you want to prioritise those with an offer first set this to true
     * @return array|false If any instructors cover the area will return an array of their details else will return false
     */
    public function findClosestInstructors($postcode, $limit = 50, $cover = true, $hasOffer = false)
    {
        $this->querySQL = " AND `automatic` = 1";
        return parent::findClosestInstructors($postcode, $limit, $cover, $hasOffer);
    }
    
    /**
     * Returns a list of instructors covering a given postcode area
     * @param string $postcode This should be the postcode area
     * @param int $limit The maximum number of instructors to display
     * @param boolean $hasOffer If you want to prioritise those with an offer first set this to true
     * @return array|false If any instructors exist they will be returned as an array else will return false
     */
    public function findInstructorsByPostcode($postcode, $limit = 50, $hasOffer = false)
    {
        $this->querySQL = " AND `automatic` = 1";
        return parent::findInstructorsByPostcode($postcode, $limit, $hasOffer);
    }
    
    /**
     * Returns a list of instructors covering a given postcode area array
     * @param array $postcodes This should be the postcode areas as an array
     * @param int $limit The maximum number of instructors to display
     * @param boolean $hasOffer If you want to prioritise those with an offer first set this to true
     * @return array|false If any instructors exist they will be returned as an array else will return false
     */
    public function findInstructorsByPostcodeArray($postcodes, $limit = 50, $hasOffer = false)
    {
        $this->querySQL = " AND `automatic` = 1";
        return parent::findInstructorsByPostcodeArray($postcodes, $limit, $hasOffer);
    }
}
