<?php
/**
 * @author Dan Bettles <dan@danbettles.net>
 */

namespace portico\test\Registry;

/**
 * @author Dan Bettles <dan@danbettles.net>
 */
class Test extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \portico\Registry::setInstance(new \portico\Registry(array()));
    }

    public function testIsConstructedWithTheRequestParameters()
    {
        $aRequestParameter = array('foo' => 'bar');
        $oRegistry = new \portico\Registry($aRequestParameter);

        $this->assertEquals($aRequestParameter, $oRegistry->getRequestParameters());
    }

    public function testGetrequestcustomfieldvaluesReturnsTheCustomFieldValuesForTheSpecifiedCustomPostTypeSubmittedInTheRequestParameters()
    {
        $aRequestParameter = array(
            'foo' => 'bar',
            'podcast' => array(
                'baz' => 'bip',
            ),
        );

        $aExpectedCustomFieldValue = array(
            'baz' => array('bip'),
        );

        $oRegistry = new \portico\Registry($aRequestParameter);

        $this->assertEquals($aExpectedCustomFieldValue, $oRegistry->getRequestCustomFieldValues('podcast'));
    }

    public function testGetrequestcustomfieldvaluesThrowsAnExceptionIfThereAreNoCustomFieldValuesForTheSpecifiedCustomPostType()
    {
        $oRegistry = new \portico\Registry(array());

        try {
            $oRegistry->getRequestCustomFieldValues('podcast');
        }
        catch (\OutOfBoundsException $e) {
            if ($e->getMessage() == 'No custom field values were submitted for the custom post-type "podcast"') {
                return;
            }
        }

        $this->fail();
    }

    /**
     * @dataProvider defaultValuesForGetrequestcustomfieldvalues
     */
    public function testGetrequestcustomfieldvaluesCanReturnADefaultValueIfThereAreNoCustomFieldValuesForTheSpecifiedCustomPostType($p_defaultValue)
    {
        $oRegistry = new \portico\Registry(array());
        $this->assertSame($p_defaultValue, $oRegistry->getRequestCustomFieldValues('podcast', $p_defaultValue));
    }

    public function defaultValuesForGetrequestcustomfieldvalues()
    {
        return array(
            array(array()),
            array(false),
            array(null),
        );
    }

    public function testGetinstanceReturnsTheInstanceSetWithSetinstance()
    {
        $oRegistry = new \portico\Registry(array());
        \portico\Registry::setInstance($oRegistry);

        $this->assertSame($oRegistry, \portico\Registry::getInstance());
    }

    public function testGetinstanceAlwaysReturnsTheSameInstanceOfRegistry()
    {
        $oRegistry = \portico\Registry::getInstance();

        $this->assertTrue($oRegistry instanceof \portico\Registry);
        $this->assertSame($oRegistry, \portico\Registry::getInstance());
    }

    public function testAddcustomposttypesAddsCustomPostTypes()
    {
        $oRegistry = new \portico\Registry(array());

        $aExpectedCustomPostTypeClassName = array(
            'blankcustomposttype' => 'portico\test\BlankCustomPostType',
            'customposttype01' => __NAMESPACE__ . '\CustomPostType01',
        );

        $oRegistry->addCustomPostTypes($aExpectedCustomPostTypeClassName);

        $this->assertEquals($aExpectedCustomPostTypeClassName, $oRegistry->getCustomPostTypeClassNames());
    }

    public function testAddcustomposttypesThrowsAnExceptionIfOneOfTheSpecifiedClassesDoesNotExist()
    {
        $customPostTypeClassName = __NAMESPACE__ . '\Foo';
        $oRegistry = new \portico\Registry(array());

        try {
            $oRegistry->addCustomPostTypes(array($customPostTypeClassName));
        }
        catch (\InvalidArgumentException $e) {
            if ($e->getMessage() == "The class \"{$customPostTypeClassName}\" is not loaded") {
                return;
            }
        }

        $this->fail();
    }

    public function testAddcustomposttypesThrowsAnExceptionIfOneOfTheSpecifiedClassesIsNotACustomPostType()
    {
        $customPostTypeClassName = __NAMESPACE__ . '\NotACustomPostType';
        $oRegistry = new \portico\Registry(array());

        try {
            $oRegistry->addCustomPostTypes(array($customPostTypeClassName));
        }
        catch (\portico\exception\NotACustomPostType $e) {
            if ($e->getMessage() == "The class \"{$customPostTypeClassName}\" is not a custom post type") {
                return;
            }
        }

        $this->fail();
    }

    public function testAddcustomposttypesReturnsTheRegistry()
    {
        $oRegistry = new \portico\Registry(array());
        $actualReturnValue = $oRegistry->addCustomPostTypes(array());

        $this->assertSame($oRegistry, $actualReturnValue);
    }

    public function testCustomposttypewasaddedReturnsTrueIfTheSpecifiedCustomPostTypeWasAdded()
    {
        $oRegistry = new \portico\Registry(array());
        $customPostTypeName = 'blankcustomposttype';
        $oRegistry->addCustomPostTypes(array($customPostTypeName => 'portico\test\BlankCustomPostType'));

        $this->assertTrue($oRegistry->customPostTypeWasAdded($customPostTypeName));
        $this->assertFalse($oRegistry->customPostTypeWasAdded('foobar'));
    }

    public function testTryaddcustomposttypesTriesToAddAllTheSpecifiedCustomPostTypes()
    {
        $oRegistry = new \portico\Registry(array());

        $aValidCustomPostTypeClassName = array(
            'blankcustomposttype' => 'portico\test\BlankCustomPostType',
            'customposttype01' => __NAMESPACE__ . '\CustomPostType01',
        );

        $aInvalidCustomPostTypeClassName = array(
            __NAMESPACE__ . '\NotACustomPostType',
        );

        $oRegistry->tryAddCustomPostTypes(array_merge($aValidCustomPostTypeClassName, $aInvalidCustomPostTypeClassName));

        $this->assertEquals($aValidCustomPostTypeClassName, $oRegistry->getCustomPostTypeClassNames());
    }

    public function testTryaddcustomposttypesReturnsTheRegistry()
    {
        $oRegistry = new \portico\Registry(array());
        $actualReturnValue = $oRegistry->tryAddCustomPostTypes(array());

        $this->assertSame($oRegistry, $actualReturnValue);
    }

    public function testGetcustomposttypeclassnamesReturnsAnEmptyArrayAtStartup()
    {
        $oRegistry = new \portico\Registry(array());
        $this->assertEquals(array(), $oRegistry->getCustomPostTypeClassNames());
    }

    public function testGetcustomposttypeclassnameReturnsTheClassNameOfTheSpecifiedCustomPostType()
    {
        $oRegistry = new \portico\Registry(array());
        $className = 'portico\test\BlankCustomPostType';
        $oRegistry->addCustomPostTypes(array($className));

        $this->assertEquals($className, $oRegistry->getCustomPostTypeClassName('blankcustomposttype'));
    }

    public function testGetcustomposttypeclassnameThrowsAnExceptionIfTheSpecifiedCustomPostTypeHasNotBeenAdded()
    {
        $oRegistry = new \portico\Registry(array());

        try {
            $oRegistry->getCustomPostTypeClassName('podcast');
        }
        catch (\OutOfBoundsException $e) {
            if ($e->getMessage() == 'The custom post-type "podcast" has not been added') {
                return;
            }
        }

        $this->fail();
    }
}

class CustomPostType01 extends \portico\test\BlankCustomPostType
{
}

class NotACustomPostType
{
}