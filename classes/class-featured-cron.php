<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BLOKHAUSRE_Featured_Cron {
    
    
    public static function init() {
        add_filter('cron_featured_schedules', array( __CLASS__, 'BLOKHAUS_Featured_cron_schedules' ), 10, 1);
        add_action( 'blokhaus_featured_update', array( __CLASS__, 'BLOKHAUSRE_update_featured' ) );
    }

    /**
     * Update Featured.
     *
     */
    public static function BLOKHAUSRE_update_featured() {
        BLOKHAUSRE_Featured_mls::update();
    }

    /**
     * Schedule Featured updates.
     */
    public static function BLOKHAUSRE_schedule_featured_updates( $api_key = '', $interval = '', $limit_query = '' ) {

        if ( empty( $api_key ) || empty(  $interval ) || empty(  $limit_query ) ) {
            $api_key = BLOKHAUSRE_API_Settings::get_setting('api_key');
            $interval = BLOKHAUSRE_API_Settings::get_setting('update_interval');
            $limit_query = BLOKHAUSRE_API_Settings::get_setting('limit_query');
        }

        if ( $api_key && $interval ) {

            if ( ! wp_next_scheduled( 'blokhaus_featured_update' ) ) {
                wp_schedule_event(   time(), $interval, 'blokhaus_featured_update' );
            } else {
                wp_reschedule_event( time(), $interval, 'blokhaus_featured_update' );
            }
        }

        self::BLOKHAUSRE_update_featured();

    }

    /**
     * Add new schedules to wp_cron.
     *
     */
    public static function BLOKHAUS_Featured_cron_schedules( $schedules ) {
        $schedules['one_minute'] = array(
            'interval' => 60,
            'display'  => esc_html__( 'Once a Minute', 'blokhausre-property-up' ),
        );
        $schedules['weekly'] = array(
            'interval' => 604800,
            'display' => esc_html__( 'Once Weekly', 'blokhausre-property-up' )
        );
        $schedules['biweekly'] = array(
            'interval' => 1209600,
            'display' => esc_html__( 'Once Biweekly', 'blokhausre-property-up' )
        );
        $schedules['monthly'] = array(
            'interval' => 2419200,
            'display' => esc_html__( 'Once Monthly', 'blokhausre-property-up' )
        );
        return $schedules;
    }
}
?>