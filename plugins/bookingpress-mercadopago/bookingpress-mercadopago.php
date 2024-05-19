<?php
/*
Plugin Name: BookingPress - Mercado Pago Payment Gateway Addon
Description: Extension for BookingPress plugin to accept payments using Mercado pago Payment Gateway.
Version: 1.0
Requires at least: 5.0
Requires PHP:      5.6
Plugin URI: https://www.bookingpressplugin.com/
Author: Repute InfoSystems
Author URI: https://www.bookingpressplugin.com/
Text Domain: bookingpress-mercadopago
Domain Path: /languages
*/

define('BOOKINGPRESS_MERCADOPAGO_DIR_NAME', 'bookingpress-mercadopago');
define('BOOKINGPRESS_MERCADOPAGO_DIR', WP_PLUGIN_DIR . '/' . BOOKINGPRESS_MERCADOPAGO_DIR_NAME);
define('BOOKINGPRESS_MERCADOPAGO_VIEW_DIR', WP_PLUGIN_DIR . '/' . BOOKINGPRESS_MERCADOPAGO_DIR_NAME.'/core/views/');

if (file_exists( BOOKINGPRESS_MERCADOPAGO_DIR . '/autoload.php')) {
    require_once BOOKINGPRESS_MERCADOPAGO_DIR . '/autoload.php';
}