<?php

if (is_ssl()) {
    define('BOOKINGPRESS_MULTILANGUAGE_URL', str_replace('http://', 'https://', WP_PLUGIN_URL . '/' . BOOKINGPRESS_MULTILANGUAGE_DIR_NAME));
} else {
    define('BOOKINGPRESS_MULTILANGUAGE_URL', WP_PLUGIN_URL . '/' . BOOKINGPRESS_MULTILANGUAGE_DIR_NAME);
}

if(file_exists(BOOKINGPRESS_MULTILANGUAGE_DIR . "/core/classes/class.bookingpress-multilanguage.php") ){
	require_once BOOKINGPRESS_MULTILANGUAGE_DIR . "/core/classes/class.bookingpress-multilanguage.php";
}

global $bookingpress_multilanguage_version;
$bookingpress_multilanguage_version = '1.4';
define('BOOKINGPRESS_MULTILANGUAGE_VERSION', $bookingpress_multilanguage_version);

load_plugin_textdomain( 'bookingpress-multilanguage', false, 'bookingpress-multilanguage/languages/' );

define( 'BOOKINGPRESS_MULTILANGUAGE_STORE_URL', 'https://www.bookingpressplugin.com/' );

if ( ! class_exists( 'bookingpress_pro_updater' ) ) {
	require_once BOOKINGPRESS_MULTILANGUAGE_DIR . '/core/classes/class.bookingpress_pro_plugin_updater.php';
}

function bookingpress_multilanguage_plugin_updater() {

	$plugin_slug_for_update = 'bookingpress-multilanguage/bookingpress-multilanguage.php';

	// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
	$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;
	if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
		return;
	}

	// retrieve our license key from the DB
	$license_key = trim( get_option( 'bkp_multilanguage_license_key' ) );
	$package = trim( get_option( 'bkp_multilanguage_license_package' ) );

	// setup the updater
	$edd_updater = new bookingpress_pro_updater(
		BOOKINGPRESS_MULTILANGUAGE_STORE_URL,
		$plugin_slug_for_update,
		array(
			'version' => BOOKINGPRESS_MULTILANGUAGE_VERSION,  // current version number
			'license' => $license_key,             // license key (used get_option above to retrieve from DB)
			'item_id' => $package,       // ID of the product
			'author'  => 'Repute Infosystems', // author of this plugin
			'beta'    => false,
		)
	);

}
add_action( 'init', 'bookingpress_multilanguage_plugin_updater' );

?>