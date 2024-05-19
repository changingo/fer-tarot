<!-- Customize Custom Fields Language Translate Start -->
<el-drawer custom-class="bpa-drawer__language-translate" :visible.sync="open_customize_custom_fields_translate_language">
	<div class="bpa-dlt__heading">
		<h3><?php esc_html_e( 'Language Translate', 'bookingpress-multilanguage' ); ?></h3>
	</div>
	<div class="bpa-dlt__body">
		<div v-if="empty_selected_language == 0" class="bpa-dlt-language-items">
			<div @click="change_customize_current_language(field_language_ind)" v-for="(select_lang, field_language_ind) in bookingpress_get_selected_languages" :class="(bookingpress_current_selected_lang == field_language_ind)?'__bpa-is-active':''" class="bpa-li__item">				
				<img v-if="select_lang.flag_image != ''" :src="select_lang.flag_image" :alt="select_lang.english_name">
				<p>{{select_lang.english_name}}</p> 				
			</div>
		</div>
		<div class="bpa-dlt-body-module-wrapper">
			<div v-if="empty_selected_language == 0" v-for="(field_language, field_language_ind) in bookingpress_get_selected_languages">
					<div v-if="typeof custom_form_fields_language_data !== 'undefined' && bookingpress_current_selected_lang == field_language_ind" v-for="(lang_form_fields, lang_field_key) in custom_form_fields_language_data[field_language_ind]" class="bpa-bmw__block">
						<div class="bpa-mw__title">							
							<h4 v-html="(typeof bookingpress_customize_custom_form_fields_language_section_title != 'undefined' && typeof bookingpress_customize_custom_form_fields_language_section_title[lang_field_key] !== 'undefined')?bookingpress_customize_custom_form_fields_language_section_title[lang_field_key]:''"></h4>
						</div>
						<el-form ref="" label-position="top">
							<div class="bpa-mw__form">
								<template v-for="(lang_fields, lang_field_id) in lang_form_fields">										
									<el-form-item v-for="(lang_field_data, lang_field_data_key) in lang_fields">
										<template #label>
											<span class="bpa-form-label">{{lang_field_data.field_label}}</span>
										</template>
										<el-input v-if="lang_field_data_key != 'bookingpress_field_values'" class="bpa-form-control"  v-model="custom_form_language_data[field_language_ind][lang_field_id][lang_field_data_key][lang_field_key]" :type="(lang_field_data.field_type == 'text')?'text':'textarea'" :rows="(lang_field_data.field_type == 'text')?1:5">
										</el-input>	
										<div class="bpa-custom-field-option-add">
											<div v-if="lang_field_data_key == 'bookingpress_field_values'">
												<el-row type="flex" class="bpa-field-values-row-with-border-1">
													<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16" class="bpa-cs__heading bpa-cs__option-label">
														<?php echo esc_html__('Value', 'bookingpress-multilanguage'); ?>
													</el-col>
													<el-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16" class="bpa-cs__heading bpa-cs__option-label">
														<?php echo esc_html__('Label', 'bookingpress-multilanguage'); ?>
													<el-col>
												</el-row>
												<div v-if="typeof lang_field_data.bookingpress_field_values != 'undefined' && lang_field_data.bookingpress_field_values.length > 1" v-for="(optiondata, option_ind) in lang_field_data.bookingpress_field_values">
													<el-row type="flex" class="">
														<el-col v-for="(optiond, optiondind) in optiondata" :xs="16" :sm="16" :md="16" :lg="16" :xl="16" class="bpa-cs__heading bpa-cs__option-row">														
															<el-input class="bpa-form-control bpa-form-lang-option-inp"  v-model="custom_form_language_data[field_language_ind][lang_field_id][lang_field_data_key][lang_field_key][option_ind][optiondind]" type="text" rows="1">
															</el-input>
														</el-col>																											
													</el-row>																						
												</div>	
											</div>												
										</div>							
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
		<el-button @click="open_customize_custom_fields_translate_language = false;" class="bpa-btn bpa-btn--primary"><?php esc_html_e( 'Okay', 'bookingpress-multilanguage' ); ?></el-button>
	</div> 				
</el-drawer>
<!-- Customize Custom Fields Language Translate Over -->