<?php
namespace uncanny_learndash_reporting;
?>
<div class="uo-tclr-admin wrap" id="snc_options">
	<?php

	// Add admin header and tabs
	$tab_active = 'snc_options';
	include Config::get_template( 'admin-header.php' );

	?>

	<div class="tclr__admin-content">
		<form enctype="multipart/form-data" id="snc_options_form" method="POST">
			<input type="hidden" name="security" value="<?php echo wp_create_nonce( "snc-options" ) ?>"/>

			<!-- Reports settings -->
			<div class="uo-admin-section">
				<div class="uo-admin-header">
					<div class="uo-admin-title"><?php _e( 'Reports', 'uncanny-learndash-reporting' ); ?></div>
				</div>
				<div class="uo-admin-block">
					<div class="uo-admin-form">

						<!-- Disable wp-admin dashboard widget -->
						<div class="uo-admin-field">
							<div
								class="uo-admin-label"><?php _e( 'Disable wp-admin dashboard widget', 'uncanny-learndash-reporting' ); ?></div>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['disableDashWidget'] ) && self::$OPTION['disableDashWidget'] === '1' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="disableDashWidget" value="1">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'Yes', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['disableDashWidget'] ) && self::$OPTION['disableDashWidget'] === '0' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="disableDashWidget" value="0">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'No', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<div
								class="uo-admin-description"><?php _e( 'This settings allows the wp-admin dashboard(admin home screen) reporting widget to be disabled.', 'uncanny-learndash-reporting' ); ?></div>
						</div>

						<!-- Suppress loading page rows. Load all rows. -->
						<div class="uo-admin-field">
							<div
								class="uo-admin-label"><?php _e( 'Enable sorting by % complete', 'uncanny-learndash-reporting' ); ?></div>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['disablePerformanceEnhancments'] ) && self::$OPTION['disablePerformanceEnhancments'] === '1' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="disablePerformanceEnhancments" value="1">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'Yes', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['disablePerformanceEnhancments'] ) && self::$OPTION['disablePerformanceEnhancments'] === '0' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="disablePerformanceEnhancments" value="0">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'No', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<div
								class="uo-admin-description"><?php _e( 'The ability to sort by % Complete requires a very large amount of data be requested from the server, which will fail or cause poor performance on sites with many users. Disable this setting to improve the responsiveness of the reports.', 'uncanny-learndash-reporting' ); ?></div>
						</div>

						<!-- Enable reporting on front-end. -->
						<div class="uo-admin-field">
							<div
								class="uo-admin-label"><?php _e( 'Enable Tin Can Report on front end', 'uncanny-learndash-reporting' ); ?></div>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['enableTinCanReportFrontEnd'] ) && self::$OPTION['enableTinCanReportFrontEnd'] === '1' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="enableTinCanReportFrontEnd" value="1">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'Yes', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['enableTinCanReportFrontEnd'] ) && self::$OPTION['enableTinCanReportFrontEnd'] === '0' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="enableTinCanReportFrontEnd" value="0">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'No', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<div
								class="uo-admin-description"><?php _e( 'This setting enables the Tin Can Report tab when using the [tincanny] shortcode to display reports.', 'uncanny-learndash-reporting' ); ?></div>
						</div>

						<!-- Enable xAPI reporting on front-end. -->
						<div class="uo-admin-field">
							<div
								class="uo-admin-label"><?php _e( 'Enable xAPI Quiz Report on front end', 'uncanny-learndash-reporting' ); ?></div>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['enablexapiReportFrontEnd'] ) && self::$OPTION['enablexapiReportFrontEnd'] === '1' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="enablexapiReportFrontEnd" value="1">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'Yes', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['enablexapiReportFrontEnd'] ) && self::$OPTION['enablexapiReportFrontEnd'] === '0' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="enablexapiReportFrontEnd" value="0">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'No', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<div
								class="uo-admin-description"><?php _e( 'This setting enables the xAPI Quiz Report tab when using the [tincanny] shortcode to display reports.', 'uncanny-learndash-reporting' ); ?></div>

						</div>

						<!-- Select which user identifier(s) are shown in reports -->
						<div class="uo-admin-field">
							<div
								class="uo-admin-label"><?php _e( 'User identifier(s)', 'uncanny-learndash-reporting' ); ?></div>

							<label class="uo-checkbox">
								<input <?php if ( isset( self::$OPTION['userIdentifierDisplayName'] ) && self::$OPTION['userIdentifierDisplayName'] === '1' ) {
									echo ' checked="checked"';
								} ?> type="checkbox" name="userIdentifierDisplayName" value="1">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'Display Name', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-checkbox">
								<input <?php if ( isset( self::$OPTION['userIdentifierFirstName'] ) && self::$OPTION['userIdentifierFirstName'] === '1' ) {
									echo ' checked="checked"';
								} ?> type="checkbox" name="userIdentifierFirstName" value="1">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'First Name', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-checkbox">
								<input <?php if ( isset( self::$OPTION['userIdentifierLastName'] ) && self::$OPTION['userIdentifierLastName'] === '1' ) {
									echo ' checked="checked"';
								} ?> type="checkbox" name="userIdentifierLastName" value="1">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'Last Name', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-checkbox">
								<input <?php if ( isset( self::$OPTION['userIdentifierUsername'] ) && self::$OPTION['userIdentifierUsername'] === '1' ) {
									echo ' checked="checked"';
								} ?> type="checkbox" name="userIdentifierUsername" value="1">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'Username', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-checkbox">
								<input <?php if ( isset( self::$OPTION['userIdentifierEmail'] ) && self::$OPTION['userIdentifierEmail'] === '1' ) {
									echo ' checked="checked"';
								} ?> type="checkbox" name="userIdentifierEmail" value="1">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'Email Address', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<div
								class="uo-admin-description"><?php _e( 'These options will show or hide columns in the user list of the Course report and the User reports.', 'uncanny-learndash-reporting' ); ?></div>
						</div>

						<!-- Submit -->
						<div class="uo-admin-field uo-admin-extra-space">
							<input type="submit" name="submit" id="submit" class="uo-admin-form-submit"
								   value="<?php _e( 'Save Changes', 'uncanny-learndash-reporting' ); ?>">
						</div>
					</div>
				</div>
			</div>

			<!-- Settings -->
			<div class="uo-admin-section">
				<div class="uo-admin-header">
					<div class="uo-admin-title"><?php _e( 'Tin Can/SCORM', 'uncanny-learndash-reporting' ); ?></div>
				</div>
				<div class="uo-admin-block">
					<div class="uo-admin-form">

						<!-- Do you want to capture Tin Can Data? -->
						<div class="uo-admin-field">
							<div
								class="uo-admin-label"><?php _e( 'Capture Tin Can and SCORM data', 'uncanny-learndash-reporting' ); ?></div>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['tinCanActivation'] ) && self::$OPTION['tinCanActivation'] === '1' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="tinCanActivation" value="1">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'Yes', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['tinCanActivation'] ) && self::$OPTION['tinCanActivation'] === '0' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="tinCanActivation" value="0">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'No', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<div
								class="uo-admin-description"><?php _e( 'If you are uploading modules from a supported authoring tool (e.g. Storyline, Captivate) and want to capture data, turn this on.', 'uncanny-learndash-reporting' ); ?></div>
						</div>


						<!-- Protect SCORM/Tin Can Modules? -->
						<div class="uo-admin-field">
							<div
								class="uo-admin-label"><?php _e( 'Protect SCORM/Tin Can modules', 'uncanny-learndash-reporting' ); ?></div>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['nonceProtection'] ) && self::$OPTION['nonceProtection'] === '1' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="nonceProtection" value="1">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'Yes', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['nonceProtection'] ) && self::$OPTION['nonceProtection'] === '0' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="nonceProtection" value="0">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'No', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<div
								class="uo-admin-description"><?php _e( 'This setting adds a layer of basic protection to your modules to discourage users from attempting to access them directly (outside of your WordPress site). Disable this if you are experiencing module loading issues.', 'uncanny-learndash-reporting' ); ?>
								<strong><?php _e( 'This feature is only supported on hosts that support mod_rewrite.', 'uncanny-learndash-reporting' ); ?></strong>
							</div>
						</div>

						<!-- Submit -->
						<div class="uo-admin-field uo-admin-extra-space">
							<input type="submit" name="submit" id="submit" class="uo-admin-form-submit"
								   value="<?php _e( 'Save Changes', 'uncanny-learndash-reporting' ); ?>">
						</div>
					</div>
				</div>
			</div>

			<!-- MARK COMPLETE BUTTON -->
			<div class="uo-admin-section" id="mark_complete_button_box" style="<?php if ( isset( self::$OPTION['tinCanActivation'] ) && self::$OPTION['tinCanActivation'] !== '1' ) { echo 'display: none';} ?>">
				<div class="uo-admin-header">
					<div
						class="uo-admin-title"><?php _e( 'Mark Complete button', 'uncanny-learndash-reporting' ); ?></div>
				</div>
				<div class="uo-admin-block">
					<div class="uo-admin-form">

						<!-- Disable LearnDash Mark Complete button until the learner completes all Tin Can modules in the Lesson/Topic? -->
						<div class="uo-admin-field">
							<div class="uo-admin-label"><?php _e( 'Behavior', 'uncanny-learndash-reporting' ); ?></div>
							<div
								class="uo-admin-description"><?php _e( 'Use these options to set the default behavior of the Mark Complete button in lessons and topics that contain embedded Tin Canny content. This setting can be overridden at the individual lesson/topic level.', 'uncanny-learndash-reporting' ); ?></div>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['disableMarkComplete'] ) && self::$OPTION['disableMarkComplete'] === '0' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="disableMarkComplete" value="0">
								<div class="uo-checkmark"></div>
								<span
									class="uo-label"><?php _e( '<strong>Always Enabled:</strong> Mark Complete button is always enabled.', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['disableMarkComplete'] ) && self::$OPTION['disableMarkComplete'] === '1' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="disableMarkComplete" value="1">
								<div class="uo-checkmark"></div>
								<span
									class="uo-label"><?php _e( '<strong>Disabled until complete:</strong> Mark Complete button is disabled until the learner completes the Tin Can module.', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['disableMarkComplete'] ) && self::$OPTION['disableMarkComplete'] === '3' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="disableMarkComplete" value="3">
								<div class="uo-checkmark"></div>
								<span
									class="uo-label"><?php _e( '<strong>Hidden until complete:</strong> Mark Complete button is hidden until the learner completes the Tin Can module.', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['disableMarkComplete'] ) && self::$OPTION['disableMarkComplete'] === '4' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="disableMarkComplete" value="4">
								<div class="uo-checkmark"></div>
								<span
									class="uo-label"><?php _e( '<strong>Hidden and autocomplete:</strong> Mark Complete button is hidden and the lesson/topic is automatically marked complete when the learner completes the Tin Can module. <b>Note:</b> With this option, you will need to provide a way for the user to progress to the next lesson or topic when the module has been completed.', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['disableMarkComplete'] ) && self::$OPTION['disableMarkComplete'] === '5' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="disableMarkComplete" value="5">
								<div class="uo-checkmark"></div>
								<span
									class="uo-label"><?php _e( '<strong>Hidden and autoadvance:</strong> Mark Complete button is hidden, the lesson/topic is automatically marked complete and the learner is automatically advanced to the next lesson or topic when they complete the Tin Can module.', 'uncanny-learndash-reporting' ); ?></span>
							</label>


						</div>

						<!-- Enable compatibility mode -->
						<div class="uo-admin-field">
							<div class="uo-admin-label"><?php _e( 'Enable compatibility mode', 'uncanny-learndash-reporting' ); ?></div>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['methodMarkCompleteForTincan'] ) && self::$OPTION['methodMarkCompleteForTincan'] === '1' ) echo ' checked="checked"'; ?> type="radio" name="methodMarkCompleteForTincan" value="1">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'Yes', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['methodMarkCompleteForTincan'] ) && self::$OPTION['methodMarkCompleteForTincan'] === '0' ) echo ' checked="checked"'; ?> type="radio" name="methodMarkCompleteForTincan" value="0">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'No', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<div class="uo-admin-description"><?php _e( 'Enables a slower method of recording xAPI statements and unlocking Mark Complete behaviors that works in a greater range of situations, including when statements are sent simultaneously with module closure.', 'uncanny-learndash-reporting' ); ?> </div>
						</div>

						<!-- Custom Label -->
						<div class="uo-admin-field">
							<div
								class="uo-admin-label"><?php _e( 'Custom label', 'uncanny-learndash-reporting' ); ?></div>
							<div
								class="uo-admin-description"><?php _e( 'Set a custom label on the Mark Complete button when a Tin Canny module is embedded in the lesson/topic.', 'uncanny-learndash-reporting' ); ?></div>
							<input class="uo-admin-input" type="text" name="labelMarkComplete" id="labelMarkComplete"
								   value="<?php echo self::$OPTION['labelMarkComplete'] ?>"/>
						</div>

						<!-- Autocomplete Lessons and Topics even if Tin Canny content on page (Uncanny Toolkit Pro) -->
						<div class="uo-admin-field">
							<div
								class="uo-admin-label"><?php _e( 'Autocomplete Lessons and Topics even if Tin Canny content on page (Uncanny Toolkit Pro)', 'uncanny-learndash-reporting' ); ?></div>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['autocompleLessonsTopicsTincanny'] ) && self::$OPTION['autocompleLessonsTopicsTincanny'] === '1' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="autocompleLessonsTopicsTincanny" value="1">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'Yes', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<label class="uo-radio">
								<input <?php if ( isset( self::$OPTION['autocompleLessonsTopicsTincanny'] ) && self::$OPTION['autocompleLessonsTopicsTincanny'] === '0' ) {
									echo ' checked="checked"';
								} ?> type="radio" name="autocompleLessonsTopicsTincanny" value="0">
								<div class="uo-checkmark"></div>
								<span class="uo-label"><?php _e( 'No', 'uncanny-learndash-reporting' ); ?></span>
							</label>

							<div
								class="uo-admin-description"><?php _e( 'When set to Yes, lessons and topics will be marked complete immediately upon page load, even when a Tin Canny module is present on the page. (Requires Uncanny LearnDash Toolkit Pro with Autocomplete Lessons and Topics module activated)', 'uncanny-learndash-reporting' ); ?></div>
						</div>

						<!-- Submit -->
						<div class="uo-admin-field uo-admin-extra-space">
							<input type="submit" name="submit" id="submit" class="uo-admin-form-submit"
								   value="<?php _e( 'Save Changes', 'uncanny-learndash-reporting' ); ?>">
						</div>

					</div>
				</div>
			</div>

			<!-- Lightbox -->
			<div class="uo-admin-section">
				<div class="uo-admin-header">
					<div class="uo-admin-title"><?php _e( 'Lightbox', 'uncanny-learndash-reporting' ); ?></div>
				</div>
				<div class="uo-admin-block">
					<div class="uo-admin-form">

						<!-- Nive Transition -->
						<div class="uo-admin-field nivo">
							<div
								class="uo-admin-label"><?php _e( 'Transition', 'uncanny-learndash-reporting' ); ?></div>
							<select class="uo-admin-select" name="nivo-transition" id="nivo-transition">
								<?php foreach ( $nivo_transitions as $key => $nivo_transition ) : ?>
									<option
										value="<?php echo $nivo_transition ?>"<?php if ( self::$OPTION['nivo-transition'] === $nivo_transition ) {
										echo ' selected="selected"';
									} ?>><?php echo $key ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<!-- Lightbox size -->
						<div class="uo-admin-field">
							<div
								class="uo-admin-label"><?php _e( 'Default lightbox size', 'uncanny-learndash-reporting' ); ?></div>

							<div class="uo-admin-field uo-admin-field-inline">

								<div class="uo-admin-field-inline-row">
									<div class="uo-admin-field-part">
										<div
											class="uo-admin-label"><?php _e( 'Width', 'uncanny-learndash-reporting' ); ?></div>
									</div>
									<div class="uo-admin-field-part">
										<input class="uo-admin-input" type="text" name="width" id="width"
											   value="<?php echo self::$OPTION['width'] ?>"/>
									</div>
									<div class="uo-admin-field-part">
										<select class="uo-admin-select" name="width_type" id="width_type">
											<option value="px"<?php if ( self::$OPTION['width_type'] === 'px' ) {
												echo ' selected="selected"';
											} ?>>px
											</option>
											<option value="%"<?php if ( self::$OPTION['width_type'] === '%' ) {
												echo ' selected="selected"';
											} ?>>%
											</option>
										</select>
									</div>
								</div>

								<div class="uo-admin-field-inline-row">
									<div class="uo-admin-field-part">
										<div
											class="uo-admin-label"><?php _e( 'Height', 'uncanny-learndash-reporting' ); ?></div>
									</div>
									<div class="uo-admin-field-part">
										<input class="uo-admin-input" type="text" name="height" id="height"
											   value="<?php echo self::$OPTION['height'] ?>"/>
									</div>
									<div class="uo-admin-field-part">
										<select class="uo-admin-select" name="height_type" id="height_type">
											<option value="px"<?php if ( self::$OPTION['height_type'] === 'px' ) {
												echo ' selected="selected"';
											} ?>>px
											</option>
											<option value="%"<?php if ( self::$OPTION['height_type'] === '%' ) {
												echo ' selected="selected"';
											} ?>>%
											</option>
										</select>
									</div>
								</div>

							</div>
						</div>

						<!-- Submit -->
						<div class="uo-admin-field uo-admin-extra-space">
							<input type="submit" name="submit" id="submit" class="uo-admin-form-submit"
								   value="<?php _e( 'Save Changes', 'uncanny-learndash-reporting' ); ?>">
						</div>

					</div>
				</div>
			</div>
		</form>

		<!-- Reset database -->
		<div class="uo-admin-section">
			<div class="uo-admin-header">
				<div class="uo-admin-title"><?php _e( 'Reset data', 'uncanny-learndash-reporting' ); ?></div>
			</div>
			<div class="uo-admin-block">
				<div class="uo-admin-form">
					<div class="uo-admin-field">
						<div
							class="uo-admin-label"><?php _e( 'Reset Tin Can data', 'uncanny-learndash-reporting' ); ?></div>
						<button class="uo-admin-form-submit uo-admin-form-submit-danger"
								id="btnResetTinCanData"><?php _e( 'Reset data', 'uncanny-learndash-reporting' ); ?></button>
						<div
							class="uo-admin-description"><?php _e( 'This will delete all Tin Can data. Use with caution!', 'uncanny-learndash-reporting' ); ?></div>
					</div>

					<div class="uo-admin-field">
						<div
							class="uo-admin-label"><?php _e( 'Reset xAPI Quiz data', 'uncanny-learndash-reporting' ); ?></div>
						<button class="uo-admin-form-submit uo-admin-form-submit-danger"
								id="btnResetQuizData"><?php _e( 'Reset Quiz Data', 'uncanny-learndash-reporting' ); ?></button>
						<div
							class="uo-admin-description"><?php _e( 'This will delete all xAPI Quiz data.  Use with caution!', 'uncanny-learndash-reporting' ); ?></div>
					</div>

					<div class="uo-admin-field">

						<div class="uo-admin-label"><?php _e( 'Reset bookmark data', 'uncanny-learndash-reporting' ); ?></div>
						<button class="uo-admin-form-submit uo-admin-form-submit-danger"
								id="btnResetBookmarkData"><?php _e( 'Reset bookmark data', 'uncanny-learndash-reporting' ); ?></button>
						<div
							class="uo-admin-description"><?php _e( 'This will delete all saved resume data for uploaded modules, forcing users to restart modules from the beginning.', 'uncanny-learndash-reporting' ); ?></div>
					</div>

					<div class="uo-admin-field">
						<div class="uo-admin-label"><?php _e( 'Purge Experienced statements', 'uncanny-learndash-reporting' ); ?></div>
						<button class="uo-admin-form-submit uo-admin-form-submit-danger"
								id="btnPurgeExperienced"><?php _e( 'Purge Experienced statements', 'uncanny-learndash-reporting' ); ?></button>
						<div
							class="uo-admin-description"><?php _e( 'This will delete all saved xAPI statements with the "Experienced" verb.', 'uncanny-learndash-reporting' ); ?></div>
					</div>

					<div class="uo-admin-field">
						<div class="uo-admin-label"><?php _e( 'Purge Answered statements', 'uncanny-learndash-reporting' ); ?></div>
						<button class="uo-admin-form-submit uo-admin-form-submit-danger"
								id="btnPurgeAnswered"><?php _e( 'Purge Answered statements', 'uncanny-learndash-reporting' ); ?></button>
						<div
							class="uo-admin-description"><?php _e( 'This will delete all saved xAPI statements with the "Answered" verb.', 'uncanny-learndash-reporting' ); ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function () {
		jQuery('input:radio[name="tinCanActivation"]').change(
			function(){
				if (this.checked && this.value == '1') {
					jQuery('#mark_complete_button_box').show();
				} else {
					jQuery('#mark_complete_button_box').hide();
				}
			});
	});
</script>
