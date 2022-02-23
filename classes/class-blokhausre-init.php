


<?php
/**
 * Class Blokhausre
 * Created by KAMBDA.
 * Author: Wuilly Vargas
 * Date: 28/10/2021
 * Time: 10:16 AM
 */
class Blokhausre {

    /**
     * Plugin instance.
     *
     * @var Blokhausre
     */
    protected static $instance;


    /**
     * Plugin version.
     *
     * @var string
     */
    protected static $version = '2.0.7';


    /**
     * Constructor.
     */
    protected function __construct()
    {   
        $this->actions();
        $this->init();
        $this->filters();

        do_action( 'blokhausre_core' ); 
    }

    /**
     * Return plugin version.
     *
     * @return string
     */
    public static function getVersion() {
        return static::$version;
    }

    /**
     * Return plugin instance.
     *
     * @return Houzez
     */
    protected static function getInstance() {
        return is_null( static::$instance ) ? new Blokhausre() : static::$instance;
    }

    /**
     * Initialize plugin.
     *
     * @return void
     */
    public static function run() {
        self::blokhausre_function_loader();
        self::blokhausre_class_loader();
        static::$instance = static::getInstance();
    }



    /**
     * Include admin files conditionally.
     */
    public function conditional_includes() {
        $screen = get_current_screen();

        if ( ! $screen ) {
            return;
        }

        switch ( $screen->id ) {
            case 'page':
                
                break;
        }
    }


    /**
     * Plugin actions.
     *
     * @return void
     */
    public function actions() {

    }

    /**
     * Add filters to the WordPress functionality.
     *
     * @return void
     */
    public function filters() {
        
    }

    public static function blokhausre_clean_meta_fields20($string) {
       $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

       return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }


    public static function blokhausre_ucfd_dummy() {
        $all_fields = Blokhausre_Fields_Builder::get_form_fields();

        if(!empty($all_fields)) {
            foreach ( $all_fields as $value ) {
                $id = $value->id;
                $slug = self::blokhausre_clean_meta_fields20($value->field_id);
                self::blokhausre_update_meta_key($value->field_id, $slug);
                self::blokhausre_update_cf($id, $slug);

                update_option('blokhausre_custom_fields_update', true);
            }
        }
    }

    public static function blokhausre_update_meta_key( $old_key=null, $new_key=null ){
        global $wpdb;

        $old_key = 'mls_'.$old_key;
        $new_key = 'mls_'.$new_key;

        $query = "UPDATE ".$wpdb->prefix."postmeta SET meta_key = '".$new_key."' WHERE meta_key = '".$old_key."'";
        $wpdb->query($query);
    }

    public static function blokhausre_update_cf($id, $slug) {
        global $wpdb;
        $query = "UPDATE ".$wpdb->prefix."blokhausre_fields_builder SET field_id = '".$slug."' WHERE id =".$id;
        $wpdb->query($query);
    }


    /**
     * Initialize classes
     *
     * @return void
     */
    public function init() {
        
        Blokhausre_Post_Type_Property::init();

        BLOKHAUSRE_Cron::init();
        BLOKHAUSRE_Condos_Cron::init();
        BLOKHAUSRE_Featured_Cron::init();
        
        if( is_admin() ) {

            BLOKHAUSRE_API_Settings::init();
            Blokhausre_Menu::instance();

            BLOKHAUSRE_mls::init();
            BLOKHAUSRE_Condos_mls::init();
            BLOKHAUSRE_Featured_mls::init();

            if(isset($_GET['fcc-update']) && $_GET['fcc-update'] == 1) {
                BLOKHAUSRE_mls::update();
                BLOKHAUSRE_Condos_mls::update();
                BLOKHAUSRE_Featured_mls::update();
            }
        }

        add_action( 'admin_enqueue_scripts', array( __CLASS__ , 'admin_enqueue_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__ , 'frontend_enqueue_scripts' ) );

    }

    public static function admin_enqueue_scripts() {
        $js_path = 'assets/admin/js/';
        $css_path = 'assets/admin/css/';

        wp_enqueue_style('blokhausre-admin-style', BLOKHAUSRE_PLUGIN_URL . $css_path . 'style.css', array(), BLOKHAUSRE_VERSION, 'all');
    }

    public static function frontend_enqueue_scripts() {
        $js_path = 'assets/frontend/js/';
        $css_path = 'assets/frontend/css/';
        wp_enqueue_style('wv-frontend-style',BLOKHAUSRE_PLUGIN_URL.$css_path.'wv-style.css',array());
        wp_enqueue_script('wv-jquery','//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js',array());
        wp_enqueue_script('wv-frontend-script',BLOKHAUSRE_PLUGIN_URL.$js_path.'wv-script.js',array());
        wp_localize_script( 'wv-ajax-script', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) ); // setting ajaxurl
    }


    /**
     * Load plugin files.
     *
     * @return void
     */
    public static function blokhausre_class_loader()
    {
        $files = apply_filters( 'blokhausre_class_loader', array(
            BLOKHAUSRE_PLUGIN_PATH . '/classes/class-property-post-type.php',
            BLOKHAUSRE_PLUGIN_PATH . '/classes/class-mls.php',
            BLOKHAUSRE_PLUGIN_PATH . '/classes/class-cron.php',
            BLOKHAUSRE_PLUGIN_PATH . '/classes/class-api-settings.php',
            BLOKHAUSRE_PLUGIN_PATH . '/classes/class-menu.php',

            BLOKHAUSRE_PLUGIN_PATH . '/classes/class-condos-mls.php',
            BLOKHAUSRE_PLUGIN_PATH . '/classes/class-condos-cron.php',

            BLOKHAUSRE_PLUGIN_PATH . '/classes/class-featured-mls.php',
            BLOKHAUSRE_PLUGIN_PATH . '/classes/class-featured-cron.php',
        ) );

        foreach ( $files as $file ) {
            if ( file_exists( $file ) ) {
                include $file;
            }
        }
    }


    public static function blokhausre_function_loader() {
        $files = apply_filters( 'blokhausre_function_loader', array(
            BLOKHAUSRE_PLUGIN_PATH . '/functions/functions.php',
            BLOKHAUSRE_PLUGIN_PATH . '/functions/price_functions.php',
            BLOKHAUSRE_PLUGIN_PATH . '/functions/helper_functions.php',
            BLOKHAUSRE_PLUGIN_PATH . '/functions/property_functions.php',
        ) );

        foreach ( $files as $file ) {
            if ( file_exists( $file ) ) {
                require_once $file;
            }
        }
    }


    /**
     * Comma separated taxonomy terms with admin side links
     *
     * @return boolean | term
     */
    public static function admin_taxonomy_terms( $post_id, $taxonomy, $post_type ) {

        $terms = get_the_terms( $post_id, $taxonomy );

        if ( ! empty ( $terms ) ) {
            $out = array();
            /* Loop through each term, linking to the 'edit posts' page for the specific term. */
            foreach ( $terms as $term ) {
                $out[] = sprintf( '<a href="%s">%s</a>',
                    esc_url( add_query_arg( array( 'post_type' => $post_type, $taxonomy => $term->slug ), 'edit.php' ) ),
                    esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, $taxonomy, 'display' ) )
                );
            }
            /* Join the terms, separating them with a comma. */
            return join( ', ', $out );
        }

        return false;
    }

    public static function admin_get_post_meta($post_id, $key, $bool){
        $value = get_post_meta($post_id, $key, $bool);
        if(!empty($value)){
            return esc_attr( $value );
        }else{
            return 'NA';
        } 
    }


    /*
    * Render Form fields
    */
    public static function render_form_field( $label, $field_name, $type, $options = array() )
    {
        $template = '<div class="form-field">
                        <label>%s</label>
                        %s
                    </div>';

        $template = apply_filters( 'blokhausre_form_fields_template', $template, $label, $options );

        $options_string = null;
        $options['name'] = $field_name;
        $options['value'] = ! empty( $options['value'] ) ? $options['value'] : false;

        $multiple_options = isset( $options['options'] ) ? $options['options'] : '';
        unset($options['options']);
        
        foreach ( $options as $key => $value ) {
            if ( is_array( $value ) || ! $value ) continue;


            $options_string .= $key . '="' . $value . '" ';
        }

        switch ( $type ) {
            case 'checkbox':
                $field = "<input type='hidden' name='{$field_name}' value='0'/>
                          <input type='checkbox' {$options_string}>";
                break;

            case 'list':
            case 'select':
            case 'selectbox':
                $field = "<select {$options_string}>";

                if ( ! empty( $options['placeholder'] ) ) {
                    $field .= '<option value="">' . $options['placeholder'] . '</option>';
                }

                if ( ! empty( $options['values'] ) ) {
                    foreach ( $options['values'] as $pvalue => $plabel ) {
                        $field .= '<option value="' . $pvalue . '" '. selected( $pvalue, $options['value'], false ) .'>' .
                            ( is_string( $plabel ) ? $plabel : $plabel['label'] )
                            . '</option>';
                    }
                }

                $field .= '</select>';

                break;

            case 'textarea':
                    
                    $field = "<textarea type='" . $type . "' {$options_string}>".$multiple_options."</textarea>";
                    
                    break;    

            default:
                $field = "<input type='" . $type . "' {$options_string}>";
        }

        $template = sprintf( $template, $label, $field );

        return $template;
    }


    public static function blokhausre_plugin_activation(){
        
        BLOKHAUSRE_Cron::BLOKHAUSRE_schedule_updates();
        BLOKHAUSRE_Condos_Cron::BLOKHAUSRE_schedule_condos_updates();
        BLOKHAUSRE_Featured_Cron::BLOKHAUSRE_schedule_featured_updates();

        if (!wp_next_scheduled('mls_check_new_listing_action_hook')) {
            wp_schedule_event(time(), 'daily', 'mls_check_new_listing_action_hook');
        }

        if (!wp_next_scheduled('mls_check_new_listing_action_hook')) {
           wp_schedule_event(time(), 'weekly', 'mls_check_new_listing_action_hook');
        }

        update_option( 'elementor_disable_typography_schemes', 'yes' );
        update_option( 'elementor_disable_color_schemes', 'yes' );

    }

    public static function blokhausre_plugin_deactivate(){

        wp_clear_scheduled_hook('blokhausre_check_new_listing_action_hook');
        wp_clear_scheduled_hook( 'favethemes_currencies_update' );

    }

    public function redirect($plugin) {
        if ( $plugin == BLOKHAUSRE_PLUGIN_BASENAME ) {
            wp_redirect( 'admin.php?page=blokhausre_dashboard' );
            wp_die();
        }
    }

}
?>