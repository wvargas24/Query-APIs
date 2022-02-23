


 






















<?php
/**
 * Class BLOKHAUSRE_API_Settings
 * Created by KAMBDA.
 * Author: Wuilly Vargas
 * Date: 28/10/2021
 * Time: 10:16 AM
 */
class BLOKHAUSRE_API_Settings {


	/**
	 * Sets up init
	 *
	 */
	public static function init() {
        add_action( 'admin_init', array( __CLASS__, 'blokhausre_register_settings' ) );

        // Update cron job when API settings updated
        add_action( 'update_option_blokhausre_api_settings', array( __CLASS__, 'updated_option' ), 10, 2 );
    }


    public static function render() {

        // Flush the rewrite rules if the settings were updated.
        if ( isset( $_GET['settings-updated'] ) ) {
            flush_rewrite_rules();
            //BLOKHAUSRE_mls::update();
        }
        ?>

        <div class="houzez-admin-wrapper">

            <?php settings_errors(); 
            
            $header = get_template_directory().'/framework/admin/header.php';
            $tabs = get_template_directory().'/framework/admin/tabs.php';

            if ( file_exists( $header ) ) {
                //load_template( $header );
            }

            if ( file_exists( $tabs ) ) {
                //load_template( $tabs );
            }
            ?>

            <div class="admin-houzez-content">
                <h2 class="houzez-heading-inline">Bridge Api</h2>
                <div class="admin-houzez-row">
                    <div class="admin-houzez-box-wrap">
                        <div class="admin-houzez-box">
                            <div class="admin-houzez-box-header">
                                <div class="dashicons-before dashicons-admin-generic"></div><h3>API Settings</h3>
                            </div><!-- admin-houzez-box-header -->
                            <div class="admin-houzez-box-content">
                                <p>
                                <?php printf(
                                _x( 'Plugin gets MLS data from %1s and imports it into the WordPress database. The properties will be updated on a frequency that you can specify below.',
                                    'bridgedataoutput.com link', 'blokhausre-property-up' ),
                                    '<a href="//bridgedataoutput.com" target="_blank">bridgedataoutput.com</a>' ); ?>
                                </p>
                                <form class="form-wrap" method="post" action="options.php">
                                    <?php settings_fields( 'blokhausre_api_settings' ); ?>
                                    <?php do_settings_sections( 'blokhausre_api_settings' ); ?>
                                    <?php submit_button( esc_attr__( 'Save', 'blokhausre-property-up' ), 'primary' ); ?>
                                </form>
                            </div><!-- admin-houzez-box-content -->
                        </div><!-- admin-houzez-box -->
                    </div><!-- admin-houzez-box-wrap -->
                </div><!-- admin-houzez-row -->
            </div>
        </div><!-- wrap -->
        <?php
    }

    public static function blokhausre_register_settings() {

        // Register the setting.
        register_setting( 'blokhausre_api_settings', 'blokhausre_api_settings', array( __CLASS__, 'blokhausre_api_validate_settings' ) );

        /* === Settings Sections === */
        add_settings_section( 'blokhausre_api_section', '', array( __CLASS__, 'blokhausre_section_callback' ), 'blokhausre_api_settings' );

        /* === Settings Fields === */
        add_settings_field( 'api_key',   esc_html__( 'Access Token',   'blokhausre-property-up' ), array( __CLASS__, 'blokhausre_api_callback'   ), 'blokhausre_api_settings', 'blokhausre_api_section' );

        add_settings_field( 'update_interval',   esc_html__( 'Update Interval',   'blokhausre-property-up' ), array( __CLASS__, 'blokhausre_interval_field_callback'   ), 'blokhausre_api_settings', 'blokhausre_api_section' );

        add_settings_field( 'limit_query',   esc_html__( 'Limit Query',   'blokhausre-property-up' ), array( __CLASS__, 'blokhausre_limit_callback'   ), 'blokhausre_api_settings', 'blokhausre_api_section' );

    }  

    /**
     * Validates the plugin settings.
     *
     * @since  1.0.0
     * @access public
     * @param  array  $input
     * @return array
     */
    public static function blokhausre_api_validate_settings( $settings ) {

        $settings['api_key'] = $settings['api_key'] ? trim( strip_tags( $settings['api_key']   ), '/' ) : '';
        $settings['update_interval'] = $settings['update_interval'] ? trim( strip_tags( $settings['update_interval']   ), '/' ) : '';
        $settings['limit_query'] = $settings['limit_query'] ? trim( strip_tags( $settings['limit_query']   ), '/' ) : '';
        return $settings;
    }

    /**
     * Section callback.
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public static function blokhausre_section_callback() {}


    /**
     * API Key field callback.
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public static function blokhausre_api_callback() {

        $api_key = self::get_setting('api_key');
        ?>
        <label for="api_key" class="form-field">
            <input type="password" id="api_key" name="blokhausre_api_settings[api_key]" value="<?php echo $api_key; ?>" class="regular-text" placeholder="<?php esc_html_e( 'Enter the Bridget API Token', 'blokhausre-property-up' ); ?>">
            <p class="hidden">
            <?php printf(
                _x( 'Get yours at: %1s', 'URL where to get the API key', 'blokhausre-property-up' ),
                '<a href="//openexchangerates.org/" target="_blank">openexchangerates.org</a>' ); ?>
            </p>
        </label>
        
            <?php 
    }

    /**
     * Limit field callback.
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public static function blokhausre_limit_callback() {

        $limit_query = self::get_setting('limit_query');
        ?>
        <label for="limit_query" class="form-field hidden" style="display:none;">
            <input type="text" id="limit_query" name="blokhausre_api_settings[limit_query]" value="<?php echo $limit_query; ?>" class="regular-text" placeholder="<?php esc_html_e( 'Enter Limit', 'blokhausre-property-up' ); ?>">
        </label>
        <p style="display:none;">Limits the size of the result set.</p>
        
            <?php 
    }



    /**
     * Interval field callback.
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
    public static function blokhausre_interval_field_callback() { 

        $update_frequency = self::get_setting('update_interval');

        ?>
        <label for="update_interval" class="hidden" style="display:none;">
            <?php // esc_html_e( 'Rates update frequency:', 'blokhausre-property-up' ); ?>
            <select name="blokhausre_api_settings[update_interval]" id="update_interval" class="hidden">
                <option value="hourly"   <?php selected( $update_frequency, 'hourly',   true ); ?>><?php esc_html_e( 'Hourly',  'blokhausre-property-up' ); ?></option>
                <option value="daily"    <?php selected( $update_frequency, 'daily',    true ); ?>><?php esc_html_e( 'Daily',   'blokhausre-property-up' ); ?></option>
                <option value="weekly"   <?php selected( $update_frequency, 'weekly',   true ); ?>><?php esc_html_e( 'Weekly',  'blokhausre-property-up' ); ?></option>
                <option value="biweekly" <?php selected( $update_frequency, 'biweekly', true ); ?>><?php esc_html_e( 'Biweekly','blokhausre-property-up' ); ?></option>
                <option value="monthly"  <?php selected( $update_frequency, 'monthly',  true ); ?>><?php esc_html_e( 'Monthly', 'blokhausre-property-up' ); ?></option>
            </select>
        </label>
        
        <p style="display:none;">
            <?php esc_html_e( 'Specify the frequency when to update properties', 'blokhausre-property-up' ); ?>
        </p>
        <?php
    } 


    /**
     * Updated option callback.
     *
     * @since   1.0.0
     *
     * @param string $old_value
     * @param string $new_value
     */
     public static function updated_option( $old_value, $new_value ) {

        if ( $old_value != $new_value ) {

            wp_clear_scheduled_hook( 'blokhaus_properties_update' );
            wp_clear_scheduled_hook( 'blokhaus_condos_update' );            

            $api_key = isset( $new_value['api_key'] ) ? $new_value['api_key'] : ( isset( $old_value['api_key'] ) ? $old_value['api_key'] : '' );

            $limit_query = isset( $new_value['limit_query'] ) ? $new_value['limit_query'] : ( isset( $old_value['limit_query'] ) ? $old_value['limit_query'] : '' );

            if ( ! empty( $api_key ) ) {

                $interval = isset( $new_value['update_interval'] ) ? $new_value['update_interval'] : ( isset( $old_value['update_interval'] ) ? $old_value['update_interval'] : 'daily' );
                echo '<pre>'.$api_key.'</pre>';
                BLOKHAUSRE_Cron::BLOKHAUSRE_schedule_updates($api_key, $interval, $limit_query);
                BLOKHAUSRE_Condos_Cron::BLOKHAUSRE_schedule_condos_updates($api_key, $interval, $limit_query);
                BLOKHAUSRE_Featured_Cron::BLOKHAUSRE_schedule_featured_updates($api_key, $interval, $limit_query);
            }

        }

    } 



    /**
     * Returns settings.
     *
     * @since  1.0.0
     * @access public
     * @param  string  $setting
     * @return mixed
     */
    public static function get_setting( $setting ) {

        $defaults = self::get_default_settings();
        $settings = wp_parse_args( get_option('blokhausre_api_settings', $defaults ), $defaults );

        return isset( $settings[ $setting ] ) ? $settings[ $setting ] : false;
    }

    /**
     * Returns the default settings for the plugin.
     *
     * @since  1.0.0
     * @access public
     * @return array
     */
    public static function get_default_settings() {

        $settings = array(
            'api_key' => '103a9efb0c687b2b6af63cc6f3f4177c',
            'update_interval' => 'daily',
            'limit_query' => '200',
        );

        return $settings;
    }

}