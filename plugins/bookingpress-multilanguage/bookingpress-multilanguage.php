<?php
/*
Plugin Name: BookingPress - Multi-Language Addon
Description: Extension for BookingPress plugin to translate it in multiple languages.
Version: 1.4
Requires at least: 5.0
Requires PHP:      5.6
Plugin URI: https://www.bookingpressplugin.com/
Author: Repute InfoSystems
Author URI: https://www.bookingpressplugin.com/
Text Domain: bookingpress-multilanguage
Domain Path: /languages
*/

define('BOOKINGPRESS_MULTILANGUAGE_DIR_NAME', 'bookingpress-multilanguage');
define('BOOKINGPRESS_MULTILANGUAGE_DIR', WP_PLUGIN_DIR . '/' . BOOKINGPRESS_MULTILANGUAGE_DIR_NAME);
define('BOOKINGPRESS_MULTILANGUAGE_VIEW_DIR', WP_PLUGIN_DIR . '/' . BOOKINGPRESS_MULTILANGUAGE_DIR_NAME.'/core/views/');

define('BOOKINGPRESS_MULTILANGUAGE_IMAGES_DIR', WP_PLUGIN_DIR . '/' . BOOKINGPRESS_MULTILANGUAGE_DIR_NAME.'/images/');

if (file_exists( BOOKINGPRESS_MULTILANGUAGE_DIR . '/autoload.php')) {
    require_once BOOKINGPRESS_MULTILANGUAGE_DIR . '/autoload.php';
}