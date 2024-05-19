<!-- Service -->
<el-drawer custom-class="bpa-drawer__language-translate" :visible.sync="open_service_translate_language">
	<div class="bpa-dlt__heading">
		<h3><?php esc_html_e( 'Language Translate', 'bookingpress-multilanguage' ); ?></h3>
	</div>
	<div class="bpa-dlt__body">
		<div v-if="empty_selected_language == 0" class="bpa-dlt-language-items">

			<div @click="change_service_current_language(field_language_ind)" v-for="(select_lang, field_language_ind) in bookingpress_get_selected_languages" :class="(bookingpress_current_selected_lang == field_language_ind)?'__bpa-is-active':''" class="bpa-li__item">
				<img v-if="select_lang.flag_image != ''" :src="select_lang.flag_image" :alt="select_lang.english_name">
				<p>{{select_lang.english_name}}</p> 
			</div>

		</div>				
		<div class="bpa-dlt-body-module-wrapper">

			<div v-if="empty_selected_language == 0" v-for="(field_language, field_language_ind) in bookingpress_get_selected_languages">
				<div v-if="bookingpress_current_selected_lang == field_language_ind" v-for="(lang_fields, lang_field_key) in language_fields_data[field_language_ind]" class="bpa-bmw__block">
					<div class="bpa-mw__title">
						<h4 v-html="(typeof bookingpress_service_language_section_title[lang_field_key] !== 'undefined')?bookingpress_service_language_section_title[lang_field_key]:''"></h4>
					</div>
					<el-form ref="" label-position="top">
						<template>
							<div class="bpa-mw__form">
								<el-form-item v-for="(lang_field_data, lang_field_data_key) in lang_fields">
									<template #label>
										<span class="bpa-form-label">{{lang_field_data.field_label}}</span>
									</template>
									<el-input class="bpa-form-control"  v-model="service.language_data[field_language_ind][lang_field_key][lang_field_data_key]" :type="(lang_field_data.field_type == 'text')?'text':'textarea'" :rows="(lang_field_data.field_type == 'text')?1:5"  :placeholder="lang_field_data.field_label">
									</el-input>								
								</el-form-item>
							</div>
						</template>
					</el-form>
				</div>
				<div v-if="is_service_extra_module_activated == 1 && typeof service.extraServicesData !== 'undefined' && service.extraServicesData.length != 0">
					<div v-if="bookingpress_current_selected_lang == field_language_ind" v-for="(extra_service_data,index) in service.extraServicesData" class="bpa-bmw__block">
						<div class="bpa-mw__title">							
							<h4 v-html="extra_service_data.extra_service_titles"></h4>
						</div>
						<el-form ref="" label-position="top">
							<template>
								<div class="bpa-mw__form">
									
									<el-form-item v-for="(lang_field_data, lang_field_data_key) in bookingpress_service_extra_language_translate_fields">
										<template #label>
											<span class="bpa-form-label">{{lang_field_data.field_label}}</span>
										</template>
										<el-input v-model="service.service_extra_language_data[field_language_ind]['service_extra'][lang_field_data_key][index]" class="bpa-form-control" :type="(lang_field_data.field_type == 'text')?'text':'textarea'" :rows="(lang_field_data.field_type == 'text')?1:5"  :placeholder="lang_field_data.field_label">
										</el-input>								
									</el-form-item>

								</div>
							</template>
						</el-form>
					</div>
				</div>	
			</div>
			<?php do_action('bookingpress_multi_language_popup_translate_language_not_found'); ?>

		</div>
	</div>
	<div class="bpa-dlt__footer">
		<el-button @click="open_service_translate_language = false;" class="bpa-btn bpa-btn--primary"><?php esc_html_e( 'Okay', 'bookingpress-multilanguage' ); ?></el-button>
	</div> 
</el-drawer>

<!-- Category -->
<el-drawer custom-class="bpa-drawer__language-translate" :visible.sync="open_category_translate_language">
	<div class="bpa-dlt__heading">
		<h3><?php esc_html_e( 'Category Language Translate', 'bookingpress-multilanguage' ); ?></h3>
	</div>
	<div class="bpa-dlt__body">
		<div v-if="empty_selected_language == 0 && category_items.length > 1" class="bpa-dlt-language-items">
			<div @click="change_category_current_language(field_language_ind)" v-for="(select_lang, field_language_ind) in bookingpress_get_selected_languages" :class="(bookingpress_current_selected_cat_lang == field_language_ind)?'__bpa-is-active':''" class="bpa-li__item">
				<img v-if="select_lang.flag_image != ''" :src="select_lang.flag_image" :alt="select_lang.english_name">
				<p>{{select_lang.english_name}}</p> 
			</div>
		</div>				
		<div class="bpa-dlt-body-module-wrapper">
			<div v-if="empty_selected_language == 0" v-for="(field_language, field_language_ind) in bookingpress_get_selected_languages">
				<div v-if="bookingpress_current_selected_cat_lang == field_language_ind && 'undefined' != typeof category_language_fields_data && 'undefined' != typeof category_language_fields_data[field_language_ind]" v-for="(lang_category, lang_field_key) in category_language_fields_data[field_language_ind]" class="bpa-bmw__block">					
					<el-form ref="" label-position="top">
						<div class="bpa-mw__form">
							<template v-for="(lang_fields, lang_category_id) in lang_category">
								<el-form-item v-for="(lang_field_data, lang_field_data_key) in lang_fields">
									<template #label>
										<span class="bpa-form-label">{{lang_field_data.bookingpress_category_name}}</span>
									</template>
									<el-input class="bpa-form-control"  v-model="category_language[field_language_ind][lang_field_key][lang_field_data_key][lang_category_id]" :type="(lang_field_data.field_type == 'text')?'text':'textarea'" :rows="(lang_field_data.field_type == 'text')?1:5">
									</el-input>								
								</el-form-item>
							</template>									
						</div>
					</el-form>
				</div>
			</div>	
			<?php do_action('bookingpress_multi_language_popup_translate_language_not_found'); ?>
		</div>
	</div>
	<div class="bpa-dlt__footer">
	<el-button @click="open_category_translate_language = false;" class="bpa-btn el-button--default"><?php esc_html_e( 'Close', 'bookingpress-multilanguage' ); ?></el-button>	
	<el-button :class="(is_display_category_save_loader == '1') ? 'bpa-btn--is-loader' : ''" class="bpa-btn bpa-btn--primary" @click="save_category_language_data()"  v-if="empty_selected_language != 1 && category_items.length > 1">                    
		<span class="bpa-btn__label"><?php esc_html_e('Save', 'bookingpress-multilanguage'); ?></span>
		<div class="bpa-btn--loader__circles">                    
			<div></div>
			<div></div>
			<div></div>
		</div>
	</el-button>
	</div> 
</el-drawer>