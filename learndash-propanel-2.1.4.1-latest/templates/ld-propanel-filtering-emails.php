<?php
/**
 * Learndash ProPanel Reporting - Emails section
 */
?>
<div class="email toggle-section" id="email">
	<div class="no-results">
		<strong class="note"><?php esc_html_e( 'Please select user(s) to send an email to.', 'ld_propanel' ); ?></strong>
	</div>
	<div class="results" style="display:none;">
		<input type="text" class="subject" placeholder="Subject">
		<textarea rows="10" class="message" placeholder="<?php _e('Your Message', 'ld_propanel') ?>"></textarea>
		<button id="propanel-send-email" class="button button-primary" disabled><?php echo esc_html__( 'Send', 'ld_propanel' ); ?></button> <button id="propanel-reset-email" class="button button-secondary" disabled><?php echo esc_html__( 'Reset', 'ld_propanel' ); ?></button>
		<span class="sending" style="display:none;">
			<?php esc_html_e( 'Sending...', 'ld_propanel' ); ?>
			<img src="<?php echo admin_url( 'images/spinner.gif' ); ?>">
		</span>
		<span class="sent" style="display:none;"></span>
	</div>
</div>
