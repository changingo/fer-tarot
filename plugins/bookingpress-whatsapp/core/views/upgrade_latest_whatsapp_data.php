<?php

global $BookingPress, $wpdb, $bookingpress_whatsapp_version;
$bookingpress_whatsapp_old_version = get_option( 'bookingpress_whatsapp_gateway' );

if (version_compare($bookingpress_whatsapp_old_version, '1.3', '<') ) {
    $tbl_bookingpress_notifications = $wpdb->prefix . 'bookingpress_notifications';
    
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_notifications} ADD bookingpress_whatsapp_admin_number VARCHAR(60) NULL DEFAULT NULL AFTER bookingpress_send_whatsapp_notification");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
}

if( version_compare($bookingpress_whatsapp_old_version, '2.1', '<') ){

    $tbl_bookingpress_notifications = $wpdb->prefix . 'bookingpress_notifications';
    
    $wpdb->query("ALTER TABLE {$tbl_bookingpress_notifications} ADD bookingpress_wp_template_placeholder text DEFAULT NULL AFTER bookingpress_send_whatsapp_notification");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_notifications} ADD bookingpress_wp_selected_template VARCHAR(255) DEFAULT NULL AFTER bookingpress_wp_template_placeholder");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

    $wpdb->query("ALTER TABLE {$tbl_bookingpress_notifications} ADD bookingpress_whatsapp_dynamic_data INT DEFAULT NULL AFTER bookingpress_wp_selected_template");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

    $selected_whatsapp_gateway = $BookingPress->bookingpress_get_settings( 'bookingpress_selected_whatsapp_gateway', 'notification_setting');

    if( 'Twilio' == $selected_whatsapp_gateway ){   
        $BookingPress->bookingpress_update_settings( 'bookingpress_whatsapp_show_freeform_msg', 'notification_setting', true );
        $BookingPress->bookingpress_update_settings( 'bookingpress_whatsapp_twilio_msg_type', 'notification_setting', 'freeform' );
    } else {
        $BookingPress->bookingpress_update_settings( 'bookingpress_whatsapp_twilio_msg_type', 'notification_setting', 'template' );
    }
}

$bookingpress_whatsapp_new_version = '2.2';
update_option('bookingpress_whatsapp_gateway', $bookingpress_whatsapp_new_version);
update_option('bookingpress_whatsapp_gateway_updated_date_' . $bookingpress_whatsapp_new_version, current_time('mysql'));