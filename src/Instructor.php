<?php

namespace Instructor;

use DBAL\Database;
use Jabranr\PostcodesIO\PostcodesIO;
use UserAuth\User;
use DBAL\Modifiers\Modifier;
use Instructor\Modifiers\SQLBuilder;
use Instructor\Modifiers\Format;

class Instructor extends User
{
    protected $db;
    protected $postcodeLookup;
    protected $status = [-1 => 'Suspended', 0 => 'Disabled', 1 => 'Active', 2 => 'Pending', 3 => 'Delisted'];
    
    public $instructor_table = 'instructors';
    public $testimonial_table = 'testimonials';
    
    public $priority_period = '3 months';
    
    public $display_testimonials = false;
    
    protected $querySQL = '';
    protected $postcodeInfo;
    
    /**
     * Constructor
     * @param Database $db This should be an instance of the database class
     */
    public function __construct(Database $db, $language = "en_GB")
    {
        parent::__construct($db, $language);
        $this->postcodeLookup = new PostcodesIO();
        $this->table_users = $this->instructor_table;
        $this->table_attempts = $this->instructor_table.'_attempts';
        $this->table_requests = $this->instructor_table.'_requests';
        $this->table_sessions = $this->instructor_table.'_sessions';
    }
    
    /**
     * Returns the status text for the given status number
     * @param int $status This should be the status number you wish to get the test for
     * @return string|false This will be the status text if key exists else will be false
     */
    public function instructorStatus($status)
    {
        if (array_key_exists($status, $this->status)) {
            return $this->status[intval($status)];
        }
        return false;
    }
    
    /**
     * Returns the list of statuses
     * @return array
     */
    public function listStatuses()
    {
        return $this->status;
    }
    
    /**
     * Get a list of all of the instructors
     * @param int $active If set to a number should be the active value else should be set to false for all instructors
     * @return array|false Should return an array of all existing instructors or if no values exist will return false
     */
    public function getAllInstructors($active = 1)
    {
        return $this->db->selectAll($this->table_users, ['isactive' => $active], '*', ['id' => 'DESC'], 0, false);
    }
    
    /**
     * Returns the information for a individual driving instructor
     * @param int $id This should be the franchise number of the instructor
     * @return array|false This should be an array of the instructor information if the id exists else will be false
     */
    public function getInstructorInfo($id)
    {
        $instInfo = $this->getUserInfo($id);
        if (is_array($instInfo)) {
            $instInfo['status'] = $this->status[$instInfo['isactive']];
            $instInfo['offers'] = unserialize(stripslashes($instInfo['offers']));
            $instInfo['lessons'] = unserialize(stripslashes($instInfo['lessons']));
            $instInfo['social'] = unserialize(stripslashes($instInfo['social']));
        }
        return $instInfo;
    }
    
    /**
     * Add a new instructor to the database
     * @param int $id This should be the instructors unique franchise number
     * @param string $name The instructors name
     * @param string $email The email address to associate with the instructor
     * @param string $domain The website that this instructor has been assigned
     * @param string $gender The instructors gender set to either M or F
     * @param string $password The password that the instructor has been assigned
     * @param array $additionalInfo Any additional information can be added to this as an array
     * @return boolean If the information has been successfully added will return true else will return false
     */
    public function addInstructor($id, $name, $email, $domain, $gender, $password, $additionalInfo = [])
    {
        if (!$this->getInstructorInfo($id) && is_numeric($id) && is_array($additionalInfo) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $additionalInfo['postcodes'] = ','.trim(str_replace([' ', '.'], ['', ','], $additionalInfo['postcodes']), ',').',';
            $additionalInfo['about'] = Modifier::setNullOnEmpty($additionalInfo['about']);
            $additionalInfo['offers'] = Modifier::setNullOnEmpty($additionalInfo['offers']);
            return $this->db->insert($this->table_users, array_merge(['id' => intval($id), 'name' => $name, 'gender' => $gender, 'email' => $email, 'website' => $domain, 'password' => $this->getHash($password), 'hash' => base64_encode($password)], $additionalInfo));
        }
        return false;
    }
    
    /**
     * Updates instructor information
     * @param int $id This should be the franchise number of the instructor you are updating
     * @param array $information This should be an array of all of the information you are updating in the format or array('field' => 'value', 'fields2' => 2)
     * @return boolean If the information is successfully updated will return true else returns false
     */
    public function updateInstructor($id, $information = [])
    {
        $nullIfSetAndEmpty = ['about', 'offers', 'notes'];
        foreach ($nullIfSetAndEmpty as $field) {
            if (isset($information[$field])) {
                $information[$field] = Modifier::setNullOnEmpty($information[$field]);
            }
        }
        return $this->db->update($this->table_users, $information, ['id' => $id]);
    }
    
    /**
     * Updates the instructors personal information in the database
     * @param int $id This should be franchise number for the instructor
     * @param array $information This should be any information that you are updating in an array
     * @return boolean If the information is updated will return true else will return false
     */
    public function updateInstructorPersonalInformation($id, $information = [])
    {
        foreach ($information as $info => $value) {
            $information[$info] = Modifier::setNullOnEmpty($information[$info]);
        }
        return $this->db->update($this->table_users, $information, ['id' => $id]);
    }
    
    /**
     * Update the latitude and longitude where the instructor is located
     * @param int $id This should be the franchise number of the instructor you are updating
     * @param string $postcode This should be the postcode where the instructor is located
     * @return boolean If the information is updated will return true else returns false
     */
    public function updateInstructorLocation($id, $postcode)
    {
        if ($this->checkPostcode($postcode) !== false) {
            return $this->db->update($this->table_users, ['lat' => $this->postcodeInfo->result[0]->latitude, 'lng' => $this->postcodeInfo->result[0]->longitude], ['id' => $id]);
        }
        return false;
    }
    
    /**
     * Gets a list of all of the instructors matching the given criteria
     * @param array $where This should be the criteria that the database query needs to match
     * @param int $limit This should be the maximum number of instructors to display
     * @param boolean $active If you only wish to retrieve the active instructors set this to true else for all instructors set to false
     * @param boolean|array $order If you only change the order set the field you want to order by as an array else set to false
     * @param boolean $onlyOffer Returns only those who have an offer if set to true
     * @return array|false Will return a list of instructors if any match the criteria else will return false
     */
    public function getInstructors($where, $limit = 50, $active = true, $order = false, $onlyOffer = false)
    {
        if ($active === true) {
            $where['isactive'] = ['>=', 1];
        }
        if ($onlyOffer === true) {
            $where['offers'] = 'IS NOT NULL';
        }
        return $this->listInstructors($this->db->selectAll($this->table_users, $where, '*', (is_array($order) ? $order : ['priority' => 'DESC', 'offers' => 'DESC', 'RAND()']), $limit, false));
    }
    
    /**
     * Find the closest instructors to the given postcode
     * @param string $postcode This should be the postcode that you wish to find the closest instructor to
     * @param int $limit The maximum number of instructors to display
     * @param boolean $cover If the search is only postcodes set this to true to only display instructors who have this listed as an area they cover
     * @param boolean $hasOffer If you want to prioritise those with an offer first set this to true
     * @param boolean $onlyOffer Returns only those who have an offer if set to true
     * @param array $additionalInfo Any additional SQl query parameters should be added as an array here
     * @return array|boolean If any instructors exist they will be returned as an array else will return false
     */
    public function findClosestInstructors($postcode, $limit = 50, $cover = true, $hasOffer = false, $onlyOffer = false, $additionalInfo = [])
    {
        if ($this->checkPostcode($postcode) !== false) {
            $distance = 100;
            if ($cover === true || preg_match('/([A-Z]\S\d?\d)/', Format::smallPostcode($postcode)) === true) {
                $coverSQL = " AND `postcodes` LIKE '%,".Format::smallPostcode($postcode).",%'";
            } else {
                $coverSQL = "";
            }
            return $this->listInstructors($this->db->query("SELECT *, (3959 * acos(cos(radians('{$this->postcodeInfo->result[0]->latitude}')) * cos(radians(lat)) * cos(radians(lng) - radians('{$this->postcodeInfo->result[0]->longitude}')) + sin(radians('{$this->postcodeInfo->result[0]->latitude}')) * sin(radians(lat)))) AS `distance` FROM `{$this->table_users}` WHERE `isactive` >= 1{$this->querySQL}{$coverSQL}{$this->getOffersString($onlyOffer)}".SQLBuilder::createAdditionalString($additionalInfo)." HAVING `distance` < {$distance} ORDER BY `priority` DESC,{$this->getAdditionalOffersSQLOrder($hasOffer)} `distance` ASC LIMIT {$limit};", SQLBuilder::$values, 300));
        }
        return $this->findInstructorsByPostcode($postcode, $limit, $hasOffer, false, $additionalInfo);
    }
    
    /**
     * Check the postcode given
     * @param string $postcode This should be the given postcode
     * @return boolean If the postcode is valid true will be returned else will return false
     */
    protected function checkPostcode($postcode)
    {
        $this->postcodeInfo = $this->postcodeLookup->query($postcode);
        if ($this->postcodeInfo->status === 200 && !empty($this->postcodeInfo->result)) {
            return true;
        }
        return false;
    }
    
    /**
     * Returns a list of instructors covering a given postcode area
     * @param string $postcode This should be the postcode area
     * @param int $limit The maximum number of instructors to display
     * @param boolean $hasOffer If you want to prioritise those with an offer first set this to true
     * @param boolean $onlyOffer Returns only those who have an offer if set to true
     * @param array $additionalInfo Any additional SQl query parameters should be added as an array here
     * @return array|false If any instructors exist they will be returned as an array else will return false
     */
    public function findInstructorsByPostcode($postcode, $limit = 50, $hasOffer = false, $onlyOffer = false, $additionalInfo = [])
    {
        return $this->listInstructors($this->db->query("SELECT * FROM `{$this->table_users}` WHERE `isactive` >= 1 AND `postcodes` LIKE '%,".Format::smallPostcode($postcode).",%'{$this->querySQL}{$this->getOffersString($onlyOffer)}".SQLBuilder::createAdditionalString($additionalInfo)." ORDER BY `priority` DESC,{$this->getAdditionalOffersSQLOrder($hasOffer)} RAND() LIMIT {$limit};", SQLBuilder::$values, 300));
    }
    
    /**
     * Returns a list of instructors covering a given postcode area array
     * @param array $postcodes This should be the postcode areas as an array
     * @param int $limit The maximum number of instructors to display
     * @param boolean $hasOffer If you want to prioritise those with an offer first set this to true
     * @param boolean $onlyOffer Returns only those who have an offer if set to true
     * @param array $additionalInfo Any additional SQl query parameters should be added as an array here
     * @return array|false If any instructors exist they will be returned as an array else will return false
     */
    public function findInstructorsByPostcodeArray($postcodes, $limit = 50, $hasOffer = false, $onlyOffer = false, $additionalInfo = [])
    {
        if (is_array($postcodes)) {
            $sql = [];
            $values = [];
            foreach (array_filter($postcodes) as $postcode) {
                $sql[] = "`postcodes` LIKE ?";
                $values[] = '%,'.$postcode.',%';
            }
            return $this->listInstructors($this->db->query("SELECT * FROM `{$this->table_users}` WHERE `isactive` >= 1{$this->querySQL}{$this->getOffersString($onlyOffer)} AND ".implode(" OR ", $sql).SQLBuilder::createAdditionalString($additionalInfo)." ORDER BY `priority` DESC,{$this->getAdditionalOffersSQLOrder($hasOffer)} RAND() LIMIT {$limit};", array_values(array_merge($values, SQLBuilder::$values)), 3600));
        }
        return false;
    }
    
    /**
     * Find the closest instructors with those who have offers prioritised first
     * @param string $postcode This should be the postcode that you wish to find the closest instructor to
     * @param int $limit The maximum number of instructors to display
     * @param boolean $cover If the search is only postcodes set this to true to only display instructors who have this listed as an area they cover
     * @param array $additionalInfo Any additional SQl query parameters should be added as an array here
     * @return array|boolean If any instructors exist they will be returned as an array else will return false
     */
    public function findClosestInstructorWithOffer($postcode, $limit = 50, $cover = true, $additionalInfo = [])
    {
        return $this->findClosestInstructors($postcode, $limit, $cover, true, true, $additionalInfo);
    }
    
    /**
     * List of all of the instructors and get additional variables
     * @param array $instructors An array of the instructors results from the database so additional information can be retrieved and added
     * @return array|false If any instructors are returned their information will be returned else if none exists will return false
     */
    protected function listInstructors($instructors)
    {
        if (is_array($instructors)) {
            foreach ($instructors as $i => $instructor) {
                $instructors[$i]['offers'] = unserialize($instructor['offers']);
                $instructors[$i]['lessons'] = unserialize($instructor['lessons']);
                $instructors[$i]['social'] = unserialize($instructor['social']);
                $instructors[$i]['postcodes'] = Format::instPostcodes($instructor['postcodes']);
                $instructors[$i]['firstname'] = Format::firstname($instructor['name']);
                $instructors[$i]['testimonials'] = $this->instTestimonials($instructor['id']);
                unset($instructors[$i]['password']);
                unset($instructors[$i]['hash']);
            }
            return $instructors;
        }
        return false;
    }
    
    /**
     * Add priority to a given instructor
     * @param int $id This should be the instructors unique franchise number
     * @return boolean If the record is updated will return true else returns false
     */
    public function addPriority($id)
    {
        if (is_numeric($id)) {
            $date = new \DateTime();
            return $this->db->update($this->table_users, ['priority' => 1, 'priority_start_date' => $date->format('Y-m-d H:i:s')], ['id' => intval($id)]);
        }
        return false;
    }

    /**
     * Remove anyone from the priority list who has been there for longer than the allotted period
     */
    public function removePriorities()
    {
        $date = new \DateTime();
        $date->modify("-{$this->priority_period}");
        $this->db->update($this->table_users, ['priority' => 0, 'priority_start_date' => null], ['priority' => 1, 'priority_start_date' => ['<=', $date->format('Y-m-d H:i:s')]]);
    }
    
    /**
     * Returns the SQL string if only to display offers
     * @param boolean $onlyOffer If you only want to display offers set to true
     * @return string The SQL offers string should be returned
     */
    private function getOffersString($onlyOffer = false)
    {
        if ($onlyOffer === true) {
            return ' AND `offers` IS NOT NULL';
        }
        return '';
    }
    
    /**
     * check to see if to return an offers order for the SQL
     * @param boolean $hasOffer If to order by those with and offer set to true
     * @return string The SQL offers order string should be returned
     */
    private function getAdditionalOffersSQLOrder($hasOffer = false)
    {
        if ($hasOffer !== false) {
            return ' `offer` DESC,';
        }
        return '';
    }
    
    /**
     * Return any instructor testimonials in a random order
     * @param int $id This should be the instructors unique franchise number
     * @param int $limit The maximum number of testimonials to show
     * @return array|false If any testimonials exist they will be returned as an array else will return false
     */
    public function instTestimonials($id, $limit = 5)
    {
        if ($this->display_testimonials === true) {
            return $this->db->selectAll($this->testimonial_table, ['id' => intval($id)], '*', 'RAND()', intval($limit));
        }
        return false;
    }
    
    /**
     * Get the information for selected instructors
     * @param string $list the list of instructor ids (must be comma separated)
     * @return array|false If the instructors exist they will be returned as an array else will return false
     */
    public function getSelectedInstructors($list)
    {
        return $this->getActiveInstructorsByList($list, 'id');
    }
    
    /**
     * Get the information for instructors in selected areas
     * @param string $list The list of main postcode areas e.g. AB,WF,TN (must be comma separated)
     * @return array|false Returns an array of instructors if any exist in the area else returns false
     */
    public function getInstructorsBySelectedArea($list)
    {
        return $this->getActiveInstructorsByList($list, 'postcode_areas', 'LIKE ?');
    }
    
    /**
     * Gets a list of active instructors from lists and selected fields
     * @param string $list This should be the list that you want to retrieve instructors for
     * @param string $field The field that you are searching
     * @param string $operator The SQL operator to use in the search query
     * @return array|false If the given information is correct and instructors exists an array will be returned else returns false
     */
    protected function getActiveInstructorsByList($list, $field, $operator = '= ?')
    {
        $listArray = Format::getListArray($list);
        $instructors = [];
        $values = [];
        $numItems = count($listArray);
        for ($s = 0; $s < $numItems; $s++) {
            $instructors[] = sprintf("`%s` %s", $field, $operator);
            $values[] = (strpos($operator, 'LIKE') !== false ? '%,' : '').$listArray[$s].(strpos($operator, 'LIKE') !== false ? ',%' : '');
        }
        return $this->db->query("SELECT * FROM `{$this->table_users}` WHERE `isactive` >= 1 AND (".implode(' OR ', $instructors).");", $values, 3600);
    }
    
    /**
     * Sets the database delete cache field
     * @param int|boolean $id This should be the instructors number for an individual instructor else set to false to set for all instructors
     * @return boolean Returns true if successfully updated else returns false
     */
    public function deleteCache($id = false)
    {
        $where = [];
        if (is_numeric($id)) {
            $where = ['id' => $id];
        }
        return $this->db->update($this->table_users, ['delcache' => 1], $where);
    }
    
    /**
     * Return only the first name for the instructor
     * @param string $name The full name for the instructor
     * @return string Will return only the first name
     */
    public function firstname($name){
        return Format::firstname($name);
    }
}
