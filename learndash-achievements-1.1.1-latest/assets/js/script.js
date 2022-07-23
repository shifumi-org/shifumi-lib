jQuery(document).ready(function($) {

var LD_Achievements = LD_Achievements || {};

if ( LD_Achievements_Data.notifications.length == 0 ) {
	LD_Achievements_Data.notifications = [];
} else {
	LD_Achievements_Data.notifications = JSON.parse( LD_Achievements_Data. notifications );
}

// TEST DATA
// LD_Achievements_Data.notifications = [
// 	{
// 		title: 'First Login',
// 		headline: 'Congratulations John Doe! You unlocked the badge: ',
// 		// title: '!ﺎﻨﻴﻧﺎﻬﺗ',
// 		message: 'Congratulation! You have passed the exam!',
// 		// message: '.ﺭﺎﺒﺘﺧﻻا ﺕﺰﺘﺟا ﺪﻘﻟ',
// 		image: 'http://dummy1.test/wp-content/plugins/learndash-achievement/assets/img/icons/awards_128-01.png',
// 	},
// ];
// console.log( LD_Achievements_Data.notifications );

LD_Achievements.frontend = {
	data: LD_Achievements_Data,

	init: function () {
		this.data.notifications.forEach( function ( notif ) {
			LD_Achievements.frontend.show_notification( notif.title, notif.message, notif.image );
		});
	},

	show_notification: function (title = '', message = '', image = null) {
		var user_id = LD_Achievements_Data.user_id;

		var html = `
			<div class="wrapper">
				<div class="image">
						<img src="${image}">
					</div>
					<div class="text">
						<div class="title">${title}</div>
						<div class="message">${message}</div>
					</div>
			</div>
		`

		var html_rtl =
			'<div class="wrapper rtl">' +
				'<div class="text">' +
					'<div class="title">' +
						title +
					'</div>' +
					'<div class="message">' +
						message +
					'</div>' +
				'</div>' +
				'<div class="image">' +
					'<img src="' + image + '">' +
				'</div>' +
				'<div class="clear"></div>' +
			'</div>';

		new Noty({
			type: 'success',
			layout: this.data.settings.rtl != '1' ? 'topRight' : 'topLeft',
			text: this.data.settings.rtl != '1' ? html : html_rtl,
			theme: 'learndash',
			timeout: this.data.settings.popup_time * 1000 || false,
			callbacks: {
				onShow: function() {
					LD_Achievements.frontend.delete_notification( user_id );
				}
			}
		}).show();
	},

	delete_notification: function ( user_id ) {
		$.ajax({
			url: this.data.ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'ld_achievement_delete_queue',
				user_id: user_id,
			},
		});
	}
}

LD_Achievements.frontend.init();

});
