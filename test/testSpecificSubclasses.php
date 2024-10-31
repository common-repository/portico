<?php
/**
 * @author Dan Bettles <dan@danbettles.net>
 */

namespace portico\test;

require_once dirname(__DIR__) . '/include/boot.php';

class BlankCustomPostType extends \portico\CustomPostType
{
    protected function setUp()
    {
    }
}

abstract class CustomPostTypeWithGetcustomfielddefinitionsAccessible extends \portico\CustomPostType
{
    public function getCustomFieldDefinitions()
    {
        return parent::getCustomFieldDefinitions();
    }
}

class CPTWithCustomFields extends \portico\CustomPostType
{
    protected function setUp()
    {
        $this->addCustomField('foo', 'Foo');
        $this->addCustomField('bar', 'Bar');
    }
}