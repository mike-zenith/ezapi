<?php

class EzapiTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Ezapi\Api
     */
    protected $obj;

    public function setUp() {
        $this->obj = new \Ezapi\Api();
    }

    public function tearDown() {
        \Ezapi\Api::setDefaultCommunicator('');
        $this->obj = null;
    }

    public function testLazySetDefaultCommunicator() {
        $comm = $this->getMock('Ezapi\CommunicatorInterface');
        \Ezapi\Api::setDefaultCommunicator(get_class($comm));

        $this->assertEquals($comm, $this->obj->getCommunicator());
    }

    public function testResourceMagicMethods() {
        $mock = $this->getMock('Ezapi\Resource\Generic');

        $this->obj->foo = $mock;
        $this->assertEquals($mock, $this->obj->foo);
    }


}
 