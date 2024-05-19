<?php

global $BookingPress, $bookingpress_multilanguage_version, $wpdb;
$bookingpress_db_multilanguage_version = get_option('bookingpress_multilanguage_version', true);


$bookingpress_multilanguage_new_version = '1.4';
update_option('bookingpress_multilanguage_version', $bookingpress_multilanguage_new_version);
update_option('bookingpress_multilanguage_version_updated_date_' . $bookingpress_multilanguage_new_version, current_time('mysql'));