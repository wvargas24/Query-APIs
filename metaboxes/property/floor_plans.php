<?php
/**
 * Add floor_plans metabox tab
 *
 * @param $metabox_tabs
 *
 * @return array
 */
function blokhausre_floor_plans_metabox_tab( $metabox_tabs ) {
	if ( is_array( $metabox_tabs ) ) {

		$metabox_tabs['floor_plans'] = array(
			'label' => blokhausre_option('cls_floor_plans', 'Floor Plans'),
            'icon' => 'dashicons-layout',
		);

	}
	return $metabox_tabs;
}
add_filter( 'blokhausre_property_metabox_tabs', 'blokhausre_floor_plans_metabox_tab', 65 );


/**
 * Add floor_plans metaboxes fields
 *
 * @param $metabox_fields
 *
 * @return array
 */
function blokhausre_floor_plans_metabox_fields( $metabox_fields ) {
	$blokhausre_prefix = 'mls_';

	$fields = array(
		array(
	        'id'     => 'floor_plans',
	        // Gropu field
	        'type'   => 'group',
	        // Clone whole group?
	        'clone'  => true,
	        'sort_clone' => false,
	        'tab' => 'floor_plans',
	        // Sub-fields
	        'fields' => array(
	            array(
	                'name' => blokhausre_option('cl_plan_title', 'Plan Title' ),
	                'placeholder' => blokhausre_option('cl_plan_title_plac', 'Enter the title'),
	                'id'   => "{$blokhausre_prefix}plan_title",
	                'type' => 'text',
	                'columns' => 12,
	            ),
	            array(
	                'name' => blokhausre_option('cl_plan_bedrooms', 'Bedrooms' ),
	                'placeholder' => blokhausre_option('cl_plan_bedrooms_plac', 'Enter the number of bedrooms'),
	                'id'   => "{$blokhausre_prefix}plan_rooms",
	                'type' => 'text',
	                'columns' => 6,
	            ),
	            array(
	                'name' => blokhausre_option('cl_plan_bathrooms', 'Bathrooms' ),
	                'placeholder' => blokhausre_option('cl_plan_bathrooms_plac', 'Enter the number of bathrooms'),
	                'id'   => "{$blokhausre_prefix}plan_bathrooms",
	                'type' => 'text',
	                'columns' => 6,
	            ),
	            array(
	                'name' => blokhausre_option('cl_plan_price', 'Price' ),
	                'id'   => "{$blokhausre_prefix}plan_price",
	                'placeholder' => blokhausre_option('cl_plan_price_plac', 'Enter the price'),
	                'type' => 'text',
	                'columns' => 6,
	            ),
	            array(
	                'name' => blokhausre_option('cl_plan_price_postfix', 'Price Postfix' ),
	                'placeholder' => blokhausre_option('cl_plan_price_postfix_plac', 'Enter the price postfix'),
	                'id'   => "{$blokhausre_prefix}plan_price_postfix",
	                'type' => 'text',
	                'columns' => 6,
	            ),
	            array(
	                'name' => blokhausre_option('cl_plan_size', 'Plan Size' ),
	                'placeholder' => blokhausre_option('cl_plan_size_plac', 'Enter the plan size' ),
	                'id'   => "{$blokhausre_prefix}plan_size",
	                'type' => 'text',
	                'columns' => 6,
	            ),
	            array(
	                'name' => blokhausre_option('cl_plan_img', 'Plan Image'),
	                'id'   => "{$blokhausre_prefix}plan_image",
	                'placeholder' => blokhausre_option('cl_plan_img_plac', 'upload the plan image'),
	                'desc' => blokhausre_option('cl_plan_img_size', 'Minimum size 800 x 600 px'),
	                'type' => 'file_input',
	                'columns' => 6,
	            ),
	            array(
	                'name' => blokhausre_option('cl_plan_des', 'Description'),
	                'placeholder' => blokhausre_option('cl_plan_des_plac', 'Enter the plan description'),
	                'id'   => "{$blokhausre_prefix}plan_description",
	                'type' => 'textarea',
	                'columns' => 12,
	            ),

	        ),
	    ),
	);

	return array_merge( $metabox_fields, $fields );

}
add_filter( 'blokhausre_property_metabox_fields', 'blokhausre_floor_plans_metabox_fields', 65 );
