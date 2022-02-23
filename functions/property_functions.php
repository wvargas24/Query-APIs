<?php
/**
 * Created by Kambda.
 * Author: Wuilly Vargas
 * Date: 11/12/21
 * Time: 4:28 PM
 */

if( !function_exists('blokhausre_listing_expire')) {
    function blokhausre_listing_expire() {
        global $post;

        //If manual expire date set
        $manual_expire = get_post_meta( $post->ID, 'blokhausre_manual_expire', true );
        if( !empty( $manual_expire )) {
            $expiration_date = get_post_meta( $post->ID,'_blokhausre_expiration_date',true );
            echo ( $expiration_date ? get_date_from_gmt(gmdate('Y-m-d H:i:s', $expiration_date), get_option('date_format').' '.get_option('time_format')) : __('Never', 'houzez'));
        } else {
            $submission_type = blokhausre_option('enable_paid_submission');
            // Per listing
            if( $submission_type == 'per_listing' || $submission_type == 'free_paid_listing' || $submission_type == 'no' ) {
                $per_listing_expire_unlimited = blokhausre_option('per_listing_expire_unlimited');
                if( $per_listing_expire_unlimited != 0 ) {
                    $per_listing_expire = blokhausre_option('per_listing_expire');

                    $publish_date = $post->post_date;
                    echo date_i18n( get_option('date_format').' '.get_option('time_format'), strtotime( $publish_date. ' + '.$per_listing_expire.' days' ) );
                }
            } elseif( $submission_type == 'membership' ) {
                $post_author = get_post_field( 'post_author', $post->ID );
                $package_id = get_user_meta( $post_author, 'package_id', true );
                if( !empty($package_id) ) {
                    $billing_time_unit = get_post_meta( $package_id, 'fave_billing_time_unit', true );
                    $billing_unit = get_post_meta( $package_id, 'fave_billing_unit', true );

                    if( $billing_time_unit == 'Day')
                        $billing_time_unit = 'days';
                    elseif( $billing_time_unit == 'Week')
                        $billing_time_unit = 'weeks';
                    elseif( $billing_time_unit == 'Month')
                        $billing_time_unit = 'months';
                    elseif( $billing_time_unit == 'Year')
                        $billing_time_unit = 'years';

                    $publish_date = $post->post_date;
                    echo date_i18n( get_option('date_format').' '.get_option('time_format'), strtotime( $publish_date. ' + '.$billing_unit.' '.$billing_time_unit ) );
                }
            }
        }
    }
}

/*-----------------------------------------------------------------------------------*/
// Submit Property filter
/*-----------------------------------------------------------------------------------*/
add_filter('blokhausre_submit_listing', 'blokhausre_submit_listing');

if( !function_exists('blokhausre_submit_listing') ) {
    function blokhausre_submit_listing($new_property) {
        global $current_user;

        wp_get_current_user();
        $userID = $current_user->ID;
        $listings_admin_approved = blokhausre_option('listings_admin_approved');
        $edit_listings_admin_approved = blokhausre_option('edit_listings_admin_approved');
        $enable_paid_submission = blokhausre_option('enable_paid_submission');

        // Title
        if( isset( $_POST['prop_title']) ) {
            $new_property['post_title'] = sanitize_text_field( $_POST['prop_title'] );
        }

        if( $enable_paid_submission == 'membership' ) {
            $user_submit_has_no_membership = isset($_POST['user_submit_has_no_membership']) ? $_POST['user_submit_has_no_membership'] : '';
        } else {
            $user_submit_has_no_membership = 'no';
        }

        // Description
        if( isset( $_POST['prop_des'] ) ) {
            $new_property['post_content'] = wp_kses_post( $_POST['prop_des'] );
        }

        $new_property['post_author'] = $userID;

        $submission_action = $_POST['action'];
        $prop_id = 0;

        if( $submission_action == 'add_property' ) {

            if( $listings_admin_approved != 'yes' && ( $enable_paid_submission == 'no' || $enable_paid_submission == 'free_paid_listing' || $enable_paid_submission == 'membership' ) ) {
                if( $user_submit_has_no_membership == 'yes' ) {
                    $new_property['post_status'] = 'draft';
                } else {
                    $new_property['post_status'] = 'publish';
                }
            } else {
                if( $user_submit_has_no_membership == 'yes' && $enable_paid_submission = 'membership' ) {
                    $new_property['post_status'] = 'draft';
                } else {
                    $new_property['post_status'] = 'pending';
                }
            }

            $prop_id = wp_insert_post( $new_property );

            if( $prop_id > 0 ) {
                $submitted_successfully = true;
                if( $enable_paid_submission == 'membership'){ // update package status
                    blokhausre_update_package_listings( $userID );
                }
                //do_action( 'wp_insert_post', 'wp_insert_post' ); // Post the Post
            }
        }else if( $submission_action == 'update_property' ) {
            $new_property['ID'] = intval( $_POST['prop_id'] );

            if( get_post_status( intval( $_POST['prop_id'] ) ) == 'draft' ) {
                if( $enable_paid_submission == 'membership') {
                    blokhausre_update_package_listings($userID);
                }
                if( $listings_admin_approved != 'yes' && ( $enable_paid_submission == 'no' || $enable_paid_submission == 'free_paid_listing' || $enable_paid_submission == 'membership' ) ) {
                    $new_property['post_status'] = 'publish';
                } else {
                    $new_property['post_status'] = 'pending';
                }
            } elseif( $edit_listings_admin_approved == 'yes' ) {
                    $new_property['post_status'] = 'pending';
            }
            $prop_id = wp_update_post( $new_property );

        }

        if( $prop_id > 0 ) {


            //Custom Fields
            if(class_exists('Houzez_Fields_Builder')) {
                $fields_array = Houzez_Fields_Builder::get_form_fields();
                if(!empty($fields_array)):
                    foreach ( $fields_array as $value ):
                        $field_name = $value->field_id;

                        if( isset( $_POST[$field_name] ) ) {
                            update_post_meta( $prop_id, 'fave_'.$field_name, sanitize_text_field( $_POST[$field_name] ) );
                        }

                    endforeach; endif;
            }


            if( $user_submit_has_no_membership == 'yes' ) {
                update_user_meta( $userID, 'user_submit_has_no_membership', $prop_id );
                update_user_meta( $userID, 'user_submitted_without_membership', 'yes' );
            }

            // Add price post meta
            if( isset( $_POST['prop_price'] ) ) {
                update_post_meta( $prop_id, 'fave_property_price', sanitize_text_field( $_POST['prop_price'] ) );

                if( isset( $_POST['prop_label'] ) ) {
                    update_post_meta( $prop_id, 'fave_property_price_postfix', sanitize_text_field( $_POST['prop_label']) );
                }
            }

            //price prefix
            if( isset( $_POST['prop_price_prefix'] ) ) {
                update_post_meta( $prop_id, 'fave_property_price_prefix', sanitize_text_field( $_POST['prop_price_prefix']) );
            }

            // Second Price
            if( isset( $_POST['prop_sec_price'] ) ) {
                update_post_meta( $prop_id, 'fave_property_sec_price', sanitize_text_field( $_POST['prop_sec_price'] ) );
            }

            // currency
            if( isset( $_POST['currency'] ) ) {
                update_post_meta( $prop_id, 'fave_currency', sanitize_text_field( $_POST['currency'] ) );
                if(class_exists('Houzez_Currencies')) {
                    $currencies = Houzez_Currencies::get_property_currency_2($prop_id, $_POST['currency']);

                    update_post_meta( $prop_id, 'fave_currency_info', $currencies );
                }
            }


            // Area Size
            if( isset( $_POST['prop_size'] ) ) {
                update_post_meta( $prop_id, 'fave_property_size', sanitize_text_field( $_POST['prop_size'] ) );
            }

            // Area Size Prefix
            if( isset( $_POST['prop_size_prefix'] ) ) {
                update_post_meta( $prop_id, 'fave_property_size_prefix', sanitize_text_field( $_POST['prop_size_prefix'] ) );
            }

            // Land Area Size
            if( isset( $_POST['prop_land_area'] ) ) {
                update_post_meta( $prop_id, 'fave_property_land', sanitize_text_field( $_POST['prop_land_area'] ) );
            }

            // Land Area Size Prefix
            if( isset( $_POST['prop_land_area_prefix'] ) ) {
                update_post_meta( $prop_id, 'fave_property_land_postfix', sanitize_text_field( $_POST['prop_land_area_prefix'] ) );
            }

            // Bedrooms
            if( isset( $_POST['prop_beds'] ) ) {
                update_post_meta( $prop_id, 'fave_property_bedrooms', sanitize_text_field( $_POST['prop_beds'] ) );
            }

            // Bathrooms
            if( isset( $_POST['prop_baths'] ) ) {
                update_post_meta( $prop_id, 'fave_property_bathrooms', sanitize_text_field( $_POST['prop_baths'] ) );
            }

            // Garages
            if( isset( $_POST['prop_garage'] ) ) {
                update_post_meta( $prop_id, 'fave_property_garage', sanitize_text_field( $_POST['prop_garage'] ) );
            }

            // Garages Size
            if( isset( $_POST['prop_garage_size'] ) ) {
                update_post_meta( $prop_id, 'fave_property_garage_size', sanitize_text_field( $_POST['prop_garage_size'] ) );
            }

            // Virtual Tour
            if( isset( $_POST['virtual_tour'] ) ) {
                update_post_meta( $prop_id, 'fave_virtual_tour', $_POST['virtual_tour'] );
            }

            // Year Built
            if( isset( $_POST['prop_year_built'] ) ) {
                update_post_meta( $prop_id, 'fave_property_year', sanitize_text_field( $_POST['prop_year_built'] ) );
            }

            // Property ID
            $auto_property_id = blokhausre_option('auto_property_id');
            if( $auto_property_id != 1 ) {
                if (isset($_POST['property_id'])) {
                    update_post_meta($prop_id, 'fave_property_id', sanitize_text_field($_POST['property_id']));
                }
            } else {
                    update_post_meta($prop_id, 'fave_property_id', $prop_id );
            }

            // Property Video Url
            if( isset( $_POST['prop_video_url'] ) ) {
                update_post_meta( $prop_id, 'fave_video_url', sanitize_text_field( $_POST['prop_video_url'] ) );
            }

            // property video image - in case of update
            $property_video_image = "";
            $property_video_image_id = 0;
            if( $submission_action == "update_property" ) {
                $property_video_image_id = get_post_meta( $prop_id, 'fave_video_image', true );
                if ( ! empty ( $property_video_image_id ) ) {
                    $property_video_image_src = wp_get_attachment_image_src( $property_video_image_id, 'houzez-property-detail-gallery' );
                    $property_video_image = $property_video_image_src[0];
                }
            }

            // clean up the old meta information related to images when property update
            if( $submission_action == "update_property" ){
                delete_post_meta( $prop_id, 'fave_property_images' );
                delete_post_meta( $prop_id, 'fave_attachments' );
                delete_post_meta( $prop_id, '_thumbnail_id' );
            }

            // Property Images
            if( isset( $_POST['propperty_image_ids'] ) ) {
                if (!empty($_POST['propperty_image_ids']) && is_array($_POST['propperty_image_ids'])) {
                    $property_image_ids = array();
                    foreach ($_POST['propperty_image_ids'] as $prop_img_id ) {
                        $property_image_ids[] = intval( $prop_img_id );
                        add_post_meta($prop_id, 'fave_property_images', $prop_img_id);
                    }

                    // featured image
                    if( isset( $_POST['featured_image_id'] ) ) {
                        $featured_image_id = intval( $_POST['featured_image_id'] );
                        if( in_array( $featured_image_id, $property_image_ids ) ) {
                            update_post_meta( $prop_id, '_thumbnail_id', $featured_image_id );

                            /* if video url is provided but there is no video image then use featured image as video image */
                            if ( empty( $property_video_image ) && !empty( $_POST['prop_video_url'] ) ) {
                                update_post_meta( $prop_id, 'fave_video_image', $featured_image_id );
                            }
                        }
                    } elseif ( ! empty ( $property_image_ids ) ) {
                        update_post_meta( $prop_id, '_thumbnail_id', $property_image_ids[0] );

                        /* if video url is provided but there is no video image then use featured image as video image */
                        if ( empty( $property_video_image ) && !empty( $_POST['prop_video_url'] ) ) {
                            update_post_meta( $prop_id, 'fave_video_image', $property_image_ids[0] );
                        }
                    }
                }
            }

            if( isset( $_POST['propperty_attachment_ids'] ) ) {
                    $property_attach_ids = array();
                    foreach ($_POST['propperty_attachment_ids'] as $prop_atch_id ) {
                        $property_attach_ids[] = intval( $prop_atch_id );
                        add_post_meta($prop_id, 'fave_attachments', $prop_atch_id);
                    }
            }

            // Add property type
            if( isset( $_POST['prop_type'] ) && ( $_POST['prop_type'] != '-1' ) ) {
                wp_set_object_terms( $prop_id, intval( $_POST['prop_type'] ), 'property_type' );
            }

            // Add property status
            if( isset( $_POST['prop_status'] ) && ( $_POST['prop_status'] != '-1' ) ) {
                wp_set_object_terms( $prop_id, intval( $_POST['prop_status'] ), 'property_status' );
            }

            // Add property status
            if( isset( $_POST['prop_labels'] ) ) {
                wp_set_object_terms( $prop_id, intval( $_POST['prop_labels'] ), 'property_label' );
            }

            
            $location_dropdowns = blokhausre_option('location_dropdowns');

            // Add property city and area
            if($location_dropdowns == 'yes' && $submission_action == 'update_property') {

                if( isset( $_POST['locality2'] ) ) {
                    $property_city = sanitize_text_field( $_POST['locality2'] );
                    $city_id = wp_set_object_terms( $prop_id, $property_city, 'property_city' );

                    $blokhausre_meta = array();
                    $blokhausre_meta['parent_state'] = isset( $_POST['administrative_area_level_1'] ) ? $_POST['administrative_area_level_1'] : '';
                    if( !empty( $city_id) ) {
                        update_option('_blokhausre_property_city_' . $city_id[0], $blokhausre_meta);
                    }
                }

                if( isset( $_POST['neighborhood2'] ) ) {
                    $property_area = sanitize_text_field( $_POST['neighborhood2'] );
                    $area_id = wp_set_object_terms( $prop_id, $property_area, 'property_area' );

                    $blokhausre_meta = array();
                    $blokhausre_meta['parent_city'] = isset( $_POST['locality'] ) ? $_POST['locality'] : '';
                    if( !empty( $area_id) && isset( $_POST['locality'] )) {
                        update_option('_blokhausre_property_area_' . $area_id[0], $blokhausre_meta);
                    }
                }

            } else {

                if( isset( $_POST['locality'] ) ) {
                    $property_city = sanitize_text_field( $_POST['locality'] );
                    $city_id = wp_set_object_terms( $prop_id, $property_city, 'property_city' );

                    $blokhausre_meta = array();
                    $blokhausre_meta['parent_state'] = isset( $_POST['administrative_area_level_1'] ) ? $_POST['administrative_area_level_1'] : '';
                    if( !empty( $city_id) ) {
                        update_option('_blokhausre_property_city_' . $city_id[0], $blokhausre_meta);
                    }
                }

                if( isset( $_POST['neighborhood'] ) ) {
                    $property_area = sanitize_text_field( $_POST['neighborhood'] );
                    $area_id = wp_set_object_terms( $prop_id, $property_area, 'property_area' );

                    $blokhausre_meta = array();
                    $blokhausre_meta['parent_city'] = isset( $_POST['locality'] ) ? $_POST['locality'] : '';
                    if( !empty( $area_id) ) {
                        update_option('_blokhausre_property_area_' . $area_id[0], $blokhausre_meta);
                    }
                }
            }


            // Add property state
            if( isset( $_POST['administrative_area_level_1'] ) ) {
                $property_state = sanitize_text_field( $_POST['administrative_area_level_1'] );
                $state_id = wp_set_object_terms( $prop_id, $property_state, 'property_state' );

                $blokhausre_meta = array();
                $country_short = isset( $_POST['country_short'] ) ? $_POST['country_short'] : '';
                if(!empty($country_short)) {
                   $country_short = strtoupper($country_short); 
                } else {
                    $country_short = '';
                }
                
                $blokhausre_meta['parent_country'] = $country_short;
                if( !empty( $state_id) ) {
                    update_option('_blokhausre_property_state_' . $state_id[0], $blokhausre_meta);
                }
            }

            //echo $_POST['country_short'].' '.$_POST['administrative_area_level_1'].' '.$_POST['locality'].' '.$_POST['neighborhood']; die;
           
            // Add property features
            if( isset( $_POST['prop_features'] ) ) {
                $features_array = array();
                foreach( $_POST['prop_features'] as $feature_id ) {
                    $features_array[] = intval( $feature_id );
                }
                wp_set_object_terms( $prop_id, $features_array, 'property_feature' );
            }

            // additional details
            if( isset( $_POST['additional_features'] ) ) {
                $additional_features = $_POST['additional_features'];
                if( ! empty( $additional_features ) ) {
                    update_post_meta( $prop_id, 'additional_features', $additional_features );
                    update_post_meta( $prop_id, 'fave_additional_features_enable', 'enable' );
                }
            }

            //Floor Plans
            if( isset( $_POST['floorPlans_enable'] ) ) {
                $floorPlans_enable = $_POST['floorPlans_enable'];
                if( ! empty( $floorPlans_enable ) ) {
                    update_post_meta( $prop_id, 'fave_floor_plans_enable', $floorPlans_enable );
                }
            }

            if( isset( $_POST['floor_plans'] ) ) {
                $floor_plans_post = $_POST['floor_plans'];
                if( ! empty( $floor_plans_post ) ) {
                    update_post_meta( $prop_id, 'floor_plans', $floor_plans_post );
                }
            }

            //Multi-units / Sub-properties
            if( isset( $_POST['multiUnits'] ) ) {
                $multiUnits_enable = $_POST['multiUnits'];
                if( ! empty( $multiUnits_enable ) ) {
                    update_post_meta( $prop_id, 'fave_multiunit_plans_enable', $multiUnits_enable );
                }
            }

            if( isset( $_POST['fave_multi_units'] ) ) {
                $fave_multi_units = $_POST['fave_multi_units'];
                if( ! empty( $fave_multi_units ) ) {
                    update_post_meta( $prop_id, 'fave_multi_units', $fave_multi_units );
                }
            }

            // Make featured
            if( isset( $_POST['prop_featured'] ) ) {
                $featured = intval( $_POST['prop_featured'] );
                update_post_meta( $prop_id, 'fave_featured', $featured );
            }

            // Private Note
            if( isset( $_POST['private_note'] ) ) {
                $private_note = wp_kses_post( $_POST['private_note'] );
                update_post_meta( $prop_id, 'fave_private_note', $private_note );
            }

            //Energy Class
            if(isset($_POST['energy_class'])) {
                $energy_class = sanitize_text_field($_POST['energy_class']);
                update_post_meta( $prop_id, 'fave_energy_class', $energy_class );
            }
            if(isset($_POST['energy_global_index'])) {
                $energy_global_index = sanitize_text_field($_POST['energy_global_index']);
                update_post_meta( $prop_id, 'fave_energy_global_index', $energy_global_index );
            }
            if(isset($_POST['renewable_energy_global_index'])) {
                $renewable_energy_global_index = sanitize_text_field($_POST['renewable_energy_global_index']);
                update_post_meta( $prop_id, 'fave_renewable_energy_global_index', $renewable_energy_global_index );
            }
            if(isset($_POST['energy_performance'])) {
                $energy_performance = sanitize_text_field($_POST['energy_performance']);
                update_post_meta( $prop_id, 'fave_energy_performance', $energy_performance );
            }


            // Property Payment
            if( isset( $_POST['prop_payment'] ) ) {
                $prop_payment = sanitize_text_field( $_POST['prop_payment'] );
                update_post_meta( $prop_id, 'fave_payment_status', $prop_payment );
            }


            if( isset( $_POST['fave_agent_display_option'] ) ) {

                $prop_agent_display_option = sanitize_text_field( $_POST['fave_agent_display_option'] );
                if( $prop_agent_display_option == 'agent_info' ) {

                    $prop_agent = sanitize_text_field( $_POST['fave_agents'] );
                    update_post_meta( $prop_id, 'fave_agent_display_option', $prop_agent_display_option );
                    update_post_meta( $prop_id, 'fave_agents', $prop_agent );
                    if (blokhausre_is_agency()) {
                        $user_agency_id = get_user_meta( $userID, 'fave_author_agency_id', true );
                        if( !empty($user_agency_id)) {
                            update_post_meta($prop_id, 'fave_property_agency', $user_agency_id);
                        }
                    }

                } elseif( $prop_agent_display_option == 'agency_info' ) {

                    $user_agency_id = get_user_meta( $userID, 'fave_author_agency_id', true );
                    if( !empty($user_agency_id) ) {
                        update_post_meta($prop_id, 'fave_agent_display_option', $prop_agent_display_option);
                        update_post_meta($prop_id, 'fave_property_agency', $user_agency_id);
                    } else {
                        update_post_meta( $prop_id, 'fave_agent_display_option', 'author_info' );
                    }

                } else {
                    update_post_meta( $prop_id, 'fave_agent_display_option', $prop_agent_display_option );
                }

            } else {
                if (blokhausre_is_agency()) {
                    $user_agency_id = get_user_meta( $userID, 'fave_author_agency_id', true );
                    if( !empty($user_agency_id) ) {
                        update_post_meta($prop_id, 'fave_agent_display_option', 'agency_info');
                        update_post_meta($prop_id, 'fave_property_agency', $user_agency_id);
                    } else {
                        update_post_meta( $prop_id, 'fave_agent_display_option', 'author_info' );
                    }

                } elseif(blokhausre_is_agent()){
                    $user_agent_id = get_user_meta( $userID, 'fave_author_agent_id', true );

                    if ( !empty( $user_agent_id ) ) {

                        update_post_meta($prop_id, 'fave_agent_display_option', 'agent_info');
                        update_post_meta($prop_id, 'fave_agents', $user_agent_id);

                    } else {
                        update_post_meta($prop_id, 'fave_agent_display_option', 'author_info');
                    }

                } else {
                    update_post_meta($prop_id, 'fave_agent_display_option', 'author_info');
                }
            }

            // Address
            if( isset( $_POST['property_map_address'] ) ) {
                update_post_meta( $prop_id, 'fave_property_map_address', sanitize_text_field( $_POST['property_map_address'] ) );
                update_post_meta( $prop_id, 'fave_property_address', sanitize_text_field( $_POST['property_map_address'] ) );
            }

            if( ( isset($_POST['lat']) && !empty($_POST['lat']) ) && (  isset($_POST['lng']) && !empty($_POST['lng'])  ) ) {
                $lat = sanitize_text_field( $_POST['lat'] );
                $lng = sanitize_text_field( $_POST['lng'] );
                $streetView = sanitize_text_field( $_POST['prop_google_street_view'] );
                $lat_lng = $lat.','.$lng;

                update_post_meta( $prop_id, 'blokhausre_geolocation_lat', $lat );
                update_post_meta( $prop_id, 'blokhausre_geolocation_long', $lng );
                update_post_meta( $prop_id, 'fave_property_location', $lat_lng );
                update_post_meta( $prop_id, 'fave_property_map', '1' );
                update_post_meta( $prop_id, 'fave_property_map_street_view', $streetView );

            }
            // Country
            if( isset( $_POST['country_short'] ) ) {
                update_post_meta( $prop_id, 'fave_property_country', sanitize_text_field( $_POST['country_short'] ) );
            } else {
                $default_country = blokhausre_option('default_country');
                update_post_meta( $prop_id, 'fave_property_country', $default_country );
            }
            // Postal Code
            if( isset( $_POST['postal_code'] ) ) {
                update_post_meta( $prop_id, 'fave_property_zip', sanitize_text_field( $_POST['postal_code'] ) );
            }

        return $prop_id;
        }
    }
}

if( !function_exists('blokhausre_update_property_from_draft') ) {
    function blokhausre_update_property_from_draft( $property_id ) {
        $listings_admin_approved = blokhausre_option('listings_admin_approved');

        if( $listings_admin_approved != 'yes' ) {
            $prop_status = 'publish';
        } else {
            $prop_status = 'pending';
        }

        $updated_property = array(
            'ID' => $property_id,
            'post_type'	=> 'propertymls',
            'post_status' => $prop_status
        );
        $prop_id = wp_update_post( $updated_property );
    }
}

add_action('wp_ajax_blokhausre_relist_free', 'blokhausre_relist_free');
if( !function_exists('blokhausre_relist_free') ) {
    function blokhausre_relist_free() {
        $propID = $_POST['propID'];
        $updated_property = array(
            'ID' => $propID,
            'post_type'	=> 'propertymls',
            'post_status' => 'publish',
            'post_date'     => current_time( 'mysql' ),
        );
        $post_id = wp_update_post( $updated_property );
    }
}

/*-----------------------------------------------------------------------------------*/
// validate Email
/*-----------------------------------------------------------------------------------*/
add_action('wp_ajax_save_as_draft', 'save_property_as_draft');
if( !function_exists('save_property_as_draft') ) {
    function save_property_as_draft() {
        global $current_user;

        wp_get_current_user();
        $userID = $current_user->ID;

        $new_property = array(
            'post_type'	=> 'propertymls'
        );

        $submission_action = isset($_POST['update_property']) ? $_POST['update_property'] : '';

        // Title
        if( isset( $_POST['prop_title']) ) {
            $new_property['post_title'] = sanitize_text_field( $_POST['prop_title'] );
        }
        // Description
        if( isset( $_POST['description'] ) ) {
            $new_property['post_content'] = wp_kses_post( $_POST['description'] );
        }

        $new_property['post_author'] = $userID;

        $prop_id = 0;
        $new_property['post_status'] = 'draft';

        if( isset($_POST['draft_prop_id']) && !empty( $_POST['draft_prop_id'] ) ) {
            $new_property['ID'] = $_POST['draft_prop_id'];
            $prop_id = wp_update_post( $new_property );
        } else {
            $prop_id = wp_insert_post( $new_property );
        }


        if( $prop_id > 0 ) {
            //do_action( 'wp_insert_post', 'wp_insert_post' ); // Post the Post

            //Custom Fields
            if(class_exists('Houzez_Fields_Builder')) {
                $fields_array = Houzez_Fields_Builder::get_form_fields();
                if(!empty($fields_array)):
                    foreach ( $fields_array as $value ):
                        $field_name = $value->field_id;

                        if( isset( $_POST[$field_name] ) ) {
                            update_post_meta( $prop_id, 'fave_'.$field_name, sanitize_text_field( $_POST[$field_name] ) );
                        }

                    endforeach; endif;
            }
            
            // Add price post meta
            if( isset( $_POST['prop_price'] ) ) {
                update_post_meta( $prop_id, 'fave_property_price', sanitize_text_field( $_POST['prop_price'] ) );

                if( isset( $_POST['prop_label'] ) ) {
                    update_post_meta( $prop_id, 'fave_property_price_postfix', sanitize_text_field( $_POST['prop_label']) );
                }
            }

            //price prefix
            if( isset( $_POST['prop_price_prefix'] ) ) {
                update_post_meta( $prop_id, 'fave_property_price_prefix', sanitize_text_field( $_POST['prop_price_prefix']) );
            }

            // Second Price
            if( isset( $_POST['prop_sec_price'] ) ) {
                update_post_meta( $prop_id, 'fave_property_sec_price', sanitize_text_field( $_POST['prop_sec_price'] ) );
            }

            // Area Size
            if( isset( $_POST['prop_size'] ) ) {
                update_post_meta( $prop_id, 'fave_property_size', sanitize_text_field( $_POST['prop_size'] ) );
            }

            // Area Size Prefix
            if( isset( $_POST['prop_size_prefix'] ) ) {
                update_post_meta( $prop_id, 'fave_property_size_prefix', sanitize_text_field( $_POST['prop_size_prefix'] ) );
            }
            // Land Area Size
            if( isset( $_POST['prop_land_area'] ) ) {
                update_post_meta( $prop_id, 'fave_property_land', sanitize_text_field( $_POST['prop_land_area'] ) );
            }

            // Land Area Size Prefix
            if( isset( $_POST['prop_land_area_prefix'] ) ) {
                update_post_meta( $prop_id, 'fave_property_land_postfix', sanitize_text_field( $_POST['prop_land_area_prefix'] ) );
            }

            // Bedrooms
            if( isset( $_POST['prop_beds'] ) ) {
                update_post_meta( $prop_id, 'fave_property_bedrooms', sanitize_text_field( $_POST['prop_beds'] ) );
            }

            // Bathrooms
            if( isset( $_POST['prop_baths'] ) ) {
                update_post_meta( $prop_id, 'fave_property_bathrooms', sanitize_text_field( $_POST['prop_baths'] ) );
            }

            // Garages
            if( isset( $_POST['prop_garage'] ) ) {
                update_post_meta( $prop_id, 'fave_property_garage', sanitize_text_field( $_POST['prop_garage'] ) );
            }

            // Garages Size
            if( isset( $_POST['prop_garage_size'] ) ) {
                update_post_meta( $prop_id, 'fave_property_garage_size', sanitize_text_field( $_POST['prop_garage_size'] ) );
            }

            // Virtual Tour
            if( isset( $_POST['virtual_tour'] ) ) {
                update_post_meta( $prop_id, 'fave_virtual_tour', $_POST['virtual_tour'] );
            }

            // Year Built
            if( isset( $_POST['prop_year_built'] ) ) {
                update_post_meta( $prop_id, 'fave_property_year', sanitize_text_field( $_POST['prop_year_built'] ) );
            }

            // Property ID
            $auto_property_id = blokhausre_option('auto_property_id');
            if( $auto_property_id != 1 ) {
                if (isset($_POST['property_id'])) {
                    update_post_meta($prop_id, 'fave_property_id', sanitize_text_field($_POST['property_id']));
                }
            } else {
                update_post_meta($prop_id, 'fave_property_id', $prop_id );
            }

            // Property Video Url
            if( isset( $_POST['prop_video_url'] ) ) {
                update_post_meta( $prop_id, 'fave_video_url', sanitize_text_field( $_POST['prop_video_url'] ) );
            }

            // property video image - in case of update
            $property_video_image = "";
            $property_video_image_id = 0;
            if( $submission_action == "update_property" ) {
                $property_video_image_id = get_post_meta( $prop_id, 'fave_video_image', true );
                if ( ! empty ( $property_video_image_id ) ) {
                    $property_video_image_src = wp_get_attachment_image_src( $property_video_image_id, 'houzez-property-detail-gallery' );
                    $property_video_image = $property_video_image_src[0];
                }
            }

            // clean up the old meta information related to images when property update
            if( $submission_action == "update_property" ){
                delete_post_meta( $prop_id, 'fave_property_images' );
                delete_post_meta( $prop_id, '_thumbnail_id' );
            }

            // Property Images
            if( isset( $_POST['propperty_image_ids'] ) ) {
                if (!empty($_POST['propperty_image_ids']) && is_array($_POST['propperty_image_ids'])) {
                    $property_image_ids = array();
                    foreach ($_POST['propperty_image_ids'] as $prop_img_id ) {
                        $property_image_ids[] = intval( $prop_img_id );
                        add_post_meta($prop_id, 'fave_property_images', $prop_img_id);
                    }

                    // featured image
                    if( isset( $_POST['featured_image_id'] ) ) {
                        $featured_image_id = intval( $_POST['featured_image_id'] );
                        if( in_array( $featured_image_id, $property_image_ids ) ) {
                            update_post_meta( $prop_id, '_thumbnail_id', $featured_image_id );

                            /* if video url is provided but there is no video image then use featured image as video image */
                            if ( empty( $property_video_image ) && !empty( $_POST['prop_video_url'] ) ) {
                                update_post_meta( $prop_id, 'fave_video_image', $featured_image_id );
                            }
                        }
                    } elseif ( ! empty ( $property_image_ids ) ) {
                        update_post_meta( $prop_id, '_thumbnail_id', $property_image_ids[0] );
                    }
                }
            }

            // Add property type
            if( isset( $_POST['prop_type'] ) && ( $_POST['prop_type'] != '-1' ) ) {
                wp_set_object_terms( $prop_id, intval( $_POST['prop_type'] ), 'property_type' );
            }

            // Add property status
            if( isset( $_POST['prop_status'] ) && ( $_POST['prop_status'] != '-1' ) ) {
                wp_set_object_terms( $prop_id, intval( $_POST['prop_status'] ), 'property_status' );
            }

            // Add property status
            if( isset( $_POST['prop_labels'] ) && ( $_POST['prop_labels'] != '-1' ) ) {
                wp_set_object_terms( $prop_id, intval( $_POST['prop_labels'] ), 'property_label' );
            }

            // Add property city
            if( isset( $_POST['locality'] ) ) {
                $property_city = sanitize_text_field( $_POST['locality'] );
                $city_id = wp_set_object_terms( $prop_id, $property_city, 'property_city' );

                $blokhausre_meta = array();
                $blokhausre_meta['parent_state'] = isset( $_POST['administrative_area_level_1'] ) ? $_POST['administrative_area_level_1'] : '';
                if( !empty( $city_id) ) {
                    update_option('_blokhausre_property_city_' . $city_id[0], $blokhausre_meta);
                }
            }

            // Add property area
            if( isset( $_POST['neighborhood'] ) ) {
                $property_area = sanitize_text_field( $_POST['neighborhood'] );
                $area_id = wp_set_object_terms( $prop_id, $property_area, 'property_area' );

                $blokhausre_meta = array();
                $blokhausre_meta['parent_city'] = isset( $_POST['locality'] ) ? $_POST['locality'] : '';
                if( !empty( $area_id) ) {
                    update_option('_blokhausre_property_area_' . $area_id[0], $blokhausre_meta);
                }
            }

            // Add property state
            if( isset( $_POST['administrative_area_level_1'] ) ) {
                $property_state = sanitize_text_field( $_POST['administrative_area_level_1'] );
                $state_id = wp_set_object_terms( $prop_id, $property_state, 'property_state' );

                $blokhausre_meta = array();
                $blokhausre_meta['parent_country'] = isset( $_POST['country_short'] ) ? $_POST['country_short'] : '';
                if( !empty( $state_id) ) {
                    update_option('_blokhausre_property_state_' . $state_id[0], $blokhausre_meta);
                }
            }

            //echo $_POST['country_short'].' '.$_POST['administrative_area_level_1'].' '.$_POST['locality'].' '.$_POST['neighborhood']; die;

            // Add property features
            if( isset( $_POST['prop_features'] ) ) {
                $features_array = array();
                foreach( $_POST['prop_features'] as $feature_id ) {
                    $features_array[] = intval( $feature_id );
                }
                wp_set_object_terms( $prop_id, $features_array, 'property_feature' );
            }

            // additional details
            if( isset( $_POST['additional_features'] ) ) {
                $additional_features = $_POST['additional_features'];
                if( ! empty( $additional_features ) ) {
                    update_post_meta( $prop_id, 'additional_features', $additional_features );
                    update_post_meta( $prop_id, 'fave_additional_features_enable', 'enable' );
                }
            }

            //Floor Plans
            if( isset( $_POST['floorPlans_enable'] ) ) {
                $floorPlans_enable = $_POST['floorPlans_enable'];
                if( ! empty( $floorPlans_enable ) ) {
                    update_post_meta( $prop_id, 'fave_floor_plans_enable', $floorPlans_enable );
                }
            }

            if( isset( $_POST['floor_plans'] ) ) {
                $floor_plans_post = $_POST['floor_plans'];
                if( ! empty( $floor_plans_post ) ) {
                    update_post_meta( $prop_id, 'floor_plans', $floor_plans_post );
                }
            }

            //Multi-units / Sub-properties
            if( isset( $_POST['multiUnits'] ) ) {
                $multiUnits_enable = $_POST['multiUnits'];
                if( ! empty( $multiUnits_enable ) ) {
                    update_post_meta( $prop_id, 'fave_multiunit_plans_enable', $multiUnits_enable );
                }
            }

            if( isset( $_POST['fave_multi_units'] ) ) {
                $fave_multi_units = $_POST['fave_multi_units'];
                if( ! empty( $fave_multi_units ) ) {
                    update_post_meta( $prop_id, 'fave_multi_units', $fave_multi_units );
                }
            }

            // Make featured
            if( isset( $_POST['prop_featured'] ) ) {
                $featured = intval( $_POST['prop_featured'] );
                update_post_meta( $prop_id, 'fave_featured', $featured );
            }

            // Private Note
            if( isset( $_POST['private_note'] ) ) {
                $private_note = wp_kses_post( $_POST['private_note'] );
                update_post_meta( $prop_id, 'fave_private_note', $private_note );
            }

            // Property Payment
            if( isset( $_POST['prop_payment'] ) ) {
                $prop_payment = sanitize_text_field( $_POST['prop_payment'] );
                update_post_meta( $prop_id, 'fave_payment_status', $prop_payment );
            }

             //Energy Class
            if(isset($_POST['energy_class'])) {
                $energy_class = sanitize_text_field($_POST['energy_class']);
                update_post_meta( $prop_id, 'fave_energy_class', $energy_class );
            }
            if(isset($_POST['energy_global_index'])) {
                $energy_global_index = sanitize_text_field($_POST['energy_global_index']);
                update_post_meta( $prop_id, 'fave_energy_global_index', $energy_global_index );
            }
            if(isset($_POST['renewable_energy_global_index'])) {
                $renewable_energy_global_index = sanitize_text_field($_POST['renewable_energy_global_index']);
                update_post_meta( $prop_id, 'fave_renewable_energy_global_index', $renewable_energy_global_index );
            }
            if(isset($_POST['energy_performance'])) {
                $energy_performance = sanitize_text_field($_POST['energy_performance']);
                update_post_meta( $prop_id, 'fave_energy_performance', $energy_performance );
            }

            // Property Agent
            if( isset( $_POST['fave_agent_display_option'] ) ) {

                $prop_agent_display_option = sanitize_text_field( $_POST['fave_agent_display_option'] );
                if( $prop_agent_display_option == 'agent_info' ) {

                    $prop_agent = sanitize_text_field( $_POST['fave_agents'] );
                    update_post_meta( $prop_id, 'fave_agent_display_option', $prop_agent_display_option );
                    update_post_meta( $prop_id, 'fave_agents', $prop_agent );

                } else {
                    update_post_meta( $prop_id, 'fave_agent_display_option', $prop_agent_display_option );
                }

            } else {
                update_post_meta( $prop_id, 'fave_agent_display_option', 'author_info' );
            }

            // Address
            if( isset( $_POST['property_map_address'] ) ) {
                update_post_meta( $prop_id, 'fave_property_map_address', sanitize_text_field( $_POST['property_map_address'] ) );
                update_post_meta( $prop_id, 'fave_property_address', sanitize_text_field( $_POST['property_map_address'] ) );
            }

            if( ( isset($_POST['lat']) && !empty($_POST['lat']) ) && (  isset($_POST['lng']) && !empty($_POST['lng'])  ) ) {
                $lat = sanitize_text_field( $_POST['lat'] );
                $lng = sanitize_text_field( $_POST['lng'] );
                $streetView = sanitize_text_field( $_POST['prop_google_street_view'] );
                $lat_lng = $lat.','.$lng;

                update_post_meta( $prop_id, 'blokhausre_geolocation_lat', $lat );
                update_post_meta( $prop_id, 'blokhausre_geolocation_long', $lng );
                update_post_meta( $prop_id, 'fave_property_location', $lat_lng );
                update_post_meta( $prop_id, 'fave_property_map', '1' );
                update_post_meta( $prop_id, 'fave_property_map_street_view', $streetView );

            }
            // Country
            if( isset( $_POST['country_short'] ) ) {
                update_post_meta( $prop_id, 'fave_property_country', sanitize_text_field( $_POST['country_short'] ) );
            }
            // Postal Code
            if( isset( $_POST['postal_code'] ) ) {
                update_post_meta( $prop_id, 'fave_property_zip', sanitize_text_field( $_POST['postal_code'] ) );
            }
        }
        echo json_encode( array( 'success' => true, 'property_id' => $prop_id, 'msg' => esc_html__('Successfull', 'houzez') ) );
        wp_die();
    }
}

/*-----------------------------------------------------------------------------------*/
// validate Email
/*-----------------------------------------------------------------------------------*/
add_action('wp_ajax_blokhausre_check_email', 'blokhausre_check_email');
add_action('wp_ajax_nopriv_blokhausre_check_email', 'blokhausre_check_email');

if( !function_exists('blokhausre_check_email') ) {
    function blokhausre_check_email() {
        $allowed_html = array();
        $email = wp_kses( $_POST['useremail'], $allowed_html );

        if( email_exists( $email ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('This email address is already registered.', 'houzez') ) );
            wp_die();
        
        } elseif( !is_email( $email ) ) {
            echo json_encode( array( 'success' => false, 'msg' => esc_html__('Invalid email address.', 'houzez') ) );
            wp_die();
        } else {
            echo json_encode( array( 'success' => true, 'msg' => esc_html__('Successfull', 'houzez') ) );
            wp_die();
        }

        wp_die();
    }
}

/*-----------------------------------------------------------------------------------*/
// Add custom post status Expired
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists('blokhausre_custom_post_status') ) {
    function blokhausre_custom_post_status() {

        $args = array(
            'label'                     => _x( 'Expired', 'Status General Name', 'houzez' ),
            'label_count'               => _n_noop( 'Expired (%s)',  'Expired (%s)', 'houzez' ),
            'public'                    => true,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'exclude_from_search'       => false,
        );
        register_post_status( 'expired', $args );

    }
    add_action( 'init', 'blokhausre_custom_post_status', 1 );
}

/*-----------------------------------------------------------------------------------*/
// Add custom post status Hold
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists('blokhausre_custom_post_status_on_hold') ) {
    function blokhausre_custom_post_status_on_hold() {

        $args = array(
            'label'                     => _x( 'On Hold', 'Status General Name', 'houzez' ),
            'label_count'               => _n_noop( 'On Hold (%s)',  'On Hold (%s)', 'houzez' ),
            'public'                    => true,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'exclude_from_search'       => false,
        );
        register_post_status( 'on_hold', $args );

    }
    add_action( 'init', 'blokhausre_custom_post_status_on_hold', 1 );
}

add_action( 'wp_ajax_blokhausre_save_search', 'blokhausre_save_search' );
if( !function_exists('blokhausre_save_search') ) {
    function blokhausre_save_search() {

        $nonce = $_REQUEST['blokhausre_save_search_ajax'];
        if( !wp_verify_nonce( $nonce, 'houzez-save-search-nounce' ) ) {
            echo json_encode(array(
                'success' => false,
                'msg' => esc_html__( 'Unverified Nonce!', 'houzez')
            ));
            wp_die();
        }

        global $wpdb, $current_user;

        wp_get_current_user();
        $userID       =  $current_user->ID;
        $userEmail    =  $current_user->user_email;
        $search_args  =  $_REQUEST['search_args'];
        $table_name   = $wpdb->prefix . 'blokhausre_search';
        $request_url  = $_REQUEST['search_URI'];

        $wpdb->insert(
            $table_name,
            array(
                'auther_id' => $userID,
                'query'     => $search_args,
                'email'     => $userEmail,
                'url'       => $request_url,
                'time'      => current_time( 'mysql' )
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%s'
            )
        );

        echo json_encode( array( 'success' => true, 'msg' => esc_html__('Search is saved. You will receive an email notification when new properties matching your search will be published', 'houzez') ) );
        wp_die();
    }
}

/*-----------------------------------------------------------------------------------*/
/*     Remove Search
/*-----------------------------------------------------------------------------------*/

add_action('wp_ajax_blokhausre_delete_search', 'blokhausre_delete_search');
if(!function_exists('blokhausre_delete_search') ) {
    function blokhausre_delete_search () {
        global $current_user;
        wp_get_current_user();
        $userID = $current_user->ID;

        $property_id = intval( $_POST['property_id']);

        if( !is_numeric( $property_id ) ){
            echo json_encode( array(
                'success' => false,
                'msg' => esc_html__('you don\'t have the right to delete this', 'houzez')
            ));
            wp_die();
        }else{

            global $wpdb;

            $table_name     = $wpdb->prefix . 'blokhausre_search';
            $results        = $wpdb->get_row( 'SELECT * FROM ' . $table_name . ' WHERE id = ' . $property_id );
            if ( $userID != $results->auther_id ) :

                echo json_encode( array(
                    'success' => false,
                    'msg' => esc_html__('you don\'t have the right to delete this', 'houzez')
                ));

                wp_die();

            else :

                $wpdb->delete( $table_name, array( 'id' => $property_id ), array( '%d' ) );

                echo json_encode( array(
                    'success' => true,
                    'msg' => esc_html__('Deleted Successfully', 'houzez')
                ));

                wp_die();

            endif;
        }
    }
}


/*-----------------------------------------------------------------------------------*/
// Property paypal payment
/*-----------------------------------------------------------------------------------*/
add_action('wp_ajax_blokhausre_property_paypal_payment', 'blokhausre_property_paypal_payment');
if( !function_exists('blokhausre_property_paypal_payment') ) {
    function blokhausre_property_paypal_payment() {
        global $current_user;
        $propID        =   intval($_POST['prop_id']);
        $is_prop_featured   =   intval($_POST['is_prop_featured']);
        $is_prop_upgrade    =   intval($_POST['is_prop_upgrade']);
        $price_per_submission = blokhausre_option('price_listing_submission');
        $price_featured_submission = blokhausre_option('price_featured_listing_submission');
        $currency = blokhausre_option('currency_paid_submission');

        $blogInfo = esc_url( home_url() );

        wp_get_current_user();
        $userID =   $current_user->ID;
        $post   =   get_post($propID);

        if( $post->post_author != $userID ){
            wp_die('Are you kidding?');
        }

        $is_paypal_live             =   blokhausre_option('paypal_api');
        $host                       =   'https://api.sandbox.paypal.com';
        $price_per_submission       =   floatval( $price_per_submission );
        $price_featured_submission  =   floatval( $price_featured_submission );
        $submission_curency         =   esc_html( $currency );
        $payment_description        =   esc_html__('Listing payment on ','houzez').$blogInfo;

        if( $is_prop_featured == 0 ) {
            $total_price =  number_format( $price_per_submission, 2, '.','' );
        } else {
            $total_price = $price_per_submission + $price_featured_submission;
            $total_price = number_format( $total_price, 2, '.','' );
        }

        if ( $is_prop_upgrade == 1 ) {
            $total_price     =  number_format($price_featured_submission, 2, '.','');
            $payment_description =   esc_html__('Upgrade to featured listing on ','houzez').$blogInfo;
        }

        // Check if payal live
        if( $is_paypal_live =='live'){
            $host='https://api.paypal.com';
        }

        $url             =   $host.'/v1/oauth2/token';
        $postArgs        =   'grant_type=client_credentials';

        // Get Access token
        $paypal_token    =   blokhausre_get_paypal_access_token( $url, $postArgs );
        $url             =   $host.'/v1/payments/payment';
        $cancel_link     =   blokhausre_dashboard_listings();
        $return_link     =   blokhausre_get_template_link('template/template-thankyou.php');

        $payment = array(
            'intent' => 'sale',
            "redirect_urls" => array(
                "return_url" => $return_link,
                "cancel_url" => $cancel_link
            ),
            'payer' => array("payment_method" => "paypal"),
        );

        /* Prepare basic payment details
        *--------------------------------------*/
        $payment['transactions'][0] = array(
            'amount' => array(
                'total' => $total_price,
                'currency' => $submission_curency,
                'details' => array(
                    'subtotal' => $total_price,
                    'tax' => '0.00',
                    'shipping' => '0.00'
                )
            ),
            'description' => $payment_description
        );


        /* Prepare individual items
        *--------------------------------------*/
        if( $is_prop_upgrade == 1 ) {

            $payment['transactions'][0]['item_list']['items'][] = array(
                'quantity' => '1',
                'name' => esc_html__('Upgrade to Featured Listing','houzez'),
                'price' => $total_price,
                'currency' => $submission_curency,
                'sku' => 'Upgrade Listing',
            );

        } else {
            if( $is_prop_featured == 1 ) {

                $payment['transactions'][0]['item_list']['items'][] = array(
                    'quantity' => '1',
                    'name' => esc_html__('Listing with Featured Payment option','houzez'),
                    'price' => $total_price,
                    'currency' => $submission_curency,
                    'sku' => 'Featured Paid Listing',
                );

            } else {
                $payment['transactions'][0]['item_list']['items'][] = array(
                    'quantity' => '1',
                    'name' => esc_html__('Listing Payment','houzez'),
                    'price' => $total_price,
                    'currency' => $submission_curency,
                    'sku' => 'Paid Listing',
                );
            }
        }

        /* Convert PHP array into json format
        *--------------------------------------*/
        $jsonEncode = json_encode($payment);
        $json_response = blokhausre_execute_paypal_request( $url, $jsonEncode, $paypal_token );

        //print_r($json_response);
        foreach ($json_response['links'] as $link) {
            if($link['rel'] == 'execute'){
                $payment_execute_url = $link['href'];
            } else  if($link['rel'] == 'approval_url'){
                $payment_approval_url = $link['href'];
            }
        }

        // Save data in database for further use on processor page
        $output['payment_execute_url'] = $payment_execute_url;
        $output['paypal_token']        = $paypal_token;
        $output['property_id']         = $propID;
        $output['is_prop_featured']    = $is_prop_featured;
        $output['is_prop_upgrade']     = $is_prop_upgrade;

        $save_output[$current_user->ID]   =   $output;
        update_option('blokhausre_paypal_transfer',$save_output);

        print $payment_approval_url;

        wp_die();

    }
}


/* -----------------------------------------------------------------------------------------------------------
 *  Resend Property for Approval per listing
 -------------------------------------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_blokhausre_resend_for_approval_perlisting', 'blokhausre_resend_for_approval_perlisting' );
add_action( 'wp_ajax_blokhausre_resend_for_approval_perlisting', 'blokhausre_resend_for_approval_perlisting' );

if( !function_exists('blokhausre_resend_for_approval_perlisting') ):

    function blokhausre_resend_for_approval_perlisting() {

        global $current_user;
        $prop_id = intval($_POST['propid']);

        wp_get_current_user();
        $userID = $current_user->ID;
        $post   = get_post($prop_id);

        if( $post->post_author != $userID){
            wp_die('get out of my cloud');
        }

        $prop = array(
            'ID'            => $prop_id,
            'post_type'     => 'property',
            'post_status'   => 'pending',
            'post_date'     => current_time( 'mysql' ),
            'post_date_gmt'     => current_time( 'mysql' ),
        );
        wp_update_post( $prop );
        update_post_meta( $prop_id, 'fave_featured', 0 );
        update_post_meta( $prop_id, 'fave_payment_status', 'not_paid' );

        echo json_encode( array( 'success' => true, 'msg' => esc_html__('Sent for approval','houzez') ) );

        $submit_title =   get_the_title( $prop_id) ;

        $args = array(
            'submission_title' =>  $submit_title,
            'submission_url'   =>  get_permalink( $prop_id )
        );
        blokhausre_email_type( get_option('admin_email'), 'admin_expired_listings', $args );

        wp_die();



    }

endif; // end

/*-----------------------------------------------------------------------------------*/
// Simple property filter
/*-----------------------------------------------------------------------------------*/
if( !function_exists('blokhausre_property_filter') ) {
    function blokhausre_property_filter( $property_query_args ) {
        global $paged;

        $page_id = get_the_ID();
        $what_to_show = get_post_meta( $page_id, 'fave_properties_sort', true );
        $fave_prop_no = get_post_meta( $page_id, 'fave_prop_no', true );
        $fave_listings_tabs = get_post_meta( $page_id, 'fave_listings_tabs', true );

        $tax_query = array();
        $meta_query = array();

        if ( is_front_page()  ) {
            $paged = (get_query_var('page')) ? get_query_var('page') : 1;
        }

        if(!$fave_prop_no){
            $property_query_args[ 'posts_per_page' ]  = 9;
        } else {
            $property_query_args[ 'posts_per_page' ] = $fave_prop_no;
        }

        if (!empty($paged)) {
            $property_query_args['paged'] = $paged;
        } else {
            $property_query_args['paged'] = 1;
        }

        if($what_to_show == 'x_featured_first' || $what_to_show == 'x_rand_featured_first') { 
            $meta_query[] = array(
                'key' => 'fave_featured',
                'value' => '0',
                'compare' => '='
            );
        }

        if ( isset( $_GET['tab'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => $_GET['tab']
            );
        }

        $states = get_post_meta( $page_id, 'fave_states', false );
        if ( ! empty( $states ) && is_array( $states ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_state',
                'field' => 'slug',
                'terms' => $states
            );
        }

        $locations = get_post_meta( $page_id, 'fave_locations', false );
        if ( ! empty( $locations ) && is_array( $locations ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_city',
                'field' => 'slug',
                'terms' => $locations
            );
        }

        $types = get_post_meta( $page_id, 'fave_types', false );
        if ( ! empty( $types ) && is_array( $types ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $types
            );
        }

        $labels = get_post_meta( $page_id, 'fave_labels', false );
        if ( ! empty( $labels ) && is_array( $labels ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_label',
                'field' => 'slug',
                'terms' => $labels
            );
        }

        $fave_areas = get_post_meta( $page_id, 'fave_area', false );
        if ( ! empty( $fave_areas ) && is_array( $fave_areas ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_area',
                'field' => 'slug',
                'terms' => $fave_areas
            );
        }

        $features = get_post_meta( $page_id, 'fave_features', false );
        if ( ! empty( $features ) && is_array( $features ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_feature',
                'field' => 'slug',
                'terms' => $features
            );
        }

        if( !isset( $_GET['tab'] ) ) {
            $status = get_post_meta($page_id, 'fave_status', false);
            if (!empty($status) && is_array($status)) {
                $tax_query[] = array(
                    'taxonomy' => 'property_status',
                    'field' => 'slug',
                    'terms' => $status
                );
            }
        }

        $min_price = get_post_meta( $page_id, 'fave_min_price', true );
        $max_price = get_post_meta( $page_id, 'fave_max_price', true );

        // min and max price logic
        if (!empty($min_price) && !empty($max_price)) {
            $min_price = doubleval(blokhausre_clean($min_price));
            $max_price = doubleval(blokhausre_clean($max_price));

            if ($min_price >= 0 && $max_price > $min_price) {
                $meta_query[] = array(
                    'key' => 'fave_property_price',
                    'value' => array($min_price, $max_price),
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN',
                );
            }
        } else if (!empty($min_price)) {
            $min_price = doubleval(blokhausre_clean($min_price));
            if ($min_price >= 0) {
                $meta_query[] = array(
                    'key' => 'fave_property_price',
                    'value' => $min_price,
                    'type' => 'NUMERIC',
                    'compare' => '>=',
                );
            }
        } else if (!empty($max_price)) {
            $max_price = doubleval(blokhausre_clean($max_price));
            if ($max_price >= 0) {
                $meta_query[] = array(
                    'key' => 'fave_property_price',
                    'value' => $max_price,
                    'type' => 'NUMERIC',
                    'compare' => '<=',
                );
            }
        }

        $meta_count = count($meta_query);
        if( $meta_count > 1 ) {
            $meta_query['relation'] = 'AND';
        }
        if ($meta_count > 0) {
            $property_query_args['meta_query'] = $meta_query;
        }


        $tax_count = count( $tax_query );
        if( $tax_count > 1 ) {
            $tax_query['relation'] = 'AND';
        }
        if( $tax_count > 0 ) {
            $property_query_args['tax_query'] = $tax_query;
        }
        //print_r($property_query_args);
        return $property_query_args;
    }
}
add_filter('blokhausre_property_filter', 'blokhausre_property_filter');

/*-----------------------------------------------------------------------------------*/
// Featured property filter
/*-----------------------------------------------------------------------------------*/
if( !function_exists('blokhausre_featured_property_filter') ) {
    function blokhausre_featured_property_filter( $property_query_args ) {
        global $paged;

        $page_id = get_the_ID();
        $what_to_show = get_post_meta( $page_id, 'fave_properties_sort', true );
        $fave_featured_prop_no = get_post_meta( $page_id, 'fave_featured_prop_no', true );
        $fave_listings_tabs = get_post_meta( $page_id, 'fave_listings_tabs', true );

        $tax_query = array();
        $meta_query = array();

        if ( is_front_page()  ) {
            $paged = (get_query_var('page')) ? get_query_var('page') : 1;
        }

        if(!$fave_featured_prop_no){
            $property_query_args[ 'posts_per_page' ]  = 4;
        } else {
            $property_query_args[ 'posts_per_page' ] = $fave_featured_prop_no;
        }

        if (!empty($paged)) {
            $property_query_args['paged'] = $paged;
        } else {
            $property_query_args['paged'] = 1;
        }

        $meta_query[] = array(
            'key' => 'fave_featured',
            'value' => '1',
            'compare' => '='
        );


        if($what_to_show == 'x_rand_featured_first') { 
            $property_query_args['orderby'] = 'rand';
        }

        if ( isset( $_GET['tab'] ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => $_GET['tab']
            );
        }

        $states = get_post_meta( $page_id, 'fave_states', false );
        if ( ! empty( $states ) && is_array( $states ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_state',
                'field' => 'slug',
                'terms' => $states
            );
        }

        $locations = get_post_meta( $page_id, 'fave_locations', false );
        if ( ! empty( $locations ) && is_array( $locations ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_city',
                'field' => 'slug',
                'terms' => $locations
            );
        }

        $types = get_post_meta( $page_id, 'fave_types', false );
        if ( ! empty( $types ) && is_array( $types ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $types
            );
        }

        $features = get_post_meta( $page_id, 'fave_features', false );
        if ( ! empty( $features ) && is_array( $features ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_feature',
                'field' => 'slug',
                'terms' => $features
            );
        }

        if( !isset( $_GET['tab'] ) ) {
            $status = get_post_meta($page_id, 'fave_status', false);
            if (!empty($status) && is_array($status)) {
                $tax_query[] = array(
                    'taxonomy' => 'property_status',
                    'field' => 'slug',
                    'terms' => $status
                );
            }
        }

        $labels = get_post_meta( $page_id, 'fave_labels', false );
        if ( ! empty( $labels ) && is_array( $labels ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_label',
                'field' => 'slug',
                'terms' => $labels
            );
        }

        $fave_areas = get_post_meta( $page_id, 'fave_area', false );
        if ( ! empty( $fave_areas ) && is_array( $fave_areas ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_area',
                'field' => 'slug',
                'terms' => $fave_areas
            );
        }

        $min_price = get_post_meta( $page_id, 'fave_min_price', false );
        $max_price = get_post_meta( $page_id, 'fave_max_price', false );

        // min and max price logic
        if (!empty($min_price) && !empty($max_price)) {
            $min_price = doubleval(blokhausre_clean($min_price));
            $max_price = doubleval(blokhausre_clean($max_price));

            if ($min_price >= 0 && $max_price > $min_price) {
                $meta_query[] = array(
                    'key' => 'fave_property_price',
                    'value' => array($min_price, $max_price),
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN',
                );
            }
        } else if (!empty($min_price)) {
            $min_price = doubleval(blokhausre_clean($min_price));
            if ($min_price >= 0) {
                $meta_query[] = array(
                    'key' => 'fave_property_price',
                    'value' => $min_price,
                    'type' => 'NUMERIC',
                    'compare' => '>=',
                );
            }
        } else if (!empty($max_price)) {
            $max_price = doubleval(blokhausre_clean($max_price));
            if ($max_price >= 0) {
                $meta_query[] = array(
                    'key' => 'fave_property_price',
                    'value' => $max_price,
                    'type' => 'NUMERIC',
                    'compare' => '<=',
                );
            }
        }

        $meta_count = count($meta_query);
        if( $meta_count > 1 ) {
            $meta_query['relation'] = 'AND';
        }
        if ($meta_count > 0) {
            $property_query_args['meta_query'] = $meta_query;
        }


        $tax_count = count( $tax_query );
        if( $tax_count > 1 ) {
            $tax_query['relation'] = 'AND';
        }
        if( $tax_count > 0 ) {
            $property_query_args['tax_query'] = $tax_query;
        }

        return $property_query_args;
    }
}
add_filter('blokhausre_featured_property_filter', 'blokhausre_featured_property_filter');


/*-----------------------------------------------------------------------------------*/
// Search Radius filter
/*-----------------------------------------------------------------------------------*/
add_filter('blokhausre_radius_filter', 'blokhausre_radius_filter_callback', 10, 6);
if( !function_exists('blokhausre_radius_filter_callback') ) {
    function blokhausre_radius_filter_callback( $query_args, $search_lat, $search_long, $search_radius, $use_radius, $location ) {

        global $wpdb;

        if ( ! ( $use_radius && $search_lat && $search_long && $search_radius ) || ! $location ) {
            return $query_args;
        }

        $radius_unit = blokhausre_option('radius_unit');
        if( $radius_unit == 'km' ) {
            $earth_radius = 6371;
        } elseif ( $radius_unit == 'mi' ) {
            $earth_radius = 3959;
        } else {
            $earth_radius = 6371;
        }

        $sql = $wpdb->prepare( "SELECT $wpdb->posts.ID,
				( %s * acos(
					cos( radians(%s) ) *
					cos( radians( latitude.meta_value ) ) *
					cos( radians( longitude.meta_value ) - radians(%s) ) +
					sin( radians(%s) ) *
					sin( radians( latitude.meta_value ) )
				) )
				AS distance, latitude.meta_value AS latitude, longitude.meta_value AS longitude
				FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta
					AS latitude
					ON $wpdb->posts.ID = latitude.post_id
				INNER JOIN $wpdb->postmeta
					AS longitude
					ON $wpdb->posts.ID = longitude.post_id
				WHERE 1=1
					AND ($wpdb->posts.post_status = 'publish' )
					AND latitude.meta_key='blokhausre_geolocation_lat'
					AND longitude.meta_key='blokhausre_geolocation_long'
				HAVING distance < %s
				ORDER BY $wpdb->posts.menu_order ASC, distance ASC",
            $earth_radius,
            $search_lat,
            $search_long,
            $search_lat,
            $search_radius
        );
        $post_ids = $wpdb->get_results( $sql, OBJECT_K );

        if ( empty( $post_ids ) || ! $post_ids ) {
            $post_ids = array(0);
        }

        $query_args[ 'post__in' ] = array_keys( (array) $post_ids );
        return $query_args;
    }
}




/*-----------------------------------------------------------------------------------*/
/*	Get Properties for Header Map
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_blokhausre_header_map_listings', 'blokhausre_header_map_listings' );
add_action( 'wp_ajax_blokhausre_header_map_listings', 'blokhausre_header_map_listings' );
if( !function_exists('blokhausre_header_map_listings') ) {
    function blokhausre_header_map_listings() {
        check_ajax_referer('blokhausre_header_map_ajax_nonce', 'security');

        $meta_query = array();
        $tax_query = array();
        $date_query = array();
        $allowed_html = array();
        $keyword_array =  '';

        
        $dummy_array = array();

        $custom_fields_values = isset($_POST['custom_fields_values']) ? $_POST['custom_fields_values'] : '';
        
        if(!empty($custom_fields_values)) {
            foreach ($custom_fields_values as $value) {
               $dummy_array[] = $value;
            }
        }


        $initial_city = isset($_POST['initial_city']) ? $_POST['initial_city'] : '';
        $features = isset($_POST['features']) ? $_POST['features'] : '';
        $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
        $country = isset($_POST['country']) ? ($_POST['country']) : '';
        $state = isset($_POST['state']) ? ($_POST['state']) : '';
        $location = isset($_POST['location']) ? ($_POST['location']) : '';
        $area = isset($_POST['area']) ? ($_POST['area']) : '';
        $status = isset($_POST['status']) ? ($_POST['status']) : '';
        $type = isset($_POST['type']) ? ($_POST['type']) : '';
        $label = isset($_POST['label']) ? ($_POST['label']) : '';
        $property_id = isset($_POST['property_id']) ? ($_POST['property_id']) : '';
        $bedrooms = isset($_POST['bedrooms']) ? ($_POST['bedrooms']) : '';
        $bathrooms = isset($_POST['bathrooms']) ? ($_POST['bathrooms']) : '';
        $min_price = isset($_POST['min_price']) ? ($_POST['min_price']) : '';
        $max_price = isset($_POST['max_price']) ? ($_POST['max_price']) : '';
        $currency = isset($_POST['currency']) ? ($_POST['currency']) : '';
        $min_area = isset($_POST['min_area']) ? ($_POST['min_area']) : '';
        $max_area = isset($_POST['max_area']) ? ($_POST['max_area']) : '';
        $publish_date = isset($_POST['publish_date']) ? ($_POST['publish_date']) : '';

        $search_location = isset( $_POST[ 'search_location' ] ) ? esc_attr( $_POST[ 'search_location' ] ) : false;
        $use_radius = isset( $_POST[ 'use_radius' ] ) && 'on' == $_POST[ 'use_radius' ];
        $search_lat = isset($_POST['search_lat']) ? (float) $_POST['search_lat'] : false;
        $search_long = isset($_POST['search_long']) ? (float) $_POST['search_long'] : false;
        $search_radius = isset($_POST['search_radius']) ? (int) $_POST['search_radius'] : false;


        $prop_locations = array();
        blokhausre_get_terms_array( 'property_city', $prop_locations );

        $keyword_field = blokhausre_option('keyword_field');
        $beds_baths_search = blokhausre_option('beds_baths_search');
        $property_id_prefix = blokhausre_option('property_id_prefix');

        $property_id = str_replace($property_id_prefix, "", $property_id);

        $search_criteria = '=';
        if( $beds_baths_search == 'greater') {
            $search_criteria = '>=';
        }

        $query_args = array(
            'post_type' => 'property',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        );

        $query_args = apply_filters('blokhausre_radius_filter', $query_args, $search_lat, $search_long, $search_radius, $use_radius, $search_location );

        $keyword = stripcslashes($keyword);

        if ( $keyword != '') {

            if( $keyword_field == 'prop_address' ) {
                $meta_keywork = wp_kses($keyword, $allowed_html);
                $address_array = array(
                    'key' => 'fave_property_map_address',
                    'value' => $meta_keywork,
                    'type' => 'CHAR',
                    'compare' => 'LIKE',
                );

                $street_array = array(
                    'key' => 'fave_property_address',
                    'value' => $meta_keywork,
                    'type' => 'CHAR',
                    'compare' => 'LIKE',
                );

                $zip_array = array(
                    'key' => 'fave_property_zip',
                    'value' => $meta_keywork,
                    'type' => 'CHAR',
                    'compare' => '=',
                );
                $propid_array = array(
                    'key' => 'fave_property_id',
                    'value' => str_replace($property_id_prefix, "", $meta_keywork),
                    'type' => 'CHAR',
                    'compare' => '=',
                );

                $keyword_array = array(
                    'relation' => 'OR',
                    $address_array,
                    $street_array,
                    $propid_array,
                    $zip_array
                );
            } else if( $keyword_field == 'prop_city_state_county' ) {
                $taxlocation[] = sanitize_title (  esc_html( wp_kses($keyword, $allowed_html) ) );

                $_tax_query = Array();
                $_tax_query['relation'] = 'OR';

                $_tax_query[] = array(
                    'taxonomy'     => 'property_area',
                    'field'        => 'slug',
                    'terms'        => $taxlocation
                );

                $_tax_query[] = array(
                    'taxonomy'     => 'property_city',
                    'field'        => 'slug',
                    'terms'        => $taxlocation
                );

                $_tax_query[] = array(
                    'taxonomy'      => 'property_state',
                    'field'         => 'slug',
                    'terms'         => $taxlocation
                );
                $tax_query[] = $_tax_query;

            } else {
                $keyword = trim( $keyword );
                if ( ! empty( $keyword ) ) {
                    $query_args['s'] = $keyword;
                }
            }
        }

        //Date Query
        if( !empty($publish_date) ) {
            $publish_date = explode('/', $publish_date);
            $query_args['date_query'] = array(
                array(
                    'year' => $publish_date[2],
                    'compare'   => '>=',
                ),
                array(
                    'month' => $publish_date[1],
                    'compare'   => '>=',
                ),
                array(
                    'day' => $publish_date[0],
                    'compare'   => '>=',
                )
            );
        }


        //Custom Fields
        if(class_exists('Houzez_Fields_Builder')) {
            $fields_array = Houzez_Fields_Builder::get_form_fields();
            if(!empty($fields_array)):
                
                foreach ( $fields_array as $key => $value ):
                    $field_title = $value->label;
                    $field_name = $value->field_id;
                    $is_search = $value->is_search;

                    if( $is_search == 'yes' ) {
                        if(!empty($dummy_array[$key])) {
                            $meta_query[] = array(
                                'key' => 'fave_'.$field_name,
                                'value' => $dummy_array[$key],
                                'type' => 'CHAR',
                                'compare' => '=',
                            );
                        }
                    }
                endforeach; endif;
        }

        if(!empty($currency)) {
            $meta_query[] = array(
                'key' => 'fave_currency',
                'value' => $currency,
                'type' => 'CHAR',
                'compare' => '=',
            );
        }

        // Meta Queries
        $meta_query[] = array(
            'key' => 'fave_property_map_address',
            'compare' => 'EXISTS',
        );

        // Property ID
        if( !empty( $property_id )  ) {
            $meta_query[] = array(
                'key' => 'fave_property_id',
                'value'   => $property_id,
                'type'    => 'char',
                'compare' => '=',
            );
        }

        if( !empty($location) && $location != 'all' ) {
            $tax_query[] = array(
                'taxonomy' => 'property_city',
                'field' => 'slug',
                'terms' => $location
            );

        } else {
            if( $location == 'all' ) {
                /*$tax_query[] = array(
                    'taxonomy' => 'property_city',
                    'field' => 'slug',
                    'terms' => $prop_locations
                );*/
            } else {
                if (!empty($initial_city)) {
                    $tax_query[] = array(
                        'taxonomy' => 'property_city',
                        'field' => 'slug',
                        'terms' => $initial_city
                    );
                }
            }
        }

        if( !empty($area) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_area',
                'field' => 'slug',
                'terms' => $area
            );
        }
        if( !empty($state) ) {
            $tax_query[] = array(
                'taxonomy'      => 'property_state',
                'field'         => 'slug',
                'terms'         => $state
            );
        }

        if( !empty( $country ) ) {
            $meta_query[] = array(
                'key' => 'fave_property_country',
                'value'   => $country,
                'type'    => 'CHAR',
                'compare' => '=',
            );
        }

        if( !empty($status) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => $status
            );
        }
        if( !empty($type) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $type
            );
        }

        if ( !empty($label) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_label',
                'field' => 'slug',
                'terms' => $label
            );
        }

        if( !empty( $features ) ) {

            foreach ($features as $feature):
                $tax_query[] = array(
                    'taxonomy' => 'property_feature',
                    'field' => 'slug',
                    'terms' => $feature
                );
            endforeach;
        }

        // bedrooms logic
        if( !empty( $bedrooms ) && $bedrooms != 'any'  ) {
            $bedrooms = sanitize_text_field($bedrooms);
            $meta_query[] = array(
                'key' => 'fave_property_bedrooms',
                'value'   => $bedrooms,
                'type'    => 'CHAR',
                'compare' => $search_criteria,
            );
        }

        // bathrooms logic
        if( !empty( $bathrooms ) && $bathrooms != 'any'  ) {
            $bathrooms = sanitize_text_field($bathrooms);
            $meta_query[] = array(
                'key' => 'fave_property_bathrooms',
                'value'   => $bathrooms,
                'type'    => 'CHAR',
                'compare' => $search_criteria,
            );
        }

        // min and max price logic
        if( !empty( $min_price ) && $min_price != 'any' && !empty( $max_price ) && $max_price != 'any' ) {
            $min_price = doubleval( blokhausre_clean( $min_price ) );
            $max_price = doubleval( blokhausre_clean( $max_price ) );

            if( $min_price > 0 && $max_price > $min_price ) {
                $meta_query[] = array(
                    'key' => 'fave_property_price',
                    'value' => array($min_price, $max_price),
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN',
                );
            }
        } else if( !empty( $min_price ) && $min_price != 'any'  ) {
            $min_price = doubleval( blokhausre_clean( $min_price ) );
            if( $min_price > 0 ) {
                $meta_query[] = array(
                    'key' => 'fave_property_price',
                    'value' => $min_price,
                    'type' => 'NUMERIC',
                    'compare' => '>=',
                );
            }
        } else if( !empty( $max_price ) && $max_price != 'any'  ) {
            $max_price = doubleval( blokhausre_clean( $max_price ) );
            if( $max_price > 0 ) {
                $meta_query[] = array(
                    'key' => 'fave_property_price',
                    'value' => $max_price,
                    'type' => 'NUMERIC',
                    'compare' => '<=',
                );
            }
        }

        // min and max area logic
        if( !empty( $min_area ) && !empty( $max_area ) ) {
            $min_area = intval( $min_area );
            $max_area = intval( $max_area );

            if( $min_area > 0 && $max_area > $min_area ) {
                $meta_query[] = array(
                    'key' => 'fave_property_size',
                    'value' => array($min_area, $max_area),
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN',
                );
            }

        } else if( !empty( $max_area ) ) {
            $max_area = intval( $max_area );
            if( $max_area > 0 ) {
                $meta_query[] = array(
                    'key' => 'fave_property_size',
                    'value' => $max_area,
                    'type' => 'NUMERIC',
                    'compare' => '<=',
                );
            }
        } else if( !empty( $min_area ) ) {
            $min_area = intval( $min_area );
            if( $min_area > 0 ) {
                $meta_query[] = array(
                    'key' => 'fave_property_size',
                    'value' => $min_area,
                    'type' => 'NUMERIC',
                    'compare' => '>=',
                );
            }
        }

        $meta_count = count($meta_query);

        if( $meta_count > 0 || !empty($keyword_array)) {
            $query_args['meta_query'] = array(
                'relation' => 'AND',
                $keyword_array,
                array(
                    'relation' => 'AND',
                    $meta_query
                ),
            );
        }

        $tax_count = count($tax_query);


        $tax_query['relation'] = 'AND';


        if( $tax_count > 0 ) {
            $query_args['tax_query']  = $tax_query;
        }


        $query_args = new WP_Query( $query_args );


        $properties = array();

        while( $query_args->have_posts() ): $query_args->the_post();

            $post_id = get_the_ID();
            $property_location = get_post_meta( get_the_ID(),'fave_property_location',true);
            $lat_lng = explode(',', $property_location);
            $prop_images        = get_post_meta( get_the_ID(), 'fave_property_images', false );
            $prop_featured       = get_post_meta( get_the_ID(), 'fave_featured', true );
            $prop_type = wp_get_post_terms( get_the_ID(), 'property_type', array("fields" => "ids") );

            $prop = new stdClass();

            $prop->id = $post_id;
            $prop->title = get_the_title();
            $prop->sanitizetitle = sanitize_title(get_the_title());
            $prop->lat = $lat_lng[0];
            $prop->lng = $lat_lng[1];
            $prop->bedrooms = get_post_meta( get_the_ID(), 'fave_property_bedrooms', true );
            $prop->bathrooms = get_post_meta( get_the_ID(), 'fave_property_bathrooms', true );
            $prop->address = get_post_meta( get_the_ID(), 'fave_property_map_address', true );
            $prop->thumbnail = get_the_post_thumbnail( $post_id, 'houzez-property-thumb-image' );
            $prop->url = get_permalink();
            $prop->prop_meta = blokhausre_listing_meta_v1();
            $prop->type = blokhausre_taxonomy_simple('property_type');
            $prop->images_count = count( $prop_images );
            $prop->price = blokhausre_listing_price_v1();
            $prop->pricePin = blokhausre_listing_price_map_pins();

            foreach( $prop_type as $term_id ) {
                $icon = get_tax_meta( $term_id, 'fave_prop_type_icon');
                $retinaIcon = get_tax_meta( $term_id, 'fave_prop_type_icon_retina');

                $prop->term_id = $term_id;

                if( !empty($icon['src']) ) {
                    $prop->icon = $icon['src'];
                } else {
                    $prop->icon = get_template_directory_uri() . '/images/map/pin-single-family.png';
                }
                if( !empty($retinaIcon['src']) ) {
                    $prop->retinaIcon = $retinaIcon['src'];
                } else {
                    $prop->retinaIcon = get_template_directory_uri() . '/images/map/pin-single-family.png';
                }
            }

            array_push($properties, $prop);

        endwhile;

        wp_reset_postdata();

        if( count($properties) > 0 ) {
            echo json_encode( array( 'getProperties' => true, 'properties' => $properties ) );
            exit();
        } else {
            echo json_encode( array( 'getProperties' => false ) );
            exit();
        }
        die();
    }
}


/*-----------------------------------------------------------------------------------*/
/*	Get Properties for Half Map listings
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_blokhausre_half_map_listings', 'blokhausre_half_map_listings' );
add_action( 'wp_ajax_blokhausre_half_map_listings', 'blokhausre_half_map_listings' );
if( !function_exists('blokhausre_half_map_listings') ) {
    function blokhausre_half_map_listings() {
        //check_ajax_referer('blokhausre_map_ajax_nonce', 'security');
        $meta_query = array();
        $tax_query = array();
        $date_query = array();
        $allowed_html = array();
        $keyword_array =  '';

        $dummy_array = array();
        $custom_fields_values = isset($_POST['custom_fields_values']) ? $_POST['custom_fields_values'] : '';

        if(!empty($custom_fields_values)) {
            foreach ($custom_fields_values as $value) {
               $dummy_array[] = $value;
            }
        }


        $sort_by = isset($_POST['sort_half_map']) ? $_POST['sort_half_map'] : '';
        $features = isset($_POST['features']) ? $_POST['features'] : '';
        $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
        $country = isset($_POST['country']) ? ($_POST['country']) : '';
        $location = isset($_POST['location']) ? ($_POST['location']) : '';
        $area = isset($_POST['area']) ? ($_POST['area']) : '';
        $state = isset($_POST['state']) ? ($_POST['state']) : '';
        $status = isset($_POST['status']) ? ($_POST['status']) : '';
        $type = isset($_POST['type']) ? ($_POST['type']) : '';
        $labels = isset($_POST['label']) ? ($_POST['label']) : '';
        $property_id = isset($_POST['property_id']) ? ($_POST['property_id']) : '';
        $bedrooms = isset($_POST['bedrooms']) ? ($_POST['bedrooms']) : '';
        $bathrooms = isset($_POST['bathrooms']) ? ($_POST['bathrooms']) : '';
        $min_price = isset($_POST['min_price']) ? ($_POST['min_price']) : '';
        $max_price = isset($_POST['max_price']) ? ($_POST['max_price']) : '';
        $currency = isset($_POST['currency']) ? ($_POST['currency']) : '';
        $min_area = isset($_POST['min_area']) ? ($_POST['min_area']) : '';
        $max_area = isset($_POST['max_area']) ? ($_POST['max_area']) : '';
        $publish_date = isset($_POST['publish_date']) ? ($_POST['publish_date']) : '';
        $post_per_page = isset($_POST['post_per_page']) ? ($_POST['post_per_page']) : 10;
        $paged = isset($_POST['paged']) ? ($_POST['paged']) : '';
        $keyword_field = blokhausre_option('keyword_field');

        $search_location = isset( $_POST[ 'search_location' ] ) ? esc_attr( $_POST[ 'search_location' ] ) : false;
        $use_radius = isset( $_POST[ 'use_radius' ] ) && 'on' == $_POST[ 'use_radius' ];
        $search_lat = isset($_POST['search_lat']) ? (float) $_POST['search_lat'] : false;
        $search_long = isset($_POST['search_long']) ? (float) $_POST['search_long'] : false;
        $search_radius = isset($_POST['search_radius']) ? (int) $_POST['search_radius'] : false;

        $prop_locations = array();
        blokhausre_get_terms_array( 'property_city', $prop_locations );

        $beds_baths_search = blokhausre_option('beds_baths_search');
        $listing_view = blokhausre_option('halfmap_listings_layout');
        if(isset($_GET['half_map_style'])) {
            $listing_view = $_GET['half_map_style'];
        }

        $property_id_prefix = blokhausre_option('property_id_prefix');
        $property_id = str_replace($property_id_prefix, "", $property_id);

        $search_criteria = '=';
        if( $beds_baths_search == 'greater') {
            $search_criteria = '>=';
        }

        $query_args = array(
            'post_type' => 'property',
            'posts_per_page' => $post_per_page,
            'paged' => $paged,
            'post_status' => 'publish'
        );


        $query_args = apply_filters( 'blokhausre_radius_filter', $query_args, $search_lat, $search_long, $search_radius, $use_radius, $search_location );

        if ( $keyword != '') {
            if( $keyword_field == 'prop_address' ) {
                $keyword = stripcslashes($keyword);
                $meta_keywork = wp_kses($keyword, $allowed_html);
                $address_array = array(
                    'key' => 'fave_property_map_address',
                    'value' => $meta_keywork,
                    'type' => 'CHAR',
                    'compare' => 'LIKE',
                );

                $street_array = array(
                    'key' => 'fave_property_address',
                    'value' => $meta_keywork,
                    'type' => 'CHAR',
                    'compare' => 'LIKE',
                );

                $zip_array = array(
                    'key' => 'fave_property_zip',
                    'value' => $meta_keywork,
                    'type' => 'CHAR',
                    'compare' => '=',
                );

                $propid_array = array(
                    'key' => 'fave_property_id',
                    'value' => str_replace($property_id_prefix, "", $meta_keywork),
                    'type' => 'CHAR',
                    'compare' => '=',
                );

                $keyword_array = array(
                    'relation' => 'OR',
                    $address_array,
                    $street_array,
                    $propid_array,
                    $zip_array
                );
            }  else if( $keyword_field == 'prop_city_state_county' ) {
                $taxlocation[] = sanitize_title (  esc_html( wp_kses($keyword, $allowed_html) ) );

                $_tax_query = Array();
                $_tax_query['relation'] = 'OR';

                $_tax_query[] = array(
                    'taxonomy'     => 'property_area',
                    'field'        => 'slug',
                    'terms'        => $taxlocation
                );

                $_tax_query[] = array(
                    'taxonomy'     => 'property_city',
                    'field'        => 'slug',
                    'terms'        => $taxlocation
                );

                $_tax_query[] = array(
                    'taxonomy'      => 'property_state',
                    'field'         => 'slug',
                    'terms'         => $taxlocation
                );

                $tax_query[] = $_tax_query;

            } else {
                $keyword = trim( $keyword );
                if ( ! empty( $keyword ) ) {
                    $query_args['s'] = $keyword;
                }
            }
        }

        //Date Query
        if( !empty($publish_date) ) {
            $publish_date = explode('/', $publish_date);
            $query_args['date_query'] = array(
                array(
                    'year' => $publish_date[2],
                    'compare'   => '>=',
                ),
                array(
                    'month' => $publish_date[1],
                    'compare'   => '>=',
                ),
                array(
                    'day' => $publish_date[0],
                    'compare'   => '>=',
                )
            );
        }

        // Meta Queries
        //Custom Fields
        if(class_exists('Houzez_Fields_Builder')) {
            $fields_array = Houzez_Fields_Builder::get_form_fields();
            if(!empty($fields_array)):
                
                foreach ( $fields_array as $key => $value ):
                    $field_title = $value->label;
                    $field_name = $value->field_id;
                    $is_search = $value->is_search;

                    if( $is_search == 'yes' ) {
                        if(!empty($dummy_array[$key])) {
                            $meta_query[] = array(
                                'key' => 'fave_'.$field_name,
                                'value' => $dummy_array[$key],
                                'type' => 'CHAR',
                                'compare' => '=',
                            );
                        }
                    }
                endforeach; endif;
        }


        if(!empty($currency)) {
            $meta_query[] = array(
                'key' => 'fave_currency',
                'value' => $currency,
                'type' => 'CHAR',
                'compare' => '=',
            );
        }


        $meta_query[] = array(
            'key' => 'fave_property_map_address',
            'compare' => 'EXISTS',
        );

        if( !empty($location) && $location != 'all' ) {
            $tax_query[] = array(
                'taxonomy' => 'property_city',
                'field' => 'slug',
                'terms' => $location
            );

        }

        // Property ID
        if( !empty( $property_id )  ) {
            $meta_query[] = array(
                'key' => 'fave_property_id',
                'value'   => $property_id,
                'type'    => 'char',
                'compare' => '=',
            );
        }

        if( !empty($area) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_area',
                'field' => 'slug',
                'terms' => $area
            );
        }

        if( !empty($state) ) {
            $tax_query[] = array(
                'taxonomy'      => 'property_state',
                'field'         => 'slug',
                'terms'         => $state
            );
        }

        if( !empty($status) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => $status
            );
        }
        if( !empty( $features ) ) {
            foreach ($features as $feature):
                $tax_query[] = array(
                    'taxonomy' => 'property_feature',
                    'field' => 'slug',
                    'terms' => $feature
                );
            endforeach;
        }
        if( !empty($type) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $type
            );
        }

        if( !empty( $country ) ) {
            $meta_query[] = array(
                'key' => 'fave_property_country',
                'value'   => $country,
                'type'    => 'CHAR',
                'compare' => '=',
            );
        }

        if ( !empty( $labels ) ) {
            $tax_query[] = array(
                'taxonomy' => 'property_label',
                'field' => 'slug',
                'terms' => $labels
            );
        }

        // bedrooms logic
        if( !empty( $bedrooms ) && $bedrooms != 'any'  ) {
            $bedrooms = sanitize_text_field($bedrooms);
            $meta_query[] = array(
                'key' => 'fave_property_bedrooms',
                'value'   => $bedrooms,
                'type'    => 'CHAR',
                'compare' => $search_criteria,
            );
        }

        // bathrooms logic
        if( !empty( $bathrooms ) && $bathrooms != 'any'  ) {
            $bathrooms = sanitize_text_field($bathrooms);
            $meta_query[] = array(
                'key' => 'fave_property_bathrooms',
                'value'   => $bathrooms,
                'type'    => 'CHAR',
                'compare' => $search_criteria,
            );
        }

        // min and max price logic
        if( !empty( $min_price ) && $min_price != 'any' && !empty( $max_price ) && $max_price != 'any' ) {
            $min_price = doubleval( blokhausre_clean( $min_price ) );
            $max_price = doubleval( blokhausre_clean( $max_price ) );

            if( $min_price > 0 && $max_price > $min_price ) {
                $meta_query[] = array(
                    'key' => 'fave_property_price',
                    'value' => array($min_price, $max_price),
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN',
                );
            }
        } else if( !empty( $min_price ) && $min_price != 'any'  ) {
            $min_price = doubleval( blokhausre_clean( $min_price ) );
            if( $min_price > 0 ) {
                $meta_query[] = array(
                    'key' => 'fave_property_price',
                    'value' => $min_price,
                    'type' => 'NUMERIC',
                    'compare' => '>=',
                );
            }
        } else if( !empty( $max_price ) && $max_price != 'any'  ) {
            $max_price = doubleval( blokhausre_clean( $max_price ) );
            if( $max_price > 0 ) {
                $meta_query[] = array(
                    'key' => 'fave_property_price',
                    'value' => $max_price,
                    'type' => 'NUMERIC',
                    'compare' => '<=',
                );
            }
        }

        // min and max area logic
        if( !empty( $min_area ) && !empty( $max_area ) ) {
            $min_area = intval( $min_area );
            $max_area = intval( $max_area );

            if( $min_area > 0 && $max_area > $min_area ) {
                $meta_query[] = array(
                    'key' => 'fave_property_size',
                    'value' => array($min_area, $max_area),
                    'type' => 'NUMERIC',
                    'compare' => 'BETWEEN',
                );
            }

        } else if( !empty( $max_area ) ) {
            $max_area = intval( $max_area );
            if( $max_area > 0 ) {
                $meta_query[] = array(
                    'key' => 'fave_property_size',
                    'value' => $max_area,
                    'type' => 'NUMERIC',
                    'compare' => '<=',
                );
            }
        } else if( !empty( $min_area ) ) {
            $min_area = intval( $min_area );
            if( $min_area > 0 ) {
                $meta_query[] = array(
                    'key' => 'fave_property_size',
                    'value' => $min_area,
                    'type' => 'NUMERIC',
                    'compare' => '>=',
                );
            }
        }

        $meta_count = count($meta_query);

        if( $meta_count > 0 || !empty($keyword_array)) {
            $query_args['meta_query'] = array(
                'relation' => 'AND',
                $keyword_array,
                array(
                    'relation' => 'AND',
                    $meta_query
                ),
            );
        }

        $tax_count = count($tax_query);


        $tax_query['relation'] = 'AND';


        if( $tax_count > 0 ) {
            $query_args['tax_query']  = $tax_query;
        }


        if ( $sort_by == 'a_price' ) {
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = 'fave_property_price';
            $query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_price' ) {
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = 'fave_property_price';
            $query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured' ) {
            $query_args['meta_key'] = 'fave_featured';
            $query_args['meta_value'] = '1';
        } else if ( $sort_by == 'a_date' ) {
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_date' ) {
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured_top' ) {
            $query_args['orderby'] = 'meta_value';
            $query_args['meta_key'] = 'fave_featured';
            $query_args['order'] = 'DESC';
        }


        $query_args = new WP_Query( $query_args );

        $properties = array();

        ob_start();

        $total_properties = $query_args->found_posts;

        while( $query_args->have_posts() ): $query_args->the_post();

            $post_id = get_the_ID();
            $property_location = get_post_meta( get_the_ID(),'fave_property_location',true);
            $lat_lng = explode(',', $property_location);
            $prop_images        = get_post_meta( get_the_ID(), 'fave_property_images', false );
            $prop_featured       = get_post_meta( get_the_ID(), 'fave_featured', true );
            $prop_type = wp_get_post_terms( get_the_ID(), 'property_type', array("fields" => "ids") );

            $prop = new stdClass();

            $prop->id = $post_id;
            $prop->title = get_the_title();
            $prop->sanitizetitle = sanitize_title(get_the_title());
            $prop->lat = $lat_lng[0];
            $prop->lng = $lat_lng[1];
            $prop->bedrooms = get_post_meta( get_the_ID(), 'fave_property_bedrooms', true );
            $prop->bathrooms = get_post_meta( get_the_ID(), 'fave_property_bathrooms', true );
            $prop->address = get_post_meta( get_the_ID(), 'fave_property_map_address', true );
            $prop->thumbnail = get_the_post_thumbnail( $post_id, 'houzez-property-thumb-image' );
            $prop->url = get_permalink();
            $prop->prop_meta = blokhausre_listing_meta_v1();
            $prop->type = blokhausre_taxonomy_simple('property_type');
            $prop->images_count = count( $prop_images );
            $prop->price = blokhausre_listing_price_v1();
            $prop->pricePin = blokhausre_listing_price_map_pins();

            $prop->icon = $prop->retinaIcon = get_template_directory_uri() . '/images/map/pin-single-family.png';
            foreach( $prop_type as $term_id ) {
                $icon = get_tax_meta( $term_id, 'fave_prop_type_icon');
                $retinaIcon = get_tax_meta( $term_id, 'fave_prop_type_icon_retina');

                $prop->term_id = $term_id;

                if( !empty($icon['src']) ) {
                    $prop->icon = $icon['src'];
                } else {
                    $prop->icon = get_template_directory_uri() . '/images/map/pin-single-family.png';
                }
                if( !empty($retinaIcon['src']) ) {
                    $prop->retinaIcon = $retinaIcon['src'];
                } else {
                    $prop->retinaIcon = get_template_directory_uri() . '/images/map/pin-single-family.png';
                }
            }

            array_push($properties, $prop);

            if( $listing_view == 'grid-view-style3') {
                get_template_part('template-parts/property-for-listing-v3');
            } else if( $listing_view == 'listing-style-2') {
                get_template_part('template-parts/property-for-listing', 'v2');
            } else {
                get_template_part('template-parts/property-for-listing');
            }


        endwhile;

        wp_reset_query();

        echo '<hr>';
        blokhausre_halpmap_ajax_pagination( $query_args->max_num_pages, $paged, $range = 2 );

        $listings_html = ob_get_contents();
        ob_end_clean();

        $encoded_query = base64_encode( serialize( $query_args->query ) );

        if( count($properties) > 0 ) {
            echo json_encode( array( 'getProperties' => true, 'properties' => $properties, 'total_results' => $total_properties, 'propHtml' => $listings_html, 'query' => $encoded_query ) );
            exit();
        } else {
            echo json_encode( array( 'getProperties' => false, 'total_results' => $total_properties, 'query' => $encoded_query ) );
            exit();
        }
        die();
    }
}

/*-----------------------------------------------------------------------------------*/
// Add to favorite
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_blokhausre_add_to_favorite', 'blokhausre_favorites' );
if( !function_exists( 'blokhausre_favorites' ) ) {
    // a:1:{i:0;i:543;}
    function blokhausre_favorites () {
        global $current_user;
        wp_get_current_user();
        $userID      =   $current_user->ID;
        $fav_option = 'blokhausre_favorites-'.$userID;
        $property_id = intval( $_POST['property_id'] );
        $current_prop_fav = get_option( 'blokhausre_favorites-'.$userID );

        // Check if empty or not
        if( empty( $current_prop_fav ) ) {
            $prop_fav = array();
            $prop_fav['1'] = $property_id;
            update_option( $fav_option, $prop_fav );
            $arr = array( 'added' => true, 'response' => esc_html__('Added', 'houzez') );
            echo json_encode($arr);
            wp_die();
        } else {
            if(  ! in_array ( $property_id, $current_prop_fav )  ) {
                $current_prop_fav[] = $property_id;
                update_option( $fav_option,  $current_prop_fav );
                $arr = array( 'added' => true, 'response' => esc_html__('Added', 'houzez') );
                echo json_encode($arr);
                wp_die();
            } else {
                $key = array_search( $property_id, $current_prop_fav );

                if( $key != false ) {
                    unset( $current_prop_fav[$key] );
                }

                update_option( $fav_option, $current_prop_fav );
                $arr = array( 'added' => false, 'response' => esc_html__('Removed', 'houzez') );
                echo json_encode($arr);
                wp_die();
            }
        }
        wp_die();
    }
}

/*-----------------------------------------------------------------------------------*/
/*	Properties sorting
/*-----------------------------------------------------------------------------------*/
if( !function_exists( 'blokhausre_prop_sort' ) ){

    function blokhausre_prop_sort( $query_args ) {
        $sort_by = '';
        $show_featured_on_top = blokhausre_option('show_featured_on_top');

        if ( isset( $_GET['sortby'] ) ) {
            $sort_by = $_GET['sortby'];
        } else {

            if ( is_page_template( array( 'template/property-listing-template.php', 'template/property-listing-template-style2.php', 'template/property-listing-fullwidth.php', 'template/property-listing-style2-fullwidth.php', 'template/property-listing-template-style3.php', 'template/property-listing-template-style3-fullwidth.php' ))) {
                $sort_by = get_post_meta( get_the_ID(), 'fave_properties_sort', true );

            } else if( is_page_template( array( 'template/template-search.php' )) ) {
                if($show_featured_on_top != 1 ) {
                    $sort_by = blokhausre_option('search_default_order');
                }
            } else if ( is_tax() ) {
                $sort_by = blokhausre_option('taxonomy_default_order');
            } else if(is_singular('blokhausre_agent')) {
                $sort_by = blokhausre_option('agent_listings_order');

            } else if(is_singular('blokhausre_agency')) {
                $sort_by = blokhausre_option('agency_listings_order');

            } else if(is_page_template( array( 'template/properties-parallax.php' ))) {
                $sort_by = get_post_meta( get_the_ID(), 'fave_properties_sort_halfmap', true );

            }
        }

        if ( $sort_by == 'a_price' ) {
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = 'fave_property_price';
            $query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_price' ) {
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = 'fave_property_price';
            $query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured' ) {
            $query_args['meta_key'] = 'fave_featured';
            $query_args['meta_value'] = '1';
            $query_args['orderby'] = 'meta_value date';
        } else if ( $sort_by == 'a_date' ) {
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_date' ) {
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured_first' ) {
            $query_args['orderby'] = 'meta_value date';
            $query_args['meta_key'] = 'fave_featured';
            //$query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured_top' ) {
            $query_args['orderby'] = 'meta_value date';
            $query_args['meta_key'] = 'fave_featured';
            //$query_args['order'] = 'DESC';
        }
        return $query_args;

        //$q = new WP_Query( array( 'orderby' => array( 'title' => 'DESC', 'menu_order' => 'ASC' ) ) );

    }
}


/*-----------------------------------------------------------------------------------*/
// Remove property attachments
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_blokhausre_remove_property_thumbnail', 'blokhausre_remove_property_thumbnail' );
add_action( 'wp_ajax_nopriv_blokhausre_remove_property_thumbnail', 'blokhausre_remove_property_thumbnail' );
if( !function_exists('blokhausre_remove_property_thumbnail') ) {
    function blokhausre_remove_property_thumbnail() {

        $nonce = $_POST['removeNonce'];
        $remove_attachment = false;
        if (!wp_verify_nonce($nonce, 'verify_gallery_nonce')) {

            echo json_encode(array(
                'remove_attachment' => false,
                'reason' => esc_html__('Invalid Nonce', 'houzez')
            ));
            wp_die();
        }

        if (isset($_POST['thumb_id']) && isset($_POST['prop_id'])) {
            $thumb_id = intval($_POST['thumb_id']);
            $prop_id = intval($_POST['prop_id']);

            if ( $thumb_id > 0 && $prop_id > 0 ) {
                delete_post_meta($prop_id, 'fave_property_images', $thumb_id);
                $remove_attachment = wp_delete_attachment($thumb_id);
            } elseif ($thumb_id > 0) {
                if( false == wp_delete_attachment( $thumb_id )) {
                    $remove_attachment = false;
                } else {
                    $remove_attachment = true;
                }
            }
        }

        echo json_encode(array(
            'remove_attachment' => $remove_attachment,
        ));
        wp_die();

    }
}

/*-----------------------------------------------------------------------------------*/
/*	 Upload property gallery images
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_blokhausre_property_img_upload', 'blokhausre_property_img_upload' );    // only for logged in user
add_action( 'wp_ajax_nopriv_blokhausre_property_img_upload', 'blokhausre_property_img_upload' );
if( !function_exists( 'blokhausre_property_img_upload' ) ) {
    function blokhausre_property_img_upload( ) {

        // Check security Nonce
        $verify_nonce = $_REQUEST['verify_nonce'];
        if ( ! wp_verify_nonce( $verify_nonce, 'verify_gallery_nonce' ) ) {
            echo json_encode( array( 'success' => false , 'reason' => 'Invalid nonce!' ) );
            die;
        }

        $submitted_file = $_FILES['property_upload_file'];
        $uploaded_image = wp_handle_upload( $submitted_file, array( 'test_form' => false ) );

        if ( isset( $uploaded_image['file'] ) ) {
            $file_name          =   basename( $submitted_file['name'] );
            $file_type          =   wp_check_filetype( $uploaded_image['file'] );

            // Prepare an array of post data for the attachment.
            $attachment_details = array(
                'guid'           => $uploaded_image['url'],
                'post_mime_type' => $file_type['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $attach_id      =   wp_insert_attachment( $attachment_details, $uploaded_image['file'] );
            $attach_data    =   wp_generate_attachment_metadata( $attach_id, $uploaded_image['file'] );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            $thumbnail_url = wp_get_attachment_image_src( $attach_id, 'houzez-image350_350' );
            //$fullimage_url  = wp_get_attachment_image_src( $attach_id, 'full' );

            $feat_image_url = wp_get_attachment_url( $attach_id );

            $ajax_response = array(
                'success'   => true,
                'url' => $thumbnail_url[0],
                'attachment_id'    => $attach_id,
                //'full_image'    => $fullimage_url[0]
                'full_image'    => $feat_image_url
            );

            echo json_encode( $ajax_response );
            die;

        } else {
            $ajax_response = array( 'success' => false, 'reason' => 'Image upload failed!' );
            echo json_encode( $ajax_response );
            die;
        }

    }
}


/*-----------------------------------------------------------------------------------*/
/*	 Upload property gallery images
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_blokhausre_property_attachment_upload', 'blokhausre_property_attachment_upload' );    // only for logged in user
add_action( 'wp_ajax_nopriv_blokhausre_property_attachment_upload', 'blokhausre_property_attachment_upload' );
if( !function_exists( 'blokhausre_property_attachment_upload' ) ) {
    function blokhausre_property_attachment_upload( ) {

        // Check security Nonce
        $verify_nonce = $_REQUEST['verify_nonce'];
        if ( ! wp_verify_nonce( $verify_nonce, 'verify_gallery_nonce' ) ) {
            echo json_encode( array( 'success' => false , 'reason' => 'Invalid nonce!' ) );
            die;
        }

        $submitted_file = $_FILES['property_attachment_file'];
        $uploaded_image = wp_handle_upload( $submitted_file, array( 'test_form' => false ) );

        if ( isset( $uploaded_image['file'] ) ) {
            $file_name          =   basename( $submitted_file['name'] );
            $file_type          =   wp_check_filetype( $uploaded_image['file'] );

            // Prepare an array of post data for the attachment.
            $attachment_details = array(
                'guid'           => $uploaded_image['url'],
                'post_mime_type' => $file_type['type'],
                'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $attach_id      =   wp_insert_attachment( $attachment_details, $uploaded_image['file'] );
            $attach_data    =   wp_generate_attachment_metadata( $attach_id, $uploaded_image['file'] );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            $thumbnail_url = wp_get_attachment_image_src( $attach_id, 'houzez-image350_350' );
            //$fullimage_url  = wp_get_attachment_image_src( $attach_id, 'full' );
            $attachment_title = get_the_title($attach_id);
            $fullimage_url = wp_get_attachment_url( $attach_id );

            $ajax_response = array(
                'success'   => true,
                'url' => $thumbnail_url[0],
                'attachment_id'    => $attach_id,
                'full_image'    => $fullimage_url,
                'attach_title'    => $attachment_title,
            );

            echo json_encode( $ajax_response );
            die;

        } else {
            $ajax_response = array( 'success' => false, 'reason' => 'File upload failed!' );
            echo json_encode( $ajax_response );
            die;
        }

    }
}

/*-----------------------------------------------------------------------------------*/
/*	Houzez get ajax single property
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_blokhausre_get_single_property', 'blokhausre_get_single_property' );
add_action( 'wp_ajax_blokhausre_get_single_property', 'blokhausre_get_single_property' );

if( !function_exists('blokhausre_get_single_property') ) {
    function blokhausre_get_single_property() {
        check_ajax_referer('blokhausre_map_ajax_nonce', 'security');

        $prop_id = isset($_POST['prop_id']) ? sanitize_text_field($_POST['prop_id']) : '';

        $args = array(
            'p' => $prop_id,
            'posts_per_page' => 1,
            'post_type' => 'property',
            'post_status' => 'publish'
        );


        $query = new WP_Query($args);
        $props = array();

        while( $query->have_posts() ) {
            $query->the_post();

            $post_id = get_the_ID();
            $property_location = get_post_meta( get_the_ID(),'fave_property_location',true);
            $lat_lng = explode(',', $property_location);
            $prop_images        = get_post_meta( get_the_ID(), 'fave_property_images', false );
            $prop_featured       = get_post_meta( get_the_ID(), 'fave_featured', true );
            $prop_type = wp_get_post_terms( get_the_ID(), 'property_type', array("fields" => "ids") );

            $prop = new stdClass();

            $prop->id = $post_id;
            $prop->title = get_the_title();
            $prop->lat = $lat_lng[0];
            $prop->lng = $lat_lng[1];
            $prop->bedrooms = get_post_meta( get_the_ID(), 'fave_property_bedrooms', true );
            $prop->bathrooms = get_post_meta( get_the_ID(), 'fave_property_bathrooms', true );
            $prop->address = get_post_meta( get_the_ID(), 'fave_property_map_address', true );
            $prop->thumbnail = get_the_post_thumbnail( $post_id, 'houzez-property-thumb-image' );
            $prop->url = get_permalink();
            $prop->prop_meta = blokhausre_listing_meta_v1();
            $prop->type = blokhausre_taxonomy_simple('property_type');
            $prop->images_count = count( $prop_images );
            $prop->price = blokhausre_listing_price_v1();
            $prop->pricePin = blokhausre_listing_price_map_pins();

            foreach( $prop_type as $term_id ) {
                $icon = get_tax_meta( $term_id, 'fave_prop_type_icon');
                $retinaIcon = get_tax_meta( $term_id, 'fave_prop_type_icon_retina');

                $prop->term_id = $term_id;

                if( !empty($icon['src']) ) {
                    $prop->icon = $icon['src'];
                } else {
                    $prop->icon = get_template_directory_uri() . '/images/map/pin-single-family.png';
                }
                if( !empty($retinaIcon['src']) ) {
                    $prop->retinaIcon = $retinaIcon['src'];
                } else {
                    $prop->retinaIcon = get_template_directory_uri() . '/images/map/pin-single-family.png';
                }
            }

            array_push($props, $prop);
        }

        wp_reset_postdata();

        if(count($props) > 0) {
            echo json_encode(array('getprops'=>true, 'props'=>$props));
            exit();
        } else {
            echo json_encode(array('getprops'=>false));
            exit();
        }

        die();

    }
}


/*-----------------------------------------------------------------------------------*/
/*	Houzez Print Property
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_blokhausre_create_print', 'blokhausre_create_print' );
add_action( 'wp_ajax_blokhausre_create_print', 'blokhausre_create_print' );

if( !function_exists('blokhausre_create_print')) {
    function blokhausre_create_print () {

        if(!isset($_POST['propid'])|| !is_numeric($_POST['propid'])){
            exit();
        }

        $property_id = intval($_POST['propid']);
        $the_post= get_post( $property_id );

        if( $the_post->post_type != 'property' || $the_post->post_status != 'publish' ) {
            exit();
        }

        print  '<html><head><link href="'.get_stylesheet_uri().'" rel="stylesheet" type="text/css" />';
        print  '<html><head><link href="'.get_template_directory_uri().'/css/bootstrap.min.css" rel="stylesheet" type="text/css" />';
        print  '<html><head><link href="'.get_template_directory_uri().'/css/main.css" rel="stylesheet" type="text/css" />';

        if( is_rtl() ) {
            print '<link href="'.get_template_directory_uri().'/css/rtl.css" rel="stylesheet" type="text/css" />';
            print '<link href="'.get_template_directory_uri().'/css/bootstrap-rtl.min.css" rel="stylesheet" type="text/css" />';
        }
        print '</head>';
        print  '<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script><script>$(window).load(function(){ print(); });</script>';
        print  '<body>';

        $print_logo = blokhausre_option( 'print_page_logo', false, 'url' );

        $image_id           = get_post_thumbnail_id( $property_id );
        $full_img           = wp_get_attachment_image_src($image_id, 'houzez-single-big-size');
        $full_img           = $full_img [0];

        $title              = get_the_title( $property_id );
        $prop_excerpt       = $the_post->post_content;

        $property_status = blokhausre_taxonomy_simple_2('property_status', $property_id);
        $property_type = blokhausre_taxonomy_simple_2('property_type', $property_id);

        $prop_images          = get_post_meta( $property_id, 'fave_property_images', false );
        $prop_address         = get_post_meta( $property_id, 'fave_property_map_address', true );
        $prop_id = get_post_meta( $property_id, 'fave_property_id', true );
        $prop_price = get_post_meta( $property_id, 'fave_property_price', true );
        $prop_size = get_post_meta( $property_id, 'fave_property_size', true );
        $land_area = get_post_meta( $property_id, 'fave_property_land', true );
        $bedrooms = get_post_meta( $property_id, 'fave_property_bedrooms', true );
        $bathrooms = get_post_meta( $property_id, 'fave_property_bathrooms', true );
        $year_built = get_post_meta( $property_id, 'fave_property_year', true );
        $garage = get_post_meta( $property_id, 'fave_property_garage', true );
        $garage_size = get_post_meta( $property_id, 'fave_property_garage_size', true );
        $prop_floor_plan      = get_post_meta( $property_id, 'fave_floor_plans_enable', true );
        $floor_plans          = get_post_meta( $property_id, 'floor_plans', true );
        $additional_features_enable = get_post_meta( $property_id, 'fave_additional_features_enable', true );
        $additional_features = get_post_meta( $property_id, 'additional_features', true );

        $agent_display_option = get_post_meta( $property_id, 'fave_agent_display_option', true );
        $prop_agent_display = get_post_meta( $property_id, 'fave_agents', true );
        $prop_agent_num = $agent_num_call = $prop_agent_email = '';

        if( $prop_agent_display != '-1' && $agent_display_option == 'agent_info' ) {
            $prop_agent_id = get_post_meta( $property_id, 'fave_agents', true );
            $prop_agent_mobile = get_post_meta( $prop_agent_id, 'fave_agent_mobile', true );
            $prop_agent_phone = get_post_meta( $prop_agent_id, 'fave_agent_office_num', true );
            $prop_agent_skype = get_post_meta( $prop_agent_id, 'fave_agent_skype', true );
            $prop_agent_website = get_post_meta( $prop_agent_id, 'fave_agent_website', true );
            $prop_agent_email = get_post_meta( $prop_agent_id, 'fave_agent_email', true );
            $prop_agent = get_the_title( $prop_agent_id );
            $thumb_id = get_post_thumbnail_id( $prop_agent_id );
            $thumb_url_array = wp_get_attachment_image_src( $thumb_id, 'thumbnail', true );
            $prop_agent_photo_url = $thumb_url_array[0];

        } elseif ( $agent_display_option == 'author_info' ) {
            $author_id = get_post_field ('post_author', $property_id);
            $prop_agent = get_the_author_meta('display_name', $author_id );
            $prop_agent_mobile = get_the_author_meta( 'fave_author_mobile', $author_id );
            $prop_agent_phone = get_the_author_meta( 'fave_author_phone', $author_id );
            $prop_agent_skype = get_the_author_meta( 'fave_author_skype', $author_id );
            $prop_agent_website = get_the_author_meta( 'url', $author_id );
            $prop_agent_photo_url = get_the_author_meta( 'fave_author_custom_picture', $author_id );
            $prop_agent_email = get_the_author_meta( 'email', $author_id );
        }
        if( empty( $prop_agent_photo_url )) {
            $prop_agent_photo_url = get_template_directory_uri().'/images/profile-avatar.png';
        }

        $print_agent = blokhausre_option('print_agent');
        $print_description = blokhausre_option('print_description');
        $print_details = blokhausre_option('print_details');
        $print_details_additional = blokhausre_option('print_details_additional');
        $print_features = blokhausre_option('print_features');
        $print_floorplans = blokhausre_option('print_floorplans');
        $print_gallery = blokhausre_option('print_gallery');
        $print_gr_code = blokhausre_option('print_gr_code');
        ?>

        <section id="section-body">
            <!--start detail content-->
            <section class="section-detail-content">
                <div class="detail-bar print-detail">
                    <div class="detail-block">
                        <div class="print-header">
                            <div class="print-header-left">
                                <a href="#" class="print-logo">

                                    <img src="<?php echo esc_url($print_logo); ?>" alt="logo">
                                    <span class="tag-line"><?php bloginfo( 'description' ); ?></span>

                                </a>
                            </div>
                        </div>
                        <div class="print-header-detail">
                            <div class="print-header-detail-left">
                                <h1><?php echo esc_attr( $title ); ?></h1>
                                <p><?php echo esc_attr( $prop_address ); ?></p>
                            </div>
                            <div class="print-header-detail-right">
                                <?php echo blokhausre_listing_price_for_print( $property_id ); ?>
                            </div>
                        </div>
                        <div class="print-banner">
                            <div class="print-main-image">
                                <?php if( !empty($full_img) ) { ?>
                                    <img src="<?php echo esc_url( $full_img ); ?>" alt="<?php echo esc_attr($title); ?>">
                                    
                                    <?php if($print_gr_code != 0) { ?>
                                    <img class="qr-image" src="https://chart.googleapis.com/chart?chs=105x104&cht=qr&chl=<?php echo esc_url( get_permalink($property_id) ); ?>&choe=UTF-8" title="<?php echo esc_attr($title); ?>" />
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>

                        <?php if( $print_agent != 0 ) { ?>
                        <div class="print-block">
                            <div class="media agent-media">
                                <div class="media-left">
                                    <a href="#">
                                        <img src="<?php echo esc_url( $prop_agent_photo_url ); ?>" class="media-object" alt="image" height="74" width="74">
                                    </a>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading"><?php esc_html_e( 'Contact Agent', 'houzez' ); ?></h4>
                                    <ul>
                                        <li><strong><?php echo esc_attr($prop_agent); ?></strong></li>
                                        <li><strong><?php esc_html_e( 'Mobile:', 'houzez' ); ?></strong> <?php echo esc_attr($prop_agent_mobile); ?></li>
                                        <li><strong><?php esc_html_e( 'Email:', 'houzez' ); ?></strong> <?php echo esc_attr($prop_agent_email); ?></li>
                                        <li><strong><?php esc_html_e( 'Phone:', 'houzez' ); ?></strong> <?php echo esc_attr($prop_agent_phone); ?></li>
                                        <?php if( !empty($prop_agent_skype) ) { ?>
                                            <li><strong><?php esc_html_e( 'Skype:', 'houzez' ); ?></strong> <?php echo esc_attr($prop_agent_skype); ?></li>
                                        <?php } ?>
                                        <?php if( !empty($prop_agent_website) ) { ?>
                                            <li><strong><?php esc_html_e( 'Website:', 'houzez' ); ?></strong> <?php echo esc_url($prop_agent_website); ?></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if( $print_description != 0 ) { ?>
                        <div class="print-block">
                            <div class="detail-title-inner">
                                <h4 class="title-inner"><?php esc_html_e('Property Description', 'houzez'); ?></h4>
                            </div>
                            <p><?php
                                $content = preg_replace('#\[[^\]]+\]#', '',$the_post->post_content);
                                echo apply_filters('the_content', $content);
                            ?></p>
                        </div>
                        <?php } ?>

                        <?php if( $print_details != 0 ) { ?>
                        <div class="print-block">
                            <div class="detail-title-inner">
                                <h4 class="title-inner"><?php esc_html_e('Property Details', 'houzez'); ?></h4>
                            </div>
                            <div class="alert">
                                <ul class="print-list-three-col">
                                    <?php
                                    if( !empty( $prop_id ) ) {
                                        echo '<li><strong>'.esc_html__( 'Property ID:', 'houzez').'</strong> '.blokhausre_propperty_id_prefix($prop_id).'</li>';
                                    }
                                    if( !empty( $prop_price ) ) {
                                        echo '<li><strong>'.esc_html__( 'Price:', 'houzez'). '</strong> '.blokhausre_listing_price_by_id($property_id).'</li>';
                                    }
                                    if( !empty( $prop_size ) ) {
                                        echo '<li><strong>'.esc_html__( 'Property Size:', 'houzez'). '</strong> '.blokhausre_property_size_by_id( $property_id, 'after' ).'</li>';
                                    }
                                    if( !empty( $land_area ) ) {
                                        echo '<li><strong>'.esc_html__( 'Land Area:', 'houzez'). '</strong> '.blokhausre_property_land_area_by_id( $property_id, 'after' ).'</li>';
                                    }
                                    if( !empty( $bedrooms ) ) {
                                        echo '<li><strong>'.esc_html__( 'Bedrooms:', 'houzez').'</strong> '.esc_attr( $bedrooms ).'</li>';
                                    }
                                    if( !empty( $bathrooms ) ) {
                                        echo '<li><strong>'.esc_html__( 'Bathrooms:', 'houzez').'</strong> '.esc_attr( $bathrooms ).'</li>';
                                    }
                                    if( !empty( $garage ) ) {
                                        echo '<li><strong>'.esc_html__( 'Garage:', 'houzez').'</strong> '.esc_attr( $garage ).'</li>';
                                    }
                                    if( !empty( $garage_size ) ) {
                                        echo '<li><strong>'.esc_html__( 'Garage Size:', 'houzez').'</strong> '.esc_attr( $garage_size ).'</li>';
                                    }
                                    if( !empty( $year_built ) ) {
                                        echo '<li><strong>'.esc_html__( 'Year Built:', 'houzez').'</strong> '.esc_attr( $year_built ).'</li>';
                                    }
                                    if( !empty( $property_type ) ) {
                                        echo '<li class="prop_type"><strong>'.esc_html__( 'Property Type:', 'houzez').'</strong> '.esc_attr( $property_type ).'</li>';
                                    }
                                    if( !empty( $property_status )) {
                                        echo '<li class="prop_status"><strong>'.esc_html__( 'Property Status:', 'houzez').'</strong> '.esc_attr( $property_status ).'</li>';
                                    }

                                    //Custom Fields
                                    if(class_exists('Houzez_Fields_Builder')) {
                                    $fields_array = Houzez_Fields_Builder::get_form_fields(); 

                                        if(!empty($fields_array)) {
                                            foreach ( $fields_array as $value ) {
                                                $data_value = get_post_meta( $property_id, 'fave_'.$value->field_id, true );
                                                $field_title = $value->label;
                                                
                                                $field_title = blokhausre_wpml_translate_single_string($field_title);

                                                if(!empty($data_value) && $hide_detail_prop_fields[$value->field_id] != 1) {
                                                    echo '<li class="'.$value->field_id.'"><strong>'.$field_title.':</strong> '.esc_attr( $data_value ).'</li>';
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                            <?php } ?>

                            <?php if( $additional_features_enable != 'disable' && !empty( $additional_features_enable ) && $print_details_additional != 0 ) { ?>
                            <div class="detail-title-inner">
                                <h4 class="title-inner"><?php esc_html_e('Additional details', 'houzez'); ?></h4>
                            </div>
                            <ul class="print-list-three-col">
                                <?php
                                foreach( $additional_features as $ad_del ):
                                    echo '<li><strong>'.esc_attr( $ad_del['fave_additional_feature_title'] ).':</strong> '.esc_attr( $ad_del['fave_additional_feature_value'] ).'</li>';
                                endforeach;
                                ?>
                            </ul>
                            <?php } ?>

                        </div>

                        <?php if( $print_features != 0 ) { ?>
                        <div class="print-block">
                            <div class="detail-title-inner">
                                <h4 class="title-inner"><?php esc_html_e('Property Features', 'houzez'); ?></h4>
                            </div>
                            <ul class="print-list-three-col list-features">
                                <?php
                                $prop_features = wp_get_post_terms( $property_id, 'property_feature', array("fields" => "all"));
                                if (!empty($prop_features)):
                                    foreach ($prop_features as $term):
                                        $term_link = get_term_link($term, 'property_feature');
                                        if (is_wp_error($term_link))
                                            continue;
                                        echo '<li>' . esc_attr( $term->name ). '</li>';
                                    endforeach;
                                endif;
                                ?>
                            </ul>
                        </div>
                        <?php } ?>

                        <?php if( $prop_floor_plan != 'disable' && !empty( $prop_floor_plan ) && $print_floorplans != 0 ) { ?>
                        <div class="print-floor">
                            <div class="detail-title-inner">
                                <h4 class="title-inner"><?php esc_html_e('Floor Plans', 'houzez'); ?></h4>
                            </div>
                            <div class="accord-block">

                                <?php foreach( $floor_plans as $plan ): ?>
                                    <div class="accord-outer">
                                        <div class="accord-tab">
                                            <h3><?php echo esc_attr( $plan['fave_plan_title'] ); ?></h3>
                                            <ul>
                                                <?php if( !empty( $plan['fave_plan_size'] ) ) { ?>
                                                    <li><?php esc_html_e( 'Size:', 'houzez' ); ?> <strong><?php echo esc_attr( $plan['fave_plan_size'] ); ?></strong></li>
                                                <?php } ?>

                                                <?php if( !empty( $plan['fave_plan_rooms'] ) ) { ?>
                                                    <li><?php esc_html_e( 'Rooms:', 'houzez' ); ?> <strong><?php echo esc_attr( $plan['fave_plan_rooms'] ); ?></strong></li>
                                                <?php } ?>

                                                <?php if( !empty( $plan['fave_plan_bathrooms'] ) ) { ?>
                                                    <li><?php esc_html_e( 'Baths:', 'houzez' ); ?> <strong><?php echo esc_attr( $plan['fave_plan_bathrooms'] ); ?></strong></li>
                                                <?php } ?>

                                                <?php if( !empty( $plan['fave_plan_price'] ) ) { ?>
                                                    <li><?php esc_html_e( 'Price:', 'houzez' ); ?> <strong><?php echo esc_attr( $plan['fave_plan_price'] ); ?></strong></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                        <div class="accord-content" style="display: none">
                                            <?php if( !empty( $plan['fave_plan_image'] ) ) { ?>
                                                <img src="<?php echo esc_url( $plan['fave_plan_image'] ); ?>" alt="img" width="400" height="436">
                                            <?php } ?>

                                            <?php if( !empty( $plan['fave_plan_description'] ) ) { ?>
                                                <p><?php echo esc_attr( $plan['fave_plan_description'] ); ?></p>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if( !empty( $prop_images ) && $print_gallery != 0 ) { ?>
                        <div class="print-gallery">
                            <div class="detail-title-inner">
                                <h4 class="title-inner"><?php esc_html_e('Property images', 'houzez'); ?></h4>
                            </div>
                            <?php foreach( $prop_images as $img_id ): ?>
                                <div class="print-gallery-image"> <?php echo wp_get_attachment_image( $img_id, 'houzez-imageSize1170_738' ); ?> </div>
                            <?php endforeach; ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </section>
            <!--end detail content-->

        </section>

<?php
        print '</body></html>';
        wp_die();
    }
}

/*-----------------------------------------------------------------------------------*/
// Get Current Area | @return mixed|string|void
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'blokhausre_get_current_area' ) ) {

    function blokhausre_get_current_area() {

        if ( isset( $_COOKIE[ "blokhausre_current_area" ] ) ) {
            $current_area = $_COOKIE[ "blokhausre_current_area" ];
        }

        if ( empty( $current_area ) ) {
            $current_area = blokhausre_option('blokhausre_base_area');
        }

        return $current_area;
    }
}

/*-----------------------------------------------------------------------------------*/
// Ajax Area Switch
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'blokhausre_switch_area' ) ) {

    function blokhausre_switch_area() {

        if ( isset( $_POST[ 'switch_to_area' ] ) ):

            $expiry_period = '';

            $nonce = $_POST[ 'security' ];
            if ( ! wp_verify_nonce( $nonce, 'blokhausre_switch_area_nonce' ) ) {
                echo json_encode( array(
                    'success' => false,
                    'message' => __( 'Unverified Nonce!', 'houzez' )
                ) );
                wp_die();
            }

            $switch_to_area = $_POST[ 'switch_to_area' ];

            // expiry time
            $expiry_period = intval( $expiry_period );
            if ( ! $expiry_period ) {
                $expiry_period = 60 * 60;   // one hour
            }
            $expiry = time() + $expiry_period;

            // save cookie
            if ( setcookie( 'blokhausre_current_area', $switch_to_area, $expiry, '/' ) ) {
                echo json_encode( array(
                    'success' => true
                ) );
            } else {
                echo json_encode( array(
                    'success' => false,
                    'message' => __( "Failed to updated cookie !", 'houzez' )
                ) );
            }

        else:
            echo json_encode( array(
                    'success' => false,
                    'message' => __( "Invalid Request !", 'houzez' )
                )
            );
        endif;

        die;

    }

    add_action( 'wp_ajax_nopriv_blokhausre_switch_area', 'blokhausre_switch_area' );
    add_action( 'wp_ajax_blokhausre_switch_area', 'blokhausre_switch_area' );
}


if( !function_exists('blokhausre_get_area_size') ) {
    function blokhausre_get_area_size( $areaSize ) {
        $prop_size = $areaSize;
        $blokhausre_base_area = blokhausre_option('blokhausre_base_area');

        if( !empty( $prop_size ) ) {

            if( isset( $_COOKIE[ "blokhausre_current_area" ] ) ) {
                if( $_COOKIE[ "blokhausre_current_area" ] == 'sq_meter' && $blokhausre_base_area != 'sq_meter'  ) {
                    $prop_size = $prop_size * 0.09290304; //m2 = ft2 x 0.09290304

                } elseif( $_COOKIE[ "blokhausre_current_area" ] == 'sqft' && $blokhausre_base_area != 'sqft' ) {
                    $prop_size = $prop_size / 0.09290304; //ft2 = m2 ÷ 0.09290304
                }
            }

            $prop_area_size = esc_attr( round( $prop_size ) );

        }
        return $prop_area_size;

    }
}

if( !function_exists('blokhausre_get_size_unit') ) {
    function blokhausre_get_size_unit( $areaUnit ) {
        $measurement_unit_global = blokhausre_option('measurement_unit_global');
        $area_switcher_enable = blokhausre_option('area_switcher_enable');

        if( $area_switcher_enable != 0 ) {
            $prop_size_prefix = blokhausre_option('blokhausre_base_area');

            if( isset( $_COOKIE[ "blokhausre_current_area" ] ) ) {
                $prop_size_prefix =$_COOKIE[ "blokhausre_current_area" ];
            }

            if( $prop_size_prefix == 'sqft' ) {
                $prop_size_prefix = blokhausre_option('measurement_unit_sqft_text');
            } elseif( $prop_size_prefix == 'sq_meter' ) {
                $prop_size_prefix = blokhausre_option('measurement_unit_square_meter_text');
            }

        } else {
            if ($measurement_unit_global == 1) {
                $prop_size_prefix = blokhausre_option('measurement_unit');

                if( $prop_size_prefix == 'sqft' ) {
                    $prop_size_prefix = blokhausre_option('measurement_unit_sqft_text');
                } elseif( $prop_size_prefix == 'sq_meter' ) {
                    $prop_size_prefix = blokhausre_option('measurement_unit_square_meter_text');
                }

            } else {
                $prop_size_prefix = $areaUnit;
            }
        }
        return $prop_size_prefix;
    }
}

if( !function_exists('blokhausre_autocomplete_search') ) {
    function blokhausre_autocomplete_search() {

        return;
    }
}

if( !function_exists('blokhausre_generate_invoice') ):
    function blokhausre_generate_invoice( $billingFor, $billionType, $packageID, $invoiceDate, $userID, $featured, $upgrade, $paypalTaxID, $paymentMethod ) {

        $price_per_submission = blokhausre_option('price_listing_submission');
        $price_featured_submission = blokhausre_option('price_featured_listing_submission');

        $price_per_submission      = floatval( $price_per_submission );
        $price_featured_submission = floatval( $price_featured_submission );

        $args = array(
            'post_title'	=> 'Invoice ',
            'post_status'	=> 'publish',
            'post_type'     => 'blokhausre_invoice'
        );
        $inserted_post_id =  wp_insert_post( $args );

        if( $billionType != 'one_time' ) {
            $billionType = __( 'Recurring', 'houzez' );
        } else {
            $billionType = __( 'One Time', 'houzez' );
        }

        if( $billingFor != 'package' ) {
            if( $upgrade == 1 ) {
                $total_price = $price_featured_submission;

            } else {
                if( $featured == 1 ) {
                    $total_price = $price_per_submission+$price_featured_submission;
                } else {
                    $total_price = $price_per_submission;
                }
            }
        } else {
            $total_price = get_post_meta( $packageID, 'fave_package_price', true);
        }

        $fave_meta = array();

        $fave_meta['invoice_billion_for'] = $billingFor;
        $fave_meta['invoice_billing_type'] = $billionType;
        $fave_meta['invoice_item_id'] = $packageID;
        $fave_meta['invoice_item_price'] = $total_price;
        $fave_meta['invoice_purchase_date'] = $invoiceDate;
        $fave_meta['invoice_buyer_id'] = $userID;
        $fave_meta['paypal_txn_id'] = $paypalTaxID;
        $fave_meta['invoice_payment_method'] = $paymentMethod;

        update_post_meta( $inserted_post_id, 'HOUZEZ_invoice_buyer', $userID );
        update_post_meta( $inserted_post_id, 'HOUZEZ_invoice_type', $billionType );
        update_post_meta( $inserted_post_id, 'HOUZEZ_invoice_for', $billingFor );
        update_post_meta( $inserted_post_id, 'HOUZEZ_invoice_item_id', $packageID );
        update_post_meta( $inserted_post_id, 'HOUZEZ_invoice_price', $total_price );
        update_post_meta( $inserted_post_id, 'HOUZEZ_invoice_date', $invoiceDate );
        update_post_meta( $inserted_post_id, 'HOUZEZ_paypal_txn_id', $paypalTaxID );
        update_post_meta( $inserted_post_id, 'HOUZEZ_invoice_payment_method', $paymentMethod );

        update_post_meta( $inserted_post_id, '_blokhausre_invoice_meta', $fave_meta );

        // Update post title
        $update_post = array(
            'ID'         => $inserted_post_id,
            'post_title' => 'Invoice '.$inserted_post_id,
        );
        wp_update_post( $update_post );
        return $inserted_post_id;
    }
endif;

/*-----------------------------------------------------------------------------------*/
/*	Houzez Invoice Filter
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_blokhausre_invoices_ajax_search', 'blokhausre_invoices_ajax_search' );
add_action( 'wp_ajax_blokhausre_invoices_ajax_search', 'blokhausre_invoices_ajax_search' );

if( !function_exists('blokhausre_invoices_ajax_search') ){
    function blokhausre_invoices_ajax_search() {
        global $current_user, $blokhausre_local;
        wp_get_current_user();
        $userID = $current_user->ID;

        $blokhausre_local = blokhausre_get_localization();

        $meta_query = array();
        $date_query = array();

        if( isset($_POST['invoice_status']) &&  $_POST['invoice_status'] !='' ){
            $temp_array = array();
            $temp_array['key'] = 'invoice_payment_status';
            $temp_array['value'] = esc_html( $_POST['invoice_status'] );
            $temp_array['compare'] = '=';
            $temp_array['type'] = 'NUMERIC';
            $meta_query[] = $temp_array;
        }

        if( isset($_POST['invoice_type']) &&  $_POST['invoice_type'] !='' ){
            $temp_array = array();
            $temp_array['key'] = 'HOUZEZ_invoice_for';
            $temp_array['value'] = esc_html( $_POST['invoice_type'] );
            $temp_array['compare'] = 'LIKE';
            $temp_array['type'] = 'CHAR';
            $meta_query[] = $temp_array;
        }

        if( isset($_POST['startDate']) &&  $_POST['startDate'] !='' ){
            $temp_array = array();
            $temp_array['after'] = esc_html( $_POST['startDate'] );
            $date_query[] = $temp_array;
        }

        if( isset($_POST['endDate']) &&  $_POST['endDate'] !='' ){
            $temp_array = array();
            $temp_array['before'] = esc_html( $_POST['endDate'] );
            $date_query[] = $temp_array;
        }


        $invoices_args = array(
            'post_type' => 'blokhausre_invoice',
            'posts_per_page' => '-1',
            'meta_query' => $meta_query,
            'date_query' => $date_query,
            'author' => $userID
        );

        $invoices = new WP_Query( $invoices_args );
        $total_price = 0;

        ob_start();
        while ( $invoices->have_posts()): $invoices->the_post();
            $fave_meta = blokhausre_get_invoice_meta( get_the_ID() );
            get_template_part('template-parts/invoices');

            $total_price += $fave_meta['invoice_item_price'];
        endwhile;

        $result = ob_get_contents();
        ob_end_clean();

        echo json_encode( array( 'success' => true, 'result' => $result, 'total_price' => blokhausre_get_invoice_price( $total_price ) ) );
        wp_die();
    }
}

if( !function_exists('blokhausre_get_agent_info') ) {
    function blokhausre_get_agent_info( $args, $type ) {
        if( $type == 'for_grid_list' ) {
            return '<a href="'.$args[ 'link' ].'">'.$args[ 'agent_name' ].'</a> ';

        } elseif( $type == 'agent_form' ) {
            $output = '';

            $output .= '<div class="media agent-media">';
                $output .= '<div class="media-left">';
                    $output .= '<input type="checkbox">';
                    $output .= '<a href="'.$args[ 'link' ].'">';
                        $output .= '<img src="'.$args[ 'picture' ].'" alt="'.$args[ 'agent_name' ].'" width="75" height="75">';
                    $output .= '</a>';
                $output .= '</div>';

                $output .= '<div class="media-body">';
                    $output .= '<dl>';
                        if( !empty($args[ 'agent_name' ]) ) {
                            $output .= '<dd><i class="fa fa-user"></i> '.$args[ 'agent_name' ].'</dd>';
                        }
                        if( !empty( $args[ 'agent_mobile' ] ) ) {
                            $output .= '<dd><i class="fa fa-phone"></i><span class="clickToShow">'.esc_attr( $args[ 'agent_mobile' ] ).'</span></dd>';
                        }
                        $output .= '<dd><a href="'.$args[ 'link' ].'" class="view">'.esc_html__('View my listing', 'houzez' ).'</a></dd>';
                    $output .= '</dl>';
                $output .= '</div>';
            $output .= '</div>';

            return $output;
        }
    }
}

if( !function_exists('blokhausre_get_property_agent') ) {
    function blokhausre_get_property_agent($prop_id) {

        $agent_display_option = get_post_meta( $prop_id, 'fave_agent_display_option', true );
        $prop_agent_display = get_post_meta( $prop_id, 'fave_agents', true );
		$listing_agent = '';
        $prop_agent_num = $agent_num_call = $prop_agent = $prop_agent_link = $property_agent = '';
        if( $prop_agent_display != '-1' && $agent_display_option == 'agent_info' ) {

            $prop_agent_ids = get_post_meta( $prop_id, 'fave_agents' );
            // remove invalid ids
            $prop_agent_ids = array_filter( $prop_agent_ids, function($hz){
                return ( $hz > 0 );
            });

            $prop_agent_ids = array_unique( $prop_agent_ids );

            if ( ! empty( $prop_agent_ids ) ) {
                $agents_count = count( $prop_agent_ids );
                $listing_agent = array();
                foreach ( $prop_agent_ids as $agent ) {
                    if ( 0 < intval( $agent ) ) {
                        $agent_args = array();
                        $agent_args[ 'agent_id' ] = intval( $agent );
                        $agent_args[ 'agent_name' ] = get_the_title( $agent_args[ 'agent_id' ] );
                        $agent_args[ 'agent_mobile' ] = get_post_meta( $agent_args[ 'agent_id' ], 'fave_agent_mobile', true );
                        $agent_num_call = str_replace(array('(',')',' ','-'),'', $agent_args[ 'agent_mobile' ]);
                        $agent_args[ 'agent_email' ] = get_post_meta( $agent_args[ 'agent_id' ], 'fave_agent_email', true );
                        $agent_args[ 'link' ] = get_permalink($agent_args[ 'agent_id' ]);
                        $listing_agent[] = blokhausre_get_agent_info( $agent_args, 'for_grid_list' );
                    }
                }
            }

        } elseif( $agent_display_option == 'agency_info' ) {

            $prop_agency_ids = get_post_meta( $prop_id, 'fave_property_agency' );
            // remove invalid ids
            $prop_agency_ids = array_filter( $prop_agency_ids, function($hz){
                return ( $hz > 0 );
            });

            $prop_agency_ids = array_unique( $prop_agency_ids );

            if ( ! empty( $prop_agency_ids ) ) {
                $agency_count = count( $prop_agency_ids );
                $listing_agent = array();
                foreach ( $prop_agency_ids as $agency ) {
                    if ( 0 < intval( $agency ) ) {
                        $agency_args = array();
                        $agency_args[ 'agent_id' ] = intval( $agency );
                        $agency_args[ 'agent_name' ] = get_the_title( $agency_args[ 'agent_id' ] );
                        $agency_args[ 'agent_mobile' ] = get_post_meta( $agency_args[ 'agent_id' ], 'fave_agency_mobile', true );
                        $agent_num_call = str_replace(array('(',')',' ','-'),'', $agency_args[ 'agent_mobile' ]);
                        $agency_args[ 'agent_email' ] = get_post_meta( $agency_args[ 'agent_id' ], 'fave_agency_email', true );
                        $agency_args[ 'link' ] = get_permalink($agency_args[ 'agent_id' ]);
                        $listing_agent[] = blokhausre_get_agent_info( $agency_args, 'for_grid_list' );
                    }
                }
            }

        } elseif( $agent_display_option == 'author_info' ) {

            $listing_agent = array();
            $author_args = array();
            $author_args[ 'agent_name' ] = get_the_author();
            $author_args[ 'agent_mobile' ] = get_the_author_meta( 'fave_author_mobile' );
            $agent_num_call = str_replace(array('(',')',' ','-'),'', get_the_author_meta( 'fave_author_mobile' ));
            $author_args[ 'agent_email' ] = get_the_author_meta( 'email' );
            $author_args[ 'link' ] = get_author_posts_url( get_the_author_meta( 'ID' ) );

            $listing_agent[] = blokhausre_get_agent_info( $author_args, 'for_grid_list' );
        }
        return $listing_agent;
    }
}



/*-------------------------------------------------------------------------------
*
* Agency ajax pagination data
*-------------------------------------------------------------------------------*/
add_action('wp_ajax_blokhausre_ajax_agency_filter', 'blokhausre_ajax_agency_filter');
add_action('wp_ajax_nopriv_blokhausre_ajax_agency_filter', 'blokhausre_ajax_agency_filter');

if( !function_exists('blokhausre_ajax_agency_filter')) {
    function blokhausre_ajax_agency_filter() {

        $paged = isset($_POST['paged']) ? sanitize_text_field($_POST['paged']) : '';
        $agency_id = $_POST['agency_id'];

        $agency_agents = Houzez_Query::get_agency_agents_ids($agency_id);

        $loop_get_agency_agents_properties_ids = Houzez_Query::loop_get_agency_agents_properties_ids($agency_agents);
        $loop_agency_properties_ids = Houzez_Query::loop_agency_properties_ids($agency_id);
        $properties_ids = array_merge($loop_get_agency_agents_properties_ids, $loop_agency_properties_ids);

        if(empty($properties_ids)) {
            
            $agency_listing_args = array(
                'post_type' => 'property',
                'posts_per_page' => 9,
                'paged' => $paged,
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => 'mls_property_agency',
                        'value' => $agency_id,
                        'compare' => '='
                    )
                )
            );

            ?>
            <div class="grid-view grid-view-3-col">
                <div class="row">
            <?php
            $agency_qry = new WP_Query( $agency_listing_args );
            if( $agency_qry->have_posts() ):
                while( $agency_qry->have_posts() ): $agency_qry->the_post();
                    get_template_part('template-parts/property-for-listing');
                endwhile;
                wp_reset_postdata();
            else:
                get_template_part('template-parts/property', 'none');
            endif;
            echo '</div>';
            echo '</div>';
            echo '<hr>';
            blokhausre_halpmap_ajax_pagination( $agency_qry->max_num_pages, $paged, $range = 2 );

        } else {
            
            $agency_listing_args = array(
                'post_type' => 'property',
                'posts_per_page' => 9,
                'paged' => $paged,
                'post__in' => $properties_ids,
                'post_status' => 'publish'
            );

            ?>
            <div class="grid-view grid-view-3-col">
                <div class="row">
            <?php
            $agency_qry = new WP_Query( $agency_listing_args );
            if( $agency_qry->have_posts() ):
                while( $agency_qry->have_posts() ): $agency_qry->the_post();
                    get_template_part('template-parts/property-for-listing');
                endwhile;
                wp_reset_postdata();
            else:
                get_template_part('template-parts/property', 'none');
            endif;
            echo '</div>';
            echo '</div>';
            echo '<hr>';
            blokhausre_halpmap_ajax_pagination( $agency_qry->max_num_pages, $paged, $range = 2 );

        }

        wp_die();
    }
}


if ( !function_exists( 'blokhausre_get_agent_info_bottom' ) ) {
    function blokhausre_get_agent_info_bottom( $args, $type, $is_single = true ) {

       global $user_role;

       $user_info = get_userdata(get_the_author_meta('ID'));
       $user_role = implode(', ', $user_info->roles);
       $agent_phone_full = blokhausre_option('agent_phone_full');

        $form_title = esc_html__('CONTACT AGENT', 'houzez' );

        if( $user_role == 'blokhausre_owner' ) {
            $form_title = esc_html__('CONTACT OWNER', 'houzez' );
        } elseif( $user_role == 'blokhausre_seller' ) {
            $form_title = esc_html__('CONTACT SELLER', 'houzez' );
        } elseif( $user_role == 'blokhausre_manager' ) {
            $form_title = esc_html__('CONTACT MANAGER', 'houzez' );
        }  elseif( $user_role == 'blokhausre_agency' ) {
            $form_title = esc_html__('CONTACT AGENCY', 'houzez' );
        }

        if( $type == 'for_grid_list' ) {
            return '<a href="'.$args[ 'link' ].'">'.$args[ 'agent_name' ].'</a> ';

        } elseif( $type == 'agent_form' ) {
            $output = '';

            $output .= '<div class="media agent-media">';
            $output .= '<div class="media-left">';
            if ( $is_single == false ) :
                $output .= '<input type="checkbox" class="multiple-agent-check" name="target_email[]" value="' . $args['agent_email'] . '" >';
            endif;
            $output .= '<a href="'.$args[ 'link' ].'">';
            $output .= '<img src="'.$args[ 'picture' ].'" alt="'.$args[ 'agent_name' ].'" width="80" height="80">';
            $output .= '</a>';
            $output .= '</div>';

            $output .= '<div class="media-body">';
            $output .= '<dl>';

            //$output .= '<dt>' . $form_title . '</dt>';

            if ( !empty( $args[ 'agent_name' ] ) ) :

                $output .= '<dd><i class="fa fa-user"></i> ' . $args[ 'agent_name' ] . '</dd>';

            endif;

            $output .= '<dd>';

            // /agent_skype

            if ( !empty( $args[ 'agent_phone' ] ) ) :

                if( $agent_phone_full != 1 ) {
                    $output .= '<span class="hz-agent-phone"><i class="fa fa-phone"></i><span class="clickToShowPhone">' . $args[ 'agent_phone' ] . '</span></span>';
                } else {
                    $output .= '<span class="hz-agent-phone"><i class="fa fa-phone"></i><span>' . $args[ 'agent_phone' ] . '</span></span>';
                }

            endif;

            if ( !empty( $args[ 'agent_mobile' ] ) ) :

                if( $agent_phone_full != 1 ) {
                    $output .= '<span class="hz-agent-mobile"><i class="fa fa-mobile"></i><span class="clickToShow">' . $args[ 'agent_mobile' ] . '</span></span>';
                } else {
                    $output .= '<span class="hz-agent-mobile"><i class="fa fa-mobile"></i><span>' . $args[ 'agent_mobile' ] . '</span></span>';
                }

            endif;

            if ( !empty( $args[ 'agent_skype' ] ) ) :

                $output .= '<span><a href="skype:' . esc_attr( $args[ 'agent_skype' ] ) . '?call"><i class="fa fa-skype"></i>' . $args[ 'agent_skype' ] . '</a></span>';

            endif;

            $output .= '</dd>';

            $output .= '<dd>';

            if( !empty( $args[ 'facebook' ] ) ) :

                $output .= '<span><a class="btn-facebook" target="_blank" href="' . $args[ 'facebook' ] . '"><i class="fa fa-facebook-square"></i> <span>Facebook</span></a></span>';

            endif;

            if( !empty( $args[ 'instagram' ] ) ) :

                $output .= '<span><a class="btn-instagram" target="_blank" href="' . $args[ 'instagram' ] . '"><i class="fa fa-instagram"></i> <span>Instagram</span></a></span>';

            endif;

            if( !empty( $args[ 'twitter' ] ) ) :

                $output .= '<span><a class="btn-twitter" target="_blank" href="' . $args[ 'twitter' ] . '"><i class="fa fa-twitter-square"></i> <span>Twitter</span></a></span>';

            endif;

            if( !empty( $args[ 'linkedin' ] ) ) :

                $output .= '<span><a class="btn-linkedin" target="_blank" href="' . $args[ 'linkedin' ] . '"><i class="fa fa-linkedin-square"></i> <span>Linkedin</span></a></span>';

            endif;

            if( !empty( $args[ 'googleplus' ] ) ) :

                $output .= '<span><a class="btn-googleplus" target="_blank" href="' . $args[ 'googleplus' ] . '"><i class="fa fa-google-plus-square"></i> <span>Google Plus</span></a></span>';

            endif;

            if( !empty( $args[ 'youtube' ] ) ) :

                $output .= '<span><a class="btn-youtube" target="_blank" href="' . $args[ 'youtube' ] . '"><i class="fa fa-youtube-square"></i> <span>Youtube</span></a></span>';

            endif;

            $output .= '</dd>';

            // $output .= '<dd><a href="'.$args[ 'link' ].'" class="view">'.esc_html__('View my listing', 'houzez' ).'</a></dd>';

            $output .= '</dl>';
            $output .= '</div>';
            $output .= '</div>';

            return $output;

        }

    }
}

if ( !function_exists( 'blokhausre_get_agent_info_top' ) ) {
    function blokhausre_get_agent_info_top($args, $type, $is_single = true)
    {
        $view_listing = blokhausre_option('agent_view_listing');
        $agent_phone_full = blokhausre_option('agent_phone_full');

        if ($type == 'for_grid_list') {
            return '<a href="' . $args['link'] . '">' . $args['agent_name'] . '</a> ';

        } elseif ($type == 'agent_form') {
            $output = '';
            $output .= '<div class="media agent-media">';
            $output .= '<div class="media-left">';
            if ( $is_single == false ) {
                $output .= '<input type="checkbox" class="multiple-agent-check" name="target_email[]" value="' . $args['agent_email'] . '" >';
            }
            $output .= '<a href="' . $args['link'] . '">';
            $output .= '<img src="' . $args['picture'] . '" alt="' . $args['agent_name'] . '" width="75" height="75">';
            $output .= '</a>';
            $output .= '</div>';

            $output .= '<div class="media-body">';
            $output .= '<dl>';
            if (!empty($args['agent_name'])) {
                $output .= '<dd><i class="fa fa-user"></i> ' . $args['agent_name'] . '</dd>';
            }
            if (!empty($args['agent_mobile'])) {

                if( $agent_phone_full != 1 ) {
                    $output .= '<dd class="hz-agent-mobile"><i class="fa fa-phone"></i><span class="clickToShow">' . esc_attr($args['agent_mobile']) . '</span></dd>';
                } else {
                    $output .= '<dd class="hz-agent-mobile"><i class="fa fa-phone"></i><span>' . esc_attr($args['agent_mobile']) . '</span></dd>';
                }
            }
            if($view_listing != 0) {
                $output .= '<dd><a href="' . $args['link'] . '" class="view">' . esc_html__('View listings', 'houzez') . '</a></dd>';
            }
            $output .= '</dl>';
            $output .= '</div>';
            $output .= '</div>';

            return $output;
        }
    }
}

// blokhausre_property_clone
add_action( 'wp_ajax_nopriv_blokhausre_property_clone', 'blokhausre_property_clone' );
add_action( 'wp_ajax_blokhausre_property_clone', 'blokhausre_property_clone' );

if ( !function_exists( 'blokhausre_property_clone' ) ) {
    function blokhausre_property_clone() {

        if ( isset( $_POST['propID'] ) ) {

            global $wpdb;
            if (! isset( $_POST['propID'] ) ) {
                wp_die('No post to duplicate has been supplied!');
            }
            $post_id = absint( $_POST['propID'] );
            $post = get_post( $post_id );
            $current_user = wp_get_current_user();
            $new_post_author = $current_user->ID;

            if (isset( $post ) && $post != null) {

                /*
                 * new post data array
                 */
                $args = array(
                    'comment_status' => $post->comment_status,
                    'ping_status'    => $post->ping_status,
                    'post_author'    => $new_post_author,
                    'post_content'   => $post->post_content,
                    'post_excerpt'   => $post->post_excerpt,
                    'post_name'      => $post->post_name,
                    'post_parent'    => $post->post_parent,
                    'post_password'  => $post->post_password,
                    'post_status'    => 'draft',
                    'post_title'     => $post->post_title,
                    'post_type'      => $post->post_type,
                    'to_ping'        => $post->to_ping,
                    'menu_order'     => $post->menu_order
                );

                /*
                 * insert the post by wp_insert_post() function
                 */
                $new_post_id = wp_insert_post( $args );

                /*
                 * get all current post terms ad set them to the new post draft
                 */
                $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
                foreach ($taxonomies as $taxonomy) {
                    $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                    wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
                }

                /*
                 * duplicate all post meta just in two SQL queries
                 */
                $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
                if (count($post_meta_infos)!=0) {
                    $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                    foreach ($post_meta_infos as $meta_info) {
                        $meta_key = $meta_info->meta_key;
                        $meta_value = addslashes($meta_info->meta_value);
                        $sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
                    }
                    $sql_query.= implode(" UNION ALL ", $sql_query_sel);
                    $wpdb->query($sql_query);
                }

                update_post_meta( $new_post_id, 'fave_featured', 0 );
                update_post_meta( $new_post_id, 'fave_payment_status', 'not_paid' );

                /*
                 * finally, redirect to the edit post screen for the new draft
                 */
                // wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
                wp_die();
            } else {
                wp_die('Post creation failed, could not find original post: ' . $post_id);
            }

        }

    }
}

// blokhausre_property_on_hold
add_action( 'wp_ajax_blokhausre_property_on_hold', 'blokhausre_property_on_hold' );

if ( !function_exists( 'blokhausre_property_on_hold' ) ) {
    function blokhausre_property_on_hold() {

        if ( isset( $_POST['propID'] ) ) {

            global $wpdb;
            if (! isset( $_POST['propID'] ) ) {
                wp_die('No post to put on hold has been supplied!');
            }
            $post_id = absint( $_POST['propID'] );
            
            $post_status = get_post_status( $post_id );

            if($post_status == 'publish') { 
                $post = array(
                    'ID'            => $post_id,
                    'post_status'   => 'on_hold'
                );
            } elseif ($post_status == 'on_hold') {
                $post = array(
                    'ID'            => $post_id,
                    'post_status'   => 'publish'
                );
            }
            $post_id =  wp_update_post($post);
            wp_die();
        }

    }
}

/*-----------------------------------------------------------------------------------*/
/*	Houzez Invoice Print Property
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_blokhausre_create_invoice_print', 'blokhausre_create_invoice_print' );
add_action( 'wp_ajax_blokhausre_create_invoice_print', 'blokhausre_create_invoice_print' );

if ( !function_exists( 'blokhausre_create_invoice_print' ) ) {
    function blokhausre_create_invoice_print() {

        if(!isset($_POST['invoice_id'])|| !is_numeric($_POST['invoice_id'])){
            exit();
        }

        $blokhausre_local = blokhausre_get_localization();
        $invoice_id = intval($_POST['invoice_id']);
        $the_post= get_post( $invoice_id );

        if( $the_post->post_type != 'blokhausre_invoice' || $the_post->post_status != 'publish' ) {
            exit();
        }

        print  '<html><head><link href="'.get_stylesheet_uri().'" rel="stylesheet" type="text/css" />';
        print  '<html><head><link href="'.get_template_directory_uri().'/css/bootstrap.min.css" rel="stylesheet" type="text/css" />';
        print  '<html><head><link href="'.get_template_directory_uri().'/css/main.css" rel="stylesheet" type="text/css" />';

        if( is_rtl() ) {
            print '<link href="'.get_template_directory_uri().'/css/rtl.css" rel="stylesheet" type="text/css" />';
            print '<link href="'.get_template_directory_uri().'/css/bootstrap-rtl.min.css" rel="stylesheet" type="text/css" />';
        }
        print '</head>';
        print  '<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script><script>$(window).load(function(){ print(); });</script>';
        print  '<body>';

        global $current_user;
        wp_get_current_user();
        $userID         = $current_user->ID;
        $user_login     = $current_user->user_login;
        $user_email     = $current_user->user_email;
        $first_name     = $current_user->first_name;
        $last_name     = $current_user->last_name;
        $user_address = get_user_meta( $userID, 'fave_author_address', true);
        if( !empty($first_name) && !empty($last_name) ) {
            $fullname = $first_name.' '.$last_name;
        } else {
            $fullname = $current_user->display_name;
        }
        $invoice_id = $_REQUEST['invoice_id'];
        $post = get_post( $invoice_id );
        $invoice_data = blokhausre_get_invoice_meta( $invoice_id );

        $publish_date = $post->post_date;
        $publish_date = date_i18n( get_option('date_format'), strtotime( $publish_date ) );
        $invoice_logo = blokhausre_option( 'invoice_logo', false, 'url' );
        $invoice_company_name = blokhausre_option( 'invoice_company_name' );
        $invoice_address = blokhausre_option( 'invoice_address' );
        $invoice_phone = blokhausre_option( 'invoice_phone' );
        $invoice_additional_info = blokhausre_option( 'invoice_additional_info' );
        $invoice_thankyou = blokhausre_option( 'invoice_thankyou' );
        ?>
        <div class="row dashboard-inner-main">
            <div class="col-lg-12 col-md-12 col-sm-12 dashboard-inner-left">
                <div class="invoice-area">
                    <div class="invoice-detail">
                        <div class="invoice-header">
                            <div class="invoice-head-left">
                                <?php if( !empty($invoice_logo) ) { ?>
                                    <img src="<?php echo esc_url($invoice_logo); ?>" alt="logo">
                                <?php } ?>
                            </div>
                            <div class="invoice-date">
                                <p class="invoice-number"><strong> <?php echo esc_html__('INVOICE', 'houzez'); ?> </strong> : <?php echo esc_attr($invoice_id); ?> </p>
                                <p class="invoice-date"><strong><?php echo esc_html__('DATE', 'houzez'); ?></strong> : <?php echo $publish_date; ?> </p>
                            </div>
                        </div>

                        <div class="invoice-contact">
                            <div class="invoice-contact-left">
                                <p><strong><?php echo esc_html__('To:', 'houzez'); ?></strong><br>
                                    <?php echo esc_attr($fullname); ?><br>
                                    <?php if( !empty($user_address)) { echo $user_address.'<br>'; }?>
                                    <?php echo esc_html__('Email:', 'houzez'); ?> <?php echo esc_attr($user_email);?>
                                </p>
                            </div>
                            <div class="invoice-contact-right">
                                <?php if( !empty($invoice_company_name) ) { ?>
                                    <h2> <?php echo esc_attr($invoice_company_name); ?></h2>
                                <?php } ?>
                                <p>
                                    <?php
                                    echo $invoice_address;
                                    if( !empty($invoice_phone) ) {
                                        echo '<br>'.esc_html__('Phone:', 'houzez'); echo esc_attr($invoice_phone);
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>

                        <table class="table invoice-total">
                            <tr>
                                <td class="description"> <?php echo $blokhausre_local['billing_for']; ?> </td>
                                <td class="amount">
                                    <?php
                                    if( $invoice_data['invoice_billion_for'] != 'package' && $invoice_data['invoice_billion_for'] != 'Package' ) {
                                        echo esc_html($invoice_data['invoice_billion_for']);
                                    } else {
                                        echo esc_html__('Membership Plan', 'houzez').' '. get_the_title( get_post_meta( $invoice_id, 'HOUZEZ_invoice_item_id', true) );
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="description"> <?php echo $blokhausre_local['billing_type']; ?> </td>
                                <td class="amount"> <?php echo esc_html( $invoice_data['invoice_billing_type'] ); ?> </td>
                            </tr>
                            <tr>
                                <td class="description"> <?php echo $blokhausre_local['payment_method']; ?> </td>
                                <td class="amount">
                                    <?php if( $invoice_data['invoice_payment_method'] == 'Direct Bank Transfer' ) {
                                        echo $blokhausre_local['bank_transfer'];
                                    } else {
                                        echo $invoice_data['invoice_payment_method'];
                                    } ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="description"> </td>
                                <td class="amount"><strong><?php echo $blokhausre_local['invoice_price']; ?></strong> <?php echo blokhausre_get_invoice_price( $invoice_data['invoice_item_price'] )?> </td>
                            </tr>
                        </table>

                        <?php if( !empty($invoice_additional_info) || !empty($invoice_thankyou) ) { ?>
                            <div class="invoice-info">
                                <?php if( !empty($invoice_additional_info)) { ?>
                                    <h3> <?php echo esc_html__('Additional Information:', 'houzez'); ?> </h3>
                                    <p> <?php echo $invoice_additional_info; ?> </p>
                                <?php } ?>
                                <?php if( !empty($invoice_thankyou) ) { ?>
                                    <h3> <?php echo $invoice_thankyou; ?> </h3>
                                <?php } ?>
                            </div>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
        <?php

        print '</body></html>';
        wp_die();
    }
}

if ( !function_exists( 'blokhausre_get_agent_info_bottom_v2' ) ) {
    function blokhausre_get_agent_info_bottom_v2( $args, $type, $is_single = true ) {

        ob_start();
        ?>
        <div class="agent-info-block">
            <div class="agent-thumb">
                <img src="<?php echo esc_url( $args[ 'picture' ] ); ?>" alt="<?php echo esc_attr( $args['agent_name'] ); ?>" width="80" height="80">
                <?php if ( $is_single == false ) { ?>
                <input type="checkbox" class="multiple-agent-check" name="target_email[]" value="<?php echo $args['agent_email']; ?>" >
                <?php } ?>
            </div>
            
            <ul class="agent-info">
                <li class="agent-name">
                    <?php if( !empty( $args['agent_name'] ) ) { ?>
                        <i class="fa fa-user"></i> <?php echo esc_attr( $args['agent_name'] ); ?>
                    <?php } ?>
                </li>
                <li class="agent-mobile">
                    <?php if( !empty( $args['agent_mobile'] ) ) { ?>
                        <i class="fa fa-mobile"></i> <?php echo esc_attr( $args['agent_mobile'] );?>
                    <?php } ?>
                </li>
                <li class="agent-phone">
                    <?php if( !empty( $args['agent_phone'] ) ) { ?>
                        <i class="fa fa-phone"></i> <?php echo esc_attr( $args['agent_phone'] );?>
                    <?php } ?>
                </li>
            </ul>
            <ul class="profile-social">
                <?php if( !empty( $args['facebook'] ) ) { ?>
                    <li><a class="btn-facebook" target="_blank" href="<?php echo esc_url( $args['facebook'] ); ?>"><i class="fa fa-facebook-square"></i></a></li>
                <?php } ?>

                <?php if( !empty( $args['twitter'] ) ) { ?>
                    <li><a class="btn-twitter" target="_blank" href="<?php echo esc_url( $args['twitter'] ); ?>"><i class="fa fa-twitter-square"></i></a></li>
                <?php } ?>

                <?php if( !empty( $args['linkedin'] ) ) { ?>
                    <li><a class="btn-linkedin" target="_blank" href="<?php echo esc_url( $args['linkedin'] ); ?>"><i class="fa fa-linkedin-square"></i></a></li>
                <?php } ?>

                <?php if( !empty( $args['googleplus'] ) ) { ?>
                    <li><a class="btn-google-plus" target="_blank" href="<?php echo esc_url( $args['googleplus'] ); ?>"><i class="fa fa-google-plus-square"></i></a></li>
                <?php } ?>

                <?php if( !empty( $args['youtube'] ) ) { ?>
                    <li><a class="btn-youtube" target="_blank" href="<?php echo esc_url( $args['youtube'] ); ?>"><i class="fa fa-youtube-square"></i></a></li>
                <?php } ?>
            </ul>
            <a class="view-link" href="<?php echo esc_url($args[ 'link' ]); ?>"><?php esc_html_e( 'View listings', 'houzez' ); ?></a>
        </div>
        <?php
        $data = ob_get_contents();
        ob_clean();

        return $data;

    }
}

/* --------------------------------------------------------------------------
* Property delete ajax
* --------------------------------------------------------------------------- */
add_action( 'wp_ajax_nopriv_blokhausre_delete_property', 'blokhausre_delete_property' );
add_action( 'wp_ajax_blokhausre_delete_property', 'blokhausre_delete_property' );

if ( !function_exists( 'blokhausre_delete_property' ) ) {

    function blokhausre_delete_property()
    {

        $nonce = $_REQUEST['security'];
        if ( ! wp_verify_nonce( $nonce, 'delete_my_property_nonce' ) ) {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'Security check failed!', 'houzez' ) );
            echo json_encode( $ajax_response );
            die;
        }

        if ( !isset( $_REQUEST['prop_id'] ) ) {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'No Property ID found', 'houzez' ) );
            echo json_encode( $ajax_response );
            die;
        }

        $propID = $_REQUEST['prop_id'];
        $post_author = get_post_field( 'post_author', $propID );

        global $current_user;
        wp_get_current_user();
        $userID      =   $current_user->ID;

        if ( $post_author == $userID ) {
            wp_delete_post( $propID );
            $ajax_response = array( 'success' => true , 'mesg' => esc_html__( 'Property Deleted', 'houzez' ) );
            echo json_encode( $ajax_response );
            die;
        } else {
            $ajax_response = array( 'success' => false , 'reason' => esc_html__( 'Permission denied', 'houzez' ) );
            echo json_encode( $ajax_response );
            die;
        }

    }

}


/* --------------------------------------------------------------------------
* Property load more
* --------------------------------------------------------------------------- */
add_action( 'wp_ajax_nopriv_blokhausre_loadmore_properties', 'blokhausre_loadmore_properties' );
add_action( 'wp_ajax_blokhausre_loadmore_properties', 'blokhausre_loadmore_properties' );

if ( !function_exists( 'blokhausre_loadmore_properties' ) ) {
    function blokhausre_loadmore_properties() {

        $fake_loop_offset = 0; 

        $tax_query = array();
        //$property_ids_array = array();
        $prop_grid_style = $_POST['grid_style'];
        $property_type = blokhausre_traverse_comma_string($_POST['type']);
        $property_status = blokhausre_traverse_comma_string($_POST['status']);
        $property_state = blokhausre_traverse_comma_string($_POST['state']);
        $property_city = blokhausre_traverse_comma_string($_POST['city']);
        $property_area = blokhausre_traverse_comma_string($_POST['area']);
        $property_label = blokhausre_traverse_comma_string($_POST['label']);
        $blokhausre_user_role = $_POST['user_role'];
        $featured_prop = $_POST['featured_prop'];
        $posts_limit = $_POST['prop_limit'];
        $sort_by = $_POST['sort_by'];
        $offset = $_POST['offset'];
        $paged = $_POST['paged'];

        $wp_query_args = array(
            'ignore_sticky_posts' => 1
        );

        if( !empty($blokhausre_user_role) ) {
            $role_ids = blokhausre_author_ids_by_role( $blokhausre_user_role );
            if (!empty($role_ids)) {
                $wp_query_args['author__in'] = $role_ids;
            }
        }

        if (!empty($property_type)) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $property_type
            );
        }

        if (!empty($property_status)) {
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => $property_status
            );
        }
        if (!empty($property_state)) {
            $tax_query[] = array(
                'taxonomy' => 'property_state',
                'field' => 'slug',
                'terms' => $property_state
            );
        }
        if (!empty($property_city)) {
            $tax_query[] = array(
                'taxonomy' => 'property_city',
                'field' => 'slug',
                'terms' => $property_city
            );
        }
        if (!empty($property_area)) {
            $tax_query[] = array(
                'taxonomy' => 'property_area',
                'field' => 'slug',
                'terms' => $property_area
            );
        }
        if (!empty($property_label)) {
            $tax_query[] = array(
                'taxonomy' => 'property_label',
                'field' => 'slug',
                'terms' => $property_label
            );
        }

        if ( $sort_by == 'a_price' ) {
            $wp_query_args['orderby'] = 'meta_value_num';
            $wp_query_args['meta_key'] = 'fave_property_price';
            $wp_query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_price' ) {
            $wp_query_args['orderby'] = 'meta_value_num';
            $wp_query_args['meta_key'] = 'fave_property_price';
            $wp_query_args['order'] = 'DESC';
        } else if ( $sort_by == 'a_date' ) {
            $wp_query_args['orderby'] = 'date';
            $wp_query_args['order'] = 'ASC';
        } else if ( $sort_by == 'd_date' ) {
            $wp_query_args['orderby'] = 'date';
            $wp_query_args['order'] = 'DESC';
        } else if ( $sort_by == 'featured_top' ) {
            $wp_query_args['orderby'] = 'meta_value';
            $wp_query_args['meta_key'] = 'fave_featured';
            $wp_query_args['order'] = 'DESC';
        } else if ( $sort_by == 'random' ) {
            $wp_query_args['orderby'] = 'rand';
            $wp_query_args['order'] = 'DESC';
        }

        if (!empty($featured_prop)) {
            
            if( $featured_prop == "yes" ) {
                $wp_query_args['meta_key'] = 'fave_featured';
                $wp_query_args['meta_value'] = '1';
            } else {
                $wp_query_args['meta_key'] = 'fave_featured';
                $wp_query_args['meta_value'] = '0';
            }
        }

        $tax_count = count( $tax_query );

    
        if( $tax_count > 1 ) {
            $tax_query['relation'] = 'AND';
        }
        if( $tax_count > 0 ){
            $wp_query_args['tax_query'] = $tax_query;
        }

        $wp_query_args['post_status'] = 'publish';

        if (empty($posts_limit)) {
            $posts_limit = get_option('posts_per_page');
        }
        $wp_query_args['posts_per_page'] = $posts_limit;

        if (!empty($paged)) {
            $wp_query_args['paged'] = $paged;
        } else {
            $wp_query_args['paged'] = 1;
        }

        if (!empty($offset) and $paged > 1) {
            $wp_query_args['offset'] = $offset + ( ($paged - 1) * $posts_limit) ;
        } else {
            $wp_query_args['offset'] = $offset ;
        }

        $fake_loop_offset = $offset;

        $wp_query_args['post_type'] = 'property';
        
        $the_query = new WP_Query($wp_query_args);

        if( $prop_grid_style == "v_2" ) {
            if ($the_query->have_posts()) :
                while ($the_query->have_posts()) : $the_query->the_post();

                    get_template_part('template-parts/property-for-listing-v2');

                endwhile;
                wp_reset_postdata();
            else:
                echo 'no_result';
            endif;
        } else {
            if ($the_query->have_posts()) :
                while ($the_query->have_posts()) : $the_query->the_post();

                    get_template_part('template-parts/property-for-listing');

                endwhile;
                wp_reset_postdata();
            else:
                echo 'no_result';
            endif;
        }

        wp_die();
    }
}