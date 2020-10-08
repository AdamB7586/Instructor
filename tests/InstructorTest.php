<?php

namespace Instructor\Tests;

use PHPUnit\Framework\TestCase;
use Instructor\Instructor;
use DBAL\Database;

error_reporting(0);

class InstructorTest extends TestCase
{
    protected $instructor;
    protected $db;

    public function setUp(): void
    {
        $this->db = new Database($GLOBALS['HOSTNAME'], $GLOBALS['USERNAME'], $GLOBALS['PASSWORD'], $GLOBALS['DATABASE']);
        if (!$this->db->isConnected()) {
            $this->markTestSkipped(
                'No local database connection is available'
            );
        } else {
            $this->db->query(file_get_contents(dirname(dirname(__FILE__)).'/database/mysql_database.sql'));
            $this->db->query(file_get_contents(dirname(__FILE__).'/sample_data/mysql_data.sql'));
            $this->instructor = new Instructor($this->db);
        }
    }
    
    public function tearDown(): void
    {
        $this->instructor = null;
        $this->db = null;
    }
    
    /**
     * @covers Instructor\Instructor::__construct
     * @covers Instructor\Instructor::instructorStatus
     */
    public function testGetStatus()
    {
        $this->assertEquals('Active', $this->instructor->instructorStatus(1));
        $this->assertEquals('Disabled', $this->instructor->instructorStatus(0));
        $this->assertEquals('Delisted', $this->instructor->instructorStatus(3));
        $this->assertFalse($this->instructor->instructorStatus(15546));
        $this->assertFalse($this->instructor->instructorStatus('hello'));
    }
    
    /**
     * @covers Instructor\Instructor::__construct
     * @covers Instructor\Instructor::getAllInstructors
     */
    public function testListInstructors()
    {
        $this->assertArrayHasKey('id', $this->instructor->getAllInstructors(1)[3]);
        $this->assertGreaterThan(5, count($this->instructor->getAllInstructors(1)));
        $this->assertEquals(1, count($this->instructor->getAllInstructors(2)));
        $this->assertFalse($this->instructor->getAllInstructors(8));
        $this->assertFalse($this->instructor->getAllInstructors('hello'));
    }
    
    /**
     * @covers Instructor\Instructor::__construct
     * @covers Instructor\Instructor::getInstructorInfo
     */
    public function testGetInstructorInfo()
    {
        $info = $this->instructor->getInstructorInfo(2);
        $this->assertEquals('Helen Smith', $info['name']);
        $this->assertArrayHasKey('postcodes', $info);
        $person_two_info = $this->instructor->getInstructorInfo(6);
        $this->assertEquals('Bob Clark', $person_two_info['name']);
        $this->assertArrayHasKey('postcodes', $person_two_info);
        $this->assertFalse($this->instructor->getInstructorInfo(5326));
        $this->assertFalse($this->instructor->getInstructorInfo('string'));
    }
    
    /**
     * @covers Instructor\Instructor::__construct
     * @covers Instructor\Instructor::addInstructor
     */
    public function testAddInstructor()
    {
        $this->assertTrue($this->instructor->addInstructor(11, 'Steve Smith', 'test@test.com', 'https://www.steveswebsite.com', 'M', 'mypassword#', array('notes' => 'New Person', 'postcodes' => ',LS1,LS2,LS3,LS4,LS5,LS21,')));
        $this->assertEquals('Steve Smith', $this->instructor->getInstructorInfo(11)['name']);
        $this->assertFalse($this->instructor->addInstructor(11, 'Steve Smith', 'test@test.com', 'https://www.steveswebsite.com', 'M', 'mypassword#', array('notes' => 'New Person', 'postcodes' => ',LS1,LS2,LS3,LS4,LS5,LS21,')));
        $this->assertFalse($this->instructor->addInstructor(12, 'Diane Turner', 'invalidemail.com', '', 'F', 'mypassword#', array('notes' => 'New Person', 'postcodes' => ',KT1,KT2,KT3,KT6,KT16,')));
        $this->assertFalse($this->instructor->addInstructor(12, 'Diane Turner', 'test@email.com', '', 'F', 'mypassword#', 'not_an_array'));
    }
    
    /**
     * @covers Instructor\Instructor::__construct
     * @covers Instructor\Instructor::updateInstructor
     * @covers Instructor\Instructor::updateInstructorPersonalInformation
     */
    public function testUpdateInstructor()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * @covers Instructor\Instructor::__construct
     * @covers Instructor\Instructor::updateInstructorLocation
     */
    public function testUpdateInstructorLocation()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * @covers Instructor\Instructor::__construct
     * @covers Instructor\Instructor::getInstructors
     * @covers Instructor\Instructor::listInstructors
     * @covers Instructor\Instructor::instPostcodes
     * @covers Instructor\Instructor::firstname
     * @covers Instructor\Instructor::instTestimonials
     * @covers Instructor\Instructor::findClosestInstructors
     * @covers Instructor\Instructor::findInstructorsByPostcode
     * @covers Instructor\Instructor::smallPostcode
     * @covers Instructor\Instructor::replaceIncorrectNumbers
     */
    public function testGetInstructors()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * @covers Instructor\Instructor::__construct
     * @covers Instructor\Instructor::findClosestInstructorWithOffer
     * @covers Instructor\Instructor::getInstructors
     * @covers Instructor\Instructor::listInstructors
     * @covers Instructor\Instructor::instPostcodes
     * @covers Instructor\Instructor::firstname
     * @covers Instructor\Instructor::instTestimonials
     * @covers Instructor\Instructor::findClosestInstructors
     * @covers Instructor\Instructor::findInstructorsByPostcode
     * @covers Instructor\Instructor::smallPostcode
     * @covers Instructor\Instructor::replaceIncorrectNumbers
     */
    public function testGetInstructorsWithOffer()
    {
        $this->markTestIncomplete();
    }
    
    /**
     * @covers Instructor\Instructor::__construct
     * @covers Instructor\Instructor::addPriority
     * @covers Instructor\Instructor::removePriorities
     */
    public function testPriority()
    {
        $this->markTestIncomplete();
    }
}
