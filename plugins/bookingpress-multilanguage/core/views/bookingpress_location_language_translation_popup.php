<!-- Service -->
<el-drawer custom-class="bpa-drawer__language-translate" :visible.sync="open_location_translate_language">
	<div class="bpa-dlt__heading">
		<h3><?php esc_html_e( 'Language Translate', 'bookingpress-multilanguage' ); ?></h3>
	</div>
	<div class="bpa-dlt__body">
		<div v-if="empty_selected_language == 0" class="bpa-dlt-language-items">

			<div @click="change_multilanguage_current_language(field_language_ind)" v-for="(select_lang, field_language_ind) in bookingpress_get_selected_languages" :class="(bookingpress_current_selected_lang == field_language_ind)?'__bpa-is-active':''" class="bpa-li__item">
				<img v-if="select_lang.flag_image != ''" :src="select_lang.flag_image" :alt="select_lang.english_name">
				<p>{{select_lang.english_name}}</p> 
			</div>

		</div>				
		<div class="bpa-dlt-body-module-wrapper">
			<div v-if="empty_selected_language == 0" v-for="(field_language, field_language_ind) in bookingpress_get_selected_languages">
				<div v-if="(typeof language_fields_data !== 'undefined' && bookingpress_current_selected_lang == field_language_ind && typeof language_fields_data[field_language_ind] !== 'undefined') " v-for="(lang_fields, lang_field_key) in language_fields_data[field_language_ind]" class="bpa-bmw__block">
					<div class="bpa-mw__title">
						<h4 v-html="(typeof bookingpress_location_language_section_title !== 'undefined' && typeof bookingpress_location_language_section_title[lang_field_key] !== 'undefined')?bookingpress_location_language_section_title[lang_field_key]:''"></h4>
					</div>
					<el-form ref="" label-position="top">
						<template>
							<div class="bpa-mw__form">
								<el-form-item v-for="(lang_field_data, lang_field_data_key) in lang_fields">
									<template #label>
										<span class="bpa-form-label">{{lang_field_data.field_label}}</span>
									</template>
									<el-input class="bpa-form-control" v-model="language_data[field_language_ind][lang_field_key][lang_field_data_key]" :type="(lang_field_data.field_type == 'text')?'text':'textarea'" :rows="(lang_field_data.field_type == 'text')?1:5"  :placeholder="lang_field_data.field_label">
									</el-input>								
								</el-form-item>
							</div>
						</template>
					</el-form>
				</div>
			</div>
			<?php do_action('bookingpress_multi_language_popup_translate_language_not_found'); ?>
		</div>
	</div>
	<div class="bpa-dlt__footer">
		<el-button @click="open_location_translate_language = false;" class="bpa-btn bpa-btn--primary"><?php esc_html_e( 'Okay', 'bookingpress-multilanguage' ); ?></el-button>
	</div> 
</el-drawer>
