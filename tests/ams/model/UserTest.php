<?php

class UserTest extends PHPUnit_Framework_TestCase
{
    public function testisAdmin()
    {
Doo::loadModel('User');
        $u = new User();
        $u->type = 'admin';
        $this->assertTrue($u->isAdmin());
    }

    public function testScopeSeenByMe()
    {
        $u = new User();
        $this->assertTrue($u->isAdmin()); //<-- Default case
        $this->assertFalse($u->isAdmin());
        //$this->assertEquals(\Slim\Log::DEBUG, $log->getLevel());
        //$this->assertSame($writer2, $log->getWriter());
    }
}

?>
