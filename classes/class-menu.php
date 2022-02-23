<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Blokhausre_Menu {

    public $slug = 'blokhaus-real-estate';
    public $capability = 'edit_posts';
    public static $instance;

    public function __construct() {

        add_action( 'admin_menu', array( $this, 'setup_menu' ) );
    }

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setup_menu() {

        $wv_submenus = array();

        $menu_name = apply_filters('blokhausre_realestate_menu_label', esc_html__( 'Properties MLS', 'blokhausre-theme-functionality' ));
        add_menu_page(
            $menu_name,
            $menu_name,
            $this->capability,
            $this->slug,
            '',
            BLOKHAUSRE_PLUGIN_IMAGES_URL. 'houzez-icon.svg',
            '5'
        );

        $wv_submenus['addnew'] = array(
            $this->slug,
            esc_html__( 'Add New Property', 'blokhausre-theme-functionality' ),
            esc_html__( 'New Property', 'blokhausre-theme-functionality' ),
            $this->capability,
            'post-new.php?post_type=propertymls',
        );

        // Property post type taxonomies
        $taxonomies = get_object_taxonomies( 'propertymls', 'objects' );
        foreach ( $taxonomies as $single_tax ) {
            $wv_submenus[ $single_tax->name ] = array(
                $this->slug,
                $single_tax->labels->add_new_item,
                $single_tax->labels->name,
                $this->capability,
                'edit-tags.php?taxonomy=' . $single_tax->name . '&post_type=propertymls',
            );
        }

        $wv_submenus['blokhausre_api_settings'] = array(
            $this->slug,
            esc_html__( 'API Settings', 'blokhausre-theme-functionality' ),
            esc_html__( 'API Settings', 'blokhausre-theme-functionality' ),
            $this->capability,
            'blokhausre_api_settings',
            array( 'BLOKHAUSRE_API_Settings', 'render' )
        );

        

        // Add filter for third party scripts
        $wv_submenus = apply_filters( 'blokhausre_admin_realestate_menu', $wv_submenus );

        if ( $wv_submenus ) {
            foreach ( $wv_submenus as $sub_menu ) {
                call_user_func_array( 'add_submenu_page', $sub_menu );
            }
        } // end $wv_submenus
    }

}