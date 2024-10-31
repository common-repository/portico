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
class Registry
{
    /**
     * The single instance of Registry
     * 
     * @var portico\Registry
     */
    private static $oInstance;

    /**
     * The request parameters used to construct the instance
     * 
     * @var array
     */
    private $aRequestParameter;

    /**
     * An array containing the class-names of custom post-types that have been added to the registry
     * 
     * @var array
     */
    private $aCustomPostTypeClassName = array();

    /**
     * Treat Registry a Singleton and don't call the constructor directly: use getInstance() instead
     * 
     * @param array $p_aRequestParameter
     */
    public function __construct(array $p_aRequestParameter)
    {
        $this->aRequestParameter = $p_aRequestParameter;
    }

    /**
     * Returns the request parameters used to construct the instance
     * 
     * @return array
     */
    public function getRequestParameters()
    {
        return $this->aRequestParameter;
    }

    /**
     * Returns the custom field values for the specified custom post-type submitted in the request parameters, or, if no
     * custom field values were submitted, returns the default value, or throws an exception
     * 
     * @param string $p_customPostTypeName
     * @param mixed [$p_defaultValue]
     * @return mixed
     * @throws OutOfBoundsException If no custom field values were submitted for the specified custom post-type 
     */
    public function getRequestCustomFieldValues($p_customPostTypeName)
    {
        $aRequestParameter = $this->getRequestParameters();

        if (! isset($aRequestParameter[$p_customPostTypeName])) {
            if (func_num_args() > 1) {
                return func_get_arg(1);
            }

            throw new \OutOfBoundsException(
                "No custom field values were submitted for the custom post-type \"{$p_customPostTypeName}\""
            );
        }

        $aCustomFieldValue = $aRequestParameter[$p_customPostTypeName];

        foreach ($aCustomFieldValue as $name => &$value) {
            $value = array($value);
        }

        return $aCustomFieldValue;
    }

    /**
     * Sets the single instance of Registry
     * 
     * @param portico\Registry $p_oRegistry
     */
    public static function setInstance(Registry $p_oRegistry)
    {
        self::$oInstance = $p_oRegistry;
    }

    /**
     * Returns the single instance of Registry
     * 
     * @return portico\Registry
     */
    public static function getInstance()
    {
        return self::$oInstance;
    }

    /**
     * Adds custom post-types to the registry
     * 
     * @param array $p_aClassName
     * @return portico\Registry $this
     * @throws InvalidArgumentException The specified class is not loaded
     * @throws InvalidArgumentException If the specified class is not a custom post type
     */
    public function addCustomPostTypes(array $p_aClassName)
    {
        foreach ($p_aClassName as $className) {
            $this->addCustomPostType($className);
        }

        return $this;
    }

    /**
     * Adds a custom post-type to the registry
     * 
     * @param string $p_className
     * @throws InvalidArgumentException The specified class is not loaded
     * @throws InvalidArgumentException If the specified class is not a custom post type
     */
    private function addCustomPostType($p_className)
    {
        if (! class_exists($p_className)) {
            throw new \InvalidArgumentException("The class \"{$p_className}\" is not loaded");
        }

        if (! is_subclass_of($p_className, __NAMESPACE__ . '\CustomPostType')) {
            throw new exception\NotACustomPostType("The class \"{$p_className}\" is not a custom post type");
        }

        $oCustomPostType = $this->createCustomPostType($p_className);
        $this->aCustomPostTypeClassName[$oCustomPostType->getName()] = get_class($oCustomPostType);
    }

    /**
     * Tries to add custom post-types to the registry
     * 
     * Unlike addCustomPostTypes(), this method will not throw an exception if there's a problem with a class: instead,
     * it'll keep trying to add custom post types
     * 
     * @param array $p_aClassName
     * @return portico\Registry $this
     */
    public function tryAddCustomPostTypes(array $p_aClassName)
    {
        foreach ($p_aClassName as $className) {
            $this->tryAddCustomPostType($className);
        }

        return $this;
    }

    /**
     * Tries to add the specified custom post-type to the registry
     * 
     * Unlike addCustomPostType(), this method will not throw an exception if there's a problem with a class: instead,
     * it'll return TRUE on success, or FALSE on failure
     * 
     * @param string $p_className
     * @return bool
     */
    private function tryAddCustomPostType($p_className)
    {
        try {
            $this->addCustomPostType($p_className);
        }
        catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns an array containing the names of custom post-types that have been added to the registry
     * 
     * @return array
     */
    public function getCustomPostTypeClassNames()
    {
        return $this->aCustomPostTypeClassName;
    }

    /**
     * Returns TRUE if the specified custom post type was added, or FALSE otherwise
     * 
     * @param string $p_name
     * @return bool
     */
    public function customPostTypeWasAdded($p_name)
    {
        return isset($this->aCustomPostTypeClassName[$p_name]);
    }

    /**
     * Returns the class-name of the specified custom post type
     * 
     * @param string $p_name
     * @return string
     * @throws OutOfBoundsException If the specified custom post-type has not been added
     */
    public function getCustomPostTypeClassName($p_name)
    {
        if (! $this->customPostTypeWasAdded($p_name)) {
            throw new \OutOfBoundsException("The custom post-type \"{$p_name}\" has not been added");
        }

        return $this->aCustomPostTypeClassName[$p_name];
    }

    /**
     * Returns a new instance of the custom post-type with the specified class name
     * 
     * By default, a blank instance, with an empty post and custom field values, is created
     * 
     * @param string $p_className
     * @param array [$p_aArgument = array()]
     * @return portico\CustomPostType
     */
    private function createCustomPostType($p_className, array $p_aArgument = array())
    {
        $aArgument = empty($p_aArgument) ? array(new \stdClass(), array()) : $p_aArgument;
        $oReflectionClass = new \ReflectionClass($p_className);
        return $oReflectionClass->newInstanceArgs($aArgument);
    }

    /**
     * Returns a new instance of the custom post-type with the specified name
     * 
     * By default, a blank instance, with an empty post and custom field values, is created
     * 
     * @param string $p_name
     * @param array [$p_aArgument = array()]
     * @return portico\CustomPostType
     */
    private function createCustomPostTypeByName($p_name, array $p_aArgument = array())
    {
        return $this->createCustomPostType($this->getCustomPostTypeClassName($p_name), $p_aArgument);
    }

    /**
     * Registers all include-d Portico CustomPostTypes with WordPress
     * 
     * @todo Test this
     */
    public function onInit()
    {
        $this
            ->tryAddCustomPostTypes(get_declared_classes())
            ->registerCustomPostTypes();
    }

    /**
     * Registers the custom post-types with WordPress
     * 
     * This is the main entry point into the system and must be called in init
     * 
     * @return portico\Registry $this
     */
    public function registerCustomPostTypes()
    {
        foreach ($this->getCustomPostTypeClassNames() as $customPostTypeClassName) {
            $this->registerCustomPostType($customPostTypeClassName);
        }

        return $this;
    }

    /**
     * Registers the specified custom post-type with WordPress
     * 
     * @param string $p_customPostTypeClassName
     */
    private function registerCustomPostType($p_customPostTypeClassName)
    {
        $oCustomPostType = $this->createCustomPostType($p_customPostTypeClassName);

        $aRegistrationParameter = array_merge($oCustomPostType->getRegistrationParameters(true), array(
            'register_meta_box_cb' => array($this, 'afterAdminMenuRendered'),
        ));

        register_post_type($oCustomPostType->getName(), $aRegistrationParameter);
        add_action('save_post', array($this, 'afterPostSaved'));
    }

    /**
     * Registers the meta-box for the custom fields in the specified custom post type
     * 
     * This method is called by WordPress after the admin menu has been rendered
     * 
     * @param stdClass|WP_Post $p_oPost
     * @todo Test this
     */
    public function afterAdminMenuRendered($p_oPost)
    {
        $oCustomPostType = $this->createCustomPostTypeByName($p_oPost->post_type);

        if (! $oCustomPostType->hasCustomFields()) {
            return;
        }

        add_meta_box(
            $oCustomPostType->getName(),
            $oCustomPostType->getTitle(),
            array($this, 'echoMetaBoxHtml'),
            $oCustomPostType->getName(),
            'normal',
            'high'
        );
    }

    /**
     * Called by WordPress to output the HTML for the custom meta box for the specified custom post type
     * 
     * @param stdClass|WP_Post $p_oPost
     * @todo Test this
     */
    public function echoMetaBoxHtml($p_oPost)
    {
        $oCustomPostType = $this->createCustomPostTypeByName(
            $p_oPost->post_type,
            array($p_oPost, get_post_custom($p_oPost->ID))
        );

        echo $oCustomPostType->getMetaBoxHtml();
    }

    /**
     * Called by WordPress after a custom post type is saved
     * 
     * This is where we save the custom field values associated with the custom post type
     * 
     * @param int $p_id
     * @todo Test this
     */
    public function afterPostSaved($p_id)
    {
        //get_post() accepts a reference so we're forced to assign the ID to a variable
        $id = wp_is_post_revision($p_id) ?: $p_id;
        $oPost = get_post($id);

        //Do nothing more if we don't know anything about the post
        if (! $this->customPostTypeWasAdded($oPost->post_type)) {
            return;
        }

        $oCustomPostType = $this->createCustomPostTypeByName($oPost->post_type, array(
            $oPost,
            $this->getRequestCustomFieldValues($oPost->post_type, array())
        ));

        $oCustomPostType->saveCustomFieldValues();
    }

    /**
     * Adds the Portico admin CSS to the HEAD element in the backend
     * 
     * @todo Test this
     */
    public function onRenderingAdminHead()
    {
        $stylesheetUrl = plugins_url('public/admin.css', dirname(__FILE__));
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$stylesheetUrl}\" />";
    }
}