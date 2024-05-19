<div class="bpa-gs__cb--item bpa-cb-item__language-row">
	<div class="bpa-gs__cb--item-heading">
		<el-row type="flex" align="middle">
			<el-col :xs="12" :sm="12" :md="12" :lg="16" :xl="16">	
				<h4 class="bpa-sec--sub-heading"><?php esc_html_e( 'Language Settings', 'bookingpress-multilanguage' ); ?></h4>
			</el-col>
			<el-col :xs="12" :sm="12" :md="12" :lg="8" :xl="8">	
				<el-form-item prop="appointment_status">	
					<el-select v-model="general_setting_form.bookingpress_selected_languages" multiple class="bpa-form-control" collapse-tags filterable popper-class="bpa-el-select--is-with-navbar">
						<el-option v-for="(lang, index) in bookingpress_all_language_list" :label="lang.english_name +' - '+lang.language+''" :value="index"></el-option>
					</el-select>
				</el-form-item>
			</el-col>
		</el-row>							
	</div>
	<div class="bpa-lr__body-items">
		<div class="bpa-lr-body-item-wrap bpa-multi-lang-sec">					
		    <div class="bpa-lr__item" v-if="general_setting_form.bookingpress_selected_languages != '' && typeof general_setting_form.bookingpress_selected_languages != 'undefined' && typeof bookingpress_all_language_list[lang_ind] != 'undefined'" v-for="lang_ind in general_setting_form.bookingpress_selected_languages"> <img class="bpa-setting-lng-flg" v-if="bookingpress_all_language_list[lang_ind].flag_image != ''" :src="bookingpress_all_language_list[lang_ind].flag_image" :alt="bookingpress_all_language_list[lang_ind].english_name">{{bookingpress_all_language_list[lang_ind].english_name +' - '+bookingpress_all_language_list[lang_ind].language}} <span class="material-icons-round" @click="bookingpress_removed_selected_language(lang_ind)">close</span></div>			
		</div>
	</div>	
</div>						