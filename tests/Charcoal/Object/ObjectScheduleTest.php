<?php

namespace Charcoal\Object\Tests;

use DateTime;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From Pimple
use Pimple\Container;

// From 'charcoal-object'
use Charcoal\Object\ObjectSchedule;
use Charcoal\Object\Tests\ContainerProvider;

/**
 *
 */
class ObjectScheduleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var ObjectSchedule
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $container = $this->container();

        $this->obj = $container['model/factory']->create(ObjectSchedule::class);
    }

    public function testSetTargetType()
    {
        $this->assertNull($this->obj->targetType());
        $ret = $this->obj->setTargetType('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->targetType());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setTargetType(false);
    }

    public function testSetTargetId()
    {
        $this->assertNull($this->obj->targetId());
        $ret = $this->obj->setTargetId(42);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->targetId());
    }

    public function testSetDataDiff()
    {
        $this->assertEquals([], $this->obj->dataDiff());
        $ret = $this->obj->setDataDiff(['foo'=>42]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['foo'=>42], $this->obj->dataDiff());
    }

    public function testSetProcessed()
    {
        $this->assertFalse($this->obj->processed());
        $ret = $this->obj->setProcessed(true);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->processed());
    }

    public function testSetScheduledDate()
    {
        $obj = $this->obj;
        $this->assertNull($obj->scheduledDate());
        $ret = $obj->setScheduledDate('2015-01-01 13:05:45');
        $this->assertSame($ret, $obj);
        $expected = new DateTime('2015-01-01 13:05:45');
        $this->assertEquals($expected, $obj->scheduledDate());

        $obj->setScheduledDate(null);
        $this->assertNull($obj->scheduledDate());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setScheduledDate(false);
    }

    public function testSetScheduledDateInvalidTime()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setScheduledDate('A totally invalid date time');
    }

    public function testSetProcessedDate()
    {
        $obj = $this->obj;
        $this->assertNull($obj->processedDate());
        $ret = $obj->setProcessedDate('2015-01-01 13:05:45');
        $this->assertSame($ret, $obj);
        $expected = new DateTime('2015-01-01 13:05:45');
        $this->assertEquals($expected, $obj->processedDate());

        $obj->setProcessedDate(null);
        $this->assertNull($obj->processedDate());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setProcessedDate(false);
    }

    public function testSetProcessedDateInvalidTime()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setProcessedDate('A totally invalid date time');
    }

    public function testProcess()
    {
        $container = $this->container();
        $this->obj->setModelFactory($container['model/factory']);

        $this->assertFalse($this->obj->process());

        $this->obj->setTargetType('charcoal/object/content');
        $this->assertFalse($this->obj->process());

        $this->obj->setTargetId(42);
        $this->assertFalse($this->obj->process());

        //q$this->obj->process();
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerModelFactory($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
