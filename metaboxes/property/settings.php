<?php
/**
 * Add settings metabox tab
 *
 * @param $metabox_tabs
 *
 * @return array
 */
function blokhausre_settings_metabox_tab( $metabox_tabs ) {
	if ( is_array( $metabox_tabs ) ) {

		$metabox_tabs['property_settings'] = array(
			'label' => blokhausre_option('cls_settings', 'Property Setting'),
            'icon' => 'dashicons-admin-generic',
		);

	}
	return $metabox_tabs;
}
add_filter( 'blokhausre_property_metabox_tabs', 'blokhausre_settings_metabox_tab', 30 );


/**
 * Add settings metaboxes fields
 *
 * @param $metabox_fields
 *
 * @return array
 */
function blokhausre_settings_metabox_fields( $metabox_fields ) {
	$blokhausre_prefix = 'mls_';

	

	$fields = array(
		array(
	        'id' => "{$blokhausre_prefix}property_address",
	        'name' => blokhausre_option('cl_streat_address', 'Street Address'),
	        'desc' => blokhausre_option('cl_streat_address_plac', 'Enter only the street name and the building number'),
	        'type' => 'text',
	        'columns' => 6,
	        'tab' => 'property_settings',
	    ),
	    array(
	        'id' => "{$blokhausre_prefix}property_zip",
	        'name' => blokhausre_option('cl_zip', 'Postal Code / Zip'),
	        'desc' => "",
	        'type' => 'text',
	        'columns' => 6,
	        'tab' => 'property_settings',
	    ),
	    array(
	        'name' => blokhausre_option('cl_make_featured', 'Do you want to mark this property as featured?'),
	        'id' => "{$blokhausre_prefix}featured",
	        'type' => 'radio',
	        'std' => 0,
	        'options' => array(
	            1 => blokhausre_option('cl_yes', 'Yes '),
	            0 => blokhausre_option('cl_no', 'No')
	        ),
	        'columns' => 6,
	        'tab' => 'property_settings',
	    ),
	    
	    array(
	        'name' => blokhausre_option('cl_login_view', 'The user must be logged in to view this property'),
	        'id' => "{$blokhausre_prefix}loggedintoview",
	        'type' => 'radio',
	        'desc' => blokhausre_option('cl_login_view_plac', 'If "Yes" then only logged in user can view property details.'),
	        'std' => 0,
	        'options' => array(
	            1 => blokhausre_option('cl_yes', 'Yes '),
	            0 => blokhausre_option('cl_no', 'No')
	        ),
	        'columns' => 6,
	        'tab' => 'property_settings',
	    ),

	    array(
	        'id' => "{$blokhausre_prefix}property_disclaimer",
	        'name' => blokhausre_option('cl_disclaimer', 'Disclaimer'),
	        'desc' => "",
	        'type' => 'textarea',
	        'columns' => 12,
	        'tab' => 'property_settings',
	    ),
	);

	return array_merge( $metabox_fields, $fields );

}
add_filter( 'blokhausre_property_metabox_fields', 'blokhausre_settings_metabox_fields', 30 );
