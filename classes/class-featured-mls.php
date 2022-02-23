
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BLOKHAUSRE_Featured_mls {
    
    /**
     * Properties list API
     *
     * @since 1.0.0
     * @access protected
     * @var string
     */
    protected static $api_endpoint  = 'https://api.bridgedataoutput.com/api/v2/miamire/listings';

    /**
     * Initialize
     *
     * @access public
     * @return void
     */
    public static function init() {
        static::$api_endpoint  = 'https://api.bridgedataoutput.com/api/v2/miamire/listings';
    }


    /**
     *
     * Insert/Update database with properties from MLS
     *
     */
    public static function update($page = '') {
        $data = array();
        $option = get_option( 'blokhausre_api_settings' );
        if ( ! isset( $option['api_key'] ) ) {
            return null;
        }

        $access_token = $option['api_key'];
        $limit_query = $option['limit_query'];
        $mlsStatus = $option['mlsStatus'];

        if(empty($access_token)) {
            return null;
        }

        if( empty($page) || $page == '') { $page = 1; }

        $mlsStatus = ( isset($mlsStatus) || $mlsStatus!='' ) ? $mlsStatus : 'Active' ;
        $limit_query = ( isset($limit_query) || $limit_query!='' ) ? intval($limit_query) : 200 ;

        $date_properties_yesterday = date('Y-m-d',strtotime("-1 days"));                
        $date_properties_yesterday = $date_properties_yesterday.'T00:00:00Z';
        $page_first_result = ($page-1) * $limit_query; 

        $api_args = array(
            'method'     => 'GET'
        );
        $queries      = array(
            'access_token'  => $access_token,
            'offset'        => $page_first_result,
            'limit'         => $limit_query,
            'sortBy'        => 'ListPrice',
            'order'         => 'desc',
            //'MlsStatus'     => $mlsStatus,
             // comment the next line to get all properties by date from API 
            'BridgeModificationTimestamp.gt' => '2022-01-01T00:00:00Z',
            
            'ListOfficeKey'  => 'db7f55fa7c207e6d0907c49119bf2245',
            "or[0][PropertyType][prefix]" => 'Residential Lease',
            "or[1][PropertyType][prefix]" => 'Residential',            
        );

        self::getPropertyOfMLS($queries, $api_args, 1, $limit_query);

        return false;
    }

    public static function getPropertyOfMLS($queries, $api_args, $page, $limit_query){

        if( empty($page) || $page == '') { $page = 1; }
        //WP_CLI::log( 'Page: '.$page );
        $page_first_result = ($page-1) * $limit_query; 

        $a2=array(
            'offset'        => $page_first_result,
            'limit'         => $limit_query,
        );
        $queries = array_replace($queries,$a2);

        $property_data = add_query_arg( $queries, static::$api_endpoint );
        WP_CLI::log( $property_data );
        $response = wp_remote_get( $property_data, $api_args );
        $coderesponse = wp_remote_retrieve_response_code( $response );

        if ( $coderesponse == 200 ) {
            $result = json_decode( wp_remote_retrieve_body( $response ), false );            
            $total_properties = $result->total;
            WP_CLI::log( 'Total Properties: '.$total_properties );
            $totalPages = ceil($total_properties/$limit_query);
            WP_CLI::log( 'Page: '.$page.' of '.$totalPages );

            $api_ids = array();
            for($i = 0; $i < count($result->bundle); $i++){
                $ListingId = $result->bundle[$i]->ListingId;
                $BuildingName = $result->bundle[$i]->BuildingName;
                $Description = $result->bundle[$i]->PublicRemarks;

                $price = $result->bundle[$i]->ListPrice;
                $title = $result->bundle[$i]->BuildingName;
                $address = $result->bundle[$i]->UnparsedAddress;
                $description = $result->bundle[$i]->PublicRemarks;
                $city = $result->bundle[$i]->City;
                $state = $result->bundle[$i]->StateOrProvince;
                $zipcode = $result->bundle[$i]->PostalCode;
                //$country = $result->bundle[$i]->Country;
                $country = null;
                $county = $result->bundle[$i]->CountyOrParish;
                
                $Coordinates = $result->bundle[$i]->Coordinates;
                $latitude = $Coordinates[1];
                $longitude = $Coordinates[0];
                $PropertyType = $result->bundle[$i]->PropertyType;
                $ListingId = $result->bundle[$i]->ListingId;
                $BuyerAgentFullName = $result->bundle[$i]->BuyerAgentFullName;
                $ListOfficeName = $result->bundle[$i]->ListOfficeName;
                $MlsStatus = $result->bundle[$i]->MlsStatus;  
                         

                $AccessibilityFeatures = $result->bundle[$i]->AccessibilityFeatures;
                $BuildingFeatures = $result->bundle[$i]->BuildingFeatures;
                $CommunityFeatures = $result->bundle[$i]->CommunityFeatures;
                $DoorFeatures = $result->bundle[$i]->DoorFeatures;
                $ExteriorFeatures = $result->bundle[$i]->ExteriorFeatures;

                $InteriorFeatures = $result->bundle[$i]->InteriorFeatures;
                $WaterfrontYN = $result->bundle[$i]->WaterfrontYN;
                $PoolPrivateYN = $result->bundle[$i]->MIAMIRE_PoolYN;
                $TaxYear = $result->bundle[$i]->TaxYear;
                $TaxAnnualAmount = $result->bundle[$i]->TaxAnnualAmount;                        
                
                $LotSizeSquareFeet = $result->bundle[$i]->LotSizeSquareFeet;
                $MIAMIRE_RATIO_CurrentPrice_By_SQFT = $result->bundle[$i]->MIAMIRE_RATIO_CurrentPrice_By_SQFT;
                $Furnished = $result->bundle[$i]->Furnished;
                $BedroomsTotal = $result->bundle[$i]->BedroomsTotal;
                $BathroomsTotalDecimal = $result->bundle[$i]->BathroomsTotalDecimal;
                $RoomsTotal = $result->bundle[$i]->RoomsTotal;
                $GarageSpaces = $result->bundle[$i]->GarageSpaces;
                $LivingArea = $result->bundle[$i]->LivingArea; 
                $YearBuilt = $result->bundle[$i]->YearBuilt;
                $FrontageType = $result->bundle[$i]->FrontageType;
                $GarageSpaces = $result->bundle[$i]->GarageSpaces;

                $ListingContractDate = $result->bundle[$i]->ListingContractDate;

                $LaundryFeatures = $result->bundle[$i]->LaundryFeatures;
                $LotFeatures = $result->bundle[$i]->LotFeatures;
                $ParkingFeatures = $result->bundle[$i]->ParkingFeatures;
                $PatioAndPorchFeatures = $result->bundle[$i]->PatioAndPorchFeatures;
                $PoolFeatures = $result->bundle[$i]->PoolFeatures;
                $SpecialListingConditions = $result->bundle[$i]->SpecialListingConditions;
                $UnitNumber = $result->bundle[$i]->UnitNumber;
                $SubdivisionName = $result->bundle[$i]->SubdivisionName;
                $area = self::get_Area($SubdivisionName);

                //WP_CLI::log('Neighborhood: '.$area);

                if (isset($WaterfrontYN)) {
                    $WaterfrontYN = $WaterfrontYN==false ? 0 : 1;
                }

                if ($WaterfrontYN) {
                    $arraywaterfront = array('Water front');
                }else{
                    $arraywaterfront = array();
                }

                
                $PoolPrivateYN = $PoolPrivateYN==false ? 0 : 1;
                $Furnished = $Furnished=='Unfurnished' ? 0 : 1;
                $zoom = '14';

                $Media = $result->bundle[$i]->Media;
                if ($Media!=null || $Media!='') {
                    $images = json_decode(json_encode($Media), true);
                }

                $type = $result->bundle[$i]->PropertySubType;

                $CloseDate='';
                
                if ($MlsStatus=='Closed Sale') {
                    $price = $result->bundle[$i]->ClosePrice;
                    $CloseDate = $result->bundle[$i]->CloseDate;
                }

                $brkr = $ListOfficeName!=null ? $ListOfficeName : $BuyerAgentFullName;
                $country = $country!=null ? $country : 'United States';
                $disclaimer = self::get_disclaimer($ListingId,$brkr);
                $location = array("address" => $address, "lat" => $latitude, "lng" => $longitude, "zoom" => $zoom);
                

                $status = $result->bundle[$i]->PropertyType;
                if ($status=='Residential Lease') {
                    $status='For Rent';
                }
                if ($status=='Residential') {
                    $status='For Sale';
                }

                $label = $result->bundle[$i]->MlsStatus;
                if($label=='Closed Sale'){
                    $label = 'Sold';
                }else if($label == 'Active'){
                    $label = 'Hot';
                }else{
                    $label = $label;
                }                           

                $array_values = array(
                    'mls_property_price' => $price,
                    'mls_property_sec_price' => '',
                    'mls_property_price_prefix' => 'From',
                    'mls_property_price_postfix' => '',
                    'mls_property_size' => $LivingArea,
                    'mls_property_size_prefix' => 'Sq Ft',
                    'mls_property_land' => '',
                    'mls_property_land_postfix' => 'Sq Ft',
                    'mls_property_bedrooms' => $BedroomsTotal,
                    'mls_property_rooms' => $RoomsTotal,
                    'mls_property_bathrooms' => $BathroomsTotalDecimal,
                    'mls_property_garage' => $GarageSpaces,
                    'mls_property_garage_size' => '',
                    'mls_property_year' => $YearBuilt,
                    'mls_property_id' => $ListingId,

                    'mls_property_taxyear' => $TaxYear,
                    'mls_property_taxannualamount' => $TaxAnnualAmount,
                    'mls_property_waterfrontyn' => $WaterfrontYN,
                    'mls_property_poolprivateyn' => $PoolPrivateYN,
                    'mls_property_lotsizesquarefeet' => $LotSizeSquareFeet,
                    'mls_property_pricebysqft' => $MIAMIRE_RATIO_CurrentPrice_By_SQFT,
                    'mls_property_furnished' => $Furnished,

                    'mls_property_map' => '1',
                    'mls_property_map_address' => $address,
                    'mls_property_map_street_view' => 'hide',
                    'mls_property_map_latitude' => $latitude,
                    'mls_property_map_longitude' => $longitude,

                    'mls_property_address' => $address,
                    'mls_property_zip' => $zipcode,
                    'mls_featured' => '1',
                    'mls_loggedintoview' => '0',
                    'mls_property_disclaimer' => $disclaimer,

                    'mls_agent_display_option' => 'agent_info',
                    'mls_agents' => '156',
                    'mls_property_subdivisionname' => $SubdivisionName,

                    'mls_property_unitnumber' => $UnitNumber,
                    'mls_property_listingcontractdate' => $ListingContractDate,
                );

                $existingPost = self::get_post_by_meta( array( 'meta_key' => 'mls_property_id', 'meta_value' => $ListingId ) );

                $action = $existingPost ? 'update' : 'insert';
                WP_CLI::log("Existing Post: ".$existingPost." - Action: ".$action." - Area: ".$area);

                if ( $action == 'update' ) {

                    $args = array(
                      'post_type' => 'propertymls',
                      'ignore_sticky_posts' => 1,
                      'posts_per_page' => 1,
                      'meta_key' => 'mls_property_id',
                      'meta_value' => $ListingId,
                      'meta_compare' => '=='
                    );
                    //Using WP_Query
                    $wv_query = new WP_Query( $args );
                    if ( $wv_query->have_posts() ) {
                        while ( $wv_query->have_posts() ) {
                            $wv_query->the_post();
                            $property_ID = get_the_ID(); 

                            // Update properties
                            $update_post = array(
                                'ID'            => $property_ID,
                                'post_type'     => 'propertymls',
                                'post_title'    => $title,
                                'post_status'   => 'publish',
                                'post_author'   => 1,
                                'post_content'  => $description,
                                'meta_input'    => $array_values,
                            );                             
                            // Update the post into the database
                            wp_update_post( $update_post );
                            // Update the post Yoast SEO into the database
                            update_post_meta( $property_ID, '_yoast_wpseo_focuskw', $title );
                            update_post_meta( $property_ID, '_yoast_wpseo_metadesc', substr($description, 0, 135) );
                            update_post_meta( $property_ID, 'mls_property_location', $location );
                            // Update the post TAxonomies into the database
                            self::addPropertyTaxonomy( $property_ID, $type, 'propertymls_type' );
                            self::addPropertyTaxonomy( $property_ID, $status, 'propertymls_status' );
                            self::addPropertyTaxonomy( $property_ID, $label, 'propertymls_label' );
                            self::addPropertyTaxonomy( $property_ID, $country, 'propertymls_country' );
                            self::addPropertyTaxonomy( $property_ID, $state, 'propertymls_state' );
                            self::addPropertyTaxonomy( $property_ID, $city, 'propertymls_city' );
                            self::addPropertyTaxonomy( $property_ID, $area, 'propertymls_area' );
                            // Update the post Features into the database
                            self::addPropertyFeatures($property_ID,$AccessibilityFeatures);
                            self::addPropertyFeatures($property_ID,$BuildingFeatures);
                            self::addPropertyFeatures($property_ID,$CommunityFeatures);
                            self::addPropertyFeatures($property_ID,$DoorFeatures);
                            self::addPropertyFeatures($property_ID,$ExteriorFeatures);
                            self::addPropertyFeatures($property_ID,$InteriorFeatures);
                            self::addPropertyFeatures($property_ID,$LaundryFeatures);
                            self::addPropertyFeatures($property_ID,$LotFeatures);
                            self::addPropertyFeatures($property_ID,$ParkingFeatures);
                            self::addPropertyFeatures($property_ID,$PatioAndPorchFeatures);
                            self::addPropertyFeatures($property_ID,$PoolFeatures);
                            self::addPropertyFeatures($property_ID,$arraywaterfront);
                             
                        }
                    }
                    //Reset post data to original query
                    wp_reset_postdata(); 
                    $message = 'Updated: '.$ListingId.' - '.$type.' - '.$status.' - '.$label.' - '.$country.' - '.$state.' - '.$city.' - '.$area;
                    WP_CLI::log( $message );
                } elseif ( $action == 'insert' ) {  
                    $new_post = array(
                        'post_type'     => 'propertymls',
                        'post_title'    => $title,
                        'post_status'   => 'publish',
                        'post_author'   => 1,
                        'post_content'  => $description,
                        'meta_input'    => $array_values,
                    );            
                    // Insert the post into the database
                    $property_ID = wp_insert_post($new_post);
                    // Insert the post images into the database
                    self::addImagesToProperty($images,$property_ID,$action); 
                    // Insert the post Yoast SEO into the database
                    update_post_meta( $property_ID, '_yoast_wpseo_focuskw', $title ); 
                    update_post_meta( $property_ID, '_yoast_wpseo_metadesc', substr($description, 0, 135) );
                    update_post_meta( $property_ID, 'mls_property_location', $location );
                    // Insert the post TAxonomies into the database
                    self::addPropertyTaxonomy( $property_ID, $type, 'propertymls_type' );
                    self::addPropertyTaxonomy( $property_ID, $status, 'propertymls_status' );
                    self::addPropertyTaxonomy( $property_ID, $label, 'propertymls_label' );
                    self::addPropertyTaxonomy( $property_ID, $country, 'propertymls_country' );
                    self::addPropertyTaxonomy( $property_ID, $state, 'propertymls_state' );
                    self::addPropertyTaxonomy( $property_ID, $city, 'propertymls_city' );
                    self::addPropertyTaxonomy( $property_ID, $area, 'propertymls_area' );
                    // Insert the post Features into the database
                    self::addPropertyFeatures($property_ID,$AccessibilityFeatures);
                    self::addPropertyFeatures($property_ID,$BuildingFeatures);
                    self::addPropertyFeatures($property_ID,$CommunityFeatures);
                    self::addPropertyFeatures($property_ID,$DoorFeatures);
                    self::addPropertyFeatures($property_ID,$ExteriorFeatures);
                    self::addPropertyFeatures($property_ID,$InteriorFeatures);
                    self::addPropertyFeatures($property_ID,$LaundryFeatures);
                    self::addPropertyFeatures($property_ID,$LotFeatures);
                    self::addPropertyFeatures($property_ID,$ParkingFeatures);
                    self::addPropertyFeatures($property_ID,$PatioAndPorchFeatures);
                    self::addPropertyFeatures($property_ID,$PoolFeatures);
                    self::addPropertyFeatures($property_ID,$arraywaterfront);

                    $message = 'Added: '.$ListingId.' - '.$type.' - '.$status.' - '.$label.' - '.$country.' - '.$state.' - '.$city.' - '.$area;
                    WP_CLI::log( $message ); 
                    WP_CLI::line( $message ); 
                }
                
            }
            
            $nextPage = $page+1;
            if ( $nextPage <= $totalPages ) {
                //self::update($page+1, $next_property_link );
                self::getPropertyOfMLS($queries, $api_args, $nextPage, $limit_query);
            }
        }

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            WP_CLI::log( esc_html__( $error_message , 'blokhausre-property-up' ), E_USER_WARNING );
        }
    }

    public static function get_post_by_meta( $args = array() ){
       
        // Parse incoming $args into an array and merge it with $defaults - caste to object ##
        $args = ( object )wp_parse_args( $args );
       
        // grab page - polylang will take take or language selection ##
        $args = array(
            'meta_query'        => array(
                array(
                    'key'       => $args->meta_key,
                    'value'     => $args->meta_value
                )
            ),
            'post_type'         => 'propertymls',
            'posts_per_page'    => '1'
        );
       
        // run query ##
        $posts = get_posts( $args );
       
        // check results ##
        if ( !$posts || is_wp_error( $posts ) ) return false;
       
        // test it ##
        #pr( $posts[0] );
       
        // kick back results ##
        return true;
       
    }

    public static function addPropertyFeatures($property_ID,$PropertyFeatures){

        foreach($PropertyFeatures as $value){            
            $feature_term = term_exists( $value, 'propertymls_feature', 0 );
            if ( !$feature_term ) { 
                $feature_term = wp_insert_term( $value, 'propertymls_feature', array( 'parent' => 0 ) ); 
            }

            if ( !is_wp_error($feature_term) && isset( $feature_term[ 'term_taxonomy_id' ] ) ){
                //WP_CLI::log('Adding PropertyFeatures...'.$value);
                wp_set_object_terms( $property_ID, $value, 'propertymls_feature', true );
            }            
        }
    }

    public static function addPropertyTaxonomy($property_ID,$value,$taxonomy){

        if ( isset($value) && $value!='' ) {
            $taxonomy_term = term_exists( $value, $taxonomy, 0 );
            if ( !$taxonomy_term ) { $taxonomy_term = wp_insert_term( $value, $taxonomy ); }

            if ( !is_wp_error($taxonomy_term) && isset( $taxonomy_term[ 'term_taxonomy_id' ] ) ){
                wp_set_object_terms( $property_ID, $value, $taxonomy, false );
            }                    
        }

    }

    public static function get_Area($SubdivisionName){
        $taxonomyName = 'property_area';
        $parent_terms = get_terms( $taxonomyName, array( 'parent' => $number = 69, 'orderby' => 'slug', 'hide_empty' => false ) );   
        $area = '';
        if (!empty($parent_terms)){

            foreach ( $parent_terms as $pterm ) {
                $repeater = 'subdivision';
                $taxonomy_idParent = $pterm->term_id;  
                
                $repeater_value = get_term_meta($taxonomy_idParent, $repeater, true);
                $count = intval($repeater_value);
                if ($repeater_value) {
                    for ($i=0; $i<$count; $i++) {
                        $meta_key = $repeater.'_'.$i.'_'.'subdivision_items';
                        $sub_field_value = get_term_meta($taxonomy_id, $meta_key, true);
                        if ($sub_field_value!='') {
                            if(strpos($SubdivisionName, $sub_field_value) !== false){
                                $area = $pterm->name;
                            }
                        }                        
                    }
                }
                 
                $termchildren = get_term_children( $taxonomy_idParent, $taxonomyName);
                if (!empty($termchildren)) {
                    foreach ( $termchildren as $child ) {
                        $term = get_term_by( 'id', $child, $taxonomyName );
                        $taxonomy_id = $term->term_id;            
                        $repeater_value = get_term_meta($taxonomy_id, $repeater, true);
                        $count = intval($repeater_value);
                        if ($repeater_value) {
                            for ($i=0; $i<$count; $i++) {
                                $meta_key = $repeater.'_'.$i.'_'.'subdivision_items';
                                $sub_field_value = get_term_meta($taxonomy_id, $meta_key, true);
                                if ($sub_field_value!=''){
                                    if(strpos($SubdivisionName, $sub_field_value) !== false){
                                        $area = $term->name;
                                    }
                                }                                
                            }
                        }
                    }
                }
            }
        }
        return $area;
    }

    public static function addImagesToProperty($images,$property_ID,$action){
        if ($images!=null && is_array($images)) {
            $array_attach_id = array();
            $array_url = array();
            for($w = 0; $w <= count($images); $w++){                            
                $key=key($images);
                if (isset($key)) {
                    $values=$images[$key];
                    if ($values!=null) {                                
                        for ($p = 0; $p <= count($values); $p++) { 
                            $k=key($values);
                            if (isset($k)) {
                                $v=$values[$k];
                                if ($v <> ' ' && $k == 'MediaURL') {
                                    $imgUrl = $v; 
                                    if ($action == 'insert' && $w == 0) {
                                        $attach_id = self::get_image_attach_id($imgUrl, $property_ID); 
                                        set_post_thumbnail( $property_ID, $attach_id );
                                    }                                                                           
                                }
                            }                                    
                            next($values);
                        } 
                        array_push($array_url,$imgUrl);
                    }
                 } 
                next($images);                                         
            }
            if ($array_url!=null && $array_url!='') {
                if ($action == 'insert') {
                    add_post_meta( $property_ID,'mls_url_img_list', $array_url );
                }else{
                    update_post_meta( $property_ID,'mls_url_img_list', $array_url );
                }                
            }                       
        }
    }

    public static function get_disclaimer($ListingId,$brkr){
        $disclaimer = 'The MLS data provided for the property above (MLS#'.$ListingId.') is provided courtesy of '.$brkr.'. Source: Southeast Florida MLS. Data updated '.date("m/d/y").'. Juan M. Alvarez above is a licensed Real Estate Broker with Blokhaus Real Estate + Investments as a cooperating broker or cooperating agent, and Juan M. Alvarez may not be the listing agent for this property. Information is deemed reliable but not guaranteed. The listing data on this page comes from the Internet Data Exchange (IDX), a collaboration between the REALTOR® associations in the South Florida Multiple Listing Service (MLS) including The Miami Association of Realtors and the Realtor Association of Greater Ft. Lauderdale. All rights reserved. A property appearing on this website is not necessarily an exclusive listing of Blokhaus Real Estate + Investments, and may be listed with other brokers. An agent of Blokhaus Real Estate + Investments appearing on this website as the contact person for any property is not necessarily the listing agent, however, is acting at a transaction broker. The name of the listing broker appears with every property detail page, shown just after the map. When properties are presented as part of lists, generally the Blokhaus Real Estate + Investments listings for Miami Beach are highlighted in yellow. The information being provided is for consumers&#39; personal, non-commercial use. Federal law prohibits discrimination on the basis of race, color, religion, sex, handicap, familial status or national origin in the sale, rental or financing of housing. Contact Juan M. Alvarez on Blokhaus Real Estate + Investments for more information. Juan is a licensed Real Estate Broker and president of Blokhaus Real Estate + Investments.';
        return $disclaimer;
    }

    public static function get_image_attach_id( $filename, $parent_id ) {
        $image = $filename;
        $get = wp_remote_get( $image );
        $type = wp_remote_retrieve_header( $get, 'content-type' );

        if (!$type)
            return false;

        $mirror = wp_upload_bits( basename( $image ), '', wp_remote_retrieve_body( $get ) );
        $attachment = array(
            'post_title'=> basename( $image ),
            'post_mime_type' => $type
        );
        $attach_id = wp_insert_attachment( $attachment, $mirror['file'], $parent_id );
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        return $attach_id;
    }
        
}
?>