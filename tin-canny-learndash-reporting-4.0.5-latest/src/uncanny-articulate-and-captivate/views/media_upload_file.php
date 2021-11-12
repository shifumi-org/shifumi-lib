<div id="snc-media_upload_file_wrap" class="wrap snc_TB">
	<div class="title"><?php _e( 'Upload File', 'uncanny-learndash-reporting' ); ?></div>

	<div class="clear"></div>

	<form enctype="multipart/form-data" id="snc-media_upload_file_form" action="<?php echo admin_url( 'admin-ajax.php' ) ?>" method="POST">
		<input type="hidden" name="action" value="SnC_Media_Upload" />
		<input type="hidden" name="security" value="<?php echo wp_create_nonce( "snc-media_upload_form" ) ?>" />
		<?php if( isset( $_GET['content_id'] ) ) {?>
			<input type="hidden" id="content_id" name="content_id" value="<?php echo $_GET['content_id'];?>" />
		<?php } ?>
		<?php if( isset( $_GET['no_tab'] ) ) {?>
			<input type="hidden" id="no_tab" name="no_tab" value="no_tab" />
		<?php } ?>
		<?php if( isset( $_GET['no_refresh'] ) ) {?>
			<input type="hidden" id="no_refresh" name="no_refresh" value="no_refresh" />
			<input type="hidden" id="ele_id" name="ele_id" value="<?php echo $_GET['item_id'];?>" />
			
		<?php } ?>
		<input type="hidden" name="extension" id="snc-extension" value="" />
		<input type="hidden" name="max_file_size" id="snc-max_file_size" value="<?php echo wp_max_upload_size(); ?>" />

		<p class="description"><?php _e( 'Please upload a zip file published from one of <a href="https://www.uncannyowl.com/knowledge-base/authoring-tools-supported/" target="_blank">the supported authoring tools.', 'uncanny-learndash-reporting' ); ?></a></p>
		<p class="description">
			<a href="https://www.wpbeginner.com/wp-tutorials/how-to-increase-the-maximum-file-upload-size-in-wordpress/" target="_blank"><?php _e( 'Maximum upload file size', 'uncanny-learndash-reporting' ); ?></a>: <strong><?php echo $this->format_bytes( wp_max_upload_size() ); ?></strong>
		</p>
		<p class="description"><?php _e( '(To change this size, increase your PHP upload limit or contact your web host)', 'uncanny-learndash-reporting' ); ?></p>

		<div class="clear"></div>

		<!-- Button -->
		<section id="snc-upload_button" class="file_upload_button" data-id="snc-media_upload_file">
			<span class="dashicons dashicons-plus-alt"></span>
			<div><?php _e( 'Click to Upload', 'uncanny-learndash-reporting' ); ?></div>
		</section>

		<div class="progress">
			<div id="snc-progress_bar_wrapper"><div id="snc-progress_bar"></div></div>
			<p class="description"><?php _e( 'Please wait while your file is uploaded.', 'uncanny-learndash-reporting' ); ?></p>
		</div>

		<!-- Message -->
		<h2></h2>
		<div id="snc-media_upload_message" class="updated"><p></p></div>

		<!-- File Upload -->
		<input name="media_upload_file"  id="snc-media_upload_file" type="file" data-id="snc-media_upload_file" />
	</form>
</div>



