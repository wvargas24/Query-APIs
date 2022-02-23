<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Blokhausre_Post_Type_Property {
    /**
     * Initialize custom post type
     *
     * @access public
     * @return void
     */
    public static function init() {
        

        // Add form.
        add_action( 'init', array( __CLASS__, 'Blokhausre_definition' ) );
        add_action( 'init', array( __CLASS__, 'Blokhausre_property_type' ) );
        add_action( 'init', array( __CLASS__, 'Blokhausre_property_status' ) );
        add_action( 'init', array( __CLASS__, 'Blokhausre_property_features' ) );
        add_action( 'init', array( __CLASS__, 'Blokhausre_property_label' ) );
        
        add_action( 'init', array( __CLASS__, 'Blokhausre_property_country' ) );
        add_action( 'init', array( __CLASS__, 'Blokhausre_property_state' ) );
        add_action( 'init', array( __CLASS__, 'Blokhausre_property_city' ) );
        add_action( 'init', array( __CLASS__, 'Blokhausre_property_area' ) );

        add_action( 'created_term', array( __CLASS__, 'Blokhausre_save_taxonomies_fields' ), 10, 3 );
        add_action( 'edit_term', array( __CLASS__, 'Blokhausre_save_taxonomies_fields' ), 10, 3 );

        add_action('admin_init', array( __CLASS__, 'Blokhausre_approve_listing' ));
        add_action('admin_init', array( __CLASS__, 'Blokhausre_expire_listing' ));

        add_action('restrict_manage_posts', array( __CLASS__, 'Blokhausre_admin_property_type_filter' ));
        add_filter('parse_query', array( __CLASS__, 'Blokhausre_convert_property_type_to_term_in_query' ));

        add_action('restrict_manage_posts', array( __CLASS__, 'Blokhausre_admin_property_status_filter' ));
        add_filter('parse_query', array( __CLASS__, 'Blokhausre_convert_property_status_to_term_in_query' ));

        add_action('restrict_manage_posts', array( __CLASS__, 'Blokhausre_admin_property_city_filter' ));
        add_filter('parse_query', array( __CLASS__, 'Blokhausre_convert_property_city_to_term_in_query' ));

        //add_action( 'save_post_property', array( __CLASS__, 'Blokhausre_save_property_post_type' ), 10, 3 );
        add_action( 'added_post_meta', array( __CLASS__, 'Blokhausre_save_property_post_type' ), 10, 4 );
        add_action( 'updated_post_meta', array( __CLASS__, 'Blokhausre_save_property_post_type' ), 10, 4 );

        add_filter( 'manage_edit-propertymls_columns', array( __CLASS__, 'Blokhausre_custom_columns' ) );
        add_action( 'manage_propertymls_posts_custom_column', array( __CLASS__, 'Blokhausre_custom_columns_manage' ) );

        add_filter('manage_edit-propertymls_area_columns', array( __CLASS__, 'Blokhausre_propertyArea_columns_head' ));
        add_filter('manage_propertymls_area_custom_column',array( __CLASS__, 'Blokhausre_propertyArea_columns_content_taxonomy' ), 10, 3);

        add_filter('manage_edit-propertymls_city_columns', array( __CLASS__, 'Blokhausre_propertyCity_columns_head' ));
        add_filter('manage_propertymls_city_custom_column',array( __CLASS__, 'Blokhausre_propertyCity_columns_content_taxonomy' ), 10, 3);

        add_filter('manage_edit-propertymls_state_columns', array( __CLASS__, 'Blokhausre_propertyState_columns_head' ));
        add_filter('manage_propertymls_state_custom_column',array( __CLASS__, 'Blokhausre_propertyState_columns_content_taxonomy' ), 10, 3);

        if(is_admin() && isset($_GET['post_type']) && $_GET['post_type'] == 'propertymls') {
            add_filter( 'manage_edit-propertymls_sortable_columns', array( __CLASS__, 'Blokhausre_sortable_columns' ) );

            add_action('restrict_manage_posts', array( __CLASS__, 'Blokhausre_admin_property_id_field' ));
            add_filter('pre_get_posts', array( __CLASS__, 'Blokhausre_property_admin_custom_query' ));

        }
    }


    
    /**
     * Save category fields
     *
     * @param mixed  $term_id Term ID being saved.
     * @param mixed  $tt_id Term taxonomy ID.
     * @param string $taxonomy Taxonomy slug.
     */
    public static function Blokhausre_save_taxonomies_fields( $term_id, $tt_id = '', $taxonomy = '' ) {

        
        if ( isset( $_POST['mls'] ) && 'propertymls_city' === $taxonomy ) { // WPCS: CSRF ok, input var ok.
    
            $mls_meta = array();
            $mls_meta['parent_state'] = isset( $_POST['mls']['parent_state'] ) ? esc_attr($_POST['mls']['parent_state']) : '';
            update_option( '_mls_property_city_'.$term_id, $mls_meta );
        }

        if ( isset( $_POST['mls'] ) && 'propertymls_state' === $taxonomy ) {

            $mls_meta = array();
            $mls_meta['parent_country'] = isset( $_POST['mls']['parent_country'] ) ? $_POST['mls']['parent_country'] : '';

            update_option( '_mls_property_state_'.$term_id, $mls_meta );
        }

        if ( isset( $_POST['mls'] ) && 'propertymls_area' === $taxonomy ) {

            $mls_meta = array();

            $mls_meta['parent_city'] = isset( $_POST['mls']['parent_city'] ) ? $_POST['mls']['parent_city'] : '';

            update_option( '_mls_property_area_'.$term_id, $mls_meta );
        }

        if ( isset( $_POST['mls'] ) && 'propertymls_type' === $taxonomy ) {

            $mls_meta = array();

            $mls_meta['color'] = isset( $_POST['mls']['color'] ) ? $_POST['mls']['color'] : 0;
            $mls_meta['color_type'] = isset( $_POST['mls']['color_type'] ) ? $_POST['mls']['color_type'] : 0;

            update_option( '_mls_property_type_'.$term_id, $mls_meta );

            if ( $mls_meta['color_type'] == 'custom' ) {
                blokhausre_update_recent_colors20( $mls_meta['color'] );
            }

            blokhausre_update_property_type_colors20( $term_id, $mls_meta['color'], $mls_meta['color_type'] );

            if( isset($_POST['mls_marker_icon']) && !empty($_POST['mls_marker_icon'][0]) ) {
                update_term_meta($term_id, 'mls_marker_icon', intval($_POST['mls_marker_icon'][0]) ); 
            }

            if( isset($_POST['mls_marker_retina_icon']) && !empty($_POST['mls_marker_retina_icon'][0]) ) {
                update_term_meta($term_id, 'mls_marker_retina_icon', intval($_POST['mls_marker_retina_icon'][0]) ); 
            }
            


        }

        if ( isset( $_POST['mls'] ) && 'propertymls_status' === $taxonomy ) {

            $mls_meta = array();

            $mls_meta['color'] = isset( $_POST['mls']['color'] ) ? $_POST['mls']['color'] : 0;
            $mls_meta['color_type'] = isset( $_POST['mls']['color_type'] ) ? $_POST['mls']['color_type'] : 0;

            update_option( '_mls_property_status_'.$term_id, $mls_meta );

            if ( $mls_meta['color_type'] == 'custom' ) {
                blokhausre_update_recent_colors20( $mls_meta['color'] );
            }

            blokhausre_update_property_status_colors20( $term_id, $mls_meta['color'], $mls_meta['color_type'] );
        }

        if ( isset( $_POST['mls'] ) && 'propertymls_label' === $taxonomy ) {

            $mls_meta = array();

            $mls_meta['color'] = isset( $_POST['mls']['color'] ) ? $_POST['mls']['color'] : 0;
            $mls_meta['color_type'] = isset( $_POST['mls']['color_type'] ) ? $_POST['mls']['color_type'] : 0;

            update_option( '_mls_property_label_'.$term_id, $mls_meta );

            if ( $mls_meta['color_type'] == 'custom' ) {
                blokhausre_update_recent_colors20( $mls_meta['color'] );
            }

            blokhausre_update_property_label_colors20( $term_id, $mls_meta['color'], $mls_meta['color_type'] );
        }

        if( isset($_POST['mls_taxonomy_img']) && !empty($_POST['mls_taxonomy_img'][0]) ) {
            update_term_meta($term_id, 'mls_taxonomy_img', intval($_POST['mls_taxonomy_img'][0]) ); 
        }

        if( isset($_POST['mls_prop_taxonomy_custom_link']) && !empty($_POST['mls_prop_taxonomy_custom_link']) ) {
            update_term_meta($term_id, 'mls_prop_taxonomy_custom_link', esc_url($_POST['mls_prop_taxonomy_custom_link']) ); 
        }

        if ( isset( $_POST['mls_prop_features_icon'] ) && 'property_feature' === $taxonomy ) {
            update_term_meta($term_id, 'mls_prop_features_icon', esc_attr($_POST['mls_prop_features_icon']) );
        }

        if ( isset( $_POST['mls_feature_icon_type'] ) && 'property_feature' === $taxonomy ) {
            update_term_meta($term_id, 'mls_feature_icon_type', esc_attr($_POST['mls_feature_icon_type']) );
        }

        if ( isset( $_POST['mls_feature_img_icon'] ) && !empty($_POST['mls_feature_img_icon'][0]) ) {
            update_term_meta($term_id, 'mls_feature_img_icon', esc_url($_POST['mls_feature_img_icon'][0]) );
        }
    }

    /**
     * Custom post type definition
     *
     * @access public
     * @return void
     */
    public static function Blokhausre_definition() {
        $labels = array(
            'name' => __( 'Properties MLS','mls-query-api'),
            'singular_name' => __( 'Property','mls-query-api' ),
            'add_new' => __('Add New Property','mls-query-api'),
            'add_new_item' => __('Add New','mls-query-api'),
            'edit_item' => __('Edit Property','mls-query-api'),
            'new_item' => __('New Property','mls-query-api'),
            'view_item' => __('View Property','mls-query-api'),
            'search_items' => __('Search Property','mls-query-api'),
            'not_found' =>  __('No Property found','mls-query-api'),
            'not_found_in_trash' => __('No Property found in Trash','mls-query-api'),
            'parent_item_colon' => ''
          );

        $labels = apply_filters( 'blokhausre_post_type_labels_property', $labels );

        $args = array(
            'labels'            => $labels,
            'public'            => true,
            'publicly_queryable'=> true,
            'show_ui'           => true,
            'show_in_menu'      => 'blokhaus-real-estate',
            'query_var'         => true,
            'has_archive'       => true,
            'capability_type'   => 'post',
            'map_meta_cap'      => true,
            'hierarchical'      => false,
            'menu_icon'         => 'dashicons-location',
            'menu_position'     => 5,
            'can_export'        => true,
            'show_in_rest'      => true,
            'rest_base'         => apply_filters( 'blokhausre_property_rest_base', __( 'propertiesmls', 'houzez-theme-functionality' ) ),
            'supports'          => array('title','editor','thumbnail','revisions','author','page-attributes','excerpt', 'custom-fields'),
            'rewrite'            => array( 'slug' => 'singleproperty' ),
        );

        $args = apply_filters( 'blokhausre_post_type_args_property', $args );

        register_post_type('propertymls',$args);
    }


    public static function Blokhausre_property_type() {

        $type_labels = array(
                    'name'              => __('Type','mls-query-api'),
                    'add_new_item'      => __('Add New Type','mls-query-api'),
                    'new_item_name'     => __('New Type','mls-query-api')
        );
        $type_labels = apply_filters( 'mls_type_labels', $type_labels );

        $post_type = apply_filters('mls_property_type_post_type_filter', array('propertymls'));

        $args =  array(
                'labels'                => $type_labels,
                'hierarchical'          => true,
                'query_var'             => true,
                'show_in_rest'          => true,
                'rest_base'             => 'propertymls_type',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'               => array( 'slug' => 'propertytype' ),
            );
        $args = apply_filters('mls_property_type_tax_args_filter', $args);
        register_taxonomy('propertymls_type', $post_type, $args);
    }

    public static function Blokhausre_property_status() {

        $status_labels = array(
                    'name'              => __('Status','mls-query-api'),
                    'add_new_item'      => __('Add New Status','mls-query-api'),
                    'new_item_name'     => __('New Status','mls-query-api')
        );
        $status_labels = apply_filters( 'mls_status_labels', $status_labels );

        $post_type = apply_filters('mls_property_status_post_type_filter', array('propertymls'));

        $args = array(
                'labels' => $status_labels,
                'hierarchical'  => true,
                'query_var'     => true,
                'show_in_rest'          => true,
                'rest_base'             => 'propertymls_status',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'               => array( 'slug' => 'propertystatus' ),
            );
        $args = apply_filters('mls_property_status_tax_args_filter', $args);
        register_taxonomy('propertymls_status', $post_type, $args);
        
    }

    public static function Blokhausre_property_features() {

        $features_labels = array(
                    'name'              => __('Features','mls-query-api'),
                    'add_new_item'      => __('Add New Feature','mls-query-api'),
                    'new_item_name'     => __('New Feature','mls-query-api')
        );
        $features_labels = apply_filters( 'mls_features_labels', $features_labels );

        $post_type = apply_filters('mls_property_features_post_type_filter', array('propertymls'));

        $args = array(
                'labels' => $features_labels,
                'hierarchical'  => true,
                'query_var'     => true,
                'show_in_rest'          => true,
                'rest_base'             => 'propertymls_feature',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'               => array( 'slug' => 'propertyfeature' ),
            );

        $args = apply_filters('mls_property_features_tax_args_filter', $args);
        register_taxonomy('propertymls_feature', $post_type, $args);
    }

    public static function Blokhausre_property_label() {

        $label_labels = array(
                    'name'              => __('Labels', 'mls-query-api'),
                    'add_new_item'      => __('Add New Label','mls-query-api'),
                    'new_item_name'     => __('New Label','mls-query-api')
        );
        $label_labels = apply_filters( 'mls_label_labels', $label_labels );

        $post_type = apply_filters('mls_property_label_post_type_filter', array('propertymls'));

        $args = array(
                'labels' => $label_labels,
                'hierarchical'  => true,
                'query_var'     => true,
                'show_in_rest'          => true,
                'rest_base'             => 'propertymls_label',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'               => array( 'slug' => 'propertylabel' ),
            );

        $args = apply_filters('mls_property_label_tax_args_filter', $args);

        register_taxonomy('propertymls_label', $post_type, $args);
        
    }

    public static function Blokhausre_property_city() {

        $city_labels = array(
                    'name'              => __('City','mls-query-api'),
                    'add_new_item'      => __('Add New City','mls-query-api'),
                    'new_item_name'     => __('New City','mls-query-api')
        );
        $city_labels = apply_filters( 'mls_city_labels', $city_labels );

        $post_type = apply_filters('mls_property_city_post_type_filter', array('propertymls'));

        $args = array(
                'labels' => $city_labels,
                'hierarchical'  => true,
                'query_var'     => true,
                'show_in_rest'          => true,
                'rest_base'             => 'propertymls_city',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'               => array( 'slug' => 'propertycity' ),
            );

        $args = apply_filters('mls_property_city_tax_args_filter', $args);
        register_taxonomy('propertymls_city', $post_type, $args);

    }

    public static function Blokhausre_property_area() {

        $area_labels = array(
                    'name'              => __('Area','mls-query-api'),
                    'add_new_item'      => __('Add New Area','mls-query-api'),
                    'new_item_name'     => __('New Area','mls-query-api')
        );
        $area_labels = apply_filters( 'mls_area_labels', $area_labels );

        $post_type = apply_filters('mls_property_area_post_type_filter', array('propertymls'));
        $args = array(
                'labels' => $area_labels,
                'hierarchical'  => true,
                'query_var'     => true,
                'show_in_rest'          => true,
                'rest_base'             => 'propertymls_area',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'               => array( 'slug' => 'propertyarea' ),
            );

        $args = apply_filters('mls_property_area_tax_args_filter', $args);

        register_taxonomy('propertymls_area', $post_type, $args);
    }


    public static function Blokhausre_property_country() {

        $property_country_labels = array(
                    'name'              => __('Country','mls-query-api'),
                    'add_new_item'      => __('Add New Country','mls-query-api'),
                    'new_item_name'     => __('New Country','mls-query-api')
        );
        $property_country_labels = apply_filters( 'mls_country_labels', $property_country_labels );

        $post_type = apply_filters('mls_property_country_post_type_filter', array('propertymls'));

        $args = array(
                'labels' => $property_country_labels,
                'hierarchical'  => true,
                'query_var'     => true,
                'show_in_rest'          => true,
                'rest_base'             => 'propertymls_country',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'               => array( 'slug' => 'propertycountry' ),
            );

        $args = apply_filters('mls_property_country_tax_args_filter', $args);

        register_taxonomy('propertymls_country', $post_type, $args);
    }

    public static function Blokhausre_property_state() {

        $property_state_labels = array(
                    'name'              => __('State','mls-query-api'),
                    'add_new_item'      => __('Add New State','mls-query-api'),
                    'new_item_name'     => __('New State','mls-query-api')
        );
        $property_state_labels = apply_filters( 'mls_state_labels', $property_state_labels );

        $post_type = apply_filters('mls_property_state_post_type_filter', array('propertymls'));

        $args = array(
                'labels' => $property_state_labels,
                'hierarchical'  => true,
                'query_var'     => true,
                'show_in_rest'          => true,
                'rest_base'             => 'propertymls_state',
                'rest_controller_class' => 'WP_REST_Terms_Controller',
                'rewrite'               => array( 'slug' => 'propertystate' ),
            );

        $args = apply_filters('mls_property_state_tax_args_filter', $args);

        register_taxonomy('propertymls_state', $post_type, $args);
    }

    public static function blokhausre_get_property_capabilities() {

        $caps = array(
            // meta caps (don't assign these to roles)
            'edit_post'              => 'edit_property',
            'read_post'              => 'read_property',
            'delete_post'            => 'delete_property',

            // primitive/meta caps
            'create_posts'           => 'create_properties',

            // primitive caps used outside of map_meta_cap()
            'edit_posts'             => 'edit_properties',
            'edit_others_posts'      => 'edit_others_properties',
            'publish_post'           => 'publish_properties',
            'read_private_posts'     => 'read_private_properties',

            // primitive caps used inside of map_meta_cap()
            'read'                   => 'read',
            'delete_posts'           => 'delete_properties',
            'delete_private_posts'   => 'delete_private_properties',
            'delete_published_posts' => 'delete_published_properties',
            'delete_others_posts'    => 'delete_others_properties',
            'edit_private_posts'     => 'edit_private_properties',
            'edit_published_posts'   => 'edit_published_properties'
        );

        return apply_filters( 'blokhausre_get_property_capabilities', $caps );
    }


    /**
     * Custom admin columns for post type
     *
     * @access public
     * @return array
     */
    public static function Blokhausre_custom_columns() {
        $columns = array(
            "cb" => "<input type=\"checkbox\" />",            
            "title" => __( 'Title','mls-query-api' ),
            "thumbnail" => __( 'Thumbnail','mls-query-api' ),            
            'area' => __( 'Area','mls-query-api' ),
            'city' => __( 'City','mls-query-api' ),
            "type" => __('Type','mls-query-api'),
            "status" => __('Status','mls-query-api'),
            "price" => __('Price','mls-query-api'),
            "id" => __( 'Property ID','mls-query-api' ),
            "featured" => __( 'Featured','mls-query-api' ),
            "listing_posted" => __( 'Posted','mls-query-api' ),
            "listing_expiry" => __( 'Expires','mls-query-api' ),
            "blokhausre_actions" => __( 'Actions','mls-query-api' ),
            "prop_id" => __( 'ID','mls-query-api' ),
        );

        $columns = apply_filters( 'mls_custom_post_property_columns', $columns );

        if ( is_rtl() ) {
            $columns = array_reverse( $columns );
        }

        return $columns;
        
    }

    /**
     * Custom admin columns implementation
     *
     * @access public
     * @param string $column
     * @return array
     */
    public static function Blokhausre_custom_columns_manage( $column ) {
        global $post;
        $blokhausre_prefix = 'mls_';
        switch ($column)
        {
            case 'thumbnail':
                if ( has_post_thumbnail() ) {
                    the_post_thumbnail( array(75, 75), array(
                        'class' => 'attachment-thumbnail attachment-thumbnail-small',
                    ) );
                } else {
                    echo '-';
                }
                break;
            case 'id':
                echo Blokhausre::admin_get_post_meta( $post->ID, $blokhausre_prefix.'property_id', true);
                break;
            case 'prop_id':
                echo get_the_ID();
                break;
            case 'featured':
                $featured = get_post_meta($post->ID, $blokhausre_prefix.'featured',true);
                if($featured != 1 ) {
                    _e( 'No', 'mls-query-api' );
                } else {
                    _e( 'Yes', 'mls-query-api' );
                }
                break;
            case 'area':
                echo Blokhausre::admin_taxonomy_terms ( $post->ID, 'propertymls_area', 'propertymls' );
                break;
            case 'city':
                echo Blokhausre::admin_taxonomy_terms ( $post->ID, 'propertymls_city', 'propertymls' );
                break;
            case 'address':
                $address = get_post_meta($post->ID, $blokhausre_prefix.'propertymls_address',true);
                if(!empty($address)){
                    echo esc_attr( $address );
                }else{
                    _e('No Address Provided!','mls-query-api');
                }
                break;
            case 'type':
                echo Blokhausre::admin_taxonomy_terms ( $post->ID, 'propertymls_type', 'propertymls' );
                break;
            case 'status':
                echo Blokhausre::admin_taxonomy_terms ( $post->ID, 'propertymls_status', 'propertymls' );
                break;
            case 'price':
                if( function_exists('blokhausre_property_price_admin')) {
                    blokhausre_property_price_admin();
                }
                break;
            case 'bed':
                $bed = get_post_meta($post->ID, $blokhausre_prefix.'property_bedrooms',true);
                if(!empty($bed)){
                    echo esc_attr( $bed );
                }else{
                    _e('NA','mls-query-api');
                }
                break;
            case 'bath':
                $bath = get_post_meta($post->ID, $blokhausre_prefix.'property_bathrooms',true);
                if(!empty($bath)){
                    echo esc_attr( $bath );
                }else{
                    _e('NA','mls-query-api');
                }
                break;
            case 'garage':
                $garage = get_post_meta($post->ID, $blokhausre_prefix.'property_garage',true);
                if(!empty($garage)){
                    echo esc_attr( $garage );
                }else{
                    _e('NA','mls-query-api');
                }
                break;
            case 'features':
                echo get_the_term_list($post->ID,'property-feature', '', ', ','');
                break;
            case 'blokhausre_actions':
                echo '<div class="actions">';

                $admin_actions = apply_filters( 'post_row_actions', array(), $post );

                $user = wp_get_current_user();

                if ( in_array( $post->post_status, array( 'pending' ) ) && in_array( 'administrator', (array) $user->roles ) ) {
                    $admin_actions['approve']   = array(
                        'action'  => 'approve',
                        'name'    => __( 'Approve', 'mls-query-api' ),
                        'url'     =>  wp_nonce_url( add_query_arg( 'approve_listing', $post->ID ), 'approve_listing' )
                    );
                }
                if ( in_array( $post->post_status, array( 'publish', 'pending' ) ) && in_array( 'administrator', (array) $user->roles ) ) {
                    $admin_actions['expire']   = array(
                        'action'  => 'expire',
                        'name'    => __( 'Expire', 'mls-query-api' ),
                        'url'     =>  wp_nonce_url( add_query_arg( 'expire_listing', $post->ID ), 'expire_listing' )
                    );
                }
                
                $admin_actions = apply_filters( 'blokhausre_admin_actions', $admin_actions, $post );

                foreach ( $admin_actions as $action ) {
                    if ( is_array( $action ) ) {
                        printf( '<a class="button button-icon tips icon-%1$s" href="%2$s" data-tip="%3$s">%4$s</a>', $action['action'], esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_html( $action['name'] ) );
                    } else {
                        
                    }
                }

                echo '</div>';

                break;
            case "listing_posted" :
                echo '<p>' . date_i18n( get_option('date_format').' '.get_option('time_format'), strtotime( $post->post_date ) ) . '</p>';
                echo '<p>'.( empty( $post->post_author ) ? __( 'by a guest', 'mls-query-api' ) : sprintf( __( 'by %s', 'mls-query-api' ), '<a href="' . esc_url( add_query_arg( 'author', $post->post_author ) ) . '">' . get_the_author() . '</a>' ) ) . '</p>';
                break;
            case "listing_expiry" :

                if( function_exists('blokhausre_user_role_by_post_id')) {
                    if( blokhausre_user_role_by_post_id($post->ID) != 'administrator' && get_post_status ( $post->ID ) == 'publish' ) {
                        if( function_exists('blokhausre_listing_expire')) {
                            blokhausre_listing_expire();
                        }

                    } else {

                        if( get_post_status($post->ID) == 'expired' ) {
                            echo '<span style="color:red;">'.get_post_status($post->ID).'</span>';
                        }
                    }
                }
                break;
        }
    }



    /**
     * Custom admin columns for area taxonomy
     *
     * @access public
     * @return array
     */
    
    public static function Blokhausre_propertyArea_columns_head() {

        $new_columns = array(
            'cb'            => '<input type="checkbox" />',
            'name'          => __('Name','mls-query-api'),
            'city'          => __('City','mls-query-api'),
            'header_icon'   => '',
            'slug'          => __('Slug','mls-query-api'),
            'posts'         => __('Posts','mls-query-api')
        );

        if ( is_rtl() ) {
            $new_columns = array_reverse( $new_columns );
        }

        return $new_columns;
    }


    public static function Blokhausre_propertyArea_columns_content_taxonomy($out, $column_name, $term_id) {
        if ($column_name == 'city') {
            $term_meta= get_option( "_mls_property_area_$term_id");
            $term = get_term_by('slug', $term_meta['parent_city'], 'propertymls_city'); 
            if(!empty($term)) {
                print stripslashes( $term->name );
            }
            return;
        }
    }

    /**
     * Custom admin columns for city taxonomy
     *
     * @access public
     * @return array
     */
    public static function Blokhausre_propertyCity_columns_head() {

        $new_columns = array(
            'cb'            => '<input type="checkbox" />',
            'name'          => __('Name','mls-query-api'),
            'county_state'          => __('County/State','mls-query-api'),
            'header_icon'   => '',
            'slug'          => __('Slug','mls-query-api'),
            'posts'         => __('Posts','mls-query-api')
        );

        if ( is_rtl() ) {
            $new_columns = array_reverse( $new_columns );
        }

        return $new_columns;
    }


    public static function Blokhausre_propertyCity_columns_content_taxonomy($out, $column_name, $term_id) {
        if ($column_name == 'county_state') {
            $term_meta= get_option( "_mls_property_city_$term_id");
            $term = get_term_by('slug', $term_meta['parent_state'], 'property_state'); 
            if(!empty($term)) {
                print stripslashes( $term->name );
            }
            return;
        }
    }



    /**
     * Custom admin columns for state taxonomy
     *
     * @access public
     * @return array
     */
    public static function Blokhausre_propertyState_columns_head() {

        $new_columns = array(
            'cb'            => '<input type="checkbox" />',
            'name'          => __('Name','mls-query-api'),
            'country'       => __('Country','mls-query-api'),
            'header_icon'   => '',
            'slug'          => __('Slug','mls-query-api'),
            'posts'         => __('Posts','mls-query-api')
        );

        if ( is_rtl() ) {
            $new_columns = array_reverse( $new_columns );
        }
        return $new_columns;
    }


    public static function Blokhausre_propertyState_columns_content_taxonomy($out, $column_name, $term_id) {
        if ($column_name == 'country') {
            $term_meta= get_option( "_mls_property_state_$term_id");
            $term = get_term_by('slug', $term_meta['parent_country'], 'propertymls_country'); 
            if(!empty($term)) {
                print stripslashes( $term->name );
            }
        }
    }


    /**
     * Update post meta associated info when post updated
     *
     * @access public
     * @return
     */
    public static function Blokhausre_save_property_post_type($meta_id, $property_id, $meta_key, $meta_value) {

        if ( empty( $meta_id ) || empty( $property_id ) || empty( $meta_key ) ) {
            return;
        }

        if ( 'mls_property_id' === $meta_key ) {
            if( blokhausre_option('auto_property_id', 0) != 0 ) {
                $existing_id     = get_post_meta( $property_id, 'mls_property_id', true );
                $pattern = blokhausre_option( 'property_id_pattern' );
                $new_id   = preg_replace( '/{ID}/', $property_id, $pattern );
                
                if ( $existing_id !== $new_id ) {
                    update_post_meta($property_id, 'mls_property_id', $new_id);
                }
            }
        }

        
        /*
        if ( 'mls_geolocation_lat' !== $meta_key || 'mls_geolocation_long' !== $meta_key ) {
            $lat_long = get_post_meta( $property_id, 'mls_property_location', true );
            if( isset($lat_long) && !empty($lat_long) && is_array($lat_long)) {
                $lat_long = explode(',', $lat_long);
                $lat = $lat_long[0];
                $long = $lat_long[1];

                update_post_meta($property_id, 'mls_geolocation_lat', $lat);
                update_post_meta($property_id, 'mls_geolocation_long', $long);
            }
        }*/

        if(class_exists('mls_Currencies')) {

            if ( 'mls_currency' === $meta_key ) {
                $currency_code = get_post_meta($property_id, 'mls_currency', true);
                $currencies = blokhausre_Currencies::get_property_currency_2($property_id, $currency_code);

                update_post_meta( $property_id, 'mls_currency_info', $currencies );
            }
            
        }

    }

    public static function Blokhausre_approve_listing(){
        if (!empty($_GET['approve_listing']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'approve_listing') && current_user_can('publish_post', $_GET['approve_listing'])) {
            $post_id = absint($_GET['approve_listing']);
            $listing_data = array(
                'ID' => $post_id,
                'post_status' => 'publish'
            );
            wp_update_post($listing_data);

            $author_id = get_post_field ('post_author', $post_id);
            $user           =   get_user_by('id', $author_id );
            $user_email     =   $user->user_email;

            $args = array(
                'listing_title' => get_the_title($post_id),
                'listing_url' => get_permalink($post_id)
            );
            blokhausre_email_type( $user_email,'listing_approved', $args );

            wp_redirect(remove_query_arg('approve_listing', add_query_arg('approve_listing', $post_id, admin_url('edit.php?post_type=propertymls'))));
            exit;
        }
    }

    public static function Blokhausre_expire_listing() {

        if (!empty($_GET['expire_listing']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'expire_listing') && current_user_can('publish_post', $_GET['expire_listing'])) {
            $post_id = absint($_GET['expire_listing']);
            $listing_data = array(
                'ID' => $post_id,
                'post_status' => 'expired'
            );
            wp_update_post($listing_data);

            update_post_meta($post_id, 'mls_featured', '0');

            $author_id = get_post_field ('post_author', $post_id);
            $user           =   get_user_by('id', $author_id );
            $user_email     =   $user->user_email;

            $args = array(
                'listing_title' => get_the_title($post_id),
                'listing_url' => get_permalink($post_id)
            );
            blokhausre_email_type( $user_email,'listing_expired', $args );

            wp_redirect(remove_query_arg('expire_listing', add_query_arg('expire_listing', $post_id, admin_url('edit.php?post_type=propertymls'))));
            exit;
        }
    }


    /*------------------------------------------------
     * Types filter
     *----------------------------------------------- */
    public static function Blokhausre_admin_property_type_filter() {
        global $typenow;
        $post_type = 'propertymls';
        $taxonomy = 'propertymls_type';
        if ($typenow == $post_type) {
            $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
            $info_taxonomy = get_taxonomy($taxonomy);
            wp_dropdown_categories(array(
                'show_option_all' => esc_html__("All Types", 'mls-query-api'),
                'taxonomy' => $taxonomy,
                'name' => $taxonomy,
                'orderby' => 'name',
                'selected' => $selected,
                'show_count' => false,
                'hide_empty' => false,
            ));
        };
    }

    public static function Blokhausre_convert_property_type_to_term_in_query($query) {
        global $pagenow;
        $post_type = 'propertymls';
        $taxonomy = 'propertymls_type';
        $q_vars = &$query->query_vars;
        if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }
    }

    /*------------------------------------------------
     * Status filter
     *----------------------------------------------- */
    public static function Blokhausre_admin_property_status_filter() {
        global $typenow;
        $post_type = 'propertymls';
        $taxonomy = 'propertymls_status';
        if ($typenow == $post_type) {
            $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
            $info_taxonomy = get_taxonomy($taxonomy);
            wp_dropdown_categories(array(
                'show_option_all' => esc_html__("All Status", 'mls-query-api'),
                'taxonomy' => $taxonomy,
                'name' => $taxonomy,
                'orderby' => 'name',
                'selected' => $selected,
                'show_count' => false,
                'hide_empty' => false,
            ));
        };
    }

    public static function Blokhausre_convert_property_status_to_term_in_query($query) {
        global $pagenow;
        $post_type = 'propertymls';
        $taxonomy = 'propertymls_status';
        $q_vars = &$query->query_vars;
        if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }
    }

    /*------------------------------------------------
     * Labels filter
     *----------------------------------------------- */
    public static function Blokhausre_admin_property_label_filter() {
        global $typenow;
        $post_type = 'propertymls';
        $taxonomy = 'propertymls_label';
        if ($typenow == $post_type) {
            $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
            $info_taxonomy = get_taxonomy($taxonomy);
            wp_dropdown_categories(array(
                'show_option_all' => esc_html__("All Labels", 'mls-query-api'),
                'taxonomy' => $taxonomy,
                'name' => $taxonomy,
                'orderby' => 'name',
                'selected' => $selected,
                'show_count' => false,
                'hide_empty' => false,
            ));
        };
    }

    public static function Blokhausre_convert_property_label_to_term_in_query($query) {
        global $pagenow;
        $post_type = 'propertymls';
        $taxonomy = 'propertymls_label';
        $q_vars = &$query->query_vars;
        if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }
    }

    /*------------------------------------------------
     * Cities filter
     *----------------------------------------------- */
    public static function Blokhausre_admin_property_city_filter() {
        global $typenow;
        $post_type = 'propertymls';
        $taxonomy = 'propertymls_city';
        if ($typenow == $post_type) {
            $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
            $info_taxonomy = get_taxonomy($taxonomy);
            wp_dropdown_categories(array(
                'show_option_all' => esc_html__("All Cities", 'mls-query-api'),
                'taxonomy' => $taxonomy,
                'name' => $taxonomy,
                'orderby' => 'name',
                'selected' => $selected,
                'show_count' => false,
                'hide_empty' => false,
            ));
        };
    }

    public static function Blokhausre_convert_property_city_to_term_in_query($query) {
        global $pagenow;
        $post_type = 'propertymls';
        $taxonomy = 'propertymls_city';
        $q_vars = &$query->query_vars;
        if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0) {
            $term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
            $q_vars[$taxonomy] = $term->slug;
        }
    }

    /*------------------------------------------------
     * Admin filters UI
     *----------------------------------------------- */
    public static function Blokhausre_admin_property_id_field() {
        global $post_type;

        if ( $post_type == 'propertymls' ) {

            // Property ID filter
            $property_id = '';
            if ( isset( $_GET['property_id'] ) && ! empty( $_GET['property_id'] ) ) {
                $property_id = esc_attr( $_GET['property_id'] );
            }
            ?>
            <input style="width: 110px;" id="property_id" type="text" name="property_id" placeholder="<?php esc_html_e( 'Property ID', 'mls-query-api' ); ?>" value="<?php echo esc_attr($property_id); ?>">
            <?php

        }
    }

    public static function Blokhausre_sortable_columns($columns) {
        $columns['price'] = 'price';
        $columns['listing_posted'] = 'listing_posted';

        return $columns;
    }

    /*------------------------------------------------
     * Properties admin filter query
     *----------------------------------------------- */
    public static function Blokhausre_property_admin_custom_query($query) {

        global $post_type, $pagenow;

        if ( $pagenow == 'edit.php' && $post_type == 'propertymls' ) {

            $meta_query = array();

            if ( isset( $_GET['property_id'] ) && ! empty( $_GET['property_id'] ) ) {

                $meta_query[] = array(
                    'key'     => 'mls_property_id',
                    'value'   => sanitize_text_field( $_GET['property_id'] ),
                    'compare' => 'LIKE',
                );

            }
            if ( ! empty( $meta_query ) ) {
                $query->query_vars['meta_query'] = $meta_query;

            }
            
            $orderby = $query->get( 'orderby' );

            if ( 'price' == $orderby ) {
                $query->set( 'meta_key', 'mls_property_price' );
                $query->set( 'orderby', 'meta_value_num' );

            } elseif( 'title' == $orderby || 'listing_posted' == $orderby ) {

                $query->set('orderby', $_GET['orderby']);
                $query->set('order', $_GET['order']);

            } else {
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
            }

        } // $pagenow
    }


}