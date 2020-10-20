<?php

namespace Instructor;

class Tutor extends Instructor
{
    
    
    /**
     * Get a list of all of the tutors
     * @param int $active If set to a number should be the active value else should be set to false for all instructors
     * @return array|false Should return an array of all existing instructors or if no values exist will return false
     */
    public function getAllTutors($active = 1)
    {
        return $this->db->selectAll($this->instructor_table, array_filter(array_merge(['tutor' => 1], ['isactive' => (is_numeric($active) ? ($active >= 1 ? ['>=', 1] : ['<=' => 0]) : [])])), '*', ['id' => 'DESC']);
    }
    
    /**
     * Returns the information for a individual tutors
     * @param int $id This should be the franchise number of the instructor
     * @return array|false This should be an array of the instructor information if the id exists else will be false
     */
    public function getTutorInfo($id)
    {
        return $this->getInstructorInfo($id);
    }
    
    /**
     * Gets a list of all of the instructors matching the given criteria
     * @param array $where This should be the criteria that the database query needs to match
     * @param int $limit This should be the maximum number of instructors to display
     * @param boolean $active If you only wish to retrieve the active instructors set this to true else for all instructors set to false
     * @return array|false Will return a list of instructors if any match the criteria else will return false
     */
    public function getTutors($where, $limit = 100, $active = true, $order = false)
    {
        $where['tutor'] = 1;
        if ($active === true) {
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
    public function findClosestTutors($postcode, $limit = 100, $cover = true, $hasOffer = false)
    {
        $this->querySQL = " AND `tutor` = 1";
        return $this->findClosestInstructors($postcode, $limit, $cover, $hasOffer);
    }

    /**
     * Returns a list of tutors covering a given postcode area
     * @param string $postcode This should be the postcode area
     * @param int $limit The maximum number of instructors to display
     * @param boolean $hasOffer If you want to prioritise those with an offer first set this to true
     * @return array|false If any instructors exist they will be returned as an array else will return false
     */
    public function findTutorsByPostcode($postcode, $limit = 100, $hasOffer = false)
    {
        $this->querySQL = " AND `tutor` = 1";
        return $this->findInstructorsByPostcode($postcode, $limit, $hasOffer);
    }
    
    /**
     * Override parent class
     * @return boolean
     */
    public function removePriorities()
    {
        return false;
    }
}
