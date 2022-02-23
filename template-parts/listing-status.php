<?php
/**
 * Created by Kambda.
 * Author: Wuilly Vargas
 * Date: 11/12/21
 * Time: 3:47 PM
 */
$term_id = '';
$term_status = wp_get_post_terms( get_the_ID(), 'propertymls_status', array("fields" => "all"));
$label_id = '';
$term_label = wp_get_post_terms( get_the_ID(), 'propertymls_label', array("fields" => "all"));

if( !empty($term_status) ) {
    foreach( $term_status as $status ) {
        $status_id = $status->term_id;
        $status_name = $status->name;
        echo '<span class="label-status label-status-'.intval($status_id).' label label-default"><a href="'.get_term_link($status_id).'">'.esc_attr($status_name).'</a></span>';
    }
}

if( !empty($term_label) ) {
    foreach( $term_label as $label ) {
        $label_id = $label->term_id;
        $label_name = $label->name;
        echo '<span class="label label-default label-color-'.intval($label_id).'"><a href="'.get_term_link($label_id).'">'.esc_attr($label_name).'</a></span>';
    }
}
?>