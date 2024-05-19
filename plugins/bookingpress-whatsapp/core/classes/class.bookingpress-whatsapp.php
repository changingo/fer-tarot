<?php 
if (!class_exists('bookingpress_whatsapp') && class_exists( 'BookingPress_Core')) {
    class bookingpress_whatsapp Extends BookingPress_Core {
        function __construct(){
            register_activation_hook(BOOKINGPRESS_WHATSAPP_DIR.'/bookingpress-whatsapp.php', array('bookingpress_whatsapp', 'install'));
            register_uninstall_hook(BOOKINGPRESS_WHATSAPP_DIR.'/bookingpress-whatsapp.php', array('bookingpress_whatsapp', 'uninstall'));

            //Admiin notices
            add_action('admin_notices', array($this, 'bookingpress_admin_notices'));
            if( !function_exists('is_plugin_active') ){
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            if(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')) {                
                add_action('admin_enqueue_scripts',array($this,'set_whatsapp_css'),11);
                add_action('bookingpress_add_notification_settings_section', array($this, 'bookingpress_add_notification_settings_section_func'));
                add_action('bookingpress_add_setting_dynamic_vue_methods', array($this, 'bookingpress_add_setting_dynamic_vue_methods_func'));                

                add_filter('bookingpress_addon_list_data_filter',array($this,'bookingpress_addon_list_data_filter_func'));

                //Hook for add view for message of whatsapp notification
                add_action('bookingpress_add_email_notification_section', array($this, 'bookingpress_add_email_notification_section_func'),12);
                add_filter('bookingpress_add_dynamic_notification_data_fields', array($this, 'bookingpress_add_dynamic_notification_data_fields_func'));

                //Hook for pass whatsapp notification data when click on save button
                add_action('bookingpress_add_email_notification_data', array($this, 'bookingpress_add_email_notification_data_func'));
                add_action('bookingpress_add_dynamic_notifications_vue_methods', array( $this, 'bookingpress_notification_dynamic_vue_methods_func' ), 10);

                //Modify database save values
                add_filter('bookingpress_save_email_notification_data_filter', array($this, 'bookingpress_save_email_notification_data_filter_func'), 10, 2);

                //Set value of whatsapp notification message when email notification data retrieved
                add_action('bookingpress_email_notification_get_data', array($this, 'bookingpress_email_notification_get_data_func'));
                add_action('wp_ajax_bookingpress_send_test_whatsapp', array($this, 'bookingpress_send_test_whatsapp_func'));

                //add value to debug log array
                add_filter('bookingpress_add_integration_debug_logs', array($this, 'bookingpress_add_integration_debug_logs_func'), 10, 1);

                //Filter for add data variables for debug logs
                add_filter('bookingpress_add_setting_dynamic_data_fields', array( $this, 'bookingpress_add_setting_dynamic_data_fields_func' ), 10 );

                //add_action('send_whatsapp_notification', array( $this, 'bookingpress_front_side_send_notification'),10,5);

                add_filter('bookingpress_get_notifiacation_data_filter',array($this,'bookingpress_get_notifiacation_data_filter_func'));

                // bookingpress_after_book_appointment                
                add_action('bookingpress_after_book_appointment', array($this, 'bookingpress_send_whatsapp_notification_after_appointment_booked'), 11, 3);

                //After reschedule appointment
                add_action('bookingpress_after_rescheduled_appointment', array($this, 'bookingpress_after_reschedule_appointment_func'),11);
                add_action('bookingpress_after_update_appointment', array($this,'bookingpress_after_update_appointment_func'));

                //After cancel appointment
                add_action('bookingpress_after_cancel_appointment', array($this, 'bookingpress_after_cancel_appointment_func'),11);

                //-- Add New Function For Waiting List TO Send Custom SMS Notification For Waiting List
                add_action('bookingpress_send_custom_status_whatsapp_notification', array($this, 'bookingpress_send_custom_status_whatsapp_notification_func'),11,2);

                //After refund appointment
                add_action('bookingpress_after_refund_appointment', array($this, 'bookingpress_after_refund_appointment_func'),11);

                //After change status from backend
                add_action('bookingpress_after_change_appointment_status', array($this, 'bookingpress_after_change_appointment_status_func'), 11, 2);

                //Cron Whatsapp notification                
               add_action('bookingpress_cron_external_notification', array($this, 'bookingpress_cron_external_notification_func'),11,4);

               //Staff Cron Whatsapp notification
               add_action('bookingpress_staff_cron_external_notification', array($this, 'bookingpress_staff_cron_external_notification_func'), 11, 4);

               //After add appointment backend
               add_action('bookingpress_after_add_appointment_from_backend',array($this,'bookingpress_after_add_appointment_from_backend_func'),11,3);

               add_filter('bookingpress_frontend_apointment_form_add_dynamic_data',array($this,'bookingpress_frontend_apointment_form_add_dynamic_data_func'));

               add_filter('bookingpress_customize_add_dynamic_data_fields',array($this,'bookingpress_customize_add_dynamic_data_fields_func'));

               add_filter('bookingpress_get_booking_form_customize_data_filter',array($this,'bookingpress_get_booking_form_customize_data_filter_func'));

               add_filter('bookingpress_before_save_customize_booking_form',array($this,'bookingpress_before_save_customize_booking_form_func'),11);

               add_filter( 'bookingpress_modify_appointment_data_fields', array( $this, 'bookingpress_modify_appointment_data_fields_func' ), 10 );

               add_action('bookingpress_add_appointment_field_section',array($this,'bookingpress_add_appointment_field_section_func'),10);

               add_action('bookingpress_get_appointment_meta_value_filter',array($this,'bookingpress_get_appointment_meta_value_filter_func'));

               add_filter('bookingpress_front_modify_cart_data_filter',array($this,'bookingpress_front_modify_cart_data_filter_func'));

               add_action('bookingpress_add_booking_form_basic_details_data',array($this,'bookingpress_add_booking_form_basic_details_data_func'));

               add_filter('bookingpress_modify_capability_data', array($this, 'bookingpress_modify_capability_data_func'), 11, 1);

                //Share URLs Hooks
                add_action('bookingpress_add_more_sharing_url_content_for_appointment', array($this, 'bookingpress_add_more_sharing_url_content_for_appointment_func'), 11);
                add_action('bookingpress_add_more_sharing_url_options_for_appointment', array($this, 'bookingpress_add_more_sharing_url_options_for_appointment_func'), 10);
                add_action('bpa_externally_share_appointment_url', array($this, 'bpa_externally_share_appointment_url_func'));

                //Complete Payment Hooks
                add_action('bookingpress_add_more_complete_payment_link_option', array($this, 'bookingpress_add_more_complete_payment_link_option_func'));
                add_action('bookingpress_send_complete_payment_link_externally', array($this, 'bookingpress_send_complete_payment_link_externally_func'), 10, 2);
                
                add_filter('bookingpress_modify_email_notification_data_for_extrnal_notification',array($this,'bookingpress_modify_email_notification_data_for_extrnal_notification_func'),10,4);

                add_filter( 'bookingpress_modify_save_setting_data', array( $this, 'bookingpress_save_wp_settings'), 10, 2 );

                if(is_plugin_active('bookingpress-multilanguage/bookingpress-multilanguage.php')) {
					
                    add_filter('bookingpress_modified_language_translate_fields',array($this,'bookingpress_modified_language_translate_fields_func'),10);
                	add_filter('bookingpress_modified_customize_form_language_translate_fields',array($this,'bookingpress_modified_language_translate_fields_func'),10);
                    
                    add_filter('bookingpress_modified_notification_manage_language_translate_fields',array($this,'bookingpress_modified_notification_manage_language_translate_fields_func'),10,1);

				}
                /* Package Order Fields Added Start  */
                add_filter('bookingpress_frontend_package_order_form_add_dynamic_data', array($this, 'bookingpress_frontend_whatsapp_form_add_dynamic_data_func'), 10);
                add_action('bookingpress_after_add_package_order', array( $this,'bookingpress_after_add_package_order'),10,2);

                add_action( 'bookingpress_insert_whatsapp_placeholder_outside', array( $this, 'bookingpress_prevent_inserting_whatsapp_placeholder') );

                add_action( 'bookingpress_page_admin_notices', array( $this, 'bookingpress_page_admin_notices_for_whatsapp'));
                add_action( 'bookingpress_admin_panel_vue_methods', array( $this, 'bookingpress_admin_common_vue_methods'));
                add_action( 'bookingpress_admin_vue_data_variables_script', array( $this, 'bookingpress_admin_whatsapp_notice_data') );
                add_action( 'bookingpress_settings_response', array( $this, 'bookingpress_manage_whatsapp_twilio_notice') );

                add_action( 'wp_ajax_bookingpress_refresh_whatsapp_template_list', array( $this, 'bookingpress_refresh_whatsapp_template_list_callback') );
            }
	    add_action( 'admin_init', array( $this, 'bookingpress_update_whatsapp_data') );

            add_action('activated_plugin',array($this,'bookingpress_is_whatsapp_addon_activated'),11,2);
        }

        function bookingpress_manage_whatsapp_twilio_notice(){
            ?>
            if( "undefined" != typeof setting_type && "notification_setting" == setting_type && response.data.variant == 'success' ){
                let selected_gateway = vm.notification_setting_form.bookingpress_selected_whatsapp_gateway;
                if( 'Twilio' == selected_gateway ){
                    let show_free_form_msg =  vm.notification_setting_form.bookingpress_whatsapp_show_freeform_msg;
                    let twilio_msg_type = vm.notification_setting_form.bookingpress_whatsapp_twilio_msg_type;
                    if( 1 == show_free_form_msg && 'freeform' == twilio_msg_type ) {
                        vm.bpa_show_twilio_whatsapp_notice = true;
                    } else {
                        vm.bpa_show_twilio_whatsapp_notice = false;
                    }
                } else {
                    vm.bpa_show_twilio_whatsapp_notice = false;
                }
            }
            <?php
        }

        function bookingpress_admin_common_vue_methods(){
            ?>
            bookingpress_close_whatsapp_notice(){
                const vm = this;
                vm.bpa_show_twilio_whatsapp_notice = false;
            },
            <?php
        }

        function bookingpress_admin_whatsapp_notice_data(){
            global $BookingPress;
            $is_show_freeform = $BookingPress->bookingpress_get_settings( 'bookingpress_whatsapp_show_freeform_msg', 'notification_setting' );
            $twilio_msg_type = $BookingPress->bookingpress_get_settings( 'bookingpress_whatsapp_twilio_msg_type', 'notification_setting' );
            $selected_wp_gateway = $BookingPress->bookingpress_get_settings('bookingpress_selected_whatsapp_gateway', 'notification_setting');

            $show_twilio_wpa_notice = ( 'Twilio' == $selected_wp_gateway && 1 == $is_show_freeform && 'freeform' == $twilio_msg_type );

            ?>
            bookingpress_return_data['bpa_show_twilio_whatsapp_notice'] = '<?php echo $show_twilio_wpa_notice; ?>';
            <?php
        }

        function bookingpress_page_admin_notices_for_whatsapp(){
            
            global $BookingPress, $bookingpress_pro_settings;
            
            if( !current_user_can( 'administrator' ) ){
				return;
			}

            if( ( isset($_REQUEST['page']) && ($_REQUEST['page'] == 'bookingpress_lite_wizard' || $_REQUEST['page'] == 'bookingpress_wizard' || $_REQUEST['page'] == 'bookingpress_growth_tools') )){
				return;
			}

            ?>
                <div class="bpa-pg-warning-belt-box" v-if="bpa_show_twilio_whatsapp_notice == true">
                    <p class="bpa-wbb__desc">
                        <span class="material-icons-round bpa-wbb__desc-icon">warning</span>
                        <span class="bpa-wbb__desc-content"><?php esc_html_e("It seems that you're using Free Form message option. It's highly recommended to use the WhatsApp template method for structured messaging and to ensure compliance with WhatsApp's guidelines.", 'bookingpress-whatsapp') ?></span>
                    </p>
                    <span class="bpa-uwb-close-icon material-icons-round" @click="bookingpress_close_whatsapp_notice">close</span>
                </div>
            <?php

        }

        function bookingpress_prevent_inserting_whatsapp_placeholder(){
            ?>
            return false;
            <?php
        }

        function bookingpress_save_wp_settings( $bookingpress_save_settings_data, $posted_data  ) {
            
            if( !empty($bookingpress_save_settings_data['bookingpress_selected_whatsapp_gateway']) && 'Whatsapp Business' == $bookingpress_save_settings_data['bookingpress_selected_whatsapp_gateway'] ) {

                global $BookingPress;
                $bpa_wp_account_id = $bookingpress_save_settings_data['whatsapp_business_account_id'];
                $bpa_wp_access_token = $bookingpress_save_settings_data['whatsapp_business_access_token'];
                $whatsapp_url = 'https://graph.facebook.com/v19.0/'. $bpa_wp_account_id.'/message_templates';
                $args = array(
                    'timeout' => 45,
                    'method' => 'GET',
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $bpa_wp_access_token
                    ),
                    'body' =>array(
                        'fields' => 'name,id,status,components,language',
                        'limit' => '100',
                    ),
                );

                $whatsapp_response = wp_remote_get(
                    $whatsapp_url,
                    $args
                );

                if( is_wp_error( $whatsapp_response ) ){
                    $bookingpress_return_res['return_type'] = 'error';
                    $bookingpress_return_res['return_res'] = $whatsapp_response->get_error_message();
                } else {  
                    if( $whatsapp_response['response']['code'] == 200 ){
                        
                        $whatsapp_response = wp_remote_retrieve_body( $whatsapp_response );
                        $whatsapp_response_arr = json_decode( $whatsapp_response, true );


                        $bpa_wp_template_data = array();
                        foreach( $whatsapp_response_arr['data'] as $key=>$val ){
                            $bpa_components = $val['components'];
                            foreach( $bpa_components as $bpa_components_key=>$bpa_components_val ){
                                
                                if( $bpa_components_val['type'] == 'BODY' ){
                                    if( $val['status'] == 'APPROVED'){
                                        $bpa_wp_template_data[] = array(
                                            'template_id' => $val['id'],
                                            'template_name' => $val['name'],
                                            'template_label' => $val['name'] .' ('.$val['language'].')',
                                            'template_body' => $bpa_components_val['text'],
                                            'language' => $val['language'],
                                        );
                                    }
                                }
                            }
                        }
                        $bookingpress_save_settings_data['bpa_wp_template_data'] = json_encode( $bpa_wp_template_data );
                    }
                }

            } else if( !empty($bookingpress_save_settings_data['bookingpress_selected_whatsapp_gateway']) && ( 'Twilio' == $bookingpress_save_settings_data['bookingpress_selected_whatsapp_gateway'] ) ){
                global $BookingPress;
                $show_free_form_msg = $BookingPress->bookingpress_get_settings( 'bookingpress_whatsapp_show_freeform_msg', 'notification_setting' );
                $twilio_msg_type = $BookingPress->bookingpress_get_settings('bookingpress_whatsapp_twilio_msg_type', 'notification_setting');

                if( empty( $show_free_form_msg ) || 'template' == $twilio_msg_type ) {

                    $whatsapp_twilio_account_sid = $bookingpress_save_settings_data['whatsapp_twilio_account_sid'];
                    $whatsapp_twilio_auth_token = $bookingpress_save_settings_data['whatsapp_twilio_auth_token'];
                    $whatsapp_twilio_from_number = $bookingpress_save_settings_data['whatsapp_twilio_from_number'];

                    $twilio_url = 'https://content.twilio.com/v1/ContentAndApprovals?PageSize=100';
                    $args = array(
                        'timetout' => 4500,
                        'headers' => array(
                            'Content-Type' => 'application/x-www-form-urlencoded',
                            'Authorization' =>  'Basic '. base64_encode( $whatsapp_twilio_account_sid.':'.$whatsapp_twilio_auth_token )
                        )
                    );

                    $twilio_get_contents = wp_remote_get( $twilio_url, $args );

                    if( is_wp_error( $twilio_get_contents ) ){
                        /** handle wp error here */
                    } else {
                        $twilio_content_data = wp_remote_retrieve_body( $twilio_get_contents );

                        $content_data = json_decode( $twilio_content_data, true );
                        
                        if( empty( $content_data['contents'] ) ){
                            /** handle error message */
                        } else {
                            $contents = $content_data['contents'];

                            $bpa_wp_template_data = array();
                            foreach( $contents as $twilio_content_details ){
                                $status = $twilio_content_details['approval_requests']['status'];

                                if( 'approved' == $status ){
                                    $template_id = $twilio_content_details['sid'];
                                    $template_name = $twilio_content_details['approval_requests']['name'];
                                    $template_body = !empty( $twilio_content_details['types']['twilio/text']['body'] ) ? $twilio_content_details['types']['twilio/text']['body'] : '';
                                    $template_language = $twilio_content_details['language'];

                                    if( !empty( $template_body ) ){
                                        $bpa_wp_template_data[] = array(
                                            'template_id' => $template_id,
                                            'template_name' => $template_name,
                                            'template_label' => $template_name .' ('.$template_language.')',
                                            'template_body' => $template_body,
                                            'language' => $template_language
                                        );
                                    }
                                }
                            }

                            $bookingpress_save_settings_data['bpa_wp_twilio_template_data'] = json_encode( $bpa_wp_template_data );

                        }

                    }
                }

            }

            return $bookingpress_save_settings_data;
            /* die; */
        }
        /* package addon related notification send */
        function bookingpress_after_add_package_order( $entry_id,$inserted_booking_id ){

            global $wpdb, $BookingPress, $tbl_bookingpress_package_bookings, $tbl_bookingpress_package_bookings_meta;

            $bookingpress_package_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_package_bookings} WHERE bookingpress_package_booking_id = %d", $inserted_booking_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_package_bookings is a table name. false alarm

            $bookingpress_configured_options = array();

            $bookingpress_package_appointment_meta_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_package_meta_value,bookingpress_package_meta_key FROM {$tbl_bookingpress_package_bookings_meta} WHERE bookingpress_package_booking_id = %d AND bookingpress_package_meta_key = %s", $inserted_booking_id,'package_form_fields_data' ), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_package_bookings_meta is a table name. false alarm.

            $bookingpress_package_appointment_meta_data = !empty($bookingpress_package_appointment_meta_data['bookingpress_package_meta_value']) ? json_decode($bookingpress_package_appointment_meta_data['bookingpress_package_meta_value'],true) : array();
                              
            $bookingpress_package_appointment_form_fields = !empty($bookingpress_package_appointment_meta_data['form_fields']) ? $bookingpress_package_appointment_meta_data['form_fields'] : array();

            $is_send_whatsapp_notification = !empty($bookingpress_package_appointment_form_fields['send_whatsapp_notification']) ? $bookingpress_package_appointment_form_fields['send_whatsapp_notification'] : '';
            
            if(!empty($is_send_whatsapp_notification) && $is_send_whatsapp_notification == 'true') {     
                $bookingpress_phone_no = $this->bookingpress_get_mobile_number_field($bookingpress_package_appointment_data);
                $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_phone_no, '', 0, $inserted_booking_id, $bookingpress_configured_options, 'Package Order' , 'customer');
            }

            $bookingpress_whatsapp_admin_number = '';
            $bookingpress_send_res = $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_whatsapp_admin_number, '', 0, $inserted_booking_id, $bookingpress_configured_options, 'Package Order', 'employee');

        }

        /*Multi language addon filter */
        function bookingpress_modified_language_translate_fields_func($bookingpress_all_language_translation_fields){
            global $BookingPress;
            $show_free_form_msg = $BookingPress->bookingpress_get_settings('bookingpress_whatsapp_show_freeform_msg', 'notification_setting');
            $twilio_msg_type = $BookingPress->bookingpress_get_settings('bookingpress_whatsapp_twilio_msg_type', 'notification_setting');

            if( 'template' == $twilio_msg_type ){
                return $bookingpress_all_language_translation_fields;
            }
            $bookingpress_whatsapp_language_translation_fields = array(                
				'send_whatsapp_notification_label' => array('field_type'=>'text','field_label'=>__('Send whatsapp notification', 'bookingpress-whatsapp'),'save_field_type'=>'booking_form'),                 
			);  
			$bookingpress_all_language_translation_fields['customized_form_basic_details_step_labels'] = array_merge($bookingpress_all_language_translation_fields['customized_form_basic_details_step_labels'], $bookingpress_whatsapp_language_translation_fields);           
            return $bookingpress_all_language_translation_fields;
		}

        function bookingpress_modified_notification_manage_language_translate_fields_func($bookingpress_all_language_translation_fields){
            
            global $BookingPress;
            $show_free_form_msg = $BookingPress->bookingpress_get_settings('bookingpress_whatsapp_show_freeform_msg', 'notification_setting');
            $twilio_msg_type = $BookingPress->bookingpress_get_settings('bookingpress_whatsapp_twilio_msg_type', 'notification_setting');

            if( 'template' == $twilio_msg_type ){
                return $bookingpress_all_language_translation_fields;
            }

            if(isset($bookingpress_all_language_translation_fields['manage_notification_customer'])){
                $bookingpress_all_language_translation_fields['manage_notification_customer']['bookingpress_whatsapp_notification_message'] = array('field_type'=>'textarea','field_label'=>__('WhatsApp Notification Message', 'bookingpress-whatsapp'),'save_field_type'=>'manage_notification_customer'); 
            }
			if(isset($bookingpress_all_language_translation_fields['manage_notification_employee'])){
                $bookingpress_all_language_translation_fields['manage_notification_employee']['bookingpress_whatsapp_notification_message'] = array('field_type'=>'textarea','field_label'=>__('WhatsApp Notification Message', 'bookingpress-whatsapp'),'save_field_type'=>'manage_notification_customer'); 
            } 
            return $bookingpress_all_language_translation_fields;
        }

        function bookingpress_update_whatsapp_data() {
            global $BookingPress,$bookingpress_whatsapp_version;
            $bookingpress_whatsapp_db_version = get_option( 'bookingpress_whatsapp_gateway');

            if( version_compare( $bookingpress_whatsapp_db_version, '2.2', '<' ) ){
                $bookingpress_load_whatsapp_update_file = BOOKINGPRESS_WHATSAPP_DIR . '/core/views/upgrade_latest_whatsapp_data.php';
                include $bookingpress_load_whatsapp_update_file;
                $BookingPress->bookingpress_send_anonymous_data_cron();
            }
        }
        function bookingpress_modify_email_notification_data_for_extrnal_notification_func($bookingpress_email_data,$notification_from,$template_type,$notification_event_action) {
            global $tbl_bookingpress_notifications,$wpdb;
            if(!empty($notification_from) && $notification_from == 'whatsapp') {                
                $bookingpress_email_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_notification_name,bookingpress_notification_service FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_receiver_type = %s AND bookingpress_notification_type = %s AND bookingpress_notification_event_action = %s AND bookingpress_send_whatsapp_notification = %d AND bookingpress_custom_notification_type = %s ORDER BY bookingpress_notification_id DESC", $template_type,'custom', $notification_event_action,1,'action-trigger' ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm                                
            }
            return $bookingpress_email_data;
        }

        function bookingpress_send_complete_payment_link_externally_func($bookingpress_appointment_data, $bookingpress_selected_options){
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings;

            if( !empty($bookingpress_appointment_data) && in_array("whatsapp", $bookingpress_selected_options) ){
                $bookingpress_configured_options = array(
                    'bookingpress_selected_whatsapp_gateway' => $BookingPress->bookingpress_get_settings('bookingpress_selected_whatsapp_gateway', 'notification_setting')
                );
                
                $bookingpress_phone_no = $this->bookingpress_get_mobile_number_field($bookingpress_appointment_data);                
                $bookingpress_appointment_id = !empty($bookingpress_appointment_data['bookingpress_appointment_booking_id']) ? intval($bookingpress_appointment_data['bookingpress_appointment_booking_id']) : '';

                $bookingpress_send_res = $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_phone_no, '', 0, $bookingpress_appointment_id, $bookingpress_configured_options, 'Complete Payment URL', 'customer');
                
                $bookingpress_whatsapp_admin_number = '';
                $bookingpress_send_res = $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_whatsapp_admin_number, '', 0, $bookingpress_appointment_id, $bookingpress_configured_options, 'Complete Payment URL', 'employee');

                //Send staff email notification
                $bookingpress_staffmember_details = !empty($bookingpress_appointment_data['bookingpress_staff_member_details']) ? json_decode($bookingpress_appointment_data['bookingpress_staff_member_details'], TRUE) : array();

                if(!empty($bookingpress_staffmember_details)){
                    $bookingpress_staff_phone_no = !empty($bookingpress_staffmember_details['bookingpress_staffmember_phone']) ? $bookingpress_staffmember_details['bookingpress_staffmember_phone'] : '';

                    $bookingpress_staff_country_dial_code = !empty($bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code']) ? $bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code'] : '';

                    if(!empty($bookingpress_staff_phone_no)){
                        $bookingpress_staff_phone_no = preg_replace('/^0/', '', $bookingpress_staff_phone_no);
                    }

                    if(!empty($bookingpress_staff_country_dial_code)){
                        $bookingpress_staff_phone_no = "+".$bookingpress_staff_country_dial_code."".$bookingpress_staff_phone_no;
                    }
                    if(!empty($bookingpress_staff_phone_no)){
                        $bookingpress_send_res = $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_staff_phone_no, '', 0, $bookingpress_appointment_id, $bookingpress_configured_options, 'Complete Payment URL', 'employee');
                    }
                }
            }
        }

        function bookingpress_add_more_complete_payment_link_option_func(){
            ?>
            <el-checkbox class="bpa-front-label bpa-custom-checkbox--is-label" label="whatsapp"><?php esc_html_e( 'Through Whatsapp', 'bookingpress-whatsapp' ); ?></el-checkbox>
            <?php
        }
	
	    function set_whatsapp_css() {
            wp_register_style('bookigpress_whatsapp_amin_css',BOOKINGPRESS_WHATSAPP_URL.'/core/css/bookingpress_whatsapp_admin.css',array(),BOOKINGPRESS_WHATSAPP_VERSION);
            if(isset($_REQUEST['page']) && (sanitize_text_field($_REQUEST['page']) == 'bookingpress_settings' || 'bookingpress_notifications' == sanitize_text_field($_REQUEST['page'])) ) {
                wp_enqueue_style('bookigpress_whatsapp_amin_css');
            }
        }

        function bpa_externally_share_appointment_url_func($bpa_share_url_form_data){
            global $BookingPress;
            if( !empty($bpa_share_url_form_data['whatsapp_sharing']) && ($bpa_share_url_form_data['whatsapp_sharing'] == "true") && !empty($bpa_share_url_form_data['phone_number']) ){
                $bookingpress_phone_no = $bpa_share_url_form_data['phone_number'];
                $bookingpress_configured_options = array();
                $bookingpress_email_notification_type = 'Share Appointment URL';                
                $this->bookingpress_send_whatsapp_function($bookingpress_phone_no, '', 0, 0, $bookingpress_configured_options, $bookingpress_email_notification_type, 'customer');
            }
        }
        
        function bookingpress_modify_appointment_data_fields_func($bookingpress_appointment_vue_data_fields){
            $bookingpress_appointment_vue_data_fields['appointment_formdata']['bookingpress_appointment_meta_fields_value']['send_whatsapp_notification'] = false;

            if(empty($bookingpress_appointment_vue_data_fields['share_url_form']['phone_number'])){
                $bookingpress_appointment_vue_data_fields['share_url_form']['phone_number'] = '';
            }

            if(empty($bookingpress_appointment_vue_data_fields['share_url_rules']['phone_number'])){
                $bookingpress_appointment_vue_data_fields['share_url_rules']['phone_number'] = array(
                    array(
                        'required' => true,
                        'message'  => __('Please enter phone number', 'bookingpress-whatsapp'),
                        'trigger'  => 'blur',
                    ),
                );
            }
            $bookingpress_appointment_vue_data_fields['share_url_form']['whatsapp_sharing'] = false;
            return $bookingpress_appointment_vue_data_fields;
        }

        function bookingpress_add_more_sharing_url_options_for_appointment_func(){
            ?>
                <label class="bpa-form-label bpa-custom-checkbox--is-label"> <el-checkbox v-model="share_url_form.whatsapp_sharing" @change="bpa_enable_service_share"></el-checkbox> <?php esc_html_e( 'Whatsapp', 'bookingpress-whatsapp' ); ?></label>
            <?php
        }

        function bookingpress_add_more_sharing_url_content_for_appointment_func(){
            ?>
                <div class="bpa-form-body-row" v-if="(share_url_form.sms_sharing != true || share_url_form.sms_sharing == undefined) && share_url_form.whatsapp_sharing == true">
                    <el-row>
                        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                            <el-form-item prop="phone_number">
                                <template #label>
                                    <span class="bpa-form-label"><?php echo esc_html__('Phone Number', 'bookingpress-whatsapp'); ?></span>
                                </template>
                                <el-input class="bpa-form-control" v-model="share_url_form.phone_number" placeholder="<?php esc_html_e('Enter phone number', 'bookingpress-whatsapp'); ?>" @blur="bpa_enable_service_share"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </div>
            <?php
        }

        function bookingpress_modify_capability_data_func($bpa_caps){
            $bpa_caps['bookingpress_settings'][] = 'bpa_send_test_whatspp_msg';
            $bpa_caps['bookingpress_notifications'][] = 'bpa_refresh_whatsapp_template';
            return $bpa_caps;
        }

        function bookingpress_is_whatsapp_addon_activated($plugin,$network_activation)
        { 
            $myaddon_name = "bookingpress-whatsapp/bookingpress-whatsapp.php";

            if($plugin == $myaddon_name)
            { 
                if(!(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')))
                {
                    deactivate_plugins($plugin, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Whatsapp Add-on', 'bookingpress-whatsapp');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-whatsapp'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }

                $license = trim( get_option( 'bkp_license_key' ) );
                $package = trim( get_option( 'bkp_license_package' ) );

                if( '' === $license || false === $license ) 
                {
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Whatsapp Add-on', 'bookingpress-whatsapp');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-whatsapp'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }
                else
                {
                    $store_url = BOOKINGPRESS_WHATSAPP_STORE_URL;
                    $api_params = array(
                        'edd_action' => 'check_license',
                        'license' => $license,
                        'item_id'  => $package,
                        //'item_name' => urlencode( $item_name ),
                        'url' => home_url()
                    );
                    $response = wp_remote_post( $store_url, array( 'body' => $api_params, 'timeout' => 15, 'sslverify' => false ) );
                    if ( is_wp_error( $response ) ) {
                        return false;
                    }
        
                    $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                    $license_data_string =  wp_remote_retrieve_body( $response );
        
                    $message = '';

                    if ( true === $license_data->success ) 
                    {
                        if($license_data->license != "valid")
                        {
                            deactivate_plugins($myaddon_name, FALSE);
                            $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                            $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Whatsapp Add-on', 'bookingpress-whatsapp');
                            $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-whatsapp'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
                            wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                            die;
                        }

                    }
                    else
                    {
                        deactivate_plugins($myaddon_name, FALSE);
                        $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                        $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Whatsapp Add-on', 'bookingpress-whatsapp');
                        $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-whatsapp'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
                        wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                        die;
                    }
                }
            }
        }

        function bookingpress_add_booking_form_basic_details_data_func(){
            ?>
             <div class="bpa-sm--item">
                <label class="bpa-form-label"><?php esc_html_e('Send whatsapp notification', 'bookingpress-whatsapp'); ?></label>
                <el-input v-model="basic_details_container_data.send_whatsapp_notification_label" class="bpa-form-control"></el-input>
            </div>
            <?php
        }

        function bookingpress_front_modify_cart_data_filter_func($bookingpress_appointment_details) {
            global $BookingPress;            
            if(isset($bookingpress_appointment_details['form_fields']['send_whatsapp_notification']) && !empty($bookingpress_appointment_details['form_fields']['send_whatsapp_notification'])) {
                $bookingpress_appointment_details['form_fields']['send_whatsapp_notification'] = $bookingpress_appointment_details['form_fields']['send_whatsapp_notification'] == 'true' ? true : $bookingpress_appointment_details['form_fields']['send_whatsapp_notification'];
            }
            return $bookingpress_appointment_details;
        }

        function bookingpress_get_appointment_meta_value_filter_func($bookingpress_form_field_value) {
            if(!empty($bookingpress_form_field_value['send_whatsapp_notification']) && $bookingpress_form_field_value['send_whatsapp_notification'] == 'true') {
                $bookingpress_form_field_value['send_whatsapp_notification'] = true;                
            } else {
                $bookingpress_form_field_value['send_whatsapp_notification'] = false;
            }
            return $bookingpress_form_field_value;
        }

        function bookingpress_add_appointment_field_section_func() {
            ?>
            <el-col :xs="24" :sm="24" :md="24" :lg="08" :xl="08">
                <el-form-item>
                    <label class="bpa-form-label bpa-custom-checkbox--is-label"> <el-checkbox v-model="appointment_formdata.bookingpress_appointment_meta_fields_value.send_whatsapp_notification"></el-checkbox> <?php esc_html_e( 'Send Whatsapp Notification', 'bookingpress-whatsapp' ); ?></label>
                </el-form-item>
            </el-col>
            <?php
        }
        
        function bookingpress_before_save_customize_booking_form_func($booking_form_settings) {            
            $send_whatsapp_notification_label = ! empty($_POST['basic_details_container_data']['send_whatsapp_notification_label']) ? sanitize_text_field($_POST['basic_details_container_data']['send_whatsapp_notification_label']) : '';   // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.         
            $booking_form_settings['basic_details_container_data']['send_whatsapp_notification_label'] = $send_whatsapp_notification_label; 
            return $booking_form_settings;
        }

        function bookingpress_customize_add_dynamic_data_fields_func($bookingpress_customize_vue_data_fields) {
            $bookingpress_customize_vue_data_fields['is_whatsapp_activated'] = $this->is_addon_activated();
            $bookingpress_customize_vue_data_fields['basic_details_container_data']['send_whatsapp_notification_label'] = '';                        
            return $bookingpress_customize_vue_data_fields;
        }

        function bookingpress_get_booking_form_customize_data_filter_func($bookingpress_booking_form_data) {
            $bookingpress_booking_form_data['basic_details_container_data']['send_whatsapp_notification_label'] = '';   
            return $bookingpress_booking_form_data;
        }

        public function is_addon_activated(){
            $bookingpress_captcha_version = get_option('bookingpress_whatsapp_gateway');
            return !empty($bookingpress_captcha_version) ? 1 : 0;
        }

        function bookingpress_frontend_apointment_form_add_dynamic_data_func($bookingpress_front_vue_data_fields) {
            global $BookingPress;
            $bookingpress_whatsapp_selected_phone_number_field = $BookingPress->bookingpress_get_settings('bookingpress_whatsapp_selected_phone_number_field', 'notification_setting');
            $bookingpress_selected_whatsapp_gateway = $BookingPress->bookingpress_get_settings('bookingpress_selected_whatsapp_gateway', 'notification_setting');
            $whatsapp_twilio_account_sid = $BookingPress->bookingpress_get_settings('whatsapp_twilio_account_sid','notification_setting');
            $whatsapp_twilio_auth_token = $BookingPress->bookingpress_get_settings('whatsapp_twilio_auth_token', 'notification_setting');     
            $whatsapp_twilio_from_number = $BookingPress->bookingpress_get_settings('whatsapp_twilio_from_number', 'notification_setting');
            
            $send_whatsapp_notification_label = $BookingPress->bookingpress_get_customize_settings('send_whatsapp_notification_label', 'booking_form');
            $bookingpress_front_vue_data_fields['send_whatsapp_notification_label'] = stripslashes_deep($send_whatsapp_notification_label);

            $whatsapp_phone_id = $BookingPress->bookingpress_get_settings( 'whatsapp_phone_number_id', 'notification_setting' );
            $whatsapp_business_id = $BookingPress->bookingpress_get_settings( 'whatsapp_business_account_id', 'notification_setting' );
            $whatsapp_permanent_token = $BookingPress->bookingpress_get_settings( 'whatsapp_business_access_token', 'notification_setting' );

            $display_consent_field = false;
            if( (!empty($whatsapp_twilio_account_sid) && !empty($whatsapp_twilio_auth_token) && !empty($whatsapp_twilio_from_number)) || ( !empty( $whatsapp_phone_id ) && !empty( $whatsapp_business_id ) && !empty( $whatsapp_permanent_token ) ) ){
                $display_consent_field = true;
            }

            if(!empty( $bookingpress_whatsapp_selected_phone_number_field ) && !empty($bookingpress_selected_whatsapp_gateway) && $bookingpress_selected_whatsapp_gateway != 'select_whatsapp_gateway' && $display_consent_field ) {

                $bookingpress_field_data = $this->bookingpress_is_appointment_field_active($bookingpress_whatsapp_selected_phone_number_field);
                if(!empty($bookingpress_field_data)) {                                        
                    $bookingpress_front_vue_data_fields['is_display_whatsapp_consent_field'] = true;                
                    $bookingpress_front_vue_data_fields['whatsapp_notification_selected_field'] = $bookingpress_whatsapp_selected_phone_number_field;
                    $bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['send_whatsapp_notification'] = true;
                    $is_default_field = $bookingpress_field_data['bookingpress_field_is_default'];                
                    if($is_default_field == '1') {
                        $bookingpress_field_name = $bookingpress_field_data['bookingpress_form_field_name'];
                        if($bookingpress_field_name == 'fullname' ) {
                            $bookingpress_front_vue_data_fields['whatsapp_notification_selected_field'] = 'customer_name';
                        } elseif($bookingpress_field_name == 'firstname') {
                            $bookingpress_front_vue_data_fields['whatsapp_notification_selected_field'] = 'customer_firstname';
                        } elseif($bookingpress_field_name == 'lastname') {
                            $bookingpress_front_vue_data_fields['whatsapp_notification_selected_field'] = 'customer_lastname';
                        } elseif($bookingpress_field_name == 'phone_number') {
                            $bookingpress_front_vue_data_fields['whatsapp_notification_selected_field'] = 'customer_phone';
                        }
                    }
                }    
            }   
            return $bookingpress_front_vue_data_fields;
        }

        /**
         * Function for package order data
         *
         * @param  mixed $bookingpress_front_vue_data_fields
         * @return void
         */
        function bookingpress_frontend_whatsapp_form_add_dynamic_data_func($bookingpress_front_vue_data_fields){

            global $BookingPress;
            $bookingpress_whatsapp_selected_phone_number_field = $BookingPress->bookingpress_get_settings('bookingpress_whatsapp_selected_phone_number_field', 'notification_setting');
            $bookingpress_selected_whatsapp_gateway = $BookingPress->bookingpress_get_settings('bookingpress_selected_whatsapp_gateway', 'notification_setting');
            
            $send_whatsapp_notification_label = $BookingPress->bookingpress_get_customize_settings('send_whatsapp_notification_label', 'booking_form');
            $bookingpress_front_vue_data_fields['send_whatsapp_notification_label'] = stripslashes_deep($send_whatsapp_notification_label);

            if(!empty( $bookingpress_whatsapp_selected_phone_number_field ) && !empty($bookingpress_selected_whatsapp_gateway) && $bookingpress_selected_whatsapp_gateway != 'select_whatsapp_gateway') {

                $bookingpress_field_data = $this->bookingpress_is_appointment_field_active($bookingpress_whatsapp_selected_phone_number_field);
                if(!empty($bookingpress_field_data)) {                                        
                    $bookingpress_front_vue_data_fields['is_display_whatsapp_consent_field'] = true;                
                    $bookingpress_front_vue_data_fields['whatsapp_notification_selected_field'] = $bookingpress_whatsapp_selected_phone_number_field;
                    $bookingpress_front_vue_data_fields['appointment_step_form_data']['form_fields']['send_whatsapp_notification'] = true;
                    $is_default_field = $bookingpress_field_data['bookingpress_field_is_default'];                
                    if($is_default_field == '1') {
                        $bookingpress_field_name = $bookingpress_field_data['bookingpress_form_field_name'];
                        if($bookingpress_field_name == 'fullname' ) {
                            $bookingpress_front_vue_data_fields['whatsapp_notification_selected_field'] = 'customer_name';
                        } elseif($bookingpress_field_name == 'firstname') {
                            $bookingpress_front_vue_data_fields['whatsapp_notification_selected_field'] = 'customer_firstname';
                        } elseif($bookingpress_field_name == 'lastname') {
                            $bookingpress_front_vue_data_fields['whatsapp_notification_selected_field'] = 'customer_lastname';
                        } elseif($bookingpress_field_name == 'phone_number') {
                            $bookingpress_front_vue_data_fields['whatsapp_notification_selected_field'] = 'customer_phone';
                        }
                    }
                }

            }
            return $bookingpress_front_vue_data_fields;

        }

        function bookingpress_addon_list_data_filter_func($bookingpress_body_res){
            global $bookingpress_slugs;
            if(!empty($bookingpress_body_res)) {
                foreach($bookingpress_body_res as $bookingpress_body_res_key =>$bookingpress_body_res_val) {
                    $bookingpress_setting_page_url = add_query_arg('page', $bookingpress_slugs->bookingpress_settings, esc_url( admin_url() . 'admin.php?page=bookingpress' ));
                    $bookingpress_config_url = add_query_arg('setting_page', 'notification_settings', $bookingpress_setting_page_url);
                    if($bookingpress_body_res_val['addon_key'] == 'bookingpress_whatsapp_gateway') {
                        $bookingpress_body_res[$bookingpress_body_res_key]['addon_configure_url'] = $bookingpress_config_url;
                    }
                }
            }
            return $bookingpress_body_res;
        } 

        function bookingpress_get_mobile_number_field($bookingpress_appointment_data) {
            global $BookingPress,$wpdb,$tbl_bookingpress_appointment_meta;
            $bookingpress_customer_phone = '';
            $bookingpress_whatsapp_selected_phone_number_field = $BookingPress->bookingpress_get_settings('bookingpress_whatsapp_selected_phone_number_field', 'notification_setting');             
            if(!empty($bookingpress_whatsapp_selected_phone_number_field)) {   
                $bookingpress_field_data = $this->bookingpress_is_appointment_field_active($bookingpress_whatsapp_selected_phone_number_field);
                if(!empty($bookingpress_field_data))  {   
                    $is_default_field = $bookingpress_field_data['bookingpress_field_is_default'];
                    if($is_default_field == '1') {
                        $bookingpress_field_name = $bookingpress_field_data['bookingpress_form_field_name'];
                        if($bookingpress_field_name == 'fullname' ) {
                            $bookingpress_customer_phone = !empty($bookingpress_appointment_data['bookingpress_customer_name']) ? $bookingpress_appointment_data['bookingpress_customer_name'] : '';
                            if(!empty($bookingpress_customer_phone)) {
                                $bookingpress_customer_phone = "+".$bookingpress_customer_phone;
                            }
                        } elseif($bookingpress_field_name == 'firstname') {
                            $bookingpress_customer_phone = !empty($bookingpress_appointment_data['bookingpress_customer_firstname']) ? $bookingpress_appointment_data['bookingpress_customer_firstname'] : '';
                            if(!empty($bookingpress_customer_phone)) {
                                $bookingpress_customer_phone = "+".$bookingpress_customer_phone;
                            }
                        } elseif($bookingpress_field_name == 'lastname') {
                            $bookingpress_customer_phone = !empty($bookingpress_appointment_data['bookingpress_customer_lastname']) ? $bookingpress_appointment_data['bookingpress_customer_lastname'] : '';
                            if(!empty($bookingpress_customer_phone)) {
                                $bookingpress_customer_phone = "+".$bookingpress_customer_phone;
                            }                       
                        } elseif($bookingpress_field_name == 'phone_number') {
                            $bookingpress_customer_phone = !empty($bookingpress_appointment_data['bookingpress_customer_phone']) ? $bookingpress_appointment_data['bookingpress_customer_phone'] : '';                            
                            $bookingpress_country_dial_code = !empty($bookingpress_appointment_data['bookingpress_customer_phone_dial_code']) ? $bookingpress_appointment_data['bookingpress_customer_phone_dial_code'] : '';                
                            if(!empty($bookingpress_customer_phone)){
                                $bookingpress_customer_phone = preg_replace('/^0/', '', $bookingpress_customer_phone);
                            }            
                            if(!empty($bookingpress_country_dial_code) && !empty($bookingpress_customer_phone)){
                                $bookingpress_customer_phone = "+".$bookingpress_country_dial_code."".$bookingpress_customer_phone;
                            }            
                        }
                    } else {
                        $bookingpress_field_name = $bookingpress_whatsapp_selected_phone_number_field;
                        $bookingpress_appointment_id = !empty($bookingpress_appointment_data['bookingpress_appointment_booking_id']) ? intval($bookingpress_appointment_data['bookingpress_appointment_booking_id']) : '';                        

                        $bookingpress_appointment_meta_data = $wpdb->get_results( $wpdb->prepare( "SELECT bookingpress_appointment_meta_value,bookingpress_appointment_meta_key FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_id = %d", $bookingpress_appointment_id ), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_appointment_meta is a table name. false alarm.
                        $bookingpress_appointment_field_meta_data  = array();
                        foreach($bookingpress_appointment_meta_data as $key => $value) {                            
                            if($value['bookingpress_appointment_meta_key'] == 'appointment_form_fields_data') {
                                $bookingpress_appointment_field_meta_data = !empty($value['bookingpress_appointment_meta_value']) ? json_decode
                                ($value['bookingpress_appointment_meta_value'],true): array();
                            }                            
                        }     
                        $bookingpress_appointment_form_fields = !empty($bookingpress_appointment_field_meta_data['form_fields']) ? $bookingpress_appointment_field_meta_data['form_fields'] : array();                                               
                        $bookingpress_customer_phone = !empty($bookingpress_appointment_form_fields[$bookingpress_field_name]) ? $bookingpress_appointment_form_fields[$bookingpress_field_name] : '';
                        
                        if(!empty($bookingpress_customer_phone)) {
                            $bookingpress_customer_phone = "+".$bookingpress_customer_phone;
                        }
                    }                    
                }    
            } 
            return $bookingpress_customer_phone;
        }

        function bookingpress_send_whatsapp_notification($bookingpress_appointment_data) {
            global $wpdb,$tbl_bookingpress_appointment_meta;
            $bookingpress_appointment_form_fields = array();
            $bookingpress_appointment_id = !empty($bookingpress_appointment_data['bookingpress_appointment_booking_id']) ? intval($bookingpress_appointment_data['bookingpress_appointment_booking_id']) : 0;
            
            $bookingpress_check_group_order_for_whatsapp = apply_filters('bookingpress_check_group_order_for_whatsapp',false,$bookingpress_appointment_data);

            if((!empty($bookingpress_appointment_data['bookingpress_is_cart']) && $bookingpress_appointment_data['bookingpress_is_cart'] == 1 ) || $bookingpress_check_group_order_for_whatsapp){
                $bookingpress_order_id = !empty($bookingpress_appointment_data['bookingpress_order_id']) ? intval($bookingpress_appointment_data['bookingpress_order_id']) : 0;
                
                if(!empty($bookingpress_order_id)) {
                    $bookingpress_appointment_meta_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_meta_value,bookingpress_appointment_meta_key FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_order_id = %d AND bookingpress_appointment_meta_key = %s", $bookingpress_order_id,'appointment_details' ), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_appointment_meta is a table name. false alarm.
                }
                $bookingpress_appointment_meta_data = !empty($bookingpress_appointment_meta_data['bookingpress_appointment_meta_value']) ? json_decode($bookingpress_appointment_meta_data['bookingpress_appointment_meta_value'],true) : array();                       
                $bookingpress_appointment_form_fields = !empty($bookingpress_appointment_meta_data['form_fields']) ? $bookingpress_appointment_meta_data['form_fields'] : array();
                $send_whatsapp_notification = !empty($bookingpress_appointment_form_fields['send_whatsapp_notification']) ? $bookingpress_appointment_form_fields['send_whatsapp_notification'] : '';
                
            } else {                            
                $bookingpress_appointment_meta_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_appointment_meta_value,bookingpress_appointment_meta_key FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_id = %d AND bookingpress_appointment_meta_key = %s", $bookingpress_appointment_id,'appointment_form_fields_data' ), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason $tbl_bookingpress_appointment_meta is a table name. false alarm.

                $bookingpress_appointment_meta_data = !empty($bookingpress_appointment_meta_data['bookingpress_appointment_meta_value']) ? json_decode($bookingpress_appointment_meta_data['bookingpress_appointment_meta_value'],true) : array();
                              
                $bookingpress_appointment_form_fields = !empty($bookingpress_appointment_meta_data['form_fields']) ? $bookingpress_appointment_meta_data['form_fields'] : array();
            }

            $send_whatsapp_notification = !empty($bookingpress_appointment_form_fields['send_whatsapp_notification']) ? $bookingpress_appointment_form_fields['send_whatsapp_notification'] : '';
            return $send_whatsapp_notification;
        }

        function bookingpress_is_appointment_field_active($appointment_field) {
            global $wpdb, $tbl_bookingpress_form_fields;            
            $bookingpress_field_list_data = $wpdb->get_row( $wpdb->prepare( 'SELECT bookingpress_field_is_default,bookingpress_form_field_name FROM ' . $tbl_bookingpress_form_fields . ' WHERE bookingpress_is_customer_field = %d AND bookingpress_field_meta_key = %s order by bookingpress_form_field_id',0,$appointment_field), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason $tbl_bookingpress_form_fields is a table name. false alarm.

            return $bookingpress_field_list_data;
        }

        function bookingpress_after_add_appointment_from_backend_func($inserted_booking_id, $bookingpress_appointment_data, $entry_id) {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings;

            
            $bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $inserted_booking_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

            if(!empty($bookingpress_appointment_data)) {                 
                $bookingpress_appointment_status = $bookingpress_appointment_data['bookingpress_appointment_status'];
                $bookingpress_configured_options = array();

                $bookingpress_staff_email_notification_type = $bookingpress_email_notification_type = '';
                if ( $bookingpress_appointment_status == '2' ) {
                    $bookingpress_staff_email_notification_type  = $bookingpress_email_notification_type = 'Appointment Pending';
                } elseif ( $bookingpress_appointment_status == '1' ) {
                    $bookingpress_staff_email_notification_type = $bookingpress_email_notification_type = 'Appointment Approved';
                } elseif ( $bookingpress_appointment_status == '3' ) {
                    $bookingpress_staff_email_notification_type = $bookingpress_email_notification_type = 'Appointment Canceled';
                } elseif ( $bookingpress_appointment_status == '4' ) {
                    $bookingpress_staff_email_notification_type = $bookingpress_email_notification_type = 'Appointment Rejected';
                }

                $bookingpress_staff_email_notification_type = $bookingpress_email_notification_type = apply_filters('bookingpress_modify_send_email_notification_type',$bookingpress_email_notification_type,$bookingpress_appointment_status);                                                   

                if(!empty($_POST['appointment_data']['complete_payment_url_selection']) && $_POST['appointment_data']['complete_payment_url_selection'] == 'send_payment_link' && !empty($_POST['appointment_data']['complete_payment_url_selected_method'] && in_array('whatsapp',$_POST['appointment_data']['complete_payment_url_selected_method']))){ // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
                    $bookingpress_phone_no = $this->bookingpress_get_mobile_number_field($bookingpress_appointment_data);
                    $this->bookingpress_send_whatsapp_function($bookingpress_phone_no, '', 0, $inserted_booking_id, $bookingpress_configured_options, 'Complete Payment URL', 'customer');
                }

                if(!empty($_POST['appointment_data']['complete_payment_url_selection']) && $_POST['appointment_data']['complete_payment_url_selection'] == 'send_payment_link' && !empty($_POST['appointment_data']['complete_payment_url_selected_method'] && in_array('whatsapp',$_POST['appointment_data']['complete_payment_url_selected_method']))){ // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.
                    $bookingpress_whatsapp_admin_number = $this->bookingpress_get_whatsapp_admin_number('Complete Payment URL');
                    if(!empty($bookingpress_whatsapp_admin_number)){
                        $bookingpress_whatsapp_admin_number_arr = explode( ',', $bookingpress_whatsapp_admin_number );
                        if(!empty($bookingpress_whatsapp_admin_number_arr) && is_array($bookingpress_whatsapp_admin_number_arr)){
                            foreach($bookingpress_whatsapp_admin_number_arr as $whatsappno){
                                $bookingpress_send_res = $this->bookingpress_send_whatsapp_function($whatsappno, '', 0, $inserted_booking_id, $bookingpress_configured_options, 'Complete Payment URL', 'employee');
                            }
                        }
                    }                    
                }

                //Send staff email notification
                $bookingpress_staffmember_details = !empty($bookingpress_appointment_data['bookingpress_staff_member_details']) ? json_decode($bookingpress_appointment_data['bookingpress_staff_member_details'], TRUE) : array();

                if(!empty($bookingpress_staffmember_details)){
                    $bookingpress_staff_phone_no = !empty($bookingpress_staffmember_details['bookingpress_staffmember_phone']) ? $bookingpress_staffmember_details['bookingpress_staffmember_phone'] : '';

                    $bookingpress_staff_country_dial_code = !empty($bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code']) ? $bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code'] : '';

                    if(!empty($bookingpress_staff_phone_no)){
                        $bookingpress_staff_phone_no = preg_replace('/^0/', '', $bookingpress_staff_phone_no);
                    }

                    if(!empty($bookingpress_staff_country_dial_code)){
                        $bookingpress_staff_phone_no = "+".$bookingpress_staff_country_dial_code."".$bookingpress_staff_phone_no;
                    }

                    if(!empty($bookingpress_staff_phone_no)){
                        //$this->bookingpress_send_all_whatsapp_notification_function($bookingpress_staff_phone_no, '', 0, $inserted_booking_id, $bookingpress_configured_options, $bookingpress_staff_email_notification_type, 'employee');
                    }

                    if(!empty($_POST['appointment_data']['complete_payment_url_selection']) && $_POST['appointment_data']['complete_payment_url_selection'] == 'send_payment_link' && !empty($_POST['appointment_data']['complete_payment_url_selected_method'] && in_array('whatsapp',$_POST['appointment_data']['complete_payment_url_selected_method']))) { // phpcs:ignore WordPress.Security.NonceVerification.Missing --Reason Nonce already verified from the caller function.

                        if(!empty($bookingpress_staff_phone_no)){
                            $this->bookingpress_send_whatsapp_function($bookingpress_staff_phone_no, '', 0, $inserted_booking_id, $bookingpress_configured_options, 'Complete Payment URL', 'employee');
                        }
                    }
                }
            }
        }

        function bookingpress_send_whatsapp_notification_after_appointment_booked($inserted_booking_id, $entry_id, $payment_gateway_data) {

            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings;

            $bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $inserted_booking_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

            $bookingpress_send_single_integration_notification_after_booking = apply_filters('bookingpress_send_single_whatsapp_notification_after_booking',true,$bookingpress_appointment_data);
            
            if(!empty($bookingpress_appointment_data) && $bookingpress_send_single_integration_notification_after_booking){                
                $bookingpress_appointment_status = $bookingpress_appointment_data['bookingpress_appointment_status'];
                $bookingpress_configured_options = array();                
                $bookingpress_email_notification_type = '';
                if ( $bookingpress_appointment_status == '2' ) {
                    $bookingpress_email_notification_type = 'Appointment Pending';
                } elseif ( $bookingpress_appointment_status == '1' ) {
                    $bookingpress_email_notification_type = 'Appointment Approved';
                } elseif ( $bookingpress_appointment_status == '3' ) {
                    $bookingpress_email_notification_type = 'Appointment Canceled';
                } elseif ( $bookingpress_appointment_status == '4' ) {
                    $bookingpress_email_notification_type = 'Appointment Rejected';
                }
                $bookingpress_email_notification_type = apply_filters('bookingpress_modify_send_email_notification_type',$bookingpress_email_notification_type,$bookingpress_appointment_status);
                
                

                $is_send_whatsapp_notification = $this->bookingpress_send_whatsapp_notification($bookingpress_appointment_data);
                if(!empty($is_send_whatsapp_notification) && $is_send_whatsapp_notification == 'true') {     
                    $bookingpress_phone_no = $this->bookingpress_get_mobile_number_field($bookingpress_appointment_data);
                    $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_phone_no, '', 0, $inserted_booking_id, $bookingpress_configured_options, $bookingpress_email_notification_type, 'customer');                    
                }   

                $bookingpress_whatsapp_admin_number = '';
                $bookingpress_send_res = $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_whatsapp_admin_number, '', 0, $inserted_booking_id, $bookingpress_configured_options, $bookingpress_email_notification_type, 'employee');

                //Send staff email notification
                $bookingpress_staffmember_details = !empty($bookingpress_appointment_data['bookingpress_staff_member_details']) ? json_decode($bookingpress_appointment_data['bookingpress_staff_member_details'], TRUE) : array();

                if(!empty($bookingpress_staffmember_details)){
                    $bookingpress_staff_phone_no = !empty($bookingpress_staffmember_details['bookingpress_staffmember_phone']) ? $bookingpress_staffmember_details['bookingpress_staffmember_phone'] : '';

                    $bookingpress_staff_country_dial_code = !empty($bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code']) ? $bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code'] : '';

                    if(!empty($bookingpress_staff_phone_no)){
                        $bookingpress_staff_phone_no = preg_replace('/^0/', '', $bookingpress_staff_phone_no);
                    }

                    if(!empty($bookingpress_staff_country_dial_code)){
                        $bookingpress_staff_phone_no = "+".$bookingpress_staff_country_dial_code."".$bookingpress_staff_phone_no;
                    }

                    if(!empty($bookingpress_staff_phone_no)){
                        $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_staff_phone_no, '', 0, $inserted_booking_id, $bookingpress_configured_options, $bookingpress_email_notification_type, 'employee');
                    }
                }
            }
        }

        function bookingpress_staff_cron_external_notification_func($appointment_id, $bookingpress_email_notification_name, $bookingpress_notification_id,$bookingpress_db_fields) {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings,$tbl_bookingpress_cron_email_notifications_logs;
            $bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

            $bookingpress_customer_id = !empty($bookingpress_db_fields['bookingpress_customer_id']) ? $bookingpress_db_fields['bookingpress_customer_id'] : 0;
            $bookingpress_customer_email = !empty($bookingpress_db_fields['bookingpress_email_address']) ? $bookingpress_db_fields['bookingpress_email_address'] : '';
            $bookingpress_appointment_date = !empty($bookingpress_db_fields['bookingpress_appointment_date']) ? $bookingpress_db_fields['bookingpress_appointment_date'] : '';
            $bookingpress_appointment_time = !empty($bookingpress_db_fields['bookingpress_appointment_time']) ? $bookingpress_db_fields['bookingpress_appointment_time'] : '';               
            $bookingpress_appointment_status = !empty($bookingpress_db_fields['bookingpress_appointment_status']) ? $bookingpress_db_fields['bookingpress_appointment_status'] : '';                      
            $bookingpress_email_cron_hook_name = !empty($bookingpress_db_fields['bookingpress_email_cron_hook_name']) ? $bookingpress_db_fields['bookingpress_email_cron_hook_name'] : '';
            $bookingpress_staffmember_id = !empty($bookingpress_db_fields['bookingpress_staffmember_id']) ? intval($bookingpress_db_fields['bookingpress_staffmember_id']) : '';
            $bookingpress_staffmember_email = !empty($bookingpress_db_fields['bookingpress_staffmember_email']) ? ($bookingpress_db_fields['bookingpress_staffmember_email']) : '';

            $is_sent_notification = $this->bookingpress_check_cron_whatsapp_notification_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $appointment_id,$bookingpress_appointment_date, $bookingpress_appointment_time,$bookingpress_appointment_status, $bookingpress_email_cron_hook_name,$bookingpress_staffmember_id, $bookingpress_staffmember_email);                       
            
            $bookingpress_configured_options = array();
    
            $bookingpress_whatsapp_admin_number = '';
            $bookingpress_send_res = $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_whatsapp_admin_number, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_email_notification_name, 'employee');

            if(!empty($bookingpress_appointment_data) && empty($is_sent_notification)) {                

                $bookingpress_staffmember_details = !empty($bookingpress_appointment_data['bookingpress_staff_member_details']) ? json_decode($bookingpress_appointment_data['bookingpress_staff_member_details'], TRUE) : array();

                if(!empty($bookingpress_staffmember_details)){
                    $bookingpress_staff_phone_no = !empty($bookingpress_staffmember_details['bookingpress_staffmember_phone']) ? $bookingpress_staffmember_details['bookingpress_staffmember_phone'] : '';
                    $bookingpress_staff_country_dial_code = !empty($bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code']) ? $bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code'] : '';

                    if(!empty($bookingpress_staff_phone_no)){
                        $bookingpress_staff_phone_no = preg_replace('/^0/', '', $bookingpress_staff_phone_no);
                    }
                    if(!empty($bookingpress_staff_country_dial_code)){
                        $bookingpress_staff_phone_no = "+".$bookingpress_staff_country_dial_code."".$bookingpress_staff_phone_no;
                    }

                    $bookingpress_send_res = $this->bookingpress_send_whatsapp_function($bookingpress_staff_phone_no, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_email_notification_name, 'employee');

                    $is_notification_sent = !empty($bookingpress_send_res['return_type']) && $bookingpress_send_res['return_type'] == 'success' ?  1 : 0;
                    $email_configurations = !empty($bookingpress_send_res['configure_options']) ? $bookingpress_send_res['configure_options'] : array();
                    $to_message = !empty($bookingpress_send_res['to_message']) ? $bookingpress_send_res['to_message'] : '';
                    $return_res = !empty($bookingpress_send_res['return_res']) ? $bookingpress_send_res['return_res'] : '';
                    $bookingpress_email_posted_data = array(
                        'template_type'     => 'customer',
                        'notification_name' => $bookingpress_email_notification_name,
                        'appointment_id'    => $appointment_id,
                        'customer_email'    => $bookingpress_customer_email,
                        'template_details'  => $to_message,
                    );
                    $bookingpress_db_fields['bookingpress_notification_type'] = 'whatsapp';
                    $bookingpress_db_fields['bookingpress_email_is_sent'] =  $is_notification_sent;
                    $bookingpress_db_fields['bookingpress_email_posted_data'] = wp_json_encode( $bookingpress_email_posted_data );
                    $bookingpress_db_fields['bookingpress_email_response'] = $return_res;
                    $bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );
                    $wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
                }
            }
        }

        function bookingpress_cron_external_notification_func($appointment_id,$bookingpress_email_notification_name,$bookingpress_notification_id,$bookingpress_db_fields) {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings,$tbl_bookingpress_cron_email_notifications_logs;

            $bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm            

            $bookingpress_customer_id = !empty($bookingpress_db_fields['bookingpress_customer_id']) ? $bookingpress_db_fields['bookingpress_customer_id'] : 0;
            $bookingpress_customer_email = !empty($bookingpress_db_fields['bookingpress_email_address']) ? $bookingpress_db_fields['bookingpress_email_address'] : '';
            $bookingpress_appointment_date = !empty($bookingpress_db_fields['bookingpress_appointment_date']) ? $bookingpress_db_fields['bookingpress_appointment_date'] : '';
            $bookingpress_appointment_time = !empty($bookingpress_db_fields['bookingpress_appointment_time']) ? $bookingpress_db_fields['bookingpress_appointment_time'] : '';               
            $bookingpress_appointment_status = !empty($bookingpress_db_fields['bookingpress_appointment_status']) ? $bookingpress_db_fields['bookingpress_appointment_status'] : '';                      
            $bookingpress_email_cron_hook_name = !empty($bookingpress_db_fields['bookingpress_email_cron_hook_name']) ? $bookingpress_db_fields['bookingpress_email_cron_hook_name'] : '';

            $is_sent_notification = $this->bookingpress_check_cron_whatsapp_notification_sent_or_not( $bookingpress_notification_id, $bookingpress_customer_id, $bookingpress_customer_email, $appointment_id,$bookingpress_appointment_date, $bookingpress_appointment_time,$bookingpress_appointment_status, $bookingpress_email_cron_hook_name );
            
            if(!empty($bookingpress_appointment_data) && !empty($bookingpress_email_notification_name) && empty($is_sent_notification)) {                

                $bookingpress_appointment_id = !empty($bookingpress_appointment_data['bookingpress_appointment_booking_id']) ? intval($bookingpress_appointment_data['bookingpress_appointment_booking_id']) : '';
                $bookingpress_customer_email = !empty($bookingpress_appointment_data['bookingpress_customer_email']) ? intval($bookingpress_appointment_data['bookingpress_customer_email']) : '';    
                $is_send_whatsapp_notification = $this->bookingpress_send_whatsapp_notification($bookingpress_appointment_data);
                if(!empty($is_send_whatsapp_notification) && $is_send_whatsapp_notification == 'true') {
                    $bookingpress_phone_no = $this->bookingpress_get_mobile_number_field($bookingpress_appointment_data);                         
                    $bookingpress_configured_options = array();                    
                    if(!empty($bookingpress_phone_no)){                    
                        $bookingpress_send_res = $this->bookingpress_send_whatsapp_function($bookingpress_phone_no, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_email_notification_name, 'customer');

                        $is_notification_sent = !empty($bookingpress_send_res['return_type']) && $bookingpress_send_res['return_type'] == 'success' ?  1 : 0;
                        $email_configurations = !empty($bookingpress_send_res['configure_options']) ? $bookingpress_send_res['configure_options'] : array();
                        $to_message = !empty($bookingpress_send_res['to_message']) ? $bookingpress_send_res['to_message'] : '';
                        $return_res = !empty($bookingpress_send_res['return_res']) ? $bookingpress_send_res['return_res'] : '';
                        $bookingpress_email_posted_data = array(
                            'template_type'     => 'customer',
                            'notification_name' => $bookingpress_email_notification_name,
                            'appointment_id'    => $bookingpress_appointment_id,
                            'customer_email'    => $bookingpress_customer_email,
                            'template_details'  => $to_message,
                        );
                        $bookingpress_db_fields['bookingpress_notification_type'] = 'whatsapp';
						$bookingpress_db_fields['bookingpress_email_is_sent'] =  $is_notification_sent;
                        $bookingpress_db_fields['bookingpress_email_posted_data'] = wp_json_encode( $bookingpress_email_posted_data );
						$bookingpress_db_fields['bookingpress_email_response'] = $return_res;
						$bookingpress_db_fields['bookingpress_email_sending_configuration'] = wp_json_encode( $email_configurations );
						$wpdb->insert( $tbl_bookingpress_cron_email_notifications_logs, $bookingpress_db_fields );
                    }
                }  

            }            
        }     

        function bookingpress_after_change_appointment_status_func($appointment_id, $appointment_new_status){

            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings;
            $bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

            if(!empty($bookingpress_appointment_data) && !empty($bookingpress_appointment_data['bookingpress_customer_id'])) {
                $bookingpress_configured_options = array();
                $bookingpress_notification_type = $bookingpress_staff_notification_name = '';
                if ($appointment_new_status == '1' ) {
                    $bookingpress_notification_type = $bookingpress_staff_notification_name = 'Appointment Approved';
                } else if ($appointment_new_status == '2') {
                    $bookingpress_notification_type = $bookingpress_staff_notification_name = 'Appointment Pending';
                } else if ($appointment_new_status == '3') {
                    $bookingpress_notification_type = $bookingpress_staff_notification_name = 'Appointment Canceled';
                } else if ($appointment_new_status == '4') {
                    $bookingpress_notification_type = $bookingpress_staff_notification_name = 'Appointment Rejected';
                }
                
                $bookingpress_notification_type = $bookingpress_staff_notification_name = apply_filters('bookingpress_modify_send_email_notification_type',$bookingpress_notification_type,$appointment_new_status);

                $is_send_whatsapp_notification = $this->bookingpress_send_whatsapp_notification($bookingpress_appointment_data);

                if(!empty($is_send_whatsapp_notification) && $is_send_whatsapp_notification == 'true') {        
                    $bookingpress_phone_no = $this->bookingpress_get_mobile_number_field($bookingpress_appointment_data);
                    $bookingpress_send_res = $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_phone_no, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_notification_type, 'customer');

                }

                $bookingpress_whatsapp_admin_number = '';
                $bookingpress_send_res = $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_whatsapp_admin_number, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_notification_type, 'employee');

                //Send staff email notification
                $bookingpress_staffmember_details = !empty($bookingpress_appointment_data['bookingpress_staff_member_details']) ? json_decode($bookingpress_appointment_data['bookingpress_staff_member_details'], TRUE) : array();

                if(!empty($bookingpress_staffmember_details)){
                    $bookingpress_staff_phone_no = !empty($bookingpress_staffmember_details['bookingpress_staffmember_phone']) ? $bookingpress_staffmember_details['bookingpress_staffmember_phone'] : '';

                    $bookingpress_staff_country_dial_code = !empty($bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code']) ? $bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code'] : '';

                    if(!empty($bookingpress_staff_phone_no)){
                        $bookingpress_staff_phone_no = preg_replace('/^0/', '', $bookingpress_staff_phone_no);
                    }

                    if(!empty($bookingpress_staff_country_dial_code)){
                        $bookingpress_staff_phone_no = "+".$bookingpress_staff_country_dial_code."".$bookingpress_staff_phone_no;
                    }
                    if(!empty($bookingpress_staff_phone_no)){
                        $bookingpress_send_res = $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_staff_phone_no, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_staff_notification_name, 'employee');
                    }
                }
            }            
        }    

        function bookingpress_after_reschedule_appointment_func($appointment_id){

            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings;
            
            if( !empty(sanitize_text_field($_REQUEST['action'])) && sanitize_text_field($_REQUEST['action']) == 'bookingpress_save_appointment_booking' ){
				update_option('bookingpress_rescheduled_appointment_whatsapp_'.$appointment_id, '0');
			}

            if( !empty(sanitize_text_field($_REQUEST['action'])) && sanitize_text_field($_REQUEST['action']) != 'bookingpress_save_appointment_booking' ){
                $bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

            if(!empty($bookingpress_appointment_data)) {
                $bookingpress_notification_type = $bookingpress_staff_notification_name = 'Appointment Rescheduled';
                $bookingpress_configured_options = array();
                $is_send_whatsapp_notification = $this->bookingpress_send_whatsapp_notification($bookingpress_appointment_data);                
                if(!empty($is_send_whatsapp_notification) && $is_send_whatsapp_notification == 'true') { 
                    $bookingpress_phone_no = $this->bookingpress_get_mobile_number_field($bookingpress_appointment_data);  
                    $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_phone_no, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_notification_type, 'customer');
                }      

                $bookingpress_whatsapp_admin_number = '';
                $bookingpress_send_res = $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_whatsapp_admin_number, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_notification_type, 'employee');

                //Send staff email notification
                $bookingpress_staffmember_details = !empty($bookingpress_appointment_data['bookingpress_staff_member_details']) ? json_decode($bookingpress_appointment_data['bookingpress_staff_member_details'], TRUE) : array();

                if(!empty($bookingpress_staffmember_details)){
                    $bookingpress_staff_phone_no = !empty($bookingpress_staffmember_details['bookingpress_staffmember_phone']) ? $bookingpress_staffmember_details['bookingpress_staffmember_phone'] : '';

                    $bookingpress_staff_country_dial_code = !empty($bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code']) ? $bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code'] : '';

                    if(!empty($bookingpress_staff_phone_no)){
                        $bookingpress_staff_phone_no = preg_replace('/^0/', '', $bookingpress_staff_phone_no);
                    }

                    if(!empty($bookingpress_staff_country_dial_code)){
                        $bookingpress_staff_phone_no = "+".$bookingpress_staff_country_dial_code."".$bookingpress_staff_phone_no;
                    }
                    if(!empty($bookingpress_staff_phone_no)){
                        $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_staff_phone_no, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_staff_notification_name, 'employee');
                    }
                }

                }
            }
        }

        function bookingpress_after_update_appointment_func( $appointment_id ){

            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings;

            $bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

            $bpa_chk_rescheduled_appointment_id = get_option('bookingpress_rescheduled_appointment_whatsapp_'.$appointment_id ); //check rescheduled email sent or not

            if( isset($bpa_chk_rescheduled_appointment_id) && $bpa_chk_rescheduled_appointment_id === '0' ){

                if(!empty($bookingpress_appointment_data)) {
                    $bookingpress_notification_type = $bookingpress_staff_notification_name = 'Appointment Rescheduled';
                    $bookingpress_configured_options = array();
                    $is_send_whatsapp_notification = $this->bookingpress_send_whatsapp_notification($bookingpress_appointment_data);                
                    if(!empty($is_send_whatsapp_notification) && $is_send_whatsapp_notification == 'true') { 
                        $bookingpress_phone_no = $this->bookingpress_get_mobile_number_field($bookingpress_appointment_data);  
                        $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_phone_no, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_notification_type, 'customer');
                    }      

                    $bookingpress_whatsapp_admin_number = '';
                    $bookingpress_send_res = $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_whatsapp_admin_number, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_notification_type, 'employee');

                    //Send staff email notification
                    $bookingpress_staffmember_details = !empty($bookingpress_appointment_data['bookingpress_staff_member_details']) ? json_decode($bookingpress_appointment_data['bookingpress_staff_member_details'], TRUE) : array();

                    if(!empty($bookingpress_staffmember_details)){
                        $bookingpress_staff_phone_no = !empty($bookingpress_staffmember_details['bookingpress_staffmember_phone']) ? $bookingpress_staffmember_details['bookingpress_staffmember_phone'] : '';

                        $bookingpress_staff_country_dial_code = !empty($bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code']) ? $bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code'] : '';

                        if(!empty($bookingpress_staff_phone_no)){
                            $bookingpress_staff_phone_no = preg_replace('/^0/', '', $bookingpress_staff_phone_no);
                        }

                        if(!empty($bookingpress_staff_country_dial_code)){
                            $bookingpress_staff_phone_no = "+".$bookingpress_staff_country_dial_code."".$bookingpress_staff_phone_no;
                        }
                        if(!empty($bookingpress_staff_phone_no)){
                            $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_staff_phone_no, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_staff_notification_name, 'employee');
                        }
                    }

                }
                update_option('bookingpress_rescheduled_appointment_whatsapp_'.$appointment_id, 1); //rescheduled email sent update the option
            }
        }

        function bookingpress_send_custom_status_whatsapp_notification_func($appointment_id,$bookingpress_notification_type) {

            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings;            
            $bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

            if(!empty($bookingpress_appointment_data)) {
                $bookingpress_configured_options = array();
                $bookingpress_staff_notification_name = $bookingpress_notification_type = $bookingpress_notification_type;                            
                $is_send_whatsapp_notification = $this->bookingpress_send_whatsapp_notification($bookingpress_appointment_data);                
                if(!empty($is_send_whatsapp_notification) && $is_send_whatsapp_notification == 'true') {
                    $bookingpress_phone_no = $this->bookingpress_get_mobile_number_field($bookingpress_appointment_data);
                    $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_phone_no, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_notification_type, 'customer');
                }   

                $bookingpress_whatsapp_admin_number = '';
                $bookingpress_send_res = $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_whatsapp_admin_number, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_notification_type, 'employee');

                //Send staff email notification
                $bookingpress_staffmember_details = !empty($bookingpress_appointment_data['bookingpress_staff_member_details']) ? json_decode($bookingpress_appointment_data['bookingpress_staff_member_details'], TRUE) : array();

                if(!empty($bookingpress_staffmember_details)){
                    $bookingpress_staff_phone_no = !empty($bookingpress_staffmember_details['bookingpress_staffmember_phone']) ? $bookingpress_staffmember_details['bookingpress_staffmember_phone'] : '';

                    $bookingpress_staff_country_dial_code = !empty($bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code']) ? $bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code'] : '';

                    if(!empty($bookingpress_staff_phone_no)){
                        $bookingpress_staff_phone_no = preg_replace('/^0/', '', $bookingpress_staff_phone_no);
                    }

                    if(!empty($bookingpress_staff_country_dial_code)){
                        $bookingpress_staff_phone_no = "+".$bookingpress_staff_country_dial_code."".$bookingpress_staff_phone_no;
                    }
                    if(!empty($bookingpress_staff_phone_no)){
                        $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_staff_phone_no, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_staff_notification_name, 'employee');
                    }
                }
            }            

        }

        function bookingpress_after_cancel_appointment_func($appointment_id) {

            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings;            
            $bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

            if(!empty($bookingpress_appointment_data)) {
                $bookingpress_configured_options = array();
                $bookingpress_staff_notification_name = $bookingpress_notification_type = 'Appointment Canceled';                            
                $is_send_whatsapp_notification = $this->bookingpress_send_whatsapp_notification($bookingpress_appointment_data);                
                if(!empty($is_send_whatsapp_notification) && $is_send_whatsapp_notification == 'true') {
                    $bookingpress_phone_no = $this->bookingpress_get_mobile_number_field($bookingpress_appointment_data);
                    $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_phone_no, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_notification_type, 'customer');
                }   

                $bookingpress_whatsapp_admin_number = '';
                $bookingpress_send_res = $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_whatsapp_admin_number, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_notification_type, 'employee');

                //Send staff email notification
                $bookingpress_staffmember_details = !empty($bookingpress_appointment_data['bookingpress_staff_member_details']) ? json_decode($bookingpress_appointment_data['bookingpress_staff_member_details'], TRUE) : array();

                if(!empty($bookingpress_staffmember_details)){
                    $bookingpress_staff_phone_no = !empty($bookingpress_staffmember_details['bookingpress_staffmember_phone']) ? $bookingpress_staffmember_details['bookingpress_staffmember_phone'] : '';

                    $bookingpress_staff_country_dial_code = !empty($bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code']) ? $bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code'] : '';

                    if(!empty($bookingpress_staff_phone_no)){
                        $bookingpress_staff_phone_no = preg_replace('/^0/', '', $bookingpress_staff_phone_no);
                    }

                    if(!empty($bookingpress_staff_country_dial_code)){
                        $bookingpress_staff_phone_no = "+".$bookingpress_staff_country_dial_code."".$bookingpress_staff_phone_no;
                    }
                    if(!empty($bookingpress_staff_phone_no)){
                        $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_staff_phone_no, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_staff_notification_name, 'employee');
                    }
                }
            }
        }

        function bookingpress_after_refund_appointment_func($appointment_id) {
            global $wpdb, $BookingPress, $tbl_bookingpress_appointment_bookings;            
            $bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm

            if(!empty($bookingpress_appointment_data)) {
                $bookingpress_configured_options = array();
                $bookingpress_staff_notification_name = $bookingpress_notification_type = 'Refund Payment';
                $is_send_whatsapp_notification = $this->bookingpress_send_whatsapp_notification($bookingpress_appointment_data);                
                if(!empty($is_send_whatsapp_notification) && $is_send_whatsapp_notification == 'true') {
                    $bookingpress_phone_no = $this->bookingpress_get_mobile_number_field($bookingpress_appointment_data);
                    $bookingpress_send_res = $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_phone_no, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_notification_type, 'customer');
                }      

                $bookingpress_whatsapp_admin_number = '';
                $bookingpress_send_res = $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_whatsapp_admin_number, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_notification_type, 'employee');

                //Send staff email notification
                $bookingpress_staffmember_details = !empty($bookingpress_appointment_data['bookingpress_staff_member_details']) ? json_decode($bookingpress_appointment_data['bookingpress_staff_member_details'], TRUE) : array();

                if(!empty($bookingpress_staffmember_details)){
                    $bookingpress_staff_phone_no = !empty($bookingpress_staffmember_details['bookingpress_staffmember_phone']) ? $bookingpress_staffmember_details['bookingpress_staffmember_phone'] : '';

                    $bookingpress_staff_country_dial_code = !empty($bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code']) ? $bookingpress_staffmember_details['bookingpress_staffmember_country_dial_code'] : '';

                    if(!empty($bookingpress_staff_phone_no)){
                        $bookingpress_staff_phone_no = preg_replace('/^0/', '', $bookingpress_staff_phone_no);
                    }

                    if(!empty($bookingpress_staff_country_dial_code)){
                        $bookingpress_staff_phone_no = "+".$bookingpress_staff_country_dial_code."".$bookingpress_staff_phone_no;
                    }
                    if(!empty($bookingpress_staff_phone_no)){
                        $this->bookingpress_send_all_whatsapp_notification_function($bookingpress_staff_phone_no, '', 0, $appointment_id, $bookingpress_configured_options, $bookingpress_staff_notification_name, 'employee');
                    }
                }
            }
        }

        function bookingpress_get_appointment_advanced_field_data() {
            global $wpdb,$tbl_bookingpress_form_fields;
			$bookingpress_field_list_data = $wpdb->get_results( $wpdb->prepare( 'SELECT bookingpress_field_label,bookingpress_field_meta_key,bookingpress_field_is_default,bookingpress_form_field_name FROM ' . $tbl_bookingpress_form_fields . ' WHERE bookingpress_is_customer_field = %d AND ( bookingpress_field_type = %s OR bookingpress_field_type = %s ) order by bookingpress_form_field_id',0,'text','phone'), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared --Reason $tbl_bookingpress_form_fields is a table name. false alarm.            
            $bookingpress_field_final_list_data = array();
			if ( ! empty( $bookingpress_field_list_data ) ) {				
				foreach ( $bookingpress_field_list_data as $field_data ) {
                    $bookingpress_field_final_list_data[] = array(
                        'bookingpress_field_label' => $field_data['bookingpress_field_label'],
                        'bookingpress_field_meta_key' => $field_data['bookingpress_field_meta_key'],
                    );
				}
			}
			return $bookingpress_field_final_list_data;            
        }

        function bookingpress_admin_notices(){
            if(!is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')){
                echo "<div class='notice notice-warning'><p>" . esc_html__('Bookingpress - WhatsApp plugin requires Bookingpress Premium Plugin installed and active.', 'bookingpress-whatsapp
                ') . "</p></div>";
            }

            if( file_exists( WP_PLUGIN_DIR . '/bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' ) ){
                $bpa_pro_plugin_info = get_plugin_data( WP_PLUGIN_DIR . '/bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php' );
                $bpa_pro_plugin_version = $bpa_pro_plugin_info['Version'];

                if( version_compare( $bpa_pro_plugin_version, '2.8', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("It's Required to update the BookingPress Premium Plugin to version 2.8 or higher in order to use the BookingPress Whatsapp plugin", "bookingpress-whatsapp")."</p></div>";
                }
            }
        }

        function bookingpress_add_setting_dynamic_data_fields_func($bookingpress_dynamic_setting_data_fields) {
            global $BookingPress,$wpdb,$tbl_bookingpress_appointment_bookings;
            $bookingpress_dynamic_setting_data_fields['bookingpress_whatsapp_gateways'] = $this->bookingpress_whatsapp_gateway_list();
            $bookingpress_dynamic_setting_data_fields['notification_setting_form']['bookingpress_selected_whatsapp_gateway'] = 'select_whatsapp_gateway';
            $bookingpress_dynamic_setting_data_fields['notification_setting_form']['whatsapp_twilio_account_sid'] = '';
            $bookingpress_dynamic_setting_data_fields['notification_setting_form']['whatsapp_twilio_auth_token'] = '';
            $bookingpress_dynamic_setting_data_fields['notification_setting_form']['whatsapp_twilio_from_number'] = '';

            $bookingpress_dynamic_setting_data_fields['notification_setting_form']['whatsapp_business_phone_id'] = '';
            $bookingpress_dynamic_setting_data_fields['notification_setting_form']['whatsapp_business_account_id'] = '';
            $bookingpress_dynamic_setting_data_fields['notification_setting_form']['whatsapp_business_access_token'] = '';

            $bookingpress_dynamic_setting_data_fields['notification_setting_form']['show_twilio_freeform_msg'] = $BookingPress->bookingpress_get_settings('bookingpress_whatsapp_show_freeform_msg', 'notification_setting');
            $bookingpress_dynamic_setting_data_fields['notification_setting_form']['twilio_msg_type'] = $BookingPress->bookingpress_get_settings( 'bookingpress_whatsapp_twilio_msg_type', 'notification_setting' );
                        
            $bookingpress_whatsapp_form_fields_arr = $this->bookingpress_get_appointment_advanced_field_data();
            $bookingpress_dynamic_setting_data_fields['bookingpress_whatsapp_form_fields_data'] = $bookingpress_whatsapp_form_fields_arr; 
            $bookingpress_dynamic_setting_data_fields['is_display_send_whatsapp_loader'] = '0';            
            $bookingpress_dynamic_setting_data_fields['notification_setting_form']['bookingpress_whatsapp_selected_phone_number_field'] = '';          
            $bookingpress_dynamic_setting_data_fields['bookingpress_test_whatsapp_form'] = array(
                'whatsapp_test_to_number' => '',
                'whatsapp_test_to_msg' => '',
            );            
            $bookingpress_dynamic_setting_data_fields['bookingpress_test_whatsapp_rules'] = array(
                'whatsapp_test_to_number' => array(
                    array( 'required' => true, 'message' => esc_html__('This field is required', 'bookingpress-whatsapp'), 'trigger' => 'blur'  ),
                ),
                'whatsapp_test_to_msg' => array(
                    array( 'required' => true, 'message' => esc_html__('This field is required', 'bookingpress-whatsapp'), 'trigger' => 'blur'  ),
                ),
            );

            $bookingpress_dynamic_setting_data_fields['bookingpress_whatsapp_err_msg'] = '';
            $bookingpress_dynamic_setting_data_fields['bookingpress_whatsapp_success_msg'] = '';            
            $bookingpress_dynamic_setting_data_fields['debug_log_setting_form']['whatsapp_debug_logs'] = false;
            $bookingpress_dynamic_setting_data_fields['rules_notification']['whatsapp_twilio_account_sid'] = array(
                array(
                    'required' => true,
                    'message'  => __('Please enter account sid', 'bookingpress-whatsapp'),
                    'trigger'  => 'blur',
                )
            );
            $bookingpress_dynamic_setting_data_fields['rules_notification']['whatsapp_twilio_auth_token'] = array(
                array(
                    'required' => true,
                    'message'  => __('Please enter auth token', 'bookingpress-whatsapp'),
                    'trigger'  => 'blur',
                )
            );

            global $BookingPress;
            $show_free_form_msg = $BookingPress->bookingpress_get_settings('bookingpress_whatsapp_show_freeform_msg', 'notification_setting');
            $twilio_msg_type = $BookingPress->bookingpress_get_settings('bookingpress_whatsapp_twilio_msg_type', 'notification_setting');

            $from_number_message = esc_html__( 'Please enter from number', 'bookingpress-whatsapp' );
            if( 'template' == $twilio_msg_type ){
                $from_number_message = esc_html__( 'Please enter service id', 'bookingpress-whatsapp' );
            }
            $bookingpress_dynamic_setting_data_fields['rules_notification']['whatsapp_twilio_from_number'] = array(
                array(
                    'required' => true,
                    'message'  => $from_number_message,
                    'trigger'  => 'blur',
                )
            );

            $bookingpress_dynamic_setting_data_fields['rules_notification']['whatsapp_phone_number_id'] = array(
                array(
                    'required' => true,
                    'message' => esc_html__( 'Please enter Whatsapp Business Phone number id', 'bookingpress-whatsapp' ),
                    'trigger' => 'blur'
                )
            );

            $bookingpress_dynamic_setting_data_fields['rules_notification']['whatsapp_business_account_id'] = array(
                array(
                    'required' => true,
                    'message' => esc_html__( 'Please enter Whatsapp Business Account id', 'bookingpress-whatsapp' ),
                    'trigger' => 'blur'
                )
            );

            $bookingpress_dynamic_setting_data_fields['rules_notification']['whatsapp_business_access_token'] = array(
                array(
                    'required' => true,
                    'message' => esc_html__( 'Please enter Whatsapp Permanent Access Token', 'bookingpress-whatsapp' ),
                    'trigger' => 'blur'
                )
            );

            return $bookingpress_dynamic_setting_data_fields;
        }

        function bookingpress_add_integration_debug_logs_func($bookingpress_integration_debug_logs_arr){
            
            $bookingpress_integration_debug_logs_arr[] = array(
                'integration_name' => __('WhatsApp Debug Logs', 'bookingpress-whatsapp'),
                'integration_key' => 'whatsapp_debug_logs'
            );
            return $bookingpress_integration_debug_logs_arr;
        }

        function bookingpress_get_whatsapp_admin_number($bookingpress_notification_name){

            global $wpdb,$tbl_bookingpress_notifications,$BookingPress,$bookingpress_pro_staff_members;    

            $bookingpress_staffmember_module = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();
            $bookingpress_admin_phone = esc_html($BookingPress->bookingpress_get_settings('company_phone_number', 'company_setting'));

            if(!empty($bookingpress_notification_name) ){
                $bookingpress_whatsapp_notification_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_whatsapp_admin_number FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_receiver_type = %s", $bookingpress_notification_name, 'employee')); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

                $bookingpress_whatsapp_admin_number = $bookingpress_whatsapp_notification_data->bookingpress_whatsapp_admin_number;                    
                if(!empty($bookingpress_whatsapp_admin_number)){
                    $bookingpress_whatsapp_admin_number = preg_replace('/^0/', '', $bookingpress_whatsapp_admin_number);
                }
            }


            if( $bookingpress_notification_name == 'Package Order' || ( !($bookingpress_staffmember_module ) && !empty( $bookingpress_notification_name ) ) ){
                
                if( !empty( $bookingpress_whatsapp_admin_number ) && !empty( $bookingpress_admin_phone ) ){                       
                    $bookingpress_admin_phone = preg_replace('/^0/', '', $bookingpress_admin_phone);   
                    $bookingpress_admin_phone_arr = array( $bookingpress_whatsapp_admin_number, $bookingpress_admin_phone );
                    $bookingpress_whatsapp_admin_number = implode( ',',$bookingpress_admin_phone_arr );                    
                } else {
                    if(!empty($bookingpress_admin_phone)){
                        $bookingpress_whatsapp_admin_number = preg_replace('/^0/', '', $bookingpress_admin_phone);
                    }
                }
            }
            return $bookingpress_whatsapp_admin_number;
        }

        function bookingpress_send_all_whatsapp_notification_function($to_number, $to_message = '', $is_test = 0, $appointment_id = 0, $bookingpress_configured_options = array(), $notification_type ='Appointment Approved', $notification_receiver_type = 'customer') {

            global $BookingPress;
            $bookingpress_send_all_whatsapp_arr[] = $notification_type;
            $bookingpress_send_all_whatsapp_arr = apply_filters('bookingpress_send_all_custom_email_notifications',$bookingpress_send_all_whatsapp_arr,$notification_receiver_type,$appointment_id,'whatsapp');

            // Send customer sms
            foreach($bookingpress_send_all_whatsapp_arr as $key => $email_notification_name) {                
                if (! empty($email_notification_name) ) {

                    if($notification_receiver_type == 'employee' && $to_number == '') {
                        
                        $bookingpress_whatsapp_number = $this->bookingpress_get_whatsapp_admin_number($email_notification_name);

                        if(!empty($bookingpress_whatsapp_number)) {

                            $bookingpress_whatsapp_number_arr =  explode( ',', $bookingpress_whatsapp_number ); 
                            if(is_array($bookingpress_whatsapp_number_arr) && !empty($bookingpress_whatsapp_number_arr)){
                                foreach($bookingpress_whatsapp_number_arr as $whatsapp_no){
                                    $this->bookingpress_send_whatsapp_function($whatsapp_no, $to_message, $is_test, $appointment_id, $bookingpress_configured_options, $email_notification_name, $notification_receiver_type);
                                }                                
                            }

                        }
                    }else{
                        $this->bookingpress_send_whatsapp_function($to_number, $to_message, $is_test, $appointment_id, $bookingpress_configured_options, $email_notification_name, $notification_receiver_type);
                    }
    
                }
            }
        }

        function bookingpress_send_whatsapp_function($to_number, $to_message = '', $is_test = 0, $appointment_id = 0, $bookingpress_configured_options = array(), $notification_type ='Appointment Approved', $notification_receiver_type = 'customer') {

            global $wpdb, $BookingPress, $tbl_bookingpress_notifications, $tbl_bookingpress_appointment_bookings, $bookingpress_debug_integration_log_id, $tbl_bookingpress_settings;            

            
            $bookingpress_send_whatsapp_notification = 0;            
            $bookingpress_return_res = array(
                'return_type' => 'error',
                'return_res' => __('Something went wrong while sending WhatsApp Message', 'bookingpress-whatsapp'),
            );
            $bookingpress_debug_log_data = array(
                'to_number' => $to_number,
                'to_message' => $to_message,
                'is_test' => $is_test,
                'appointment_id' => $appointment_id,
                'configure_options' => $bookingpress_configured_options,
                'notification_type' => $notification_type,
                'notification_receiver_type' => $notification_receiver_type
            );
            
            do_action('bookingpress_integration_log_entry', 'whatsapp_debug_logs', '', 'Send WHATSAPP Params', 'Core Whatsapp Sending Function', $bookingpress_debug_log_data, $bookingpress_debug_integration_log_id);

            if( $is_test == 0 ) {
                $bookingpress_configured_options = array(
                    'bookingpress_selected_whatsapp_gateway' => $BookingPress->bookingpress_get_settings('bookingpress_selected_whatsapp_gateway', 'notification_setting')
                );
                $bookingpress_whatsapp_data = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_whatsapp_notification_message,bookingpress_wp_template_placeholder,bookingpress_send_whatsapp_notification,bookingpress_wp_selected_template FROM {$tbl_bookingpress_notifications} WHERE bookingpress_notification_name = %s AND bookingpress_notification_receiver_type = %s ORDER BY bookingpress_notification_id DESC", $notification_type, $notification_receiver_type), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

                
                if( !empty($notification_type) && $notification_type == 'Package Order'){

                    global $tbl_bookingpress_package_bookings;

                    $bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_package_bookings} WHERE bookingpress_package_booking_id = %d", $appointment_id), ARRAY_A);  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_package_bookings is a table name. false alarm

                    $bookingpress_appointment_data['bookingpress_package_payment_id'] = $bookingpress_appointment_data['bookingpress_payment_id'];

                } else {

                    //Modify WhatsApp Content
                    $bookingpress_appointment_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_appointment_booking_id = %d", $appointment_id), ARRAY_A);  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_appointment_bookings is a table name. false alarm
                }

                /* New Filter Added */
                $bookingpress_whatsapp_data = apply_filters( 'bookingpress_replace_notification_content_language_wise', $bookingpress_whatsapp_data, array('bookingpress_whatsapp_notification_message'), $notification_receiver_type,$notification_type, $bookingpress_appointment_data);

                if(!empty($bookingpress_whatsapp_data['bookingpress_send_whatsapp_notification'])){
                    $bookingpress_send_whatsapp_notification = $bookingpress_whatsapp_data['bookingpress_send_whatsapp_notification'];
                }                
                if(!empty($bookingpress_whatsapp_data['bookingpress_whatsapp_notification_message'])){
                    $to_message = stripslashes_deep($bookingpress_whatsapp_data['bookingpress_whatsapp_notification_message']);
                }
                if( !empty( $bookingpress_whatsapp_data['bookingpress_wp_template_placeholder'])){
                    $wp_template_placeholder = $bookingpress_whatsapp_data['bookingpress_wp_template_placeholder'];
					
                    $wp_template_placeholder = apply_filters( 'bookingpress_modify_email_notification_content', $wp_template_placeholder, $bookingpress_appointment_data,$notification_type,$notification_receiver_type ); 
					
					$bookingpress_return_res['wp_template_placeholder'] = $wp_template_placeholder;

                    if( !empty($notification_type) && $notification_type == 'Package Order'){
                    
                        if( is_plugin_active('bookingpress-package/bookingpress-package.php') ){
                            global $bookingpress_package_order;
                            $bookingpress_get_messasge_data = $bookingpress_package_order->bookingpress_replace_package_notification_data_new('', $wp_template_placeholder, $bookingpress_appointment_data,$notification_receiver_type);
                            $wp_template_placeholder = (!empty($bookingpress_get_messasge_data['template_content_body']))?$bookingpress_get_messasge_data['template_content_body']:'';
                        }
                    }
                    
                    $wp_template_placeholder_arr = !empty( $wp_template_placeholder ) ? json_decode( $wp_template_placeholder,true ) : array();                    
                    
                    $bpa_components_data = array();
                    $x = 0;
                    foreach( $wp_template_placeholder_arr as $key => $val ){
                        if( $x > 0 ){
                            $bpa_component_placeholde_arr = array(
                                'type' => 'text',
                                'text' => $val,
                            );
                            array_push( $bpa_components_data, $bpa_component_placeholde_arr );
                        }
                        $x++;
                    }
                }
				
				$bookingpress_return_res['bpa_components_data'] = json_encode( $bpa_components_data );
                
                if( !empty( $bookingpress_whatsapp_data['bookingpress_wp_selected_template'])){
                    $wp_selected_template = $bookingpress_whatsapp_data['bookingpress_wp_selected_template'];
                }

                if(!empty($bookingpress_appointment_data)){
                    $bookingpress_appointment_data['notification_language_compare_field'] = 'bookingpress_whatsapp_notification_message';
                }

                $to_message = apply_filters( 'bookingpress_modify_email_notification_content', $to_message, $bookingpress_appointment_data,$notification_type,$notification_receiver_type );
                
                if( !empty($notification_type) && $notification_type == 'Package Order'){
                    
                    if( is_plugin_active('bookingpress-package/bookingpress-package.php') ){
                        global $bookingpress_package_order;
                        $bookingpress_get_messasge_data = $bookingpress_package_order->bookingpress_replace_package_notification_data_new('', $to_message, $bookingpress_appointment_data,$notification_receiver_type);
                        $to_message = (!empty($bookingpress_get_messasge_data['template_content_body']))?$bookingpress_get_messasge_data['template_content_body']:'';
                    }
                }
                
            }             

            $bookingpress_selected_whatsapp_gateway = !empty($bookingpress_configured_options['bookingpress_selected_whatsapp_gateway']) ? $bookingpress_configured_options['bookingpress_selected_whatsapp_gateway'] : '';

            if(!empty($bookingpress_selected_whatsapp_gateway) && !empty($to_number) && ( $bookingpress_send_whatsapp_notification == 1 || $is_test ==  1 )) {               
                if($bookingpress_selected_whatsapp_gateway == "Twilio") {

                    $bookingpress_account_sid = !empty($bookingpress_configured_options['whatsapp_twilio_account_sid']) ? $bookingpress_configured_options['whatsapp_twilio_account_sid'] : $BookingPress->bookingpress_get_settings('whatsapp_twilio_account_sid', 'notification_setting');
                    $bookingpress_auth_token = !empty($bookingpress_configured_options['whatsapp_twilio_auth_token']) ? $bookingpress_configured_options['whatsapp_twilio_auth_token'] : $BookingPress->bookingpress_get_settings('whatsapp_twilio_auth_token', 'notification_setting');
                    $bookingpress_from_number = !empty($bookingpress_configured_options['whatsapp_twilio_from_number']) ? $bookingpress_configured_options['whatsapp_twilio_from_number'] : $BookingPress->bookingpress_get_settings('whatsapp_twilio_from_number', 'notification_setting');                    
                    $to_number = str_replace(' ', '', $to_number);
                    
                    $bpa_msg_type = $BookingPress->bookingpress_get_settings( 'bookingpress_whatsapp_twilio_msg_type', 'notification_setting' );

                    $twilio_url = 'https://api.twilio.com/2010-04-01/Accounts/'.$bookingpress_account_sid.'/Messages.json';
					$bookingpress_return_res['twilio_msg_type'] = $bpa_msg_type;
                    if( 'freeform' != $bpa_msg_type ){

                        $templates_data = $BookingPress->bookingpress_get_settings('bpa_wp_twilio_template_data', 'notification_setting');
                        $templates_data = json_decode( $templates_data, true );

                        $template_id = $wp_selected_template;

                        $content_variables = array();

						$content_key = 1;
                        foreach( $bpa_components_data as $placeholder_key => $placeholder_value ){
							$content_variables[ $content_key ] = $placeholder_value['text'];
							$content_key++;
                        }
                        

                        $args = array(
                            'timetout' => 4500,
                            'body' => array(
                                'From' => $bookingpress_from_number,
                                'To' => 'whatsapp:'. $to_number,
                                'ContentSid' => $template_id,
                                'ContentVariables' => json_encode( $content_variables ),
                            ),
                            'headers' => array(
                                'Content-Type' => 'application/x-www-form-urlencoded',
                                'Authorization' =>  'Basic '. base64_encode( $bookingpress_account_sid.':'.$bookingpress_auth_token )
                            )
                        );

                        $whatsapp_response = wp_remote_post(
                            $twilio_url,
                            $args
                        );

                        $whatsapp_response = wp_remote_retrieve_body( $whatsapp_response );
                        $whatsapp_response_arr = json_decode( $whatsapp_response, true );
						
						$bookingpress_return_res['submitted_args'] = json_encode( $args );
						$bookingpress_return_res['return_res'] = $whatsapp_response;


                    } else {
                        
                        
                        $args = array(
                            'timetout' => 4500,
                            'body' => array(
                                'From' => 'whatsapp:'.$bookingpress_from_number,
                                'To' => 'whatsapp:'. $to_number,
                                'Body' => $to_message
                            ),
                            'headers' => array(
                                'Content-Type' => 'application/x-www-form-urlencoded',
                                'Authorization' =>  'Basic '. base64_encode( $bookingpress_account_sid.':'.$bookingpress_auth_token )
                            )
                        );
    
                        $whatsapp_response = wp_remote_post(
                            $twilio_url,
                            $args
                        );  
    
                        $whatsapp_response = wp_remote_retrieve_body( $whatsapp_response );
                        $whatsapp_response_arr = json_decode( $whatsapp_response, true );
    
    
                        $respnose_uri = !empty($whatsapp_response_arr['uri']) ? $whatsapp_response_arr['uri'] : '';
                        $check_status_uri = 'https://api.twilio.com'. $respnose_uri;
                        $status_response = wp_remote_get(
                            $check_status_uri,
                            array(
                                'timeout' => 4500,
                                'headers' => array(
                                    'Authorization' =>  'Basic '. base64_encode( $bookingpress_account_sid.':'.$bookingpress_auth_token )
                                )
                            )
                        );
    
                        $whatsapp_status_response = wp_remote_retrieve_body( $status_response );
                        $whatsapp_formatted_response = json_decode( $whatsapp_status_response, true );
    
                        $whatsapp_status = '';
                        if( !empty($whatsapp_formatted_response) ) {
                            $whatsapp_status = $whatsapp_formatted_response['status']; //sent or failed
                            $whatsapp_error_code = $whatsapp_formatted_response['error_code'];
                            if( !empty($whatsapp_error_code )){
                                $bookingpress_return_res['return_type'] = 'error';
                                $bookingpress_return_res['return_res'] = __('Message','bookingpress-whatsapp').' '.$whatsapp_status.' '.__('with error code','bookingpress-whatsapp').' '.$whatsapp_error_code;   
                            } else {                            
                                $bookingpress_return_res['return_type'] = 'success';
                                $bookingpress_return_res['return_res'] = __('WhatsApp Message Sent Successfully', 'bookingpress-whatsapp');
                            }
    
                        } else {
                            $bookingpress_return_res['return_type'] = 'error';
                            $bookingpress_return_res['return_res'] = __('Something went wrong while sending the message please check your Twilio configuration.' , 'bookingpress-whatsapp');
                        }
                    }

                } else if( 'Whatsapp Business' == $bookingpress_selected_whatsapp_gateway ){

                    $bookingpress_wa_business_phone_id = !empty( $bookingpress_configured_options['whatsapp_phone_number_id'] ) ? $bookingpress_configured_options['whatsapp_phone_number_id'] : $BookingPress->bookingpress_get_settings( 'whatsapp_phone_number_id', 'notification_setting' );
                    $bookingpress_wa_business_account_id = !empty( $bookingpress_configured_options['whatsapp_business_account_id'] ) ? $bookingpress_configured_options['whatsapp_business_account_id'] : $BookingPress->bookingpress_get_settings( 'whatsapp_business_account_id', 'notification_setting' );
                    $bookingpress_wa_business_access_token = !empty( $bookingpress_configured_options['whatsapp_business_access_token'] ) ? $bookingpress_configured_options['whatsapp_business_access_token'] : $BookingPress->bookingpress_get_settings( 'whatsapp_business_access_token', 'notification_setting' );
                    $bookingpress_wp_template_data = !empty( $bookingpress_configured_options['bpa_wp_template_data'] ) ? $bookingpress_configured_options['bpa_wp_template_data'] : $BookingPress->bookingpress_get_settings( 'bpa_wp_template_data', 'notification_setting' );

                    $bookingpress_wp_template_data = !empty( $bookingpress_wp_template_data ) ? json_decode( $bookingpress_wp_template_data ) : array();

                    $bpa_language_code = 'en_US';
                    $wp_template_name = '';

                    if( !empty( $bookingpress_wp_template_data)){
                        foreach( $bookingpress_wp_template_data as $wp_tempalte_key => $wp_template_val ){
                            if( $wp_template_val->template_id ==  $wp_selected_template ){
                                $bpa_language_code = $wp_template_val->language;
                                $wp_template_name = $wp_template_val->template_name;
                            }
                        }
                    }

                    $to_number = str_replace( ' ', '', $to_number );
                    $whatsapp_url = 'https://graph.facebook.com/v19.0/'. $bookingpress_wa_business_phone_id.'/messages';

                    $args = array(
                        'timeout' => 45,
                        'headers' => array(
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer ' . $bookingpress_wa_business_access_token
                        ),
                        'body' => json_encode(
                            array(
                                'messaging_product' => 'whatsapp',
                                'recipient_type' => 'individual',
                                'to' => $to_number,
                                'type' => 'template',
                                'template' => array(
                                    'name' => $wp_template_name,
                                    'language'=> array(
                                        "code"=> $bpa_language_code,
                                    ),
                                    'components' => [
                                        array(
                                            'type' => 'body',
                                            'parameters' => 
                                            $bpa_components_data,
                                        ),
                                    ]
                                ),
                            )
                        )
                    );

                    $whatsapp_response = wp_remote_post(
                        $whatsapp_url,
                        $args
                    );

                    if( is_wp_error( $whatsapp_response ) ){
                        $bookingpress_return_res['return_type'] = 'error';
                        $bookingpress_return_res['return_res'] = $whatsapp_response->get_error_message();
                    } else {   
                        $whatsapp_response = wp_remote_retrieve_body( $whatsapp_response );
                        $whatsapp_response_arr = json_decode( $whatsapp_response, true );

                        if( !empty( $whatsapp_response_arr['error'] ) ){
                            $bookingpress_return_res['return_type'] = 'error';
                            $bookingpress_return_res['return_res'] = $whatsapp_response_arr['error']['message'];
                            $bookingpress_return_res['return_details'] = $whatsapp_response_arr['error'];
                        } else {
                            $bookingpress_return_res['return_type'] = 'success';
                            $bookingpress_return_res['return_res'] = __('WhatsApp Message Sent Successfully', 'bookingpress-whatsapp');
                        }
                    }

                }
                $bookingpress_debug_log_data = array(
                    'to_number' => $to_number,
                    'to_message' => $to_message,
                    'is_test' => $is_test,
                    'appointment_id' => $appointment_id,
                    'configure_options' => $bookingpress_configured_options,
                    'notification_type' => $notification_type,
                    'notification_receiver_type' => $notification_receiver_type,
                    'whatsapp_notification_res' => $bookingpress_return_res                    
                );                

                $bookingpress_return_res['to_number'] = $to_number;
                $bookingpress_return_res['to_message'] = $to_message;
                $bookingpress_return_res['configure_options'] = $bookingpress_configured_options;

                do_action('bookingpress_integration_log_entry', 'whatsapp_debug_logs', '', 'WhatsApp Addon', 'Core WhatsApp Sending Function', $bookingpress_debug_log_data, $bookingpress_debug_integration_log_id);
            }

            return $bookingpress_return_res;
        }

        function bookingpress_refresh_whatsapp_template_list_callback(){
            global $wpdb, $BookingPress;
            $response = array();

            $bpa_check_authorization = $this->bpa_check_authentication( 'bpa_send_test_whatspp_msg', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-whatsapp');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-whatsapp');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $is_show_freeform = $BookingPress->bookingpress_get_settings( 'bookingpress_whatsapp_show_freeform_msg', 'notification_setting' );
            $twilio_msg_type = $BookingPress->bookingpress_get_settings( 'bookingpress_whatsapp_twilio_msg_type', 'notification_setting' );
            $selected_wp_gateway = $BookingPress->bookingpress_get_settings('bookingpress_selected_whatsapp_gateway', 'notification_setting');

            if( 'Twilio' == $selected_wp_gateway && 'freeform' != $twilio_msg_type ){
                $whatsapp_twilio_account_sid = $BookingPress->bookingpress_get_settings('whatsapp_twilio_account_sid', 'notification_setting');
                $whatsapp_twilio_auth_token = $BookingPress->bookingpress_get_settings('whatsapp_twilio_auth_token', 'notification_setting');
                $whatsapp_twilio_from_number = $BookingPress->bookingpress_get_settings('whatsapp_twilio_from_number', 'notification_setting');

                $twilio_url = 'https://content.twilio.com/v1/ContentAndApprovals?PageSize=100';
                $args = array(
                    'timetout' => 4500,
                    'headers' => array(
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Authorization' =>  'Basic '. base64_encode( $whatsapp_twilio_account_sid.':'.$whatsapp_twilio_auth_token )
                    )
                );

                $twilio_get_contents = wp_remote_get( $twilio_url, $args );

                if( is_wp_error( $twilio_get_contents ) ){
                    /** handle wp error here */
                } else {
                    $twilio_content_data = wp_remote_retrieve_body( $twilio_get_contents );

                    $content_data = json_decode( $twilio_content_data, true );
                    
                    if( empty( $content_data['contents'] ) ){
                        /** handle error message */
                    } else {
                        $contents = $content_data['contents'];

                        $bpa_wp_template_data = array();
                        foreach( $contents as $twilio_content_details ){
                            $status = $twilio_content_details['approval_requests']['status'];

                            if( 'approved' == $status ){
                                $template_id = $twilio_content_details['sid'];
                                $template_name = $twilio_content_details['approval_requests']['name'];
                                $template_body = !empty( $twilio_content_details['types']['twilio/text']['body'] ) ? $twilio_content_details['types']['twilio/text']['body'] : '';
                                $template_language = $twilio_content_details['language'];

                                if( !empty( $template_body ) ){
                                    $bpa_wp_template_data[] = array(
                                        'template_id' => $template_id,
                                        'template_name' => $template_name,
                                        'template_label' => $template_name .' ('.$template_language.')',
                                        'template_body' => $template_body,
                                        'language' => $template_language
                                    );
                                }
                            }
                        }
                        
                        $bpa_wp_template_data = json_encode( $bpa_wp_template_data );

                        $BookingPress->bookingpress_update_settings('bpa_wp_template_data', 'notification_setting', $bpa_wp_template_data );

                        $response['variant'] = 'success';
                        $response['title'] = esc_html__( 'Success', 'bookingpress-whatsapp');
                        $response['template_list'] = $bpa_wp_template_data;
                        $response['msg'] = esc_html__( 'Template list has been refreshed', 'bookingpress-whatsapp');

                    }

                }
            } else if( 'Whatsapp Business' == $selected_wp_gateway ){
                
                $whatsapp_phone_id = $BookingPress->bookingpress_get_settings( 'whatsapp_phone_number_id', 'notification_setting' );
                $whatsapp_business_id = $BookingPress->bookingpress_get_settings( 'whatsapp_business_account_id', 'notification_setting' );
                $whatsapp_permanent_token = $BookingPress->bookingpress_get_settings( 'whatsapp_business_access_token', 'notification_setting' );

                $whatsapp_url = 'https://graph.facebook.com/v19.0/'. $whatsapp_business_id .'/message_templates';
                $args = array(
                    'timeout' => 45,
                    'method' => 'GET',
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $whatsapp_permanent_token
                    ),
                    'body' =>array(
                        'fields' => 'name,id,status,components,language',
                        'limit' => '100',
                    ),
                );

                $whatsapp_response = wp_remote_get(
                    $whatsapp_url,
                    $args
                );

                if( is_wp_error( $whatsapp_response ) ){
                    $bookingpress_return_res['return_type'] = 'error';
                    $bookingpress_return_res['return_res'] = $whatsapp_response->get_error_message();
                } else {  
                    if( $whatsapp_response['response']['code'] == 200 ){
                        
                        $whatsapp_response = wp_remote_retrieve_body( $whatsapp_response );
                        $whatsapp_response_arr = json_decode( $whatsapp_response, true );


                        $bpa_wp_template_data = array();
                        foreach( $whatsapp_response_arr['data'] as $key=>$val ){
                            $bpa_components = $val['components'];
                            foreach( $bpa_components as $bpa_components_key=>$bpa_components_val ){
                                
                                if( $bpa_components_val['type'] == 'BODY' ){
                                    if( $val['status'] == 'APPROVED'){
                                        $bpa_wp_template_data[] = array(
                                            'template_id' => $val['id'],
                                            'template_name' => $val['name'],
                                            'template_label' => $val['name'] .' ('.$val['language'].')',
                                            'template_body' => $bpa_components_val['text'],
                                            'language' => $val['language'],
                                        );
                                    }
                                }
                            }
                        }
                        $bpa_wp_template_data = json_encode( $bpa_wp_template_data );

                        $BookingPress->bookingpress_update_settings('bpa_wp_template_data', 'notification_setting', $bpa_wp_template_data );

                        $response['variant'] = 'success';
                        $response['title'] = esc_html__( 'Success', 'bookingpress-whatsapp');
                        $response['template_list'] = $bpa_wp_template_data;
                        $response['msg'] = esc_html__( 'Template list has been refreshed', 'bookingpress-whatsapp');
                    }
                }
            }
            echo json_encode( $response );
            die;
        }

        function bookingpress_send_test_whatsapp_func(){
            global $wpdb;
			$response              = array();
			$bpa_check_authorization = $this->bpa_check_authentication( 'bpa_send_test_whatspp_msg', true, 'bpa_wp_nonce' );
            
            if( preg_match( '/error/', $bpa_check_authorization ) ){
                $bpa_auth_error = explode( '^|^', $bpa_check_authorization );
                $bpa_error_msg = !empty( $bpa_auth_error[1] ) ? $bpa_auth_error[1] : esc_html__( 'Sorry. Something went wrong while processing the request', 'bookingpress-whatsapp');

                $response['variant'] = 'error';
                $response['title'] = esc_html__( 'Error', 'bookingpress-whatsapp');
                $response['msg'] = $bpa_error_msg;

                wp_send_json( $response );
                die;
            }

            $bookingpress_to_number = !empty($_POST['bookingpress_test_to_number']) ? esc_html($_POST['bookingpress_test_to_number']) : ''; //phpcs:ignore
            $bookingpress_to_msg = !empty($_POST['bookingpress_test_message']) ? $_POST['bookingpress_test_message'] : ''; //phpcs:ignore

            $bookingpress_posted_fields_data = !empty($_POST['bookingpress_posted_fields_data']) ? $_POST['bookingpress_posted_fields_data'] : array(); //phpcs:ignore

           $bookingpress_send_whatsapp_res = $this->bookingpress_send_whatsapp_function($bookingpress_to_number, $bookingpress_to_msg, 1, 0, $bookingpress_posted_fields_data);

            echo json_encode($bookingpress_send_whatsapp_res);
            exit;
        }

        
        function bookingpress_check_cron_whatsapp_notification_sent_or_not( $bookingpress_email_notification_id, $bookingpress_customer_id, $bookingpress_email_address, $bookingpress_appointment_id, $bookingpress_appointment_date, $bookingpress_appointment_time, $bookingpress_appointment_status, $bookingpress_hook_name, $bookingpress_staffmember_id = 0, $bookingpress_staffmember_email = '' ) {
			global $wpdb, $tbl_bookingpress_cron_email_notifications_logs,$BookingPress;

			if(empty($bookingpress_staffmember_id)) {                
				$bookingpress_is_record_exists = $wpdb->get_var( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_cron_email_notifications_logs} WHERE bookingpress_email_notification_id = %d AND bookingpress_customer_id = %d AND bookingpress_email_address = %s AND bookingpress_appointment_id = %d AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_status = %s AND bookingpress_email_cron_hook_name = %s AND bookingpress_staffmember_email = %s AND bookingpress_notification_type = %s", $bookingpress_email_notification_id, $bookingpress_customer_id, $bookingpress_email_address, $bookingpress_appointment_id, $bookingpress_appointment_date, $bookingpress_appointment_time, $bookingpress_appointment_status, $bookingpress_hook_name, $bookingpress_staffmember_email,'whatsapp' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_cron_email_notifications_logs is a table name. false alarm                
			}else if(!empty($bookingpress_staffmember_id)) {
				$bookingpress_is_record_exists = $wpdb->get_var( $wpdb->prepare( "SELECT * FROM {$tbl_bookingpress_cron_email_notifications_logs} WHERE bookingpress_email_notification_id = %d AND bookingpress_customer_id = %d AND bookingpress_email_address = %s AND bookingpress_appointment_id = %d AND bookingpress_appointment_date = %s AND bookingpress_appointment_time = %s AND bookingpress_appointment_status = %s AND bookingpress_email_cron_hook_name = %s AND bookingpress_staffmember_id = %d AND bookingpress_staffmember_email = %s AND bookingpress_notification_type = %s", $bookingpress_email_notification_id, $bookingpress_customer_id, $bookingpress_email_address, $bookingpress_appointment_id, $bookingpress_appointment_date, $bookingpress_appointment_time, $bookingpress_appointment_status, $bookingpress_hook_name, $bookingpress_staffmember_id, $bookingpress_staffmember_email,'whatsapp' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_cron_email_notifications_logs is a table name. false alarm
			}
			return $bookingpress_is_record_exists;
		}


        function bookingpress_add_setting_dynamic_vue_methods_func(){
            ?>
            bookingpress_send_test_whatsapp() {
                const vm = this
                vm.$refs['bookingpress_test_whatsapp_form'].validate((valid) => {
                    if(valid) {
                        vm.is_display_send_whatsapp_loader = '1'
                        var postdata = {}
                        postdata.action = 'bookingpress_send_test_whatsapp'
                        postdata.bookingpress_posted_fields_data = vm.notification_setting_form
                        postdata.bookingpress_test_to_number = vm.bookingpress_test_whatsapp_form.whatsapp_test_to_number
                        postdata.bookingpress_test_message = vm.bookingpress_test_whatsapp_form.whatsapp_test_to_msg
                        postdata._wpnonce = '<?php echo esc_html(wp_create_nonce( 'bpa_wp_nonce' )); ?>'
                        axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
                        .then( function (response) {
                            vm.is_display_send_whatsapp_loader = '0'
                            vm.bookingpress_whatsapp_err_msg = ''
                            vm.bookingpress_whatsapp_success_msg = ''
                            if(response.data.return_type != 'success'){
                                vm.bookingpress_whatsapp_err_msg = response.data.return_res
                            }else{
                                vm.bookingpress_whatsapp_success_msg = response.data.return_res
                            }
                        }.bind(this) )
                        .catch( function (error) {
                            console.log(error)
                        });
                    }
                });    
            },
            bookingpress_reset_setting_validate(){
                const vm = this;
                if('undefined' != typeof vm.$refs.notification_setting_form){
                    vm.$refs.notification_setting_form.clearValidate();
                }
            },
            bookingpress_reset_twilio_service_from_number(){
				const vm = this;
				vm.notification_setting_form.whatsapp_twilio_from_number = '';

                let twilio_msg_type = vm.notification_setting_form.bookingpress_whatsapp_twilio_msg_type;
                let rule_msg = '<?php esc_html_e('Please enter from number', 'bookingpress-whatsapp'); ?>';
                if( 'template' == twilio_msg_type ){
                    rule_msg = '<?php esc_html_e('Please enter service id', 'bookingpress-whatsapp'); ?>';
                }

                vm.rules_notification.whatsapp_twilio_from_number[0].message = rule_msg;

                if('undefined' != typeof vm.$refs.notification_setting_form){
                    vm.$refs.notification_setting_form.clearValidate();
                }
			},
            <?php
        }

        function bookingpress_email_notification_get_data_func(){            
            ?>
            vm.bookingpress_whatsapp_notification_msg = bookingpress_return_notification_data.bookingpress_whatsapp_notification_message
            vm.bookingpress_whatsapp_admin_number = bookingpress_return_notification_data.bookingpress_whatsapp_admin_number
            vm.bookingpress_wp_selected_template = bookingpress_return_notification_data.bookingpress_wp_selected_template
            if( bookingpress_return_notification_data.bookingpress_whatsapp_dynamic_data != '' ){
                vm.bookingpress_whatsapp_dynamic_data =  parseInt(bookingpress_return_notification_data.bookingpress_whatsapp_dynamic_data)
            } else {
                vm.bookingpress_whatsapp_dynamic_data =  0
            }
            if ( bookingpress_return_notification_data.bookingpress_wp_template_placeholder != 'undefined' && bookingpress_return_notification_data.bookingpress_wp_template_placeholder != ''){
                vm.bookingpress_wp_placeholder_text = JSON.parse(bookingpress_return_notification_data.bookingpress_wp_template_placeholder)
            }

            if(bookingpress_return_notification_data.bookingpress_send_whatsapp_notification != 'undefined') {
                vm.bookingpress_send_whatsapp_notification = bookingpress_return_notification_data.bookingpress_send_whatsapp_notification
            }
            <?php
        }

        function bookingpress_get_notifiacation_data_filter_func($bookingpress_exist_record_data) {
            if(isset($bookingpress_exist_record_data['bookingpress_send_whatsapp_notification']) ) {
                $bookingpress_exist_record_data['bookingpress_send_whatsapp_notification'] = $bookingpress_exist_record_data['bookingpress_send_whatsapp_notification'] == '1' ? true : false;
                
                
                $bookingpress_exist_record_data['bookingpress_whatsapp_dynamic_data'] = !empty( $bookingpress_exist_record_data['bookingpress_whatsapp_dynamic_data'] ) ? $bookingpress_exist_record_data['bookingpress_whatsapp_dynamic_data'] : 0;
            }
            return $bookingpress_exist_record_data;
        }

        function bookingpress_save_email_notification_data_filter_func($bookingpress_database_modify_data, $posted_data){

            global $bookingpress_global_options;

 
            if(!empty($posted_data['whatsapp_notification_data'])){

                $bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();    
                $bookingpress_allow_tag = json_decode( $bookingpress_global_options_arr['allowed_html'], true );
                $bookingpress_whatsapp_notification_msg = ! empty( $posted_data['whatsapp_notification_data'] ) ? wp_kses( stripslashes_deep($posted_data['whatsapp_notification_data']), $bookingpress_allow_tag ) : '';
                $bookingpress_database_modify_data['bookingpress_whatsapp_notification_message'] = $bookingpress_whatsapp_notification_msg;
                $bookingpress_database_modify_data['bookingpress_wp_selected_template'] = !empty( $posted_data['bookingpress_wp_selected_template']) ? sanitize_text_field($posted_data['bookingpress_wp_selected_template']) : '';
                
                //$bookingpress_database_modify_data['bookingpress_whatsapp_dynamic_data'] = !empty( $posted_data['bookingpress_whatsapp_dynamic_data']) ? intval($posted_data['bookingpress_whatsapp_dynamic_data']) : 0;

                $bookingpress_database_modify_data['bookingpress_whatsapp_dynamic_data'] = 0;
                preg_match_all( '/\{\{([\d]+)\}\}/', $bookingpress_whatsapp_notification_msg, $matches );
                if( !empty( $matches[0] ) ){
                    $bookingpress_database_modify_data['bookingpress_whatsapp_dynamic_data'] = count( $matches[0] );
                }



                $bookingpress_database_modify_data['bookingpress_wp_template_placeholder'] = !empty( $posted_data['bookingpress_wp_placeholder_text']) ? json_encode( $posted_data['bookingpress_wp_placeholder_text']) : array();
            }
            if(!empty($posted_data['bookingpress_send_whatsapp_notification'])) {
                $bookingpress_database_modify_data['bookingpress_send_whatsapp_notification'] = ($posted_data['bookingpress_send_whatsapp_notification'] == 'true') ? 1 : 0 ;
            }

            $bookingpress_database_modify_data['bookingpress_whatsapp_admin_number'] = !empty($posted_data['bookingpress_whatsapp_admin_number']) ? $posted_data['bookingpress_whatsapp_admin_number']  : "" ;


            return $bookingpress_database_modify_data;
        }

        function bookingpress_notification_dynamic_vue_methods_func(){
            ?>
                
                bookingpress_select_template( event ){

                    const vm = this
                    let bookingpress_wp_selected_template = event;

                    for( let items of vm.bookingpress_get_whatsapp_template_list ){
                        if( items.template_id == bookingpress_wp_selected_template ){
                            vm.bookingpress_whatsapp_notification_msg = items.template_body;
                            
                            let matches = vm.bookingpress_whatsapp_notification_msg.match( /\{{([\d]+)}}/g );
                            
                            if( matches && 0 < matches.length ){
                                vm.bookingpress_whatsapp_dynamic_data = matches.length;
                            } else {
                                vm.bookingpress_whatsapp_dynamic_data = 0;
                            }

                            if( 0 < vm.bookingpress_whatsapp_dynamic_data ){
                                let whatsapp_dynamic_data = {};
                                for( let x = 0; x < vm.bookingpress_whatsapp_dynamic_data; x++ ){
                                    whatsapp_dynamic_data[x] = "";
                                }
                                vm.bookingpress_wp_placeholder_text = whatsapp_dynamic_data;
                            } else {
                                vm.bookingpress_wp_placeholder_text = [];
                            }
                        }
                    }
                },
                bookingpress_whatsapp_refresh_templates(){
                    const vm = this;
                    let svg = document.getElementById( 'bookingpress-whatspp-svg' );
                    svg.classList.add('whatsapp-refresh-rotate');
                    var postdata = {};
                    postdata.action = 'bookingpress_refresh_whatsapp_template_list'
                    postdata._wpnonce = '<?php echo esc_html(wp_create_nonce( 'bpa_wp_nonce' )); ?>'
                    axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postdata ) )
                    .then( function (response) {
                        svg.classList.remove('whatsapp-refresh-rotate');
                        if( response.data.variant == 'success' ){
                            vm.bookingpress_get_whatsapp_template_list = JSON.parse( response.data.template_list );
                            vm.$notify({
                                title: response.data.title,
                                message: response.data.msg,
                                type: response.data.variant,
                                customClass: response.data.variant+'_notification',
                            });
                        }
                    }.bind(this) )
                    .catch( function (error) {
                        console.log(error)
                    });
                },
            <?php 
        }

        function bookingpress_add_email_notification_data_func(){
            
            ?>
                bookingpress_save_notification_data.whatsapp_notification_data = vm.bookingpress_whatsapp_notification_msg
                bookingpress_save_notification_data.bookingpress_send_whatsapp_notification = vm.bookingpress_send_whatsapp_notification
                bookingpress_save_notification_data.bookingpress_whatsapp_admin_number = vm.bookingpress_whatsapp_admin_number

                bookingpress_save_notification_data.bookingpress_wp_selected_template = vm.bookingpress_wp_selected_template
                bookingpress_save_notification_data.bookingpress_whatsapp_dynamic_data = vm.bookingpress_whatsapp_dynamic_data
                bookingpress_save_notification_data.bookingpress_whatsapp_dynamic_data = ""
                bookingpress_save_notification_data.bookingpress_wp_placeholder_text = vm.bookingpress_wp_placeholder_text
            <?php
        }

        function bookingpress_add_dynamic_notification_data_fields_func($bookingpress_notification_vue_methods_data){
            
            global $BookingPress;
            $wp_template_data = $BookingPress->bookingpress_get_settings('bpa_wp_template_data', 'notification_setting');
            $bpa_selected_wp_gateway = $BookingPress->bookingpress_get_settings('bookingpress_selected_whatsapp_gateway', 'notification_setting');
            
            if( $bpa_selected_wp_gateway == 'Twilio' ){
                $wp_template_data = $BookingPress->bookingpress_get_settings('bpa_wp_twilio_template_data', 'notification_setting');
            }
            $wp_get_template_data = !empty( $wp_template_data ) ? json_decode( $wp_template_data, true ) : array();

            
            $bookingpress_notification_vue_methods_data['bookingpress_whatsapp_notification_msg'] = '';
            $bookingpress_notification_vue_methods_data['bookingpress_send_whatsapp_notification'] = 0;
            $bookingpress_notification_vue_methods_data['bookingpress_whatsapp_admin_number'] = '';
            $bookingpress_notification_vue_methods_data['bookingpress_get_whatsapp_template_list'] = $wp_get_template_data;
            $bookingpress_notification_vue_methods_data['bookingpress_whatsapp_dynamic_data'] = 0;
            $bookingpress_notification_vue_methods_data['bookingpress_wp_selected_template'] = '';
            $bookingpress_notification_vue_methods_data['bookingpress_wp_placeholder_text'] = array();
            $bookingpress_notification_vue_methods_data['bookingpress_wp_selected_gateway'] = !empty($bpa_selected_wp_gateway) ? $bpa_selected_wp_gateway : '';

            $customer_placeholder_data = $bookingpress_notification_vue_methods_data['bookingpress_customer_placeholders'];
            $service_placeholder_data = $bookingpress_notification_vue_methods_data['bookingpress_service_placeholders'];
            $company_placeholder_data = $bookingpress_notification_vue_methods_data['bookingpress_company_placeholders'];
            $staff_member_placeholders = $bookingpress_notification_vue_methods_data['bookingpress_staff_member_placeholders'];
            $appointment_placeholders = $bookingpress_notification_vue_methods_data['bookingpress_appointment_placeholders'];
            $custom_fields_placeholders = $bookingpress_notification_vue_methods_data['bookingpress_custom_fields_placeholders'];

            $bookingpress_notification_vue_methods_data['show_twilio_freeform_msg'] = $BookingPress->bookingpress_get_settings('bookingpress_whatsapp_show_freeform_msg', 'notification_setting');
            $bookingpress_notification_vue_methods_data['twilio_msg_type'] = $BookingPress->bookingpress_get_settings( 'bookingpress_whatsapp_twilio_msg_type', 'notification_setting' );

            $bpa_whatsapp_template_placeholders = array(
                'customer' => array(
                    'label' => esc_html__( 'Customer', 'bookingpress-whatsapp'),
                    'placeholders' => $customer_placeholder_data
                ),
                'service' => array(
                    'label' => esc_html__( 'Service', 'bookingpress-whatsapp'),
                    'placeholders' => $service_placeholder_data
                ),
                'company' => array(
                    'label' => esc_html__( 'Company', 'bookingpress-whatsapp'),
                    'placeholders' => $company_placeholder_data
                ),
                'staff_members' => array(
                    'label' => esc_html__( 'Staff Member', 'bookingpress-whatsapp'),
                    'placeholders' => $staff_member_placeholders
                ),
                'appointment' => array(
                    'label' => esc_html__( 'Appointment', 'bookingpress-whatsapp'),
                    'placeholders' => $appointment_placeholders
                ),
            );
            if( !empty( $custom_fields_placeholders ) ){
                $bpa_whatsapp_template_placeholders['custom_fields'] = array(
                    'label' => esc_html__( 'Custom Fields', 'bookingpress-whatsapp' ),
                    'placeholders' => $custom_fields_placeholders
                );
            }

            $cart_placeholders = !empty( $bookingpress_notification_vue_methods_data['cart_appointment_list'] ) ? $bookingpress_notification_vue_methods_data['cart_appointment_list'] : array();
            if( !empty( $cart_placeholders ) ){
                $bpa_whatsapp_template_placeholders['cart'] = array(
                    'label' => esc_html__( 'Cart', 'bookingpress-whatsapp' ),
                    'placeholders' => $cart_placeholders
                );
            }

            $advance_discount_placeholder = !empty( $bookingpress_notification_vue_methods_data['advanced_discount_variable'] ) ? $bookingpress_notification_vue_methods_data['advanced_discount_variable'] : array();
            if( !empty( $advance_discount_placeholder ) ){
                $bpa_whatsapp_template_placeholders['advance_discount'] = array(
                    'label' => esc_html__( 'Advance Discount', 'bookingpress-whatsapp' ),
                    'placeholders' => $advance_discount_placeholder
                );
            }

            $location_placeholders = !empty( $bookingpress_notification_vue_methods_data['bookingpress_location_placeholders'] ) ? $bookingpress_notification_vue_methods_data['bookingpress_location_placeholders'] : array();
            if( !empty( $location_placeholders ) ){
                $bpa_whatsapp_template_placeholders['location'] = array(
                    'label' => esc_html__( 'Location', 'bookingpress-whatsapp' ),
                    'placeholders' => $location_placeholders
                );
            }

            $package_placeholders = !empty( $bookingpress_notification_vue_methods_data['bookingpress_package_order_placeholder'] ) ? $bookingpress_notification_vue_methods_data['bookingpress_package_order_placeholder'] : array();
            if( !empty( $package_placeholders ) ){
                $bpa_whatsapp_template_placeholders['package'] = array(
                    'label' => esc_html__( 'Package', 'bookingpress-whatsapp' ),
                    'placeholders' => $package_placeholders
                );
            }

            $recurring_placeholders = !empty( $bookingpress_notification_vue_methods_data['bookingpress_add_recurring_appointment_placeholder_list'] ) ? $bookingpress_notification_vue_methods_data['bookingpress_add_recurring_appointment_placeholder_list'] : array();
            if( !empty( $recurring_placeholders ) ){
                $bpa_whatsapp_template_placeholders['recurring'] = array(
                    'label' => esc_html__( 'Recurring', 'bookingpress-whatsapp' ),
                    'placeholders' => $recurring_placeholders
                );
            }


            $waiting_list_placeholders = !empty( $bookingpress_notification_vue_methods_data['bookingpress_waitinglist_placeholders'] ) ? $bookingpress_notification_vue_methods_data['bookingpress_waitinglist_placeholders'] : array();
            if( !empty( $waiting_list_placeholders ) ){
                $bpa_whatsapp_template_placeholders['waiting_list'] = array(
                    'label' => esc_html__( 'Waiting List', 'bookingpress-whatsapp' ),
                    'placeholders' => $waiting_list_placeholders
                );
            }

            $zoom_meeting_placeholders = !empty( $bookingpress_notification_vue_methods_data['zoom_meeting_placeholder'] ) ? $bookingpress_notification_vue_methods_data['zoom_meeting_placeholder'] : array();
            if( !empty( $zoom_meeting_placeholders ) ){
                $bpa_whatsapp_template_placeholders['zoom'] = array(
                    'label' => esc_html__( 'Zoom Meeting', 'bookingpress-whatsapp' ),
                    'placeholders' => $zoom_meeting_placeholders
                );
            }

            $google_meet_placeholders = !empty( $bookingpress_notification_vue_methods_data['google_meet_placeholder'] ) ? $bookingpress_notification_vue_methods_data['google_meet_placeholder'] : array();
            if( !empty( $google_meet_placeholders ) ){
                $bpa_whatsapp_template_placeholders['google_calendar'] = array(
                    'label' => esc_html__( 'Google Calendar', 'bookingpress-whatsapp' ),
                    'placeholders' => $google_meet_placeholders
                );
            }

            $bpa_whatsapp_template_placeholders = apply_filters( 'bookingpress_modify_whatsapp_template_placeholder_outside', $bpa_whatsapp_template_placeholders, $bookingpress_notification_vue_methods_data );

            $bookingpress_notification_vue_methods_data['bookingpress_wp_placeholders_data'] = $bpa_whatsapp_template_placeholders;



            return $bookingpress_notification_vue_methods_data;
        }

        function bookingpress_add_email_notification_section_func(){
            ?>  
                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                    <el-form-item>
                        <div class="bpa-en-status--swtich-row">
                            <label class="bpa-form-label"><?php esc_html_e( 'Send Whatsapp Notification', 'bookingpress-whatsapp' ); ?></label>
                            <el-switch class="bpa-swtich-control" v-model="bookingpress_send_whatsapp_notification"></el-switch>
                        </div>
                    </el-form-item>
                </el-col>

                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="bookingpress_active_email_notification != 'share_appointment' &&(bookingpress_send_whatsapp_notification == true && activeTabName == 'employee')"> 
                <el-form-item>
                    <template #label>
                        <span class="bpa-form-label"><?php esc_html_e('Enter phone number ( With Contry code ) to send Whatsapp notification to extra recipient', 'bookingpress-whatsapp'); ?></span>
                    </template>
                    <el-input class="bpa-form-control" v-model="bookingpress_whatsapp_admin_number"></el-input>
                </el-form-item>
            </el-col>

            <el-row type="flex" :gutter="32">
                <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="bookingpress_send_whatsapp_notification == true && ( 'Whatsapp Business' == bookingpress_wp_selected_gateway || 'template' == twilio_msg_type )">
                    <el-form-item class="bookingpress_whatsapp_template_container">
                        <template #label>
                            <span class="bpa-form-label"><?php esc_html_e('Select Template', 'bookingpress-whatsapp'); ?></span>
                        </template>
                        <el-select class="bpa-form-control bpa_whatsapp_select_template_input" v-model="bookingpress_wp_selected_template" placeholder="<?php esc_html_e( 'Select the Whatsapp Template', 'bookingpress-whatsapp' ); ?>" @change="bookingpress_select_template($event)">
                            <el-option v-for="item in bookingpress_get_whatsapp_template_list" :key="item.template_id" :label="item.template_label" :value="item.template_id"></el-option>
                        </el-select>
                        <el-tooltip class="bpa_refresh_whatsapp_template_btn" effect="dark" content="" placement="top" open-delay="300">
                            <div slot="content">
                                <span><?php esc_html_e( 'Refresh Template List', 'bookingpress-whatsapp' ); ?></span>
                            </div>
                            <el-button class="bpa-btn bpa-btn__medium bpa-btn--full-width" @click="bookingpress_whatsapp_refresh_templates()" >
                            <svg width="18" height="18" viewBox="0.96 1.88 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" id="bookingpress-whatspp-svg">
                            <g clip-path="url(#clip0_30_1275)">
                            <path d="M5.24736 7.17508C5.06799 7.12776 4.91891 7.00321 4.84043 6.83512C4.76196 6.66703 4.7622 6.47277 4.84109 6.30487L6.22519 3.35924C6.32834 3.13973 6.54915 2.9997 6.79169 3C7.03423 3.0003 7.25469 3.14088 7.3573 3.36064L7.96788 4.66847C7.98971 4.6599 8.01225 4.65245 8.03545 4.64624C11.6586 3.67541 15.3828 5.82556 16.3536 9.44873C17.3244 13.0719 15.1743 16.7961 11.5511 17.7669C7.92795 18.7377 4.20378 16.5876 3.23295 12.9644C2.92424 11.8122 2.93118 10.6481 3.20157 9.56566C3.28524 9.23074 3.62457 9.02706 3.95949 9.11073C4.29441 9.19439 4.4981 9.53372 4.41443 9.86865C4.19416 10.7504 4.18809 11.6989 4.44049 12.6408C5.23262 15.5971 8.27129 17.3515 11.2276 16.5594C14.1838 15.7672 15.9382 12.7286 15.1461 9.77229C14.3669 6.86436 11.414 5.11933 8.50407 5.81695L9.12013 7.13651C9.22273 7.35627 9.18894 7.61555 9.03344 7.80168C8.87794 7.98781 8.62881 8.06719 8.39429 8.00532L5.24736 7.17508Z" fill="#727E95" stroke="#727E95" stroke-width="0.55" stroke-linejoin="round"/>
                            </g>
                            <defs>
                            <clipPath id="clip0_30_1275">
                            <rect width="20" height="20" fill="white"/>
                            </clipPath>
                            </defs>
                            </svg>
                            </el-button>
                        </el-tooltip>
                    </el-form-item>    
                </el-col>
            </el-row>

            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="bookingpress_send_whatsapp_notification == true">
                <el-form-item>
                    <template #label>
                        <span class="bpa-form-label"><?php esc_html_e( 'WhatsApp Notification Message', 'bookingpress-whatsapp' ); ?></span>
                    </template>
                    <el-input class="bpa-form-control" id="bookingpress_whatsapp_notification" v-model="bookingpress_whatsapp_notification_msg" type="textarea" :rows="3"  :disabled="( 'Whatsapp Business' == bookingpress_wp_selected_gateway || 'template' == twilio_msg_type ) ? true: false"></el-input>
                </el-form-item>												
            </el-col>
            <el-row type="flex" :gutter="24" v-for="(item,keys) in bookingpress_whatsapp_dynamic_data" v-if="bookingpress_send_whatsapp_notification == true && ( 'Whatsapp Business' == bookingpress_wp_selected_gateway || 'template' == twilio_msg_type )">
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                    <el-form-item>
                        <template #label>
                            <span class="bpa-form-label bpa-placeholder-label"><?php esc_html_e('Placeholder for variable', 'bookingpress-whatsapp'); ?> <span>{{item}}</span> </span>
                        </template>
                    </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                    <el-form-item>
                        <el-select class="bpa-form-control" v-model="bookingpress_wp_placeholder_text[item]" :id="item">
                            <el-option-group v-for="group in bookingpress_wp_placeholders_data" :label="group.label">
                                <el-option v-for="item in group.placeholders" :label="item.name" :value="item.value"></el-option>
                            </el-option-group>
                        </el-select>
                    </el-form-item>
                </el-col>
            </el-row>

            <?php
        }
        
        function bookingpress_whatsapp_gateway_list(){
            $bookingpress_whatsapp_gateway_list = array(
                
                "twilio"  => array(
                    "name"    => "Twilio",
                    "url"     => "https://api.twilio.com/2010-04-01/Accounts/{BOOKINGPRESS_ACCOUNT_NUMNER}/Messages.json",
                    "group"   => "post_usdpwd",
                    "content_type" => "application/x-www-form-urlencoded",
                    "header"  => array(
                        "account_sid" => array(
                            "label"            => __("Account SID", 'bookingpress-whatsapp'),
                            "slug"             => "account_sid",
                            "show_in_settings" => 1,
                            "required"         => 1,
                            "id"               => "bookingpress_account_sid",
                            "type"             => "text",
                            "empty_msg"        => __("Account SID can not be left blank", 'bookingpress-whatsapp'),
                        ),
                        "auth_token" => array(
                            "label"            => __("Auth Token", 'bookingpress-whatsapp'),
                            "slug"             => "auth_token",
                            "show_in_settings" => 1,
                            "required"         => 0,
                            "id"               => "bookingpress_auth_token",
                            "type"             => "text",
                        )
                    ),
                ),
                "whatsapp_business" => array(
                    "name"  => "Whatsapp Business",
                    "url"   => "https://graph.facebook.com/{version}/{BOOKINGPRESS_WHATSAPP_FROM_NUMBER_ID}/messages",
                    "group" => "post_bearer",
                    "content_type" => "application/json",
                    "header" => array(
                        
                    )
                )
            );
            return $bookingpress_whatsapp_gateway_list;
        }
        
        function bookingpress_add_notification_settings_section_func(){
            require(BOOKINGPRESS_WHATSAPP_DIR.'/core/views/bookingpress_whatsapp_settings.php');
        }

        public static function install(){
			global $wpdb, $bookingpress_whatsapp_version, $tbl_bookingpress_notifications,$tbl_bookingpress_customize_settings, $BookingPress;
            $bookingpress_wp_version = get_option('bookingpress_whatsapp_gateway');
            if (!isset($bookingpress_wp_version) || $bookingpress_wp_version == '') {


                // activate license for this addon
                $posted_license_key = trim( get_option( 'bkp_license_key' ) );
			    $posted_license_package = '4870';

                $myaddon_name = "bookingpress-whatsapp/bookingpress-whatsapp.php";

                $api_params = array(
                    'edd_action' => 'activate_license',
                    'license'    => $posted_license_key,
                    'item_id'  => $posted_license_package,
                    //'item_name'  => urlencode( BOOKINGPRESS_ITEM_NAME ), // the name of our product in EDD
                    'url'        => home_url()
                );

                // Call the custom API.
                $response = wp_remote_post( BOOKINGPRESS_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

                //echo "<pre>";print_r($response); echo "</pre>"; exit;

                // make sure the response came back okay
                $message = "";
                if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                    $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.','bookingpress-whatsapp' );
                } else {
                    $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                    $license_data_string = wp_remote_retrieve_body( $response );
                    if ( false === $license_data->success ) {
                        switch( $license_data->error ) {
                            case 'expired' :
                                $message = sprintf(
                                    __( 'Your license key expired on %s.','bookingpress-whatsapp' ),
                                    date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                                );
                                break;
                            case 'revoked' :
                                $message = __( 'Your license key has been disabled.','bookingpress-whatsapp' );
                                break;
                            case 'missing' :
                                $message = __( 'Invalid license.','bookingpress-whatsapp' );
                                break;
                            case 'invalid' :
                            case 'site_inactive' :
                                $message = __( 'Your license is not active for this URL.','bookingpress-whatsapp' );
                                break;
                            case 'item_name_mismatch' :
                                $message = __('This appears to be an invalid license key for your selected package.','bookingpress-whatsapp');
                                break;
                            case 'invalid_item_id' :
                                    $message = __('This appears to be an invalid license key for your selected package.','bookingpress-whatsapp');
                                    break;
                            case 'no_activations_left':
                                $message = __( 'Your license key has reached its activation limit.','bookingpress-whatsapp' );
                                break;
                            default :
                                $message = __( 'An error occurred, please try again.','bookingpress-whatsapp' );
                                break;
                        }

                    }

                }

                if ( ! empty( $message ) ) {
                    update_option( 'bkp_whatsapp_license_data_activate_response', $license_data_string );
                    update_option( 'bkp_whatsapp_license_status', $license_data->license );
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Whatsapp Add-on', 'bookingpress-whatsapp');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-whatsapp'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }
                
                if($license_data->license === "valid")
                {
                    update_option( 'bkp_whatsapp_license_key', $posted_license_key );
                    update_option( 'bkp_whatsapp_license_package', $posted_license_package );
                    update_option( 'bkp_whatsapp_license_status', $license_data->license );
                    update_option( 'bkp_whatsapp_license_data_activate_response', $license_data_string );
                }


                
                update_option('bookingpress_whatsapp_gateway', $bookingpress_whatsapp_version);
                $wpdb->query("ALTER TABLE {$tbl_bookingpress_notifications} ADD bookingpress_whatsapp_notification_message TEXT NULL DEFAULT NULL AFTER bookingpress_notification_message"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

                $wpdb->query("ALTER TABLE {$tbl_bookingpress_notifications} ADD bookingpress_send_whatsapp_notification INT(1) DEFAULT 0 AFTER bookingpress_notification_duration_unit"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

                $wpdb->query("ALTER TABLE {$tbl_bookingpress_notifications} ADD bookingpress_whatsapp_admin_number VARCHAR(60) NULL DEFAULT NULL AFTER bookingpress_send_whatsapp_notification");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

                $wpdb->query("ALTER TABLE {$tbl_bookingpress_notifications} ADD bookingpress_wp_template_placeholder text DEFAULT NULL AFTER bookingpress_send_whatsapp_notification");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

                $wpdb->query("ALTER TABLE {$tbl_bookingpress_notifications} ADD bookingpress_wp_selected_template VARCHAR(255) DEFAULT NULL AFTER bookingpress_wp_template_placeholder");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

                $wpdb->query("ALTER TABLE {$tbl_bookingpress_notifications} ADD bookingpress_whatsapp_dynamic_data INT DEFAULT NULL AFTER bookingpress_wp_selected_template");  // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
                
                $BookingPress->bookingpress_update_settings( 'bookingpress_whatsapp_twilio_msg_type', 'notification_setting', 'template' );

                $bookingpress_customize_settings_db_fields = array(
                    'bookingpress_setting_name'  => 'send_whatsapp_notification_label',
                    'bookingpress_setting_value' => __('Send whatsapp notification','bookingpress-whatsapp'),
                    'bookingpress_setting_type'  => 'booking_form',
                );
                $wpdb->insert($tbl_bookingpress_customize_settings, $bookingpress_customize_settings_db_fields);
            }
		}

        public static function uninstall() {
            global $wpdb,$tbl_bookingpress_notifications;
            delete_option('bookingpress_whatsapp_gateway');            
            
            delete_option('bkp_whatsapp_license_key');
            delete_option('bkp_whatsapp_license_package');
            delete_option('bkp_whatsapp_license_status');
            delete_option('bkp_whatsapp_license_data_activate_response');

            $wpdb->query( "ALTER TABLE {$tbl_bookingpress_notifications} DROP COLUMN bookingpress_whatsapp_notification_message" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
            $wpdb->query( "ALTER TABLE {$tbl_bookingpress_notifications} DROP COLUMN bookingpress_send_whatsapp_notification" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm

            $wpdb->query( "ALTER TABLE {$tbl_bookingpress_notifications} DROP COLUMN bookingpress_whatsapp_admin_number" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared --Reason: $tbl_bookingpress_notifications is a table name. false alarm
        }
    }
    global $bookingpress_whatsapp;
    $bookingpress_whatsapp = new bookingpress_whatsapp;
}
?>