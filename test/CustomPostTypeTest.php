<?php
/**
 * @author Dan Bettles <dan@danbettles.net>
 */

namespace portico\test\CustomPostType;

/**
 * @author Dan Bettles <dan@danbettles.net>
 */
class Test extends \PHPUnit_Framework_TestCase
{
    public function testIsAbstract()
    {
        $oReflectionClass = new \ReflectionClass('\portico\CustomPostType');
        $this->assertTrue($oReflectionClass->isAbstract());
    }

    /**
     * @dataProvider possiblePostClassNames
     */
    public function testIsConstructedWithAPostObjectAndTheCustomFieldValuesForThePost($possiblePostClassName)
    {
        $oPost = new $possiblePostClassName();
        $oPost->ID = 1;

        $aCustomFieldValue = array('subtitle' => 'The subtitle');

        $oBlankCustomPostType = new \portico\test\BlankCustomPostType($oPost, $aCustomFieldValue);

        $this->assertSame($oPost, $oBlankCustomPostType->getPost());
        $this->assertSame($aCustomFieldValue, $oBlankCustomPostType->getCustomFieldValues());
    }

    public static function possiblePostClassNames()
    {
        return array(
            array('\stdClass'),
            array('\WP_Post'),
        );
    }

    public function testSetupIsAbstract()
    {
        $oReflectionMethod = new \ReflectionMethod('\portico\CustomPostType', 'setUp');
        $this->assertTrue($oReflectionMethod->isAbstract());
    }

    public function testGetnameReturnsTheNameOfTheCustomPostType()
    {
        $oBlankCustomPostType = new \portico\test\BlankCustomPostType(new \stdClass(), array());
        $this->assertEquals('blankcustomposttype', $oBlankCustomPostType->getName());
    }

    public function testGetnameReturnsTheNameSetWithSetname()
    {
        $oCustomPostType04 = new CustomPostType04(new \stdClass(), array());
        $this->assertEquals('foo', $oCustomPostType04->getName());
    }

    /**
     * @dataProvider invalidNames
     */
    public function testSetnameThrowsAnExceptionIfTheNameIsInvalid($p_invalidName)
    {
        $oCustomPostType05 = new CustomPostType05(new \stdClass(), array());

        try {
            //setName() must _always_ throw an exception if an invalid name is specified
            $oCustomPostType05->setName($p_invalidName);
        }
        catch (\OutOfRangeException $e) {
            if ($e->getMessage() == 'The length of the name is not between 1 and 20') {
                return;
            }
        }

        $this->fail();
    }

    public static function invalidNames()
    {
        return array(
            array(''),
            array('abcdefghijklmnopqrstu'),
        );
    }

    public function testGettitleReturnsTheTitleOfTheCustomPostType()
    {
        //We use a class whose name contains numbers to ensure that the title is correctly derived
        $oCustomPostType08 = new CustomPostType08(new \stdClass(), array());

        $this->assertEquals('Custom Post Type 08', $oCustomPostType08->getTitle());
    }

    public function testGetpluraltitleReturnsThePluralTitleOfTheCustomPostType()
    {
        //As before, we use a class whose name contains numbers to ensure that the title is correctly derived
        $oCustomPostType08 = new CustomPostType08(new \stdClass(), array());

        $this->assertEquals('Custom Post Type 08s', $oCustomPostType08->getPluralTitle());
    }

    public function testGettitleReturnsTheTitleSetWithSettitle()
    {
        $oCustomPostType10 = new CustomPostType10(new \stdClass(), array());

        $this->assertEquals('Sheep', $oCustomPostType10->getTitle());
        $this->assertEquals('Sheeps', $oCustomPostType10->getPluralTitle());
    }

    public function testGettitleReturnsTheTitlesSetWithSettitle()
    {
        $oCustomPostType09 = new CustomPostType09(new \stdClass(), array());

        $this->assertEquals('Sheep', $oCustomPostType09->getTitle());
        $this->assertEquals('Sheep', $oCustomPostType09->getPluralTitle());
    }

    public function testGetcustomfielddefinitionsReturnsAnArrayContainingTheCustomFieldsDeclaredWithAddcustomfield()
    {
        $oCustomPostType02 = new CustomPostType02(new \stdClass(), array());

        $this->assertEquals(
            $oCustomPostType02->expectedCustomFieldDefinitions,
            $oCustomPostType02->getCustomFieldDefinitions()
        );
    }

    public function testGetcustomfielddefinitionsReturnsAnEmptyArrayAtStartup()
    {
        $oCustomPostType07 = new CustomPostType07(new \stdClass(), array());
        $this->assertEquals(array(), $oCustomPostType07->getCustomFieldDefinitions());
    }

    public function testGetregistrationparametersReturnsTheRegistrationParametersSetWithSetregistrationparameters()
    {
        $oCustomPostType01 = new CustomPostType01(new \stdClass(), array());
        $this->assertEquals($oCustomPostType01->aExpectedRegistrationParameter, $oCustomPostType01->getRegistrationParameters());
    }

    public function testGetregistrationparametersCanReturnTheRegistrationParametersWithDefaultsApplied()
    {
        $oCustomPostType01 = new CustomPostType01(new \stdClass(), array());

        $aDefaultRegistrationParameter = array(
            'label' => 'Custom Post Type 01s',
            'labels' => array(
                'name' => 'Custom Post Type 01s',
                'singular_name' => 'Custom Post Type 01',
                'add_new' => 'Add New',
                'add_new_item' => "Add New Custom Post Type 01",
                'edit_item' => "Edit Custom Post Type 01",
                'new_item' => "New Custom Post Type 01",
                'view_item' => "View Custom Post Type 01",
                'search_items' => "Search Custom Post Type 01s",
                'not_found' => "No Custom Post Type 01s found",
                'not_found_in_trash' => "No Custom Post Type 01s found in Trash",
                'parent_item_colon' => "Parent Custom Post Type 01:",
            ),
            'public' => true,
            'menu_position' => 20,
            'supports' => array(
                'title',
                'editor',
                'author',
                'thumbnail',
                'excerpt',
                'trackbacks',
                'comments',
                'revisions',
                'page-attributes'
            ),
        );

        $aExpectedRegistrationParameter = array_merge(
            $aDefaultRegistrationParameter,
            $oCustomPostType01->aExpectedRegistrationParameter
        );

        $this->assertEquals($aExpectedRegistrationParameter, $oCustomPostType01->getRegistrationParameters(true));
    }

    public function testGetdefaultregistrationparametersCanBeOverriddenToChangeTheDefaultRegistrationParameters()
    {
        $oCustomPostType11 = new CustomPostType11(new \stdClass(), array());
        $this->assertEquals($oCustomPostType11->aExpectedRegistrationParameter, $oCustomPostType11->getRegistrationParameters(true));
    }

    public function testGetmetaboxhtmlReturnsHtml()
    {
        $oCustomPostType02 = new CustomPostType02(new \stdClass(), array());

        $expectedHtml = (
            '<div id="portico-customfields">' .
            '<p><label for="customposttype02_subtitle">Subtitle</label><input name="customposttype02[subtitle]" id="customposttype02_subtitle" type="text" value="Enter a subtitle"/></p>' .
            '<p class="portico-customfieldmandatory"><label for="customposttype02_license">License *</label><select name="customposttype02[license]" id="customposttype02_license"><option value="BSD" selected="selected">Simplified BSD License</option><option value="GPL2">GNU General Public License 2</option></select></p>' .
            '<p><label for="customposttype02_requirements">Requirements</label><textarea cols="60" rows="5" name="customposttype02[requirements]" id="customposttype02_requirements"></textarea></p>' .
            '</div>'
        );

        $this->assertEquals($expectedHtml, $oCustomPostType02->getMetaboxHtml());
    }

    public function testGetmetaboxhtmlReturnsHtmlWithCurrentValues()
    {
        $aCustomFieldValue = array(
            'subtitle' => array('PHP Library <span class="amp">&amp;</span> WordPress Plugin for Twitter'),
            'license' => array('GPL2'),
            'requirements' => array('SimpleXML extension'),
        );

        $oCustomPostType02 = new CustomPostType02(new \stdClass(), $aCustomFieldValue);

        $expectedHtml = (
            '<div id="portico-customfields">' .
            '<p><label for="customposttype02_subtitle">Subtitle</label><input name="customposttype02[subtitle]" id="customposttype02_subtitle" type="text" value="PHP Library &lt;span class=&quot;amp&quot;&gt;&amp;amp;&lt;/span&gt; WordPress Plugin for Twitter"/></p>' .
            '<p class="portico-customfieldmandatory"><label for="customposttype02_license">License *</label><select name="customposttype02[license]" id="customposttype02_license"><option value="BSD">Simplified BSD License</option><option value="GPL2" selected="selected">GNU General Public License 2</option></select></p>' .
            '<p><label for="customposttype02_requirements">Requirements</label><textarea cols="60" rows="5" name="customposttype02[requirements]" id="customposttype02_requirements">SimpleXML extension</textarea></p>' .
            '</div>'
        );

        $this->assertEquals($expectedHtml, $oCustomPostType02->getMetaboxHtml());
    }

    public function testSavecustomfieldvaluesSavesTheCustomFieldValues()
    {
        $oReflectionMethod = new \ReflectionMethod('\portico\CustomPostType', 'saveCustomFieldValues');
        $this->assertTrue($oReflectionMethod->isPublic());
        $this->markTestIncomplete();
    }

    public function testGetcustomfieldvalueReturnsTheRawValueOfTheSpecifiedCustomField()
    {
        $customFieldName = 'foo';
        $customFieldValue = array('bar');

        $oBlankCustomPostType = new \portico\test\BlankCustomPostType(
            new \stdClass(),
            array($customFieldName => $customFieldValue)
        );

        $this->assertEquals($customFieldValue, $oBlankCustomPostType->getCustomFieldValue($customFieldName));
    }

    public function testGetcustomfieldvalueThrowsAnExceptionIfTheSpecifiedFieldDoesNotExist()
    {
        $oBlankCustomPostType = new \portico\test\BlankCustomPostType(new \stdClass(), array());

        try {
            $oBlankCustomPostType->getCustomFieldValue('foo');
        }
        catch (\OutOfBoundsException $e) {
            if ($e->getMessage() == 'The custom field "foo" is not set') {
                return;
            }
        }

        $this->fail();
    }

    /**
     * @dataProvider defaultValuesForGetcustomfieldvalue
     */
    public function testGetcustomfieldvalueCanReturnADefaultValueIfTheSpecifiedFieldDoesNotExist($p_defaultValue)
    {
        $oBlankCustomPostType = new \portico\test\BlankCustomPostType(new \stdClass(), array());
        $this->assertSame($p_defaultValue, $oBlankCustomPostType->getCustomFieldValue('foo', $p_defaultValue));
    }

    public static function defaultValuesForGetcustomfieldvalue()
    {
        return array(
            array(false),
            array(null),
            array(array()),
        );
    }

    public function testGetsinglecustomfieldvalueReturnsTheValueOfAFieldThatCanHaveOnlyOneValue()
    {
        $customFieldName = 'foo';
        $expectedCustomFieldValue = 'bar';

        $oBlankCustomPostType = new \portico\test\BlankCustomPostType(
            new \stdClass(),
            array($customFieldName => array($expectedCustomFieldValue))
        );

        $this->assertEquals($expectedCustomFieldValue, $oBlankCustomPostType->getSingleCustomFieldValue($customFieldName));
    }

    public function testGetsinglecustomfieldvalueThrowsAnExceptionIfTheSpecifiedFieldDoesNotExist()
    {
        $oBlankCustomPostType = new \portico\test\BlankCustomPostType(new \stdClass(), array());

        try {
            $oBlankCustomPostType->getSingleCustomFieldValue('foo');
        }
        catch (\OutOfBoundsException $e) {
            if ($e->getMessage() == 'The custom field "foo" is not set') {
                return;
            }
        }

        $this->fail();
    }

    /**
     * @dataProvider defaultValuesForGetcustomfieldvalue
     */
    public function testGetsinglecustomfieldvalueCanReturnADefaultValueIfTheSpecifiedFieldDoesNotExist($p_defaultValue)
    {
        $oBlankCustomPostType = new \portico\test\BlankCustomPostType(new \stdClass(), array());
        $this->assertSame($p_defaultValue, $oBlankCustomPostType->getSingleCustomFieldValue('foo', $p_defaultValue));
    }

    public function testCustomfieldissetReturnsTrueIfTheSpecifiedCustomFieldIsSet()
    {
        $oBlankCustomPostType = new \portico\test\BlankCustomPostType(new \stdClass(), array('foo' => array('bar')));

        $this->assertTrue($oBlankCustomPostType->customFieldIsSet('foo'));
        $this->assertFalse($oBlankCustomPostType->customFieldIsSet('baz'));
    }

    public function testHascustomfieldsReturnsTrueIfTheCustomPostTypeHasCustomFields()
    {
        $oBlankCustomPostType = new \portico\test\BlankCustomPostType(new \stdClass(), array());
        $this->assertFalse($oBlankCustomPostType->hasCustomFields());

        $oCPTWithCustomFields = new \portico\test\CPTWithCustomFields(new \stdClass(), array());
        $this->assertTrue($oCPTWithCustomFields->hasCustomFields());
    }

    public function testGetmetaboxhtmlEncodesAmpersandsInValuesArrays()
    {
        $oCustomPostType = new CustomPostType12(new \stdClass(), array());

        $expectedHtml = (
            '<div id="portico-customfields">' .
            '<p><label for="customposttype12_genre">Genre</label><select name="customposttype12[genre]" id="customposttype12_genre"><option value="Drum &amp; Bass">Drum &amp; Bass</option><option value="Country &amp; Western">Country &amp; Western</option></select></p>' .
            '</div>'
        );

        $this->assertEquals($expectedHtml, $oCustomPostType->getMetaboxHtml());
    }

    /**
     * @dataProvider invalidPosts
     */
    public function testThrowsAnExceptionIfThePostIsInvalid($invalidPost)
    {
        try {
            new \portico\test\BlankCustomPostType($invalidPost, array());
        }
        catch (\InvalidArgumentException $e) {
            return $this->assertEquals('The post is invalid', $e->getMessage());
        }

        $this->fail();
    }

    public static function invalidPosts()
    {
        return array(
            array('string'),
            array(123),
            array(array()),
            array(true),
            array(null),
        );
    }
}

/*--------------------------------------------------------------------------------------------------------------------*/

class CustomPostType01 extends \portico\CustomPostType
{
    public $aExpectedRegistrationParameter = array(
        'description' => 'CustomPostType01 is a custom post type',
        'hierarchical' => true,
    );

    protected function setUp()
    {
        $this->setRegistrationParameters($this->aExpectedRegistrationParameter);
    }
}

class CustomPostType11 extends CustomPostType01
{
    protected function getDefaultRegistrationParameters()
    {
        return array();
    }
}

class CustomPostType02 extends \portico\test\CustomPostTypeWithGetcustomfielddefinitionsAccessible
{
    public $expectedCustomFieldDefinitions = array(
        'subtitle' => array(
            'name' => 'subtitle',
            'label' => 'Subtitle',
            'type' => 'string',
            'length' => 0,  /*This is pretty arbitrary at the moment*/
            'values' => null,
            'default' => 'Enter a subtitle',
            'mandatory' => false,
        ),
        'license' => array(
            'name' => 'license',
            'label' => 'License',
            'type' => 'string',
            'length' => 0,
            'values' => array('BSD' => 'Simplified BSD License', 'GPL2' => 'GNU General Public License 2'),
            'default' => 'BSD',
            'mandatory' => true,
        ),
        'requirements' => array(
            'name' => 'requirements',
            'label' => 'Requirements',
            'type' => 'string',
            'values' => null,
            'default' => null,
            'length' => null,
            'mandatory' => false,
        ),
    );

    protected function setUp()
    {
        $this->addCustomField('subtitle', 'Subtitle', array(
            'default' => 'Enter a subtitle',
        ));

        $this->addCustomField('license', 'License', array(
            'type' => 'string',
            'values' => array('BSD' => 'Simplified BSD License', 'GPL2' => 'GNU General Public License 2'),
            'default' => 'BSD',
            'mandatory' => true,
        ));

        $this->addCustomField('requirements', 'Requirements', array(
            'length' => null,
        ));
    }
}

class CustomPostType04 extends \portico\CustomPostType
{
    protected function setUp()
    {
        $this->setName('foo');
    }
}

class CustomPostType05 extends \portico\test\BlankCustomPostType
{
    public function setName($p_name)
    {
        parent::setName($p_name);
    }
}

class CustomPostType07 extends \portico\test\CustomPostTypeWithGetcustomfielddefinitionsAccessible
{
    protected function setUp()
    {
    }
}

class CustomPostType08 extends \portico\test\BlankCustomPostType
{
}

class CustomPostType09 extends \portico\test\BlankCustomPostType
{
    protected function setUp()
    {
        $this->setTitle('Sheep', 'Sheep');
    }
}

class CustomPostType10 extends \portico\test\BlankCustomPostType
{
    protected function setUp()
    {
        $this->setTitle('Sheep');
    }
}

class CustomPostType12 extends \portico\test\BlankCustomPostType
{
    protected function setUp()
    {
        $this->addCustomField('genre', 'Genre', array(
            'values' => array(
                'Drum & Bass' => 'Drum & Bass',
                'Country & Western' => 'Country & Western',
            )
        ));
    }
}