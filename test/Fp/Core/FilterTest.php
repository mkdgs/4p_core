<?php
/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-10-25 at 11:23:00.
 */
class FilterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Filter
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Filter;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
    

    public function provider()  {
        return array(
                array('simple string œ&é"\'(-è_çà)=“´~#{[|^@}!^$ù*,;:!%µ/§.123456789'),
                array('<html> string</string>'),
                array(1),
                array(0),
                array(null),
                array(array()),
                array(array('data','data2')),
        );
    }
}
