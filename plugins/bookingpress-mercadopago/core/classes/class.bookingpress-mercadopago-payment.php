<?php
if (!class_exists('bookingpress_mercadopago_payment')) {
	class bookingpress_mercadopago_payment {
        var $bookingpress_selected_payment_method;
        var $bookingpress_mercadopago_public_key;
        var $bookingpress_mercadopago_access_token;
        var $bookingpress_mercadopago_secret_signature;
        
        function __construct() {
            if(is_plugin_active('bookingpress-appointment-booking-pro/bookingpress-appointment-booking-pro.php')) {

                add_filter('bookingpress_mercadopago_submit_form_data', array($this, 'bookingpress_mercadopago_submit_form_data_func'), 10, 2);
                add_action('wp', array($this, 'bookingpress_payment_gateway_data'));

                add_filter('bookingpress_mercadopago_apply_refund', array($this, 'bookingpress_mercadopago_apply_refund_func'),10,2);

                add_filter( 'bookingpress_package_order_mercadopago_submit_form_data', array( $this, 'bookingpress_package_order_mercadopago_submission_data' ), 10, 2 );

                /* Stripe Payment GateWay Submit Data - Gift Card*/
                add_filter('bookingpress_gift_card_order_mercadopago_submit_form_data', array($this, 'bookingpress_gift_card_order_mercadopago_submission_data'), 10, 2);

            }    
        }

        
        function bookingpress_init_mercadopago(){
            global $BookingPress;
            $bookingpress_mercadopago_payment_mode = $BookingPress->bookingpress_get_settings('mercadopago_payment_mode', 'payment_setting');
            $this->bookingpress_selected_payment_method = !empty($bookingpress_mercadopago_payment_mode) ? $bookingpress_mercadopago_payment_mode : 'sandbox';

            $bookingpress_mercadopago_public_key = $BookingPress->bookingpress_get_settings('mercadopago_public_key', 'payment_setting');
            $this->bookingpress_mercadopago_public_key = !empty($bookingpress_mercadopago_public_key) ? $bookingpress_mercadopago_public_key : '';

            $bookingpress_mercadopago_access_token = $BookingPress->bookingpress_get_settings('mercadopago_access_token', 'payment_setting');
            $this->bookingpress_mercadopago_access_token = !empty($bookingpress_mercadopago_access_token) ? $bookingpress_mercadopago_access_token : '';

            $bookingpress_mercadopago_secret_signature = $BookingPress->bookingpress_get_settings('mercadopago_secret_signature', 'payment_setting');
            $this->bookingpress_mercadopago_secret_signature = !empty($bookingpress_mercadopago_secret_signature) ? $bookingpress_mercadopago_secret_signature : '';

        }
        
        /**
         * Function for Package Buy
         *
        */
        function bookingpress_package_order_mercadopago_submission_data($response, $bookingpress_return_data){
            global $wpdb, $BookingPress, $bookingpress_pro_payment_gateways, $bookingpress_debug_payment_log_id;
            $this->bookingpress_init_mercadopago();

            do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercadopago submitted form data', 'bookingpress pro', $bookingpress_return_data, $bookingpress_debug_payment_log_id );

            if( !empty( $bookingpress_return_data ) ){                
                
                global $BookingPress;
                
                $entry_id                          = $bookingpress_return_data['entry_id'];
                $bookingpress_is_cart = !empty($bookingpress_return_data['is_cart']) ? 1 : 0;
                $currency_code                     = strtoupper($bookingpress_return_data['currency_code']);
                $bookingpress_final_payable_amount = isset( $bookingpress_return_data['payable_amount'] ) ? ($bookingpress_return_data['payable_amount']) : 0;
                $bookingpress_final_payable_amount = (int)$bookingpress_final_payable_amount;
                $customer_details                  = $bookingpress_return_data['customer_details'];
                $customer_email                    = ! empty( $customer_details['customer_email'] ) ? $customer_details['customer_email'] : '';
                $customer_firstname = !empty($customer_details['customer_firstname']) ? $customer_details['customer_firstname'] : $customer_email;
                $customer_lastname = !empty($customer_details['customer_lastname']) ? $customer_details['customer_lastname'] : $customer_email;

                $bookingpress_service_name =  !empty( $bookingpress_return_data['selected_package_details']['bookingpress_package_name'] ) ? $bookingpress_return_data['selected_package_details']['bookingpress_package_name'] : __( 'BookingPress Package', 'bookingpress-mercadopago' );

                $bookingpress_notify_url = $bookingpress_return_data['notify_url'];
                $redirect_url = $bookingpress_return_data['approved_appointment_url'];

                $bookingpress_booked_service_name = $bookingpress_service_name;

                

                //$webhook_url = add_query_arg('bookingpress-listener', 'bpa_pro_mercadopago_url', $redirect_url);

                $webhook_url = $bookingpress_notify_url;

                $bookingpress_cancel_url = $bookingpress_return_data['canceled_appointment_url'];

                //$BookingPress->bookingpress_write_response($webhook_url);
                
                $bookingpress_create_preference_params = array();
                $bookingpress_create_preference_params['external_reference'] = 'ref-'.$entry_id;
                $bookingpress_create_preference_params['auto_return'] = "all";
                $bookingpress_create_preference_params['items'][] = array(
                    'title' => $bookingpress_booked_service_name,
                    'quantity' => 1,
                    'currency_id' => $currency_code,
                    "unit_price" => (float)$bookingpress_final_payable_amount,
                );
                $bookingpress_create_preference_params['notification_url'] = $webhook_url;
                $bookingpress_create_preference_params['payer'] = array(
                    'name' => $customer_firstname,
                    'surname' => $customer_lastname,
                    'email' => $customer_email,
                );
                $bookingpress_create_preference_params['back_urls'] = array(
                    'success' => $redirect_url
                ); 
                $bookingpress_create_preference_params['metadata'] = array(
                    'entry_id' => $entry_id,
                    'bookingpress_is_cart' => $bookingpress_is_cart,
                );
                $bookingpress_create_preference_header_params = array(
                    'Authorization' => 'Bearer '.$this->bookingpress_mercadopago_access_token,
                );
                

                $bookingpress_create_preference_body_params = array(
                    'method' => 'POST',
                    'body' => json_encode($bookingpress_create_preference_params),
                    'headers' => $bookingpress_create_preference_header_params,
                    'timeout' => 5000,
                );
                
                $bookingpress_create_preference_url = "https://api.mercadopago.com/checkout/preferences";
                $bookingpress_created_preference_response = wp_remote_request($bookingpress_create_preference_url, $bookingpress_create_preference_body_params);

                do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercadopago Create Preference Response', 'bookingpress pro', $bookingpress_created_preference_response, $bookingpress_debug_payment_log_id );

                if(!is_wp_error($bookingpress_created_preference_response))
                {
                    $bookingpress_created_preference_response_arr = json_decode($bookingpress_created_preference_response['body'], TRUE);

                    if(!empty($bookingpress_created_preference_response_arr['error']))
                    {
                        $err_msg = __('Error returned from payment gateway ', 'bookingpress-mercadopago');
                        $err_msg .= !empty($bookingpress_created_preference_response_arr['message']) ? $bookingpress_created_preference_response_arr['message'] : __('Something went wrong while processing with payment', 'bookingpress-mercadopago');
                        $response['variant']       = 'error';
                        $response['title']         = esc_html__( 'Error', 'bookingpress-mercadopago' );
                        $response['msg']           = $err_msg;
                        $response['is_redirect']   = 0;
                        $response['redirect_data'] = '';
                        $response['is_spam']       = 0;

                    }
                    else
                    {
                        $bookingpress_checkout_url = ( $this->bookingpress_selected_payment_method=='sandbox') ? $bookingpress_created_preference_response_arr['sandbox_init_point'] : $bookingpress_created_preference_response_arr['init_point'];
                        $redirect = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="' . $bookingpress_checkout_url . '";</script>';

                        do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercadopago Checkout URL', 'bookingpress pro', $bookingpress_checkout_url, $bookingpress_debug_payment_log_id );
                       
                        $response['variant']       = 'redirect';
                        $response['title']         = '';
                        $response['msg']           = '';
                        $response['is_redirect']   = 1;
                        $response['redirect_data'] = $redirect;
                        $response['entry_id'] = $entry_id;
                    }
                }
                else
                {
                    $response['variant']       = 'error';
                    $response['title']         = esc_html__( 'Error', 'bookingpress-mercadopago' );
                    $response['msg']           = __('Something went wrong while processing with payment', 'bookingpress-mercadopago');
                    $response['is_redirect']   = 0;
                    $response['redirect_data'] = '';
                    $response['is_spam']       = 0;
                }                

                
            } 

            return $response;
        }
        
        /**
         * Function for Gift Card Buy
         *
        */
        function bookingpress_gift_card_order_mercadopago_submission_data($response, $bookingpress_return_data){
            global $wpdb, $BookingPress, $bookingpress_pro_payment_gateways, $bookingpress_debug_payment_log_id;
            $this->bookingpress_init_mercadopago();

            do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercadopago gift card submitted form data', 'bookingpress pro', $bookingpress_return_data, $bookingpress_debug_payment_log_id );

            if( !empty( $bookingpress_return_data ) ){                
                
                global $BookingPress;
                
                $entry_id                          = $bookingpress_return_data['entry_id'];
                $bookingpress_is_cart              = isset($bookingpress_return_data['is_cart']) ? 1 : 0;
                $currency_code                     = strtoupper($bookingpress_return_data['currency_code']);
                $bookingpress_final_payable_amount = isset( $bookingpress_return_data['payable_amount'] ) ? ($bookingpress_return_data['payable_amount']) : 0;
                $bookingpress_final_payable_amount = (int)$bookingpress_final_payable_amount;
                $customer_details                  = $bookingpress_return_data['customer_details'];
                $customer_email                    = ! empty( $customer_details['customer_email'] ) ? $customer_details['customer_email'] : '';
                $customer_firstname = !empty($customer_details['customer_firstname']) ? $customer_details['customer_firstname'] : $customer_email;
                $customer_lastname = !empty($customer_details['customer_lastname']) ? $customer_details['customer_lastname'] : $customer_email;

                $bookingpress_service_name =  !empty( $bookingpress_return_data['selected_gift_card_details']['bookingpress_gift_card_title'] ) ? $bookingpress_return_data['selected_gift_card_details']['bookingpress_gift_card_title'] : __( 'BookingPress Gift Card', 'bookingpress-mercadopago' );

                $bookingpress_notify_url = $bookingpress_return_data['notify_url'];
                $redirect_url = $bookingpress_return_data['approved_appointment_url'];
                $webhook_url = $bookingpress_notify_url;

                $bookingpress_cancel_url = $bookingpress_return_data['canceled_appointment_url'];

                $bookingpress_create_preference_params = array();
                $bookingpress_create_preference_params['external_reference'] = 'ref-'.$entry_id;
                $bookingpress_create_preference_params['auto_return'] = "all";
                $bookingpress_create_preference_params['items'][] = array(
                    'title' => stripslashes_deep($bookingpress_service_name),
                    'quantity' => 1,
                    'currency_id' => $currency_code,
                    "unit_price" => (float)$bookingpress_final_payable_amount,
                );
                $bookingpress_create_preference_params['notification_url'] = $webhook_url;
                $bookingpress_create_preference_params['payer'] = array(
                    'name' => $customer_firstname,
                    'surname' => $customer_lastname,
                    'email' => $customer_email,
                );
                $bookingpress_create_preference_params['back_urls'] = array(
                    'success' => $redirect_url
                ); 
                $bookingpress_create_preference_params['metadata'] = array(
                    'entry_id' => $entry_id,
                    'bookingpress_is_cart' => $bookingpress_is_cart,
                );
                $bookingpress_create_preference_header_params = array(
                    'Authorization' => 'Bearer '.$this->bookingpress_mercadopago_access_token,
                );
                
                $bookingpress_create_preference_body_params = array(
                    'method' => 'POST',
                    'body' => json_encode($bookingpress_create_preference_params),
                    'headers' => $bookingpress_create_preference_header_params,
                    'timeout' => 5000,
                );
                
                $bookingpress_create_preference_url = "https://api.mercadopago.com/checkout/preferences";
                $bookingpress_created_preference_response = wp_remote_request($bookingpress_create_preference_url, $bookingpress_create_preference_body_params);
                do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercadopago gift card Create Preference Response', 'bookingpress pro', $bookingpress_created_preference_response, $bookingpress_debug_payment_log_id );
                if(!is_wp_error($bookingpress_created_preference_response)) {
                    $bookingpress_created_preference_response_arr = json_decode($bookingpress_created_preference_response['body'], TRUE);
                    if(!empty($bookingpress_created_preference_response_arr['error'])) {
                        $err_msg = __('Error returned from payment gateway ', 'bookingpress-mercadopago');
                        $err_msg .= !empty($bookingpress_created_preference_response_arr['message']) ? $bookingpress_created_preference_response_arr['message'] : __('Something went wrong while processing with payment', 'bookingpress-mercadopago');
                        $response['variant']       = 'error';
                        $response['title']         = esc_html__( 'Error', 'bookingpress-mercadopago' );
                        $response['msg']           = $err_msg;
                        $response['is_redirect']   = 0;
                        $response['redirect_data'] = '';
                        $response['is_spam']       = 0;

                    }
                    else {
                        $bookingpress_checkout_url = ( $this->bookingpress_selected_payment_method=='sandbox') ? $bookingpress_created_preference_response_arr['sandbox_init_point'] : $bookingpress_created_preference_response_arr['init_point'];
                        $redirect = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="' . $bookingpress_checkout_url . '";</script>';

                        do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercadopago gift card Checkout URL', 'bookingpress pro', $bookingpress_checkout_url, $bookingpress_debug_payment_log_id );
                       
                        $response['variant']       = 'redirect';
                        $response['title']         = '';
                        $response['msg']           = '';
                        $response['is_redirect']   = 1;
                        $response['redirect_data'] = $redirect;
                        $response['entry_id'] = $entry_id;
                    }
                }
                else {
                    $response['variant']       = 'error';
                    $response['title']         = esc_html__( 'Error', 'bookingpress-mercadopago' );
                    $response['msg']           = __('Something went wrong while processing with payment', 'bookingpress-mercadopago');
                    $response['is_redirect']   = 0;
                    $response['redirect_data'] = '';
                    $response['is_spam']       = 0;
                }              
            } 
            return $response;
        }
        /**
         * Function for appointment booking
         *
        */
        function bookingpress_mercadopago_submit_form_data_func($response, $bookingpress_return_data){
            global $wpdb, $BookingPress, $bookingpress_pro_payment_gateways, $bookingpress_debug_payment_log_id;
            $this->bookingpress_init_mercadopago();

            do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercadopago submitted form data', 'bookingpress pro', $bookingpress_return_data, $bookingpress_debug_payment_log_id );

            if(!empty($bookingpress_return_data)){
                $entry_id                          = $bookingpress_return_data['entry_id'];
                $bookingpress_is_cart = !empty($bookingpress_return_data['is_cart']) ? 1 : 0;
                $currency_code                     = strtoupper($bookingpress_return_data['currency_code']);
                $bookingpress_final_payable_amount = isset( $bookingpress_return_data['payable_amount'] ) ? ($bookingpress_return_data['payable_amount']) : 0;
                $bookingpress_final_payable_amount = (int)$bookingpress_final_payable_amount;
                $customer_details                  = $bookingpress_return_data['customer_details'];
                $customer_email                    = ! empty( $customer_details['customer_email'] ) ? $customer_details['customer_email'] : '';
                $customer_firstname = !empty($customer_details['customer_firstname']) ? $customer_details['customer_firstname'] : $customer_email;
                $customer_lastname = !empty($customer_details['customer_lastname']) ? $customer_details['customer_lastname'] : $customer_email;

                $bookingpress_service_name = ! empty( $bookingpress_return_data['service_data']['bookingpress_service_name'] ) ? $bookingpress_return_data['service_data']['bookingpress_service_name'] : __( 'Appointment Booking', 'bookingpress-mercadopago' );

                $bookingpress_notify_url = $bookingpress_return_data['notify_url'];
                $redirect_url = $bookingpress_return_data['approved_appointment_url'];
                
                $bookingpress_appointment_status = $BookingPress->bookingpress_get_settings( 'appointment_status', 'general_setting' );
                if ( $bookingpress_appointment_status == '2' ) {
                    $redirect_url = $bookingpress_return_data['pending_appointment_url'];
                }

                $bookingpress_booked_service_name = ! empty( $bookingpress_return_data['service_data']['bookingpress_service_name'] ) ? $bookingpress_return_data['service_data']['bookingpress_service_name'] : __( 'Appointment Booking', 'bookingpress-mercadopago' );

                //$webhook_url = add_query_arg('bookingpress-listener', 'bpa_pro_mercadopago_url', $redirect_url);
                $webhook_url = $bookingpress_notify_url;


                $bookingpress_cancel_url = $bookingpress_return_data['canceled_appointment_url'];
                
                $bookingpress_create_preference_params = array();
                $bookingpress_create_preference_params['external_reference'] = 'ref-'.$entry_id;
                $bookingpress_create_preference_params['auto_return'] = "all";
                $bookingpress_create_preference_params['items'][] = array(
                    'title' => $bookingpress_booked_service_name,
                    'quantity' => 1,
                    'currency_id' => $currency_code,
                    "unit_price" => (float)$bookingpress_final_payable_amount,
                );
                $bookingpress_create_preference_params['notification_url'] = $webhook_url;
                $bookingpress_create_preference_params['payer'] = array(
                    'name' => $customer_firstname,
                    'surname' => $customer_lastname,
                    'email' => $customer_email,
                );
                $bookingpress_create_preference_params['back_urls'] = array(
                    'success' => $redirect_url
                ); 
                $bookingpress_create_preference_params['metadata'] = array(
                    'entry_id' => $entry_id,
                    'bookingpress_is_cart' => $bookingpress_is_cart,
                );
                $bookingpress_create_preference_header_params = array(
                    'Authorization' => 'Bearer '.$this->bookingpress_mercadopago_access_token,
                );
                
                $bookingpress_create_preference_body_params = array(
                    'method' => 'POST',
                    'body' => json_encode($bookingpress_create_preference_params),
                    'headers' => $bookingpress_create_preference_header_params,
                    'timeout' => 5000,
                );
                
                $bookingpress_create_preference_url = "https://api.mercadopago.com/checkout/preferences";
                $bookingpress_created_preference_response = wp_remote_request($bookingpress_create_preference_url, $bookingpress_create_preference_body_params);

                do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercadopago Create Preference Response', 'bookingpress pro', $bookingpress_created_preference_response, $bookingpress_debug_payment_log_id );

                if(!is_wp_error($bookingpress_created_preference_response))
                {
                    $bookingpress_created_preference_response_arr = json_decode($bookingpress_created_preference_response['body'], TRUE);

                    if(!empty($bookingpress_created_preference_response_arr['error']))
                    {
                        $err_msg = __('Error returned from payment gateway ', 'bookingpress-mercadopago');
                        $err_msg .= !empty($bookingpress_created_preference_response_arr['message']) ? $bookingpress_created_preference_response_arr['message'] : __('Something went wrong while processing with payment', 'bookingpress-mercadopago');
                        $response['variant']       = 'error';
                        $response['title']         = esc_html__( 'Error', 'bookingpress-mercadopago' );
                        $response['msg']           = $err_msg;
                        $response['is_redirect']   = 0;
                        $response['redirect_data'] = '';
                        $response['is_spam']       = 0;

                    }
                    else
                    {
                        $bookingpress_checkout_url = ( $this->bookingpress_selected_payment_method=='sandbox') ? $bookingpress_created_preference_response_arr['sandbox_init_point'] : $bookingpress_created_preference_response_arr['init_point'];
                        $redirect = '<script data-cfasync="false" type="text/javascript" language="javascript">window.location.href="' . $bookingpress_checkout_url . '";</script>';

                        do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercadopago Checkout URL', 'bookingpress pro', $bookingpress_checkout_url, $bookingpress_debug_payment_log_id );
                       
                        $response['variant']       = 'redirect';
                        $response['title']         = '';
                        $response['msg']           = '';
                        $response['is_redirect']   = 1;
                        $response['redirect_data'] = $redirect;
                        $response['entry_id'] = $entry_id;
                    }
                }
                else
                {
                    $response['variant']       = 'error';
                    $response['title']         = esc_html__( 'Error', 'bookingpress-mercadopago' );
                    $response['msg']           = __('Something went wrong while processing with payment', 'bookingpress-mercadopago');
                    $response['is_redirect']   = 0;
                    $response['redirect_data'] = '';
                    $response['is_spam']       = 0;
                }
            }
            return $response;
        }

        function bookingpress_payment_gateway_data(){
            global $wpdb, $BookingPress, $bookingpress_pro_payment_gateways, $bookingpress_debug_payment_log_id;
            if ( ! empty( $_REQUEST['bookingpress-listener'] ) && ($_REQUEST['bookingpress-listener'] == "bpa_pro_mercadopago_url") ) {
                $this->bookingpress_init_mercadopago();

                $bookingpress_webhook_data = $_POST; //phpcs:ignore
                do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercado Pago Webhook Data', 'bookingpress pro', $bookingpress_webhook_data, $bookingpress_debug_payment_log_id );

                //$json = file_get_contents('php://input');
                /*
                do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercado Pago file_get_contents Data', 'bookingpress pro', $json, $bookingpress_debug_payment_log_id );
                */

                $bookingpress_mercadopago_payment_type = isset($_REQUEST['type']) ? $_REQUEST['type'] : ''; //phpcs:ignore
                $bookingpress_mercadopago_payment_id = isset($_REQUEST['data_id']) ? $_REQUEST['data_id'] : ''; //phpcs:ignore
                $bookingpress_mercadopago_payment_signature = isset($_REQUEST['x-signature']) ? $_REQUEST['x-signature'] : ''; //phpcs:ignore
                if(!empty($bookingpress_mercadopago_payment_signature)){
                    $signature_arr = explode(',', $bookingpress_mercadopago_payment_signature);

                    $signature_timestamp_in_ms = isset($signature_arr['ts']) ? $signature_arr['ts'] : "";
                    $signature_encrypted_sign = isset($signature_arr['v1']) ? $signature_arr['v1'] : "";

                    //id:[data.id_url];request-id:[x-request-id_header];ts:[ts_header];

                    //$cyphedSignature = hash_hmac('sha256', $data, $key);


                }
                if($bookingpress_mercadopago_payment_type == "payment")
                {   
                    $bookingpress_payment_verification_url = "https://api.mercadopago.com/v1/payments/".$bookingpress_mercadopago_payment_id;
                    $bookingpress_payment_verify_header_params = array(
                        'Authorization' => 'Bearer '.$this->bookingpress_mercadopago_access_token,
                    );

                    $bookingpress_verified_payment_body_params = array(
                        'method' => 'GET',
                        'headers' => $bookingpress_payment_verify_header_params,
                        'timeout' => 5000,
                    );

                    do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercado Pago verfify payments params body Data', 'bookingpress pro', $bookingpress_verified_payment_body_params, $bookingpress_debug_payment_log_id );

                    $bookingpress_verified_payment_res = wp_remote_request($bookingpress_payment_verification_url, $bookingpress_verified_payment_body_params);

                    do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'MercadopagoPayment verified result', 'bookingpress pro', $bookingpress_verified_payment_res, $bookingpress_debug_payment_log_id );

                    if(!is_wp_error($bookingpress_verified_payment_res))
                    {
                        $bookingpress_verified_payment_res_arr = json_decode($bookingpress_verified_payment_res['body'], TRUE);
                        if(!empty($bookingpress_verified_payment_res_arr['status']) && ($bookingpress_verified_payment_res_arr['status'] == "approved"))
                        {
                            $bookingpress_external_ref_data = !empty($bookingpress_verified_payment_res_arr['external_reference']) ? explode('-', $bookingpress_verified_payment_res_arr['external_reference']) : array();

                            $bookingpress_payment_metadata = !empty($bookingpress_verified_payment_res_arr['metadata']) ? $bookingpress_verified_payment_res_arr['metadata'] : array();

                            if(!empty($bookingpress_payment_metadata) && !is_array($bookingpress_payment_metadata)){
                                $bookingpress_payment_metadata = json_decode($bookingpress_payment_metadata,true);
                            }

                            do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercadopago Payment metadata', 'bookingpress pro', $bookingpress_payment_metadata, $bookingpress_debug_payment_log_id );

                            if(!empty($bookingpress_payment_metadata)) {
                                $bookingpress_entry_id = isset($bookingpress_payment_metadata['entry_id']) ? $bookingpress_payment_metadata['entry_id'] : '';
                                $bookingpress_is_cart = isset($bookingpress_payment_metadata['bookingpress_is_cart']) ? $bookingpress_payment_metadata['bookingpress_is_cart'] : '';
                                if(!empty($bookingpress_entry_id)){
                                    do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercadopago entry id before confirm booking ', 'bookingpress pro', $bookingpress_entry_id, $bookingpress_debug_payment_log_id );

                                    $payment_log_id = $bookingpress_pro_payment_gateways->bookingpress_confirm_booking( $entry_id, $bookingpress_webhook_data, '1', 'id','transaction_amount', 1, $bookingpress_is_cart);
                                }
                            }
                        }
                    }
                }
                /* else if(!empty($_REQUEST['preapproval_id'])){

                    $bookingpress_preapproval_id = $_REQUEST['preapproval_id'];

                    do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'MercadopagoPayment Pre Approval ID', 'bookingpress pro', $bookingpress_preapproval_id, $bookingpress_debug_payment_log_id );

                    $bookingpress_payment_verification_url = "https://api.mercadopago.com/preapproval/".$bookingpress_preapproval_id;

                    $bookingpress_payment_verify_header_params = array(
                        'Authorization' => 'Bearer '.$this->bookingpress_mercadopago_access_token,
                    );
    
                    $bookingpress_verified_payment_body_params = array(
                        'method' => 'GET',
                        'headers' => $bookingpress_payment_verify_header_params,
                        'timeout' => 5000,
                    );
                } */
            }
        }

        function bookingpress_mercadopago_apply_refund_func($response,$bookingpress_refund_data) {
            global $bookingpress_debug_payment_log_id;

            $bookingpress_transaction_id = !empty($bookingpress_refund_data['bookingpress_transaction_id']) ? $bookingpress_refund_data['bookingpress_transaction_id'] :'';

            if(!empty($bookingpress_transaction_id ) && !empty($bookingpress_refund_data['refund_type'])) {                
                
                 $bookingpres_refund_type = $bookingpress_refund_data['refund_type'] ? $bookingpress_refund_data['refund_type'] : '';
                $bookingpress_refund_currency = $bookingpress_refund_data['bookingpress_payment_currency'] ? $bookingpress_refund_data['bookingpress_payment_currency'] : '';
                if($bookingpres_refund_type != 'full') {
                    $bookingpres_refund_amount = $bookingpress_refund_data['refund_amount'] ? $bookingpress_refund_data['refund_amount'] * 100 : 0;                    
                } else {
                    $bookingpres_refund_amount = $bookingpress_refund_data['default_refund_amount'] ? $bookingpress_refund_data['default_refund_amount'] * 100 : 0;                    
                }				

                try{                        
                    $this->bookingpress_init_mercadopago();

                    //https://api.mercadopago.com/v1/payments/{id}/refunds

                    //Create refund for mercado pago
                    $bookingpress_mercadopago_refund_url = "https://api.mercadopago.com/v1/payments/".$bookingpress_transaction_id."/refunds";					
                    $bookingpress_send_refund_data = array(
                        'headers' => [
                            'Authorization' => 'Bearer '.$this->bookingpress_mercadopago_access_token,
                            'content-type'  => 'application/json',
                        ],
                        'body'    => json_encode(array(
                            'amount' => (int) $bookingpres_refund_amount,
                        )),
                        'timeout' => 45,
                    );

                    do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercado Pago submited refund data', 'bookingpress pro', $bookingpress_send_refund_data, $bookingpress_debug_payment_log_id );

                    $bookingpress_create_refund_response = wp_remote_post($bookingpress_mercadopago_refund_url, $bookingpress_send_refund_data);															
                    $bookingpress_refund_details = json_decode(wp_remote_retrieve_body( $bookingpress_create_refund_response ), TRUE);                 

                    do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercadopago response of the refund', 'bookingpress pro', $bookingpress_create_refund_response, $bookingpress_debug_payment_log_id);						
					
                    if(!empty($bookingpress_refund_details['id'])) {
                        $response['title']   = esc_html__( 'Success', 'bookingpress-mercadopago' );
                        $response['variant'] = 'success';
                        $response['bookingpress_refund_response'] = !empty($bookingpress_create_refund_response) ? $bookingpress_create_refund_response : 
                        '';
                    } else {
                        $response['variant'] = 'error';
                        $response['title']   = esc_html__( 'Error', 'bookingpress-mercadopago' );
                        $response['msg'] = !empty( $bookingpress_refund_details['error'] ) ? $bookingpress_refund_details['error'] : esc_html__('Sorry! refund could not be processed', 'bookingpress-mercadopago');
                    }
               } catch (Exception $e){
                    $error_message = $e->getMessage();
                    do_action( 'bookingpress_payment_log_entry', 'mercadopago', 'Mercadopago refund resoponse with error', 'bookingpress pro', $error_message, $bookingpress_debug_payment_log_id);                    
                    $response['title']   = esc_html__( 'Error', 'bookingpress-mercadopago' );
                    $response['variant'] = 'error';
                    $response['msg'] = $error_message;
               }
            }            
            return 	$response;
		}
    }

    global $bookingpress_mercadopago_payment;
	$bookingpress_mercadopago_payment = new bookingpress_mercadopago_payment;
}

?>