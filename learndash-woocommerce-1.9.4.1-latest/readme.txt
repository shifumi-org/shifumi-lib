=== WooCommerce for LearnDash ===
Author: LearnDash
Author URI: https://learndash.com
Plugin URI: https://learndash.com/add-on/woocommerce/ 
LD Requires at least: 3.0
Slug: learndash-woocommerce
Tags: integration, woocommerce,
Requires at least: 5.0
Tested up to: 5.9
Requires PHP: 7.0
Stable tag: 1.9.4.1

Integrate LearnDash LMS with WooCommerce.

== Description ==

Integrate LearnDash LMS with WooCommerce.

WooCommerce is the most popular shopping cart software for WordPress. Most WordPress themes are compatible with WooCommerce. This add-on allows you to sell your LearnDash created courses with the WooCommerce shopping cart.

= Integration Features = 

* Easily map courses to products
* Associate one, or multiple courses to a single product
* Automatic course access removal
* Works with any payment gateway
* Works with WooCommerce Subscription

See the [Add-on](https://learndash.com/add-on/woocommerce/) page for more information.

== Installation ==

If the auto-update is not working, verify that you have a valid LearnDash LMS license via LEARNDASH LMS > SETTINGS > LMS LICENSE. 

Alternatively, you always have the option to update manually. Please note, a full backup of your site is always recommended prior to updating. 

1. Deactivate and delete your current version of the add-on.
1. Download the latest version of the add-on from our [support site](https://support.learndash.com/article-categories/free/).
1. Upload the zipped file via PLUGINS > ADD NEW, or to wp-content/plugins.
1. Activate the add-on plugin via the PLUGINS menu.

== Changelog ==

= 1.9.4.1 =

* Fixed require login notice always appear on some themes that don't pass createaccount input data

= 1.9.4 =

* Added course/group access support for restore/delete/trash subscription customer charge updates
* Added customer charge handler to handle course/group enrollment logic based on customer charge and subscriptio status
* Updated re-enroll users to course/group if order is marked as processing (payment received) or completed
* Updated select2 field styles
* Updated move scripts and styles to dedicated folder and rename the filename plus add select2 lib files
* Updated: add scripts registration and deregistration methods and add logic to load scripts conditionally
* Fixed simultaneous simple and subscription product order doesn't enroll user to simple product course
* Fixed allowing guest checkout with course products preventing enrollment in associated course

= 1.9.3.3 =

* Fixed checking logic to prevent errors

= 1.9.3.2 =

* Fixed Uncaught Error: Call to a member function get_type() on bool

= 1.9.3.1 = 

* Fixed courses being added to users with the incorrect payment status. Courses are now only added on processing or complete rather than on hold or pending

= 1.9.3 = 

* Added new added order item to existing order will trigger course enrollment
* Fixed retroactive tool and some variables are not compatible with WC 5.6
* Fixed retroactive tool doesn't honor the expired subscription course removal setting
* Fixed renewal subscription payment reset access date for expired courses

= 1.9.2 = 

* Updated use global variable instead of debug backtrace to enable subscription products filter  
* Fixed conflict with WooCommerce product bundle extension, better code logic                                                                                      
* Fixed typo in get_type method name

= 1.9.1 = 

* Added a setting to skip disabling course access on subscription expiry
* Added an action hook to remove course access for failed and cancelled subscriptions
* Fixed subscription renewal changing the course enrollment date
* Fixed pricing fields missing on the product edit page

= 1.9.0 =

* Added dependencies check
* Added WPML multi language course selector support
* Added background course enrollment warning above course selector field
* Added WC subscription switching feature support
* Updated allow retroactive tool to process course enrollment directly instead of storing the queue in DB
* Updated remove old code that process retroactive tool using cron
* Updated change learndash_woocommerce_silent_course_enrollment_queue option to be non autoload to improve performance
* Updated Use custom label if set
* Fixed renewal process unenroll and reenroll users to courses
* Fixed PHP notice error because of deprecated class property
* Fixed retroactive tool reset enrollment date to the tool run date

View the full changelog [here](https://www.learndash.com/add-on/woocommerce/).