=== LearnDash Course Grid ===
Author: LearnDash
Author URI: https://learndash.com 
Plugin URI: https://learndash.com/add-on/course-grid/
LD Requires at least: 3.0
Slug: learndash-course-grid
Tags: grid, view, display
Requires at least: 5.0
Tested up to: 5.8.2
Requires PHP: 7.0
Stable tag: 2.0.2

Transform the course list created with shortcode [ld_course_list] to grid view.

== Description ==

The Course Grid add-on gives you the ability to create a responsive course library that can be inserted on any page or post of your site. This is a perfect feature if you have a variety of course offerings and want to give users the ability to filter through them by category.

= Add-on Features = 

* Responsive grid layout of courses
* Filtering by category
* Display course featured image and short description
* Featured video support
* Dynamic course price listing
* Dynamic course status listing

See the [Add-on](https://learndash.com/add-on/course-grid/) page for more information.

== Installation ==

If the auto-update is not working, verify that you have a valid LearnDash LMS license via LEARNDASH LMS > SETTINGS > LMS LICENSE. 

Alternatively, you always have the option to update manually. Please note, a full backup of your site is always recommended prior to updating. 

1. Deactivate and delete your current version of the add-on.
1. Download the latest version of the add-on from our [support site](https://support.learndash.com/article-categories/free/).
1. Upload the zipped file via PLUGINS > ADD NEW, or to wp-content/plugins.
1. Activate the add-on plugin via the PLUGINS menu.

== Changelog ==

= 2.0.2 =

* Fixed asset loading for legacy course grids

= 2.0.1 =

* Added course enrollment status attributes and block option
* Udated removed fixed 4:3 aspect ratio to prevent distorted images
* Fixed course grid assets not loading when Elementor is being used

= 2.0.0 =

* Added course grid Gutenberg block
* Added learndash_course_grid shortcode
* Added learndash_course_grid_filter shortcode
* Added ability to add and use custom template
* Added support for CG 1.0 shortcode

= 1.7.0 = 

* Added ability to use HTML in the short description box
* Added custom text support for groups in the Course Grid
* Added Completed/In Progress/Not Available status for Course Grid ribbons
* Added support for the Course Grid ribbon to show for students enrolled in a group
* Added filter 'learndash_course_grid_load_resources' to load styles/scripts dynamically wherever they are wanted using conditional logic
* Updated deprecated .load in jQuery with .on('load)' instead
* Fixed Course Grid styles not being loaded when the shortcode is used in Elementor
* Fixed groups progress indicator 
* Fixed undefined variable
* Fixed array index error 

= 1.6.0 =

* Added hook to filter course grid html output
* Added docblock for html output filter
* Added class container class names and its filter hook
* Updated ribbon text escaping to wp_kses_post instead of esc_attr
* Updated load grid resources on LD courses archive page
* Updated plugin name and description
* Updated LD get course price helper function to allow users filter the values
* Updated to remove #ld_course_list wrapper on course grid shortcode to prevent conflicts with page builder plugins
* Fixed thumbnail course URL redirects to bare URL instead of nested URL
* Fixed short description unable to be set to empty because legacy value is not saved when saving new value
* Fixed undefined index error

View the full changelog [here](https://www.learndash.com/add-on/course-grid/).