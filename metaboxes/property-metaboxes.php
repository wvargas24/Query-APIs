<?php
include_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property/information.php' );

//include_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property/fields_builder.php' );

include_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property/map.php' );

include_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property/settings.php' );

include_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property/media.php' );

include_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property/virtual_tour.php' );

include_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property/agent.php' );

include_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property/home_slider.php' );

include_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property/multi_units.php' );

include_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property/floor_plans.php' );

include_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property/attachments.php' );

include_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property/private_note.php' );

include_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property/energy.php' );

include_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property/listing_layout.php' );

include_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property/listing_rental.php' );


if( !function_exists('blokhausre_register_property_metaboxes') ) {

    function blokhausre_register_property_metaboxes( $meta_boxes ) {

        $meta_boxes_tabs = array();

        $meta_boxes_fields = array();

        $meta_boxes[] = array(
            'id'         => 'blokhausre-property-meta-box',
            'title'      => esc_html__('Property MLS Data', 'blokhausre'),
            'post_types' => array( 'propertymls' ),
            'tabs'       => apply_filters( 'blokhausre_property_metabox_tabs', $meta_boxes_tabs ),
            'tab_style'  => 'left',
            'fields'     => apply_filters( 'blokhausre_property_metabox_fields', $meta_boxes_fields ),
        );

        return apply_filters( 'blokhausre_theme_meta', $meta_boxes );

    }

    add_filter( 'rwmb_meta_boxes', 'blokhausre_register_property_metaboxes' );
}