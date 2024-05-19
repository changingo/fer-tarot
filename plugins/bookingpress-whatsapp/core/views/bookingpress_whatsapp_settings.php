<div class="bpa-gs__cb--item">
    <div class="bpa-gs__cb--item-heading">
        <h4 class="bpa-sec--sub-heading"><?php esc_html_e('Whatsapp Settings', 'bookingpress-whatsapp'); ?></h4>
    </div>    
    <el-row class="bpa-gs--tabs-pb__cb-item-row">
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="bookingpress_whatsapp_err_msg != ''">
            <span class="bpa-whatsapp-error-msg">{{ bookingpress_whatsapp_err_msg }}</span>
        </el-col> 
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24" v-if="bookingpress_whatsapp_success_msg != ''">
            <span class="bpa-whatsapp-success-msg">{{ bookingpress_whatsapp_success_msg }}</span>
        </el-col>
    </el-row>
    <el-row class="bpa-gs--tabs-pb__cb-item-row">
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <div class="bpa-gs__cb--item-heading">
                <h4 class="bpa-sec--sub-heading __bpa-sec--sub-heading-no-stroke __bpa-is-gs-heading-mb-0"><?php esc_html_e( 'API Configuration', 'bookingpress-whatsapp' ); ?></h4>
            </div>
        </el-col>
    </el-row>     
    <el-row class="bpa-gs--tabs-pb__cb-item-row">
        <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
            <h4><?php esc_html_e( 'Select WhatsApp Gateway', 'bookingpress-whatsapp' ); ?></h4>
        </el-col> 
        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
            <el-form-item prop="bookingpress_selected_whatsapp_gateway">
                <el-select class="bpa-form-control" @change="bookingpress_reset_setting_validate" v-model="notification_setting_form.bookingpress_selected_whatsapp_gateway" filterable placeholder="<?php esc_html_e('Select WhatsApp Gateway', 'bookingpress-whatsapp'); ?>">
                    <el-option key="select_whatsapp_gateway" value="select_whatsapp_gateway" label="<?php esc_html_e('Select WhatsApp Gateway', 'bookingpress-whatsapp'); ?>"></el-option>
                    <el-option v-for="item in bookingpress_whatsapp_gateways" :key="item.name" :label="item.name" :value="item.name"></el-option>
                </el-select>                 
            </el-form-item>
        </el-col>
    </el-row>
    <el-row class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.bookingpress_selected_whatsapp_gateway === 'Whatsapp Business'">
        <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
            <h4><?php esc_html_e( 'Phone Number ID', 'bookingpress-whatsapp' ); ?></h4>
        </el-col> 
        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
            <el-form-item prop="whatsapp_phone_number_id">
                <el-input class="bpa-form-control" v-model="notification_setting_form.whatsapp_phone_number_id" placeholder="<?php esc_html_e( 'Enter Whatsapp Business Phone Number ID', 'bookingpress-whatsapp') ?>" id="whatsapp_phone_number_id"></el-input>
            </el-form-item>
        </el-col>
    </el-row>
    <el-row class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.bookingpress_selected_whatsapp_gateway === 'Whatsapp Business'">
        <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
            <h4><?php esc_html_e( 'Business Account ID', 'bookingpress-whatsapp' ); ?></h4>
        </el-col> 
        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
            <el-form-item prop="whatsapp_business_account_id">
                <el-input class="bpa-form-control" v-model="notification_setting_form.whatsapp_business_account_id" placeholder="<?php esc_html_e( 'Enter Whatsapp Business Account ID', 'bookingpress-whatsapp') ?>" id="whatsapp_business_account_id"></el-input>
            </el-form-item>
        </el-col>
    </el-row>
    <el-row class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.bookingpress_selected_whatsapp_gateway === 'Whatsapp Business'">
        <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
            <h4><?php esc_html_e( 'Permanent Access Token', 'bookingpress-whatsapp' ); ?></h4>
        </el-col> 
        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
            <el-form-item prop="whatsapp_business_access_token">
                <el-input class="bpa-form-control" v-model="notification_setting_form.whatsapp_business_access_token" placeholder="<?php esc_html_e( 'Enter Pemanent Access Token', 'bookingpress-whatsapp') ?>" id="whatsapp_business_access_token"></el-input>
            </el-form-item>
        </el-col>
    </el-row>
    <el-row class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.bookingpress_whatsapp_show_freeform_msg == true && notification_setting_form.bookingpress_selected_whatsapp_gateway === 'Twilio'">
        <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
            <h4><?php esc_html_e( 'Message Type', 'bookingpress-whatsapp' ); ?></h4>
        </el-col> 
        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-left">
            <el-form-item prop="bookingpress_whatsapp_twilio_msg_type">
                <el-radio v-model="notification_setting_form.bookingpress_whatsapp_twilio_msg_type" @change="bookingpress_reset_twilio_service_from_number" label="template"><?php esc_html_e('WhatsApp Template', 'bookingpress-whatsapp'); ?></el-radio>
                <el-radio v-model="notification_setting_form.bookingpress_whatsapp_twilio_msg_type" @change="bookingpress_reset_twilio_service_from_number" label="freeform"><?php esc_html_e('Free Form Message', 'bookingpress-whatsapp'); ?></el-radio>
            </el-form-item>
        </el-col>
    </el-row>
    <el-row class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.bookingpress_selected_whatsapp_gateway === 'Twilio'">
        <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
            <h4><?php esc_html_e( 'Account SID', 'bookingpress-whatsapp' ); ?></h4>
        </el-col> 
        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
            <el-form-item prop="whatsapp_twilio_account_sid">
                <el-input class="bpa-form-control" v-model="notification_setting_form.whatsapp_twilio_account_sid" placeholder="<?php esc_html_e( 'Account SID', 'bookingpress-whatsapp' ); ?>" id="whatsapp_twillo_account_sid"></el-input>                       
            </el-form-item>
        </el-col>
    </el-row>        
    <el-row class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.bookingpress_selected_whatsapp_gateway === 'Twilio'">
        <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
            <h4><?php esc_html_e( 'Auth Token', 'bookingpress-whatsapp' ); ?></h4>
        </el-col> 
        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
            <el-form-item prop="whatsapp_twilio_auth_token">
                <el-input class="bpa-form-control" v-model="notification_setting_form.whatsapp_twilio_auth_token" placeholder="<?php esc_html_e( 'Auth Token', 'bookingpress-whatsapp' ); ?>" ></el-input>                       
            </el-form-item>
        </el-col>
    </el-row>        
    <el-row class="bpa-gs--tabs-pb__cb-item-row" v-if="notification_setting_form.bookingpress_selected_whatsapp_gateway === 'Twilio'">
        <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
            <h4 v-if="'freeform' == notification_setting_form.bookingpress_whatsapp_twilio_msg_type"><?php esc_html_e( 'From Number', 'bookingpress-whatsapp' ); ?></h4>
            <h4 v-else><?php esc_html_e('Service ID', 'bookingpress-whatsapp'); ?></h4>
        </el-col> 
        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
            <el-form-item prop="whatsapp_twilio_from_number">
                <el-input v-if="'freeform' == notification_setting_form.bookingpress_whatsapp_twilio_msg_type" class="bpa-form-control" v-model="notification_setting_form.whatsapp_twilio_from_number" placeholder="<?php esc_html_e( 'From Number', 'bookingpress-whatsapp' ); ?>" ></el-input>
                <el-input v-else class="bpa-form-control" v-model="notification_setting_form.whatsapp_twilio_from_number" placeholder="<?php esc_html_e( 'Service ID', 'bookingpress-whatsapp' ); ?>" ></el-input>
            </el-form-item>
        </el-col>
    </el-row> 
    <el-row class="bpa-gs--tabs-pb__cb-item-row" >
        <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
            <h4><?php esc_html_e( 'Select Mobile Number Field', 'bookingpress-whatsapp' ); ?></h4>
        </el-col> 
        <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
            <el-form-item prop="bookingpress_whatsapp_selected_phone_number_field">
                <el-select class="bpa-form-control" v-model="notification_setting_form.bookingpress_whatsapp_selected_phone_number_field" filterable placeholder="<?php esc_html_e('Select Mobile Number Field', 'bookingpress-whatsapp'); ?>">
                    <el-option v-for="item in bookingpress_whatsapp_form_fields_data" :key="item.bookingpress_field_label" :label="item.bookingpress_field_label" :value="item.bookingpress_field_meta_key"></el-option>
                </el-select>
            </el-form-item>
        </el-col>
    </el-row>
    <el-form :model="bookingpress_test_whatsapp_form" v-if="'freeform' == notification_setting_form.bookingpress_whatsapp_twilio_msg_type && notification_setting_form.bookingpress_selected_whatsapp_gateway === 'Twilio'" :rules="bookingpress_test_whatsapp_rules" ref="bookingpress_test_whatsapp_form">
        <el-row class="bpa-gs--tabs-pb__cb-item-row">
            <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
                <div class="bpa-gs__cb--item-heading">
                    <h4 class="bpa-sec--sub-heading __bpa-sec--sub-heading-no-stroke __bpa-is-gs-heading-mb-0"><?php esc_html_e( 'WhatsApp Testing', 'bookingpress-whatsapp' ); ?></h4>
                </div>
            </el-col>
        </el-row>
        <el-row class="bpa-gs--tabs-pb__cb-item-row" >
            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                <h4><?php esc_html_e( 'To Number', 'bookingpress-whatsapp' ); ?></h4>
            </el-col> 
            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                <el-form-item prop="whatsapp_test_to_number">
                    <el-input class="bpa-form-control" v-model="bookingpress_test_whatsapp_form.whatsapp_test_to_number" placeholder="<?php esc_html_e( 'To Number', 'bookingpress-whatsapp' ); ?>" ></el-input>                       
                </el-form-item>
            </el-col>
        </el-row> 
        <el-row class="bpa-gs--tabs-pb__cb-item-row" >
            <el-col :xs="12" :sm="12" :md="12" :lg="08" :xl="08" class="bpa-gs__cb-item-left">
                <h4><?php esc_html_e( 'Enter Message', 'bookingpress-whatsapp' ); ?></h4>
            </el-col> 
            <el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16" class="bpa-gs__cb-item-right">
                <el-form-item prop="whatsapp_test_to_msg">
                <el-input v-model="bookingpress_test_whatsapp_form.whatsapp_test_to_msg" class="bpa-form-control" type="textarea"></el-input>
                </el-form-item>
            </el-col>
        </el-row> 
        <el-row class="bpa-gs--tabs-pb__cb-item-row">
            <el-col :xs="{span: 12, offset: 12}" :sm="{span: 12, offset: 12}" :md="{span: 12, offset: 12}" :lg="{span: 16, offset: 8}" :xl="{span: 16, offset: 8}">
                <el-button class="bpa-btn bpa-btn__medium bpa-btn--primary el-button--default" :class="(is_display_send_whatsapp_loader == '1') ? 'bpa-btn--is-loader' : ''" @click="bookingpress_send_test_whatsapp()">
                    <span class="bpa-btn__label"><?php esc_html_e( 'Send Test WhatsApp', 'bookingpress-whatsapp ' ); ?></span>
                    <div class="bpa-btn--loader__circles">				    
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </el-button>
            </el-col>
        </el-row> 
    </el-form>
</div>