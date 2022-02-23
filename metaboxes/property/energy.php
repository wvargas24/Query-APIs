<?php
/**
 * Add energy metabox tab
 *
 * @param $metabox_tabs
 *
 * @return array
 */
function blokhausre_energy_metabox_tab( $metabox_tabs ) {
	if ( is_array( $metabox_tabs ) ) {

		$metabox_tabs['energy'] = array(
			'label' => blokhausre_option('cls_energy_class', 'Energy Class'),
            'icon' => 'dashicons-lightbulb',
		);

	}
	return $metabox_tabs;
}
add_filter( 'blokhausre_property_metabox_tabs', 'blokhausre_energy_metabox_tab', 80 );


/**
 * Add energy metaboxes fields
 *
 * @param $metabox_fields
 *
 * @return array
 */
function blokhausre_energy_metabox_fields( $metabox_fields ) {
	$blokhausre_prefix = 'mls_';

    $title = blokhausre_option('cl_energy_cls_plac', 'Select Energy Class');

    $energy_array = blokhausre_option('energy_class_data', 'A+, A, B, C, D, E, F, G, H'); 
    $energy_array = explode(',', $energy_array);

    $options_array = array('' => blokhausre_option('cl_energy_cls_plac', 'Select Energy Class'));

    foreach ($energy_array as $e_class) {
        $energy_class = trim($e_class);
        $options_array[$energy_class] = $energy_class;
    }

	$fields = array(
		array(
            'id' => "{$blokhausre_prefix}energy_class",
            'name' => blokhausre_option('cl_energy_cls', 'Energy Class' ),
            'desc' => '',
            'type' => 'select',
            'std' => "global",
            'options' => $options_array,
            'columns' => 6,
            'tab' => 'energy'
        ),
        array(
            'id' => "{$blokhausre_prefix}energy_global_index",
            'name' => blokhausre_option('cl_energy_index', 'Global Energy Performance Index'),
            'placeholder' => blokhausre_option('cl_energy_index_plac', 'For example: 92.42 kWh / m²a'),
            'type' => 'text',
            'std' => "",
            'columns' => 6,
            'tab' => 'energy'
        ),
        array(
            'id' => "{$blokhausre_prefix}renewable_energy_global_index",
            'name' => blokhausre_option('cl_energy_renew_index', 'Renewable energy performance index'),
            'placeholder' => blokhausre_option('cl_energy_renew_index_plac', 'For example: 0.00 kWh / m²a'),
            'type' => 'text',
            'std' => "",
            'columns' => 6,
            'tab' => 'energy'
        ),
        array(
            'id' => "{$blokhausre_prefix}energy_performance",
            'name' => blokhausre_option('cl_energy_build_performance', 'Energy performance of the building'),
            'placeholder' => blokhausre_option('cl_energy_build_performance_plac'),
            'desc' => '',
            'type' => 'text',
            'std' => "",
            'columns' => 6,
            'tab' => 'energy'
        ),
        array(
            'id' => "{$blokhausre_prefix}epc_current_rating",
            'name' => blokhausre_option('cl_energy_ecp_rating', 'EPC Current Rating'),
            'placeholder' => blokhausre_option('cl_energy_ecp_rating_plac'),
            'type' => 'text',
            'std' => "",
            'columns' => 6,
            'tab' => 'energy'
        ),
        array(
            'id' => "{$blokhausre_prefix}epc_potential_rating",
            'name' => blokhausre_option('cl_energy_ecp_p', 'EPC Potential Rating'),
            'placeholder' => blokhausre_option('cl_energy_ecp_p_plac'),
            'type' => 'text',
            'std' => "",
            'columns' => 6,
            'tab' => 'energy'
        ),
	);

	return array_merge( $metabox_fields, $fields );

}
add_filter( 'blokhausre_property_metabox_fields', 'blokhausre_energy_metabox_fields', 80 );
