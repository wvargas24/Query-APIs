<?php
/**
 * Add agent metabox tab
 *
 * @param $metabox_tabs
 *
 * @return array
 */
function blokhausre_agent_metabox_tab( $metabox_tabs ) {
	if ( is_array( $metabox_tabs ) ) {

		$metabox_tabs['agent'] = array(
			'label' => blokhausre_option('cls_contact_info', 'Contact Information'),
            'icon' => 'dashicons-businessman',
		);

	}
	return $metabox_tabs;
}
add_filter( 'blokhausre_property_metabox_tabs', 'blokhausre_agent_metabox_tab', 60 );


/**
 * Add agent metaboxes fields
 *
 * @param $metabox_fields
 *
 * @return array
 */
function blokhausre_agent_metabox_fields( $metabox_fields ) {
	$blokhausre_prefix = 'mls_';

	$is_multi_agents = false;
    $enable_multi_agents = blokhausre_option('enable_multi_agents');
    if( $enable_multi_agents != 0 ) {
        $is_multi_agents = true;
    }

	$fields = array(
		array(
            'name' => blokhausre_option('cl_contact_info_text', 'What information do you want to display in agent data container?'),
            'id' => "{$blokhausre_prefix}agent_display_option",
            'type' => 'radio',
            'std' => 'author_info',
            'options' => array(
                'author_info' => blokhausre_option('cl_author_info', 'Author Info'),
                'agent_info' => blokhausre_option('cl_agent_info', 'Agent Info (Choose agent from the list below)'),
                'agency_info' => blokhausre_option('cl_agency_info', 'Agency Info (Choose agency from the list below)'),
                'none' => blokhausre_option('cl_not_display', 'Do not display'),
            ),
            'columns' => 12,
            'inline' => false,
            'tab' => 'agent',
        ),
        array(
            'name' => blokhausre_option('cl_agent_info_plac', 'Select an Agent'),
            'id' => "{$blokhausre_prefix}agents",
            'type' => 'select',
            'options' => blokhausre_get_agents_array(),
            'columns' => 12,
            'tab' => 'agent',
            'visible' => array( $blokhausre_prefix.'agent_display_option', '=', 'agent_info' ),
            'multiple' => $is_multi_agents
        ),
        array(
            'name' => blokhausre_option('cl_agency_info_plac', 'Select an Agency'),
            'id' => "{$blokhausre_prefix}property_agency",
            'type' => 'select',
            'options' => blokhausre_get_agency_array(),
            'columns' => 12,
            'tab' => 'agent',
            'visible' => array( $blokhausre_prefix.'agent_display_option', '=', 'agency_info' ),
            'multiple' => false
        ),
	);

	return array_merge( $metabox_fields, $fields );

}
add_filter( 'blokhausre_property_metabox_fields', 'blokhausre_agent_metabox_fields', 60 );
