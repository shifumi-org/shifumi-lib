jQuery(function ($) {
	if ($('#switch-to-builder').size() > 0) {
		// some cases, the autosave is off, so we need to check it.
		var postData;
		if (window.wp.autosave !== undefined) {
			postData = window.wp.autosave.getPostData();
		} else {
			var url = new URL(window.location.href);
			var post_id = url.searchParams.get('post');
			if (null === post_id) {
				post_id = 0;
			}
			postData = {post_id: post_id}
		}

		$('body').on('click', '#switch-to-builder', function () {
			//store a meta
			var that = $(this)
			$.ajax({
				url: ajaxurl,
				data: {
					action: 'use_certificate_builder',
					nonce: ld_certificate_builder_switcher.nonce,
					id: postData.post_id
				},
				type: 'POST',
				beforeSend: function () {
					that.attr('disabled', 'disabled')
				},
				success: function (data) {
					if (data.success === true) {
						location.href = data.data.url
					}
				}
			})
		})
	}
})
