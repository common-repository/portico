<?php
/*
Plugin Name: Portico
Plugin URI: http://danbettles.net/software/portico/
Description: Makes defining and administering custom post-types even easier
Version: 1.0.2
License: Simplified BSD License
Author: Dan Bettles <dan@danbettles.net>
Author URI: http://danbettles.net/
*/
/**
 * @package Portico
 * @author Dan Bettles <dan@danbettles.net>
 * @copyright Copyright (c) 2010, Dan Bettles
 * @license http://creativecommons.org/licenses/BSD/ Simplified BSD License
 * @todo Use nonces?
 */

/**
 * Loads the Portico library
 */
require_once __DIR__ . '/include/boot.php';

/**
 * Initialises the Portico registry
 */
portico\Registry::setInstance(new portico\Registry($_POST));

/**
 * Registers all include-d Portico CustomPostTypes with WordPress at init
 */
add_action('init', array(portico\Registry::getInstance(), 'onInit'));

/**
 * Adds the Portico admin CSS to the HEAD element in the backend
 */
add_action('admin_head', array(portico\Registry::getInstance(), 'onRenderingAdminHead'));