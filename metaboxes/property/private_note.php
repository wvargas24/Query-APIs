<?php
/**
 * Add private_note metabox tab
 *
 * @param $metabox_tabs
 *
 * @return array
 */
function blokhausre_private_note_metabox_tab( $metabox_tabs ) {
	if ( is_array( $metabox_tabs ) ) {

		$metabox_tabs['private_note'] = array(
			'label' => blokhausre_option('cls_private_notes', 'Private Note'),
            'icon' => 'dashicons-lightbulb',
		);

	}
	return $metabox_tabs;
}
add_filter( 'blokhausre_property_metabox_tabs', 'blokhausre_private_note_metabox_tab', 75 );


/**
 * Add private_note metaboxes fields
 *
 * @param $metabox_fields
 *
 * @return array
 */
function blokhausre_private_note_metabox_fields( $metabox_fields ) {
	$blokhausre_prefix = 'mls_';

	$fields = array(
		array(
            'id' => "{$blokhausre_prefix}private_note",
            'name' => blokhausre_option('cls_private_notes', 'Private Note'),
            'placeholder' => blokhausre_option('cl_private_note', 'Enter the note here'),
            'desc' => blokhausre_option('cl_private_note', 'Write private note for this property, it will not display for public.'),
            'type' => 'textarea',
            'mime_type' => '',
            'columns' => 12,
            'tab' => 'private_note',
        ),
	);

	return array_merge( $metabox_fields, $fields );

}
add_filter( 'blokhausre_property_metabox_fields', 'blokhausre_private_note_metabox_fields', 75 );
