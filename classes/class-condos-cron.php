<?php
/**
 * Class BLOKHAUSRE_Condos_Cron
 * Created by KAMBDA.
 * Author: Wuilly Vargas
 * Date: 28/10/2021
 * Time: 10:16 AM
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BLOKHAUSRE_Condos_Cron {
    
    
    public static function init() {
        add_filter('cron_condos_schedules', array( __CLASS__, 'BLOKHAUS_Condos_cron_schedules' ), 10, 1);
        add_action( 'blokhaus_condos_update', array( __CLASS__, 'BLOKHAUSRE_update_condos' ) );
    }

    /**
     * Update condos.
     *
     */
    public static function BLOKHAUSRE_update_condos() {
        BLOKHAUSRE_Condos_mls::update();
    }

    /**
     * Schedule Condos updates.
     */
    public static function BLOKHAUSRE_schedule_condos_updates( $api_key = '', $interval = '', $limit_query = '' ) {

        if ( empty( $api_key ) || empty(  $interval ) || empty(  $limit_query ) ) {
            $api_key = BLOKHAUSRE_API_Settings::get_setting('api_key');
            $interval = BLOKHAUSRE_API_Settings::get_setting('update_interval');
            $limit_query = BLOKHAUSRE_API_Settings::get_setting('limit_query');
        }

        if ( $api_key && $interval ) {

            if ( ! wp_next_scheduled( 'blokhaus_condos_update' ) ) {
                wp_schedule_event(   time(), $interval, 'blokhaus_condos_update' );
            } else {
                wp_reschedule_event( time(), $interval, 'blokhaus_condos_update' );
            }
        }

        self::BLOKHAUSRE_update_condos();

    }

    /**
     * Add new schedules to wp_cron.
     *
     */
    public static function BLOKHAUS_Condos_cron_schedules( $schedules ) {
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