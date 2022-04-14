<?php

/**
 * Upgrade class
 */
class Learndash_Woocommerce_Upgrade
{
    public static function init()
    {
        add_action( 'admin_init', [ __CLASS__, 'check_upgrade' ] );
    }

    public static function check_upgrade()
    {
        if ( did_action( 'admin_init' ) > 1 ) {
            return;
        }

        $saved_version   = get_option( 'learndash_woocommerce_version', false );
        $current_version = LEARNDASH_WOOCOMMERCE_VERSION;

        if ( ! $saved_version || $saved_version < $current_version ) {
            self::upgrade( $saved_version, $current_version );
            update_option( 'learndash_woocommerce_version', $current_version, true );
        }
    }

    public static function upgrade( $from_version, $to_version )
    {
        if ( ( $from_version <= '1.8.0.6' || ! $from_version ) && $to_version >= '1.8.0.7' ) {
            $queue = get_option( 'learndash_woocommerce_silent_course_enrollment_queue', [] );
            // Delete first so autoload value can be updated in DB
            delete_option( 'learndash_woocommerce_silent_course_enrollment_queue' );

            update_option( 'learndash_woocommerce_silent_course_enrollment_queue', $queue, false );
        }
    }
}

Learndash_Woocommerce_Upgrade::init();