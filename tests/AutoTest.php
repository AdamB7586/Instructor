<?php

namespace Instructor\Tests;

use PHPUnit\Framework\TestCase;
use Instructor\Auto;
use DBAL\Database;

class AutoTest extends TestCase{
    protected $auto;
    protected $db;

    public function setUp() {
        $this->db = new Database($GLOBALS['HOSTNAME'], $GLOBALS['USERNAME'], $GLOBALS['PASSWORD'], $GLOBALS['DATABASE'], '127.0.0.1', false, true, $GLOBALS['DRIVER']);
        $this->auto = new Auto($this->db);
    }
    
    protected function tearDown() {
        $this->auto = null;
        $this->db = null;
    }
}
