<?php

namespace Instructor;

use DBAL\Database;
Use GoogleMapsGeocoder;

class Instructor {
    
    protected static $db;
    protected $status = array(0 => 'Pending', 1 => 'Active', 2 => 'Disabled', 3 => 'Suspended', 4 => 'Delisted');
    
    public $instructor_table = 'instructors';
    
    /**
     * Constructor
     * @param Database $db This should be an instance of the database class
     */
    public function __construct(Database $db) {
        self::$db = $db;
    }
    
    public function instructorStatus($status){
        return $this->status[intval($status)];
    }
    
    /**
     * Get a list of all of the instructors 
     * @param inst|false $active If set to a number should be the active value else should be set to false for all instructors
     * @return array|false Should return an array of all existing instructors or if no values exist will return false
     */
    public function getAllInstructors($active = false){
        return self::$db->selectAll($this->instructor_table, array('active' => intval($active)), '*', array('FINO' => 'DESC'));
    }
    
    /**
     * Returns the information for a individual driving instructor
     * @param int $fino This should be the franchise number of the instructor
     * @return array|false This should be an array of the instructor information if the fino exists else will be false
     */
    public function getInstructorInfo($fino){
        return self::$db->select($this->instructor_table, array('fino' => intval($fino)));
    }
    
    public function addInstructor($fino, $name, $domain, $additionalInfo = []){
        
    }
    
    public function old_addInstructor($fino, $name, $gender, $phone, $mobile, $email, $website, $about, $offers, $areas, $mainarea, $pcode, $pcodeareas, $passplus, $hour, $two, $block, $midway, $semi, $boost, $week, $residential, $manual, $auto, $password){
        return $this->db->insert(self::INST_TABLE, array('fino' => $fino, 'name' => $name, 'gender' => $gender, 'phone' => $phone, 'mobile' => $mobile, 'email' => $email, 'website' => $website, 'about' => $about, 'offers' => $offers, 'areas' => $areas, 'mainarea' => $mainarea, 'pcodeareas' => $pcode, 'postcodes' => $pcodeareas, 'passplus' => intval($passplus), 'hour' => intval($hour), 'two' => intval($two), 'block' => intval($block), 'midway' => intval($midway), 'semi' => intval($semi), 'boost' => intval($boost), 'week' => intval($week), 'residential' => intval($residential), 'manual' => intval($manual), 'automatic' => intval($auto), 'password' => $password, 'hash' => md5($password)));
    }
    
    /**
     * Updates instructor information
     * @param int $fino This should be the franchise number of the instructor you are updating
     * @param array $information This should be an array of all of the information you are updating in the format or array('field' => 'value', 'fields2' => 2)
     * @return boolean If the information is successfully updated will return true else returns false
     */
    public function updateInstructor($fino, $information = []){
        if(empty(trim($information['about']))){$information['about'] = NULL;}
        if(empty(trim($information['offers']))){$information['offers'] = NULL;}
        return self::$db->update($this->instructor_table, $information, array('fino' => $fino));
    }
    
    public function old_updateInstructor($fino, $name, $areas, $mainarea, $email, $phone, $mobile, $status, $website, $about, $pcodeareas, $pcareas, $gsp, $passplus, $offers, $hour, $two, $block, $midway, $semi, $boost, $week, $manual, $auto, $residential, $notes = ''){
        if(empty(trim($about))){$about = NULL;}
        if(empty(trim($offers))){$offers = NULL;}
        return $this->db->update(self::INST_TABLE, array('name' => $name, 'areas' => $areas, 'mainarea' => $mainarea, 'email' => $email, 'phone' => $phone, 'mobile' => $mobile, 'active' => $status, 'website' => $website, 'about' => $about, 'pcodeareas' => $pcodeareas, 'postcodes' => $pcareas, 'gsp' => intval($gsp), 'passplus' => intval($passplus), 'offers' => $offers, 'hour' => intval($hour), 'two' => intval($two), 'block' => intval($block), 'midway' => intval($midway), 'semi' => intval($semi), 'boost' => intval($boost), 'week' => intval($week), 'manual' => intval($manual), 'automatic' => intval($auto), 'residential' => intval($residential), 'notes' => $notes), array('fino' => $fino));
    }
    
    /**
     * Updates the instructors personal information in the database
     * @param int $fino This should be franchise number for the instructor
     * @param array $information This should be any information that you are updating in an array
     * @return boolean If the information is updated will return true else will return false
     */
    public function updateInstructorPersonalInformation($fino, $information = []){
        foreach($information as $info => $value){
            if(empty(trim($value))){$information[$info] = NULL;}
        }
        return self::$db->update($this->instructor_table, $information, array('fino' => $fino));
    }
    
    /**
     * Update the latitude and longitude where the instructor is located
     * @param int $fino This should be the franchise number of the instructor you are updating
     * @param string $postcode This should be the postcode where the instructor is located
     * @return boolean If the information is updated will return true else returns false
     */
    public function updateInstructorLocation($fino, $postcode){
        $maps = new GoogleMapsGeocoder($postcode.', UK');
        $maps->geocode();
        if($maps->getLatitude()){
            return self::$db->update($this->instructor_table, array('lat' => $maps->getLatitude(), 'lng' => $maps->getLongitude()), array('fino' => $fino));
        }
        return false;
    }
    
    public function getInstructors($where, $limit = 50){
        $where['active'] = 1;
        return $this->listInstructors(self::$db->selectAll($this->instructor_table, $where, '*', 'RAND()', $limit));
    }
    
    public function findClosestInstructors($postcode, $limit = 50){
        $maps = new GoogleMapsGeocoder($postcode.', UK', 'xml');
        $maps->geocode();
        if($maps->getLatitude()){
            return $this->listInstructors(self::$db->query("SELECT *, (3959 * acos(cos(radians('".$maps->getLatitude()."')) * cos(radians(lat)) * cos(radians(lng) - radians('".$maps->getLongitude()."')) + sin(radians('".$maps->getLatitude()."')) * sin(radians(lat)))) AS `distance` FROM `".self::INST_TABLE."` WHERE `active` = '1' AND `postcodes` LIKE '%,".smallPostcode($postcode).",%' HAVING `distance` < '100' ORDER BY `distance` LIMIT ".$limit.";"));
        }
        return false;
    }
    
    private function listInstructors($instructors){
        if(is_array($instructors)){
            foreach($instructors as $i => $instructor){
                $instructors[$i]['postcodes'] = $this->instPostcodes($instructor['postcodes']);
                $instructors[$i]['firstname'] = $this->firstname($instructor['name']);
                //$instructors[$i]['testimonials'] = $this->instTestimonials($instructor['fino']); Need to fix this 
            }
            return $instructors;
        }
        return false;
    }
    
    private function firstname($name){
        $names = explode(' ', $name);
        return $names[0];
    }
    
    private function instPostcodes($postcodes){
        return str_replace(',', ', ', substr($postcodes, 1, -1));
    }
}
