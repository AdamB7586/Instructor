<?php

namespace Instructor\Tests;

use PHPUnit\Framework\TestCase;
use Instructor\Instructor;
use DBAL\Database;

class InstructorTest extends TestCase{
    protected $instructor;
    protected $db;

    public function setUp() {
        $this->db = new Database($GLOBALS['HOSTNAME'], $GLOBALS['USERNAME'], $GLOBALS['PASSWORD'], $GLOBALS['DATABASE'], '127.0.0.1', false, true, $GLOBALS['DRIVER']);
        $this->instructor = new Instructor($this->db);
    }
    
    public function tearDown() {
        $this->instructor = null;
        $this->db = null;
    }
    
    public function testExample(){
        $this->markTestIncomplete();
    }
}
