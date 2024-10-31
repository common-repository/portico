<?php
/**
 * @package Portico
 * @author Dan Bettles <dan@danbettles.net>
 * @copyright Copyright (c) 2010, Dan Bettles
 * @license http://creativecommons.org/licenses/BSD Simplified BSD License
 */

namespace portico;

/**
 * @package Portico
 * @author Dan Bettles <dan@danbettles.net>
 */
abstract class CustomPostType
{
    /**
     * The basename of the class (i.e. the last component of the fully-qualified class name)
     * 
     * @var string
     */
    private $_classBasename;

    /**
     * @var stdClass|WP_Post 
     */
    private $oPost;

    /**
     * @var array
     */
    private $aCustomFieldValue;

    /**
     * The name of the custom post type
     * 
     * @var string
     */
    private $name = null;

    /**
     * The (singular) title of the custom content type
     * 
     * @var string
     */
    private $title = null;

    /**
     * The plural title of the custom post type
     * 
     * @var string
     */
    private $pluralTitle = null;

    /**
     * @var array
     */
    private $aRegistrationParameter = array();

    /**
     * @var array
     */
    private $aCustomFieldDefinition = array();

    /**
     * @param stdClass|WP_Post $p_oPost
     * @param array $p_aCustomFieldValue
     */
    public function __construct($p_oPost, array $p_aCustomFieldValue)
    {
        $this->setPost($p_oPost);
        $this->aCustomFieldValue = $p_aCustomFieldValue;

        $this->setUp();

        if (is_null($this->getName())) {
            $this->setName(strtolower($this->getClassBasename()));
        }

        if (is_null($this->getTitle())) {
            $this->setTitle(ucwords(ltrim(preg_replace('/[A-Z]|(?<=[a-zA-Z])[0-9]/', ' $0', $this->getClassBasename()))));
        }
    }

    /**
     * Returns the basename of the class (i.e. the last component of the fully-qualified class name)
     * 
     * @return string
     */
    private function getClassBasename()
    {
        if (! isset($this->_classBasename)) {
            $this->_classBasename = get_class($this);
    
            if (preg_match('{([^\\\\]+)$}', $this->_classBasename, $aNamePartMatch)) {
                $this->_classBasename = $aNamePartMatch[1];
            }
        }

        return $this->_classBasename;
    }

    /**
     * Returns the post used to construct the object
     * 
     * @return stdClass|WP_Post
     */
    public function getPost()
    {
        return $this->oPost;
    }

    /**
     * Returns the custom field values used to construct the object
     * 
     * @return array
     */
    public function getCustomFieldValues()
    {
        return $this->aCustomFieldValue;
    }

    /**
     * Returns TRUE if the specified custom field has a value, or FALSE otherwise
     *
     * @param string $p_name
     * @return bool
     */
    public function customFieldIsSet($p_name)
    {
        $aCustomFieldValue = $this->getCustomFieldValues();
        return isset($aCustomFieldValue[$p_name]);
    }

    /**
     * Returns an array containing the raw value(s) of the specified custom field, or, if the field is not set, a
     * default value or throws an exception
     * 
     * @param string $p_name
     * @param mixed [$p_defaultValue]
     * @return array
     * @throws OutOfBoundsException If the specified custom field is not set
     */
    public function getCustomFieldValue($p_name)
    {
        if (! $this->customFieldIsSet($p_name)) {
            if (func_num_args() > 1) {
                return func_get_arg(1);
            }

            throw new \OutOfBoundsException("The custom field \"{$p_name}\" is not set");
        }

        $aCustomFieldValue = $this->getCustomFieldValues();

        return $aCustomFieldValue[$p_name];
    }

    /**
     * Returns the value of a custom field of which there is only one instance
     * 
     * @param string $p_name
     * @param mixed [$p_defaultValue]
     * @return mixed
     * @throws OutOfBoundsException If the specified custom field is not set
     */
    public function getSingleCustomFieldValue($p_name)
    {
        //The default value must not be tampered with, so return it immediately
        if (! $this->customFieldIsSet($p_name) && (func_num_args() > 1)) {
            return func_get_arg(1);
        }

        $aValue = call_user_func_array(array($this, 'getCustomFieldValue'), func_get_args());

        return array_shift($aValue);
    }

    /**
     * Sets the name of the custom post type
     * 
     * @param string $p_name
     * @throws OutOfRangeException If the length of the name is not between 1 and 20
     */
    protected function setName($p_name)
    {
        $len = strlen($p_name);

        if (($len < 1) || ($len > 20)) {
            throw new \OutOfRangeException('The length of the name is not between 1 and 20');
        }

        $this->name = $p_name;
    }

    /**
     * Returns the name of the custom post type, which, by default, is the name of the class lowercased
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the singular and plural titles of the custom post type
     * 
     * If a plural title is not specified, it is set to the singular title suffixed with an "s"
     * 
     * @param string $p_singular
     * @param string|null [$p_plural = null]
     */
    protected function setTitle($p_singular, $p_plural = null)
    {
        $this->title = $p_singular;
        $this->pluralTitle = is_null($p_plural) ? "{$this->title}s" : $p_plural;
    }

    /**
     * Returns the (singular) title of the custom content type
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the plural title of the custom content type
     * 
     * @return string
     */
    public function getPluralTitle()
    {
        return $this->pluralTitle;
    }

    /**
     * Implement to define the custom post type
     * 
     * Called by the constructor
     */
    abstract protected function setUp();

    /**
     * Sets the registration parameters used to register the custom post-type with WordPress
     * 
     * @param array $p_aRegistrationParameter
     */
    protected function setRegistrationParameters(array $p_aRegistrationParameter)
    {
        $this->aRegistrationParameter = $p_aRegistrationParameter;
    }

    /**
     * Returns the registration parameters used to register the custom post-type with WordPress
     * 
     * @param bool [$p_applyDefaults = false]
     * @return array
     */
    public function getRegistrationParameters($p_applyDefaults = false)
    {
        $aRegistrationParameter = $this->aRegistrationParameter;

        if ($p_applyDefaults) {
            $aRegistrationParameter = array_merge($this->getDefaultRegistrationParameters(), $aRegistrationParameter);
        }

        return $aRegistrationParameter;
    }

    /**
     * Returns the default registration parameters
     * 
     * Override this method to change the default registration parameters
     * 
     * @return array
     */
    protected function getDefaultRegistrationParameters()
    {
        return array(
            'label' => $this->getPluralTitle(),
            'labels' => array(
                'name' => $this->getPluralTitle(),
                'singular_name' => $this->getTitle(),
                'add_new' => 'Add New',
                'add_new_item' => "Add New {$this->getTitle()}",
                'edit_item' => "Edit {$this->getTitle()}",
                'new_item' => "New {$this->getTitle()}",
                'view_item' => "View {$this->getTitle()}",
                'search_items' => "Search {$this->getPluralTitle()}",
                'not_found' => "No {$this->getPluralTitle()} found",
                'not_found_in_trash' => "No {$this->getPluralTitle()} found in Trash",
                'parent_item_colon' => "Parent {$this->getTitle()}:",
            ),
            'public' => true,
            'menu_position' => 20,  /*Below Pages*/
            'supports' => array(  /*All apart from custom fields*/
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
    }

    /**
     * Adds a (custom) field
     *
     * Call in setUp()
     * 
     * Options:
     * - length => An integer, or NULL for 'unlimited' - this'll give you a textarea.
     * - values => An array.  Default = NULL.
     * - default => The default value of the field.  Default = NULL.
     * - mandatory => TRUE or FALSE.  At present, no checks are carried out: the fields are marked as mandatory only.
     * 
     * @param string $p_name
     * @param string $p_label
     * @param array [$p_aOption = array()]
     */
    protected function addCustomField($p_name, $p_label, array $p_aOption = array())
    {
        $aDefaultCustomFieldProperty = array(
            'type' => 'string',
            'length' => 0,  /*Only anything (not NULL) or NULL at present*/
            'values' => null,
            'default' => null,
            'mandatory' => false,
        );

        $this->aCustomFieldDefinition[$p_name] = array_merge($aDefaultCustomFieldProperty, array(
            'name' => $p_name,
            'label' => $p_label,
        ), $p_aOption);
    }

    /**
     * Returns the definitions of all custom fields
     * 
     * @return array
     */
    protected function getCustomFieldDefinitions()
    {
        return $this->aCustomFieldDefinition;
    }

    /**
     * Returns TRUE if the custom post-type has custom fields, or FALSE otherwise
     * 
     * @return bool
     */
    public function hasCustomFields()
    {
        return count($this->getCustomFieldDefinitions()) > 0;
    }

    /**
     * Returns the HTML used to build a meta-box for the custom post-type's custom fields
     * 
     * @return string
     */
    public function getMetaBoxHtml()
    {
        $metaBoxHtml = '';

        foreach ($this->getCustomFieldDefinitions() as $customFieldName => $aCustomFieldProperty) {
            //@todo This is valid here until we start handling fields with multiple values
            $customFieldValue = $this->getSingleCustomFieldValue($customFieldName, null);

            $customFieldValueEncd = self::entityEncode(
                is_null($customFieldValue) ? $aCustomFieldProperty['default'] : $customFieldValue
            );

            //This will (help) prevent clashes
            $name = $this->getName() . "[{$customFieldName}]";
            $id = $this->getName() . '_' . $customFieldName;

            $controlLabelHtml = self::htmlLabel(
                $aCustomFieldProperty['label'] . ($aCustomFieldProperty['mandatory'] ? ' *' : ''),
                $id
            );

            $aDefaultAttribute = array('name' => $name, 'id' => $id);
            $controlHtml = '';

            switch ($aCustomFieldProperty['type']) {
                default:
                    if (is_array($aCustomFieldProperty['values'])) {
                        $controlHtml = self::htmlSelect(
                            $aCustomFieldProperty['values'],
                            $customFieldValueEncd,
                            $aDefaultAttribute
                        );
                    }
                    else if (is_null($aCustomFieldProperty['length'])) {
                        $controlHtml = self::htmlTextarea($customFieldValueEncd, $aDefaultAttribute);
                    }
                    else {
                        $controlHtml = self::htmlTextInput($customFieldValueEncd, $aDefaultAttribute);
                    }
            }

            $aBundleAttribute = array();

            if ($aCustomFieldProperty['mandatory']) {
                $aBundleAttribute = array('class' => 'portico-customfieldmandatory');
            }

            $metaBoxHtml .= self::xmlElement('p', $controlLabelHtml . $controlHtml, $aBundleAttribute);
        }

        $metaBoxHtml = "<div id=\"portico-customfields\">{$metaBoxHtml}</div>";

        return $metaBoxHtml;
    }

    /**
     * Entity-encodes the specified string
     * 
     * @param string $p_string
     * @return string
     */
    private static function entityEncode($p_string)
    {
        return htmlentities($p_string, ENT_COMPAT);
    }

    /**
     * Returns an XML element
     * 
     * @param string $p_name
     * @param string|null [$p_textContent = null]
     * @param array [$p_aAttribute = array()]
     */
    private static function xmlElement($p_name, $p_textContent = null, array $p_aAttribute = array())
    {
        $attributes = '';

        foreach ($p_aAttribute as $name => $value) {
            $attributes .= " {$name}=\"{$value}\"";
        }

        if (is_null($p_textContent)) {
            return "<{$p_name}{$attributes}/>";
        }

        return "<{$p_name}{$attributes}>{$p_textContent}</{$p_name}>";
    }

    /**
     * Returns an HTML "label" element
     * 
     * @param string $p_label
     * @param string|null [$p_for = null]
     * @param array [$p_aAttribute = array()]
     * @return string
     */
    private static function htmlLabel($p_label, $p_for = null, array $p_aAttribute = array())
    {
        $aAttribute = is_null($p_for) ? array() : array('for' => $p_for);
        return self::xmlElement('label', $p_label, array_merge($p_aAttribute, $aAttribute));
    }

    /**
     * Returns an HTML "select" element
     * 
     * @param array $p_aOption
     * @param string|null [$p_value = null] 
     * @param array [$p_aAttribute = array()]
     * @return string
     * @todo We probably shouldn't assume $p_value is always encoded
     */
    private static function htmlSelect(array $p_aOption, $p_value = null, array $p_aAttribute = array())
    {
        $optionsHtml = '';

        foreach ($p_aOption as $value => $label) {
            $encdValue = self::entityEncode($value);

            $aOptionAttribute = array('value' => $encdValue);

            if (! is_null($p_value) && ($encdValue == $p_value)) {
                $aOptionAttribute['selected'] = 'selected';
            }

            $optionsHtml .= self::xmlElement('option', self::entityEncode($label), $aOptionAttribute);
        }

        return self::xmlElement('select', $optionsHtml, $p_aAttribute);
    }

    /**
     * Returns an HTML "textarea" element
     * 
     * @param string [$p_value = '']
     * @param array [$p_aAttribute = array()]
     * @return string
     */
    private static function htmlTextarea($p_value = '', array $p_aAttribute = array())
    {
        return self::xmlElement(
            'textarea',
            (is_null($p_value) ? '' : $p_value),
            array_merge(array('cols' => 60, 'rows' => 5), $p_aAttribute)
        );
    }

    /**
     * Returns an HTML "input" element
     * 
     * @param string [$p_value = '']
     * @param array [$p_aAttribute = array()]
     * @return string
     */
    private static function htmlTextInput($p_value = '', array $p_aAttribute = array())
    {
        return self::xmlElement(
            'input',
            null,
            array_merge($p_aAttribute, array('type' => 'text', 'value' => $p_value))
        );
    }

    /**
     * Saves the custom field values
     * 
     * @todo Test this
     */
    public function saveCustomFieldValues()
    {
        $postId = $this->getPost()->ID;

        foreach ($this->getCustomFieldDefinitions() as $customFieldName => $aCustomFieldProperty) {
            //@todo This is valid here until we start handling fields with multiple values
            $customFieldValue = $this->getSingleCustomFieldValue($customFieldName, null);

            if (! is_null($customFieldValue)) {
                update_post_meta($postId, $customFieldName, $customFieldValue);
            }
        }
    }

    /**
     * @param stdClass|WP_Post $oPost
     * 
     * @throws InvalidArgumentException If the post is invalid
     */
    private function setPost($oPost)
    {
        if (! ($oPost instanceof \stdClass || $oPost instanceof \WP_Post)) {
            throw new \InvalidArgumentException('The post is invalid');
        }

        $this->oPost = $oPost;
    }
}