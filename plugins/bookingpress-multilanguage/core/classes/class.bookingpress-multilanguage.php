<?php
if (!class_exists('bookingpress_multilanguage') && class_exists( 'BookingPress_Core')) {
	class bookingpress_multilanguage {
		function __construct() {

            global $bookingpress_pro_version,$wpdb, $bookingpress_all_language_translation_fields_section, $tbl_bookingpress_ml_translation,$bookingpress_all_element_type,$bp_translation_lang,$bookingpress_all_language_translation_fields;
            $bookingpress_pro_version = get_option( 'bookingpress_pro_version');
            $bookingpress_pro_version = (!empty($bookingpress_pro_version))?$bookingpress_pro_version:0;

            $tbl_bookingpress_ml_translation = $wpdb->prefix . 'bookingpress_ml_translation';

            $bookingpress_all_language_translation_fields_section = $this->bookingpress_all_language_translation_fields_section();
            $bookingpress_all_language_translation_fields = $this->bookingpress_all_language_translation_fields();
            
            $bookingpress_all_element_type = array('service','service_extras','categories');

            register_activation_hook(BOOKINGPRESS_MULTILANGUAGE_DIR.'/bookingpress-multilanguage.php', array('bookingpress_multilanguage', 'install'));
            register_uninstall_hook(BOOKINGPRESS_MULTILANGUAGE_DIR.'/bookingpress-multilanguage.php', array('bookingpress_multilanguage', 'uninstall'));
            
            
            if(is_plugin_active('weglot/weglot.php')) {
                add_filter( 'locale', array($this,'weglot_set_locale'));
            }

            //Admiin notices
            add_action('admin_notices', array($this, 'bookingpress_admin_notices'));
            if( !function_exists('is_plugin_active') ){
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            if(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php') && version_compare($bookingpress_pro_version, '2.6.1', '>=')) {

                /* Front booking form shortcode add language data */
                //add_action( 'bookingpress_front_booking_form_load_before', array($this,'bookingpress_front_booking_form_load_before_fun'),10);
                add_action('init',array($this,'bookingpress_set_front_global_language_data'));                
                add_action('bookingpress_invoice_pdf_generate_before',array($this,'bookingpress_invoice_pdf_generate_before_fun'),10,1);

                /*Load CSS & JS at admin side*/
                add_action( 'admin_enqueue_scripts', array( $this, 'set_admin_css' ), 12 );
                add_filter('bookingpress_addon_list_data_filter',array($this,'bookingpress_addon_list_data_filter_func'));

                             
                //add_filter('bookingpress_frontend_multilanguage_translation', array($this,'bookingpress_frontend_multilanguage_translation_func'), 10,4);

                /* Service Section language added */
                add_action('bookingpress_service_header_extra_button',array($this,'bookingpress_service_language_translation_btn_fun'));
                add_action('bookingpress_service_language_translation_popup',array($this,'bookingpress_service_language_translation_popup_fun'));
                add_filter('bookingpress_modify_service_data_fields', array( $this, 'bookingpress_modify_service_data_fields_func' ), 10 );
                add_action( 'bookingpress_add_service_dynamic_vue_methods', array( $this, 'bookingpress_add_service_dynamic_vue_methods_func' ), 12 );
                add_filter( 'bookingpress_after_add_update_service', array( $this, 'bookingpress_save_service_details' ), 12, 3 );
                add_filter( 'bookingpress_modify_edit_service_data', array( $this, 'bookingpress_modify_edit_service_data_func' ), 11, 2 );
                add_action( 'bookingpress_edit_service_more_vue_data', array( $this, 'bookingpress_edit_service_data_for_service_xhr_response') );
                add_action('bookingpress_after_open_add_service_model', array($this,'bookingpress_after_open_add_service_model_fun'));
                add_action( 'bookingpress_after_delete_service', array( $this, 'bookingpress_after_delete_service_func' ), 10 );

                /* Duplicate Service Translation */
                add_action('bookingpress_duplicate_more_details', array($this, 'bookingpress_duplicate_more_details_func'), 11, 2);
                add_action('bookingpress_after_duplicate_service_extra', array($this, 'bookingpress_after_duplicate_service_extra_func'), 11, 2);
                

                /* Category Section language added */
                add_action('bookingpress_category_header_section_button',array($this,'bookingpress_category_language_translation_btn_fun'));
                add_action('wp_ajax_bookingpress_save_category_language_data', array( $this, 'bookingpress_save_category_language_data_func' ), 10);
                add_filter('bookingpress_modified_service_category_response_data',array($this,'bookingpress_modified_service_category_response_data'),20,1);
                add_action('bookingpress_after_load_service_category_data',array($this,'bookingpress_after_load_service_category_data_func'));


                /* Customized Section Language Added */
                add_action('bookingpress_form_customized_language_translation_btn',array($this,'bookingpress_form_customized_language_translation_btn_fun'));
                add_action('bookingpress_add_customize_custom_fields_top_button',array($this,'bookingpress_add_customize_custom_fields_top_button_fun'));
                add_filter('bookingpress_customize_add_dynamic_data_fields',array($this,'bookingpress_customize_add_dynamic_data_fields_func'),20);
                add_action( 'bookingpress_customize_dynamic_vue_methods', array( $this, 'bookingpress_customize_dynamic_vue_methods_func'),10);
                add_action('bookingpress_before_save_customize_form_settings',array($this,'bookingpress_before_save_customize_form_settings_func'));
                add_action( 'bookingpress_add_manage_customized_view_bottom', array( $this, 'bookingpress_add_manage_customized_view_bottom_func'),10);                 
                add_action( 'bookingpress_add_customize_custom_fields_view_after', array( $this, 'bookingpress_add_customize_custom_fields_view_after_func'),10);
                add_action('bookingpress_after_save_customize_settings',array($this,'bookingpress_after_save_customize_settings_fun'));

                add_action('bookingpress_before_save_field_settings_method',array($this,'bookingpress_before_save_field_settings_method_fun'));
                add_action('bookingpress_after_save_custom_form_fields',array($this,'bookingpress_after_save_custom_form_fields_func'));                
                add_filter('bookingpress_modified_load_custom_fields_response',array($this,'bookingpress_modified_load_custom_fields_response_func'),10,1);
                add_action( 'bookingpress_after_load_field_settings', array( $this, 'bookingpress_after_load_field_settings_func' ) );


                /* General Settings Language Added */
                add_action('bookingpress_add_general_setting_section', array($this, 'bookingpress_add_general_language_setting_section_func'));
                add_filter('bookingpress_add_setting_dynamic_data_fields', array($this, 'bookingpress_add_setting_dynamic_data_fields_func'), 20);
                add_action('bookingpress_add_setting_dynamic_vue_methods',array($this,'bookingpress_add_setting_dynamic_vue_methods'),15);
                add_filter('bookingpress_modify_save_setting_data',array($this,'bookingpress_modify_save_setting_data_func'),10,2);
                add_filter('bookingpress_modify_get_settings_data', array( $this, 'bookingpress_modify_get_settings_data_func'), 10, 2 );

                /* New Filter Added For Change Only Response Data */
                add_filter('bookingpress_modify_get_settings_response_data', array( $this, 'bookingpress_modify_get_settings_response_data_func'), 10, 2 );

                /* Notification Section Backend */
                add_action('bookingpress_manage_notification_setting_header_button',array($this,'bookingpress_manage_notification_setting_header_button_fun'));
                add_action('bookingpress_add_manage_notification_view_bottom',array($this,'bookingpress_add_manage_notification_view_bottom_func'));
                add_filter( 'bookingpress_add_dynamic_notification_data_fields', array( $this, 'bookingpress_add_dynamic_notification_data_fields_func' ), 15 );
                add_action( 'bookingpress_add_dynamic_notifications_vue_methods', array( $this, 'bookingpress_add_dynamic_notifications_vue_methods_func' ), 15 );

                add_action('bookingpress_after_save_email_notification_data',array($this,'bookingpress_after_save_email_notification_data_func'),10,1);
                add_action('bookingpress_add_email_notification_data',array($this,'bookingpress_add_email_notification_data_fun'));
                add_filter( 'bookingpress_get_email_notification_data_modified', array( $this, 'bookingpress_get_email_notification_data_modified_func' ), 10,2 );
                add_action ('bookingpress_email_notification_get_data',array($this,'bookingpress_email_notification_get_data_func'));


                add_action('bookingpress_add_settings_more_postdata',array($this,'bookingpress_add_settings_more_postdata_fun'));
                add_action('bookingpress_add_invoice_settings_more_postdata',array($this,'bookingpress_add_invoice_settings_more_postdata_func'));
                add_action('boookingpress_after_save_settings_data',array($this,'boookingpress_after_save_settings_data_func'));                


                /* Conpany Settings Language Added */
                add_action('bookingpress_company_setting_header_button',array($this,'bookingpress_company_setting_header_button_fun'));
                add_action('bookingpress_setting_view_data_after',array($this,'bookingpress_company_view_data_after_fun'));

                /* Message Settings Language Added */
                add_action('bookingpress_message_setting_header_button',array($this,'bookingpress_message_setting_header_button_fun'));
                add_action('bookingpress_get_settings_details_response',array($this,'bookingpress_get_settings_details_response_func'));

                /* Customer Fields Settings Language Added */
                add_action('bookingpress_customer_setting_header_button',array($this,'bookingpress_customer_setting_header_button_fun'));

                //add_filter( 'bookingpress_add_setting_dynamic_data_fields', array( $this, 'bookingpress_add_setting_dynamic_data_fields_func' ), 10 );

                /* Language popup not found */
                add_action('bookingpress_multi_language_popup_translate_language_not_found',array($this,'bookingpress_multi_language_popup_translate_language_not_found_func'));


                /* Invoice Addon Multi-language Data Added */
                add_action('bookingpress_invoice_setting_header_extra_button',array($this,'bookingpress_invoice_setting_header_extra_button_fun'));
                add_action('bookingpress_invoice_setting_view_bottom',array($this,'bookingpress_invoice_setting_view_bottom_fun'));
                add_action('boookingpress_after_save_invoice_settings_data',array($this,'boookingpress_after_save_settings_data_func')); 

                /* Location Addon Language Translation Popup Add  */
                add_action('bookingpress_location_header_extra_button',array($this,'bookingpress_location_header_extra_button_fun'));
                add_filter('bookingpress_modify_location_vue_fields_data', array( $this, 'bookingpress_modify_location_vue_fields_data_func' ), 10 );
                add_action('bookingpress_manage_location_view_bottom',array($this,'bookingpress_manage_location_view_bottom_func'));                
                add_action('bookingpress_add_location_dynamic_vue_methods',array($this,'bookingpress_add_location_dynamic_vue_methods_func'));
                add_action('bookingpress_add_location_more_postdata',array($this,'bookingpress_add_location_more_postdata_func'));
                add_action('bookingpress_after_update_location',array($this,'bookingpress_after_add_or_update_location_func'),10,1);
                add_action('bookingpress_after_add_location',array($this,'bookingpress_after_add_or_update_location_func'),10,1);
                add_filter('bookingpress_modified_get_edit_location_response',array($this,'bookingpress_modified_get_edit_location_response_func'),10,2);
                add_action('bookingpress_edit_location_more_vue_data',array($this,'bookingpress_edit_location_more_vue_data_func'));
                add_action('bookingpress_open_location_modal_after',array($this,'bookingpress_open_location_modal_after_func'));

                /* Booking Form Front Side Load language data Data */                
                



                /* Book Appointment Shortcode Added dyanamic service,category & service extra data added. */             
                add_filter('bookingpress_frontend_apointment_form_add_dynamic_data', array($this,'bookingpress_frontend_apointment_form_add_dynamic_data_ml_func'), 11,1);

                /* Bookingpress modified All Customized Settings data in Front Side  */
                add_filter('bookingpress_modified_get_customize_settings', array($this,'bookingpress_modified_get_customize_settings_func'), 10,3);

                /* Bookingpress modified All Settings data in Front Side  */
                add_filter('bookingpress_modified_get_settings', array($this,'bookingpress_modified_get_settings_func'), 10,3);

                /*  BookingPress Pro Function Filter Added */
                add_filter( 'bookingpress_add_language_translate_data', array( $this, 'bookingpress_add_language_translate_data_func'), 10, 6);

                /* Save Service Extra Language Data */
                add_action('bookingpress_after_save_service_extra',array($this,'bookingpress_after_save_service_extra_func'),10,3);

                /*  BookingPress Location front booking form language data added */
                add_filter( 'bookingpress_modified_location_data_for_front_booking_form', array( $this, 'bookingpress_modified_location_data_for_front_booking_form_func'), 10, 6);                

                /* New Filter For Change Notification Data BookingPress modified email notification data */                
                add_filter('bookingpress_modify_email_template_notification_data',array($this,'bookingpress_modify_email_template_notification_data_func'),10,5);
                
                /* Function to translate form field label, placeholder and error message */
                add_filter('bookingpress_modify_field_data_before_prepare', array($this,'bookingpress_modify_field_data_before_prepare_func'),10,1);

                /* Add New Filter For Whatsapp & SMS Addon  */
                add_filter('bookingpress_replace_notification_content_language_wise',array($this,'bookingpress_replace_notification_content_language_wise_func'),10,5);

                /* My Appointment Edit Profile Field language change */
                add_filter( 'bookingpress_arrange_form_fields_outside', array( $this, 'bookingpress_arrange_form_fields_func' ),15, 2 );

                /* New Filter for My Booking Service translation */
                //add_filter('bookingpress_modify_my_appointment_data',array($this,'bookingpress_modify_my_appointment_data_func'),10,2);
                
                /* New Filter for My Booking Extra service translation */
                //add_filter('bookingpress_modify_my_appointment_extra_service_data',array($this,'bookingpress_modify_my_appointment_extra_service_data_func'),10,1);

                /* New Filter for Invoice translation */
                add_filter('bookingpress_modified_bookingpress_invoice_html_format',array($this,'bookingpress_modified_bookingpress_invoice_html_format_func'),10,1);

                /* Modified Service Shortcode Data */
                //bookingpress_modify_service_shortcode_details
                add_filter('bookingpress_modify_service_shortcode_details', array($this, 'bookingpress_modify_service_shortcode_details_func'), 20, 2);

                /* For complete payment transaltion */
                add_filter('modify_complate_payment_data_after_entry_create',array($this,'modify_complate_payment_data_after_entry_create_lang_func'),10,2);
                
                /* For replacing Email Notification placeholder */
                add_filter('bookingpress_modify_email_content_details_filter',array($this,'bookingpress_modify_email_content_details_filter_func'),10,4);

                /* Add Appointment Language Data In Appointment Meta Table.  */
                add_action('bookingpress_after_book_appointment', array($this,'bookingpress_add_appointment_language_meta'), 5, 3);

                /* Add Filter for convert service name in client language */
                add_filter('bookingpress_modify_entry_data_before_insert',array($this,'bookingpress_modify_entry_data_before_insert_func'),25,2);

                add_action('bookingpress_set_notification_language_data',array($this,'bookingpress_set_notification_language_data'),10,4);

                /* Package multi-language functionality added */
                add_action('bookingpress_package_header_extra_button',array($this,'bookingpress_package_header_extra_button_func'));
                add_filter('bookingpress_modify_package_vue_fields_data', array( $this, 'bookingpress_modify_package_vue_fields_data_func' ), 10 );
                add_action('bookingpress_package_dynamic_vue_methods',array($this,'bookingpress_package_dynamic_vue_methods_func'));
                add_action('bookingpress_manage_package_view_bottom',array($this,'bookingpress_manage_package_view_bottom_func'));  
                add_action('bookingpress_add_package_more_postdata',array($this,'bookingpress_add_package_more_postdata_func'));
                
                add_action('bookingpress_after_add_package',array($this,'bookingpress_after_add_or_update_package_func'),10,1);
                add_action('bookingpress_after_update_package',array($this,'bookingpress_after_add_or_update_package_func'),10,1);

                add_filter('bookingpress_modified_get_edit_package_response',array($this,'bookingpress_modified_get_edit_package_response_func'),10,2);
                add_action('bookingpress_edit_package_more_vue_data',array($this,'bookingpress_edit_package_more_vue_data_func'));
                add_action('bookingpress_open_package_modal_after',array($this,'bookingpress_open_package_modal_after_func'));

                /* Package order email notification multi-language functionality */
                add_action('bookingpress_after_add_package_order',array($this,'bookingpress_after_add_package_order_func'),10,2);
                add_filter('bookingpress_modify_package_email_notification_data',array($this,'bookingpress_modify_package_email_notification_data_func'),10,3);

                add_action('bookingpress_after_send_package_order_notification',array($this,'bookingpress_after_send_package_order_notification_func'));

                add_action('bookingpress_multi_language_data_unset',array($this,'bookingpress_multi_language_data_unset_func'));

                add_filter('bookingpress_selected_gateway_label_name', array( $this, 'bookingpress_selected_gateway_label_name_func'), 10, 2 );     
                add_filter('bookingpress_selected_gateway_label_name_package', array( $this, 'bookingpress_selected_gateway_label_name_package_func'), 11, 2 );     
                
                add_filter('bookingpress_modify_bpa_general_settings_data',array($this,'bookingpress_modify_bpa_general_settings_data_func'),10,1);                
                
                /*Gift Card mult language transaltion */
                add_action('bookingpress_gift_card_header_extra_button',array($this,'bookingpress_gift_card_header_extra_button_func'));
                add_action('bookingpress_gift_cards_dynamic_vue_methods',array($this,'bookingpress_gift_card_dynamic_vue_methods_func'));
                add_filter('bookingpress_modify_gift_cards_vue_fields_data', array( $this, 'bookingpress_modify_gift_cards_vue_fields_data_func' ), 10 );
                add_action('bookingpress_manage_gift_card_view_bottom',array($this,'bookingpress_manage_gift_card_view_bottom_func'));  
                add_action('bookingpress_add_gift_card_more_postdata',array($this,'bookingpress_add_gift_card_more_postdata_func'));

                add_action('bookingpress_after_add_gift_card',array($this,'bookingpress_after_add_or_update_gift_card_func'),10,1);
                add_action('bookingpress_after_update_gift_card',array($this,'bookingpress_after_add_or_update_gift_card_func'),10,1);
                add_filter('bookingpress_modified_get_edit_gift_card_response',array($this,'bookingpress_modified_get_edit_gift_card_response_func'),10,2);
                add_action('bookingpress_edit_gift_card_more_vue_data',array($this,'bookingpress_edit_gift_card_more_vue_data_func'));
                add_action('bookingpress_open_gift_card_modal_after',array($this,'bookingpress_open_gift_card_modal_after_func'));
                /*Gift card Email notification for the multi language*/
                add_action('bookingpress_after_add_gift_card_order',array($this,'bookingpress_after_add_gift_card_order_func'),10,2);
                add_filter('bookingpress_modify_gift_card_email_notification_data',array($this,'bookingpress_modify_gift_card_email_notification_data_func'),10,3);
                add_action('bookingpress_after_send_gift_card_order_notification',array($this,'bookingpress_after_send_gift_card_order_notification_func'));
                /*Gift card Email notification for the multi language*/
                add_filter('bookingpress_selected_gateway_label_name_gift_card', array( $this, 'bookingpress_selected_gateway_label_name_gift_card_func'), 10, 2 );   
                add_filter('bookingpress_selected_gateway_label_name_gift_card_backend', array( $this, 'bookingpress_selected_gateway_label_name_gift_card_func'), 10, 2 );   
                /*Gift Card mult language transaltion */
            }

            add_action('activated_plugin',array($this,'bookingpress_is_multilanguage_addon_activated'),11,2);
	    
	    add_action('admin_init', array( $this, 'bookingpress_update_multi_language_data') );
        }


        function bookingpress_selected_gateway_label_name_gift_card_func($payment_gateway_label, $payment_gateway){
            $payment_gateway_label= $this->bookingpress_get_payment_gateway_translated($payment_gateway_label, $payment_gateway, 'gift_card_form');
            return $payment_gateway_label;
		} 

         /**
         * Function for set gift card booking language
         *
         * @return void
         */
        function bookingpress_after_send_gift_card_order_notification_func(){
            global $bp_translation_lang;
            $bp_translation_lang = $this->bookingpress_get_front_current_language(); 
        }

        /**
         * Function for gift card email notification data
         *
         * @param  mixed $bookingpress_notification_data
         * @param  mixed $bookingpress_gift_card_booking_id
         * @param  mixed $bookingpress_notification_receiver_type
         * @return void
         */
        function bookingpress_modify_gift_card_email_notification_data_func($bookingpress_notification_data,$bookingpress_gift_card_booking_id,$bookingpress_notification_receiver_type){
            global $wpdb,$tbl_bookingpress_ml_translation,$BookingPress,$bp_translation_lang;	
            $tbl_bookingpress_gift_card_bookings_meta = $wpdb->prefix.'bookingpress_gift_card_bookings_meta';

            if($bookingpress_notification_receiver_type != 'customer'){
                $bp_translation_lang = 'none';
                $bookingpress_lang_translation_details = array();
                return $bookingpress_notification_data;
            }

            $gift_card_language = "";
            $bookingpress_gift_card_meta_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_gift_card_meta_value,bookingpress_gift_card_meta_key FROM {$tbl_bookingpress_gift_card_bookings_meta} WHERE bookingpress_gift_card_booking_id = %d AND bookingpress_gift_card_meta_key = %s ORDER BY bookingpress_gift_card_meta_created_date DESC", $bookingpress_gift_card_booking_id,'gift_card_booking_language' ), ARRAY_A );

            if(!empty($bookingpress_gift_card_meta_data)){                
                $gift_card_language = (isset($bookingpress_gift_card_meta_data['bookingpress_gift_card_meta_value']))?$bookingpress_gift_card_meta_data['bookingpress_gift_card_meta_value']:'';
                if(!empty($gift_card_language)){ 

                    if(!empty($bookingpress_notification_data)){
                        foreach($bookingpress_notification_data as $key=>$bookingpress_notification_single){

                            $bookingpress_notification_name = $bookingpress_notification_single['bookingpress_notification_name'];
                            //$notification_name
                            $bookingpress_lang_email_translation_avaliable = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_ml_translation} WHERE (bookingpress_element_type = %s AND bookingpress_element_ref_id = %s AND bookingpress_language_code = %s AND bookingpress_ref_column_name IN ('bookingpress_notification_message','bookingpress_notification_subject'))",'manage_notification_customer',$bookingpress_notification_name,$gift_card_language), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_ml_translation is table name defined globally. False Positive alarm

                            if(!empty($bookingpress_lang_email_translation_avaliable)){
                                foreach($bookingpress_lang_email_translation_avaliable as $transval){
                                    if($transval['bookingpress_ref_column_name'] == 'bookingpress_notification_message'){
                                        $bookingpress_translated_value = $transval['bookingpress_translated_value'];
                                        if(!empty($bookingpress_translated_value)){
                                            $bookingpress_notification_data[$key]['bookingpress_notification_message'] = $bookingpress_translated_value;
                                            $bookingpress_notification_data[$key]['gift_card_booking_language'] = $gift_card_language;
                                        }
                                    }
                                    if($transval['bookingpress_ref_column_name'] == 'bookingpress_notification_subject'){
                                        $bookingpress_translated_value = $transval['bookingpress_translated_value'];
                                        if(!empty($bookingpress_translated_value)){
                                            $bookingpress_notification_data[$key]['bookingpress_notification_subject'] = $bookingpress_translated_value;
                                            $bookingpress_notification_data[$key]['gift_card_booking_language'] = $gift_card_language;
                                        }
                                    }                                    
                                }                                
                            }
                        }
                    }                    
                }
            }
            return $bookingpress_notification_data;
        }

        /**
         * Function for after add gift card order custom fields assign
         *
         * @return void
         */
        function bookingpress_after_add_gift_card_order_func($entry_id,$inserted_booking_id){
			global $wpdb, $BookingPress;	

            $tbl_bookingpress_gift_card_bookings_meta = $wpdb->prefix.'bookingpress_gift_card_bookings_meta';
            $bp_translation_lang = $this->bookingpress_get_front_current_language();                                        
            $bookingpress_db_fields = array(
                'bookingpress_entry_id' => $entry_id,
                'bookingpress_gift_card_booking_id' => $inserted_booking_id,
                'bookingpress_gift_card_meta_value' => $bp_translation_lang,
                'bookingpress_gift_card_meta_key' => 'gift_card_booking_language',
            );            
            $wpdb->insert($tbl_bookingpress_gift_card_bookings_meta, $bookingpress_db_fields);            
        }

         /**
         * Function for open gift card modal after reset language data
         *
         * @return void
        */
        function bookingpress_open_gift_card_modal_after_func(){
            ?>
                if(action == 'add'){                
                    vm.language_data = vm.language_data_org;                                               
                }
                vm.bookingpress_current_selected_lang = vm.bookingpress_current_selected_lang_org;
            <?php 
        }

        /**
         * Edit Gift Card language Vue Data set
         *
         * @return void
        */
        function bookingpress_edit_gift_card_more_vue_data_func(){
        ?>  
            if(typeof response.data.language_fields_data !== 'undefined'){
                vm.language_fields_data = response.data.language_fields_data;
            } 
            if(typeof response.data.language_data !== 'undefined'){
                vm.language_data = response.data.language_data;
            }
            if(typeof response.data.bookingpress_gift_card_language_section_title !== 'undefined'){
                vm.bookingpress_gift_card_language_section_title = response.data.bookingpress_gift_card_language_section_title;
            }
            if(typeof response.data.bookingpress_current_selected_lang !== 'undefined'){
                vm.bookingpress_current_selected_lang = response.data.bookingpress_current_selected_lang;
            }   
            if(typeof response.data.bookingpress_get_selected_languages !== 'undefined'){
                vm.bookingpress_get_selected_languages = response.data.bookingpress_get_selected_languages;
            }                                               
        <?php     
        }

        /**
         * Function for get Gift Card edit data
         *
         * @return void
         */
        function bookingpress_modified_get_edit_gift_card_response_func($response,$bookingpress_edit_id){

            global $wpdb,$tbl_bookingpress_categories,$bookingpress_options,$BookingPress,$bookingpress_all_language_translation_fields_section,$bookingpress_all_language_translation_fields;
            $bookingpress_gift_card_vue_data_fields = array();
            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_get_selected_languages = array();
            }
            $bookingpress_current_selected_lang = '';
            $bookingpress_gift_card_vue_data_fields['empty_selected_language'] = 0;
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_gift_card_vue_data_fields['empty_selected_language'] = 1;
            }            
            $bookingpress_gift_card_language_translation_fields = array();        
            $bookingpress_gift_card_language_translation_fields = apply_filters( 'bookingpress_modified_gift_card_language_translate_fields',$bookingpress_gift_card_language_translation_fields);             
            $bookingpress_gift_card_language_section_title = $this->bookingpress_get_language_translation_section_label($bookingpress_gift_card_language_translation_fields);
            $bookingpress_current_selected_lang = '';           
            $bookingpress_gift_card_vue_data_fields['language_data'] = array();
            $bookingpress_gift_card_vue_data_fields['language_fields_data'] = array();
            if(!empty($bookingpress_get_selected_languages)){
                $save_language_data = $this->bookingpress_get_language_data_for_backend($bookingpress_edit_id,'gift_card');
                foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                    if(empty($bookingpress_current_selected_lang)){
                        $bookingpress_current_selected_lang = $key;
                    }            
                    foreach($bookingpress_gift_card_language_translation_fields as $section_key=>$service_lang){                                                
                        foreach($service_lang as $field_key => $field_value){                            
                            $bookingpress_gift_card_vue_data_fields['language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                            $bookingpress_gift_card_vue_data_fields['language_data'][$key][$section_key][$field_key] = ''; 
                            $bookingpress_gift_card_vue_data_fields['language_data_org'][$key][$section_key][$field_key] = '';                            
                            if(!empty($save_language_data)){
                                $search = array('bookingpress_element_type' => 'gift_card', 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key, 'bookingpress_element_ref_id'=> $bookingpress_edit_id);
                                $keys = array_keys(array_filter($save_language_data, function ($v) use ($search) { 
                                            return $v['bookingpress_element_ref_id'] == $search['bookingpress_element_ref_id'] && $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                        }
                                ));
                                $index_val = isset($keys[0]) ? $keys[0] : '';
                                if($index_val!='' || $index_val == 0) {
                                    if(isset($save_language_data[$index_val])){
                                        $translated_data = $save_language_data[$index_val];
                                        $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                        $bookingpress_gift_card_vue_data_fields['language_data'][$key][$section_key][$field_key] = $bp_translated_str;    
                                    }
                                }
                            }                                
                        }
                    }
                }
            }            
            $response['language_fields_data'] = $bookingpress_gift_card_vue_data_fields['language_fields_data'];            
            $response['language_data'] = $bookingpress_gift_card_vue_data_fields['language_data'];
            $response['bookingpress_gift_card_language_section_title'] = $bookingpress_gift_card_language_section_title;
            $response['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
            $response['bookingpress_current_selected_lang_org'] = $bookingpress_current_selected_lang;
            $response['open_gift_card_translate_language'] = false;
            $response['bookingpress_get_selected_languages'] = $bookingpress_get_selected_languages;
            return $response;
        }  

         /**
         * Function for add/update gift card data
         *
         * @param  mixed $bookingpress_gift_card_id
         * @return void
         */
        function bookingpress_after_add_or_update_gift_card_func($bookingpress_gift_card_id){
            if(isset($_POST['language_data']) && !empty($_POST['language_data'])){ //phpcs:ignore
                global $BookingPress;
                if( !empty( $_POST['language_data'] ) && !is_array( $_POST['language_data'] ) ){ //phpcs:ignore
                    $_POST['language_data'] = json_decode( stripslashes_deep( $_POST['language_data'] ), true ); //phpcs:ignore                    
                }                
                $language_data = !empty($_POST['language_data'])?array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['language_data']):array();  // phpcs:ignore 

                if(!empty($language_data)){
                    if(is_array($language_data)){  
                        foreach($language_data as $lang_key=>$single_language_data){
                            foreach($single_language_data as $lang_section=>$lang_fields){
                                foreach($lang_fields as $lang_field_key=>$bp_translated_val){
                                    $this->bookingpress_common_save_language_data($lang_key,$lang_section,$bp_translated_val,$bookingpress_gift_card_id,$lang_field_key);
                                }                            
                            }
                        } 
                    }
                }                                                
            }    
        }

        /**
         * Function for add package postdata
         *
         * @return void
         */
        function bookingpress_add_gift_card_more_postdata_func(){
            ?>
                saveGiftCardDetails.language_data = vm.language_data;
            <?php 
            }

        /**
         * Function for add package language translation popup
         *
         * @return void
        */
        function bookingpress_manage_gift_card_view_bottom_func(){            
            include BOOKINGPRESS_MULTILANGUAGE_VIEW_DIR.'bookingpress_gift_card_language_translation_popup.php';
        }  

        /**
         * gift card multi-language data added
         *
         * @param  mixed $bookingpress_gift_card_vue_data_fields
         * @return void
         */
        function bookingpress_modify_gift_cards_vue_fields_data_func($bookingpress_gift_card_vue_data_fields){
            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_get_selected_languages = array();
            }
            $bookingpress_current_selected_lang = '';
            $bookingpress_gift_card_vue_data_fields['empty_selected_language'] = 0;
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_gift_card_vue_data_fields['empty_selected_language'] = 1;
            }            
            $bookingpress_gift_card_language_translate_fields = array();        
            $bookingpress_gift_card_language_translate_fields = apply_filters( 'bookingpress_modified_gift_card_language_translate_fields',$bookingpress_gift_card_language_translate_fields);             
            $bookingpress_gift_card_language_section_title = $this->bookingpress_get_language_translation_section_label($bookingpress_gift_card_language_translate_fields);
        
            $bookingpress_current_selected_lang = '';           
            $bookingpress_gift_card_vue_data_fields['language_data'] = array();
            $bookingpress_gift_card_vue_data_fields['language_fields_data'] = array();
            if(!empty($bookingpress_get_selected_languages)){
                foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                    if(empty($bookingpress_current_selected_lang)){
                        $bookingpress_current_selected_lang = $key;
                    }            
                    foreach($bookingpress_gift_card_language_translate_fields as $section_key=>$service_lang){                                                
                        foreach($service_lang as $field_key => $field_value){                            
                            $bookingpress_gift_card_vue_data_fields['language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                            $bookingpress_gift_card_vue_data_fields['language_data'][$key][$section_key][$field_key] = ''; 
                            $bookingpress_gift_card_vue_data_fields['language_data_org'][$key][$section_key][$field_key] = '';      
                        }
                    }
                }
            }
        
            $bookingpress_gift_card_vue_data_fields['bookingpress_gift_card_language_section_title'] = $bookingpress_gift_card_language_section_title;
            $bookingpress_gift_card_vue_data_fields['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
            $bookingpress_gift_card_vue_data_fields['bookingpress_current_selected_lang_org'] = $bookingpress_current_selected_lang;
            $bookingpress_gift_card_vue_data_fields['open_gift_card_translate_language'] = false;
            $bookingpress_gift_card_vue_data_fields['bookingpress_get_selected_languages'] = $bookingpress_get_selected_languages;        
            return $bookingpress_gift_card_vue_data_fields;
        } 

        /**
         * Function for add location vue method
         *
         * @return void
         */
        function bookingpress_gift_card_dynamic_vue_methods_func(){
        ?>
            open_gift_card_translate_language_modal(){
                var vm2 = this;
                vm2.open_gift_card_translate_language = true;
            },   
            change_multilanguage_current_language(lang){
                var vm2 = this;            
                vm2.bookingpress_current_selected_lang = lang;
            },             
        <?php 
        }

        function bookingpress_gift_card_header_extra_button_func(){
        ?>    
            <el-button class="bpa-btn bpa-btn--ml-translate" @click="open_gift_card_translate_language_modal()">
                <span class="material-icons-round">translate</span>
                <?php esc_html_e('Translate', 'bookingpress-multilanguage'); ?>
            </el-button> 
        <?php     
        }

        /**
         * Function for general setting data get
         *
         * @param  mixed $bookingpress_bpa_general_settings_data
         * @return void
        */
        function bookingpress_modify_bpa_general_settings_data_func($bookingpress_bpa_general_settings_data){

            $bookingpress_user_language = array();
            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
            $bookingpress_bpa_general_settings_data['general_setting']['languages'] = array();
            $get_wp_default_lang = $this->bookingpress_get_wordpress_default_lang();
            $get_all_language_list = $this->get_all_wp_languages_list();

            if(isset($get_all_language_list[$get_wp_default_lang])){
                $bookingpress_user_language[$get_wp_default_lang]['language'] = $get_all_language_list[$get_wp_default_lang]['language'];
                $bookingpress_user_language[$get_wp_default_lang]['english_name'] = $get_all_language_list[$get_wp_default_lang]['english_name'];
                $bookingpress_user_language[$get_wp_default_lang]['native_name'] = $get_all_language_list[$get_wp_default_lang]['native_name'];
                $bookingpress_user_language[$get_wp_default_lang]['flag_image'] = $get_all_language_list[$get_wp_default_lang]['flag_image'];
            }
            if(!empty($bookingpress_get_selected_languages)){
                foreach($bookingpress_get_selected_languages as $key=>$val){
                    $bookingpress_user_language[$key]['language'] = $val['language'];
                    $bookingpress_user_language[$key]['english_name'] = $val['english_name'];
                    $bookingpress_user_language[$key]['native_name'] = $val['native_name'];
                    $bookingpress_user_language[$key]['flag_image'] = $val['flag_image'];
                }
                $bookingpress_bpa_general_settings_data['languages'] = $bookingpress_user_language;
            }

            return $bookingpress_bpa_general_settings_data;
        }

        function bookingpress_selected_gateway_label_name_package_func($payment_gateway_label, $payment_gateway){
            $payment_gateway_label= $this->bookingpress_get_payment_gateway_translated($payment_gateway_label, $payment_gateway, 'package_booking_form');
            return $payment_gateway_label;
		} 

        function bookingpress_selected_gateway_label_name_func($payment_gateway_label, $payment_gateway){
            $payment_gateway_label= $this->bookingpress_get_payment_gateway_translated($payment_gateway_label, $payment_gateway, 'booking_form');
            return $payment_gateway_label;
		}                

        function bookingpress_get_payment_gateway_translated($payment_gateway_label, $payment_gateway, $element_ref_key){

            global $BookingPress, $tbl_bookingpress_ml_translation, $wpdb, $bp_translation_lang;
			$payment_gateway_label_temp = $payment_gateway_label;
            if(!empty($payment_gateway)) {
                if(!empty($payment_gateway) && ($payment_gateway == 'on-site' || $payment_gateway == 'on site') ) {
                    $payment_gateway_label = $BookingPress->bookingpress_get_customize_settings('locally_text', $element_ref_key);
                    $bookingpress_column_name= 'locally_text';
                } elseif(!empty($payment_gateway) && $payment_gateway != 'manual') {
                    $payment_gateway_label = $BookingPress->bookingpress_get_customize_settings($payment_gateway.'_text', $element_ref_key);
                    $bookingpress_column_name = $payment_gateway.'_text';
                }  
                if(empty($payment_gateway_label)) {
                    $payment_gateway_label = $payment_gateway_label_temp;
                } 
                else {
                    if(isset($bookingpress_column_name)){
                        if(empty($bp_translation_lang)) {
                            $bp_translation_lang = $this->bookingpress_get_front_current_language();    
                        }
                        $bookingpress_lang_translation = $wpdb->get_results($wpdb->prepare("SELECT bookingpress_translated_value,bookingpress_ref_column_name FROM {$tbl_bookingpress_ml_translation} WHERE (bookingpress_element_type = %s AND bookingpress_element_ref_id = %s AND bookingpress_language_code = %s AND bookingpress_ref_column_name = %s)", $element_ref_key,'0',$bp_translation_lang, $bookingpress_column_name), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_ml_translation is table name defined globally. False Positive alarm

                        if(!empty($bookingpress_lang_translation)) {
                            if(isset($bookingpress_lang_translation[0]['bookingpress_ref_column_name']) && ($bookingpress_lang_translation[0]['bookingpress_ref_column_name']== $bookingpress_column_name)) {
                                $payment_gateway_label = isset($bookingpress_lang_translation[0]['bookingpress_translated_value']) ? $bookingpress_lang_translation[0]['bookingpress_translated_value'] : $payment_gateway_label_temp;
                            }
                        }
                    }
                } 
            }
            return $payment_gateway_label;
        }

        /**
         * Function for set package booking language
         *
         * @return void
         */
        function bookingpress_after_send_package_order_notification_func(){
            global $bp_translation_lang;
            $bp_translation_lang = $this->bookingpress_get_front_current_language(); 
        }
        
        /**
         * Function for reset package booking language
         *
         * @return void
         */
        function bookingpress_multi_language_data_unset_func(){
            global $bp_translation_lang,$bookingpress_lang_translation_details;
            $bp_translation_lang = 'none'; 
            $bookingpress_lang_translation_details = array();
        }    
        
        /**
         * Function for package email notification data
         *
         * @param  mixed $bookingpress_notification_data
         * @param  mixed $bookingpress_package_booking_id
         * @param  mixed $bookingpress_notification_receiver_type
         * @return void
         */
        function bookingpress_modify_package_email_notification_data_func($bookingpress_notification_data,$bookingpress_package_booking_id,$bookingpress_notification_receiver_type){
            global $wpdb,$tbl_bookingpress_ml_translation,$BookingPress,$bp_translation_lang;	
            $tbl_bookingpress_package_bookings_meta = $wpdb->prefix.'bookingpress_package_bookings_meta';

            if($bookingpress_notification_receiver_type != 'customer'){
                $bp_translation_lang = 'none';
                $bookingpress_lang_translation_details = array();
                return $bookingpress_notification_data;
            }

            $package_language = "";
            $bookingpress_package_meta_data = $wpdb->get_row( $wpdb->prepare( "SELECT bookingpress_package_meta_value,bookingpress_package_meta_key FROM {$tbl_bookingpress_package_bookings_meta} WHERE bookingpress_package_booking_id = %d AND bookingpress_package_meta_key = %s ORDER BY bookingpress_package_meta_created_date DESC", $bookingpress_package_booking_id,'package_language' ), ARRAY_A );

            if(!empty($bookingpress_package_meta_data)){                
                $package_language = (isset($bookingpress_package_meta_data['bookingpress_package_meta_value']))?$bookingpress_package_meta_data['bookingpress_package_meta_value']:'';
                if(!empty($package_language)){ 

                    if(!empty($bookingpress_notification_data)){
                        foreach($bookingpress_notification_data as $key=>$bookingpress_notification_single){

                            $bookingpress_notification_name = $bookingpress_notification_single['bookingpress_notification_name'];
                            //$notification_name
                            $bookingpress_lang_email_translation_avaliable = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_ml_translation} WHERE (bookingpress_element_type = %s AND bookingpress_element_ref_id = %s AND bookingpress_language_code = %s AND bookingpress_ref_column_name IN ('bookingpress_notification_message','bookingpress_notification_subject'))",'manage_notification_customer',$bookingpress_notification_name,$package_language), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_ml_translation is table name defined globally. False Positive alarm

                            if(!empty($bookingpress_lang_email_translation_avaliable)){
                                foreach($bookingpress_lang_email_translation_avaliable as $transval){
                                    if($transval['bookingpress_ref_column_name'] == 'bookingpress_notification_message'){
                                        $bookingpress_translated_value = $transval['bookingpress_translated_value'];
                                        if(!empty($bookingpress_translated_value)){
                                            $bookingpress_notification_data[$key]['bookingpress_notification_message'] = $bookingpress_translated_value;
                                            $bookingpress_notification_data[$key]['package_language'] = $package_language;
                                        }
                                    }
                                    if($transval['bookingpress_ref_column_name'] == 'bookingpress_notification_subject'){
                                        $bookingpress_translated_value = $transval['bookingpress_translated_value'];
                                        if(!empty($bookingpress_translated_value)){
                                            $bookingpress_notification_data[$key]['bookingpress_notification_subject'] = $bookingpress_translated_value;
                                            $bookingpress_notification_data[$key]['package_language'] = $package_language;
                                        }
                                    }                                    
                                }                                
                            }
                        }
                    }                    
                }
            }
            return $bookingpress_notification_data;
        }

        /**
         * Function for after add package order custom fields assign
         *
         * @return void
         */
        function bookingpress_after_add_package_order_func($entry_id,$inserted_booking_id){
			global $wpdb, $tbl_bookingpress_package_bookings_meta;	

            $tbl_bookingpress_package_bookings_meta = $wpdb->prefix.'bookingpress_package_bookings_meta';
            $bp_translation_lang = $this->bookingpress_get_front_current_language();                                        
            $bookingpress_db_fields = array(
                'bookingpress_entry_id' => $entry_id,
                'bookingpress_package_booking_id' => $inserted_booking_id,
                'bookingpress_package_meta_value' => $bp_translation_lang,
                'bookingpress_package_meta_key' => 'package_language',
            );            
            $wpdb->insert($tbl_bookingpress_package_bookings_meta, $bookingpress_db_fields);

        }

        /**
         * Function for open package modal after reset language data
         *
         * @return void
        */
        function bookingpress_open_package_modal_after_func(){
            ?>
                if(action == 'add'){                
                    vm.language_data = vm.language_data_org;                                               
                }
                vm.bookingpress_current_selected_lang = vm.bookingpress_current_selected_lang_org;
            <?php 
        }

        /**
         * Edit Package language Vue Data set
         *
         * @return void
        */
        function bookingpress_edit_package_more_vue_data_func(){
            ?>                
                if(typeof response.data.language_fields_data !== 'undefined'){
                    vm2.language_fields_data = response.data.language_fields_data;
                }
                if(typeof response.data.language_data !== 'undefined'){
                    vm2.language_data = response.data.language_data;
                }
                if(typeof response.data.bookingpress_package_language_section_title !== 'undefined'){
                    vm2.bookingpress_package_language_section_title = response.data.bookingpress_package_language_section_title;
                }
                if(typeof response.data.bookingpress_current_selected_lang !== 'undefined'){
                    vm2.bookingpress_current_selected_lang = response.data.bookingpress_current_selected_lang;
                }   
                if(typeof response.data.bookingpress_get_selected_languages !== 'undefined'){
                    vm2.bookingpress_get_selected_languages = response.data.bookingpress_get_selected_languages;
                }                                                
            <?php     
            }

        /**
         * Function for get package edit data
         *
         * @return void
         */
        function bookingpress_modified_get_edit_package_response_func($response,$bookingpress_edit_id){

            global $wpdb,$tbl_bookingpress_categories,$bookingpress_options,$BookingPress,$bookingpress_all_language_translation_fields_section,$bookingpress_all_language_translation_fields;
            $bookingpress_package_vue_data_fields = array();
            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_get_selected_languages = array();
            }
            $bookingpress_current_selected_lang = '';
            $bookingpress_package_vue_data_fields['empty_selected_language'] = 0;
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_package_vue_data_fields['empty_selected_language'] = 1;
            }            
            $bookingpress_package_language_translate_fields = array();        
            $bookingpress_package_language_translate_fields = apply_filters( 'bookingpress_modified_package_language_translate_fields',$bookingpress_package_language_translate_fields);             
            $bookingpress_package_language_section_title = $this->bookingpress_get_language_translation_section_label($bookingpress_package_language_translate_fields);
            $bookingpress_current_selected_lang = '';           
            $bookingpress_package_vue_data_fields['language_data'] = array();
            $bookingpress_package_vue_data_fields['language_fields_data'] = array();
            if(!empty($bookingpress_get_selected_languages)){
                $save_language_data = $this->bookingpress_get_language_data_for_backend($bookingpress_edit_id,'package');
                foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                    if(empty($bookingpress_current_selected_lang)){
                        $bookingpress_current_selected_lang = $key;
                    }            
                    foreach($bookingpress_package_language_translate_fields as $section_key=>$service_lang){                                                
                        foreach($service_lang as $field_key => $field_value){                            
                            $bookingpress_package_vue_data_fields['language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                            $bookingpress_package_vue_data_fields['language_data'][$key][$section_key][$field_key] = ''; 
                            $bookingpress_package_vue_data_fields['language_data_org'][$key][$section_key][$field_key] = '';                            
                            if(!empty($save_language_data)){
                                $search = array('bookingpress_element_type' => 'package', 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key, 'bookingpress_element_ref_id'=> $bookingpress_edit_id);
                                $keys = array_keys(array_filter($save_language_data, function ($v) use ($search) { 
                                            return $v['bookingpress_element_ref_id'] == $search['bookingpress_element_ref_id'] && $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                        }
                                ));
                                $index_val = isset($keys[0]) ? $keys[0] : '';
                                if($index_val!='' || $index_val == 0) {
                                    if(isset($save_language_data[$index_val])){
                                        $translated_data = $save_language_data[$index_val];
                                        $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                        $bookingpress_package_vue_data_fields['language_data'][$key][$section_key][$field_key] = $bp_translated_str;    
                                    }
                                }
                            }                                
                        }
                    }
                }
            }            
            $response['language_fields_data'] = $bookingpress_package_vue_data_fields['language_fields_data'];            
            $response['language_data'] = $bookingpress_package_vue_data_fields['language_data'];
            $response['bookingpress_package_language_section_title'] = $bookingpress_package_language_section_title;
            $response['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
            $response['bookingpress_current_selected_lang_org'] = $bookingpress_current_selected_lang;
            $response['open_package_translate_language'] = false;
            $response['bookingpress_get_selected_languages'] = $bookingpress_get_selected_languages;
            return $response;
        }    


        /**
         * Function for add/update package data
         *
         * @param  mixed $bookingpress_package_id
         * @return void
         */
        function bookingpress_after_add_or_update_package_func($bookingpress_package_id){
            if(isset($_POST['language_data']) && !empty($_POST['language_data'])){ //phpcs:ignore
                global $BookingPress;
                if( !empty( $_POST['language_data'] ) && !is_array( $_POST['language_data'] ) ){ //phpcs:ignore
                    $_POST['language_data'] = json_decode( stripslashes_deep( $_POST['language_data'] ), true ); //phpcs:ignore                    
                }                
                $language_data = !empty($_POST['language_data'])?array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['language_data']):array();  // phpcs:ignore 

                if(!empty($language_data)){
                    if(is_array($language_data)){  
                        foreach($language_data as $lang_key=>$single_language_data){
                            foreach($single_language_data as $lang_section=>$lang_fields){
                                foreach($lang_fields as $lang_field_key=>$bp_translated_val){
                                    $this->bookingpress_common_save_language_data($lang_key,$lang_section,$bp_translated_val,$bookingpress_package_id,$lang_field_key);
                                }                            
                            }
                        } 
                    }
                }                                                
            }    
        }

        /**
         * Function for add location postdata
         *
         * @return void
         */
        function bookingpress_add_package_more_postdata_func(){
            ?>
                postdata.language_data = vm.language_data;
            <?php 
            }

        /**
         * Function for add location language translation popup
         *
         * @return void
        */
        function bookingpress_manage_package_view_bottom_func(){            
            include BOOKINGPRESS_MULTILANGUAGE_VIEW_DIR.'bookingpress_package_language_translation_popup.php';
        }   

        /**
         * Function for add location vue method
         *
         * @return void
         */
        function bookingpress_package_dynamic_vue_methods_func(){
            ?>
            open_package_translate_language_modal(){
                var vm2 = this;
                vm2.open_package_translate_language = true;
            },   
            change_multilanguage_current_language(lang){
                var vm2 = this;            
                vm2.bookingpress_current_selected_lang = lang;
            },             
            <?php 
            }

        function bookingpress_package_header_extra_button_func(){
        ?>    
            <el-button class="bpa-btn bpa-btn--ml-translate" @click="open_package_translate_language_modal()">
                <span class="material-icons-round">translate</span>
                <?php esc_html_e('Translate', 'bookingpress-multilanguage'); ?>
            </el-button> 
        <?php     
        }
        
        /**
         * package multi-language data added
         *
         * @param  mixed $bookingpress_package_vue_data_fields
         * @return void
         */
        function bookingpress_modify_package_vue_fields_data_func($bookingpress_package_vue_data_fields){

            global $wpdb,$tbl_bookingpress_categories,$bookingpress_options,$BookingPress,$bookingpress_all_language_translation_fields_section,$bookingpress_all_language_translation_fields;
            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_get_selected_languages = array();
            }
            $bookingpress_current_selected_lang = '';
            $bookingpress_package_vue_data_fields['empty_selected_language'] = 0;
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_package_vue_data_fields['empty_selected_language'] = 1;
            }            
            $bookingpress_package_language_translate_fields = array();        
            $bookingpress_package_language_translate_fields = apply_filters( 'bookingpress_modified_package_language_translate_fields',$bookingpress_package_language_translate_fields);             
            $bookingpress_package_language_section_title = $this->bookingpress_get_language_translation_section_label($bookingpress_package_language_translate_fields);
        
            $bookingpress_current_selected_lang = '';           
            $bookingpress_package_vue_data_fields['language_data'] = array();
            $bookingpress_package_vue_data_fields['language_fields_data'] = array();
            if(!empty($bookingpress_get_selected_languages)){
                foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                    if(empty($bookingpress_current_selected_lang)){
                        $bookingpress_current_selected_lang = $key;
                    }            
                    foreach($bookingpress_package_language_translate_fields as $section_key=>$service_lang){                                                
                        foreach($service_lang as $field_key => $field_value){                            
                            $bookingpress_package_vue_data_fields['language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                            $bookingpress_package_vue_data_fields['language_data'][$key][$section_key][$field_key] = ''; 
                            $bookingpress_package_vue_data_fields['language_data_org'][$key][$section_key][$field_key] = '';      
                        }
                    }
                }
            }
        
            $bookingpress_package_vue_data_fields['bookingpress_package_language_section_title'] = $bookingpress_package_language_section_title;
            $bookingpress_package_vue_data_fields['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
            $bookingpress_package_vue_data_fields['bookingpress_current_selected_lang_org'] = $bookingpress_current_selected_lang;
            $bookingpress_package_vue_data_fields['open_package_translate_language'] = false;
            $bookingpress_package_vue_data_fields['bookingpress_get_selected_languages'] = $bookingpress_get_selected_languages;
        
            return $bookingpress_package_vue_data_fields;
        }         


        /**
         * Function for update multi-language addon
         *
         * @return void
        */
        function bookingpress_update_multi_language_data(){
            global $BookingPress, $bookingpress_multilanguage_version;
            $bookingpress_db_multilanguage_version = get_option('bookingpress_multilanguage_version', true);
            if( version_compare( $bookingpress_db_multilanguage_version, '1.4', '<' ) ){
                $bookingpress_load_multilanguage_update_file = BOOKINGPRESS_MULTILANGUAGE_DIR . '/core/views/upgrade_latest_multilanguage_data.php';
                include $bookingpress_load_multilanguage_update_file;
                $BookingPress->bookingpress_send_anonymous_data_cron();               
            }
        }


        function bookingpress_set_notification_language_data($template_content, $bookingpress_appointment_data,$notification_name, $template_type){

            global $BookingPress,$wpdb,$tbl_bookingpress_appointment_meta,$tbl_bookingpress_ml_translation,$bookingpress_lang_translation_details,$bp_translation_lang;                        
            if($template_type == 'customer'){

                $bookingpress_appointment_booking_id = (isset($bookingpress_appointment_data['bookingpress_appointment_booking_id']))?$bookingpress_appointment_data['bookingpress_appointment_booking_id']:'';
                $bookingpress_appointment_meta_value = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_appointment_meta_value FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_id = %d AND bookingpress_appointment_meta_key = %s", $bookingpress_appointment_booking_id, 'appointment_language'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_meta is table name defined globally. False Positive alarm
                                

                if(!empty($bookingpress_appointment_meta_value)){
                    $appointment_language = $bookingpress_appointment_meta_value['bookingpress_appointment_meta_value'];

                    

                    if(!empty($appointment_language)){  
                        $notification_language_compare_field = (isset($bookingpress_appointment_data['notification_language_compare_field']))?$bookingpress_appointment_data['notification_language_compare_field']:'';
                        if(!empty($notification_language_compare_field)){
                            $bookingpress_lang_email_translation_avaliable = $wpdb->get_results("SELECT bookingpress_translated_value FROM {$tbl_bookingpress_ml_translation} WHERE (bookingpress_element_type = 'manage_notification_".$template_type."' AND bookingpress_element_ref_id = '".$notification_name."' AND bookingpress_language_code = '".$appointment_language."' AND bookingpress_ref_column_name IN ('".$notification_language_compare_field."'))", ARRAY_A);//phpcs:ignore
                        }else{
                            $bookingpress_lang_email_translation_avaliable = $wpdb->get_results("SELECT bookingpress_translated_value FROM {$tbl_bookingpress_ml_translation} WHERE (bookingpress_element_type = 'manage_notification_".$template_type."' AND bookingpress_element_ref_id = '".$notification_name."' AND bookingpress_language_code = '".$appointment_language."' AND bookingpress_ref_column_name IN ('bookingpress_notification_message','bookingpress_notification_subject'))", ARRAY_A);//phpcs:ignore
                        }      
                        $has_not_empty_value = true;
                        if(!empty($bookingpress_lang_email_translation_avaliable)){
                            foreach($bookingpress_lang_email_translation_avaliable as $bpa_l_val){
                                $bookingpress_translated_value = (isset($bpa_l_val['bookingpress_translated_value']))?$bpa_l_val['bookingpress_translated_value']:'';
                                if(empty($bookingpress_translated_value)){
                                    $has_not_empty_value = false;                                                                                
                                }
                            }
                        } 
                        if(!empty($bookingpress_lang_email_translation_avaliable) && $has_not_empty_value){    
                            $bookingpress_lang_email_translation_details = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_ml_translation} WHERE (bookingpress_element_type IN ('company_setting','location','category') AND bookingpress_language_code = '".$appointment_language."') OR (bookingpress_element_type IN ('manage_notification_".$template_type."') AND bookingpress_element_ref_id = '".$notification_name."' AND bookingpress_language_code = '".$appointment_language."')", ARRAY_A); //phpcs:ignore
                            if(!empty($bookingpress_lang_email_translation_details)){
                                $bookingpress_lang_translation_details = $bookingpress_lang_email_translation_details;
                                $bp_translation_lang = $appointment_language;                               
                            }
                        }else{
                            $bookingpress_lang_translation_details = '';
                            $bp_translation_lang = '';        
                        }
                    }
                }else{
                    $bookingpress_lang_translation_details = '';
                    $bp_translation_lang = '';
                }
            }else{
                $bookingpress_lang_translation_details = '';
                $bp_translation_lang = '';
            }

        }

        function bookingpress_is_multilanguage_addon_activated($plugin,$network_activation)
        {  
            $myaddon_name = "bookingpress-multilanguage/bookingpress-multilanguage.php";

            if($plugin == $myaddon_name)
            {

                if(!(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')))
                {
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Multi Language Add-on', 'bookingpress-multilanguage');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-multilanguage'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }

                $license = trim( get_option( 'bkp_license_key' ) );
                $package = trim( get_option( 'bkp_license_package' ) );

                if( '' === $license || false === $license ) 
                {
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Multi Language Add-on', 'bookingpress-multilanguage');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-multilanguage'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }
                else
                {
                    $store_url = BOOKINGPRESS_MULTILANGUAGE_STORE_URL;
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
                            $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Multi Language Add-on', 'bookingpress-multilanguage');
                            $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-multilanguage'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
                            wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                            die;
                        }

                    }
                    else
                    {
                        deactivate_plugins($myaddon_name, FALSE);
                        $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                        $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Multi Language Add-on', 'bookingpress-multilanguage');
                        $bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-multilanguage'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
                        wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                        die;
                    }
                }
            }

        }

        /**
         * Function for modified entry data
         *
         * @param  mixed $bookingpress_entry_details
         * @param  mixed $posted_data
         * @return void
         */
        function bookingpress_modify_entry_data_before_insert_func($bookingpress_entry_details, $posted_data) {
            global $wpdb,$tbl_bookingpress_services,$BookingPress,$bookingpress_lang_translation_details,$bp_translation_lang;
            if(isset($bookingpress_entry_details['bookingpress_service_name']) && isset($bookingpress_entry_details['bookingpress_service_id'])) {                
                $bookingpress_entry_details['bookingpress_service_name'] = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$bookingpress_entry_details['bookingpress_service_name'], 'service',  'bookingpress_service_name', $bookingpress_entry_details['bookingpress_service_id'], $bp_translation_lang);
            }            
            if(isset($bookingpress_entry_details['bookingpress_extra_service_details']) && !empty($bookingpress_entry_details['bookingpress_extra_service_details'])) { 
                $bookingpress_extra_service_details = json_decode($bookingpress_entry_details['bookingpress_extra_service_details'],true);                
                if(!empty($bookingpress_extra_service_details)){
                    foreach($bookingpress_extra_service_details as $k=>$val){                                                                                                  
                        if(isset($val['bookingpress_extra_service_details']['bookingpress_extra_service_name'])){
                            $bookingpress_extra_service_details[$k]['bookingpress_extra_service_details']['bookingpress_extra_service_name'] = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$val['bookingpress_extra_service_details']['bookingpress_extra_service_name'], 'service_extra',  'bookingpress_extra_service_name',$val['bookingpress_extra_service_details']['bookingpress_extra_services_id'], $bp_translation_lang);                         
                        }
                        if(isset($val['bookingpress_extra_service_details']['bookingpress_service_description'])){
                            $bookingpress_extra_service_details[$k]['bookingpress_extra_service_details']['bookingpress_service_description'] = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$val['bookingpress_extra_service_details']['bookingpress_service_description'], 'service_extra',  'bookingpress_service_description',$val['bookingpress_extra_service_details']['bookingpress_extra_services_id'], $bp_translation_lang);                         
                        }                        
                    }
                    $bookingpress_entry_details['bookingpress_extra_service_details'] = json_encode($bookingpress_extra_service_details);
                }
            }
            return $bookingpress_entry_details;
        }

        
        /**
         * Function for add appointment meta table user language data
         *
         * @param  mixed $inserted_booking_id
         * @param  mixed $entry_id
         * @param  mixed $payment_gateway_data
         * @return void
        */
        function bookingpress_add_appointment_language_meta($inserted_booking_id, $entry_id, $payment_gateway_data){
            
            global $wpdb,$tbl_bookingpress_appointment_meta,$BookingPress;                        
            $bp_translation_lang = $this->bookingpress_get_front_current_language();                                        
            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();            
            if(!empty($bookingpress_get_selected_languages) && !empty($bp_translation_lang)){
                if(isset($bookingpress_get_selected_languages[$bp_translation_lang])){                                            
                    $bookingpress_db_fields = array(
                        'bookingpress_entry_id' => $entry_id,
                        'bookingpress_appointment_id' => $inserted_booking_id,
                        'bookingpress_appointment_meta_key' => 'appointment_language',
                        'bookingpress_appointment_meta_value' => $bp_translation_lang,
                    );
                    $wpdb->insert($tbl_bookingpress_appointment_meta, $bookingpress_db_fields);
                }
            }
        }

        /* Function for email notification placeholder replacement */        
        /**
         * Function for modified email content data
         *
         * @param  mixed $template_content
         * @param  mixed $bookingpress_appointment_data
         * @return void
         */
        function bookingpress_modify_email_content_details_filter_func($template_content, $bookingpress_appointment_data,$notification_name,$template_type){
            if (! empty($bookingpress_appointment_data) ) {                

                global $bookingpress_lang_translation_details, $bp_translation_lang, $wpdb, $tbl_bookingpress_services, $tbl_bookingpress_categories,$BookingPress, $tbl_bookingpress_payment_logs;      
                
                if(!empty($bookingpress_appointment_data['bookingpress_service_id'])) {

                    /*
                    if(!empty($bookingpress_appointment_data['bookingpress_service_id']) && !empty($bookingpress_appointment_data['bookingpress_service_name'])){
                        $bookingpress_service_id = $bookingpress_appointment_data['bookingpress_service_id'];                        
                        $bookingpress_service_name =  $bookingpress_appointment_data['bookingpress_service_name'];                        
                        $translated_service_name = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$bookingpress_service_name, 'service', 'bookingpress_service_name', $bookingpress_service_id, $bp_translation_lang);                         
                        $template_content = str_replace('%service_name%', $translated_service_name, $template_content);
                    }
                    */

                    $bookingpress_service_data= $wpdb->get_row( $wpdb->prepare ("SELECT bookingpress_category_id FROM " . $tbl_bookingpress_services." WHERE bookingpress_service_id = %d ",$bookingpress_appointment_data['bookingpress_service_id']), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_services is table name defined globally. False Positive alarm
                    $bookingpress_category_id = !empty($bookingpress_service_data['bookingpress_category_id']) ? $bookingpress_service_data['bookingpress_category_id'] : 0;                   
                    $bookingpress_category_name = '';
                    if($bookingpress_category_id == 0 ) {
                        $bookingpress_category_name = esc_html__('Uncategorized', 'bookingpress-multilanguage');
                    } else {                        
                        $categories= $wpdb->get_row($wpdb->prepare( "SELECT bookingpress_category_name FROM " . $tbl_bookingpress_categories." WHERE bookingpress_category_id = %d",$bookingpress_category_id), ARRAY_A );// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_categories is table name defined globally. False Positive alarm
                        $bookingpress_category_name = !empty($categories['bookingpress_category_name']) ? $categories['bookingpress_category_name']: '';
                        $bookingpress_category_name = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$bookingpress_category_name, 'category', 'bookingpress_category_name', $bookingpress_category_id, $bp_translation_lang); 
                    }
                    if(!empty($bookingpress_category_name)){
                        $template_content = str_replace( '%category_name%', $bookingpress_category_name, $template_content );
                    }
                    
                    $bookingpress_appointment_booking_id = ! empty($bookingpress_appointment_data['bookingpress_appointment_booking_id']) ? intval($bookingpress_appointment_data['bookingpress_appointment_booking_id']) : '';
                    $bookingpress_order_id = !empty($bookingpress_appointment_data['bookingpress_order_id']) ? intval( $bookingpress_appointment_data['bookingpress_order_id']) : '';
                    if(!empty($bookingpress_appointment_data['bookingpress_is_cart']) && $bookingpress_appointment_data['bookingpress_is_cart'] == 1 ){
                        $where_clause_condition = $wpdb->prepare( 'bookingpress_order_id = %d ', $bookingpress_order_id );
                    } else {
                        $where_clause_condition = $wpdb->prepare( 'bookingpress_appointment_booking_ref = %d ', $bookingpress_appointment_booking_id );
                    }
    
                    $log_data = array();
                    if (!empty($bookingpress_appointment_booking_id) && $bookingpress_appointment_booking_id != 0) {
                        $log_data = $wpdb->get_row( "SELECT bookingpress_payment_gateway FROM " . $tbl_bookingpress_payment_logs . " WHERE {$where_clause_condition}",ARRAY_A); // phpcs:ignore
                    }
                    $bookingpress_payment_method = !empty($log_data['bookingpress_payment_gateway']) ? $log_data['bookingpress_payment_gateway'] : '';
                    $bookingpress_payment_method_label = $this->bookingpress_selected_gateway_label_name_func($bookingpress_payment_method, $bookingpress_payment_method);
                    if(!empty($bookingpress_payment_method_label)) {
                        $template_content = str_replace( '%payment_method%', $bookingpress_payment_method_label , $template_content );                    
                    }
                }

                
            }
            return $template_content;
        }
            
        /* For complete payment transaltion */
        function modify_complate_payment_data_after_entry_create_lang_func($bookingpress_complete_payment_data_vars, $bookingpress_appointment_details){
            global $bookingpress_lang_translation_details, $bp_translation_lang;
            if(isset($bookingpress_complete_payment_data_vars['appointment_step_form_data']) && !empty($bookingpress_complete_payment_data_vars['appointment_step_form_data'])) {
                $appointment_form_data = $bookingpress_complete_payment_data_vars['appointment_step_form_data'];
                if(isset($appointment_form_data['selected_service_name']) && isset($appointment_form_data['selected_service'])) {
                    $ser_name = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$appointment_form_data['selected_service_name'], 'service', 'bookingpress_service_name', $appointment_form_data['selected_service'], $bp_translation_lang); 
                    $bookingpress_complete_payment_data_vars['appointment_step_form_data']['selected_service_name'] = $ser_name;
                }   
            }
            return $bookingpress_complete_payment_data_vars;
        }

		/**
		 * Modify [bookingpress_appointment_service] shortcode details
		 *
		 * @param  mixed $appointment_data
		 * @param  mixed $appointment_id
		 * @return void
		 */
		function bookingpress_modify_service_shortcode_details_func($appointment_data, $appointment_id){
            global $wpdb,$bookingpress_lang_translation_details,$bp_translation_lang;			
            if(isset($appointment_data['bookingpress_service_name']) && isset($appointment_data['bookingpress_service_id'])){
                $bookingpress_service_name = $appointment_data['bookingpress_service_name'];
                $appointment_data['bookingpress_service_name'] = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$bookingpress_service_name, 'service',  'bookingpress_service_name', $appointment_data['bookingpress_service_id'], $bp_translation_lang);
            }
			return $appointment_data;
		}        
        
        
        /**
         * Function for after load category service data
         *
         * @return void
         */
        function bookingpress_after_load_service_category_data_func(){
        ?>
        var vm3 = this;
        if(typeof response.data.category_language_fields_data !== 'undefined'){
            vm3.category_language_fields_data = response.data.category_language_fields_data;
        }  
        if(typeof response.data.category_language !== 'undefined'){
            vm3.category_language = response.data.category_language;
        } 
        if(typeof response.data.bookingpress_current_selected_cat_lang !== 'undefined'){
            vm3.bookingpress_current_selected_cat_lang = response.data.bookingpress_current_selected_cat_lang;
        }
        if(typeof response.data.empty_selected_language !== 'undefined'){
            vm3.empty_selected_language = response.data.empty_selected_language;
        }                                
        <?php 
        }

        /**
         * Function for modified service category data
         *
         * @param  mixed $data
         * @return void
         */
        function bookingpress_modified_service_category_response_data($data){

            global $bookingpress_all_language_translation_fields,$wpdb,$tbl_bookingpress_categories;
            $all_categories = (isset($data['items']))?$data['items']:array();
            /* Category lang variable data  */
            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_get_selected_languages = array();
            }            
            $data['category_language_fields_data'] = array();
            $data['is_display_category_save_loader'] = '0';
            $data['category_language'] = array();
            $data['bookingpress_current_selected_cat_lang'] = '';
            $data['bookingpress_get_selected_languages'] = $bookingpress_get_selected_languages;

            $data['empty_selected_language'] = 0;
            if(empty($bookingpress_get_selected_languages)){
                $data['empty_selected_language'] = 1;
            }

            $bookingpress_current_selected_cat_lang = '';               
            $bookingpress_category_language_translate_fields['category'] = $bookingpress_all_language_translation_fields['category'];
            if(!empty($bookingpress_get_selected_languages)){                
                if(!empty($all_categories)){
                 
                    $category_language_data = $this->bookingpress_get_language_data_for_backend(0,'category');
                    foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                        if(empty($bookingpress_current_selected_cat_lang)){
                            $bookingpress_current_selected_cat_lang = $key;
                            $data['bookingpress_current_selected_cat_lang'] = $bookingpress_current_selected_cat_lang;
                        }                        
                        foreach($all_categories as $category){

                            foreach($bookingpress_category_language_translate_fields as $section_key=>$category_lang){
                                foreach($category_lang as $field_key => $field_value){     

                                    if($category['category_id'] != 'add_new'){

                                        $field_value['bookingpress_category_id'] = $category['category_id'];
                                        $field_value['bookingpress_category_name'] = $category['category_name'];
                                        $data['category_language_fields_data'][$key][$section_key][$category['category_id']][$field_key] = $field_value; 
                                        $data['category_language'][$key][$section_key][$field_key][$category['category_id']] = '';            
                                        if(!empty($category_language_data)){                                
                                            $search = array('bookingpress_element_type' => $section_key, 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key, 'bookingpress_element_ref_id'=> $category['category_id']);
                                            $keys = array_keys(array_filter($category_language_data, function ($v) use ($search) { 
                                                        return $v['bookingpress_element_ref_id'] == $search['bookingpress_element_ref_id'] && $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                                    }
                                            ));
                                            $index_val = isset($keys[0]) ? $keys[0] : '';
                                            if($index_val!='' || $index_val == 0) {
                                               if(isset($category_language_data[$index_val])){
                                                    $translated_data = $category_language_data[$index_val];
                                                    $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                                    $data['category_language'][$key][$section_key][$field_key][$category['category_id']] = $bp_translated_str;
                                               } 
                                            }
                                        }

                                    }

                                }
                            }
                        }
                    }
                }
            }
            return $data;
        }

        /**
         * Function for invoice html data replace
         *
         * @return void
         */
        function bookingpress_modified_bookingpress_invoice_html_format_func($bookingpress_invoice_html_view){

            global $wpdb,$bookingpress_lang_translation_details,$bp_translation_lang, $tbl_bookingpress_ml_translation;
            if(empty($bookingpress_lang_translation_details)){                
                $bp_translation_lang = $this->bookingpress_get_front_current_language();                                                  
                $bookingpress_lang_translation_details = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_ml_translation} WHERE bookingpress_language_code = %s AND bookingpress_element_type = 'invoice_setting'",$bp_translation_lang), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_ml_translation is table name defined globally. False Positive alarm                            
            }
            $bookingpress_invoice_html_view = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$bookingpress_invoice_html_view, 'invoice_setting',  'bookingpress_invoice_html_format', 0, $bp_translation_lang);
            return $bookingpress_invoice_html_view;

        }

        /**
         * My Appointment change extra service name
         *
         * @param  mixed $bookingpress_tmp_extra_service_data
         * @return void
         */
        function bookingpress_modify_my_appointment_extra_service_data_func($bookingpress_tmp_extra_service_data){
            if(!empty($bookingpress_tmp_extra_service_data)) {
                global $bookingpress_lang_translation_details, $bp_translation_lang;
                foreach($bookingpress_tmp_extra_service_data as $key => $val) {
                    if(isset($val['bookingpress_extra_service_details']['bookingpress_extra_service_name']) && isset($val['bookingpress_extra_service_details']['bookingpress_extra_services_id'])){
                        $extra_service_name = $val['bookingpress_extra_service_details']['bookingpress_extra_service_name'];
                        $extra_service_id = $val['bookingpress_extra_service_details']['bookingpress_extra_services_id'];
                        $extra_ser_name = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$extra_service_name, 'service_extra',  'bookingpress_extra_service_name', $extra_service_id, $bp_translation_lang); 
                        $bookingpress_tmp_extra_service_data[$key]['bookingpress_extra_service_details']['bookingpress_extra_service_name'] = $extra_ser_name;
                    }
                }
            }
            return $bookingpress_tmp_extra_service_data;
        }
                 
        /**
         * My Appointment modified appointment data
         *
         * @param  mixed $appointments_data
         * @param  mixed $id
         * @return void
         */
        function bookingpress_modify_my_appointment_data_func($appointments_data, $id){
            if(!empty($appointments_data)) {
                global $bookingpress_lang_translation_details,$bp_translation_lang;
                if(isset($appointments_data[$id]['bookingpress_service_name']) && isset($appointments_data[$id]['bookingpress_service_id'])) {
                    $service_name = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$appointments_data[$id]['bookingpress_service_name'], 'service',  'bookingpress_service_name', $appointments_data[$id]['bookingpress_service_id'], $bp_translation_lang);
                    $appointments_data[$id]['bookingpress_service_name'] = $service_name;
                }
            }
            return $appointments_data;
        }


		/**
		 * Function for arrange form fields
		 *
		 * @param  mixed $form_fields_data
		 * @param  mixed $db_field_options
		 * @return void
		 */
		function bookingpress_arrange_form_fields_func( $form_fields_data, $db_field_options ) {            
            global $bookingpress_lang_translation_details, $bp_translation_lang,$BookingPress;            
            if(isset($form_fields_data['field_options']['inner_fields']) && !empty($form_fields_data['field_options']['inner_fields'])){

                foreach($form_fields_data['field_options']['inner_fields'] as $keyl=>$vall){
                    
                    if(isset($vall['field_options']['inner_fields']) && !empty($vall['field_options']['inner_fields'])){
                        foreach($vall['field_options']['inner_fields'] as $key2=>$val2){

                            if(isset($val2['label'])){                

                                $label = $val2['label'];
                                $label = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$label, 'custom_form_fields',  'bookingpress_field_label',$val2['id'] , $bp_translation_lang);                
                                $label = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$label, 'customer_custom_form_fields',  'bookingpress_field_label',$val2['id'] , $bp_translation_lang);                
                                $form_fields_data['field_options']['inner_fields'][$keyl]['field_options']['inner_fields'][$key2]['label'] =  $label;
                            }
                            if(isset($val2['placeholder'])){                
                                $placeholder = $val2['placeholder'];
                                $placeholder = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$placeholder, 'custom_form_fields',  'bookingpress_field_placeholder',$val2['id'] , $bp_translation_lang);                
                                $placeholder = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$placeholder, 'customer_custom_form_fields',  'bookingpress_field_placeholder',$val2['id'] , $bp_translation_lang);                
                                $form_fields_data['field_options']['inner_fields'][$keyl]['field_options']['inner_fields'][$key2]['placeholder'] =  $placeholder;
                            }
                            if(isset($val2['error_message'])){                
                                $error_message = $val2['error_message'];                                
                                $error_message = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$error_message, 'customer_custom_form_fields',  'bookingpress_field_error_message',$val2['id'] , $bp_translation_lang);                
                                $error_message = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$error_message, 'custom_form_fields',  'bookingpress_field_error_message',$val2['id'] , $bp_translation_lang);
                                $form_fields_data['field_options']['inner_fields'][$keyl]['field_options']['inner_fields'][$key2]['error_message'] =  $error_message;             
                            }   
                            
                            if(isset($val2['field_values'])){  

                                if(!empty($val2['field_values'])){
                                    $bookingpress_field_values_org = $val2['field_values'];
                                    $bookingpress_field_values = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,json_encode($val2['field_values']), 'custom_form_fields',  'bookingpress_field_values',$val2['id'] , $bp_translation_lang);    
                                    
                                    if(!is_array($bookingpress_field_values)){
                                        $bookingpress_field_values = json_decode($bookingpress_field_values,true);
                                    }   
                                    if(is_array($bookingpress_field_values) && !empty($bookingpress_field_values_org)){                                    
                                        foreach($bookingpress_field_values_org as $fieldkey=>$fieldv){                                        
                                            if(isset($bookingpress_field_values[$fieldkey]['value']) && !empty($bookingpress_field_values[$fieldkey]['value'])){
                                                $bookingpress_field_values_org[$fieldkey]['value'] = (isset($bookingpress_field_values[$fieldkey]['value']))?$bookingpress_field_values[$fieldkey]['value']: $bookingpress_field_values_org[$fieldkey]['value'];
                                            }
                                            if(isset($bookingpress_field_values[$fieldkey]['label']) && !empty($bookingpress_field_values[$fieldkey]['label'])){
                                                $bookingpress_field_values_org[$fieldkey]['label'] = (isset($bookingpress_field_values[$fieldkey]['label']))?$bookingpress_field_values[$fieldkey]['label']: $bookingpress_field_values_org[$fieldkey]['label'];
                                            }                                        
                                        }
                                        $form_fields_data['field_options']['inner_fields'][$keyl]['field_options']['inner_fields'][$key2]['field_values'] = $bookingpress_field_values_org;
                                    }                     
                                }
                            }                             

                        }
                    }

                    if(isset($vall['label'])){                
                        $label = $vall['label'];
                        $label = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$label, 'customer_custom_form_fields',  'bookingpress_field_label',$vall['id'] , $bp_translation_lang);
                        $label = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$label, 'custom_form_fields',  'bookingpress_field_label',$vall['id'] , $bp_translation_lang);                                                        
                        $form_fields_data['field_options']['inner_fields'][$keyl]['label'] =  $label;
                    }
                    if(isset($vall['placeholder'])){                
                        $placeholder = $vall['placeholder'];
                        $placeholder = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$placeholder, 'custom_form_fields',  'bookingpress_field_placeholder',$vall['id'] , $bp_translation_lang);                
                        $placeholder = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$placeholder, 'customer_custom_form_fields',  'bookingpress_field_placeholder',$vall['id'] , $bp_translation_lang);                
                        $form_fields_data['field_options']['inner_fields'][$keyl]['placeholder'] =  $placeholder;
                    }           
                    if(isset($vall['error_message'])){                
                        $error_message = $vall['error_message'];                                
                        $error_message = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$error_message, 'customer_custom_form_fields',  'bookingpress_field_error_message',$vall['id'] , $bp_translation_lang);                
                        $error_message = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$error_message, 'custom_form_fields',  'bookingpress_field_error_message',$vall['id'] , $bp_translation_lang);
                        $form_fields_data['field_options']['inner_fields'][$keyl]['error_message'] =  $error_message;                
                    }  
         
                    if(isset($vall['field_values'])){                
                        if(!empty($vall['field_values'])){
                            $bookingpress_field_values_org = $vall['field_values'];
                            $bookingpress_field_values = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,json_encode($vall['field_values']), 'custom_form_fields',  'bookingpress_field_values',$vall['id'] , $bp_translation_lang);                                        
                            if(!is_array($bookingpress_field_values)){
                                $bookingpress_field_values = json_decode($bookingpress_field_values,true);
                            }                    
                            if(is_array($bookingpress_field_values) && !empty($bookingpress_field_values_org)){                                    
                                foreach($bookingpress_field_values_org as $fieldkey=>$fieldv){                                        
                                    if(isset($bookingpress_field_values[$fieldkey]['value']) && !empty($bookingpress_field_values[$fieldkey]['value'])){
                                        $bookingpress_field_values_org[$fieldkey]['value'] = (isset($bookingpress_field_values[$fieldkey]['value']))?$bookingpress_field_values[$fieldkey]['value']: $bookingpress_field_values_org[$fieldkey]['value'];
                                    }
                                    if(isset($bookingpress_field_values[$fieldkey]['label']) && !empty($bookingpress_field_values[$fieldkey]['label'])){
                                        $bookingpress_field_values_org[$fieldkey]['label'] = (isset($bookingpress_field_values[$fieldkey]['label']))?$bookingpress_field_values[$fieldkey]['label']: $bookingpress_field_values_org[$fieldkey]['label'];
                                    }                                        
                                }
                                $form_fields_data['field_options']['inner_fields'][$keyl]['field_values'] = $bookingpress_field_values_org;
                            }                     
                        }
                    }  

                }
            }

            if(isset($form_fields_data['label'])){                
                $label = $form_fields_data['label'];
                $label = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$label, 'custom_form_fields',  'bookingpress_field_label',$form_fields_data['id'] , $bp_translation_lang);                
                $label = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$label, 'customer_custom_form_fields',  'bookingpress_field_label',$form_fields_data['id'] , $bp_translation_lang);                
                $form_fields_data['label'] =  $label;
            }
            if(isset($form_fields_data['placeholder'])){                
                $placeholder = $form_fields_data['placeholder'];
                $placeholder = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$placeholder, 'custom_form_fields',  'bookingpress_field_placeholder',$form_fields_data['id'] , $bp_translation_lang);                
                $placeholder = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$placeholder, 'customer_custom_form_fields',  'bookingpress_field_placeholder',$form_fields_data['id'] , $bp_translation_lang);                
                $form_fields_data['placeholder'] =  $placeholder;
            }           
            if(isset($form_fields_data['error_message'])){                
                $error_message = $form_fields_data['error_message'];                                
                $error_message = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$error_message, 'customer_custom_form_fields',  'bookingpress_field_error_message',$form_fields_data['id'] , $bp_translation_lang);                
                $error_message = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$error_message, 'custom_form_fields',  'bookingpress_field_error_message',$form_fields_data['id'] , $bp_translation_lang);
                $form_fields_data['error_message'] =  $error_message;                
            }  
 
            if(isset($form_fields_data['field_values'])){                
                if(!empty($form_fields_data['field_values'])){
                    $bookingpress_field_values_org = $form_fields_data['field_values'];
                    $bookingpress_field_values = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,json_encode($form_fields_data['field_values']), 'customer_custom_form_fields',  'bookingpress_field_values',$form_fields_data['id'] , $bp_translation_lang);  
                    if(empty($bookingpress_field_values)){
                        $bookingpress_field_values = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,json_encode($form_fields_data['field_values']), 'custom_form_fields',  'bookingpress_field_values',$form_fields_data['id'] , $bp_translation_lang); 
                    }                                      
                    if(!is_array($bookingpress_field_values)){
                        $bookingpress_field_values = json_decode($bookingpress_field_values,true);
                    }                    
                    if(is_array($bookingpress_field_values) && !empty($bookingpress_field_values_org)){                                    
                        foreach($bookingpress_field_values_org as $fieldkey=>$fieldv){                                        
                            if(isset($bookingpress_field_values[$fieldkey]['value']) && !empty($bookingpress_field_values[$fieldkey]['value'])){
                                $bookingpress_field_values_org[$fieldkey]['value'] = (isset($bookingpress_field_values[$fieldkey]['value']))?$bookingpress_field_values[$fieldkey]['value']: $bookingpress_field_values_org[$fieldkey]['value'];
                            }
                            if(isset($bookingpress_field_values[$fieldkey]['label']) && !empty($bookingpress_field_values[$fieldkey]['label'])){
                                $bookingpress_field_values_org[$fieldkey]['label'] = (isset($bookingpress_field_values[$fieldkey]['label']))?$bookingpress_field_values[$fieldkey]['label']: $bookingpress_field_values_org[$fieldkey]['label'];
                            }                                        
                        }
                        $form_fields_data['field_values'] = $bookingpress_field_values_org;
                    }                     
                }
            }           
            return $form_fields_data;            
        }


        function bookingpress_replace_notification_content_language_wise_func($notification_field_data,$notification_replace_field,$notification_receiver_type,$notification_type,$bookingpress_appointment_data){
            
            global $wpdb,$bookingpress_lang_translation_details,$bp_translation_lang, $BookingPress,$tbl_bookingpress_appointment_meta,$tbl_bookingpress_ml_translation;
            $section_key = 'manage_notification_'.$notification_receiver_type;            
            if($notification_receiver_type == 'customer'){ 

                $bookingpress_appointment_booking_id = (isset($bookingpress_appointment_data['bookingpress_appointment_booking_id']))?$bookingpress_appointment_data['bookingpress_appointment_booking_id']:'';
                $bookingpress_appointment_meta_value = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_appointment_meta_value FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_id = %d AND bookingpress_appointment_meta_key = %s", $bookingpress_appointment_booking_id, 'appointment_language'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_meta is table name defined globally. False Positive alarm
                if(!empty($bookingpress_appointment_meta_value)){                    
                    $appointment_language = $bookingpress_appointment_meta_value['bookingpress_appointment_meta_value'];
                    if(!empty($appointment_language)){
                        $notification_replace_data_var = implode(',',$notification_replace_field);
                        $bookingpress_lang_email_translation_avaliable = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_ml_translation} WHERE (bookingpress_element_type = 'manage_notification_".$notification_receiver_type."' AND bookingpress_element_ref_id = '".$notification_type."' AND bookingpress_language_code = '".$appointment_language."' AND bookingpress_ref_column_name IN ('".$notification_replace_data_var."'))", ARRAY_A);//phpcs:ignore
                        if(!empty($bookingpress_lang_email_translation_avaliable)){
                            $bookingpress_lang_email_translation_details = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_ml_translation} WHERE (bookingpress_element_type IN ('company_setting','location') AND bookingpress_language_code = '".$appointment_language."') OR (bookingpress_element_type IN ('manage_notification_".$notification_receiver_type."') AND bookingpress_element_ref_id = '".$notification_type."' AND bookingpress_language_code = '".$appointment_language."')", ARRAY_A); //phpcs:ignore
                            if(!empty($bookingpress_lang_email_translation_details)){
                                $bookingpress_lang_translation_details = $bookingpress_lang_email_translation_details;
                                $bp_translation_lang = $appointment_language;
                            } 
                        }else{
                            $bookingpress_lang_translation_details = '';
                            $bp_translation_lang = '';
                        }
                    }
                }
                if(!empty($notification_replace_field) && !empty($notification_field_data)){
                    foreach($notification_replace_field as $notification_replace){
                        if(isset($notification_field_data[$notification_replace])){
                            $orignal_str = $notification_field_data[$notification_replace];
                            $notification_field_data[$notification_replace] = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$orignal_str, $section_key,  $notification_replace, $notification_type, $bp_translation_lang);
                        }
                    }
                }
           }else{
                $bookingpress_lang_translation_details = '';
                $bp_translation_lang = '';
           }      
           return $notification_field_data;
        }

        function bookingpress_modify_field_data_before_prepare_func($bookingpress_form_fields)
        {
           if(!is_admin() ){
                if(!empty($bookingpress_form_fields)) {
                    global $bookingpress_lang_translation_details, $bp_translation_lang;
                    foreach($bookingpress_form_fields as $bookingpress_form_fields_id => $bookingpress_form_fields_data){
                        if(isset($bookingpress_form_fields_data['bookingpress_field_label']) && isset($bookingpress_form_fields_data['bookingpress_form_field_id'])) {
                            $field_label = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$bookingpress_form_fields_data['bookingpress_field_label'], 'custom_form_fields',  'bookingpress_field_label', $bookingpress_form_fields_data['bookingpress_form_field_id'], $bp_translation_lang);
                            $bookingpress_form_fields_data['bookingpress_field_label'] = $field_label;
                        }
                        if(isset($bookingpress_form_fields_data['bookingpress_field_placeholder']) && isset($bookingpress_form_fields_data['bookingpress_form_field_id'])) {
                            $field_placeholder = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$bookingpress_form_fields_data['bookingpress_field_placeholder'], 'custom_form_fields',  'bookingpress_field_placeholder', $bookingpress_form_fields_data['bookingpress_form_field_id'], $bp_translation_lang);
                            $bookingpress_form_fields_data['bookingpress_field_placeholder'] = $field_placeholder;
                        }
                        if(isset($bookingpress_form_fields_data['bookingpress_field_error_message']) && isset($bookingpress_form_fields_data['bookingpress_form_field_id'])) {
                            $field_error_message = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$bookingpress_form_fields_data['bookingpress_field_error_message'], 'custom_form_fields',  'bookingpress_field_error_message', $bookingpress_form_fields_data['bookingpress_form_field_id'], $bp_translation_lang);
                            $bookingpress_form_fields_data['bookingpress_field_error_message'] = $field_error_message;
                        }
                        if(isset($bookingpress_form_fields_data['bookingpress_field_values']) && isset($bookingpress_form_fields_data['bookingpress_form_field_id'])) {                            
                            $bookingpress_field_values = $bookingpress_form_fields_data['bookingpress_field_values'];
                            if(!empty($bookingpress_field_values)){                                
                                $bookingpress_field_values_org = json_decode($bookingpress_field_values,true);   
                                $bookingpress_field_values = json_encode($bookingpress_field_values,true);                                                             
                                $bookingpress_field_values = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$bookingpress_field_values, 'custom_form_fields',  'bookingpress_field_values', $bookingpress_form_fields_data['bookingpress_form_field_id'], $bp_translation_lang);
                                if(!is_array($bookingpress_field_values)){
                                    $bookingpress_field_values = json_decode($bookingpress_field_values,true);
                                }                                   
                                if(is_array($bookingpress_field_values)){                                    
                                    foreach($bookingpress_field_values_org as $fieldkey=>$fieldv){                                        
                                        if(isset($bookingpress_field_values[$fieldkey]['value']) && !empty($bookingpress_field_values[$fieldkey]['value'])){
                                            $bookingpress_field_values_org[$fieldkey]['value'] = (isset($bookingpress_field_values[$fieldkey]['value']))?$bookingpress_field_values[$fieldkey]['value']: $bookingpress_field_values_org[$fieldkey]['value'];
                                        }
                                        if(isset($bookingpress_field_values[$fieldkey]['label']) && !empty($bookingpress_field_values[$fieldkey]['label'])){
                                            $bookingpress_field_values_org[$fieldkey]['label'] = (isset($bookingpress_field_values[$fieldkey]['label']))?$bookingpress_field_values[$fieldkey]['label']: $bookingpress_field_values_org[$fieldkey]['label'];
                                        }                                        
                                    }
                                    $bookingpress_form_fields_data['bookingpress_field_values'] = json_encode($bookingpress_field_values_org);
                                }                                                                                                
                            }                            
                        }
                        $bookingpress_form_fields[$bookingpress_form_fields_id] = $bookingpress_form_fields_data;
                    }                    
                }
            }
            return $bookingpress_form_fields;
        }


        
        /**
         * Function for send email notification in front side with appointment selected language
         *
         * @return void
         */
        function bookingpress_modify_email_template_notification_data_func($bookingpress_email_data, $template_type, $notification_name, $bookingpress_appointment_data, $notification_type){
            
            global $wpdb,$bookingpress_lang_translation_details,$bp_translation_lang, $BookingPress,$tbl_bookingpress_appointment_meta,$tbl_bookingpress_ml_translation;
            if(!empty($bookingpress_email_data)){

                if($template_type == 'customer'){

                    $bookingpress_appointment_booking_id = (isset($bookingpress_appointment_data['bookingpress_appointment_booking_id']))?$bookingpress_appointment_data['bookingpress_appointment_booking_id']:'';
                    $bookingpress_appointment_meta_value = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_appointment_meta_value FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_id = %d AND bookingpress_appointment_meta_key = %s", $bookingpress_appointment_booking_id, 'appointment_language'), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_meta is table name defined globally. False Positive alarm 
                    if(!empty($bookingpress_appointment_meta_value)){
                                                
                        $appointment_language = $bookingpress_appointment_meta_value['bookingpress_appointment_meta_value'];
                        if(!empty($appointment_language)){                                                                                                    
                            $bookingpress_lang_email_translation_avaliable = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_ml_translation} WHERE (bookingpress_element_type = %s AND bookingpress_element_ref_id = %s AND bookingpress_language_code = %s AND bookingpress_ref_column_name IN ('bookingpress_notification_message','bookingpress_notification_subject'))",'manage_notification_'.$template_type,$notification_name,$appointment_language), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_ml_translation is table name defined globally. False Positive alarm
                            $has_not_empty_value = true;
                            if(!empty($bookingpress_lang_email_translation_avaliable)){
                                foreach($bookingpress_lang_email_translation_avaliable as $bpa_l_val){
                                    $bookingpress_translated_value = (isset($bpa_l_val['bookingpress_translated_value']))?$bpa_l_val['bookingpress_translated_value']:'';
                                    if(empty($bookingpress_translated_value)){
                                        $has_not_empty_value = false;                                                                                
                                    }
                                }
                            }                                                        
                            if(!empty($bookingpress_lang_email_translation_avaliable) && $has_not_empty_value){
                                
                                /*
                                $bookingpress_lang_email_translation_details = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_ml_translation} WHERE (bookingpress_element_type IN ('company_setting','location') AND bookingpress_language_code = '".$appointment_language."') OR (bookingpress_element_type IN ('manage_notification_".$template_type."') AND bookingpress_element_ref_id = '".$notification_name."' AND bookingpress_language_code = '".$appointment_language."')", ARRAY_A);                            
                                if(!empty($bookingpress_lang_email_translation_details)){
                                    $bookingpress_lang_translation_details = $bookingpress_lang_email_translation_details;
                                    $bp_translation_lang = $appointment_language;
                                */    
                                    $section_key = 'manage_notification_'.$template_type;                                
                                    if(isset($bookingpress_email_data['bookingpress_notification_subject'])){   
                                        
                                        $bookingpress_lang_translation_details = $bookingpress_lang_email_translation_avaliable;
                                        $bp_translation_lang  = $appointment_language;
                                        $orignal_str = $bookingpress_email_data['bookingpress_notification_subject'];
                                        $bookingpress_notification_subject = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$orignal_str, $section_key,  'bookingpress_notification_subject', $notification_name, $bp_translation_lang);
                                        $bookingpress_email_data['bookingpress_notification_subject'] = $bookingpress_notification_subject;
                                    }
                                    if(isset($bookingpress_email_data['bookingpress_notification_message'])){                    
                                        $orignal_str = $bookingpress_email_data['bookingpress_notification_message'];
                                        $bookingpress_notification_message = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$orignal_str, $section_key,  'bookingpress_notification_message', $notification_name, $bp_translation_lang);
                                        $bookingpress_email_data['bookingpress_notification_message'] = $bookingpress_notification_message;
                                    }    
                                /*    
                                }*/
                            }else{
                                $bookingpress_lang_translation_details = array();
                                $bp_translation_lang = 'none';                                
                            }
                            

                        }
                    }

                }else{

                    $bookingpress_lang_translation_details = array();
                    $bp_translation_lang = 'none';   

                }
                                

            }
                                    
            return $bookingpress_email_data;

        }
        
        /**
         * Function for before PDF generate load language data
         *
         * @return void
         */
        function bookingpress_invoice_pdf_generate_before_fun($log_id){

            global $wpdb, $tbl_bookingpress_ml_translation,$bookingpress_lang_translation_details,$bp_translation_lang,$tbl_bookingpress_appointment_meta,$tbl_bookingpress_appointment_bookings;
            $bp_translation_lang = $this->bookingpress_get_front_current_language();
            $bookingpress_appointment_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_appointment_booking_id FROM {$tbl_bookingpress_appointment_bookings} WHERE bookingpress_payment_id = %d", $log_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_bookings is table name defined globally. False Positive alarm
            if(!empty($bookingpress_appointment_details)){
                $bookingpress_appointment_booking_id = $bookingpress_appointment_details['bookingpress_appointment_booking_id'];
                $bookingpress_appointment_meta_value = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_appointment_meta_value FROM {$tbl_bookingpress_appointment_meta} WHERE bookingpress_appointment_id = %d AND bookingpress_appointment_meta_key = %s", $bookingpress_appointment_booking_id, 'appointment_language'), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_appointment_meta is table name defined globally. False Positive alarm                                                
                if(!empty($bookingpress_appointment_meta_value)){
                    $appointment_language = $bookingpress_appointment_meta_value['bookingpress_appointment_meta_value'];
                    $bp_translation_lang = $appointment_language;
                    $bookingpress_lang_translation_details = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_ml_translation} WHERE bookingpress_language_code = %s ",$bp_translation_lang), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_ml_translation is table name defined globally. False Positive alarm                                                 
                }
            }

        }

        function bookingpress_front_booking_form_load_before_fun(){
            $this->bookingpress_set_front_global_language_data();
        }

        /* Front Settings data change */
        function bookingpress_modified_get_settings_func($return_setting_data,$setting_type,$setting_name){
            global $bookingpress_lang_translation_details,$bp_translation_lang, $BookingPress;
            
            if($bp_translation_lang==''){
                $bp_translation_lang = $this->bookingpress_get_front_current_language();
            }
            if(is_string($return_setting_data) && !empty($setting_type) && !empty($setting_name)){
                $return_setting_data = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$return_setting_data, $setting_type,  $setting_name, 0, $bp_translation_lang);
            }            
            return $return_setting_data;
        }        

        /* Front Customized Settings data change */
        function bookingpress_modified_get_customize_settings_func($return_customize_setting_data,$setting_type,$setting_name){
            global $bookingpress_lang_translation_details,$bp_translation_lang;               
            if($bp_translation_lang==''){
                $bp_translation_lang = $this->bookingpress_get_front_current_language();
            }        
            $customize_allow_setting_type = array('booking_my_booking','booking_form');                
            $customize_allow_setting_type = apply_filters( 'bookingpress_multilanguage_customize_allow_setting_type', $customize_allow_setting_type );       
            if(is_string($return_customize_setting_data) && !empty($setting_type) && !empty($setting_name) && in_array($setting_type,$customize_allow_setting_type)){
                $return_customize_setting_data = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$return_customize_setting_data, $setting_type,  $setting_name, 0, $bp_translation_lang);
            }            
            return $return_customize_setting_data;
        }


        /**
         * Function For Service Extra Language Data Save 
         *
         * @param  mixed $extra_service_ids
         * @param  mixed $extra_service_key
         * @param  mixed $posted_data
         * @return void
         */
        function bookingpress_after_save_service_extra_func($extra_service_ids,$extra_service_key,$posted_data){
            if(isset($posted_data['service_extra_language_data']) && !empty($posted_data['service_extra_language_data'])){
                $service_extra_language_data = $posted_data['service_extra_language_data'];                  
                $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
                if(!empty($bookingpress_get_selected_languages)){
                    foreach($bookingpress_get_selected_languages as $lang_key=>$langd){
                        if(isset($service_extra_language_data[$lang_key]['service_extra']['bookingpress_extra_service_name'][$extra_service_key])){
                            $bp_translated_val = $service_extra_language_data[$lang_key]['service_extra']['bookingpress_extra_service_name'][$extra_service_key];
                            //if(!empty($bp_translated_val)){
                                $this->bookingpress_common_save_language_data($lang_key,'service_extra',$bp_translated_val,$extra_service_ids,'bookingpress_extra_service_name');
                            //}                            
                        }
                        if(isset($service_extra_language_data[$lang_key]['service_extra']['bookingpress_service_description'][$extra_service_key])){
                            $bp_translated_val = $service_extra_language_data[$lang_key]['service_extra']['bookingpress_service_description'][$extra_service_key];
                            //if(!empty($bp_translated_val)){
                                $this->bookingpress_common_save_language_data($lang_key,'service_extra',$bp_translated_val,$extra_service_ids,'bookingpress_service_description');
                            //}
                        }                        
                    }
                }    
            }
        }    

        /**
         * Function for weglot locale set
         *
         * @param  mixed $lang
         * @return void
         */
        function weglot_set_locale( $lang ) {
            if ( function_exists( 'weglot_get_current_language' ) ) {
                $current_language = weglot_get_current_language();
                switch ( $current_language ) {
                    case 'fr':
                        return 'fr_FR';
                        break;
                    case 'en':
                        return 'en_US';
                        break;
                    case 'no':
                        return 'nb_NO';
                        break;
                    default:
                        return $lang;
                }
            }
        
            return $lang;
        }        
        
        /**
         * Function for language popup language data not found
         *
         * @return void
         */
        function bookingpress_multi_language_popup_translate_language_not_found_func(){
        ?>
            <div class="bpa-data-empty-view bpa-data-empty-view--vertical" v-if="empty_selected_language == 1">
                <div class="bpa-ev-left-vector">
                    <picture>
                        <source srcset="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.webp'); ?>"
                            type="image/webp">
                        <img src="<?php echo esc_url(BOOKINGPRESS_IMAGES_URL . '/data-grid-empty-view-vector.png'); ?>">
                    </picture>
                </div>
                <div class="bpa-ev-right-content">
                    <h4><?php esc_html_e('Language Not Found.', 'bookingpress-multilanguage'); ?></h4>
                    <p><?php esc_html_e('Please select language from general setting.', 'bookingpress-multilanguage'); ?></p>
                </div>
            </div>
        <?php 
        }

        /**
         * Function for set front global language data 
         *
         * @return void
         */
        function bookingpress_set_front_global_language_data($has_return = false){
            $bp_translation_lang = $this->bookingpress_get_front_current_language();
            if (!is_admin() || ( !defined( 'DOING_AJAX') || ( defined('DOING_AJAX')  && true == DOING_AJAX ) ) ) {
                $bookingpress_add_language_data_in_ajax = true;
                if(defined('DOING_AJAX')){
                    $HTTP_REFERER = (isset($_SERVER['HTTP_REFERER']))?$_SERVER['HTTP_REFERER']:'none';
                    if(empty($HTTP_REFERER)){ $HTTP_REFERER='none'; }
                    if (strpos($HTTP_REFERER,'wp-admin') !== false) {
                        $bookingpress_add_language_data_in_ajax = false;
                    }
                }
                if($bookingpress_add_language_data_in_ajax){
                    global $wpdb, $tbl_bookingpress_ml_translation,$bookingpress_lang_translation_details;
                    $bp_translation_lang = $this->bookingpress_get_front_current_language();                                                  
                    $bookingpress_lang_translation_details = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_ml_translation} WHERE bookingpress_language_code = %s ",$bp_translation_lang), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_ml_translation is table name defined globally. False Positive alarm           
                    if($has_return){
                        return $bookingpress_lang_translation_details;
                    }
                }                           
            }    
            if($has_return){
                return '';
            }
            
        }

        /**
         * Function for all fileds
         *
         * @return void
        */
        function bookingpress_all_language_translation_fields(){

            $bookingpress_singular_staffmember_name = '';
            $no_staff_member_selected_label = esc_html__('No','bookingpress-multilanguage')." ".esc_html($bookingpress_singular_staffmember_name)." ".esc_html__('selected for the booking', 'bookingpress-multilanguage');
            $bookingpress_all_language_translation_fields = array(                
                'service' => array(
                    'bookingpress_service_name' => array('field_type'=>'text','field_label'=>__('Service Name', 'bookingpress-multilanguage')),
                    'bookingpress_service_description' => array('field_type'=>'textarea','field_label'=>__('Service Description', 'bookingpress-multilanguage')),                    
                ),
                'service_extra' => array(
                    'bookingpress_extra_service_name' => array('field_type'=>'text','field_label'=>__('Service Name', 'bookingpress-multilanguage')),
                    'bookingpress_service_description' => array('field_type'=>'textarea','field_label'=>__('Service Description', 'bookingpress-multilanguage')),                    
                ),                
                'category' => array(
                    'bookingpress_category_name' => array('field_type'=>'text','field_label'=>__('Category Name', 'bookingpress-multilanguage')),                    
                ),

                'manage_notification_customer' => array(
                    'bookingpress_notification_subject' => array('field_type'=>'text','field_label'=>__('Email Subject', 'bookingpress-multilanguage'),'save_field_type'=>'manage_notification_customer'),
                    'bookingpress_notification_message' => array('field_type'=>'textarea','field_label'=>__('Email Message', 'bookingpress-multilanguage'),'save_field_type'=>'manage_notification_customer'),
                ),
                
                /*
                'manage_notification_employee' => array(
                    'bookingpress_notification_subject' => array('field_type'=>'text','field_label'=>__('Email Subject', 'bookingpress-multilanguage'),'save_field_type'=>'manage_notification_customer'),
                    'bookingpress_notification_message' => array('field_type'=>'textarea','field_label'=>__('Email Message', 'bookingpress-multilanguage'),'save_field_type'=>'manage_notification_customer'),
                ),
                */

                /* Invoice settings */                
                'invoice_setting' => array(
                    'bookingpress_invoice_html_format' => array('field_type'=>'textarea','field_label'=>__('Invoice', 'bookingpress-multilanguage'),'save_field_type'=>'invoice_setting'),                    
                ),

                /* Company settings */                
                'company_setting' => array(
                    'company_name' => array('field_type'=>'text','field_label'=>__('Company Name', 'bookingpress-multilanguage'),'save_field_type'=>'company_setting'),
                    'company_address' => array('field_type'=>'text','field_label'=>__('Company Address', 'bookingpress-multilanguage'),'save_field_type'=>'company_setting'),
                ),

                /* Message settings */                
                'message_setting' => array(
                    'no_service_selected_for_the_booking' => array('field_type'=>'text','field_label'=>__('No service selected for the booking', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),
                    'no_appointment_date_selected_for_the_booking' => array('field_type'=>'text','field_label'=>__('No appointment date selected for the booking', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),
                    'no_appointment_time_selected_for_the_booking' => array('field_type'=>'text','field_label'=>__('No appointment time selected for the booking', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),                    
                    'no_payment_method_is_selected_for_the_booking' => array('field_type'=>'text','field_label'=>__('No payment method is selected for the booking', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),
                    'duplicate_email_address_found' => array('field_type'=>'text','field_label'=>__('Duplicate email address found', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),
                    'unsupported_currecy_selected_for_the_payment' => array('field_type'=>'text','field_label'=>__('Unsupported currency selected for the payment', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),
                    'duplidate_appointment_time_slot_found' => array('field_type'=>'text','field_label'=>__('Time slot already booked', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),
                    'no_payment_method_available' => array('field_type'=>'text','field_label'=>__('No payment method available', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),
                    'no_staffmember_selected_for_the_booking' => array('field_type'=>'text','field_label'=> $no_staff_member_selected_label,'save_field_type'=>'message_setting'),
                    'bookingpress_card_details_error_msg' => array('field_type'=>'text','field_label'=>__('Please fill all fields value of card details', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),
                    'payment_token_failure_message' => array('field_type'=>'text','field_label'=>__('Payment token failure message', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),
                    'payment_already_paid_message' => array('field_type'=>'text','field_label'=>__('Payment already paid message', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),
                    'complete_payment_success_message' => array('field_type'=>'text','field_label'=>__('Complete payment success message', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),
                    'no_timeslots_available' => array('field_type'=>'text','field_label'=>__('No timeslots available for booking', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),
                    'cancel_appointment_confirmation' => array('field_type'=>'text','field_label'=>__('Cancel Appointment Confirmation', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),
                    'no_appointment_available_for_cancel' => array('field_type'=>'text','field_label'=>__('No Appointment Available to Cancel', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),
                    'refund_policy_message' => array('field_type'=>'text','field_label'=>__('Refund policy message', 'bookingpress-multilanguage'),'save_field_type'=>'message_setting'),                    
                ),

                /* Custom Fields Added */
                'custom_form_fields' => array(
                    'bookingpress_field_label' => array('field_type'=>'text','field_label'=>__('Label', 'bookingpress-multilanguage'),'save_field_type'=>'custom_form_fields'),
                    'bookingpress_field_placeholder' => array('field_type'=>'text','field_label'=>__('Placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'custom_form_fields'),
                    'bookingpress_field_error_message' => array('field_type'=>'text','field_label'=>__('Error Message', 'bookingpress-multilanguage'),'save_field_type'=>'custom_form_fields'),
                    'bookingpress_field_values' => array('field_type'=>'text','field_label'=>__('Options', 'bookingpress-multilanguage'),'save_field_type'=>'custom_form_fields'),
                ),

                /* Customer Custom Fields Added */
                'customer_custom_form_fields' => array(
                    'bookingpress_field_label' => array('field_type'=>'text','field_label'=>__('Label', 'bookingpress-multilanguage'),'save_field_type'=>'customer_custom_form_fields'),
                    'bookingpress_field_placeholder' => array('field_type'=>'text','field_label'=>__('Placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'customer_custom_form_fields'),                    
                    'bookingpress_field_values' => array('field_type'=>'text','field_label'=>__('Options', 'bookingpress-multilanguage'),'save_field_type'=>'custom_form_fields'),
                ),

                /* Customized My Booking */                
                'customized_my_booking_field_labels' => array(
                    'mybooking_title_text' => array('field_type'=>'text','field_label'=>__('My booking title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'apply_button_title' => array('field_type'=>'text','field_label'=>__('Apply button', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'search_appointment_title' => array('field_type'=>'text','field_label'=>__('Search appointment placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'search_date_title' => array('field_type'=>'text','field_label'=>__('Start date placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'search_end_date_title' => array('field_type'=>'text','field_label'=>__('End date placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'my_appointment_menu_title' => array('field_type'=>'text','field_label'=>__('My appointments title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'edit_account_title' => array('field_type'=>'text','field_label'=>__('Edit account title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'change_password_title' => array('field_type'=>'text','field_label'=>__('Change password title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'logout_title' => array('field_type'=>'text','field_label'=>__('Logout title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'delete_appointment_menu_title' => array('field_type'=>'text','field_label'=>__('Delete account title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'delete_account_heading_title' => array('field_type'=>'text','field_label'=>__('Delete account heading title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'delete_account_desc' => array('field_type'=>'text','field_label'=>__('Delete account description', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'delete_account_button_title' => array('field_type'=>'text','field_label'=>__('Delete account button label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'id_main_heading' => array('field_type'=>'text','field_label'=>__('ID title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'service_main_heading' => array('field_type'=>'text','field_label'=>__('Service title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'date_main_heading' => array('field_type'=>'text','field_label'=>__('Date title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'status_main_heading' => array('field_type'=>'text','field_label'=>__('Status title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'staff_main_heading' => array('field_type'=>'text','field_label'=>__('staff title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'payment_main_heading' => array('field_type'=>'text','field_label'=>__('Payment title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'booking_id_heading' => array('field_type'=>'text','field_label'=>__('Booking id title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'booking_time_title' => array('field_type'=>'text','field_label'=>__('Booking time title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'booking_guest_title' => array('field_type'=>'text','field_label'=>__('No. Of Person title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'booking_extra_title' => array('field_type'=>'text','field_label'=>__('Extras details title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'booking_deposit_title' => array('field_type'=>'text','field_label'=>__('Deposit title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'booking_tax_title' => array('field_type'=>'text','field_label'=>__('Tax title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'booking_coupon_title' => array('field_type'=>'text','field_label'=>__('Coupon title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'payment_details_title' => array('field_type'=>'text','field_label'=>__('Payment details title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'payment_method_title' => array('field_type'=>'text','field_label'=>__('Payment method title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'total_amount_title' => array('field_type'=>'text','field_label'=>__('Total amount title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),         
                    'delete_account_content' => array('field_type'=>'textarea','field_label'=>__('Delete account content', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),                    
                ),
                'customized_my_booking_login_related_messages' => array(
                    'login_form_title' => array('field_type'=>'text','field_label'=>__('Login form title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'login_form_username_field_label' => array('field_type'=>'text','field_label'=>__('Username / Email field label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'login_form_username_field_placeholder' => array('field_type'=>'text','field_label'=>__('Username / Email field Placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'login_form_password_field_label' => array('field_type'=>'text','field_label'=>__('Password label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'login_form_password_field_placeholder' => array('field_type'=>'text','field_label'=>__('Password field Placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'login_form_username_required_field_label' => array('field_type'=>'text','field_label'=>__('User name required field label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'login_form_password_required_field_label' => array('field_type'=>'text','field_label'=>__('Password required field label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'login_form_remember_me_field_label' => array('field_type'=>'text','field_label'=>__('Remember Me field label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'login_form_button_label' => array('field_type'=>'text','field_label'=>__('Login button label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'forgot_password_link_label' => array('field_type'=>'text','field_label'=>__('Forgot Password link label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'login_form_error_msg_label' => array('field_type'=>'text','field_label'=>__('Error message label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                ),
                'customized_my_booking_forgot_password_related_messages' => array(
                    'forgot_password_form_title' => array('field_type'=>'text','field_label'=>__('Forgot Password form title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'forgot_password_form_email_label' => array('field_type'=>'text','field_label'=>__('Email address field label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'forgot_password_email_placeholder_label' => array('field_type'=>'text','field_label'=>__('Email address placeholder label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'forgot_password_form_email_required_field_label' => array('field_type'=>'text','field_label'=>__('Email required field label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'forgot_password_form_button_label' => array('field_type'=>'text','field_label'=>__('Forgot password button label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'forgot_password_form_error_msg_label' => array('field_type'=>'text','field_label'=>__('Error message label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'forgot_password_form_success_msg_label' => array('field_type'=>'text','field_label'=>__('Success message label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'forgot_password_signin_link_label' => array('field_type'=>'text','field_label'=>__('Sign In Link Label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                ),
                'customized_my_booking_forgot_edit_account_messages' => array(
                    'my_profile_title' => array('field_type'=>'text','field_label'=>__('My profile title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'update_profile_btn' => array('field_type'=>'text','field_label'=>__('Update Profile Button Label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'update_profile_success_msg' => array('field_type'=>'text','field_label'=>__('Update Profile Success Message', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),                    
                ),
                'customized_my_booking_change_password_messages' => array(
                    'current_password_label' => array('field_type'=>'text','field_label'=>__('Current password label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'new_password_label' => array('field_type'=>'text','field_label'=>__('New password label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'confirm_password_label' => array('field_type'=>'text','field_label'=>__('Confirm password label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),                    
                    'current_password_placeholder' => array('field_type'=>'text','field_label'=>__('Current password placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),                    
                    'new_password_placeholder' => array('field_type'=>'text','field_label'=>__('New password placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),                    
                    'confirm_password_placeholder' => array('field_type'=>'text','field_label'=>__('Confirm password placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'update_password_btn_text' => array('field_type'=>'text','field_label'=>__('Update password button label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'update_password_success_message' => array('field_type'=>'text','field_label'=>__('Update password success message', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'update_password_error_message' => array('field_type'=>'text','field_label'=>__('Update password error message', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'old_password_error_msg' => array('field_type'=>'text','field_label'=>__('Current password required field label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'new_password_error_msg' => array('field_type'=>'text','field_label'=>__('New password required field label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'confirm_password_error_msg' => array('field_type'=>'text','field_label'=>__('Confirm password required field label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                ),
                'customized_my_booking_reschedule_appointment_messages' => array(
                    'reschedule_title' => array('field_type'=>'text','field_label'=>__('Reschedule title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'reschedule_popup_title' => array('field_type'=>'text','field_label'=>__('Popup title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'reschedule_popup_description' => array('field_type'=>'text','field_label'=>__('Popup description', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'reschedule_date_label' => array('field_type'=>'text','field_label'=>__('Date label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'reschedule_time_label' => array('field_type'=>'text','field_label'=>__('Time label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'reschedule_time_placeholder' => array('field_type'=>'text','field_label'=>__('Time Placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'reschedule_cancel_btn_label' => array('field_type'=>'text','field_label'=>__('Cancel button label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'reschedule_update_btn_label' => array('field_type'=>'text','field_label'=>__('Update button label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'reschedule_appointment_success_msg' => array('field_type'=>'text','field_label'=>__('Reschedule appointment success message', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                ),
                'customized_my_booking_cancel_appointment_messages' => array(
                    'cancel_appointment_title' => array('field_type'=>'text','field_label'=>__('Cancel appointment title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'cancel_appointment_confirmation_message' => array('field_type'=>'text','field_label'=>__('Confirmation message', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'cancel_appointment_no_btn_text' => array('field_type'=>'text','field_label'=>__('No button text', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'cancel_appointment_yes_btn_text' => array('field_type'=>'text','field_label'=>__('Yes button text', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                ),
                'customized_my_booking_cancel_appointment_conf_messages' => array(
                    'cancel_booking_id_text' => array('field_type'=>'text','field_label'=>__('Booking ID label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'cancel_service_text' => array('field_type'=>'text','field_label'=>__('Service label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'cancel_date_time_text' => array('field_type'=>'text','field_label'=>__('Date & Time label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'paid_amount_text' => array('field_type'=>'text','field_label'=>__('Paid amount label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'refund_amount_text' => array('field_type'=>'text','field_label'=>__('Refund amount label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'refund_payment_gateway_text' => array('field_type'=>'text','field_label'=>__('Refund payment method label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'cancel_button_text' => array('field_type'=>'text','field_label'=>__('Confirm cancellation button text', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'refund_apply_text' => array('field_type'=>'text','field_label'=>__('Apply button text (my booking)', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                    'refund_cancel_text' => array('field_type'=>'text','field_label'=>__('Cancel button text (my booking)', 'bookingpress-multilanguage'),'save_field_type'=>'booking_my_booking'),
                ),
                /* Customized Form */
                'customized_form_common_field_labels' => array(
                    'goback_button_text' => array('field_type'=>'text','field_label'=>__('Go back button', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'next_button_text' => array('field_type'=>'text','field_label'=>__('Next button', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'book_appointment_btn_text' => array('field_type'=>'text','field_label'=>__('Book appointment button', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'book_appointment_min_text' => array('field_type'=>'text','field_label'=>__('Minutes label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'book_appointment_hours_text' => array('field_type'=>'text','field_label'=>__('Hours label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'book_appointment_day_text' => array('field_type'=>'text','field_label'=>__('Day label', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                ),
                'customized_form_service_step_labels' => array(
                    'service_title' => array('field_type'=>'text','field_label'=>__('Step service', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'category_title' => array('field_type'=>'text','field_label'=>__('Category title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'all_category_title' => array('field_type'=>'text','field_label'=>__('All category', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'service_heading_title' => array('field_type'=>'text','field_label'=>__('Service title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'service_duration_label' => array('field_type'=>'text','field_label'=>__('Service duration', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'service_price_label' => array('field_type'=>'text','field_label'=>__('Service price', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'continue_button_title' => array('field_type'=>'text','field_label'=>__('Continue button', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'cancel_button_title' => array('field_type'=>'text','field_label'=>__('Cancel button', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                ),
                'customized_form_date_and_time_labels' => array(
                    'datetime_title' => array('field_type'=>'text','field_label'=>__('Step Date & Time', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'timeslot_text' => array('field_type'=>'text','field_label'=>__('Time slot title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'morning_text' => array('field_type'=>'text','field_label'=>__('Morning time slot title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'afternoon_text' => array('field_type'=>'text','field_label'=>__('Afternoon time slot title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'evening_text' => array('field_type'=>'text','field_label'=>__('Evening time slot title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'night_text' => array('field_type'=>'text','field_label'=>__('Night time slot title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'date_time_step_note' => array('field_type'=>'textarea','field_label'=>__('Date & time step note', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'slot_left_text' => array('field_type'=>'text','field_label'=>__('Remaining slots title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                ),       
                'customized_form_basic_details_step_labels' => array(
                    'basic_details_title' => array('field_type'=>'text','field_label'=>__('Step basic details', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                ),
                'customized_form_summary_step_labels' => array(
                    'summary_title' => array('field_type'=>'text','field_label'=>__('Summary step', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'summary_content_text' => array('field_type'=>'text','field_label'=>__('Summary description', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'service_text' => array('field_type'=>'text','field_label'=>__('Service summary title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'date_time_text' => array('field_type'=>'text','field_label'=>__('Date & time summary title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'customer_text' => array('field_type'=>'text','field_label'=>__('Customer summary title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'summary_step_note' => array('field_type'=>'textarea','field_label'=>__('Summary step note', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'appointment_details' => array('field_type'=>'text','field_label'=>__('Appointment details summary title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'total_amount_text' => array('field_type'=>'text','field_label'=>__('Total amount title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'subtotal_text' => array('field_type'=>'text','field_label'=>__('Subtotal title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'payment_method_text' => array('field_type'=>'text','field_label'=>__('Payment method title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'locally_text' => array('field_type'=>'text','field_label'=>__('Pay locally payment title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'paypal_text' => array('field_type'=>'text','field_label'=>__('PayPal payment title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'card_details_text' => array('field_type'=>'text','field_label'=>__('Card details title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'card_name_text' => array('field_type'=>'text','field_label'=>__('Card name placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'card_number_text' => array('field_type'=>'text','field_label'=>__('Card number placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'expire_month_text' => array('field_type'=>'text','field_label'=>__('Expire month placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'expire_year_text' => array('field_type'=>'text','field_label'=>__('Expire year placeholder', 'bookingpress-multilanguage'),
                    'save_field_type'=>'booking_form'),
                    'cvv_text' => array('field_type'=>'text','field_label'=>__('Cvv placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                ),
                'customized_form_payment_link_labels' => array(
                    'complete_payment_deposit_amt_title' => array('field_type'=>'text','field_label'=>__('Deposit amount title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'make_payment_button_title' => array('field_type'=>'text','field_label'=>__('Make Payment button title', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                ), 
                'in_build_booking_form_message' => array(
                    'bookingpress_thankyou_msg' => array('field_type'=>'textarea','field_label'=>__('Thank you message', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                    'bookingpress_failed_payment_msg' => array('field_type'=>'textarea','field_label'=>__('Failed Payment', 'bookingpress-multilanguage'),'save_field_type'=>'booking_form'),
                ),    
            );

            $bookingpress_all_language_translation_fields['customized_package_booking_field_labels'] = 
            array(
                'package_form_title' => array('field_type'=>'text','field_label'=>__('Package Form Label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_search_placeholder' => array('field_type'=>'text','field_label'=>__('Package search placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_search_button' => array('field_type'=>'text','field_label'=>__('Search button', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'no_package_found_msg' => array('field_type'=>'text','field_label'=>__('No Package Found', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_buy_now_nutton_text' => array('field_type'=>'text','field_label'=>__('Buy now button', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_services_include_text' => array('field_type'=>'text','field_label'=>__('Services Includes text', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_services_show_more_text' => array('field_type'=>'text','field_label'=>__('Services Show More text', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_services_show_less_text' => array('field_type'=>'text','field_label'=>__('Services Show Less text', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_go_back_button_text' => array('field_type'=>'text','field_label'=>__('Go back button', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_desc_read_more_text' => array('field_type'=>'text','field_label'=>__('Package Description Read More', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_desc_show_less_text' => array('field_type'=>'text','field_label'=>__('Package Description Show Less', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_month_text' => array('field_type'=>'text','field_label'=>__('Package month label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'), 
                'package_months_text' => array('field_type'=>'text','field_label'=>__('Package months label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_year_text' => array('field_type'=>'text','field_label'=>__('Package year label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),                
                'package_years_text' => array('field_type'=>'text','field_label'=>__('Package years label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_day_text' => array('field_type'=>'text','field_label'=>__('Package day label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_days_text' => array('field_type'=>'text','field_label'=>__('Package days label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),                 
            ); 
                
            $bookingpress_all_language_translation_fields['customized_package_booking_user_detail_step_label'] = 
            array(
                'user_details_step_label' => array('field_type'=>'text','field_label'=>__('User details step', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
            );
              
            $bookingpress_all_language_translation_fields['customized_package_booking_login_related_labels'] = 
            array(
                'login_form_title_label' => array('field_type'=>'text','field_label'=>__('Login form title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'login_form_username_field_label' => array('field_type'=>'text','field_label'=>__('Username / Email field label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'login_form_username_field_placeholder' => array('field_type'=>'text','field_label'=>__('Username / Email field Placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'login_form_password_field_label' => array('field_type'=>'text','field_label'=>__('Password label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'), 
                'login_form_password_field_placeholder' => array('field_type'=>'text','field_label'=>__('Password field Placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'login_form_username_required_field_label' => array('field_type'=>'text','field_label'=>__('User name required field label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'login_form_password_required_field_label' => array('field_type'=>'text','field_label'=>__('Password required field label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'remember_me_field_label' => array('field_type'=>'text','field_label'=>__('Remember Me field label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'login_button_label' => array('field_type'=>'text','field_label'=>__('Login button label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'forgot_password_link_label' => array('field_type'=>'text','field_label'=>__('Forgot Password link label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'login_error_message_label' => array('field_type'=>'text','field_label'=>__('Error message label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'login_form_signup_link_text' => array('field_type'=>'text','field_label'=>__('SignUp link label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'login_form_dont_have_acc_text' => array('field_type'=>'text','field_label'=>__('Don\'t have an account label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
            );
                
            $bookingpress_all_language_translation_fields['customized_package_booking_forgot_password_labels'] = 
            array(
                'forgot_password_form_title' => array('field_type'=>'text','field_label'=>__('Forgot Password form title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'forgot_password_email_address_field_label' => array('field_type'=>'text','field_label'=>__('Email address field label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'forgot_password_email_address_placeholder' => array('field_type'=>'text','field_label'=>__('Email address placeholder label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'forgot_password_email_required_field_label' => array('field_type'=>'text','field_label'=>__('Email required field label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'forgot_password_button_label' => array('field_type'=>'text','field_label'=>__('Forgot password button label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'forgot_password_error_message' => array('field_type'=>'text','field_label'=>__('Error message label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'forgot_password_success_message_label' => array('field_type'=>'text','field_label'=>__('Success message label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'forgot_password_sing_in_link_label' => array('field_type'=>'text','field_label'=>__('Sign In Link Label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
            ); 
            
            $bookingpress_all_language_translation_fields['customized_package_booking_signup_form_labels'] = 
            array(
                'signup_account_form_title' => array('field_type'=>'text','field_label'=>__('Signup form title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'signup_account_fullname_label' => array('field_type'=>'text','field_label'=>__('Full name field label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'signup_account_email_label' => array('field_type'=>'text','field_label'=>__('Email field label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'signup_account_mobile_number_label' => array('field_type'=>'text','field_label'=>__('Mobile Number field label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'signup_account_password_label' => array('field_type'=>'text','field_label'=>__('Password field label', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'signup_account_fullname_placeholder' => array('field_type'=>'text','field_label'=>__('Full name field placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'signup_account_email_placeholder' => array('field_type'=>'text','field_label'=>__('Email field placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'signup_account_password_placeholder' => array('field_type'=>'text','field_label'=>__('Password field placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'signup_account_fullname_required_message' => array('field_type'=>'text','field_label'=>__('Full name required message', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'signup_account_email_required_message' => array('field_type'=>'text','field_label'=>__('Email required message', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'signup_account_mobile_number_required_message' => array('field_type'=>'text','field_label'=>__('Mobile Number required message
                ', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'signup_account_password_required_message' => array('field_type'=>'text','field_label'=>__('Password required message', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'signup_form_button_title' => array('field_type'=>'text','field_label'=>__('Signup button title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'signup_form_already_have_acc_text' => array('field_type'=>'text','field_label'=>__('Already have account text', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'signup_form_login_link_text' => array('field_type'=>'text','field_label'=>__('Login link title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),                
            );       
            
            $bookingpress_all_language_translation_fields['customized_package_booking_basic_details_labels'] = 
            array(
                'basic_details_form_title' => array('field_type'=>'text','field_label'=>__('Basic Details form title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'basic_details_submit_button_title' => array('field_type'=>'text','field_label'=>__('Submit button title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
            );

            $bookingpress_all_language_translation_fields['customized_package_booking_make_payment_labels'] = 
            array(
                'make_payment_tab_title' => array('field_type'=>'text','field_label'=>__('Make payment tab title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'make_payment_form_title' => array('field_type'=>'text','field_label'=>__('Make payment form title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'make_payment_subtotal_text' => array('field_type'=>'text','field_label'=>__('Subtotal text', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'make_payment_total_amount_text' => array('field_type'=>'text','field_label'=>__('Total amount text', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'make_payment_select_payment_method_text' => array('field_type'=>'text','field_label'=>__('Select payment method text', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'make_payment_buy_package_btn_text' => array('field_type'=>'text','field_label'=>__('Buy package button text', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
            );

            $bookingpress_all_language_translation_fields['customized_package_booking_summary_step_labels'] = 
            array(
                'summary_step_title' => array('field_type'=>'text','field_label'=>__('Summary step', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'summary_step_form_title' => array('field_type'=>'text','field_label'=>__('Summary form title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'summary_tab_booking_id_text' => array('field_type'=>'text','field_label'=>__('Booking Id', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'summary_tab_package_booked_success_message' => array('field_type'=>'textarea','field_label'=>__('Package booked success message', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'summary_tab_package_booking_information_sent_message' => array('field_type'=>'textarea','field_label'=>__('Package booking information sent message', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'summary_tab_package_title_text' => array('field_type'=>'text','field_label'=>__('Package title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'summary_tab_customer_title' => array('field_type'=>'text','field_label'=>__('Customer Name title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'summary_tab_book_appointment_btn_text' => array('field_type'=>'text','field_label'=>__('Book Appointment title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'pkg_card_details_text' => array('field_type'=>'text','field_label'=>__('Card details title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'pkg_card_name_text' => array('field_type'=>'text','field_label'=>__('Card name placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'pkg_card_number_text' => array('field_type'=>'text','field_label'=>__('Card number placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'pkg_expire_month_text' => array('field_type'=>'text','field_label'=>__('Expire month placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'pkg_expire_year_text' => array('field_type'=>'text','field_label'=>__('Expire year placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'pkg_cvv_text' => array('field_type'=>'text','field_label'=>__('Cvv placeholder', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_tax_title' => array('field_type'=>'text','field_label'=>__('Tax title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'pkg_paypal_text' => array('field_type'=>'text','field_label'=>__('PayPal payment title', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_purchase_limit_message' => array('field_type'=>'textarea','field_label'=>__('Package purchase limit message', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
                'package_order_payment_failed_message' => array('field_type'=>'textarea','field_label'=>__('Package Payment failed message', 'bookingpress-multilanguage'),'save_field_type'=>'package_booking_form'),
            );
                                             
            $bookingpress_all_language_translation_fields = apply_filters( 'bookingpress_modified_language_translate_fields',$bookingpress_all_language_translation_fields);             
            return $bookingpress_all_language_translation_fields;
        }

        /**
         * Function for all language translate fields label
         *
         * @return void
        */
        public function bookingpress_all_language_translation_fields_section(){

            global $bookingpress_global_options,$bookingpress_pro_staff_members;
            $bookingpress_global_options_arr = $bookingpress_global_options->bookingpress_global_options();
            $bookingpress_singular_staffmember_name = !empty($bookingpress_global_options_arr['bookingpress_staffmember_singular_name']) ? $bookingpress_global_options_arr['bookingpress_staffmember_singular_name'] : esc_html_e('Staff Member', 'bookingpress-multilanguage');

            $is_staffmember_module_activated = $bookingpress_pro_staff_members->bookingpress_check_staffmember_module_activation();

            $bookingpress_all_language_translation_fields_section = array(                
                'service' => __('Service', 'bookingpress-multilanguage'),
                'category' => __('Categories', 'bookingpress-multilanguage'),
                'customized_form_common_field_labels' => __('Common field labels', 'bookingpress-multilanguage'),
                'customized_form_service_step_labels' => __('Service step labels', 'bookingpress-multilanguage'), 
                'customized_form_date_and_time_labels' => __('Date & Time labels', 'bookingpress-multilanguage'),                                   
                'customized_form_basic_details_step_labels' => __('Basic details step labels', 'bookingpress-multilanguage'), 
                'customized_form_summary_step_labels' => __('Summary step labels', 'bookingpress-multilanguage'), 
                'customized_form_payment_link_labels' => __('Payment Link Labels', 'bookingpress-multilanguage'),
                
                'customized_my_booking_field_labels' => __('Common field labels', 'bookingpress-multilanguage'),   
                'customized_my_booking_login_related_messages' => __('Login Related Messages', 'bookingpress-multilanguage'),   
                'customized_my_booking_forgot_password_related_messages' => __('Forgot Password Messages', 'bookingpress-multilanguage'),
                'customized_my_booking_forgot_edit_account_messages' => __('Edit Account Messages', 'bookingpress-multilanguage'),
                'customized_my_booking_change_password_messages' => __('Change Password Messages', 'bookingpress-multilanguage'),
                'customized_my_booking_reschedule_appointment_messages' => __('Reschedule Appointment Messages', 'bookingpress-multilanguage'),
                'customized_my_booking_cancel_appointment_messages' => __('Cancel Appointment Messages', 'bookingpress-multilanguage'),
                'customized_my_booking_cancel_appointment_conf_messages' => __('Cancel Appointment Confirmation Messages', 'bookingpress-multilanguage'),
                'company_setting' => __('Company Details', 'bookingpress-multilanguage'), 
                'message_setting' => __('Message Settings', 'bookingpress-multilanguage'),                 
                'customized_form_waiting_list_labels' => __('Waiting List labels', 'bookingpress-multilanguage'),
                'customized_form_tip_input_labels' => __('Tip Inputs labels', 'bookingpress-multilanguage'),

                'manage_notification_customer' => __('To Customer', 'bookingpress-multilanguage'),
                //'manage_notification_employee' => __('To Admin', 'bookingpress-multilanguage'),
                'in_build_booking_form_message' => __('In-built', 'bookingpress-multilanguage'),

            );
            if($is_staffmember_module_activated){
                $bookingpress_all_language_translation_fields_section['manage_notification_employee'] = __('To ', 'bookingpress-multilanguage').$bookingpress_singular_staffmember_name;
            }
            $bookingpress_all_language_translation_fields_section = apply_filters( 'bookingpress_modified_language_translate_fields_section',$bookingpress_all_language_translation_fields_section);             
            return $bookingpress_all_language_translation_fields_section;
        }

                
        /**
         * Function for get language translation section label
         *
         * @return void
         */
        function bookingpress_get_language_translation_section_label($bookingpress_all_language_translation_fields){            
            $bookingpress_all_language_translation_fields_section = $this->bookingpress_all_language_translation_fields_section();
            $language_translation_fields_section_title = array();
            if(!empty($bookingpress_all_language_translation_fields)){
                $bookingpress_all_used_section_key = array_keys($bookingpress_all_language_translation_fields);
                if(!empty($bookingpress_all_used_section_key)){
                    foreach($bookingpress_all_used_section_key as $section_key){
                        $language_translation_fields_section_title[$section_key] = (isset($bookingpress_all_language_translation_fields_section[$section_key]))?$bookingpress_all_language_translation_fields_section[$section_key]:'';
                    }
                }
            }
            return $language_translation_fields_section_title;            
        }

        /**
         * Function for return user selected languages
         *
         * @return void
         */
        function bookingpress_get_selected_languages(){
            global $BookingPress;
            $bookingpress_all_language_list = $this->get_all_wp_languages_list();
            $bookingpress_selected_languages = $BookingPress->bookingpress_get_settings('bookingpress_selected_languages', 'general_setting');
            $bookingpress_selected_languages_list = array();
            if(!empty($bookingpress_selected_languages)){
                $bookingpress_selected_languages = explode(',',$bookingpress_selected_languages);
                foreach($bookingpress_selected_languages as $sellang){
                    if(isset($bookingpress_all_language_list[$sellang])){
                        $bookingpress_selected_languages_list[$sellang] = $bookingpress_all_language_list[$sellang];
                    }
                }
            }
            return $bookingpress_selected_languages_list;
        }

        /**
         * Function for get all avaliable language
         *
         * @return void
         */
        function get_all_wp_languages_list(){           
            require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
            $language_available = wp_get_available_translations();
            if(!isset($language_available['en_US'])){
                $language_available['en_US'] = array(
                    'language' => 'en_US',
                    'version' => '6.6.2',
                    'english_name' => 'English (United States)',
                    'native_name' => 'English (United States)',
                    'package' => '',
                    'iso' => array(
                        array('en')
                     ),
                     'strings' => array('continue'=>'English'),
                );
            }
            foreach($language_available as $key=>$avaliable_lang){
                $flag_image = '';
                if(file_exists(BOOKINGPRESS_MULTILANGUAGE_IMAGES_DIR.'flags/'.$key.'.png')){
                    $flag_image = BOOKINGPRESS_MULTILANGUAGE_URL.'/images/flags/'.$key.'.png';
                }
                $language_available[$key]['flag_image'] = $flag_image;
            }          
            return $language_available;
        }


        /* Location Backend Settings */
          
        
        
        
        /**
         * Function for add location multi-language button
         *
         * @return void
         */
        function bookingpress_location_header_extra_button_fun(){
        ?>
            <el-button class="bpa-btn bpa-btn--ml-translate" @click="open_location_translate_language_modal()">
                <span class="material-icons-round">translate</span>
                <?php esc_html_e('Translate', 'bookingpress-multilanguage'); ?>
            </el-button>            
        <?php                          
        }        
          
        /**
         * Function for add location language translation popup
         *
         * @return void
        */
        function bookingpress_manage_location_view_bottom_func(){            
            include BOOKINGPRESS_MULTILANGUAGE_VIEW_DIR.'bookingpress_location_language_translation_popup.php';
        }            

        /**
         * Function for add location vue variable
         *
         * @param  mixed $bookingpress_location_vue_data_fields
         * @return void
         */
        function bookingpress_modify_location_vue_fields_data_func($bookingpress_location_vue_data_fields){

            global $wpdb,$tbl_bookingpress_categories,$bookingpress_options,$BookingPress,$bookingpress_all_language_translation_fields_section,$bookingpress_all_language_translation_fields;
            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_get_selected_languages = array();
            }
            $bookingpress_current_selected_lang = '';
            $bookingpress_location_vue_data_fields['empty_selected_language'] = 0;
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_location_vue_data_fields['empty_selected_language'] = 1;
            }            
            $bookingpress_location_language_translate_fields = array();        
            $bookingpress_location_language_translate_fields = apply_filters( 'bookingpress_modified_location_language_translate_fields',$bookingpress_location_language_translate_fields);             
            $bookingpress_location_language_section_title = $this->bookingpress_get_language_translation_section_label($bookingpress_location_language_translate_fields);

            $bookingpress_current_selected_lang = '';           
            $bookingpress_location_vue_data_fields['language_data'] = array();
            $bookingpress_location_vue_data_fields['language_fields_data'] = array();
            if(!empty($bookingpress_get_selected_languages)){
                foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                    if(empty($bookingpress_current_selected_lang)){
                        $bookingpress_current_selected_lang = $key;
                    }            
                    foreach($bookingpress_location_language_translate_fields as $section_key=>$service_lang){                                                
                        foreach($service_lang as $field_key => $field_value){                            
                            $bookingpress_location_vue_data_fields['language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                            $bookingpress_location_vue_data_fields['language_data'][$key][$section_key][$field_key] = ''; 
                            $bookingpress_location_vue_data_fields['language_data_org'][$key][$section_key][$field_key] = '';      
                        }
                    }
                }
            }

            $bookingpress_location_vue_data_fields['bookingpress_location_language_section_title'] = $bookingpress_location_language_section_title;
            $bookingpress_location_vue_data_fields['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
            $bookingpress_location_vue_data_fields['bookingpress_current_selected_lang_org'] = $bookingpress_current_selected_lang;
            $bookingpress_location_vue_data_fields['open_location_translate_language'] = false;
            $bookingpress_location_vue_data_fields['bookingpress_get_selected_languages'] = $bookingpress_get_selected_languages;

            return $bookingpress_location_vue_data_fields;
        }
  
        /**
         * Function for get location edit data
         *
         * @return void
         */
        function bookingpress_modified_get_edit_location_response_func($response,$bookingpress_edit_id){
            global $wpdb,$tbl_bookingpress_categories,$bookingpress_options,$BookingPress,$bookingpress_all_language_translation_fields_section,$bookingpress_all_language_translation_fields;
            $bookingpress_location_vue_data_fields = array();
            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_get_selected_languages = array();
            }
            $bookingpress_current_selected_lang = '';
            $bookingpress_location_vue_data_fields['empty_selected_language'] = 0;
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_location_vue_data_fields['empty_selected_language'] = 1;
            }            
            $bookingpress_location_language_translate_fields = array();        
            $bookingpress_location_language_translate_fields = apply_filters( 'bookingpress_modified_location_language_translate_fields',$bookingpress_location_language_translate_fields);             
            $bookingpress_location_language_section_title = $this->bookingpress_get_language_translation_section_label($bookingpress_location_language_translate_fields);
            $bookingpress_current_selected_lang = '';           
            $bookingpress_location_vue_data_fields['language_data'] = array();
            $bookingpress_location_vue_data_fields['language_fields_data'] = array();
            if(!empty($bookingpress_get_selected_languages)){
                $save_language_data = $this->bookingpress_get_language_data_for_backend($bookingpress_edit_id,'location');
                foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                    if(empty($bookingpress_current_selected_lang)){
                        $bookingpress_current_selected_lang = $key;
                    }            
                    foreach($bookingpress_location_language_translate_fields as $section_key=>$service_lang){                                                
                        foreach($service_lang as $field_key => $field_value){                            
                            $bookingpress_location_vue_data_fields['language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                            $bookingpress_location_vue_data_fields['language_data'][$key][$section_key][$field_key] = ''; 
                            $bookingpress_location_vue_data_fields['language_data_org'][$key][$section_key][$field_key] = '';                            
                            if(!empty($save_language_data)){
                                $search = array('bookingpress_element_type' => 'location', 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key, 'bookingpress_element_ref_id'=> $bookingpress_edit_id);
                                $keys = array_keys(array_filter($save_language_data, function ($v) use ($search) { 
                                            return $v['bookingpress_element_ref_id'] == $search['bookingpress_element_ref_id'] && $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                        }
                                ));
                                $index_val = isset($keys[0]) ? $keys[0] : '';
                                if($index_val!='' || $index_val == 0) {
                                    if(isset($save_language_data[$index_val])){
                                        $translated_data = $save_language_data[$index_val];
                                        $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                        $bookingpress_location_vue_data_fields['language_data'][$key][$section_key][$field_key] = $bp_translated_str;    
                                    }
                                }
                            }                                
                        }
                    }
                }
            }            
            $response['language_fields_data'] = $bookingpress_location_vue_data_fields['language_fields_data'];            
            $response['language_data'] = $bookingpress_location_vue_data_fields['language_data'];
            $response['bookingpress_location_language_section_title'] = $bookingpress_location_language_section_title;
            $response['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
            $response['bookingpress_current_selected_lang_org'] = $bookingpress_current_selected_lang;
            $response['open_location_translate_language'] = false;
            $response['bookingpress_get_selected_languages'] = $bookingpress_get_selected_languages;
            return $response;
        }    

        
        /**
         * Function for open location modal after reset language data
         *
         * @return void
        */
        function bookingpress_open_location_modal_after_func(){
        ?>
            if(action == 'add'){                
                vm.language_data = vm.language_data_org;                                               
            }
            vm.bookingpress_current_selected_lang = vm.bookingpress_current_selected_lang_org;
        <?php 
        }

        /**
         * Edit Location language Vue Data set
         *
         * @return void
        */
        function bookingpress_edit_location_more_vue_data_func(){
        ?>                
            if(typeof response.data.language_fields_data !== 'undefined'){
                vm2.language_fields_data = response.data.language_fields_data;
            }
            if(typeof response.data.language_data !== 'undefined'){
                vm2.language_data = response.data.language_data;
            }
            if(typeof response.data.bookingpress_location_language_section_title !== 'undefined'){
                vm2.bookingpress_location_language_section_title = response.data.bookingpress_location_language_section_title;
            }
            if(typeof response.data.bookingpress_current_selected_lang !== 'undefined'){
                vm2.bookingpress_current_selected_lang = response.data.bookingpress_current_selected_lang;
            }   
            if(typeof response.data.bookingpress_get_selected_languages !== 'undefined'){
                vm2.bookingpress_get_selected_languages = response.data.bookingpress_get_selected_languages;
            }                                                
        <?php     
        }

        /**
         * Function for add/update location data
         *
         * @param  mixed $bookingpress_location_id
         * @return void
         */
        function bookingpress_after_add_or_update_location_func($bookingpress_location_id){
            if(isset($_POST['language_data']) && !empty($_POST['language_data'])){ //phpcs:ignore
                global $BookingPress;
                if( !empty( $_POST['language_data'] ) && !is_array( $_POST['language_data'] ) ){ //phpcs:ignore
                    $_POST['language_data'] = json_decode( stripslashes_deep( $_POST['language_data'] ), true ); //phpcs:ignore                    
                }                
                $language_data = !empty($_POST['language_data'])?array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['language_data']):array();  // phpcs:ignore 
                if(!empty($language_data)){
                    if(is_array($language_data)){  
                        foreach($language_data as $lang_key=>$single_language_data){
                            foreach($single_language_data as $lang_section=>$lang_fields){
                                foreach($lang_fields as $lang_field_key=>$bp_translated_val){
                                    $this->bookingpress_common_save_language_data($lang_key,$lang_section,$bp_translated_val,$bookingpress_location_id,$lang_field_key);
                                }                            
                            }
                        } 
                    }
                }                                                
            }    
        }    

        /**
         * Function for add location postdata
         *
         * @return void
         */
        function bookingpress_add_location_more_postdata_func(){
        ?>
            postdata.language_data = vm.language_data;
        <?php 
        }

        /**
         * Function for add location vue method
         *
         * @return void
         */
        function bookingpress_add_location_dynamic_vue_methods_func(){
        ?>
        open_location_translate_language_modal(){
            var vm2 = this;
            vm2.open_location_translate_language = true;
        },   
        change_multilanguage_current_language(lang){
            var vm2 = this;            
            vm2.bookingpress_current_selected_lang = lang;
        },             
        <?php 
        }

		/**
		 * Function for add multi language settings
		 *
		 * @param  mixed $bookingpress_setting_return_data
		 * @param  mixed $bookingpress_posted_data
		 * @return void
		*/
		function bookingpress_modify_get_settings_data_func( $bookingpress_setting_return_data, $bookingpress_posted_data ) {
			if( 'general_setting' == $bookingpress_posted_data['setting_type'] && isset($bookingpress_setting_return_data['bookingpress_selected_languages'])){
				if(!empty($bookingpress_setting_return_data['bookingpress_selected_languages'])) {					
		            $bookingpress_setting_return_data['bookingpress_selected_languages'] = explode(",",$bookingpress_setting_return_data['bookingpress_selected_languages']);

                    $bookingpress_selected_languages = $bookingpress_setting_return_data['bookingpress_selected_languages'];                    
                    $get_wp_default_lang = $this->bookingpress_get_wordpress_default_lang();
                    if(!empty($bookingpress_selected_languages) && is_array($bookingpress_selected_languages)){
                        $new_lang_added = array();
                        foreach($bookingpress_selected_languages as $lang){
                            if($lang != $get_wp_default_lang){
                                $new_lang_added[] = $lang;
                            }
                        }                       
                        $bookingpress_setting_return_data['bookingpress_selected_languages'] = $new_lang_added ;
                    }

				} else  {
					$bookingpress_setting_return_data['bookingpress_selected_languages'] = array();
				}
			}	
            /*
            if( 'message_setting' == $bookingpress_posted_data['setting_type']){
                
                global $bookingpress_all_language_translation_fields;

                $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
                if(empty($bookingpress_get_selected_languages)){
                    $bookingpress_get_selected_languages = array();
                }

                $bookingpress_dynamic_setting_data_fields = array();
                $bookingpress_message_language_translate_fields = array();
                $bookingpress_message_language_translate_fields['message_setting'] = $bookingpress_all_language_translation_fields['message_setting'];                         
                $bookingpress_current_selected_lang = '';
                $bookingpress_dynamic_setting_data_fields['empty_selected_language'] = 0;
                if(empty($bookingpress_get_selected_languages)){
                    $bookingpress_dynamic_setting_data_fields['empty_selected_language'] = 1;
                }
                $bookingpress_dynamic_setting_data_fields['message_language_fields_data'] = array();
                $bookingpress_dynamic_setting_data_fields['message_language_data'] = array();
                if(!empty($bookingpress_get_selected_languages)){                
                    $message_language_data = $this->bookingpress_get_language_data_for_backend(0,'message_setting');
                    foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                        if(empty($bookingpress_current_selected_lang)){
                            $bookingpress_current_selected_lang = $key;
                        }            
                        foreach($bookingpress_message_language_translate_fields as $section_key=>$service_lang){                                                
                            foreach($service_lang as $field_key => $field_value){                            
                                $bookingpress_dynamic_setting_data_fields['message_language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                                $bookingpress_dynamic_setting_data_fields['message_language_data'][$key][$section_key][$field_key] = ''; 
    
                                if(!empty($message_language_data)){                                
                                    $search = array('bookingpress_element_type' => $section_key, 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key);
                                    $keys = array_keys(array_filter($message_language_data, function ($v) use ($search) { 
                                                return $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                            }
                                    ));
                                    $index_val = isset($keys[0]) ? $keys[0] : '';
                                    if($index_val!='' || $index_val == 0) {
                                        $translated_data = $message_language_data[$index_val];
                                        $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                        $bookingpress_dynamic_setting_data_fields['message']['language_data'][$key][$section_key][$field_key] = $bp_translated_str;
                                    }
                                }                            
    
                            }
                        }
                    }
                }                                
                $bookingpress_setting_return_data['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
                $bookingpress_setting_return_data['message_language_fields_data'] = $bookingpress_dynamic_setting_data_fields['message_language_fields_data'];
                $bookingpress_setting_return_data['message_language_data'] = $bookingpress_dynamic_setting_data_fields['message_language_data'];
                $bookingpress_setting_return_data['empty_selected_language'] = $bookingpress_dynamic_setting_data_fields['empty_selected_language'];
                $bookingpress_setting_return_data['bookingpress_get_selected_languages'] = $bookingpress_get_selected_languages;


            }
            */
			return $bookingpress_setting_return_data;
		}
        
        

        function bookingpress_modify_get_settings_response_data_func($response,$bookingpress_posted_data){
                        
            if( 'customer_setting' == $bookingpress_posted_data['setting_type']){
                global $wpdb,$bookingpress_all_language_translation_fields,$tbl_bookingpress_form_fields;
                $bookingpress_dynamic_setting_data_fields = array();
                $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
                if(empty($bookingpress_get_selected_languages)){
                    $bookingpress_get_selected_languages = array();
                }
                $bookingpress_customer_field_language_translate_fields = array();
                $bookingpress_customer_field_language_translate_fields['customer_custom_form_fields'] = $bookingpress_all_language_translation_fields['customer_custom_form_fields'];                         
                $bookingpress_customer_field_language_section_title = $this->bookingpress_get_language_translation_section_label($bookingpress_customer_field_language_translate_fields);
                $bookingpress_current_selected_lang = '';
                $bookingpress_dynamic_setting_data_fields['empty_selected_language'] = 0;
                if(empty($bookingpress_get_selected_languages)){
                    $bookingpress_dynamic_setting_data_fields['empty_selected_language'] = 1;
                }    
                $bookingpress_dynamic_setting_data_fields['customer_language_fields_data'] = array();
                $bookingpress_dynamic_setting_data_fields['customer_language_data'] = array();
                $bookingpress_custom_form_fields = $wpdb->get_results( $wpdb->prepare('SELECT bookingpress_field_values,bookingpress_field_type,bookingpress_field_position,bookingpress_field_meta_key,bookingpress_form_field_id,bookingpress_field_label,bookingpress_field_placeholder,bookingpress_field_error_message FROM ' . $tbl_bookingpress_form_fields . ' where bookingpress_is_customer_field = %d order by bookingpress_field_position ASC',1), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm                
                $bookingpress_customer_form_fields_language_section_title = array();
                if(!empty($bookingpress_get_selected_languages) && !empty($bookingpress_custom_form_fields)){                                        
                    $customer_save_language_data = $this->bookingpress_get_language_data_for_backend(0,'customer_custom_form_fields');
                    foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                        if(empty($bookingpress_current_selected_lang)){
                            $bookingpress_current_selected_lang = $key;
                        }                           
                        foreach($bookingpress_custom_form_fields as $user_form_fields){
                            $bookingpress_customer_form_fields_language_section_title[$user_form_fields['bookingpress_form_field_id']] = (!empty($user_form_fields['bookingpress_field_label']))?stripslashes_deep($user_form_fields['bookingpress_field_label']):''; 
                            foreach($bookingpress_customer_field_language_translate_fields as $section_key=>$service_lang){ 
                                foreach($service_lang as $field_key => $field_value){   
                                    $bookingpress_is_radio_or_checkbox = false;
                                    if(($user_form_fields['bookingpress_field_type'] == 'checkbox' || $user_form_fields['bookingpress_field_type'] == 'radio' || $user_form_fields['bookingpress_field_type'] == 'dropdown')){
                                        $bookingpress_is_radio_or_checkbox = true;
                                    }
                                    if(!$bookingpress_is_radio_or_checkbox || $field_key != 'bookingpress_field_placeholder'){ 
                                        
                                        if(($field_key == 'bookingpress_field_values' && $bookingpress_is_radio_or_checkbox) || ($field_key != 'bookingpress_field_values')){

                                            $bookingpress_dynamic_setting_data_fields['customer_language_data'][$key][$section_key][$field_key][$user_form_fields['bookingpress_form_field_id']] = '';                                                                             
                                            if($field_key == 'bookingpress_field_values' && $bookingpress_is_radio_or_checkbox){                                        
                                                
                                                $field_value['bookingpress_field_values'] = json_decode($user_form_fields['bookingpress_field_values'],true);                                                                                                                                                                    
                                                $bookingpress_field_valuesnn = json_decode($user_form_fields['bookingpress_field_values'],true);                                        
                                                $bookingpress_field_values_new = array();                                        
                                                if(!empty($bookingpress_field_valuesnn)){                                            
                                                    foreach($bookingpress_field_valuesnn as $optionkey=>$optionval){                                                
                                                        $bookingpress_field_valuesnn[$optionkey]['value'] = '';
                                                        $bookingpress_field_valuesnn[$optionkey]['label'] = '';
                                                    }
                                                }                                                                                    
                                                $bookingpress_dynamic_setting_data_fields['customer_language_data'][$key][$section_key][$field_key][$user_form_fields['bookingpress_form_field_id']] = $bookingpress_field_valuesnn;
                                            }                                                                                
                                            $bookingpress_dynamic_setting_data_fields['customer_language_fields_data'][$key][$user_form_fields['bookingpress_form_field_id']][$section_key][$field_key] = $field_value;
                                            if(!empty($customer_save_language_data)){
                                                $search = array('bookingpress_element_type' => 'customer_custom_form_fields', 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key, 'bookingpress_element_ref_id'=> $user_form_fields['bookingpress_form_field_id']);
                                                $keys = array_keys(array_filter($customer_save_language_data, function ($v) use ($search) { 
                                                            return $v['bookingpress_element_ref_id'] == $search['bookingpress_element_ref_id'] && $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                                        }
                                                ));
                                                $index_val = isset($keys[0]) ? $keys[0] : '';
                                                if($index_val!='' || $index_val == 0) {
                                                   if(isset($customer_save_language_data[$index_val])){
                                                        $translated_data = $customer_save_language_data[$index_val];
                                                        $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                                        if($field_key == 'bookingpress_field_values' && $bookingpress_is_radio_or_checkbox){  
                                                            
                                                            if(!empty($bp_translated_str)){
                                                                if(empty($bp_translated_str)){
                                                                    $bp_translated_str = array();
                                                                }
                                                                $bp_translated_str = json_decode($bp_translated_str,true);
                                                                $bp_translated_str_new = array();
                                                                if(!empty($bp_translated_str)){
                                                                    $db_values = $bookingpress_dynamic_setting_data_fields['customer_language_data'][$key][$section_key][$field_key][$user_form_fields['bookingpress_form_field_id']];
                                                                    if( count( $bp_translated_str ) >= count( $db_values ) ){   
                                                                        $bookingpress_dynamic_setting_data_fields['customer_language_data'][$key][$section_key][$field_key][$user_form_fields['bookingpress_form_field_id']] = $bp_translated_str;
                                                                    } 
                                                                    else if(count( $bp_translated_str ) < count( $db_values )) {
                                                                        foreach( $db_values as $db_value_key => $db_value_data ){
                                                                            if( !empty( $bp_translated_str[ $db_value_key ] ) ){
                                                                                $bp_translated_str_new[ $db_value_key ] = $bp_translated_str[ $db_value_key ];
                                                                            } else {
                                                                                $bp_translated_str_new[ $db_value_key ] = $db_value_data;
                                                                            }
                                                                        }
                                                                    }
                                                                    if( !empty( $bp_translated_str_new ) ){
                                                                        $bookingpress_dynamic_setting_data_fields['customer_language_data'][$key][$section_key][$field_key][$user_form_fields['bookingpress_form_field_id']] = $bp_translated_str_new;
                                                                    }
                                                                }                                                          
                                                            }
                                                        }else{                                                                                                        
                                                            $bookingpress_dynamic_setting_data_fields['customer_language_data'][$key][$section_key][$field_key][$user_form_fields['bookingpress_form_field_id']] = $bp_translated_str;
                                                        }
                                                   }
                                                }
                                            }

                                        }


                                    }
                                }
                            }                        
                        }
                    }
                }                

               

                $response['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
                $response['customer_language_fields_data'] = $bookingpress_dynamic_setting_data_fields['customer_language_fields_data'];
                $response['customer_language_data'] = $bookingpress_dynamic_setting_data_fields['customer_language_data'];
                $response['empty_selected_language'] = $bookingpress_dynamic_setting_data_fields['empty_selected_language'];
                $response['bookingpress_get_selected_languages'] = $bookingpress_get_selected_languages;
                $response['bookingpress_customer_form_fields_language_section_title'] = $bookingpress_customer_form_fields_language_section_title;

            }
            if( 'company_setting' == $bookingpress_posted_data['setting_type']){
                
                global $bookingpress_all_language_translation_fields;

                $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
                if(empty($bookingpress_get_selected_languages)){
                    $bookingpress_get_selected_languages = array();
                }
                $bookingpress_dynamic_setting_data_fields = array();
                $bookingpress_company_language_translate_fields = array();
                $bookingpress_company_language_translate_fields['company_setting'] = $bookingpress_all_language_translation_fields['company_setting'];                         
                $bookingpress_company_language_section_title = $this->bookingpress_get_language_translation_section_label($bookingpress_company_language_translate_fields);

                $bookingpress_current_selected_lang = '';
                $bookingpress_dynamic_setting_data_fields['empty_selected_language'] = 0;
                if(empty($bookingpress_get_selected_languages)){
                    $bookingpress_dynamic_setting_data_fields['empty_selected_language'] = 1;
                }

                $bookingpress_dynamic_setting_data_fields['company_language_fields_data'] = array();
                $bookingpress_dynamic_setting_data_fields['company']['language_data'] = array();
                if(!empty($bookingpress_get_selected_languages)){
                    
                    $company_language_data = $this->bookingpress_get_language_data_for_backend(0,'company_setting');
                    foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                        if(empty($bookingpress_current_selected_lang)){
                            $bookingpress_current_selected_lang = $key;
                        }            
                        foreach($bookingpress_company_language_translate_fields as $section_key=>$service_lang){                                                
                            foreach($service_lang as $field_key => $field_value){                            
                                $bookingpress_dynamic_setting_data_fields['company_language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                                $bookingpress_dynamic_setting_data_fields['company']['language_data'][$key][$section_key][$field_key] = '';

                                if(!empty($company_language_data)){                                
                                    $search = array('bookingpress_element_type' => $section_key, 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key);
                                    $keys = array_keys(array_filter($company_language_data, function ($v) use ($search) { 
                                                return $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                            }
                                    ));
                                    $index_val = isset($keys[0]) ? $keys[0] : '';
                                    if($index_val!='' || $index_val == 0) {
                                        if(isset($company_language_data[$index_val])){
                                            $translated_data = $company_language_data[$index_val];
                                            $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                            $bookingpress_dynamic_setting_data_fields['company']['language_data'][$key][$section_key][$field_key] = $bp_translated_str;    
                                        }
                                    }
                                }                            

                            }
                        }
                    }
                }

                $response['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
                $response['company_language_fields_data'] = $bookingpress_dynamic_setting_data_fields['company_language_fields_data'];
                $response['company']['language_data'] = $bookingpress_dynamic_setting_data_fields['company']['language_data'];
                $response['empty_selected_language'] = $bookingpress_dynamic_setting_data_fields['empty_selected_language'];
                $response['bookingpress_get_selected_languages'] = $bookingpress_get_selected_languages;
                $response['bookingpress_company_language_section_title'] = $bookingpress_company_language_section_title;                

            }
            if( 'message_setting' == $bookingpress_posted_data['setting_type']){
                
                global $bookingpress_all_language_translation_fields;

                $bookingpress_all_language_translation_fields = $this->bookingpress_all_language_translation_fields();
                $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
                if(empty($bookingpress_get_selected_languages)){
                    $bookingpress_get_selected_languages = array();
                }

                $bookingpress_dynamic_setting_data_fields = array();
                $bookingpress_message_language_translate_fields = array();
                $bookingpress_message_language_translate_fields['message_setting'] = $bookingpress_all_language_translation_fields['message_setting'];  
                
                $bookingpress_message_language_section_title = $this->bookingpress_get_language_translation_section_label($bookingpress_message_language_translate_fields);

                $bookingpress_current_selected_lang = '';
                $bookingpress_dynamic_setting_data_fields['empty_selected_language'] = 0;
                if(empty($bookingpress_get_selected_languages)){
                    $bookingpress_dynamic_setting_data_fields['empty_selected_language'] = 1;
                }
                $bookingpress_dynamic_setting_data_fields['message_language_fields_data'] = array();
                $bookingpress_dynamic_setting_data_fields['message_language_data'] = array();
                if(!empty($bookingpress_get_selected_languages)){                
                    $message_language_save_data = $this->bookingpress_get_language_data_for_backend(0,'message_setting');
                    foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                        if(empty($bookingpress_current_selected_lang)){
                            $bookingpress_current_selected_lang = $key;
                        }            
                        foreach($bookingpress_message_language_translate_fields as $section_key=>$service_lang){                                                
                            foreach($service_lang as $field_key => $field_value){                            
                                $bookingpress_dynamic_setting_data_fields['message_language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                                $bookingpress_dynamic_setting_data_fields['message_language_data'][$key][$section_key][$field_key] = ''; 
    
                                if(!empty($message_language_save_data)){                                
                                    $search = array('bookingpress_element_type' => $section_key, 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key);
                                    $keys = array_keys(array_filter($message_language_save_data, function ($v) use ($search) { 
                                                return $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                            }
                                    ));
                                    $index_val = isset($keys[0]) ? $keys[0] : '';
                                    if($index_val!='' || $index_val == 0) {
                                        if(isset($message_language_save_data[$index_val])){
                                            $translated_data = $message_language_save_data[$index_val];
                                            $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                            $bookingpress_dynamic_setting_data_fields['message_language_data'][$key][$section_key][$field_key] = $bp_translated_str;    
                                        }
                                    }
                                }                            
    
                            }
                        }
                    }
                }                                
                $response['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
                $response['message_language_fields_data'] = $bookingpress_dynamic_setting_data_fields['message_language_fields_data'];
                $response['message_language_data'] = $bookingpress_dynamic_setting_data_fields['message_language_data'];
                $response['empty_selected_language'] = $bookingpress_dynamic_setting_data_fields['empty_selected_language'];
                $response['bookingpress_get_selected_languages'] = $bookingpress_get_selected_languages;
                $response['bookingpress_message_language_section_title'] = $bookingpress_message_language_section_title;


            }
            return $response;
        }

        /**
         * Modified Setting response
         *
         * @return void
         */
        function bookingpress_get_settings_details_response_func(){
        ?>
        if(settingType == 'customer_setting'){
           if(response.data.bookingpress_current_selected_lang != 'undefined'){
               vm.bookingpress_current_selected_lang = response.data.bookingpress_current_selected_lang;                
           }
           if(response.data.customer_language_fields_data != 'undefined'){
               vm.customer_language_fields_data = response.data.customer_language_fields_data;                
           }  
           if(response.data.customer_language_data != 'undefined'){
               vm.customer_language_data = response.data.customer_language_data;                
           } 
           if(response.data.empty_selected_language != 'undefined'){
               vm.empty_selected_language = response.data.empty_selected_language;                
           } 
           if(response.data.bookingpress_get_selected_languages != 'undefined'){
               vm.bookingpress_get_selected_languages = response.data.bookingpress_get_selected_languages;                
           } 
           if(response.data.bookingpress_customer_form_fields_language_section_title != 'undefined'){
               vm.bookingpress_customer_form_fields_language_section_title = response.data.bookingpress_customer_form_fields_language_section_title;                
           } 
        }
        
        if(settingType == 'company_setting'){
           
           if(response.data.bookingpress_current_selected_lang != 'undefined'){
               vm.bookingpress_current_selected_lang = response.data.bookingpress_current_selected_lang;                
           }
           if(response.data.company_language_fields_data != 'undefined'){
               vm.company_language_fields_data = response.data.company_language_fields_data;                
           }           
           if(response.data.company != 'undefined'){
               vm.company.language_data = response.data.company.language_data;                
           }  
           if(response.data.empty_selected_language != 'undefined'){
               vm.empty_selected_language = response.data.empty_selected_language;                
           }  
           if(response.data.bookingpress_get_selected_languages != 'undefined'){
               vm.bookingpress_get_selected_languages = response.data.bookingpress_get_selected_languages;                
           }     
           if(response.data.bookingpress_company_language_section_title != 'undefined'){
               vm.bookingpress_company_language_section_title = response.data.bookingpress_company_language_section_title;                
           }                                   
        }

        if(settingType == 'message_setting'){
           
            if(response.data.bookingpress_message_language_section_title != 'undefined'){
                vm.bookingpress_message_language_section_title = response.data.bookingpress_message_language_section_title;                
            }
            if(response.data.bookingpress_current_selected_lang != 'undefined'){
                vm.bookingpress_current_selected_lang = response.data.bookingpress_current_selected_lang;                
            }
            if(response.data.message_language_fields_data != 'undefined'){
                vm.message_language_fields_data = response.data.message_language_fields_data;                
            }
            if(response.data.message_language_data != 'undefined'){
                vm.message_language_data = response.data.message_language_data;                
            }
            if(response.data.bookingpress_get_selected_languages != 'undefined'){
                vm.bookingpress_get_selected_languages = response.data.bookingpress_get_selected_languages;                
            }  
            if(response.data.empty_selected_language != 'undefined'){
                vm.empty_selected_language = response.data.empty_selected_language;                
            }           
           /*
            if(response.data.data.bookingpress_current_selected_lang != 'undefined'){
                vm.bookingpress_current_selected_lang = response.data.data.bookingpress_current_selected_lang;                
            }
            if(response.data.data.message_language_fields_data != 'undefined'){
                vm.message_language_fields_data = response.data.data.message_language_fields_data;                
            }
            if(response.data.data.message_language_data != 'undefined'){
                vm.message_language_data = response.data.data.message_language_data;                
            }
            if(response.data.data.bookingpress_get_selected_languages != 'undefined'){
                vm.bookingpress_get_selected_languages = response.data.data.bookingpress_get_selected_languages;                
            }  
            if(response.data.data.empty_selected_language != 'undefined'){
                vm.empty_selected_language = response.data.data.empty_selected_language;                
            }
            */                                                
          }  
        <?php 
        }

		
		/**
		 * Function for modified multi-language settings
		 *
		 * @param  mixed $bookingpress_save_settings_data
		 * @param  mixed $posted_data
		 * @return void
		 */
		function bookingpress_modify_save_setting_data_func($bookingpress_save_settings_data,$posted_data) {			
			if(!empty($posted_data['settingType']) && $posted_data['settingType'] == 'general_setting' && isset($bookingpress_save_settings_data['bookingpress_selected_languages'])) {				
				if(!empty($bookingpress_save_settings_data['bookingpress_selected_languages'])){
                    $bookingpress_save_settings_data['bookingpress_selected_languages'] = implode(",",$bookingpress_save_settings_data['bookingpress_selected_languages']);
                }
			} elseif(!empty($posted_data['settingType']) && $posted_data['settingType'] == 'general_setting') {
				$bookingpress_save_settings_data['bookingpress_selected_languages'] = '';
			}
			return $bookingpress_save_settings_data;
		}        

        /**
         * Function for add settings dynamic vue method
         *
         * @return void
         */
        function bookingpress_add_setting_dynamic_vue_methods(){
        ?>
        change_setting_current_language(lang){
            var vm2 = this;            
            vm2.bookingpress_current_selected_lang = lang;
        },        
         general_settings_language_added(e){
            var vm = this;
            var has_added = true;
            if(vm.general_setting_form.bookingpress_selected_languages == ''){
                vm.general_setting_form.bookingpress_selected_languages = [];
            }
            var has_lang_added = true;
            if(vm.general_setting_form.bookingpress_selected_languages.length > 0){
                if(vm.general_setting_form.bookingpress_selected_languages.includes(e)){
                    has_lang_added = false;
                }
            }        
            if(has_lang_added){
                vm.general_setting_form.bookingpress_selected_languages.push(e);
            }                
         },   
         bookingpress_removed_selected_language(lang){
            var vm = this;     
            var temp_bookingpress_selected_languages = vm.general_setting_form.bookingpress_selected_languages;       
            const index = temp_bookingpress_selected_languages.indexOf(lang);
            if (index > -1) { 
                temp_bookingpress_selected_languages.splice(index, 1); 
                vm.general_setting_form.bookingpress_selected_languages = temp_bookingpress_selected_languages;
            }
         },
         bookingpress_open_setting_popup(type){
            var vm = this;
            vm.bookingpress_current_selected_lang = vm.bookingpress_current_selected_lang_org;
            if(type == 'company_setting'){
                vm.open_company_detail_translate_language = true;
            }else if(type == 'message_setting'){
                vm.open_message_detail_translate_language = true;
            }
            else if(type == 'customer_setting'){
                vm.open_customer_detail_translate_language = true;
            }else if(type == 'invoice_setting'){
                vm.open_invoice_detail_translate_language = true;
            }             
         },
        <?php
        }

        function bookingpress_add_invoice_settings_more_postdata_func(){
        ?>
            saveFormData.language_setting_data = vm.invoice_language_data;
        <?php 
        }

        function bookingpress_add_settings_more_postdata_fun(){
        ?>            
            if(vm.selected_tab_name == 'company_settings'){
                saveFormData.language_setting_data = vm.company.language_data;
            }            
            if(vm.selected_tab_name == 'message_settings'){                
                saveFormData.language_setting_data = vm.message_language_data;
            }            
            if(vm.selected_tab_name == 'customer_settings'){ 
                saveFormData.language_customer_setting_data = vm.customer_language_data;
            }
        <?php 
        }

        
        /**
         * Function for after save setting data added
         *
         * @param  mixed $post_data
         * @return void
        */
        function boookingpress_after_save_settings_data_func($post_data){
            if(isset($post_data['language_setting_data']) && !empty($post_data['language_setting_data'])){

                $language_data = $post_data['language_setting_data'];
                if(is_array($language_data)){  
                    foreach($language_data as $lang_key=>$single_language_data){
                        foreach($single_language_data as $lang_section=>$lang_fields){
                            foreach($lang_fields as $lang_field_key=>$bp_translated_val){
                                $this->bookingpress_common_save_language_data($lang_key,$lang_section,$bp_translated_val,0,$lang_field_key);
                            }                            
                        }
                    } 
                }

            }
            if(isset($post_data['language_customer_setting_data']) && !empty($post_data['language_customer_setting_data'])){
                global $wpdb,$tbl_bookingpress_ml_translation;

                $language_data = $post_data['language_customer_setting_data'];
                if(is_array($language_data)){  
                    if(!empty($language_data) && is_array($language_data)){                        
                        $wpdb->delete($tbl_bookingpress_ml_translation,array('bookingpress_element_type' => 'customer_custom_form_fields'));                    
                        foreach($language_data as $lang_key=>$single_language_data){                    
                            foreach($single_language_data as $lang_section=>$lang_fields){
                                foreach($lang_fields as $lang_field_key=>$bp_all_cat_data){
                                    foreach($bp_all_cat_data as $f_id=>$bp_translated_val){

                                        if($lang_field_key == 'bookingpress_field_values'){
                                            if(is_array($bp_translated_val)){
                                                $bp_translated_val = json_encode($bp_translated_val,JSON_UNESCAPED_UNICODE);
                                            }else{
                                                $bp_translated_val = json_encode(array());
                                            }
                                            $this->bookingpress_common_save_language_data($lang_key,$lang_section,$bp_translated_val,$f_id,$lang_field_key);
                                        }else{
                                            $this->bookingpress_common_save_language_data($lang_key,$lang_section,$bp_translated_val,$f_id,$lang_field_key);
                                        }                                                                                 
                                    }
                                }
                            }
                        }
                    }
                }

            }            
        }

        /**
         * Function for add settings dynamic data
         *
         * @param  mixed $bookingpress_dynamic_setting_data_fields
         * @return void
         */
        function bookingpress_add_setting_dynamic_data_fields_func($bookingpress_dynamic_setting_data_fields){
            global $BookingPress,$bookingpress_all_language_translation_fields;
            if(!empty($bookingpress_dynamic_setting_data_fields['general_setting_form'])){                
                $bookingpress_all_language_list = $this->get_all_wp_languages_list();
                $bp_translation_lang = $this->bookingpress_get_front_current_language();                                 
                $get_wp_default_lang = $this->bookingpress_get_wordpress_default_lang();
                if(isset($bookingpress_all_language_list[$get_wp_default_lang])){
                    unset($bookingpress_all_language_list[$get_wp_default_lang]);
                }                                                                                     
                $BookingPress->bookingpress_update_settings('bookingpress_default_language', 'general_setting', $get_wp_default_lang);                
                $bookingpress_dynamic_setting_data_fields['bookingpress_all_language_list'] = $bookingpress_all_language_list;                
                $bookingpress_dynamic_setting_data_fields['bookingpress_selected_languages'] = [];
                $bookingpress_dynamic_setting_data_fields['general_setting_form']['bookingpress_selected_languages'] = [];
            }

            /*  Company language data added */
            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_get_selected_languages = array();
            }
            $bookingpress_company_language_translate_fields = array();
            $bookingpress_company_language_translate_fields['company_setting'] = $bookingpress_all_language_translation_fields['company_setting'];                         
            $bookingpress_company_language_section_title = $this->bookingpress_get_language_translation_section_label($bookingpress_company_language_translate_fields);

            $bookingpress_current_selected_lang = '';
            $bookingpress_dynamic_setting_data_fields['empty_selected_language'] = 0;
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_dynamic_setting_data_fields['empty_selected_language'] = 1;
            }

            $bookingpress_dynamic_setting_data_fields['company_language_fields_data'] = array();
            $bookingpress_dynamic_setting_data_fields['company']['language_data'] = array();
            if(!empty($bookingpress_get_selected_languages)){
                
                $company_language_data = $this->bookingpress_get_language_data_for_backend(0,'company_setting');
                foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                    if(empty($bookingpress_current_selected_lang)){
                        $bookingpress_current_selected_lang = $key;
                    }            
                    foreach($bookingpress_company_language_translate_fields as $section_key=>$service_lang){                                                
                        foreach($service_lang as $field_key => $field_value){                            
                            $bookingpress_dynamic_setting_data_fields['company_language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                            $bookingpress_dynamic_setting_data_fields['company']['language_data'][$key][$section_key][$field_key] = ''; 

                            if(!empty($company_language_data)){                                
                                $search = array('bookingpress_element_type' => $section_key, 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key);
                                $keys = array_keys(array_filter($company_language_data, function ($v) use ($search) { 
                                            return $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                        }
                                ));
                                $index_val = isset($keys[0]) ? $keys[0] : '';
                                if($index_val!='' || $index_val == 0) {
                                    
                                    if(isset($company_language_data[$index_val])){
                                        
                                        $translated_data = $company_language_data[$index_val];
                                        $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                        $bookingpress_dynamic_setting_data_fields['company']['language_data'][$key][$section_key][$field_key] = $bp_translated_str;

                                    }

                                }
                            }                            

                        }
                    }
                }
            }


            $bookingpress_dynamic_setting_data_fields['message_language_fields_data'] = array();
            $bookingpress_dynamic_setting_data_fields['message_language_data'] = array();

            $bookingpress_dynamic_setting_data_fields['customer_language_fields_data'] = array();
            $bookingpress_dynamic_setting_data_fields['customer_language_data'] = array();
            $bookingpress_dynamic_setting_data_fields['bookingpress_customer_form_fields_language_section_title'] = array();            
            
            $bookingpress_dynamic_setting_data_fields['bookingpress_company_language_section_title'] = $bookingpress_company_language_section_title;
            $bookingpress_dynamic_setting_data_fields['bookingpress_message_language_section_title'] = array();

            $bookingpress_dynamic_setting_data_fields['bookingpress_get_selected_languages'] = $bookingpress_get_selected_languages;
            $bookingpress_dynamic_setting_data_fields['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
            $bookingpress_dynamic_setting_data_fields['bookingpress_current_selected_lang_org'] = $bookingpress_current_selected_lang;
            $bookingpress_dynamic_setting_data_fields['open_company_detail_translate_language'] = false;
            $bookingpress_dynamic_setting_data_fields['open_message_detail_translate_language'] = false;
            $bookingpress_dynamic_setting_data_fields['open_customer_detail_translate_language'] = false;

            if(is_plugin_active('bookingpress-invoice/bookingpress-invoice.php')) {

                $bookingpress_dynamic_setting_data_fields['invoice_language_fields_data'] = array();
                $bookingpress_dynamic_setting_data_fields['invoice_language_data'] = array();
                $bookingpress_dynamic_setting_data_fields['open_invoice_detail_translate_language'] = false;

                $bookingpress_invoice_language_translate_fields = array();
                $bookingpress_invoice_language_translate_fields['invoice_setting'] = $bookingpress_all_language_translation_fields['invoice_setting'];                         
                $bookingpress_dynamic_setting_data_fields['bookingpress_invoice_language_section_title'] = $this->bookingpress_get_language_translation_section_label($bookingpress_invoice_language_translate_fields);
                if(!empty($bookingpress_get_selected_languages)){

                    $invoice_save_language_data = $this->bookingpress_get_language_data_for_backend(0,'invoice_setting');
                    foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                        if(empty($bookingpress_current_selected_lang)){
                            $bookingpress_current_selected_lang = $key;
                        }            
                        foreach($bookingpress_invoice_language_translate_fields as $section_key=>$service_lang){                                                
                            foreach($service_lang as $field_key => $field_value){                            
                                $bookingpress_dynamic_setting_data_fields['invoice_language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                                $bookingpress_dynamic_setting_data_fields['invoice_language_data'][$key][$section_key][$field_key] = ''; 
    
                                if(!empty($invoice_save_language_data)){                                
                                    $search = array('bookingpress_element_type' => $section_key, 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key);
                                    $keys = array_keys(array_filter($invoice_save_language_data, function ($v) use ($search) { 
                                                return $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                            }
                                    ));
                                    $index_val = isset($keys[0]) ? $keys[0] : '';
                                    if($index_val!='' || $index_val == 0) {
                                        if(isset($invoice_save_language_data[$index_val])){
                                            $translated_data = $invoice_save_language_data[$index_val];
                                            $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                            $bookingpress_dynamic_setting_data_fields['invoice_language_data'][$key][$section_key][$field_key] = $bp_translated_str;    
                                        }
                                    }
                                }                            
    
                            }
                        }
                    }                    

                }                
            }
            return $bookingpress_dynamic_setting_data_fields;
        }


        /**
         * General Settings Added
         *
         * @return void
         */
        function bookingpress_add_general_language_setting_section_func(){            
            include BOOKINGPRESS_MULTILANGUAGE_VIEW_DIR.'bookingpress_add_general_language_setting_section.php';
        }
        
        
        /**
         * Function for get language data for service edit
         *
         * @param  mixed $bookingpress_element_ref_id
         * @param  mixed $bookingpress_language_code
         * @return void
         */
        function bookingpress_get_language_data_for_backend($bookingpress_element_ref_id = 0,$bookingpress_element_type=''){            
            global $wpdb, $tbl_bookingpress_ml_translation;
            if(!is_array($bookingpress_element_type) && !empty($bookingpress_element_type)){
                $bookingpress_element_type = array($bookingpress_element_type);          
            }
            if(!empty($bookingpress_element_type)){
                $eltype_data = '';
                $i=1;
                foreach($bookingpress_element_type as $eltype){
                    if($i == 1){
                        $eltype_data.="'".$eltype."'";
                    }else{
                        $eltype_data.=",'".$eltype."'";
                    }                    
                    $i++;
                }   
                $bookingpress_element_type =  $eltype_data;  
            }            
            $bookingpress_language_return_data = array();
            if($bookingpress_element_ref_id != 0 && empty($bookingpress_element_type)){
                $bookingpress_language_return_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$tbl_bookingpress_ml_translation} WHERE bookingpress_element_ref_id = %s ",$bookingpress_element_ref_id), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_ml_translation is table name defined globally. False Positive alarm
            }else if($bookingpress_element_ref_id == 0 && !empty($bookingpress_element_type)){                
                $bookingpress_language_return_data = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_ml_translation} WHERE bookingpress_element_type IN(".$bookingpress_element_type.")", ARRAY_A);//phpcs:ignore
            }else if($bookingpress_element_ref_id != 0 && !empty($bookingpress_element_type)){               
                $bookingpress_language_return_data = $wpdb->get_results("SELECT * FROM {$tbl_bookingpress_ml_translation} WHERE bookingpress_element_ref_id = '".$bookingpress_element_ref_id."' AND bookingpress_element_type IN(".$bookingpress_element_type.") ", ARRAY_A);//phpcs:ignore
            }
            return $bookingpress_language_return_data;
        }

        /**
         * Function for save common language data
         *
         * @return void
         */
        function bookingpress_common_save_language_data($bookingpress_language_code,$bookingpress_element_type,$bookingpress_translated_value,$bookingpress_element_ref_id = 0,$bookingpress_ref_column_name=''){         
            
            global $tbl_bookingpress_ml_translation, $wpdb, $BookingPress;            
            $bookingpress_translated_value = (!empty($bookingpress_translated_value))?stripslashes_deep($bookingpress_translated_value):'';
            $args = array(
                'bookingpress_element_type' => $bookingpress_element_type,
                'bookingpress_language_code' => $bookingpress_language_code,
                'bookingpress_translated_value' => $bookingpress_translated_value,
                'bookingpress_element_ref_id'=> $bookingpress_element_ref_id,
                'bookingpress_ref_column_name'=> $bookingpress_ref_column_name,
            );
            $bookingpress_lang_translation_details = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_translation_id FROM {$tbl_bookingpress_ml_translation} WHERE bookingpress_element_type = %s AND bookingpress_ref_column_name = %s AND bookingpress_language_code = %s AND bookingpress_element_ref_id = %s",$bookingpress_element_type, $bookingpress_ref_column_name,$bookingpress_language_code ,$bookingpress_element_ref_id), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_ml_translation is table name defined globally. False Positive alarm 
            if(empty($bookingpress_lang_translation_details) ) {
                $date = current_time('mysql');
                $args['bookingpress_translation_created_date'] = $date;
                if(!empty($bookingpress_translated_value)){
                    $wpdb->insert($tbl_bookingpress_ml_translation, $args);
                }
            }else{
                $bookingpress_translation_id = $bookingpress_lang_translation_details['bookingpress_translation_id'];
                $wpdb->update($tbl_bookingpress_ml_translation, $args, array( 'bookingpress_translation_id' => $bookingpress_translation_id ));
            }
           
        }

        /**
         * Function for save service language data
         *
         * @param  mixed $response
         * @param  mixed $service_id
         * @param  mixed $posted_data
         * @return void
         */
        function bookingpress_save_service_details($response, $service_id, $posted_data){            
            global $bookingpress_services,$tbl_bookingpress_ml_translation, $wpdb, $BookingPress;
            if( !empty( $service_id ) && isset($posted_data['language_data']) && !empty($posted_data['language_data'])){
                 if(is_array($posted_data['language_data'])){                    
                    foreach($posted_data['language_data'] as $lang_key=>$single_language_data){
                        foreach($single_language_data as $lang_section=>$lang_fields){
                            foreach($lang_fields as $lang_field_key=>$bp_translated_val){
                                $this->bookingpress_common_save_language_data($lang_key,$lang_section,$bp_translated_val,$service_id,$lang_field_key);
                            }                            
                        }
                    }
                 }   
            }
            return $response;
        }

        /**
         * Function for add service dynamic vue method.
         *
         * @return void
         */
        function bookingpress_add_service_dynamic_vue_methods_func(){
            global $bookingpress_global_options,$bookingpress_notification_duration;         
        ?>
        open_service_translate_language_modal(type=''){
            var vm2 = this;
            if(type == 'category'){                
                vm2.open_category_translate_language = true;
            }else{
                vm2.open_service_translate_language = true;
            }
        },
        change_service_current_language(lang){
            var vm2 = this;            
            vm2.bookingpress_current_selected_lang = lang;
        },
        change_category_current_language(lang){
            var vm2 = this;            
            vm2.bookingpress_current_selected_cat_lang = lang;
        },
        save_category_language_data(){
            var vm2 = this;
            if(vm2.is_display_category_save_loader == '0'){
                vm2.is_display_category_save_loader = '1';
                var postData = { action:'bookingpress_save_category_language_data',_wpnonce:'<?php echo esc_html(wp_create_nonce('bpa_wp_nonce')); ?>' };
                postData.category_language = JSON.stringify(vm2.category_language);
                axios.post( appoint_ajax_obj.ajax_url, Qs.stringify( postData ) )
                    .then(function(response){                                                        
                        vm2.is_display_category_save_loader = '0';                            
                        if(response.data.variant != 'error') { 
                            vm2.open_category_translate_language = false;
                        }
                        vm2.$notify({
                            title: response.data.title,
                            message: response.data.msg,
                            type: response.data.variant,
                            customClass: response.data.variant+'_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });                                                   
                    }).catch(function(error){
                        vm2.is_display_category_save_loader = '1';                            
                        console.log(error);
                        vm2.$notify({
                            title: '<?php esc_html_e('Error', 'bookingpress-multilanguage'); ?>',
                            message: '<?php esc_html_e('Something went wrong..', 'bookingpress-multilanguage'); ?>',
                            type: 'error',
                            customClass: 'error_notification',
                            duration:<?php echo intval($bookingpress_notification_duration); ?>,
                        });                            
                    });                
                    }            
        },      
        <?php  
        }

		
		/**
		 * After delete Service language fields remove
		 *
		 * @param  mixed $service_id
		 * @return void
		 */
		function bookingpress_after_delete_service_func( $service_id ) {
			global $wpdb,$tbl_bookingpress_ml_translation;
			$wpdb->delete( $tbl_bookingpress_ml_translation, array('bookingpress_element_type'=>'service', 'bookingpress_element_ref_id' => $service_id ));
		}

        /* Duplicate language details of duplicated service */
        function bookingpress_duplicate_more_details_func($duplicated_service_id, $original_service_id){
        	global $wpdb,$tbl_bookingpress_ml_translation,$bookingpress_services;
			$bookingpress_service_translation_data = $this->bookingpress_get_language_data_for_backend($original_service_id ,'service');
            if(!empty($bookingpress_service_translation_data)){
                foreach($bookingpress_service_translation_data as $service_translation_data){
                    $bookingpress_duplicate_service_translarion_arr = array(
                        'bookingpress_element_type' => 'service',
						'bookingpress_ref_column_name' => $service_translation_data['bookingpress_ref_column_name'],
						'bookingpress_element_ref_id' => $duplicated_service_id,
						'bookingpress_language_code' => $service_translation_data['bookingpress_language_code'],
						'bookingpress_translated_value' => $service_translation_data['bookingpress_translated_value'],
						'bookingpress_translation_created_date' => current_time( 'mysql' ),
					);
                    $wpdb->insert($tbl_bookingpress_ml_translation, $bookingpress_duplicate_service_translarion_arr);
                }                
			}	
        }

        /* Duplicate language details of duplicated extra service */
        function bookingpress_after_duplicate_service_extra_func($duplicated_extra_service_id, $original_extra_service_id)
        {
            global $wpdb,$tbl_bookingpress_ml_translation,$tbl_bookingpress_extra_services;
            $bookingpress_extra_service_translation_data = $this->bookingpress_get_language_data_for_backend($original_extra_service_id ,'service_extra');
            if(!empty($bookingpress_extra_service_translation_data)){
                foreach($bookingpress_extra_service_translation_data as $service_translation_data){
                    $bookingpress_duplicate_service_translarion_arr = array(
                        'bookingpress_element_type' => 'service_extra',
						'bookingpress_ref_column_name' => $service_translation_data['bookingpress_ref_column_name'],
						'bookingpress_element_ref_id' => $duplicated_extra_service_id,
						'bookingpress_language_code' => $service_translation_data['bookingpress_language_code'],
						'bookingpress_translated_value' => $service_translation_data['bookingpress_translated_value'],
						'bookingpress_translation_created_date' => current_time( 'mysql' ),
					);
                    $wpdb->insert($tbl_bookingpress_ml_translation, $bookingpress_duplicate_service_translarion_arr);
                }                
			}			
        }

        /**
         * Function for clear add popup data
         *
         * @return void
         */
        function bookingpress_after_open_add_service_model_fun() {
        ?>
            if(action == 'add') {
                vm.save_happy_hours_data = [];
                vm.bookingpress_current_selected_lang = vm.bookingpress_current_selected_lang_org;
                vm.service.language_data = vm.service.language_data_org;  
                vm.service.service_extra_language_data = vm.service.service_extra_language_data_org;           
            }            
        <?php
        } 


        /**
         * bookingpress_add_location_data_for_service_xhr_response
         *
         * @return void
        */
        function bookingpress_edit_service_data_for_service_xhr_response(){
        ?>
            if(typeof response.data.bookingpress_service_edit_language_data !== 'undefined'){
                vm2.service.language_data = response.data.bookingpress_service_edit_language_data;
            }
            if(typeof response.data.service_extra_language_data !== 'undefined'){
                vm2.service.service_extra_language_data = response.data.service_extra_language_data;
            }            
            if(typeof response.data.bookingpress_current_selected_lang !== 'undefined'){
                vm2.bookingpress_current_selected_lang = response.data.bookingpress_current_selected_lang;
            }            
        <?php
        }


        /**
         * filter service data and add location details while editing appointments
         *
         * @param  mixed $response
         * @param  mixed $service_id
         * @return void
        */
        function bookingpress_modify_edit_service_data_func( $response, $service_id ){

            global  $tbl_bookingpress_extra_services,$wpdb,$tbl_bookingpress_extra_services,$bookingpress_service_extra,$bookingpress_options,$BookingPress,$bookingpress_all_language_translation_fields;  
            $language_data = $this->bookingpress_get_language_data_for_backend($service_id,array('service','happy_hours'));
            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
            $bookingpress_services_vue_data_fields = array();
            $bookingpress_service_language_translate_fields = array();
            $bookingpress_service_language_translate_fields['service'] = $bookingpress_all_language_translation_fields['service'];
            $bookingpress_service_language_translate_fields = apply_filters( 'bookingpress_modified_service_language_translate_fields',$bookingpress_service_language_translate_fields);                         
            $bookingpress_service_edit_language_data = array();
            $bookingpress_current_selected_lang = '';

            if(!empty($bookingpress_get_selected_languages)){
                foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                    if(empty($bookingpress_current_selected_lang)){
                        $bookingpress_current_selected_lang = $key;
                    }            
                    foreach($bookingpress_service_language_translate_fields as $section_key=>$service_lang){                                                
                        foreach($service_lang as $field_key => $field_value){ 
                            $bookingpress_service_edit_language_data[$key][$section_key][$field_key] = '';
                            if(!empty($language_data)){                                
                                $search = array('bookingpress_element_type' => $section_key, 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key);
                                $keys = array_keys(array_filter($language_data, function ($v) use ($search) { 
                                            return $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                        }
                                ));
                                $index_val = isset($keys[0]) ? $keys[0] : '';
                                if($index_val!='' || $index_val == 0) {
                                    if(isset($language_data[$index_val])){
                                        $translated_data = $language_data[$index_val];
                                        $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                        $bookingpress_service_edit_language_data[$key][$section_key][$field_key] = $bp_translated_str;    
                                    }
                                }
                            }
                        }
                    }
                }                
            }

            if(!empty($bookingpress_service_edit_language_data)){
                $response['bookingpress_service_edit_language_data'] = $bookingpress_service_edit_language_data;
            }
            $response['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
            if($bookingpress_service_extra->bookingpress_check_service_extra_module_activation()){
                $bookingpress_service_extra_language_translate_fields = $bookingpress_all_language_translation_fields['service_extra'];
                $bookingpress_services_vue_data_fields['service_extra_language_data'] = array();
                if(!empty($bookingpress_get_selected_languages)){
                    $language_data = $this->bookingpress_get_language_data_for_backend(0,'service_extra');
                    foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                        foreach($bookingpress_service_extra_language_translate_fields as $field_key => $field_value){
                            $bookingpress_get_service_extras = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $tbl_bookingpress_extra_services WHERE bookingpress_service_id = %d", $service_id), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_extra_services is table name defined globally. False Positive alarm
                            $i=0;
                            foreach($bookingpress_get_service_extras as $extra){                                
                                $bookingpress_services_vue_data_fields['service_extra_language_data'][$key]['service_extra'][$field_key][$i] = '';
                                if(!empty($language_data)){                                
                                    $search = array('bookingpress_element_type' => 'service_extra', 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key, 'bookingpress_element_ref_id'=> $extra['bookingpress_extra_services_id']);
                                    $keys = array_keys(array_filter($language_data, function ($v) use ($search) { 
                                                return $v['bookingpress_element_ref_id'] == $search['bookingpress_element_ref_id'] && $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                            }
                                    ));
                                    $index_val = isset($keys[0]) ? $keys[0] : '';
                                    if($index_val!='' || $index_val == 0) {
                                        if(isset($language_data[$index_val])){
                                            $translated_data = $language_data[$index_val];
                                            $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                            $bookingpress_services_vue_data_fields['service_extra_language_data'][$key]['service_extra'][$field_key][$i] = $bp_translated_str;    
                                        }
                                    }
                                }
                                $i++;
                            }                            
                            $limit = $i+20;
                            for($j=$i;$j<$limit;$j++){
                                $bookingpress_services_vue_data_fields['service_extra_language_data'][$key]['service_extra'][$field_key][$j] = '';
                            }                        
                        }                    
                    }
                }                
                if(isset($bookingpress_services_vue_data_fields['service_extra_language_data']) && !empty($bookingpress_services_vue_data_fields['service_extra_language_data'])){
                    $response['service_extra_language_data'] = $bookingpress_services_vue_data_fields['service_extra_language_data'];
                }
            }
            return $response;
        }

        /**
         * Function for modified service data
         *
         * @param  mixed $bookingpress_services_vue_data_fields
         * @return void
         */
        function bookingpress_modify_service_data_fields_func($bookingpress_services_vue_data_fields) {
            
            global $wpdb,$tbl_bookingpress_categories,$bookingpress_options,$BookingPress,$bookingpress_all_language_translation_fields_section,$bookingpress_all_language_translation_fields;            
            
            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_get_selected_languages = array();
            }

            $bookingpress_service_language_translate_fields = array();
            $bookingpress_service_language_translate_fields['service'] = $bookingpress_all_language_translation_fields['service'];
            $bookingpress_service_language_translate_fields = apply_filters( 'bookingpress_modified_service_language_translate_fields',$bookingpress_service_language_translate_fields);             
            $bookingpress_service_language_section_title = $this->bookingpress_get_language_translation_section_label($bookingpress_service_language_translate_fields);

            $bookingpress_services_vue_data_fields['service_language_translate_fields'] = $bookingpress_service_language_translate_fields;            
            $bookingpress_services_vue_data_fields['bookingpress_get_selected_languages'] = $bookingpress_get_selected_languages;
            $bookingpress_services_vue_data_fields['bookingpress_service_language_section_title'] = $bookingpress_service_language_section_title;
            $bookingpress_services_vue_data_fields['open_service_translate_language'] = false;
            
            $bookingpress_services_vue_data_fields['open_category_translate_language'] = false;

            

            $bookingpress_current_selected_lang = '';
            $bookingpress_services_vue_data_fields['empty_selected_language'] = 0;
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_services_vue_data_fields['empty_selected_language'] = 1;
            }            
            $bookingpress_services_vue_data_fields['language_data'] = array();
            if(!empty($bookingpress_get_selected_languages)){
                foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                    if(empty($bookingpress_current_selected_lang)){
                        $bookingpress_current_selected_lang = $key;
                    }            
                    foreach($bookingpress_service_language_translate_fields as $section_key=>$service_lang){                                                
                        foreach($service_lang as $field_key => $field_value){                            
                            $bookingpress_services_vue_data_fields['language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                            $bookingpress_services_vue_data_fields['service']['language_data'][$key][$section_key][$field_key] = ''; 
                            $bookingpress_services_vue_data_fields['service']['language_data_org'][$key][$section_key][$field_key] = '';      
                        }
                    }
                }
            }
            $bookingpress_services_vue_data_fields['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
            $bookingpress_services_vue_data_fields['bookingpress_current_selected_lang_org'] = $bookingpress_current_selected_lang;
           

            /* Category lang variable data  */
            $bookingpress_services_vue_data_fields['category_language_fields_data'] = array();
            $bookingpress_services_vue_data_fields['is_display_category_save_loader'] = '0';
            $bookingpress_services_vue_data_fields['category_language'] = array();
            $bookingpress_services_vue_data_fields['bookingpress_current_selected_cat_lang'] = '';

            $bookingpress_current_selected_cat_lang = '';
            $bookingpress_category_language_translate_fields['category'] = $bookingpress_all_language_translation_fields['category'];
            


            $bookingpress_services_vue_data_fields['bookingpress_current_selected_cat_lang'] = $bookingpress_current_selected_cat_lang;

            /* Service Extra Fields Start Here */            
            $bookingpress_service_extra_language_translate_fields = $bookingpress_all_language_translation_fields['service_extra'];
            $bookingpress_services_vue_data_fields['bookingpress_service_extra_language_translate_fields'] = $bookingpress_service_extra_language_translate_fields;
            $bookingpress_services_vue_data_fields['service']['service_extra_language_data'] = array();
            $bookingpress_services_vue_data_fields['service']['service_extra_language_data_org'] = array();
            if(!empty($bookingpress_get_selected_languages)){
                foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                    foreach($bookingpress_service_extra_language_translate_fields as $field_key => $field_value){
                        for($i=0;$i<20;$i++){
                            $bookingpress_services_vue_data_fields['service']['service_extra_language_data'][$key]['service_extra'][$field_key][$i] = array();
                            $bookingpress_services_vue_data_fields['service']['service_extra_language_data_org'][$key]['service_extra'][$field_key][$i] = array();
                        }                        
                    }                    
                }
            }
            /* Service Extra Fields Over Here */

            return $bookingpress_services_vue_data_fields;
        }

        /**
         * Function for add service language translation model
         *
         * @return void
        */
        function bookingpress_service_language_translation_popup_fun(){            
            include BOOKINGPRESS_MULTILANGUAGE_VIEW_DIR.'bookingpress_service_language_translation_popup.php';
        }

                
        /**
         * Function for save category language data
         *
         * @return void
         */
        function bookingpress_save_category_language_data_func(){
            global $BookingPress,$wpdb,$tbl_bookingpress_ml_translation;
            if( !empty( $_POST['category_language'] ) && !is_array( $_POST['category_language'] ) ){ //phpcs:ignore
				$_POST['category_language'] = json_decode( stripslashes_deep( $_POST['category_language'] ), true ); //phpcs:ignore
			}
            $wpnonce               = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
            $bpa_valid_nonce = wp_verify_nonce($wpnonce, 'bpa_wp_nonce' );            
            if($bpa_valid_nonce == false){
                $response['variant'] = 'error';
                $response['title']   = esc_html__('Error', 'bookingpress-multilanguage');
                $response['msg']     = esc_html__('Sorry, Your request can not be processed due to security reason.', 'bookingpress-multilanguage');   
                wp_send_json( $response );
                die;
            }
            $response['variant'] = 'error';
            $response['title']   = esc_html__('Error', 'bookingpress-multilanguage');
            $response['msg']     = esc_html__('Something went wrong..', 'bookingpress-multilanguage');

            $category_language = !empty($_POST['category_language'])?array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['category_language']):array();  // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --Reason $_POST['category_language'] contains array and sanitized properly using appointment_sanatize_field function 
            if(!empty($category_language) && is_array($category_language)){
                $wpdb->delete($tbl_bookingpress_ml_translation,array('bookingpress_element_type' => 'category'));
                foreach($category_language as $lang_key=>$single_language_data){                    
                    foreach($single_language_data as $lang_section=>$lang_fields){
                        foreach($lang_fields as $lang_field_key=>$bp_all_cat_data){
                            foreach($bp_all_cat_data as $cat_id=>$bp_translated_val){
                                $this->bookingpress_common_save_language_data($lang_key,$lang_section,$bp_translated_val,$cat_id,$lang_field_key);
                            }
                        }
                    }
                }
            }            
            $response['variant'] = 'success';
            $response['title']   = esc_html__('Success', 'bookingpress-multilanguage');
            $response['msg']     = esc_html__('Language data has been updated successfully.', 'bookingpress-multilanguage');
            wp_send_json( $response );
            die;
        }

        /* Customer Setting setting language added */
        function bookingpress_customer_setting_header_button_fun(){
        ?>
            <el-button class="bpa-btn bpa-btn--ml-translate" @click="bookingpress_open_setting_popup('customer_setting')">
                <span class="material-icons-round">translate</span>
                <?php esc_html_e('Translate', 'bookingpress-multilanguage'); ?>
            </el-button>            
        <?php 
        }

        /* Message setting language added */
        function bookingpress_message_setting_header_button_fun(){
        ?>
            <el-button class="bpa-btn bpa-btn--ml-translate" @click="bookingpress_open_setting_popup('message_setting')">
                <span class="material-icons-round">translate</span>
                <?php esc_html_e('Translate', 'bookingpress-multilanguage'); ?>
            </el-button>            
        <?php                          
        }

        /* Invoice Multi-language start here */
        
        /**
         * Function for add invoice language translation popup add
         *
         * @return void
         */
        function bookingpress_invoice_setting_header_extra_button_fun(){
        ?>
            <el-button class="bpa-btn bpa-btn--ml-translate" @click="bookingpress_open_setting_popup('invoice_setting')">
                <span class="material-icons-round">translate</span>
                <?php esc_html_e('Translate', 'bookingpress-multilanguage'); ?>
            </el-button>            
        <?php 
        }

        /**
         * Function for add company view data
         *
         * @return void
         */
        function bookingpress_invoice_setting_view_bottom_fun(){
            include BOOKINGPRESS_MULTILANGUAGE_VIEW_DIR.'bookingpress_invoice_settings_language_translation_popup.php';
        }


        /*  Company Settings language added */
        function bookingpress_company_setting_header_button_fun(){
        ?>
            <el-button class="bpa-btn bpa-btn--ml-translate" @click="bookingpress_open_setting_popup('company_setting')">
                <span class="material-icons-round">translate</span>
                <?php esc_html_e('Translate', 'bookingpress-multilanguage'); ?>
            </el-button>            
        <?php              
        }
                
        /**
         * Function for add company view data
         *
         * @return void
         */
        function bookingpress_company_view_data_after_fun(){
            include BOOKINGPRESS_MULTILANGUAGE_VIEW_DIR.'bookingpress_settings_language_translation_popup.php';
        }

        
        /* Customized Section Language Data Added Start */

        
        /**
         * Function for add custom fields lang button
         *
         * @return void
         */
        function bookingpress_add_customize_custom_fields_top_button_fun(){
        ?>
            <el-button class="bpa-btn bpa-btn--ml-translate" @click="open_customize_form_translate_language_modal('custom_fields')">
                <span class="material-icons-round">translate</span>
                <?php esc_html_e('Translate', 'bookingpress-multilanguage'); ?>
            </el-button>            
        <?php             
        }

        /**
         * Function for customized section language data added
         *
         * @return void
         */
        function bookingpress_form_customized_language_translation_btn_fun(){
        ?>
            <el-button class="bpa-btn bpa-btn--ml-translate" @click="open_customize_form_translate_language_modal(bpa_activeTabName)">
                <span class="material-icons-round">translate</span>
                <?php esc_html_e('Translate', 'bookingpress-multilanguage'); ?>
            </el-button>            
        <?php              
        }

        
        
        /**
         * Function for add customization custom fields data
         *
         * @return void
         */
        function bookingpress_add_customize_custom_fields_view_after_func(){
            include BOOKINGPRESS_MULTILANGUAGE_VIEW_DIR.'bookingpress_customize_custom_fields_language_translation_popup.php';
        }

        /**
         * Function for add customized language popup
         *
         * @return void
         */
        function bookingpress_add_manage_customized_view_bottom_func(){
            include BOOKINGPRESS_MULTILANGUAGE_VIEW_DIR.'bookingpress_customize_language_translation_popup.php';
        }
        
        /**
         * Function for customized dynamic vue method
         *
         * @return void
         */
        function bookingpress_customize_dynamic_vue_methods_func(){
        ?>
          open_customize_form_translate_language_modal(bpa_activeTabName){             
             var vm2 = this;
             if(bpa_activeTabName == 'custom_fields'){
                vm2.open_customize_custom_fields_translate_language = true;
             }else if(bpa_activeTabName == 'booking_form'){
                vm2.open_customize_form_translate_language = true;
            }else if(bpa_activeTabName == 'package_booking_form'){
                vm2.open_customize_package_booking_form_translate_language = true;
            } else{
                vm2.open_customize_my_booking_form_translate_language = true;
             }           
             <?php 
             /*Action for the adding translation for the new tab*/
             do_action('bookingpress_customize_language_model_activetab_change'); 
            ?>
          }, 
          change_customize_current_language(lang){
            var vm2 = this;
            vm2.bookingpress_current_selected_lang = lang;
          }
        <?php 
        }

        
        /**
         * Save Customized Settings Data
         *
         * @return void
         */
        function bookingpress_after_save_customize_settings_fun(){                        
            if(isset($_POST['language_data'])){ //phpcs:ignore
                global $BookingPress;                 
                if( !empty( $_POST['language_data'] ) && !is_array( $_POST['language_data'] ) ){ //phpcs:ignore
                    $_POST['language_data'] = json_decode( stripslashes_deep( $_POST['language_data'] ), true ); //phpcs:ignore                    
                }                
                $language_data = ! empty($_POST['language_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['language_data']) : array(); // phpcs:ignore
                if(!empty($language_data)){
                    if(is_array($language_data)){  
                        foreach($language_data as $lang_key=>$single_language_data){
                            foreach($single_language_data as $lang_section=>$lang_fields){
                                foreach($lang_fields as $lang_field_key=>$bp_translated_val){
                                    $this->bookingpress_common_save_language_data($lang_key,$lang_section,$bp_translated_val,0,$lang_field_key);
                                }                            
                            }
                        } 
                    }
                }
            }
        }

        /**
         * 
         *
         * @return void
         */
        function bookingpress_before_save_customize_form_settings_func(){
        ?>                
            if(vm2.bpa_activeTabName == 'booking_form'){
                postData.language_data = JSON.stringify(vm2.language_data);
            }            
            if(vm2.bpa_activeTabName == 'my_bookings'){
                postData.language_data = JSON.stringify(vm2.my_booking_language_data);
            }
            if(vm2.bpa_activeTabName == 'package_booking_form'){
                postData.language_data = JSON.stringify(vm2.package_language_data);
            }    
            if(vm2.bpa_activeTabName == 'gift_card_form'){
                postData.language_data = JSON.stringify(vm2.gift_card_language_data);
            }           
        <?php     
        }

        /**
         * Function for add language data in custom fields
         *
         * @return void
         */
        function bookingpress_before_save_field_settings_method_fun(){
        ?>
           postData.language_data = JSON.stringify(vm2.custom_form_language_data);
        <?php 
        }        




        /**
         * Function for save custom fields language data 
         *
         * @return void
         */
        function bookingpress_after_save_custom_form_fields_func(){            
            if(isset($_POST['language_data'])){  //phpcs:ignore              
                global $wpdb,$BookingPress,$tbl_bookingpress_ml_translation;                 
                if( !empty( $_POST['language_data'] ) && !is_array( $_POST['language_data'] ) ){ //phpcs:ignore
                    $_POST['language_data'] = json_decode( stripslashes_deep( $_POST['language_data'] ), true ); //phpcs:ignore                    
                }                
                $language_data = ! empty($_POST['language_data']) ? array_map(array( $BookingPress, 'appointment_sanatize_field' ), $_POST['language_data']) : array(); // phpcs:ignore               
                if(!empty($language_data) && is_array($language_data)){
                    $wpdb->delete($tbl_bookingpress_ml_translation,array('bookingpress_element_type' => 'custom_form_fields'));                    
                    foreach($language_data as $lang_key=>$single_language_data){                    
                        foreach($single_language_data as $lang_section=>$lang_fields){
                            foreach($lang_fields as $lang_field_key=>$bp_all_cat_data){
                                foreach($bp_all_cat_data as $f_id=>$bp_translated_val){
                                    $f_id = str_replace('c','',$f_id);                                    
                                    if($lang_field_key == 'bookingpress_field_values'){
                                        if(is_array($bp_translated_val)){
                                            $bp_translated_val = json_encode($bp_translated_val,JSON_UNESCAPED_UNICODE);
                                        }else{
                                            $bp_translated_val = json_encode(array());
                                        }
                                        $this->bookingpress_common_save_language_data($lang_key,$lang_section,$bp_translated_val,$f_id,$lang_field_key);
                                    }else{
                                        $this->bookingpress_common_save_language_data($lang_key,$lang_section,$bp_translated_val,$f_id,$lang_field_key);
                                    }                                                                                                                
                                }
                            }
                        }
                    }
                }

            }
        }

		/**
		 * Function for after load field settings data
		 *
		 * @return void
		 */
		function bookingpress_after_load_field_settings_func() {
		?>
        
        if(typeof response.data.custom_form_language_data !== 'undefined' && response.data.custom_form_language_data != ''){
            vm2.custom_form_language_data = response.data.custom_form_language_data;
        }  
        if(typeof response.data.custom_form_fields_language_data !== 'undefined' && response.data.custom_form_fields_language_data != ''){
            vm2.custom_form_fields_language_data = response.data.custom_form_fields_language_data;
        } 
        if(typeof response.data.bookingpress_customize_custom_form_fields_language_section_title !== 'undefined' && response.data.bookingpress_customize_custom_form_fields_language_section_title != ''){
            vm2.bookingpress_customize_custom_form_fields_language_section_title = response.data.bookingpress_customize_custom_form_fields_language_section_title;
        }                            
		<?php
		}

        
        /**
         * Function for update load custom fields
         *
         * @param  mixed $response
         * @return void
         */
        function bookingpress_modified_load_custom_fields_response_func($response){
            global $wpdb,$BookingPress,$bookingpress_all_language_translation_fields,$tbl_bookingpress_form_fields; 
            
            $bookingpress_custom_form_fields = $wpdb->get_results( $wpdb->prepare('SELECT bookingpress_form_field_name,bookingpress_field_values,bookingpress_field_type,bookingpress_field_position,bookingpress_field_meta_key,bookingpress_form_field_id,bookingpress_field_label,bookingpress_field_placeholder,bookingpress_field_error_message FROM ' . $tbl_bookingpress_form_fields . ' where bookingpress_field_type NOT IN ("2_col","3_col","4_col") AND bookingpress_is_customer_field = %d order by bookingpress_field_position ASC',0), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
            $bookingpress_customize_custom_form_fields_language_section_title = array();
            $bookingpress_customize_ustom_form_fields_language_translate_fields = array();
            $bookingpress_customize_ustom_form_fields_language_translate_fields['custom_form_fields'] = $bookingpress_all_language_translation_fields['custom_form_fields'];
            if(!empty($bookingpress_custom_form_fields) && !empty($bookingpress_get_selected_languages)){                

                $bookingform_language_data = $this->bookingpress_get_language_data_for_backend(0,'custom_form_fields');
                $bookingpress_customize_vue_data_fields = array();
                foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                    foreach($bookingpress_custom_form_fields as $user_form_fields){
                        $bookingpress_customize_custom_form_fields_language_section_title['c'.$user_form_fields['bookingpress_form_field_id']] = (!empty($user_form_fields['bookingpress_field_label']))?stripslashes_deep($user_form_fields['bookingpress_field_label']):''; 
                        foreach($bookingpress_customize_ustom_form_fields_language_translate_fields as $section_key=>$service_lang){ 
                            foreach($service_lang as $field_key => $field_value){   
                                $bookingpress_is_radio_or_checkbox = false;
                                if(($user_form_fields['bookingpress_field_type'] == 'checkbox' || $user_form_fields['bookingpress_field_type'] == 'radio' || $user_form_fields['bookingpress_field_type'] == 'dropdown')){
                                    $bookingpress_is_radio_or_checkbox = true;
                                } 

                                if($user_form_fields['bookingpress_form_field_name'] != 'Repeater' || $field_key == 'bookingpress_field_label'){
                                    if((!$bookingpress_is_radio_or_checkbox && $user_form_fields['bookingpress_form_field_name'] != 'terms_and_conditions') || $field_key != 'bookingpress_field_placeholder'){   
                                    
                                        if(($field_key == 'bookingpress_field_values' && $bookingpress_is_radio_or_checkbox) || ($field_key != 'bookingpress_field_values')){
        
                                            $bookingpress_customize_vue_data_fields['custom_form_language_data'][$key][$section_key][$field_key]['c'.$user_form_fields['bookingpress_form_field_id']] = ''; 
                                            if($field_key == 'bookingpress_field_values' && $bookingpress_is_radio_or_checkbox){                                        
                                                $field_value['bookingpress_field_values'] = json_decode($user_form_fields['bookingpress_field_values'],true);                                        
                                                                                                                                
                                                $bookingpress_field_valuesnn = json_decode($user_form_fields['bookingpress_field_values'],true);                                        
                                                $bookingpress_field_values_new = array();                                        
                                                if(!empty($bookingpress_field_valuesnn)){                                            
                                                    foreach($bookingpress_field_valuesnn as $optionkey=>$optionval){                                                
                                                        $bookingpress_field_valuesnn[$optionkey]['value'] = '';
                                                        $bookingpress_field_valuesnn[$optionkey]['label'] = '';
                                                    }
                                                }
                                                                                        
                                                $bookingpress_customize_vue_data_fields['custom_form_language_data'][$key][$section_key][$field_key]['c'.$user_form_fields['bookingpress_form_field_id']] = $bookingpress_field_valuesnn;
                                                //$bookingpress_customize_vue_data_fields['custom_form_language_data'][$key][$section_key][$field_key]['org']['c'.$user_form_fields['bookingpress_form_field_id']] = json_decode($user_form_fields['bookingpress_field_values'],true);
                                            }                                    
                                            $bookingpress_customize_vue_data_fields['custom_form_fields_language_data'][$key]['c'.$user_form_fields['bookingpress_form_field_id']][$section_key][$field_key] = $field_value;                                                                        
                                            if($field_key == 'bookingpress_field_values' && $bookingpress_is_radio_or_checkbox){  
        
                                            }
                                            if(!empty($bookingform_language_data)){
                                                $search = array('bookingpress_element_type' => 'custom_form_fields', 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key, 'bookingpress_element_ref_id'=> $user_form_fields['bookingpress_form_field_id']);
                                                $keys = array_keys(array_filter($bookingform_language_data, function ($v) use ($search) { 
                                                            return $v['bookingpress_element_ref_id'] == $search['bookingpress_element_ref_id'] && $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                                        }
                                                ));
                                                $index_val = isset($keys[0]) ? $keys[0] : '';
                                                if($index_val!='' || $index_val == 0) {
                                                   if(isset($bookingform_language_data[$index_val])){
                                                        $translated_data = $bookingform_language_data[$index_val];
                                                        $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                                        if($field_key == 'bookingpress_field_values' && $bookingpress_is_radio_or_checkbox){  
                                                            if(empty($bp_translated_str)){
                                                                $bp_translated_str = array();
                                                            }
                                                            $bp_translated_str = json_decode($bp_translated_str,true);
                                                            if(!empty($bp_translated_str)){
                                                                $db_values = $bookingpress_customize_vue_data_fields['custom_form_language_data'][$key][$section_key][$field_key]['c'.$user_form_fields['bookingpress_form_field_id']];
                                                                if( count( $bp_translated_str ) >= count( $db_values ) ){   
                                                                    $bookingpress_customize_vue_data_fields['custom_form_language_data'][$key][$section_key][$field_key]['c'.$user_form_fields['bookingpress_form_field_id']] = $bp_translated_str;
                                                                } else if( count( $bp_translated_str ) < count( $db_values ) ) {
                                                                    $bp_translated_str_new = array();
                                                                    foreach( $db_values as $db_value_key => $db_value_data ){
                                                                        if( !empty( $bp_translated_str[ $db_value_key ] ) ){
                                                                            $bp_translated_str_new[ $db_value_key ] = $bp_translated_str[ $db_value_key ];
                                                                        } else {
                                                                            $bp_translated_str_new[ $db_value_key ] = $db_value_data;
                                                                        }
                                                                    }
                                                                    if( !empty( $bp_translated_str_new ) ){
                                                                        $bookingpress_customize_vue_data_fields['custom_form_language_data'][$key][$section_key][$field_key]['c'.$user_form_fields['bookingpress_form_field_id']] = $bp_translated_str_new;
                                                                    }
                                                                }
                                                            }
                                                        }else{
                                                            $bookingpress_customize_vue_data_fields['custom_form_language_data'][$key][$section_key][$field_key]['c'.$user_form_fields['bookingpress_form_field_id']] = $bp_translated_str;
                                                        }                                            
                                                   } 
                                                }
                                            }
        
                                        }    
        
                                        }
                                }




                            }
                        }                        
                    }
                }

            }              
            $response['custom_form_language_data'] = (isset($bookingpress_customize_vue_data_fields['custom_form_language_data']))?$bookingpress_customize_vue_data_fields['custom_form_language_data']:'';
            $response['custom_form_fields_language_data'] = (isset($bookingpress_customize_vue_data_fields['custom_form_fields_language_data']))?$bookingpress_customize_vue_data_fields['custom_form_fields_language_data']:'';
            $response['bookingpress_customize_custom_form_fields_language_section_title'] = $bookingpress_customize_custom_form_fields_language_section_title;

            return $response;
        }

		/**
		 * Function for add dynamic field to customize page
		 *
		 * @param  mixed $bookingpress_customize_vue_data_fields
		 * @return void
		 */
        function bookingpress_customize_add_dynamic_data_fields_func($bookingpress_customize_vue_data_fields) {

            global $wpdb,$BookingPress,$tbl_bookingpress_form_fields;    

            $bookingpress_all_language_translation_fields = $this->bookingpress_all_language_translation_fields();

            $bookingpress_customize_vue_data_fields['empty_selected_language'] = 0;
            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_get_selected_languages = array();
                $bookingpress_customize_vue_data_fields['empty_selected_language'] = 1;
            }

            /* Customize Booking Form Start */
            $bookingpress_customize_form_language_translate_fields = array();
            $bookingpress_customize_form_language_translate_fields['customized_form_common_field_labels'] = $bookingpress_all_language_translation_fields['customized_form_common_field_labels'];
            $bookingpress_customize_form_language_translate_fields['customized_form_service_step_labels'] = $bookingpress_all_language_translation_fields['customized_form_service_step_labels'];
            $bookingpress_customize_form_language_translate_fields['customized_form_date_and_time_labels'] = $bookingpress_all_language_translation_fields['customized_form_date_and_time_labels'];
            $bookingpress_customize_form_language_translate_fields['customized_form_basic_details_step_labels'] = $bookingpress_all_language_translation_fields['customized_form_basic_details_step_labels'];            
            $bookingpress_customize_form_language_translate_fields['customized_form_summary_step_labels'] = $bookingpress_all_language_translation_fields['customized_form_summary_step_labels'];
            $bookingpress_customize_form_language_translate_fields['customized_form_payment_link_labels'] = $bookingpress_all_language_translation_fields['customized_form_payment_link_labels'];
            $bookingpress_customize_form_language_translate_fields['in_build_booking_form_message'] = $bookingpress_all_language_translation_fields['in_build_booking_form_message'];                        
            $bookingpress_customize_form_language_translate_fields = apply_filters( 'bookingpress_modified_customize_form_language_translate_fields',$bookingpress_customize_form_language_translate_fields);             

            /* Customize My Booking  Start */
            $bookingpress_customize_my_booking_language_translate_fields = array();
            $bookingpress_customize_my_booking_language_translate_fields['customized_my_booking_field_labels'] = $bookingpress_all_language_translation_fields['customized_my_booking_field_labels'];
            $bookingpress_customize_my_booking_language_translate_fields['customized_my_booking_login_related_messages'] = $bookingpress_all_language_translation_fields['customized_my_booking_login_related_messages'];            
            $bookingpress_customize_my_booking_language_translate_fields['customized_my_booking_forgot_password_related_messages'] = $bookingpress_all_language_translation_fields['customized_my_booking_forgot_password_related_messages'];            
            $bookingpress_customize_my_booking_language_translate_fields['customized_my_booking_forgot_edit_account_messages'] = $bookingpress_all_language_translation_fields['customized_my_booking_forgot_edit_account_messages'];            
            $bookingpress_customize_my_booking_language_translate_fields['customized_my_booking_change_password_messages'] = $bookingpress_all_language_translation_fields['customized_my_booking_change_password_messages'];            
            $bookingpress_customize_my_booking_language_translate_fields['customized_my_booking_reschedule_appointment_messages'] = $bookingpress_all_language_translation_fields['customized_my_booking_reschedule_appointment_messages'];            
            $bookingpress_customize_my_booking_language_translate_fields['customized_my_booking_cancel_appointment_messages'] = $bookingpress_all_language_translation_fields['customized_my_booking_cancel_appointment_messages'];            
            $bookingpress_customize_my_booking_language_translate_fields['customized_my_booking_cancel_appointment_conf_messages'] = $bookingpress_all_language_translation_fields['customized_my_booking_cancel_appointment_conf_messages'];                        
            $bookingpress_customize_my_booking_language_translate_fields = apply_filters( 'bookingpress_modified_customize_my_booking_language_translate_fields',$bookingpress_customize_my_booking_language_translate_fields);
            
            
            $bookingpress_customize_form_language_section_title = $this->bookingpress_get_language_translation_section_label($bookingpress_customize_form_language_translate_fields);
            $bookingpress_customize_vue_data_fields['bookingpress_customize_form_language_section_title'] = $bookingpress_customize_form_language_section_title;

            $bookingpress_customize_my_booking_language_section_title = $this->bookingpress_get_language_translation_section_label($bookingpress_customize_my_booking_language_translate_fields);
            $bookingpress_customize_vue_data_fields['bookingpress_customize_my_booking_language_section_title'] = $bookingpress_customize_my_booking_language_section_title;

            
            $bookingpress_current_selected_lang = '';
            if(!empty($bookingpress_get_selected_languages)){

                $bookingform_language_data = $this->bookingpress_get_language_data_for_backend(0,'booking_form');
                $my_booking_language_data = $this->bookingpress_get_language_data_for_backend(0,'booking_my_booking');

                foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                    if(empty($bookingpress_current_selected_lang)){
                        $bookingpress_current_selected_lang = $key;
                    }            
                    foreach($bookingpress_customize_form_language_translate_fields as $section_key=>$service_lang){                                                
                        foreach($service_lang as $field_key => $field_value){                            
                            $bookingpress_customize_vue_data_fields['language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                            $bookingpress_customize_vue_data_fields['language_data'][$key]['booking_form'][$field_key] = '';                            
                            $search = array('bookingpress_element_type' => 'booking_form', 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key, 'bookingpress_element_ref_id'=> 0);
                            $keys = array_keys(array_filter($bookingform_language_data, function ($v) use ($search) { 
                                        return $v['bookingpress_element_ref_id'] == $search['bookingpress_element_ref_id'] && $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                    }
                            ));
                            $index_val = isset($keys[0]) ? $keys[0] : '';
                            if($index_val!='' || $index_val == 0) {
                                if(isset($bookingform_language_data[$index_val])){
                                    $translated_data = $bookingform_language_data[$index_val];
                                    $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                    $bookingpress_customize_vue_data_fields['language_data'][$key]['booking_form'][$field_key] = $bp_translated_str;    
                                }
                            }                            

                        }
                    }
                    foreach($bookingpress_customize_my_booking_language_translate_fields as $section_key=>$service_lang){  
                        foreach($service_lang as $field_key => $field_value){                                                        
                            $bookingpress_customize_vue_data_fields['my_booking_language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                            $bookingpress_customize_vue_data_fields['my_booking_language_data'][$key]['booking_my_booking'][$field_key] = '';                            
                            $search = array('bookingpress_element_type' => 'booking_my_booking', 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key, 'bookingpress_element_ref_id'=> 0);
                            $keys = array_keys(array_filter($my_booking_language_data, function ($v) use ($search) { 
                                        return $v['bookingpress_element_ref_id'] == $search['bookingpress_element_ref_id'] && $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                    }
                            ));
                            $index_val = isset($keys[0]) ? $keys[0] : '';
                            if($index_val!='' || $index_val == 0) {
                                if(isset($my_booking_language_data[$index_val])){
                                    $translated_data = $my_booking_language_data[$index_val];
                                    $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                    $bookingpress_customize_vue_data_fields['my_booking_language_data'][$key]['booking_my_booking'][$field_key] = $bp_translated_str;    
                                }
                            }                            
                        }
                    }
                }
            }

            $bookingpress_customize_ustom_form_fields_language_translate_fields = array();
            $bookingpress_customize_ustom_form_fields_language_translate_fields['custom_form_fields'] = $bookingpress_all_language_translation_fields['custom_form_fields'];
            $bookingpress_customize_custom_form_fields_language_section_title = array();
            $bookingpress_custom_form_fields = $wpdb->get_results( $wpdb->prepare( 'SELECT bookingpress_field_position,bookingpress_field_meta_key,bookingpress_form_field_id,bookingpress_field_label,bookingpress_field_placeholder,bookingpress_field_error_message FROM ' . $tbl_bookingpress_form_fields . ' where bookingpress_is_customer_field = %d order by bookingpress_field_position ASC',0), ARRAY_A);// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
            if(!empty($bookingpress_custom_form_fields)){
                foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                    foreach($bookingpress_custom_form_fields as $user_form_fields){
                        $bookingpress_customize_custom_form_fields_language_section_title[$user_form_fields['bookingpress_form_field_id']] = (!empty($user_form_fields['bookingpress_field_label']))?stripslashes_deep($user_form_fields['bookingpress_field_label']):''; 
                        foreach($bookingpress_customize_ustom_form_fields_language_translate_fields as $section_key=>$service_lang){ 
                            foreach($service_lang as $field_key => $field_value){  
                                $field_value['bookingpress_field_label']='';
                                $bookingpress_customize_vue_data_fields['custom_form_fields_language_data'][$key][$user_form_fields['bookingpress_form_field_id']][$section_key][$field_key] = $field_value;
                                $bookingpress_customize_vue_data_fields['custom_form_language_data'][$key][$section_key][$field_key][$user_form_fields['bookingpress_form_field_id']] = ''; 
                            }
                        }                        
                    }
                }
            }                        

            $bookingpress_customize_vue_data_fields['bookingpress_customize_custom_form_fields_language_section_title'] = $bookingpress_customize_custom_form_fields_language_section_title;
            $bookingpress_customize_vue_data_fields['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
            $bookingpress_customize_vue_data_fields['bookingpress_current_selected_lang_org'] = $bookingpress_current_selected_lang;
            $bookingpress_customize_vue_data_fields['bookingpress_get_selected_languages'] = $bookingpress_get_selected_languages;            
            $bookingpress_customize_vue_data_fields['open_customize_form_translate_language'] = false;
            $bookingpress_customize_vue_data_fields['open_customize_my_booking_form_translate_language'] = false;
            $bookingpress_customize_vue_data_fields['open_customize_custom_fields_translate_language'] = false;
            $bookingpress_customize_vue_data_fields['open_customize_package_booking_form_translate_language'] = false;            

            return $bookingpress_customize_vue_data_fields;

		}  


        /**
        * Function for add manage notification language popup
        *
        * @return void
        */
        function bookingpress_add_manage_notification_view_bottom_func(){
            include BOOKINGPRESS_MULTILANGUAGE_VIEW_DIR.'bookingpress_manage_notification_language_translation_popup.php';
        }
        
		                
        /**
         * Function for add notification post data variable
         *
         * @return void
         */
        function bookingpress_add_email_notification_data_fun(){
        ?>
            bookingpress_save_notification_data.language_data = vm.language_data;
        <?php 
        }

        /**
         * Function for save email notification
         *
         * @return void
         */
        function bookingpress_after_save_email_notification_data_func($post_data){
            $bookingpress_notification_name = ! empty($post_data['notification_name']) ? sanitize_text_field($post_data['notification_name']):'';
            $language_data = (isset($post_data['language_data']))?$post_data['language_data']:'';
            if(!empty($language_data) && is_array($language_data)){  
                foreach($language_data as $lang_key=>$single_language_data){
                    foreach($single_language_data as $lang_section=>$lang_fields){
                        foreach($lang_fields as $lang_field_key=>$bp_translated_val){                        
                            $this->bookingpress_common_save_language_data($lang_key,$lang_section,$bp_translated_val,$bookingpress_notification_name,$lang_field_key);
                        }                            
                    }
                } 
            }              
        }

		/**
		 * Function for add notification vue data
		 *
		 * @return void
		 */
		function bookingpress_add_dynamic_notifications_vue_methods_func() {
		?>
        open_manage_notification_translate_language_modal(){
            var vm2 = this;
            vm2.open_manage_notification_translate_language = true;   
            vm2.bookingpress_get_selected_languages = vm2.bookingpress_get_selected_languages_org;       
        },      
        change_customize_current_language(lang){
            var vm2 = this;
            vm2.bookingpress_current_selected_lang = lang;
        },      
        <?php 
        }    

        		


        /**
         * Function for get notification language data
         *
         * @param  mixed $bookingpress_return_data
         * @param  mixed $post_data
         * @return void
         */
        function bookingpress_get_email_notification_data_modified_func($bookingpress_return_data,$post_data){
            global $wpdb,$BookingPress,$bookingpress_all_language_translation_fields_section,$bookingpress_all_language_translation_fields;

            $bookingpress_notification_name = ! empty($post_data['bookingpress_notification_name']) ? sanitize_text_field($post_data['bookingpress_notification_name']) : '';
            $bookingpress_notification_vue_methods_data = array();
            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_get_selected_languages = array();
            }
            $bookingpress_current_selected_lang = '';
            $bookingpress_notification_vue_methods_data['empty_selected_language'] = 0;
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_notification_vue_methods_data['empty_selected_language'] = 1;
            }             
            $bookingpress_notification_manage_language_translate_fields = array();
            $bookingpress_notification_manage_language_translate_fields['manage_notification_customer'] = $bookingpress_all_language_translation_fields['manage_notification_customer'];
            //$bookingpress_notification_manage_language_translate_fields['manage_notification_employee'] = $bookingpress_all_language_translation_fields['manage_notification_employee'];
            $bookingpress_notification_manage_language_translate_fields = apply_filters( 'bookingpress_modified_notification_manage_language_translate_fields',$bookingpress_notification_manage_language_translate_fields);            
            $bookingpress_notification_manage_language_section_title = $this->bookingpress_get_language_translation_section_label($bookingpress_notification_manage_language_translate_fields);

            $bookingpress_notification_vue_methods_data['language_data'] = array();
            $bookingpress_notification_vue_methods_data['language_fields_data'] = array();
            if(!empty($bookingpress_get_selected_languages)){
                $language_data = $this->bookingpress_get_language_data_for_backend($bookingpress_notification_name,array('manage_notification_customer','manage_notification_employee'));
                foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                    if(empty($bookingpress_current_selected_lang)){
                        $bookingpress_current_selected_lang = $key;
                    }            
                    foreach($bookingpress_notification_manage_language_translate_fields as $section_key=>$service_lang){                                              
                        foreach($service_lang as $field_key => $field_value){                            
                            $bookingpress_notification_vue_methods_data['language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                            $bookingpress_notification_vue_methods_data['language_data'][$key][$section_key][$field_key] = ''; 
                            $bookingpress_notification_vue_methods_data['language_data_org'][$key][$section_key][$field_key] = '';      
                            if(!empty($language_data)){                                
                                $search = array('bookingpress_element_type' => $section_key, 'bookingpress_language_code' => $key, 'bookingpress_ref_column_name'=> $field_key, 'bookingpress_element_ref_id'=> $bookingpress_notification_name);
                                $keys = array_keys(array_filter($language_data, function ($v) use ($search) { 
                                            return $v['bookingpress_element_ref_id'] == $search['bookingpress_element_ref_id'] && $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_language_code'] == $search['bookingpress_language_code'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                                        }
                                ));
                                $index_val = isset($keys[0]) ? $keys[0] : '';
                                if($index_val!='' || $index_val == 0) {
                                   if(isset($language_data[$index_val])){
                                        $translated_data = $language_data[$index_val];
                                        $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';                                  
                                        $bookingpress_notification_vue_methods_data['language_data'][$key][$section_key][$field_key] = $bp_translated_str;
                                   } 
                                }
                            }

                        }
                    }
                }
            }

            $bookingpress_return_data['bookingpress_notification_manage_language_section_title'] =  $bookingpress_notification_manage_language_section_title;
            $bookingpress_return_data['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
            $bookingpress_return_data['open_manage_notification_translate_language'] = false;           
            $bookingpress_return_data['bookingpress_get_selected_languages'] =  $bookingpress_get_selected_languages;
            $bookingpress_return_data['language_data'] = $bookingpress_notification_vue_methods_data['language_data'];            
            $bookingpress_return_data['bookingpress_get_selected_languages_org'] =  $bookingpress_get_selected_languages;            
            $bookingpress_return_data['empty_selected_language'] = $bookingpress_notification_vue_methods_data['empty_selected_language']; 
            return $bookingpress_return_data;

        }

		/**
		 * Function for modified email notification get response data
		 *
		 * @return void
		 */
		function bookingpress_email_notification_get_data_func(){
		?>              
            if(typeof response.data.empty_selected_language !== 'undefined'){
                vm.empty_selected_language = response.data.empty_selected_language;
            }
            if(typeof response.data.bookingpress_current_selected_lang !== 'undefined'){
                vm.bookingpress_current_selected_lang = response.data.bookingpress_current_selected_lang;
            }
            if(typeof response.data.language_data !== 'undefined'){
                vm.language_data = response.data.language_data;
            }
            if(typeof response.data.bookingpress_notification_manage_language_section_title !== 'undefined'){
                vm.bookingpress_notification_manage_language_section_title = response.data.bookingpress_notification_manage_language_section_title;
            }            

			<?php
		}

        /**
         * Function for notification language variable add
         *
         * @param  mixed $bookingpress_notification_vue_methods_data
         * @return void
         */
        function bookingpress_add_dynamic_notification_data_fields_func( $bookingpress_notification_vue_methods_data ) {

            global $wpdb,$BookingPress,$bookingpress_all_language_translation_fields_section,$bookingpress_all_language_translation_fields;

            $bookingpress_get_selected_languages = $this->bookingpress_get_selected_languages();
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_get_selected_languages = array();
            }            

            $bookingpress_current_selected_lang = '';
            $bookingpress_notification_vue_methods_data['empty_selected_language'] = 0;
            if(empty($bookingpress_get_selected_languages)){
                $bookingpress_notification_vue_methods_data['empty_selected_language'] = 1;
            }             
            $bookingpress_notification_manage_language_translate_fields = array();
            $bookingpress_notification_manage_language_translate_fields['manage_notification_customer'] = $bookingpress_all_language_translation_fields['manage_notification_customer'];
            //$bookingpress_notification_manage_language_translate_fields['manage_notification_employee'] = $bookingpress_all_language_translation_fields['manage_notification_employee'];
            $bookingpress_notification_manage_language_translate_fields = apply_filters( 'bookingpress_modified_notification_manage_language_translate_fields',$bookingpress_notification_manage_language_translate_fields);            
            $bookingpress_notification_manage_language_section_title = $this->bookingpress_get_language_translation_section_label($bookingpress_notification_manage_language_translate_fields);            
            
            $bookingpress_notification_vue_methods_data['language_data'] = array();
            $bookingpress_notification_vue_methods_data['language_fields_data'] = array();
            if(!empty($bookingpress_get_selected_languages)){
                foreach($bookingpress_get_selected_languages as $key=>$sel_lang){
                    if(empty($bookingpress_current_selected_lang)){
                        $bookingpress_current_selected_lang = $key;
                    }            
                    foreach($bookingpress_notification_manage_language_translate_fields as $section_key=>$service_lang){                                                
                        foreach($service_lang as $field_key => $field_value){                            
                            $bookingpress_notification_vue_methods_data['language_fields_data'][$key][$section_key][$field_key] = $field_value; 
                            $bookingpress_notification_vue_methods_data['language_data'][$key][$section_key][$field_key] = ''; 
                            $bookingpress_notification_vue_methods_data['language_data_org'][$key][$section_key][$field_key] = '';      
                        }
                    }
                }
            }
            
            $bookingpress_notification_vue_methods_data['bookingpress_notification_manage_language_section_title'] =  $bookingpress_notification_manage_language_section_title;
            $bookingpress_notification_vue_methods_data['bookingpress_current_selected_lang'] = $bookingpress_current_selected_lang;
            $bookingpress_notification_vue_methods_data['open_manage_notification_translate_language'] = false;           
            $bookingpress_notification_vue_methods_data['bookingpress_get_selected_languages'] =  $bookingpress_get_selected_languages;
            $bookingpress_notification_vue_methods_data['bookingpress_get_selected_languages_org'] =  $bookingpress_get_selected_languages;

            return $bookingpress_notification_vue_methods_data;
        }


        /* Manage notification backup  */
        function bookingpress_manage_notification_setting_header_button_fun(){
        ?>
            <el-button :class="(bookingpress_email_notification_edit_text != '' && bookingpress_is_custom_email_notification == true && bookingpress_notification_id !='')?'bpa-rextra-margin':''" class="bpa-btn bpa-btn--ml-translate" @click="open_manage_notification_translate_language_modal()">
                <span class="material-icons-round">translate</span>
                <?php esc_html_e('Translate', 'bookingpress-multilanguage'); ?>
            </el-button>            
        <?php             
        }

        /* Customized Section Language Data Added Over */

        /**
         * Function for add category language transalation button
         *
         * @return void
         */
        function bookingpress_category_language_translation_btn_fun(){
        ?>
            <el-button class="bpa-btn bpa-btn--ml-translate" @click="open_service_translate_language_modal('category')">
                <span class="material-icons-round">translate</span>
                <?php esc_html_e('Translate', 'bookingpress-multilanguage'); ?>
            </el-button>            
        <?php             
        }

        /**
         * Function for add service new button
         *
         * @return void
        */
        function bookingpress_service_language_translation_btn_fun(){
        ?>
            <el-button class="bpa-btn bpa-btn--ml-translate" @click="open_service_translate_language_modal('service')">
                <span class="material-icons-round">translate</span>
                <?php esc_html_e('Translate', 'bookingpress-multilanguage'); ?>
            </el-button>            
        <?php 
        }

        /**
         * Function for set css in admin side
         *
         * @return void
        */
        function set_admin_css(){
            global $bookingpress_slugs;
			wp_register_style( 'bookingpress_multi_lang_admin_css', BOOKINGPRESS_MULTILANGUAGE_URL . '/css/bookingpress_multi_lang_admin.css', array(), BOOKINGPRESS_MULTILANGUAGE_VERSION );
            if ( isset( $_REQUEST['page'] ) && in_array( sanitize_text_field( $_REQUEST['page'] ), (array) $bookingpress_slugs ) ) {
				wp_enqueue_style( 'bookingpress_multi_lang_admin_css' );
			}
        }
        


        function bookingpress_get_front_current_language(){
            //$wordpress_current_language = get_bloginfo('language');
            $wordpress_current_language = get_locale();
            /*
            if(is_plugin_active('weglot/weglot.php')) {
                if(function_exists('weglot_get_current_language')){
                    $wordpress_current_language = weglot_get_current_language();                    
                }                
            }
            */
            return $wordpress_current_language;
        }
        
        /**
         * Function for get WP default language
         *
         * @return void
         */
        function bookingpress_get_wordpress_default_lang(){
            $get_wp_default_lang = get_option('WPLANG');
            if(empty($get_wp_default_lang)){
                $get_wp_default_lang = 'en_US';                    
            } 
            return $get_wp_default_lang;
        }                


        /* Location Front Side Language Data Translate  */
        function bookingpress_modified_location_data_for_front_booking_form_func($location_data){
            global $tbl_bookingpress_ml_translation, $bookingpress_lang_translation_details, $bp_translation_lang;

            $bookingpress_location_id = (isset($location_data['bookingpress_location_id']))?$location_data['bookingpress_location_id']:'';
            if(isset($location_data['bookingpress_location_name'])){
                $orignal_str = $location_data['bookingpress_location_name'];
                $bookingpress_location_name = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$orignal_str, 'location',  'bookingpress_location_name', $bookingpress_location_id, $bp_translation_lang);
                $location_data['bookingpress_location_name'] = $bookingpress_location_name;
            }
            if(isset($location_data['bookingpress_location_address'])){
                $orignal_str = $location_data['bookingpress_location_address'];
                $bookingpress_location_address = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$orignal_str, 'location',  'bookingpress_location_address', $bookingpress_location_id, $bp_translation_lang);
                $location_data['bookingpress_location_address'] = $bookingpress_location_address;
            }
            if(isset($location_data['bookingpress_location_description'])){
                $orignal_str = $location_data['bookingpress_location_description'];
                $bookingpress_location_description = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$orignal_str, 'location',  'bookingpress_location_description', $bookingpress_location_id, $bp_translation_lang);
                $location_data['bookingpress_location_description'] = $bookingpress_location_description;
            }                        
            return $location_data;
        }

        /**
         * Function for front side multi-language data load 
         *
         * @param  mixed $bookingpress_front_vue_data_fields
         * @return void
         */
        function bookingpress_frontend_apointment_form_add_dynamic_data_ml_func($bookingpress_front_vue_data_fields){
            global $wpdb, $tbl_bookingpress_ml_translation, $bookingpress_lang_translation_details, $bp_translation_lang, $tbl_bookingpress_form_fields;

            if(!empty($bookingpress_lang_translation_details) && !is_admin()){

                $all_service_data = isset($bookingpress_front_vue_data_fields['bookingpress_all_services_data']) ? $bookingpress_front_vue_data_fields['bookingpress_all_services_data'] : array();                
                if(!empty($all_service_data)) {

                    
                    foreach ($all_service_data as $all_service_data_k => $all_service_data_v) {
                        $bookingpress_service_id = (isset($all_service_data_v['bookingpress_service_id']))?$all_service_data_v['bookingpress_service_id']:'';
                        if(isset($all_service_data_v['bookingpress_service_name'])) {                            
                            $orignal_str = $all_service_data_v['bookingpress_service_name'];
                            $service_name = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$orignal_str, 'service',  'bookingpress_service_name', $bookingpress_service_id, $bp_translation_lang);
                            $bookingpress_front_vue_data_fields['bookingpress_all_services_data'][$all_service_data_k]['bookingpress_service_name']=$service_name;
                        }                        
                        if(isset($all_service_data_v['bookingpress_service_description'])) {
                            $orignal_str = $all_service_data_v['bookingpress_service_description'];
                            $service_desc = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$orignal_str, 'service',  'bookingpress_service_description', $bookingpress_service_id, $bp_translation_lang);
                            $bookingpress_front_vue_data_fields['bookingpress_all_services_data'][$all_service_data_k]['bookingpress_service_description'] = $service_desc;
                        }                                                
                        if(isset($all_service_data_v['service_extras'])) {

                            $service_extras = $all_service_data_v['service_extras'];
                            foreach ($service_extras as $service_extras_key => $service_extras_val) {
                                $bookingpress_extra_services_id = (isset($service_extras_val['bookingpress_extra_services_id']))?$service_extras_val['bookingpress_extra_services_id']:'';
                                if(isset($service_extras_val['bookingpress_extra_service_name'])) {
                                    $orignal_str = $service_extras_val['bookingpress_extra_service_name'];
                                    $extra_ser_name = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$orignal_str, 'service_extra',  'bookingpress_extra_service_name', $bookingpress_extra_services_id, $bp_translation_lang); 
                                    $bookingpress_front_vue_data_fields['bookingpress_all_services_data'][$all_service_data_k]['service_extras'][$service_extras_key]['bookingpress_extra_service_name'] = $extra_ser_name;
                                }
                                if(isset($service_extras_val['bookingpress_service_description'])) {                                    
                                    $orignal_str = $service_extras_val['bookingpress_service_description'];
                                    $extra_ser_desc = $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$orignal_str, 'service_extra',  'bookingpress_service_description', $bookingpress_extra_services_id, $bp_translation_lang); 
                                    $bookingpress_front_vue_data_fields['bookingpress_all_services_data'][$all_service_data_k]['service_extras'][$service_extras_key]['bookingpress_service_description'] = $extra_ser_desc;
                                } 
                            }                            
                        }
                    }
                }
                $all_cat_data = isset($bookingpress_front_vue_data_fields['bookingpress_all_categories']) ? $bookingpress_front_vue_data_fields['bookingpress_all_categories'] : array();
                if(!empty($all_cat_data)) {
                    foreach($all_cat_data as $bp_cat_key => $bp_cat_val) {                        
                        $category_id = (isset($bp_cat_val['category_id']))?$bp_cat_val['category_id']:'';
                        $cat_name = (isset($bp_cat_val['category_name']))? $bp_cat_val['category_name'] : '';
                        if($category_id){
                            $cat_name = $this->bookingpress_frontend_multilanguage_translation_func( $bookingpress_lang_translation_details,$cat_name, 'category',  'bookingpress_category_name', $category_id, $bp_translation_lang); 
                        }                    
                        $bookingpress_front_vue_data_fields['bookingpress_all_categories'][$bp_cat_key]['category_name'] = $cat_name;
                    }
                }

                $bookingpress_form_fields = $wpdb->get_row($wpdb->prepare("SELECT bookingpress_field_placeholder,bookingpress_form_field_id  FROM {$tbl_bookingpress_form_fields} WHERE bookingpress_form_field_name = %s", 'phone_number' ), ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Reason: $tbl_bookingpress_form_fields is table name defined globally. False Positive alarm
                if(isset($bookingpress_front_vue_data_fields['bookingpress_tel_input_props']) && !empty($bookingpress_form_fields)){
                    $phone_field_props = $bookingpress_front_vue_data_fields['bookingpress_tel_input_props'];
                    if(isset($phone_field_props['inputOptions'])) {
                        if(isset($bookingpress_form_fields['bookingpress_field_placeholder']) && isset($bookingpress_form_fields['bookingpress_form_field_id'])) {
                            $phone_number_placeholder = $this->bookingpress_frontend_multilanguage_translation_func( $bookingpress_lang_translation_details,$bookingpress_form_fields['bookingpress_field_placeholder'], 'custom_form_fields',  'bookingpress_field_placeholder', $bookingpress_form_fields['bookingpress_form_field_id'], $bp_translation_lang); 
                            $bookingpress_front_vue_data_fields['bookingpress_tel_input_props']['inputOptions']['placeholder'] = $phone_number_placeholder;
                        }
                    }
                }
            }                         
            return $bookingpress_front_vue_data_fields;
        }

        
        /**
         * Function for pro filter for language translation
         *
         * @return void
         */
        function bookingpress_add_language_translate_data_func($orignal_str,$bookingpress_element_type,  $bp_translation_str_ref, $bp_translation_str_ref_id, $bp_translation_lang){        
            global $bookingpress_lang_translation_details;
			if(empty($bookingpress_lang_translation_details)){
				$bookingpress_lang_translation_details = $this->bookingpress_set_front_global_language_data(true);
			}

            return $this->bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$orignal_str,$bookingpress_element_type,  $bp_translation_str_ref, $bp_translation_str_ref_id, $bp_translation_lang);
        }

                
        /**
         * Main Front Language translation function
         *
         * @return void
         */
        function bookingpress_frontend_multilanguage_translation_func($bookingpress_lang_translation_details,$orignal_str,$bookingpress_element_type,  $bp_translation_str_ref, $bp_translation_str_ref_id, $bp_translation_lang=''){            
            global $BookingPress;
            if($bp_translation_lang=='') {
                $bp_translation_lang = $this->bookingpress_get_front_current_language();
            }
            if(!empty($bookingpress_lang_translation_details)) {
                if($bp_translation_str_ref_id) {
                    $search = array('bookingpress_element_type' => $bookingpress_element_type, 'bookingpress_element_ref_id' => $bp_translation_str_ref_id, 'bookingpress_ref_column_name'=> $bp_translation_str_ref);

                    $keys = array_keys(array_filter($bookingpress_lang_translation_details, function ($v) use ($search) { 
                                return $v['bookingpress_element_type'] == $search['bookingpress_element_type'] && $v['bookingpress_element_ref_id'] == $search['bookingpress_element_ref_id'] && $v['bookingpress_ref_column_name'] == $search['bookingpress_ref_column_name']; 
                            }
                    ));
                }
                else {
                    $search = array('bookingpress_element_type' => $bookingpress_element_type, 'bookingpress_ref_column_name'=> $bp_translation_str_ref);                    
                    $keys = array_keys(array_filter($bookingpress_lang_translation_details, function ($v) use ($search) { 
                                return trim($v['bookingpress_element_type']) == trim($search['bookingpress_element_type']) && trim($v['bookingpress_ref_column_name']) == trim($search['bookingpress_ref_column_name']); 
                    }
                    ));
                }
                $index_val = isset($keys[0]) ? $keys[0] : '';
                if($index_val!='' || $index_val == 0) {
                    $translated_data = (isset($bookingpress_lang_translation_details[$index_val]))?$bookingpress_lang_translation_details[$index_val]:'';
                    $bp_translated_str = isset($translated_data['bookingpress_translated_value']) ? $translated_data['bookingpress_translated_value'] : '';
                    if(!empty($bp_translated_str)) {
                        $orignal_str= $bp_translated_str;
                    }
                }
            }
            return $orignal_str;
        }

        
        function bookingpress_addon_list_data_filter_func($bookingpress_body_res){
            global $bookingpress_slugs;
            if(!empty($bookingpress_body_res)) {
                foreach($bookingpress_body_res as $bookingpress_body_res_key =>$bookingpress_body_res_val) {
                    $bookingpress_setting_page_url = add_query_arg('page', $bookingpress_slugs->bookingpress_settings, esc_url( admin_url() . 'admin.php?page=bookingpress' ));
                    $bookingpress_config_url = add_query_arg('setting_page', 'payment_settings', $bookingpress_setting_page_url);
                    if($bookingpress_body_res_val['addon_key'] == 'bookingpress_multilanguage_version') {
                        $bookingpress_body_res[$bookingpress_body_res_key]['addon_configure_url'] = $bookingpress_config_url;
                    }
                }
            }
            return $bookingpress_body_res;
        }  
        
        /**
         * Function for display notice in admin side
         *
         * @return void
        */
        function bookingpress_admin_notices(){

            global $bookingpress_pro_version;
            if( version_compare( $bookingpress_pro_version, '2.6.1', '<' ) ){
                echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress Pro Plugin to version 2.6.1 or higher.", "bookingpress-multilanguage")."</p></div>";
            }
            if(is_plugin_active('bookingpress-cart/bookingpress-cart.php')){
                $bookingpress_cart_version = get_option( 'bookingpress_cart_module' );
                if( version_compare( $bookingpress_cart_version, '2.2', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress Cart Plugin to version 2.2 or higher.", "bookingpress-multilanguage")."</p></div>";
                }
            }
            if(is_plugin_active('bookingpress-custom-service-duration/bookingpress-custom-service-duration.php')){
                $bookingpress_custom_duration_version = get_option( 'bookingpress_custom_service_duration_version' );
                if( version_compare( $bookingpress_custom_duration_version, '1.5', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Custom Service Duration Plugin to version 1.5 or higher.", "bookingpress-multilanguage")."</p></div>";
                }
            }
            if(is_plugin_active('bookingpress-authorize_net/bookingpress-authorize_net.php')){
                $bookingpress_auth_net_payment_gateway_version = get_option('bookingpress_auth_net_payment_gateway', true);
                if( version_compare( $bookingpress_auth_net_payment_gateway_version, '1.2', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Authorize.Net Payment Gateway Plugin to version 1.2 or higher.", "bookingpress-multilanguage")."</p></div>";
                }
            }               
            if(is_plugin_active('bookingpress-braintree/bookingpress-braintree.php')){
                $bookingpress_braintree_addon_version = get_option('bookingpress_braintree_payment_gateway');
                if( version_compare( $bookingpress_braintree_addon_version, '1.3', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Braintree Payment Gateway Plugin to version 1.3 or higher.", "bookingpress-multilanguage")."</p></div>";
                }
            }
            if(is_plugin_active('bookingpress-happy-hours/bookingpress-happy-hours.php')){
                $happy_hours_version = get_option('happy_hours_version');
                if( version_compare( $happy_hours_version, '1.1', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Happy Hours Pricing Plugin to version 1.1 or higher.", "bookingpress-multilanguage")."</p></div>";
                }
            }
            if(is_plugin_active('bookingpress-invoice/bookingpress-invoice.php')){
                $bookingpress_invoice_version = get_option('bookingpress_invoice_version');
                if( version_compare( $bookingpress_invoice_version, '1.8', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Invoice Plugin to version 1.8 or higher.", "bookingpress-multilanguage")."</p></div>";
                }                
            }
            if(is_plugin_active('bookingpress-klarna/bookingpress-klarna.php')){
                $bookingpress_klarna_addon_version = get_option('bookingpress_klarna_payment_gateway');
                if( version_compare( $bookingpress_klarna_addon_version, '1.1', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Klarna Payment Gateway Plugin to version 1.1 or higher.", "bookingpress-multilanguage")."</p></div>";
                }
            }
            if(is_plugin_active('bookingpress-mollie/bookingpress-mollie.php')){
                $bookingpress_mollie_payment_gateway = get_option('bookingpress_mollie_payment_gateway');
                if( version_compare( $bookingpress_mollie_payment_gateway, '1.2', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Mollie Payment Gateway Plugin to version 1.2 or higher.", "bookingpress-multilanguage")."</p></div>";
                }
            }
            if(is_plugin_active('bookingpress-paddle/bookingpress-paddle.php')){                
                $bookingpress_paddle_payment_gateway = get_option('bookingpress_paddle_payment_gateway');
                if( version_compare( $bookingpress_paddle_payment_gateway, '1.1', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Paddle Payment Gateway Plugin to version 1.1 or higher.", "bookingpress-multilanguage")."</p></div>";
                }
            }
            if(is_plugin_active('bookingpress-pagseguro/bookingpress-pagseguro.php')){
                $bookingpress_pagseguro_payment_gateway = get_option('bookingpress_pagseguro_payment_gateway');
                if( version_compare( $bookingpress_pagseguro_payment_gateway, '1.2', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - PagSeguro Payment Gateway Plugin to version 1.2 or higher.", "bookingpress-multilanguage")."</p></div>";
                }                
            }
            if(is_plugin_active('bookingpress-payfast/bookingpress-payfast.php')){                
                $bookingpress_payfast_payment_gateway = get_option('bookingpress_payfast_payment_gateway');
                if( version_compare( $bookingpress_payfast_payment_gateway, '1.2', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - PayFast Payment Gateway Plugin to version 1.2 or higher.", "bookingpress-multilanguage")."</p></div>";
                }
            }
            if(is_plugin_active('bookingpress-paypalpro/bookingpress-paypalpro.php')){ 
                $bookingpress_paypalpro_payment_gateway = get_option('bookingpress_paypalpro_payment_gateway');
                if( version_compare( $bookingpress_paypalpro_payment_gateway, '1.1', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - PayPal Pro Payment Gateway Plugin to version 1.1 or higher.", "bookingpress-multilanguage")."</p></div>";
                }                
            }
            if(is_plugin_active('bookingpress-paystack/bookingpress-paystack.php')){ 
                $bookingpress_paystack_payment_gateway = get_option('bookingpress_paystack_payment_gateway');
                if( version_compare( $bookingpress_paystack_payment_gateway, '1.1', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Paystack Payment Gateway Plugin to version 1.1 or higher.", "bookingpress-multilanguage")."</p></div>";
                } 
            }
            if(is_plugin_active('bookingpress-payumoney/bookingpress-payumoney.php')){ 
                $bookingpress_payumoney_payment_gateway = get_option('bookingpress_payumoney_payment_gateway');
                if( version_compare( $bookingpress_payumoney_payment_gateway, '1.2', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - PayUMoney Payment Gateway Plugin to version 1.2 or higher.", "bookingpress-multilanguage")."</p></div>";
                }                
            }
            if(is_plugin_active('bookingpress-razorpay/bookingpress-razorpay.php')){ 
                $bookingpress_razorpay_payment_gateway = get_option('bookingpress_razorpay_payment_gateway');
                if( version_compare( $bookingpress_razorpay_payment_gateway, '1.1', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Razorpay Payment Gateway Plugin to version 1.1 or higher.", "bookingpress-multilanguage")."</p></div>";
                }
            }
            if(is_plugin_active('bookingpress-skrill/bookingpress-skrill.php')){ 
                $bookingpress_skrill_payment_gateway = get_option('bookingpress_skrill_payment_gateway');
                if( version_compare( $bookingpress_skrill_payment_gateway, '1.2', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Skrill Payment Gateway Plugin to version 1.2 or higher.", "bookingpress-multilanguage")."</p></div>";
                }                
            }
            if(is_plugin_active('bookingpress-sms/bookingpress-sms.php')){ 
                $bookingpress_sms_gateway = get_option('bookingpress_sms_gateway');
                if( version_compare( $bookingpress_sms_gateway, '1.6', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - SMS Notification Plugin to version 1.6 or higher.", "bookingpress-multilanguage")."</p></div>";
                }                  
            }
            if(is_plugin_active('bookingpress-square/bookingpress-square.php')){ 
                $bookingpress_square_payment_gateway = get_option('bookingpress_square_payment_gateway');
                if( version_compare( $bookingpress_square_payment_gateway, '1.3', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Square Payment Gateway Plugin to version 1.3 or higher.", "bookingpress-multilanguage")."</p></div>";
                } 
            }
            if(is_plugin_active('bookingpress-stripe/bookingpress-stripe.php')){ 
                $bookingpress_stripe_payment_gateway = get_option('bookingpress_stripe_payment_gateway');
                if( version_compare( $bookingpress_stripe_payment_gateway, '1.5', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Stripe Payment Gateway Plugin to version 1.5 or higher.", "bookingpress-multilanguage")."</p></div>";
                }                 
            }
            if(is_plugin_active('bookingpress-tax/bookingpress-tax.php')){ 
                $bookingpress_tax_module = get_option('bookingpress_tax_module');
                if( version_compare( $bookingpress_tax_module, '1.4', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Tax Plugin to version 1.4 or higher.", "bookingpress-multilanguage")."</p></div>";
                }   
            }
            if(is_plugin_active('bookingpress-tip/bookingpress-tip.php')){ 
                $bookingpress_tip_addon = get_option('bookingpress_tip_addon');
                if( version_compare( $bookingpress_tip_addon, '1.3', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Tip Plugin to version 1.3 or higher.", "bookingpress-multilanguage")."</p></div>";
                }                
            }
            if(is_plugin_active('bookingpress-twocheckout/bookingpress-twocheckout.php')){ 
                $bookingpress_twocheckout_payment_gateway = get_option('bookingpress_twocheckout_payment_gateway');
                if( version_compare( $bookingpress_twocheckout_payment_gateway, '1.1', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Two Checkout Payment Gateway Plugin to version 1.1 or higher.", "bookingpress-multilanguage")."</p></div>";
                }                 
            }
            if(is_plugin_active('bookingpress-waiting-list/bookingpress-waiting-list.php')){ 
                $bookingpress_waiting_list_version = get_option('bookingpress_waiting_list_version');
                if( version_compare( $bookingpress_waiting_list_version, '1.2', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Waiting List Plugin to version 1.2 or higher.", "bookingpress-multilanguage")."</p></div>";
                }                 
            }
            if(is_plugin_active('bookingpress-whatsapp/bookingpress-whatsapp.php')){ 
                $bookingpress_whatsapp_gateway = get_option('bookingpress_whatsapp_gateway');
                if( version_compare( $bookingpress_whatsapp_gateway, '1.5', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - WhatsApp Notification Plugin to version 1.5 or higher.", "bookingpress-multilanguage")."</p></div>";
                }                
            }
            if(is_plugin_active('bookingpress-woocommerce/bookingpress-woocommerce.php')){ 
                $bookingpress_woocommerce_version = get_option('bookingpress_woocommerce_version');
                if( version_compare( $bookingpress_woocommerce_version, '1.4', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - WooCommerce Payment Gateway Plugin to version 1.4 or higher.", "bookingpress-multilanguage")."</p></div>";
                }                
            }
            if(is_plugin_active('bookingpress-worldpay/bookingpress-worldpay.php')){ 
                $bookingpress_worldpay_payment_gateway = get_option('bookingpress_worldpay_payment_gateway');
                if( version_compare( $bookingpress_worldpay_payment_gateway, '1.2', '<' ) ){
                    echo "<div class='notice notice-error is-dismissible'><p>".esc_html__("BookingPress - Multi-Language Plugin Requires to update the BookingPress - Worldpay Payment Gateway Plugin to version 1.2 or higher.", "bookingpress-multilanguage")."</p></div>";
                }                  
            }
            
        }

        public static function install(){
			
            global $wpdb, $tbl_bookingpress_customize_settings, $bookingpress_multilanguage_version, $BookingPress, $tbl_bookingpress_ml_translation;

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            $wordpress_current_language = get_bloginfo('language');            
            $bookingpress_multilanguage_version_db = get_option('bookingpress_multilanguage_version'); 

            if (!isset($bookingpress_multilanguage_version_db) || $bookingpress_multilanguage_version_db == ''){

                $myaddon_name = "bookingpress-multilanguage/bookingpress-multilanguage.php";
                
                // activate license for this addon
                $posted_license_key = trim( get_option( 'bkp_license_key' ) );
			    $posted_license_package = '22056';

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
                    $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : __( 'An error occurred, please try again.','bookingpress-multilanguage' );
                } else {
                    $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                    $license_data_string = wp_remote_retrieve_body( $response );
                    if ( false === $license_data->success ) {
                        switch( $license_data->error ) {
                            case 'expired' :
                                $message = sprintf(
                                    __( 'Your license key expired on %s.','bookingpress-multilanguage' ),
                                    date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                                );
                                break;
                            case 'revoked' :
                                $message = __( 'Your license key has been disabled.','bookingpress-multilanguage' );
                                break;
                            case 'missing' :
                                $message = __( 'Invalid license.','bookingpress-multilanguage' );
                                break;
                            case 'invalid' :
                            case 'site_inactive' :
                                $message = __( 'Your license is not active for this URL.','bookingpress-multilanguage' );
                                break;
                            case 'item_name_mismatch' :
                                $message = __('This appears to be an invalid license key for your selected package.','bookingpress-multilanguage');
                                break;
                            case 'invalid_item_id' :
                                    $message = __('This appears to be an invalid license key for your selected package.','bookingpress-multilanguage');
                                    break;
                            case 'no_activations_left':
                                $message = __( 'Your license key has reached its activation limit.','bookingpress-multilanguage' );
                                break;
                            default :
                                $message = __( 'An error occurred, please try again.','bookingpress-multilanguage' );
                                break;
                        }

                    }

                }

                if ( ! empty( $message ) ) {
                    update_option( 'bkp_multilanguage_license_data_activate_response', $license_data_string );
                    update_option( 'bkp_multilanguage_license_status', $license_data->license );
                    deactivate_plugins($myaddon_name, FALSE);
                    $redirect_url = network_admin_url('plugins.php?deactivate=true&bkp_license_deactivate=true&bkp_deactivate_plugin='.$myaddon_name);
                    $bpa_dact_message = __('Please activate license of BookingPress premium plugin to use BookingPress Multi Language Add-on', 'bookingpress-multilanguage');
					$bpa_link = sprintf( __('Please %s Click Here %s to Continue', 'bookingpress-multilanguage'), '<a href="javascript:void(0)" onclick="window.location.href=\'' . $redirect_url . '\'">', '</a>');
					wp_die('<p>'.$bpa_dact_message.'<br/>'.$bpa_link.'</p>');
                    die;
                }
                
                if($license_data->license === "valid")
                {
                    update_option( 'bkp_multilanguage_license_key', $posted_license_key );
                    update_option( 'bkp_multilanguage_license_package', $posted_license_package );
                    update_option( 'bkp_multilanguage_license_status', $license_data->license );
                    update_option( 'bkp_multilanguage_license_data_activate_response', $license_data_string );
                }

                update_option('bookingpress_multilanguage_version', $bookingpress_multilanguage_version);

                //$get_wp_default_lang = $this->bookingpress_get_wordpress_default_lang();
                $get_wp_default_lang = get_option('WPLANG');
                if(empty($get_wp_default_lang)){
                    $get_wp_default_lang = 'en_US';                    
                }            
                $BookingPress->bookingpress_update_settings('bookingpress_default_language', 'general_setting', $get_wp_default_lang);
                $BookingPress->bookingpress_update_settings('bookingpress_selected_languages', 'general_setting', "");

                $charset_collate = '';            
                if ( $wpdb->has_cap( 'collation' ) ) {
                    if ( ! empty( $wpdb->charset ) ) {
                        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                    }
                    if ( ! empty( $wpdb->collate ) ) {
                        $charset_collate .= " COLLATE $wpdb->collate";
                    }
                }
                
                $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_bookingpress_ml_translation}`(
                    `bookingpress_translation_id` int(11) NOT NULL AUTO_INCREMENT,
                    `bookingpress_element_type` varchar(255) NOT NULL,
                    `bookingpress_ref_column_name` varchar(255) NOT NULL,
                    `bookingpress_element_ref_id` varchar(255) NOT NULL,
                    `bookingpress_language_code` varchar(10) NOT NULL,
                    `bookingpress_translated_value` text NOT NULL,
                    `bookingpress_translation_created_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`bookingpress_translation_id`)
                ) {$charset_collate}";
                dbDelta( $sql_table );

            }

		}

        public static function uninstall(){
            delete_option('bookingpress_multilanguage_version');

            delete_option( 'bkp_multilanguage_license_key');
            delete_option( 'bkp_multilanguage_license_package');
            delete_option( 'bkp_multilanguage_license_status');
            delete_option( 'bkp_multilanguage_license_data_activate_response');


        }
        
        /**
         * Function For Check Addon active
         *
         * @return void
         */
        public function is_addon_activated(){
            $bookingpress_multilanguage_module_version = get_option('bookingpress_multilanguage_version');
            return !empty($bookingpress_multilanguage_module_version) ? 1 : 0;
        }


        function get_all_language_data(){
            return array(
                'af' => array(
                    'code'     => 'af',
                    'locale'   => 'af',
                    'name'     => 'Afrikaans',
                    'dir'      => 'ltr',
                    'flag'     => 'za',
                    'facebook' => 'af_ZA',
                ),
                'ak' => array(
                    'facebook' => 'ak_GH',
                ),
                'am' => array(
                    'code'     => 'am',
                    'locale'   => 'am',
                    'name'     => 'አማርኛ',
                    'dir'      => 'ltr',
                    'flag'     => 'et',
                    'facebook' => 'am_ET',
                ),
                'ar' => array(
                    'code'     => 'ar',
                    'locale'   => 'ar',
                    'name'     => 'العربية',
                    'dir'      => 'rtl',
                    'flag'     => 'arab',
                    'facebook' => 'ar_AR',
                ),
                'arg' => array(
                    'code'     => 'an',
                    'locale'   => 'arg',
                    'name'     => 'Aragonés',
                    'dir'      => 'ltr',
                    'flag'     => 'es',
                ),
                'arq' => array(
                    'facebook' => 'ar_AR',
                ),
                'ary' => array(
                    'code'     => 'ar',
                    'locale'   => 'ary',
                    'name'     => 'العربية المغربية',
                    'dir'      => 'rtl',
                    'flag'     => 'ma',
                    'facebook' => 'ar_AR',
                ),
                'as' => array(
                    'code'     => 'as',
                    'locale'   => 'as',
                    'name'     => 'অসমীয়া',
                    'dir'      => 'ltr',
                    'flag'     => 'in',
                    'facebook' => 'as_IN',
                ),
                'az' => array(
                    'code'     => 'az',
                    'locale'   => 'az',
                    'name'     => 'Azərbaycan',
                    'dir'      => 'ltr',
                    'flag'     => 'az',
                    'facebook' => 'az_AZ',
                ),
                'azb' => array(
                    'code'     => 'az',
                    'locale'   => 'azb',
                    'name'     => 'گؤنئی آذربایجان',
                    'dir'      => 'rtl',
                    'flag'     => 'az',
                ),
                'bel' => array(
                    'code'     => 'be',
                    'locale'   => 'bel',
                    'name'     => 'Беларуская мова',
                    'dir'      => 'ltr',
                    'flag'     => 'by',
                    'w3c'      => 'be',
                    'facebook' => 'be_BY',
                ),
                'bg_BG' => array(
                    'code'     => 'bg',
                    'locale'   => 'bg_BG',
                    'name'     => 'български',
                    'dir'      => 'ltr',
                    'flag'     => 'bg',
                    'facebook' => 'bg_BG',
                ),
                'bn_BD' => array(
                    'code'     => 'bn',
                    'locale'   => 'bn_BD',
                    'name'     => 'বাংলা',
                    'dir'      => 'ltr',
                    'flag'     => 'bd',
                    'facebook' => 'bn_IN',
                ),
                'bo' => array(
                    'code'     => 'bo',
                    'locale'   => 'bo',
                    'name'     => 'བོད་ཡིག',
                    'dir'      => 'ltr',
                    'flag'     => 'tibet',
                ),
                'bre' => array(
                    'w3c'      => 'br',
                    'facebook' => 'br_FR',
                ),
                'bs_BA' => array(
                    'code'     => 'bs',
                    'locale'   => 'bs_BA',
                    'name'     => 'Bosanski',
                    'dir'      => 'ltr',
                    'flag'     => 'ba',
                    'facebook' => 'bs_BA',
                ),
                'ca' => array(
                    'code'     => 'ca',
                    'locale'   => 'ca',
                    'name'     => 'Català',
                    'dir'      => 'ltr',
                    'flag'     => 'catalonia',
                    'facebook' => 'ca_ES',
                ),
                'ceb' => array(
                    'code'     => 'ceb',
                    'locale'   => 'ceb',
                    'name'     => 'Cebuano',
                    'dir'      => 'ltr',
                    'flag'     => 'ph',
                    'facebook' => 'cx_PH',
                ),
                'ckb' => array(
                    'code'     => 'ku',
                    'locale'   => 'ckb',
                    'name'     => 'کوردی',
                    'dir'      => 'rtl',
                    'flag'     => 'kurdistan',
                    'facebook' => 'cb_IQ',
                ),
                'co' => array(
                    'facebook' => 'co_FR',
                ),
                'cs_CZ' => array(
                    'code'     => 'cs',
                    'locale'   => 'cs_CZ',
                    'name'     => 'Čeština',
                    'dir'      => 'ltr',
                    'flag'     => 'cz',
                    'facebook' => 'cs_CZ',
                ),
                'cy' => array(
                    'code'     => 'cy',
                    'locale'   => 'cy',
                    'name'     => 'Cymraeg',
                    'dir'      => 'ltr',
                    'flag'     => 'wales',
                    'facebook' => 'cy_GB',
                ),
                'da_DK' => array(
                    'code'     => 'da',
                    'locale'   => 'da_DK',
                    'name'     => 'Dansk',
                    'dir'      => 'ltr',
                    'flag'     => 'dk',
                    'facebook' => 'da_DK',
                ),
                'de_AT' => array(
                    'code'     => 'de',
                    'locale'   => 'de_AT',
                    'name'     => 'Deutsch',
                    'dir'      => 'ltr',
                    'flag'     => 'at',
                    'facebook' => 'de_DE',
                ),
                'de_CH' => array(
                    'code'     => 'de',
                    'locale'   => 'de_CH',
                    'name'     => 'Deutsch',
                    'dir'      => 'ltr',
                    'flag'     => 'ch',
                    'facebook' => 'de_DE',
                ),
                'de_CH_informal' => array(
                    'code'     => 'de',
                    'locale'   => 'de_CH_informal',
                    'name'     => 'Deutsch',
                    'dir'      => 'ltr',
                    'flag'     => 'ch',
                    'w3c'      => 'de-CH',
                    'facebook' => 'de_DE',
                ),
                'de_DE' => array(
                    'code'     => 'de',
                    'locale'   => 'de_DE',
                    'name'     => 'Deutsch',
                    'dir'      => 'ltr',
                    'flag'     => 'de',
                    'facebook' => 'de_DE',
                ),
                'de_DE_formal' => array(
                    'code'     => 'de',
                    'locale'   => 'de_DE_formal',
                    'name'     => 'Deutsch',
                    'dir'      => 'ltr',
                    'flag'     => 'de',
                    'w3c'      => 'de-DE',
                    'facebook' => 'de_DE',
                ),
                'dsb' => array(
                    'code'     => 'dsb',
                    'locale'   => 'dsb',
                    'name'     => 'Dolnoserbšćina',
                    'dir'      => 'ltr',
                    'flag'     => 'de',
                ),
                'dzo' => array(
                    'code'     => 'dz',
                    'locale'   => 'dzo',
                    'name'     => 'རྫོང་ཁ',
                    'dir'      => 'ltr',
                    'flag'     => 'bt',
                    'w3c'      => 'dz',
                ),
                'el' => array(
                    'code'     => 'el',
                    'locale'   => 'el',
                    'name'     => 'Ελληνικά',
                    'dir'      => 'ltr',
                    'flag'     => 'gr',
                    'facebook' => 'el_GR',
                ),
                'en_AU' => array(
                    'code'     => 'en',
                    'locale'   => 'en_AU',
                    'name'     => 'English',
                    'dir'      => 'ltr',
                    'flag'     => 'au',
                    'facebook' => 'en_US',
                ),
                'en_CA' => array(
                    'code'     => 'en',
                    'locale'   => 'en_CA',
                    'name'     => 'English',
                    'dir'      => 'ltr',
                    'flag'     => 'ca',
                    'facebook' => 'en_US',
                ),
                'en_GB' => array(
                    'code'     => 'en',
                    'locale'   => 'en_GB',
                    'name'     => 'English',
                    'dir'      => 'ltr',
                    'flag'     => 'gb',
                    'facebook' => 'en_GB',
                ),
                'en_NZ' => array(
                    'code'     => 'en',
                    'locale'   => 'en_NZ',
                    'name'     => 'English',
                    'dir'      => 'ltr',
                    'flag'     => 'nz',
                    'facebook' => 'en_US',
                ),
                'en_US' => array(
                    'code'     => 'en',
                    'locale'   => 'en_US',
                    'name'     => 'English',
                    'dir'      => 'ltr',
                    'flag'     => 'us',
                    'facebook' => 'en_US',
                ),
                'en_ZA' => array(
                    'code'     => 'en',
                    'locale'   => 'en_ZA',
                    'name'     => 'English',
                    'dir'      => 'ltr',
                    'flag'     => 'za',
                    'facebook' => 'en_US',
                ),
                'eo' => array(
                    'code'     => 'eo',
                    'locale'   => 'eo',
                    'name'     => 'Esperanto',
                    'dir'      => 'ltr',
                    'flag'     => 'esperanto',
                    'facebook' => 'eo_EO',
                ),
                'es_AR' => array(
                    'code'     => 'es',
                    'locale'   => 'es_AR',
                    'name'     => 'Español',
                    'dir'      => 'ltr',
                    'flag'     => 'ar',
                    'facebook' => 'es_LA',
                ),
                'es_CL' => array(
                    'code'     => 'es',
                    'locale'   => 'es_CL',
                    'name'     => 'Español',
                    'dir'      => 'ltr',
                    'flag'     => 'cl',
                    'facebook' => 'es_CL',
                ),
                'es_CO' => array(
                    'code'     => 'es',
                    'locale'   => 'es_CO',
                    'name'     => 'Español',
                    'dir'      => 'ltr',
                    'flag'     => 'co',
                    'facebook' => 'es_CO',
                ),
                'es_CR' => array(
                    'code'     => 'es',
                    'locale'   => 'es_CR',
                    'name'     => 'Español',
                    'dir'      => 'ltr',
                    'flag'     => 'cr',
                    'facebook' => 'es_LA',
                ),
                'es_DO' => array(
                    'code'     => 'es',
                    'locale'   => 'es_DO',
                    'name'     => 'Español',
                    'dir'      => 'ltr',
                    'flag'     => 'do',
                    'facebook' => 'es_LA',
                ),
                'es_EC' => array(
                    'code'     => 'es',
                    'locale'   => 'es_EC',
                    'name'     => 'Español',
                    'dir'      => 'ltr',
                    'flag'     => 'ec',
                    'facebook' => 'es_LA',
                ),
                'es_ES' => array(
                    'code'     => 'es',
                    'locale'   => 'es_ES',
                    'name'     => 'Español',
                    'dir'      => 'ltr',
                    'flag'     => 'es',
                    'facebook' => 'es_ES',
                ),
                'es_GT' => array(
                    'code'     => 'es',
                    'locale'   => 'es_GT',
                    'name'     => 'Español',
                    'dir'      => 'ltr',
                    'flag'     => 'gt',
                    'facebook' => 'es_LA',
                ),
                'es_MX' => array(
                    'code'     => 'es',
                    'locale'   => 'es_MX',
                    'name'     => 'Español',
                    'dir'      => 'ltr',
                    'flag'     => 'mx',
                    'facebook' => 'es_MX',
                ),
                'es_PE' => array(
                    'code'     => 'es',
                    'locale'   => 'es_PE',
                    'name'     => 'Español',
                    'dir'      => 'ltr',
                    'flag'     => 'pe',
                    'facebook' => 'es_LA',
                ),
                'es_PR' => array(
                    'code'     => 'es',
                    'locale'   => 'es_PR',
                    'name'     => 'Español',
                    'dir'      => 'ltr',
                    'flag'     => 'pr',
                    'facebook' => 'es_LA',
                ),
                'es_UY' => array(
                    'code'     => 'es',
                    'locale'   => 'es_UY',
                    'name'     => 'Español',
                    'dir'      => 'ltr',
                    'flag'     => 'uy',
                    'facebook' => 'es_LA',
                ),
                'es_VE' => array(
                    'code'     => 'es',
                    'locale'   => 'es_VE',
                    'name'     => 'Español',
                    'dir'      => 'ltr',
                    'flag'     => 've',
                    'facebook' => 'es_VE',
                ),
                'et' => array(
                    'code'     => 'et',
                    'locale'   => 'et',
                    'name'     => 'Eesti',
                    'dir'      => 'ltr',
                    'flag'     => 'ee',
                    'facebook' => 'et_EE',
                ),
                'eu' => array(
                    'code'     => 'eu',
                    'locale'   => 'eu',
                    'name'     => 'Euskara',
                    'dir'      => 'ltr',
                    'flag'     => 'basque',
                    'facebook' => 'eu_ES',
                ),
                'fa_AF' => array(
                    'code'     => 'fa',
                    'locale'   => 'fa_AF',
                    'name'     => 'فارسی',
                    'dir'      => 'rtl',
                    'flag'     => 'af',
                    'facebook' => 'fa_IR',
                ),
                'fa_IR' => array(
                    'code'     => 'fa',
                    'locale'   => 'fa_IR',
                    'name'     => 'فارسی',
                    'dir'      => 'rtl',
                    'flag'     => 'ir',
                    'facebook' => 'fa_IR',
                ),
                'fi' => array(
                    'code'     => 'fi',
                    'locale'   => 'fi',
                    'name'     => 'Suomi',
                    'dir'      => 'ltr',
                    'flag'     => 'fi',
                    'facebook' => 'fi_FI',
                ),
                'fo' => array(
                    'code'     => 'fo',
                    'locale'   => 'fo',
                    'name'     => 'Føroyskt',
                    'dir'      => 'ltr',
                    'flag'     => 'fo',
                    'facebook' => 'fo_FO',
                ),
                'fr_BE' => array(
                    'code'     => 'fr',
                    'locale'   => 'fr_BE',
                    'name'     => 'Français',
                    'dir'      => 'ltr',
                    'flag'     => 'be',
                    'facebook' => 'fr_FR',
                ),
                'fr_CA' => array(
                    'code'     => 'fr',
                    'locale'   => 'fr_CA',
                    'name'     => 'Français',
                    'dir'      => 'ltr',
                    'flag'     => 'quebec',
                    'facebook' => 'fr_CA',
                ),
                'fr_FR' => array(
                    'code'     => 'fr',
                    'locale'   => 'fr_FR',
                    'name'     => 'Français',
                    'dir'      => 'ltr',
                    'flag'     => 'fr',
                    'facebook' => 'fr_FR',
                ),
                'fuc' => array(
                    'facebook' => 'ff_NG',
                ),
                'fur' => array(
                    'code'     => 'fur',
                    'locale'   => 'fur',
                    'name'     => 'Furlan',
                    'dir'      => 'ltr',
                    'flag'     => 'it',
                ),
                'fy' => array(
                    'code'     => 'fy',
                    'locale'   => 'fy',
                    'name'     => 'Frysk',
                    'dir'      => 'ltr',
                    'flag'     => 'nl',
                    'facebook' => 'fy_NL',
                ),
                'ga' => array(
                    'facebook' => 'ga_IE',
                ),
                'gax' => array(
                    'facebook' => 'om_ET',
                ),
                'gd' => array(
                    'code'     => 'gd',
                    'locale'   => 'gd',
                    'name'     => 'Gàidhlig',
                    'dir'      => 'ltr',
                    'flag'     => 'scotland',
                ),
                'gl_ES' => array(
                    'code'     => 'gl',
                    'locale'   => 'gl_ES',
                    'name'     => 'Galego',
                    'dir'      => 'ltr',
                    'flag'     => 'galicia',
                    'facebook' => 'gl_ES',
                ),
                'gn' => array(
                    'facebook' => 'gn_PY',
                ),
                'gu' => array(
                    'code'     => 'gu',
                    'locale'   => 'gu',
                    'name'     => 'ગુજરાતી',
                    'dir'      => 'ltr',
                    'flag'     => 'in',
                    'facebook' => 'gu_IN',
                ),
                'hat' => array(
                    'facebook' => 'ht_HT',
                ),
                'hau' => array(
                    'facebook' => 'ha_NG',
                ),
                'haz' => array(
                    'code'     => 'haz',
                    'locale'   => 'haz',
                    'name'     => 'هزاره گی',
                    'dir'      => 'rtl',
                    'flag'     => 'af',
                ),
                'he_IL' => array(
                    'code'     => 'he',
                    'locale'   => 'he_IL',
                    'name'     => 'עברית',
                    'dir'      => 'rtl',
                    'flag'     => 'il',
                    'facebook' => 'he_IL',
                ),
                'hi_IN' => array(
                    'code'     => 'hi',
                    'locale'   => 'hi_IN',
                    'name'     => 'हिन्दी',
                    'dir'      => 'ltr',
                    'flag'     => 'in',
                    'facebook' => 'hi_IN',
                ),
                'hr' => array(
                    'code'     => 'hr',
                    'locale'   => 'hr',
                    'name'     => 'Hrvatski',
                    'dir'      => 'ltr',
                    'flag'     => 'hr',
                    'facebook' => 'hr_HR',
                ),
                'hu_HU' => array(
                    'code'     => 'hu',
                    'locale'   => 'hu_HU',
                    'name'     => 'Magyar',
                    'dir'      => 'ltr',
                    'flag'     => 'hu',
                    'facebook' => 'hu_HU',
                ),
                'hsb' => array(
                    'code'     => 'hsb',
                    'locale'   => 'hsb',
                    'name'     => 'Hornjoserbšćina',
                    'dir'      => 'ltr',
                    'flag'     => 'de',
                ),
                'hy' => array(
                    'code'     => 'hy',
                    'locale'   => 'hy',
                    'name'     => 'Հայերեն',
                    'dir'      => 'ltr',
                    'flag'     => 'am',
                    'facebook' => 'hy_AM',
                ),
                'id_ID' => array(
                    'code'     => 'id',
                    'locale'   => 'id_ID',
                    'name'     => 'Bahasa Indonesia',
                    'dir'      => 'ltr',
                    'flag'     => 'id',
                    'facebook' => 'id_ID',
                ),
                'ido' => array(
                    'w3c'      => 'io',
                ),
                'is_IS' => array(
                    'code'     => 'is',
                    'locale'   => 'is_IS',
                    'name'     => 'Íslenska',
                    'dir'      => 'ltr',
                    'flag'     => 'is',
                    'facebook' => 'is_IS',
                ),
                'it_IT' => array(
                    'code'     => 'it',
                    'locale'   => 'it_IT',
                    'name'     => 'Italiano',
                    'dir'      => 'ltr',
                    'flag'     => 'it',
                    'facebook' => 'it_IT',
                ),
                'ja' => array(
                    'code'     => 'ja',
                    'locale'   => 'ja',
                    'name'     => '日本語',
                    'dir'      => 'ltr',
                    'flag'     => 'jp',
                    'facebook' => 'ja_JP',
                ),
                'jv_ID' => array(
                    'code'     => 'jv',
                    'locale'   => 'jv_ID',
                    'name'     => 'Basa Jawa',
                    'dir'      => 'ltr',
                    'flag'     => 'id',
                    'facebook' => 'jv_ID',
                ),
                'ka_GE' => array(
                    'code'     => 'ka',
                    'locale'   => 'ka_GE',
                    'name'     => 'ქართული',
                    'dir'      => 'ltr',
                    'flag'     => 'ge',
                    'facebook' => 'ka_GE',
                ),
                'kab' => array(
                    'code'     => 'kab',
                    'locale'   => 'kab',
                    'name'     => 'Taqbaylit',
                    'dir'      => 'ltr',
                    'flag'     => 'dz',
                ),
                'kin' => array(
                    'w3c'      => 'rw',
                    'facebook' => 'rw_RW',
                ),
                'kir' => array(
                    'code'     => 'ky',
                    'locale'   => 'kir',
                    'name'     => 'Кыргызча',
                    'dir'      => 'ltr',
                    'flag'     => 'kg',
                ),
                'kk' => array(
                    'code'     => 'kk',
                    'locale'   => 'kk',
                    'name'     => 'Қазақ тілі',
                    'dir'      => 'ltr',
                    'flag'     => 'kz',
                    'facebook' => 'kk_KZ',
                ),
                'km' => array(
                    'code'     => 'km',
                    'locale'   => 'km',
                    'name'     => 'ភាសាខ្មែរ',
                    'dir'      => 'ltr',
                    'flag'     => 'kh',
                    'facebook' => 'km_KH',
                ),
                'kn' => array(
                    'code'     => 'kn',
                    'locale'   => 'kn',
                    'name'     => 'ಕನ್ನಡ',
                    'dir'      => 'ltr',
                    'flag'     => 'in',
                    'facebook' => 'kn_IN',
                ),
                'ko_KR' => array(
                    'code'     => 'ko',
                    'locale'   => 'ko_KR',
                    'name'     => '한국어',
                    'dir'      => 'ltr',
                    'flag'     => 'kr',
                    'facebook' => 'ko_KR',
                ),
                'ku' => array(
                    'facebook' => 'ku_TR',
                ),
                'ky_KY' => array(
                    'facebook' => 'ky_KG',
                ),
                'la' => array(
                    'facebook' => 'la_VA',
                ),
                'li' => array(
                    'facebook' => 'li_NL',
                ),
                'lin' => array(
                    'facebook' => 'ln_CD',
                ),
                'lo' => array(
                    'code'     => 'lo',
                    'locale'   => 'lo',
                    'name'     => 'ພາສາລາວ',
                    'dir'      => 'ltr',
                    'flag'     => 'la',
                    'facebook' => 'lo_LA',
                ),
                'lt_LT' => array(
                    'code'     => 'lt',
                    'locale'   => 'lt_LT',
                    'name'     => 'Lietuviškai',
                    'dir'      => 'ltr',
                    'flag'     => 'lt',
                    'facebook' => 'lt_LT',
                ),
                'lv' => array(
                    'code'     => 'lv',
                    'locale'   => 'lv',
                    'name'     => 'Latviešu valoda',
                    'dir'      => 'ltr',
                    'flag'     => 'lv',
                    'facebook' => 'lv_LV',
                ),
                'mg_MG' => array(
                    'facebook' => 'mg_MG',
                ),
                'mk_MK' => array(
                    'code'     => 'mk',
                    'locale'   => 'mk_MK',
                    'name'     => 'македонски јазик',
                    'dir'      => 'ltr',
                    'flag'     => 'mk',
                    'facebook' => 'mk_MK',
                ),
                'ml_IN' => array(
                    'code'     => 'ml',
                    'locale'   => 'ml_IN',
                    'name'     => 'മലയാളം',
                    'dir'      => 'ltr',
                    'flag'     => 'in',
                    'facebook' => 'ml_IN',
                ),
                'mlt' => array(
                    'facebook' => 'mt_MT',
                ),
                'mn' => array(
                    'code'     => 'mn',
                    'locale'   => 'mn',
                    'name'     => 'Монгол хэл',
                    'dir'      => 'ltr',
                    'flag'     => 'mn',
                    'facebook' => 'mn_MN',
                ),
                'mr' => array(
                    'code'     => 'mr',
                    'locale'   => 'mr',
                    'name'     => 'मराठी',
                    'dir'      => 'ltr',
                    'flag'     => 'in',
                    'facebook' => 'mr_IN',
                ),
                'mri' => array(
                    'w3c'      => 'mi',
                    'facebook' => 'mi_NZ',
                ),
                'ms_MY' => array(
                    'code'     => 'ms',
                    'locale'   => 'ms_MY',
                    'name'     => 'Bahasa Melayu',
                    'dir'      => 'ltr',
                    'flag'     => 'my',
                    'facebook' => 'ms_MY',
                ),
                'my_MM' => array(
                    'code'     => 'my',
                    'locale'   => 'my_MM',
                    'name'     => 'ဗမာစာ',
                    'dir'      => 'ltr',
                    'flag'     => 'mm',
                    'facebook' => 'my_MM',
                ),
                'nb_NO' => array(
                    'code'     => 'nb',
                    'locale'   => 'nb_NO',
                    'name'     => 'Norsk Bokmål',
                    'dir'      => 'ltr',
                    'flag'     => 'no',
                    'facebook' => 'nb_NO',
                ),
                'ne_NP' => array(
                    'code'     => 'ne',
                    'locale'   => 'ne_NP',
                    'name'     => 'नेपाली',
                    'dir'      => 'ltr',
                    'flag'     => 'np',
                    'facebook' => 'ne_NP',
                ),
                'nl_BE' => array(
                    'code'     => 'nl',
                    'locale'   => 'nl_BE',
                    'name'     => 'Nederlands',
                    'dir'      => 'ltr',
                    'flag'     => 'be',
                    'facebook' => 'nl_BE',
                ),
                'nl_NL' => array(
                    'code'     => 'nl',
                    'locale'   => 'nl_NL',
                    'name'     => 'Nederlands',
                    'dir'      => 'ltr',
                    'flag'     => 'nl',
                    'facebook' => 'nl_NL',
                ),
                'nl_NL_formal' => array(
                    'code'     => 'nl',
                    'locale'   => 'nl_NL_formal',
                    'name'     => 'Nederlands',
                    'dir'      => 'ltr',
                    'flag'     => 'nl',
                    'w3c'      => 'nl-NL',
                    'facebook' => 'nl_NL',
                ),
                'nn_NO' => array(
                    'code'     => 'nn',
                    'locale'   => 'nn_NO',
                    'name'     => 'Norsk Nynorsk',
                    'dir'      => 'ltr',
                    'flag'     => 'no',
                    'facebook' => 'nn_NO',
                ),
                'oci' => array(
                    'code'     => 'oc',
                    'locale'   => 'oci',
                    'name'     => 'Occitan',
                    'dir'      => 'ltr',
                    'flag'     => 'occitania',
                    'w3c'      => 'oc',
                ),
                'ory' => array(
                    'facebook' => 'or_IN',
                ),
                'pa_IN' => array(
                    'code'     => 'pa',
                    'locale'   => 'pa_IN',
                    'name'     => 'ਪੰਜਾਬੀ',
                    'dir'      => 'ltr',
                    'flag'     => 'in',
                    'facebook' => 'pa_IN',
                ),
                'pl_PL' => array(
                    'code'     => 'pl',
                    'locale'   => 'pl_PL',
                    'name'     => 'Polski',
                    'dir'      => 'ltr',
                    'flag'     => 'pl',
                    'facebook' => 'pl_PL',
                ),
                'ps' => array(
                    'code'     => 'ps',
                    'locale'   => 'ps',
                    'name'     => 'پښتو',
                    'dir'      => 'rtl',
                    'flag'     => 'af',
                    'facebook' => 'ps_AF',
                ),
                'pt_AO' => array(
                    'code'     => 'pt',
                    'locale'   => 'pt_AO',
                    'name'     => 'Português',
                    'dir'      => 'ltr',
                    'flag'     => 'ao',
                    'facebook' => 'pt_PT',
                ),
                'pt_BR' => array(
                    'code'     => 'pt',
                    'locale'   => 'pt_BR',
                    'name'     => 'Português',
                    'dir'      => 'ltr',
                    'flag'     => 'br',
                    'facebook' => 'pt_BR',
                ),
                'pt_PT' => array(
                    'code'     => 'pt',
                    'locale'   => 'pt_PT',
                    'name'     => 'Português',
                    'dir'      => 'ltr',
                    'flag'     => 'pt',
                    'facebook' => 'pt_PT',
                ),
                'pt_PT_ao90' => array(
                    'code'     => 'pt',
                    'locale'   => 'pt_PT_ao90',
                    'name'     => 'Português',
                    'dir'      => 'ltr',
                    'flag'     => 'pt',
                    'facebook' => 'pt_PT',
                ),
                'rhg' => array(
                    'code'     => 'rhg',
                    'locale'   => 'rhg',
                    'name'     => 'Ruáinga',
                    'dir'      => 'ltr',
                    'flag'     => 'mm',
                ),
                'ro_RO' => array(
                    'code'     => 'ro',
                    'locale'   => 'ro_RO',
                    'name'     => 'Română',
                    'dir'      => 'ltr',
                    'flag'     => 'ro',
                    'facebook' => 'ro_RO',
                ),
                'roh' => array(
                    'w3c'      => 'rm',
                    'facebook' => 'rm_CH',
                ),
                'ru_RU' => array(
                    'code'     => 'ru',
                    'locale'   => 'ru_RU',
                    'name'     => 'Русский',
                    'dir'      => 'ltr',
                    'flag'     => 'ru',
                    'facebook' => 'ru_RU',
                ),
                'sa_IN' => array(
                    'facebook' => 'sa_IN',
                ),
                'sah' => array(
                    'code'     => 'sah',
                    'locale'   => 'sah',
                    'name'     => 'Сахалыы',
                    'dir'      => 'ltr',
                    'flag'     => 'ru',
                ),
                'si_LK' => array(
                    'code'     => 'si',
                    'locale'   => 'si_LK',
                    'name'     => 'සිංහල',
                    'dir'      => 'ltr',
                    'flag'     => 'lk',
                    'facebook' => 'si_LK',
                ),
                'sk_SK' => array(
                    'code'     => 'sk',
                    'locale'   => 'sk_SK',
                    'name'     => 'Slovenčina',
                    'dir'      => 'ltr',
                    'flag'     => 'sk',
                    'facebook' => 'sk_SK',
                ),
                'skr' => array(
                    'code'     => 'skr',
                    'locale'   => 'skr',
                    'name'     => 'سرائیکی',
                    'dir'      => 'rtl',
                    'flag'     => 'pk',
                ),
                'sl_SI' => array(
                    'code'     => 'sl',
                    'locale'   => 'sl_SI',
                    'name'     => 'Slovenščina',
                    'dir'      => 'ltr',
                    'flag'     => 'si',
                    'facebook' => 'sl_SI',
                ),
                'sna' => array(
                    'facebook' => 'sn_ZW',
                ),
                'snd' => array(
                    'code'     => 'sd',
                    'locale'   => 'snd',
                    'name'     => 'سنڌي',
                    'dir'      => 'rtl',
                    'flag'     => 'pk',
                ),
                'so_SO' => array(
                    'code'     => 'so',
                    'locale'   => 'so_SO',
                    'name'     => 'Af-Soomaali',
                    'dir'      => 'ltr',
                    'flag'     => 'so',
                    'facebook' => 'so_SO',
                ),
                'sq' => array(
                    'code'     => 'sq',
                    'locale'   => 'sq',
                    'name'     => 'Shqip',
                    'dir'      => 'ltr',
                    'flag'     => 'al',
                    'facebook' => 'sq_AL',
                ),
                'sr_RS' => array(
                    'code'     => 'sr',
                    'locale'   => 'sr_RS',
                    'name'     => 'Српски језик',
                    'dir'      => 'ltr',
                    'flag'     => 'rs',
                    'facebook' => 'sr_RS',
                ),
                'srd' => array(
                    'w3c'      => 'sc',
                    'facebook' => 'sc_IT',
                ),
                'su_ID' => array(
                    'code'     => 'su',
                    'locale'   => 'su_ID',
                    'name'     => 'Basa Sunda',
                    'dir'      => 'ltr',
                    'flag'     => 'id',
                    'facebook' => 'su_ID',
                ),
                'sv_SE' => array(
                    'code'     => 'sv',
                    'locale'   => 'sv_SE',
                    'name'     => 'Svenska',
                    'dir'      => 'ltr',
                    'flag'     => 'se',
                    'facebook' => 'sv_SE',
                ),
                'sw' => array(
                    'code'     => 'sw',
                    'locale'   => 'sw',
                    'name'     => 'Kiswahili',
                    'dir'      => 'ltr',
                    'flag'     => 'ke',
                    'facebook' => 'sw_KE',
                ),
                'syr' => array(
                    'facebook' => 'sy_SY',
                ),
                'szl' => array(
                    'code'     => 'szl',
                    'locale'   => 'szl',
                    'name'     => 'Ślōnskŏ gŏdka',
                    'dir'      => 'ltr',
                    'flag'     => 'pl',
                    'facebook' => 'sz_PL',
                ),
                'ta_IN' => array(
                    'code'     => 'ta',
                    'locale'   => 'ta_IN',
                    'name'     => 'தமிழ்',
                    'dir'      => 'ltr',
                    'flag'     => 'in',
                    'facebook' => 'ta_IN',
                ),
                'ta_LK' => array(
                    'code'     => 'ta',
                    'locale'   => 'ta_LK',
                    'name'     => 'தமிழ்',
                    'dir'      => 'ltr',
                    'flag'     => 'lk',
                    'facebook' => 'ta_IN',
                ),
                'tah' => array(
                    'code'     => 'ty',
                    'locale'   => 'tah',
                    'name'     => 'Reo Tahiti',
                    'dir'      => 'ltr',
                    'flag'     => 'pf',
                ),
                'te' => array(
                    'code'     => 'te',
                    'locale'   => 'te',
                    'name'     => 'తెలుగు',
                    'dir'      => 'ltr',
                    'flag'     => 'in',
                    'facebook' => 'te_IN',
                ),
                'tg' => array(
                    'facebook' => 'tg_TJ',
                ),
                'th' => array(
                    'code'     => 'th',
                    'locale'   => 'th',
                    'name'     => 'ไทย',
                    'dir'      => 'ltr',
                    'flag'     => 'th',
                    'facebook' => 'th_TH',
                ),
                'tl' => array(
                    'code'     => 'tl',
                    'locale'   => 'tl',
                    'name'     => 'Tagalog',
                    'dir'      => 'ltr',
                    'flag'     => 'ph',
                    'facebook' => 'tl_PH',
                ),
                'tr_TR' => array(
                    'code'     => 'tr',
                    'locale'   => 'tr_TR',
                    'name'     => 'Türkçe',
                    'dir'      => 'ltr',
                    'flag'     => 'tr',
                    'facebook' => 'tr_TR',
                ),
                'tt_RU' => array(
                    'code'     => 'tt',
                    'locale'   => 'tt_RU',
                    'name'     => 'Татар теле',
                    'dir'      => 'ltr',
                    'flag'     => 'ru',
                    'facebook' => 'tt_RU',
                ),
                'tuk' => array(
                    'w3c'      => 'tk',
                    'facebook' => 'tk_TM',
                ),
                'tzm' => array(
                    'facebook' => 'tz_MA',
                ),
                'ug_CN' => array(
                    'code'     => 'ug',
                    'locale'   => 'ug_CN',
                    'name'     => 'Uyƣurqə',
                    'dir'      => 'ltr',
                    'flag'     => 'cn',
                ),
                'uk' => array(
                    'code'     => 'uk',
                    'locale'   => 'uk',
                    'name'     => 'Українська',
                    'dir'      => 'ltr',
                    'flag'     => 'ua',
                    'facebook' => 'uk_UA',
                ),
                'ur' => array(
                    'code'     => 'ur',
                    'locale'   => 'ur',
                    'name'     => 'اردو',
                    'dir'      => 'rtl',
                    'flag'     => 'pk',
                    'facebook' => 'ur_PK',
                ),
                'uz_UZ' => array(
                    'code'     => 'uz',
                    'locale'   => 'uz_UZ',
                    'name'     => 'Oʻzbek',
                    'dir'      => 'ltr',
                    'flag'     => 'uz',
                    'facebook' => 'uz_UZ',
                ),
                'vec' => array(
                    'code'     => 'vec',
                    'locale'   => 'vec',
                    'name'     => 'Vèneto',
                    'dir'      => 'ltr',
                    'flag'     => 'veneto',
                ),
                'vi' => array(
                    'code'     => 'vi',
                    'locale'   => 'vi',
                    'name'     => 'Tiếng Việt',
                    'dir'      => 'ltr',
                    'flag'     => 'vn',
                    'facebook' => 'vi_VN',
                ),
                'xho' => array(
                    'facebook' => 'xh_ZA',
                ),
                'yor' => array(
                    'facebook' => 'yo_NG',
                ),
                'zh_CN' => array(
                    'code'     => 'zh',
                    'locale'   => 'zh_CN',
                    'name'     => '中文 (中国)',
                    'dir'      => 'ltr',
                    'flag'     => 'cn',
                    'facebook' => 'zh_CN',
                ),
                'zh_HK' => array(
                    'code'     => 'zh',
                    'locale'   => 'zh_HK',
                    'name'     => '中文 (香港)',
                    'dir'      => 'ltr',
                    'flag'     => 'hk',
                    'facebook' => 'zh_HK',
                ),
                'zh_TW' => array(
                    'code'     => 'zh',
                    'locale'   => 'zh_TW',
                    'name'     => '中文 (台灣)',
                    'dir'      => 'ltr',
                    'flag'     => 'tw',
                    'facebook' => 'zh_TW',
                ),
            );            
        }

    }

    global $bookingpress_multilanguage;
	$bookingpress_multilanguage = new bookingpress_multilanguage;
}