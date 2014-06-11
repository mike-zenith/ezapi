<?php

namespace Ezapi\Resource\Methodmap;

class CollectionTest extends \PHPUnit_Framework_TestCase {

    protected $obj;

    public function setUp() {
        $this->obj = new Collection();
    }

    public function tearDown() {
        $this->obj = null;
    }

    public function testAcceptsArray() {
        $input = array('foo' => 'bar');
        $this->obj = new Collection($input);

        $this->assertEquals($input, $this->obj->getMap());
    }

    public function testAddAcceptsItem() {
        $mock = $this->getMock('Ezapi\Resource\Methodmap\Collection\Item');
        $mock->expects($this->once())
            ->method('toArray')
            ->willReturn(array('name' => 'foo', 'method' => 'bar'));

        $this->obj->add($mock);

        $this->assertEquals(array('name' => 'foo', 'method' => 'bar'), $this->obj->get('foo'));
    }

    public function testAddThrowsErrorWhenNoMapSet() {
        $this->setExpectedException(
            '\InvalidArgumentException',
            'Map is not accepted');
        $this->obj->add('a');
    }

    public function testBuild() {
        $this->obj->build('a')->method('POST');

        $this->assertEquals(
            array('name' => 'a', 'method' => 'POST', 'headers' => array()),
            $this->obj->get('a'));
    }


}
 