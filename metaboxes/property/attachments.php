<?php
/**
 * Add attachments metabox tab
 *
 * @param $metabox_tabs
 *
 * @return array
 */
function blokhausre_attachments_metabox_tab( $metabox_tabs ) {
	if ( is_array( $metabox_tabs ) ) {

		$metabox_tabs['attachments'] = array(
			'label' => blokhausre_option('cls_documents', 'Property Documents'),
            'icon' => 'dashicons-book',
		);

	}
	return $metabox_tabs;
}
add_filter( 'blokhausre_property_metabox_tabs', 'blokhausre_attachments_metabox_tab', 70 );


/**
 * Add attachments metaboxes fields
 *
 * @param $metabox_fields
 *
 * @return array
 */
function blokhausre_attachments_metabox_fields( $metabox_fields ) {
	$blokhausre_prefix = 'mls_';

	$fields = array(
		array(
            'id' => "{$blokhausre_prefix}attachments",
            'name' => blokhausre_option('cls_documents', 'Property Documents'),
            'desc' => blokhausre_option('cl_decuments_text', 'You can attach PDF files, Map images OR other documents.'),
            'type' => 'file_advanced',
            'mime_type' => '',
            'columns' => 12,
            'tab' => 'attachments',
        ),
	);

	return array_merge( $metabox_fields, $fields );

}
add_filter( 'blokhausre_property_metabox_fields', 'blokhausre_attachments_metabox_fields', 70 );
