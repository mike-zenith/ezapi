<?php

namespace Ezapi\Resource;

class GenericTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Resource
     */
    protected $obj;

    public function setUp() {
        $this->obj = new \Ezapi\Resource\Generic();
    }

    public function tearDown() {
        $this->obj = null;
    }

    public function testAcceptsMethodmap() {

        $methodmap = array('map' => 'foo');
        $this->obj = new \Ezapi\Resource\Generic($methodmap);

        $this->assertEquals($methodmap, $this->obj->getMethodMap());

    }

    public function testBuildLocationWithCallable() {
        $args = array('foo', 'bar');

        $callable = $this->getMock('Ezapi\Resource\Generic', array('callme'));
        $callable->expects($this->once())
            ->method('callme')
            ->with('foo', 'bar');

        $this->obj->buildLocation('foo', array($callable, 'callme'), $args);
    }

    public function testBuildLocationWithPath() {
        $method = 'methodfoo';
        $path = 'http://bar';
        $expected = array(
            'path' => $path,
            'method' => $method,
            'headers' => array(),
            'body' => null);

        $callable = $this->getMock('Ezapi\Resource\Generic', array('createLocation'));
        $callable->expects($this->once())
            ->method('createLocation')
            ->with($method, $expected);

        $callable->buildLocation($method, $path);
    }

    public function testBuildLocationStripsPathParams() {
        $method = 'methodfoo';
        $path = 'http://bar/:param1/:param2/:param3';

        $input = array(
            'path' => $path,
            'method' => $method
        );

        $expected = array(
            'path' => 'http://bar/1/2/bar',
            'method' => $method,
            'headers' => array(),
            'body' => null);

        $callable = $this->getMock('Ezapi\Resource\Generic', array('createLocation'));
        $callable->expects($this->once())
            ->method('createLocation')
            ->with($method, $expected);

        $callable->buildLocation($method, $input, array('1', '2', 'bar'));
    }

    public function testBuildLocationRemvesUnusedParamsAfterStrippingPath() {
        $method = 'methodfoo';
        $path = 'http://bar/:param1/:param2/:param3';

        $input = array(
            'path' => $path,
            'method' => $method
        );

        $expected = array(
            'path' => 'http://bar/1/2',
            'method' => $method,
            'headers' => array(),
            'body' => null);

        $callable = $this->getMock('Ezapi\Resource\Generic', array('createLocation'));
        $callable->expects($this->once())
            ->method('createLocation')
            ->with($method, $expected);

        $callable->buildLocation($method, $input, array('1', '2'));

    }

    public function testBuildLocationWithPathParamsAndBody() {
        $method = 'methodfoo';
        $path = 'http://bar/:param1/:param2/:param3';
        $body = array('bartender' => 'footender');

        $input = array(
            'path' => $path,
            'method' => $method
        );

        $expected = array(
            'path' => 'http://bar/1/2',
            'method' => $method,
            'headers' => array(),
            'body' => $body);

        $callable = $this->getMock('Ezapi\Resource\Generic', array('createLocation'));
        $callable->expects($this->once())
            ->method('createLocation')
            ->with($method, $expected);

        $callable->buildLocation($method, $input, array('1', '2', $body));
    }

    public function testBuildLocationHandlesRequiredPathParams() {
        $method = 'methodfoo';
        $path = 'http://bar/:!param1/:!param2/:!param3';
        $body = array('bartender' => 'footender');

        $input = array(
            'path' => $path,
            'method' => $method
        );

        $this->setExpectedException(
            '\InvalidArgumentException',
            'Required parameter missing: :!param3');

        $this->obj->buildLocation($method, $input, array('1', '2', $body));
    }

    public function testBuildLocationUsesArgumentHeaders() {
        $method = 'methodfoo';
        $path = 'http://bar/:param1/:param2';
        $body = array('bartender' => 'footender');

        $input = array(
            'path' => $path,
            'method' => $method,
            'headers' => array('fizz' => 'labuzz')
        );

        $expected = array(
            'path' => 'http://bar/1/2',
            'method' => $method,
            'headers' => array('fizz' => 'labuzz', 'x-foo' => 'rebar'),
            'body' => $body);

        $callable = $this->getMock('Ezapi\Resource\Generic', array('createLocation'));
        $callable->expects($this->once())
            ->method('createLocation')
            ->with($method, $expected);

        $callable->buildLocation(
            $method,
            $input,
            array('1', '2', $body, array('x-foo' => 'rebar')));
    }

    public function testAcceptsMethodMapObject() {
        $mock = $this->getMock('\Ezapi\Resource\Methodmap\Collection', array('getMap'));
        $mock->expects($this->once())
            ->method('getMap')
            ->willReturn(array(
                'foo' => array('method' => 'bar')
            ));

        $this->obj = new Generic($mock);

        $this->assertEquals(
            array('foo' => array('method' => 'bar')),
            $this->obj->getMethodMap());
    }

    public function testCallMagicMethodBuildsLocationWithNameAndArguments() {
        $name = 'foo';
        $map = array(
            'bar' => 'tender'
        );
        $args = array('flat', 'flippery');

        $mock = $this->getMock('\Ezapi\Resource\Generic', array('buildLocation'));
        $mock->expects($this->once())
            ->method('buildLocation')
            ->with($name, $map, $args);

        $mock->setMethodMap(array('foo' => $map));

        $mock->foo('flat', 'flippery');
    }

    public function testChainStoresAnotherResourceAndCallsReverseChain() {
        $mock = $this->getMock('\Ezapi\Resource\Generic');
        $mock->expects($this->once())
            ->method('chain')
            ->with('__reverse', $this->obj);

        $this->obj->chain('foo', $mock);
    }

    public function testGetMagicMethodUsesChainedResourceAndReturnsIt() {
        $mock = $this->getMock('\Ezapi\Resource\Generic');
        $mock->expects($this->once())
            ->method('chain')
            ->with('__reverse', $this->obj);

        $this->obj->chain('foo', $mock);

        $this->assertEquals($mock, $this->obj->foo);

    }

    public function testInvokingAResourceSetChainParamsAndReturnsObject() {
        $mock = $this->getMock(
            '\Ezapi\Resource\Generic',
            array('setChainParams'));

        $mock->expects($this->once())
            ->method('setChainParams')
            ->with(array(1, 2, 3))
            ->willReturn($mock);

        $this->assertEquals($mock, $mock(1, 2, 3));

    }

    public function testResourceInvocationChainUpdatesLocation() {
        $location = $this->getMock('\Ezapi\Resource\Location');
        $location->path = 'a';

        $mock = $this->getMock(
            '\Ezapi\Resource\Generic',
            array('setChainParams', 'getChainParams', 'map', 'factoryLocation'));

        $mock->expects($this->any())
            ->method('factoryLocation')
            ->willReturn($location);

        $mock->expects($this->any())
            ->method('setChainParams')
            ->willReturn($mock);

        $mock->expects($this->once())
            ->method('getChainParams')
            ->willReturn(array(1, 2, 3));

        $mock->expects($this->once())
            ->method('map')
            ->with(1, 2, 3)
            ->willReturn($location);

        $chained = $this->getMock(
            '\Ezapi\Resource\Generic',
            array('isChained'));

        $chained->expects($this->once())
            ->method('isChained', 'factoryLocation')
            ->willReturn(true);

        $chained->expects($this->any())
            ->method('factoryLocation')
            ->willReturn($location);

        $chained->setMethodMap(array('map' => 'a'));

        $mock->chain('foo', $chained);

        $final = $mock(1, 2, 3)->foo->map(4, 5, 6);
        $this->assertEquals('aa', $final->path);

    }




}
 