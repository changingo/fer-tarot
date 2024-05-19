<?php
/*
Plugin Name: BookingPress - WhatsApp Notification Addon
Description: Extension for BookingPress plugin to send WhatsApp notification upon appointment booking, appointment cancel, appointment reschedule etc.
Version: 2.2
Requires at least: 5.0
Requires PHP:      5.6
Plugin URI: https://www.bookingpressplugin.com/
Author: Repute InfoSystems
Author URI: https://www.bookingpressplugin.com/
Text Domain: bookingpress-whatsapp
Domain Path: /languages
*/

define('BOOKINGPRESS_WHATSAPP_DIR_NAME', 'bookingpress-whatsapp');
define('BOOKINGPRESS_WHATSAPP_DIR', WP_PLUGIN_DIR . '/' . BOOKINGPRESS_WHATSAPP_DIR_NAME);

if (file_exists( BOOKINGPRESS_WHATSAPP_DIR . '/autoload.php')) {
    require_once BOOKINGPRESS_WHATSAPP_DIR . '/autoload.php';
}