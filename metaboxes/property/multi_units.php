<?php
/**
 * Add multi_units metabox tab
 *
 * @param $metabox_tabs
 *
 * @return array
 */
function blokhausre_multi_units_metabox_tab( $metabox_tabs ) {
	if ( is_array( $metabox_tabs ) ) {

		$metabox_tabs['multi_units'] = array(
			'label' => blokhausre_option('cls_sub_listings', 'Sub Listings'),
            'icon' => 'dashicons-layout',
		);

	}
	return $metabox_tabs;
}
add_filter( 'blokhausre_property_metabox_tabs', 'blokhausre_multi_units_metabox_tab', 61 );


/**
 * Add multi_units metaboxes fields
 *
 * @param $metabox_fields
 *
 * @return array
 */
function blokhausre_multi_units_metabox_fields( $metabox_fields ) {
	$blokhausre_prefix = 'mls_';

	$fields = array(
		array(
            'id' => "{$blokhausre_prefix}multi_units_ids",
            'name' => blokhausre_option('cl_subl_ids', 'Listing IDs'),
            'placeholder' => blokhausre_option('cl_subl_ids_plac', 'Enter the listing IDs comma separated'),
            'desc' => blokhausre_option('cl_subl_ids_tooltip', 'If the sub-properties are separated listings, use the box above to enter the listing IDs (Example: 4,5,6)'),
            'type' => 'textarea',
            'columns' => 12,
            'tab' => 'multi_units',
        ),
        array(
            'type' => 'heading',
            'name' => blokhausre_option('cl_or', 'Or'),
            'columns' => 12,
            'desc' => "",
            'tab' => 'multi_units',
        ),
        array(
            'id'     => "{$blokhausre_prefix}multi_units",
            // Gropu field
            'type'   => 'group',
            // Clone whole group?
            'clone'  => true,
            'sort_clone' => false,
            'tab' => 'multi_units',
            // Sub-fields
            'fields' => array(
                array(
                    'name' => blokhausre_option('cl_subl_title', 'Title' ),
                    'id'   => "{$blokhausre_prefix}mu_title",
                    'type' => 'text',
                    'placeholder' => blokhausre_option('cl_subl_title_plac', 'Enter the title'),
                    'columns' => 12,
                ),
                array(
                    'name' => blokhausre_option('cl_subl_price', 'Price' ),
                    'id'   => "{$blokhausre_prefix}mu_price",
                    'placeholder' => blokhausre_option('cl_subl_price_plac', 'Enter the price'),
                    'type' => 'text',
                    'columns' => 6,
                ),
                array(
                    'name' => blokhausre_option('cl_subl_price_postfix', 'Price Postfix' ),
                    'id'   => "{$blokhausre_prefix}mu_price_postfix",
                    'placeholder' => blokhausre_option('cl_subl_price_postfix_plac', 'Enter the price postfix'),
                    'type' => 'text',
                    'columns' => 6,
                ),
                array(
                    'name' => blokhausre_option('cl_subl_bedrooms', 'Bedrooms' ),
                    'id'   => "{$blokhausre_prefix}mu_beds",
                    'placeholder' => blokhausre_option('cl_subl_bedrooms', 'Enter the number of bedrooms'),
                    'type' => 'text',
                    'columns' => 6,
                ),
                array(
                    'name' => blokhausre_option('cl_subl_bathrooms', 'Bathrooms' ),
                    'id'   => "{$blokhausre_prefix}mu_baths",
                    'placeholder' => blokhausre_option('cl_subl_bathrooms_plac', 'Enter the number of bathrooms'),
                    'type' => 'text',
                    'columns' => 6,
                ),
                array(
                    'name' => blokhausre_option('cl_subl_size', 'Property Size' ),
                    'id'   => "{$blokhausre_prefix}mu_size",
                    'placeholder' => blokhausre_option('cl_subl_size', 'Enter the property size'),
                    'type' => 'text',
                    'columns' => 6,
                ),
                array(
                    'name' => blokhausre_option('cl_subl_size_postfix', 'Size Postfix' ),
                    'id'   => "{$blokhausre_prefix}mu_size_postfix",
                    'placeholder' => blokhausre_option('cl_subl_size_postfix_plac', 'Enter the property size postfix'),
                    'type' => 'text',
                    'columns' => 6,
                ),
                array(
                    'name' => blokhausre_option('cl_subl_type', 'Property Type' ),
                    'id'   => "{$blokhausre_prefix}mu_type",
                    'placeholder' => blokhausre_option('cl_subl_type_plac', 'Enter the property type'),
                    'type' => 'text',
                    'columns' => 6,
                ),
                array(
                    'name' => blokhausre_option('cl_subl_date', 'Availability Date' ),
                    'id'   => "{$blokhausre_prefix}mu_availability_date",
                    'placeholder' => blokhausre_option('cl_subl_date_plac', 'Enter the availability date'),
                    'type' => 'text',
                    'columns' => 6,
                ),

            ),
        ),
	);

	return array_merge( $metabox_fields, $fields );

}
add_filter( 'blokhausre_property_metabox_fields', 'blokhausre_multi_units_metabox_fields', 61 );
