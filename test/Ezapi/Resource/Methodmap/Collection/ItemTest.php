<?php

namespace Ezapi\Resource\Methodmap\Collection;


class ItemTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Item
     */
    protected $obj;

    public function setUp() {
        $this->obj = new Item();
    }

    public function testSettersAndGetters() {
        $this->obj->method('GET');
        $this->assertEquals('GET', $this->obj->method());

        $callback = function ($a) { return 'b'; };
        $this->obj->parser($callback);
        $this->assertEquals($callback, $this->obj->parser());

        $this->obj->path('b');
        $this->assertEquals('b', $this->obj->path());

        $this->obj->name('c');
        $this->assertEquals('c', $this->obj->name());

        $this->obj->callback($callback);
        $this->assertEquals($callback, $this->obj->callback());

        $this->obj->body('d');
        $this->assertEquals('d', $this->obj->body());

        $this->obj->header('e', 'e1');
        $this->obj->header('f', 'f1');
        $this->assertEquals(array('e' => 'e1', 'f' => 'f1'), $this->obj->header());


    }

    public function testMethodThrowsErrorWhenSettingNotAcceptedMethod() {
        $this->setExpectedException(
            '\InvalidArgumentException',
            'Method not accepted');
        $this->obj->method('ja');
    }

    public function testToArray() {
        $expected = array(
            'body' => 'nup',
            'method' => 'POST',
            'headers' => array(),
            'name' => 'a'
        );
        $this->obj->name('a')
            ->method('POST')
            ->body('nup');

        $this->assertEquals($expected, $this->obj->toArray());
    }

    public function testToArrayThrowsExceptionWhenNoNameSet() {
        $this->setExpectedException(
            '\InvalidArgumentException',
            'Name is not set');
        $this->obj->toArray();
    }

    public function testToArrayThrowsExceptionWhenNoMethodSet() {
        $this->setExpectedException(
            '\InvalidArgumentException',
            'Method is not set');
        $this->obj->name('a')->toArray();
    }


} 