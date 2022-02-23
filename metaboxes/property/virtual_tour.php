<?php
/**
 * Add virtual_tour metabox tab
 *
 * @param $metabox_tabs
 *
 * @return array
 */
function blokhausre_virtual_tour_metabox_tab( $metabox_tabs ) {
	if ( is_array( $metabox_tabs ) ) {

		$metabox_tabs['virtual_tour'] = array(
			'label' => blokhausre_option('cls_virtual_tour', '360° Virtual Tour'),
            'icon' => 'dashicons-format-video',
		);

	}
	return $metabox_tabs;
}
add_filter( 'blokhausre_property_metabox_tabs', 'blokhausre_virtual_tour_metabox_tab', 50 );


/**
 * Add virtual_tour metaboxes fields
 *
 * @param $metabox_fields
 *
 * @return array
 */
function blokhausre_virtual_tour_metabox_fields( $metabox_fields ) {
	$blokhausre_prefix = 'mls_';

	$fields = array(
		array(
            'id' => "{$blokhausre_prefix}virtual_tour",
            'name' => blokhausre_option('cls_virtual_tour', '360° Virtual Tour'),
            'placeholder' => blokhausre_option('cl_virtual_plac', 'Enter virtual tour iframe/embeded code'),
            'type' => 'textarea',
            'columns' => 12,
            'sanitize_callback' => 'none',
            'tab' => 'virtual_tour',
        ),
	);

	return array_merge( $metabox_fields, $fields );

}
add_filter( 'blokhausre_property_metabox_fields', 'blokhausre_virtual_tour_metabox_fields', 50 );
