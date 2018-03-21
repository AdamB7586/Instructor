<?php

namespace Instructor\Tests;

use PHPUnit\Framework\TestCase;
use Instructor\Auto;
use DBAL\Database;

class AutoTest extends TestCase{
    protected $auto;
    protected $db;

    public function setUp() {
        $this->db = new Database($GLOBALS['HOSTNAME'], $GLOBALS['USERNAME'], $GLOBALS['PASSWORD'], $GLOBALS['DATABASE']);
        if(!$this->db->isConnected()){
            $this->markTestSkipped(
                'No local database connection is available'
            );
        }
        else{
            $this->db->query(file_get_contents(dirname(dirname(__FILE__)).'/database/mysql_database.sql'));
            $this->db->query(file_get_contents(dirname(__FILE__).'/sample_data/mysql_data.sql'));
            $this->auto = new Auto($this->db);
        }
    }
    
    public function tearDown() {
        $this->auto = null;
        $this->db = null;
    }
    
    
    /**
     * @covers Instructor\Instructor::__construct
     * @covers Instructor\Auto::getInstructors
     * @covers Instructor\Instructor::getInstructors
     * @covers Instructor\Instructor::listInstructors
     * @covers Instructor\Instructor::instPostcodes
     * @covers Instructor\Instructor::firstname
     * @covers Instructor\Instructor::instTestimonials
     * @covers Instructor\Auto::findClosestInstructors
     * @covers Instructor\Instructor::findClosestInstructors
     * @covers Instructor\Auto::findInstructorsByPostcode
     * @covers Instructor\Instructor::findInstructorsByPostcode
     * @covers Instructor\Instructor::getAPIKey
     * @covers Instructor\Instructor::smallPostcode
     * @covers Instructor\Instructor::replaceIncorrectNumbers
     */
    public function testGetInstructors(){
        $this->markTestIncomplete();
    }
}
