<?php
/**
 * Add map metabox tab
 *
 * @param $metabox_tabs
 *
 * @return array
 */
function blokhausre_map_metabox_tab( $metabox_tabs ) {
	if ( is_array( $metabox_tabs ) ) {

		$metabox_tabs['property_map'] = array(
			'label' => blokhausre_option('cls_map', 'Map'),
            'icon' => 'dashicons-location',
		);

	}
	return $metabox_tabs;
}
add_filter( 'blokhausre_property_metabox_tabs', 'blokhausre_map_metabox_tab', 20 );


/**
 * Add map metaboxes fields
 *
 * @param $metabox_fields
 *
 * @return array
 */
function blokhausre_map_metabox_fields( $metabox_fields ) {
	$blokhausre_prefix = 'mls_';

	

	$fields = array(
		array(
            'name' => blokhausre_option('cls_map', 'Map'),
            'id' => "{$blokhausre_prefix}property_map",
            'type' => 'radio',
            'std' => 1,
            'options' => array(
                1 => blokhausre_option('cl_show', 'Show '),
                0 => blokhausre_option('cl_hide', 'Hide')
            ),
            'columns' => 12,
            'tab' => 'property_map',
        ),
        array(
            'id' => "{$blokhausre_prefix}property_map_address",
            'name' => blokhausre_option('cl_address', 'Address'),
            'placeholder' => blokhausre_option('cl_address_plac', 'Enter your property address'),
            'desc' => '',
            'type' => 'text',
            'std' => '',
            'columns' => 12,
            'tab' => 'property_map',
        ),
        array(
            'id' => "{$blokhausre_prefix}property_location",
            'name' => '',
            'desc' => blokhausre_option('cl_drag_drop_text', 'Drag and drop the pin on map to find exact location'),
            'type' => blokhausre_metabox_map_type(),
            'std' => blokhausre_option('map_default_lat', 25.686540).','.blokhausre_option('map_default_long', -80.431345).',15',
            'style' => 'width: 100%; height: 410px',
            'address_field' => "{$blokhausre_prefix}property_map_address",
            'api_key'       => blokhausre_map_api_key(),
            'language' => get_locale(),
            'columns' => 12,
            'tab' => 'property_map',
        ),


        array(
            'name' => blokhausre_option('cl_street_view', 'Street View'),
            'id' => "{$blokhausre_prefix}property_map_street_view",
            'type' => 'select',
            'std' => 'hide',
            'options' => array(
                'hide' => blokhausre_option('cl_hide', 'Hide'),
                'show' => blokhausre_option('cl_show', 'Show')
            ),
            'columns' => 12,
            'tab' => 'property_map',
        ),
	);

	return array_merge( $metabox_fields, $fields );

}
add_filter( 'blokhausre_property_metabox_fields', 'blokhausre_map_metabox_fields', 20 );
