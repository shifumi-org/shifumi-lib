=== LearnDash Course Grid ===
Author: LearnDash
Author URI: https://learndash.com 
Plugin URI: https://learndash.com/add-on/course-grid/
LD Requires at least: 3.0
Slug: learndash-course-grid
Tags: grid, view, display
Requires at least: 5.0
Tested up to: 6.0
Requires PHP: 7.0
Stable tag: 2.0.6

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

= 2.0.6 =

* Added translation support for block strings
* Updated added back learndash_course_grid_custom_button_text filter hook
* Fixed missing course grid translations
* Fixed slow queries in sites with a large number of users (10,000+)
* Fixed allow max/min prices to be empty
* Fixed correctly set max-min width 
* Fixed shortcodes having additional parameters in their tag
* Fixed don't show ribbon if course is closed or has no price
* Fixed grids not displaying correctly in Elementor
* Fixed skin and card assets not correctly loading in Elementor
* Fixed incorrect shared steps URL
* Fixed incorrect variable type for implode
* Fixed meta box styling
* Fixed skins and cards not being filterable
* Fixed incorrect post results if taxonimies attribute values are wrapped in quotes

= 2.0.5 =

* Added pre-defined taxonomies in filter dropdown
* Added support for new global currency in LearnDash core
* Added LearnDash specific selector for CSS to avoid conflicts
* Updated improve ribbon text logic
* Fixed conflict with other plugins that execute the_content() on the front-end
* Fixed taxonomies filter not being able to have more than 1 working term
* Fixed enrolled courses query returning all courses for logged out users
* Fixed undefined functions
* Fixed remove box sizing CSS rule to avoid conflicts with other themes/plugins
* Fixed masonry styles not working correctly 

= 2.0.4 =

* Added icon supported element for cards
* Added conditional for element color selector
* Added custom color selector for ribbon, icon, and button elements
* Updated not started label text on CG 2.0 templates based on user activity for LD lesson and topic post type
* Updated not Started label based on user activity for lesson and topic
* Fixed card layouts and styles
* Fixed minor cards layout and CSS styles
* Fixed block editor meta setting value is not passed to front end templates
* Fixed incorrect variable name
* Fixed translations meta box on translations setting page is not loaded
* Fixed update translations metabox to match with plugin name
* Fixed undefined index error
* Fixed undefined JS function
* Fixed missing translation slug
* Fixed legacy v1 course grid is not detected if the shortcode already stored in HTML format in DB

= 2.0.3 =

* Updated masonry script
* Updated grid skin selector
* Fixed PHP deprecated notice
* Fixed legacy v1 assets are not loaded when using ld course list block
* Fixed compatibility error with Elementor for v1 course grid element

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