<?php
$item_id = ( !$post ) ? '1' : $post->ID;
$key = '-' . $item_id;

$item_title_type = ( !$post ) ? 'text' : 'hidden';
?>

<div id="snc-embed_information<?php echo $key ?>" class="wrap snc-embed_information">
	<div class="container">
		<form enctype="multipart/form-data" id="snc-media_enbed_form<?php echo $key ?>" class="snc-media_enbed_form" action="<?php echo admin_url( 'admin-ajax.php' ) ?>" method="POST" data-item_id="<?php echo $item_id ?>">
			<input type="hidden" name="action" value="SnC_Media_Embed" />
			<input type="hidden" name="security" value="<?php echo wp_create_nonce( "snc-media_enbed_form" ) ?>" />

			<input type="hidden" name="id" id="item_id" value="<?php echo $item_id ?>" />
			<input type="<?php echo $item_title_type ?>" name="title" id="item_title" value="<?php echo ( ! $post ) ? '' : $post->file_name; ?>" />

			<h3><?php _e( 'Insert As', 'uncanny-learndash-reporting' ); ?></h3>
			<ul class="insert_type">
				<li>
					<label for="insert_type<?php echo $key ?>_1">
						<input type="radio" name="insert_type" id="insert_type<?php echo $key ?>_1" value="iframe" checked="checked" data-item_id="<?php echo $item_id ?>" /> <?php _e( 'iFrame', 'uncanny-learndash-reporting' ); ?>
					</label>
				</li>
				<li>
					<label for="insert_type<?php echo $key ?>_2">
						<input type="radio" name="insert_type" id="insert_type<?php echo $key ?>_2" value="lightbox" data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Lightbox', 'uncanny-learndash-reporting' ); ?>
					</label>
				</li>
				<li>
					<label for="insert_type<?php echo $key ?>_3">
						<input type="radio" name="insert_type" id="insert_type<?php echo $key ?>_3" value="_blank" data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Link that opens in a new window', 'uncanny-learndash-reporting' ); ?>
					</label>
				</li>
				
			</ul>

			<div id="iframe<?php echo $key ?>" class="options" data-item_option="iframe">
				<h3 class="no-margin"><?php _e( 'iFrame Size', 'uncanny-learndash-reporting' ); ?></h3>
				<ul class="iframe_size">
					<li>
						<label for="iframe_width<?php echo $key ?>" class="size_label"><?php _e( 'Width', 'uncanny-learndash-reporting' ); ?></label>
						<input type="text" name="iframe_width" id="iframe_width<?php echo $key ?>" value="100" />
						<select name="iframe_width_type" id="iframe_width_type<?php echo $key ?>">
							<option value="px">px</option>
							<option value="%" selected="selected">%</option>
							<option value="vw">vw</option>
							<option value="vh">vh</option>
						</select>
					</li>
					<li>
						<label for="iframe_height<?php echo $key ?>" class="size_label"><?php _e( 'Height', 'uncanny-learndash-reporting' ); ?></label>
						<input type="text" name="iframe_height" id="iframe_height<?php echo $key ?>" value="600" />
						<select name="iframe_height_type" id="iframe_height_type<?php echo $key ?>">
							<option value="px" selected="selected">px</option>
							<option value="%">%</option>
							<option value="vw">vw</option>
							<option value="vh">vh</option>
						</select>
					</li>
				</ul>
			</div>

			<div id="lightbox_title<?php echo $key ?>" class="options hidden" data-item_option="lightbox">
				<h3 class="no-margin"><?php _e( 'Title', 'uncanny-learndash-reporting' ); ?></h3>
				<ul class="lightbox_title">
					<li>
						<label for="lightbox_title<?php echo $key ?>_1">
							<input type="radio" name="lightbox_title" id="lightbox_title<?php echo $key ?>_1" value="No Title" checked="checked"  data-item_id="<?php echo $item_id ?>" /> <?php _e( 'No Title', 'uncanny-learndash-reporting' ); ?>
						</label>
					</li>
					<li>
						<label for="lightbox_title<?php echo $key ?>_2">
							<input type="radio" name="lightbox_title" id="lightbox_title<?php echo $key ?>_2" value="With Title"  data-item_id="<?php echo $item_id ?>" /> <?php _e( 'With Title', 'uncanny-learndash-reporting' ); ?>
						</label>
						<input type="text"  name="lightbox_title_text" id="lightbox_title_text<?php echo $key ?>" class="hidden text_with_title text_with_radio"  data-item_id="<?php echo $item_id ?>" />
					</li>
				</ul>

				<h3><?php _e( 'Button', 'uncanny-learndash-reporting' ); ?></h3>
				<ul class="lightbox_button">
					<li>
						<label for="lightbox_button<?php echo $key ?>_2">
							<input type="radio" name="lightbox_button" id="lightbox_button<?php echo $key ?>_2" value="text" checked="checked" data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Link Text', 'uncanny-learndash-reporting' ); ?>
						</label>
					</li>
					<li>
						<label for="lightbox_button<?php echo $key ?>_1">
							<input type="radio" name="lightbox_button" id="lightbox_button<?php echo $key ?>_1" value="small"  data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Small Size Button', 'uncanny-learndash-reporting' ); ?>
						</label>
					</li>
					<li>
						<label for="lightbox_button<?php echo $key ?>_4">
							<input type="radio" name="lightbox_button" id="lightbox_button<?php echo $key ?>_4" value="medium"  data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Medium Size Button', 'uncanny-learndash-reporting' ); ?>
						</label>
					</li>
					<li>
						<label for="lightbox_button<?php echo $key ?>_5">
							<input type="radio" name="lightbox_button" id="lightbox_button<?php echo $key ?>_5" value="large"  data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Large Size Button', 'uncanny-learndash-reporting' ); ?>
						</label>
					</li>
					<li>
						<label for="lightbox_button<?php echo $key ?>_3">
							<input type="radio" name="lightbox_button" id="lightbox_button<?php echo $key ?>_3" value="image"  data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Use custom image', 'uncanny-learndash-reporting' ); ?>
						</label>

						<!-- Button -->
						<section id="lightbox_button_custom<?php echo $key ?>" class="file_upload_button lightbox_button_custom hidden" data-id="lightbox_button_custom_file<?php echo $key ?>" data-item_id="<?php echo $item_id ?>">
							<span class="dashicons dashicons-plus-alt"></span>
							<img class="loading" src="<?php echo SnC_ASSET_URL ?>images/loading-animation.gif" />
							<div><?php _e( 'Click to Upload', 'uncanny-learndash-reporting' ); ?></div>
						</section>

						<input name="lightbox_button_custom_file"  id="lightbox_button_custom_file<?php echo $key ?>" data-id="lightbox_button_custom_file<?php echo $key ?>" type="file" class="hidden" />
					</li>

					<li>
						<label for="lightbox_button<?php echo $key ?>_6">
							<input type="radio" name="lightbox_button" id="lightbox_button<?php echo $key ?>_6" value="url" data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Image URL', 'uncanny-learndash-reporting' ); ?>
						</label>
						<input type="text"  name="lightbox_button_url" id="lightbox_button_url<?php echo $key ?>" class="lightbox_button_url text_with_radio hidden"  data-item_id="<?php echo $item_id ?>" />
					</li>
					
				</ul>
				
				<div class="lightbox_button_text" id="lightbox_button_text_<?php echo $key ?>" data-item_id="<?php echo $item_id ?>">
					<h3><?php _e( 'Link / Button Text', 'uncanny-learndash-reporting' ); ?></h3>
					<input type="text" name="lightbox_button_text" id="lightbox_button_text<?php echo $key ?>" class="lightbox_button_text" data-item_id="<?php echo $item_id ?>"/>
				</div>
				
				<div class="nivo_theme" id="nivo_theme<?php echo $key ?>">
					<h3 class="with_select"><?php _e( 'Nivo Slider Transition', 'uncanny-learndash-reporting' ); ?></h3>
					<select class="with_select" name="nivo_transition" id="nivo_transition<?php echo $key ?>">
						<?php foreach( $nivo_transitions as $cb_key => $cb_value ) { ?>
						<option value="<?php echo $cb_value ?>" <?php if ( $options['nivo-transition'] === $cb_value ) echo 'selected="selected"'; ?>><?php echo $cb_key;?></option>
						<?php }?>
					</select>
				</div>

				<div class="clear"></div>

				<h3><?php _e( 'Size Options', 'uncanny-learndash-reporting' ); ?></h3>
				<ul>
					<li>
						<label for="width<?php echo $key ?>" class="size_label"><?php _e( 'Width', 'uncanny-learndash-reporting' ); ?></label>
						<input type="text" name="width" id="width<?php echo $key ?>" value="<?php echo $options['width'] ?>" />
						<select name="width_type" id="width_type<?php echo $key ?>">
							<option value="px"<?php if ( $options['width_type'] === 'px' ) echo ' selected="selected"'; ?>>px</option>
							<option value="%"<?php if ( $options['width_type'] === '%' ) echo ' selected="selected"'; ?>>%</option>
							<option value="vw"<?php if ( $options['width_type'] === 'vw' ) echo ' selected="selected"'; ?>>vw</option>
							<option value="vh"<?php if ( $options['width_type'] === 'vh' ) echo ' selected="selected"'; ?>>vh</option>
						</select>
					</li>
					<li>
						<label for="height<?php echo $key ?>" class="size_label"><?php _e( 'Height', 'uncanny-learndash-reporting' ); ?></label>
						<input type="text" name="height" id="height<?php echo $key ?>" value="<?php echo $options['height'] ?>" />
						<select name="height_type" id="height_type<?php echo $key ?>">
							<option value="px"<?php if ( $options['height_type'] === 'px' ) echo ' selected="selected"'; ?>>px</option>
							<option value="%"<?php if ( $options['height_type'] === '%' ) echo ' selected="selected"'; ?>>%</option>
							<option value="vw"<?php if ( $options['height_type'] === 'vw' ) echo ' selected="selected"'; ?>>vw</option>
							<option value="vh"<?php if ( $options['height_type'] === 'vh' ) echo ' selected="selected"'; ?>>vh</option>
						</select>
					</li>
				</ul>
			</div><!--end lightbox options-->

			<div id="new_window_option<?php echo $key ?>" class="options hidden"  data-item_id="<?php echo $item_id ?>"  data-item_option="_blank">
				<h3 class="no-margin"><?php _e( 'Link that opens in a new window options', 'uncanny-learndash-reporting' ); ?></h3>
				<ul class="new_window_option"  data-item_id="<?php echo $item_id ?>">
					<li>
						<label for="_blank<?php echo $key ?>_2">
							<input type="radio" name="_blank" id="_blank<?php echo $key ?>_2" value="text" checked="checked" data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Link Text', 'uncanny-learndash-reporting' ); ?>
						</label>
					</li>
					<li>
						<label for="_blank<?php echo $key ?>_1">
							<input type="radio" name="_blank" id="_blank<?php echo $key ?>_1" value="small"  data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Small Size Button', 'uncanny-learndash-reporting' ); ?>
						</label>
					</li>
					<li>
						<label for="_blank<?php echo $key ?>_4">
							<input type="radio" name="_blank" id="_blank<?php echo $key ?>_4" value="medium"  data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Medium Size Button', 'uncanny-learndash-reporting' ); ?>
						</label>
					</li>
					<li>
						<label for="_blank<?php echo $key ?>_5">
							<input type="radio" name="_blank" id="_blank<?php echo $key ?>_5" value="large"  data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Large Size Button', 'uncanny-learndash-reporting' ); ?>
						</label>
					</li>
					<li>
						<label for="_blank<?php echo $key ?>_3">
							<input type="radio" name="_blank" id="_blank<?php echo $key ?>_3" value="image" data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Use custom image', 'uncanny-learndash-reporting' ); ?>
						</label>

						<!-- Button -->
						<section id="snc-_blank_button<?php echo $key ?>" class="file_upload_button _blank_button hidden" data-id="_blank_button_custom_file<?php echo $key ?>">
							<span class="dashicons dashicons-plus-alt"></span>
							<img class="loading" src="<?php echo SnC_ASSET_URL ?>images/loading-animation.gif" />
							<div><?php _e( 'Click to Upload', 'uncanny-learndash-reporting' ); ?></div>
						</section>

						<input name="upload_blank_lightbox_custom_button"  id="snc-upload_blank_lightbox_custom_button<?php echo $key ?>" type="file" class="hidden" data-id="_blank_button_custom_file<?php echo $key ?>" />
					</li>

					<li>
						<label for="_blank_url<?php echo $key ?>_6">
							<input type="radio" name="_blank" id="_blank_url<?php echo $key ?>_6" value="url" data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Image URL', 'uncanny-learndash-reporting' ); ?>
						</label>

						<input type="text"  name="_blank_url" id="_blank_url<?php echo $key ?>" class="_blank_url text_with_radio hidden"  data-item_id="<?php echo $item_id ?>" />
					</li>
				</ul>
				<div class="_blank_button_text" id="_blank_button_text_<?php echo $key ?>" data-item_id="<?php echo $item_id ?>">
					<h3><?php _e( 'Link / Button Text', 'uncanny-learndash-reporting' ); ?></h3>
					<input type="text"  name="_blank_text" id="_blank_text<?php echo $key ?>" class="_blank_text text_with_radio" data-item_id="<?php echo $item_id ?>" />
				</div>
			</div>

			<div id="same_window_option<?php echo $key ?>" class="options hidden"  data-item_id="<?php echo $item_id ?>"  data-item_option="_self">
				<h3 class="no-margin"><?php _e( 'Link that opens in a same window options', 'uncanny-learndash-reporting' ); ?></h3>
				<ul class="same_window_option"  data-item_id="<?php echo $item_id ?>">
					<li>
						<label for="_self<?php echo $key ?>_2">
							<input type="radio" name="_self" id="_self<?php echo $key ?>_2" value="text" checked="checked" data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Link Text', 'uncanny-learndash-reporting' ); ?>
						</label>
					</li>
					<li>
						<label for="_self<?php echo $key ?>_1">
							<input type="radio" name="_self" id="_self<?php echo $key ?>_1" value="small"  data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Small Size Button', 'uncanny-learndash-reporting' ); ?>
						</label>
					</li>
					<li>
						<label for="_self<?php echo $key ?>_4">
							<input type="radio" name="_self" id="_self<?php echo $key ?>_4" value="medium"  data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Medium Size Button', 'uncanny-learndash-reporting' ); ?>
						</label>
					</li>
					<li>
						<label for="_self<?php echo $key ?>_5">
							<input type="radio" name="_self" id="_self<?php echo $key ?>_5" value="large"  data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Large Size Button', 'uncanny-learndash-reporting' ); ?>
						</label>
					</li>
					<li>
						<label for="_self<?php echo $key ?>_3">
							<input type="radio" name="_self" id="_self<?php echo $key ?>_3" value="image" data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Use custom image', 'uncanny-learndash-reporting' ); ?>
						</label>

						<!-- Button -->
						<section id="snc-_self_button<?php echo $key ?>" class="file_upload_button _self_button hidden"  data-id="_self_button_custom_file<?php echo $key ?>">
							<span class="dashicons dashicons-plus-alt"></span>
							<img class="loading" src="<?php echo SnC_ASSET_URL ?>images/loading-animation.gif" />
							<div><?php _e( 'Click to Upload', 'uncanny-learndash-reporting' ); ?></div>
						</section>

						<input name="upload_self_lightbox_custom_button"  id="snc-upload_self_lightbox_custom_button<?php echo $key ?>" type="file" class="hidden"  data-id="_self_button_custom_file<?php echo $key ?>" />
					</li>

					<li>
						<label for="_self<?php echo $key ?>_6">
							<input type="radio" name="_self" id="_self<?php echo $key ?>_6" value="url" data-item_id="<?php echo $item_id ?>" /> <?php _e( 'Image URL', 'uncanny-learndash-reporting' ); ?>
						</label>
						<input type="text"  name="_self_url" id="_self_url<?php echo $key ?>" class="_self_url text_with_radio hidden"  data-item_id="<?php echo $item_id ?>" />
					</li>
				</ul>
				<div class="_self_button_text" id="_self_button_text_<?php echo $key ?>" data-item_id="<?php echo $item_id ?>">
					<h3><?php _e( 'Link / Button Text', 'uncanny-learndash-reporting' ); ?></h3>
					<input type="text"  name="_self_text" id="_self_text<?php echo $key ?>" class="_self_text text_with_radio" data-item_id="<?php echo $item_id ?>" />
				</div>
			</div>

			<div class="clear">&nbsp;</div>

			<input type="submit" class="button button-primary" name="insert<?php echo $key ?>" id="insert<?php echo $key ?>" value="<?php _e( 'Insert Into Post', 'uncanny-learndash-reporting' ); ?>" />
			<a href="#" id="delete<?php echo $key ?>" class="button delete-media" data-item_id="<?php echo $item_id ?>" /><?php _e( 'Delete', 'uncanny-learndash-reporting' ); ?></a>
		</form>
	</div>
</div>
