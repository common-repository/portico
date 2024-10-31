<?php
/**
 * @author Dan Bettles <dan@danbettles.net>
 */

namespace portico\test\exception\NotACustomPostType;

/**
 * @author Dan Bettles <dan@danbettles.net>
 */
class Test extends \PHPUnit_Framework_TestCase
{
    public function testIsAnException()
    {
        $oReflectionClass = new \ReflectionClass('\portico\exception\NotACustomPostType');
        $this->assertTrue($oReflectionClass->isSubclassOf('\Exception'));
    }
}