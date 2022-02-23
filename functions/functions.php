<?php 
/**
 * Created by Wuilly Vargas.
 * User: wuilly.vargas22@gmail.com
 */
if( is_admin() ) {
	//require_once( BLOKHAUSRE_PLUGIN_PATH . '/metaboxes/property-metaboxes.php' );
}

/**
 *  ---------------------------------------------------------------------------------------
 *  Widgets
 *  ---------------------------------------------------------------------------------------
 */
//require_once(BLOKHAUSRE_PLUGIN_PATH . '/widgets/about.php' );
//require_once(BLOKHAUSRE_PLUGIN_PATH . '/widgets/code-banner.php' );
//require_once(BLOKHAUSRE_PLUGIN_PATH . '/widgets/mortgage-calculator.php' );
//require_once(BLOKHAUSRE_PLUGIN_PATH . '/widgets/image-banner-300-250.php' );
//require_once(BLOKHAUSRE_PLUGIN_PATH . '/widgets/contact.php' );
//require_once(BLOKHAUSRE_PLUGIN_PATH . '/widgets/properties.php' );
require_once(BLOKHAUSRE_PLUGIN_PATH . '/widgets/featured-properties.php' );
//require_once(BLOKHAUSRE_PLUGIN_PATH . '/widgets/properties-viewed.php' );
//require_once(BLOKHAUSRE_PLUGIN_PATH . '/widgets/property-taxonomies.php' );
//require_once(BLOKHAUSRE_PLUGIN_PATH . '/widgets/latest-posts.php' );
//require_once(BLOKHAUSRE_PLUGIN_PATH . '/widgets/agents-search.php' );
//require_once(BLOKHAUSRE_PLUGIN_PATH . '/widgets/agency-search.php' );
require_once(BLOKHAUSRE_PLUGIN_PATH . '/widgets/advanced-search.php' );

if ( ! function_exists( 'blokhausre_option' ) ) {
	function blokhausre_option( $id, $fallback = false, $param = false ) {
		if ( isset( $_GET['fave_'.$id] ) ) {
			if ( '-1' == $_GET['fave_'.$id] ) {
				return false;
			} else {
				return $_GET['fave_'.$id];
			}
		} else {
			global $blokhausre_options;
			if ( $fallback == false ) $fallback = '';
			$output = ( isset($blokhausre_options[$id]) && $blokhausre_options[$id] !== '' ) ? $blokhausre_options[$id] : $fallback;
			if ( !empty($blokhausre_options[$id]) && $param ) {
				$output = $blokhausre_options[$id][$param];
			}
		}
		return $output;
	}
}

if( !function_exists('blokhausre_metabox_map_type') ) {
    function blokhausre_metabox_map_type() {
        $blokhausre_map_system = blokhausre_option('blokhausre_map_system');

        if($blokhausre_map_system == 'osm' || $blokhausre_map_system == 'mapbox') {
            $map_system = 'osm';
        } elseif($blokhausre_map_system == 'google') {
            $map_system = 'map';
        } else {
            $map_system = 'osm';
        }
        return $map_system;
    }
}

if( !function_exists('blokhausre_map_api_key') ) {

    function blokhausre_map_api_key() {

        $blokhausre_map_system = blokhausre_get_map_system();   
        $mapbox_api_key = blokhausre_option('mapbox_api_key');   
        $googlemap_api_key = blokhausre_option('googlemap_api_key'); 

        if($blokhausre_map_system == 'google') {
            $googlemap_api_key = urlencode( $googlemap_api_key );
            return $googlemap_api_key;

        } elseif($blokhausre_map_system == 'osm') {
            $mapbox_api_key = urlencode( $mapbox_api_key );
            return $mapbox_api_key;
        }
    }
}

if( !function_exists('blokhausre_get_map_system') ) {
    function blokhausre_get_map_system() {
        $blokhausre_map_system = blokhausre_option('blokhausre_map_system');

        if($blokhausre_map_system == 'osm' || $blokhausre_map_system == 'mapbox') {
            $map_system = 'osm';
        } elseif($blokhausre_map_system == 'google' && blokhausre_option('googlemap_api_key') != "") {
            $map_system = 'google';
        } else {
            $map_system = 'osm';
        }
        return $map_system;
    }
}

if ( !function_exists( 'blokhausre_get_agents_array' ) ) {
    
    function blokhausre_get_agents_array() {

        $agents_array = array(
            - 1 => blokhausre_option('cl_none', 'None'),
        );

        $agents_posts = get_posts(
            array(
                'post_type'        => 'blokhausre_agent',
                'posts_per_page'   => - 1,
                'suppress_filters' => false,
            )
        );

        if ( count( $agents_posts ) > 0 ) {
            foreach ( $agents_posts as $agent_post ) {
                $agents_array[ $agent_post->ID ] = $agent_post->post_title;
            }
        }

        return $agents_array;

    }
}

if ( !function_exists( 'blokhausre_get_agency_array' ) ) {
    function blokhausre_get_agency_array() {

        $agency_array = array(
            - 1 => blokhausre_option('cl_none', 'None'),
        );

        $agency_posts = get_posts(
            array(
                'post_type'        => 'blokhausre_agency',
                'posts_per_page'   => - 1,
                'suppress_filters' => false,
            )
        );

        if ( count( $agency_posts ) > 0 ) {
            foreach ( $agency_posts as $agency_post ) {
                $agency_array[ $agency_post->ID ] = $agency_post->post_title;
            }
        }

        return $agency_array;

    }
}

if ( ! function_exists( 'blokhausre_option' ) ) {
	function blokhausre_option( $id, $fallback = false, $param = false ) {
		if ( isset( $_GET['fave_'.$id] ) ) {
			if ( '-1' == $_GET['fave_'.$id] ) {
				return false;
			} else {
				return $_GET['fave_'.$id];
			}
		} else {
			global $blokhausre_options;
			if ( $fallback == false ) $fallback = '';
			$output = ( isset($blokhausre_options[$id]) && $blokhausre_options[$id] !== '' ) ? $blokhausre_options[$id] : $fallback;
			if ( !empty($blokhausre_options[$id]) && $param ) {
				$output = $blokhausre_options[$id][$param];
			}
		}
		return $output;
	}
}

add_filter( 'single_template', 'override_single_template' );
function override_single_template( $single_template ){
    global $post;

    $file = dirname(__FILE__) .'/templates/single-'. $post->post_type .'.php';

    if( file_exists( $file ) ) $single_template = $file;

    return $single_template;
}


function loadPropertymlsByTabs() {


    // Getting data from GridPropertiesMLS function in wv-script.js file
    $status = $_POST['status'];
    $order = $_POST['order'];
    $orderby = $_POST['orderby'];
    $view = $_POST['view'];
    $slug = $_POST['slug'];
    $paged = $_POST['page'];
    $element = $_POST['element'];
    //$featured = $_POST['featured'];
    // var_dump('status: '.$status); 
    // var_dump('order: '.$order);
    // var_dump('orderby: '.$orderby);
    // var_dump('view: '.$view);
    // var_dump('slug: '.$slug);
    // var_dump('page: '.$paged);
    //var_dump('featured: '.$featured);
    /*---------------------------------*/
   
   

    //    $posts = new WP_Query( $argstotal );
    //      $total = $posts->found_posts;
    //         $posts->query_vars['paged'] = $paged;
    //         $posts->query_vars['posts_per_page'] = -1;
    //         $posts->query_vars['post_status'] = array('publish');
    //         $posts->query_vars['post_type'] = 'propertymls';
    //         $posts->query_vars['orderby'] = $orderby;
    //     var_dump('Total Posts: '.$posts); 



 // Code for Featured properties
    if(empty($slug)){
        //var_dump('slug empty');

        $argsFeatured = array(
            'posts_per_page'   => -1,
            'post_type'        => 'propertymls',
            'post_status'      => 'publish',
            // 'meta_key' 		   => 'mls_featured',
            // 'meta_value'       => '1',
            // 'meta_compare'     => '=='
        );
        
    


    if( !empty($status) ){
        $argsFeatured['tax_query'] =  array(
            'relation' => 'AND',
           
            array(
                'taxonomy' => 'propertymls_status',
                'field'    => 'slug',
                'terms'    => $status,
            ),
           
        );           
    }
   
    if(!empty($order)){
        $argsFeatured['meta_query'] =   array(
            'relation' => 'AND',
                    array(
                'key' 	   => 'mls_featured',
               'value'    => '1',
            'compare'     => '='
                    ),
                    array( 
                'key' 	   => 'mls_property_price',
                // 'type' 	   => 'NUMERIC',
                // 'value'    => 0,
                // 'compare'     => '>'
                    ),
        );
        $argsFeatured['order'] = $order;       
    }

    if(!empty($orderby) && $orderby=='Price'){ 
        $argsFeatured['meta_query'] =   array(
            'relation' => 'AND',
                    array(
                'key' 	   => 'mls_featured',
               'value'    => '1',
            'compare'     => '='
                    ),
                    array( 
                'key' 	   => 'mls_property_price',
                'type' 	   => 'NUMERIC',
                'value'    => 0,
                'compare'     => '>'
                    ),
        );
        $argsFeatured['meta_key'] = 'mls_property_price';
        $argsFeatured['orderby'] = 'meta_value_num';       
    }else{
        $argsFeatured['orderby'] = $orderby;   
    }
    
    $querytotal = new WP_Query( $argsFeatured );
    $count = $querytotal->post_count;
    //var_dump('Total first count: '.$count);
    /*---------------------------------*/

    $args = array(
            'post_type'             => 'propertymls',
            'posts_per_page'        => 12,
            'post_status'      => 'publish',
            'paged'                 => $paged,       
        );

    if( !empty($status) ){
        $args['tax_query'] =  array(
                       
            array(
                'taxonomy' => 'propertymls_status',
                'field'    => 'slug',
                'terms'    => $status,
            ),
           
        );           
    }

    if(!empty($order)){
        $argsFeatured['meta_query'] =   array(
            'relation' => 'AND',
                    array(
                'key' 	   => 'mls_featured',
               'value'    => '1',
            'compare'     => '='
                    ),
                    array( 
                'key' 	   => 'mls_property_price',
                // 'type' 	   => 'NUMERIC',
                // 'value'    => 0,
                // 'compare'     => '>'
                    ),
        );
        $args['order'] = $order;       
    }

    if(!empty($orderby) && $orderby=='Price'){
        $args['meta_query'] =   array(
            'relation' => 'AND',
            array(
         'key' 	    => 'mls_featured',
         'value'    => '1',
         'compare'  => '='
            ),
            array( 
         'key' 	   => 'mls_property_price',
         'type'     => 'NUMERIC',
            'value'    => 0,
            'compare'  => '>'
            ),
    );
    $args['meta_key'] = 'mls_property_price';
        $args['orderby'] = 'meta_value_num';       
    }else{
        $args['orderby'] = $orderby;   
    }

    $query = new WP_Query( $args );

    if ($query->have_posts()){ ?>

        <?php  if ($view==2) :  ?>
            <div class="property-listing list-view">
        <?php endif; ?>

        <?php
        $i=0;
        ?>
        <input type="hidden" id="cant_rows" name="cant_rows" value="<?php echo $count; ?>">
        <?php 
        while ( $query->have_posts() ){ 
            $query->the_post();
            $id             = get_the_ID();
            $title          = get_the_title();
            $link           = get_the_permalink();
            $img_url        = get_the_post_thumbnail_url(get_the_ID(),'full');
            $year_built     = get_post_meta( get_the_ID(), 'mls_property_year', true );
            $price          = get_post_meta( get_the_ID(), 'mls_property_price', true );
            $address        = get_post_meta( get_the_ID(), 'mls_property_address', true );
            $beds           = get_post_meta( get_the_ID(), 'mls_property_bedrooms', true );
            $baths          = get_post_meta( get_the_ID(), 'mls_property_bathrooms', true );
            $sqft           = get_post_meta( get_the_ID(), 'mls_property_size', true );            
            ?>
      
            <?php if ($view==2) : ?>
                <div id="<?php echo $id; ?>" class="item-wrap infobox_trigger">
                    <div class="property-item table-list">
                        <div class="table-cell">
                            <div class="figure-block">
                                <figure class="item-thumb">
                                    <a class="hover-effect" href="<?php echo $link; ?>">
                                        <img width="385" height="258" src="<?php echo $img_url; ?>" class="attachment-houzez-property-thumb-imagsize-houzez-property-thumb-image wp-post-image" alt="" loading="lazy">
                                    </a>
                                </figure>
                            </div>
                        </div>
                        <div class="item-body table-cell">
                            <div class="body-left table-cell">
                                <div class="info-row">
                                    <div class="label-wrap hide-on-grid">
                                        <?php
                                            $term_status = wp_get_post_terms( get_the_ID(), 'propertymls_status', array("fields" => "all"));
                                            if( !empty($term_status) ) {
                                                foreach( $term_status as $status ) {
                                                    $status_id = $status->term_id;
                                                    $status_name = $status->name;
                                                    echo '<span class="label-status label-status-'.intval($status_id).' label label-default">'.esc_attr($status_name).'</span>';
                                                }
                                            }
                                        ?>
                                    </div>
                                    <h2 class="property-title">
                                        <a href="<?php echo $link; ?>"><?php echo $title; ?></a>
                                    </h2>
                                    <address class="property-address"><?php echo $address; ?></address>
                                </div>
                                <div class="info-row amenities hide-on-grid">
                                    <p>
                                        <span class="h-beds">Beds: <?php echo $beds; ?></span>
                                        <span class="h-baths">Baths: <?php echo $baths; ?></span>
                                        <span class="h-area">Sq Ft: <?php echo $sqft; ?></span>
                                    </p>
                                </div>
                            </div>
                            <div class="body-right table-cell hidden-gird-cell">
                                <p class="item-price text-right">From <?php echo houzez_get_property_price($price); ?></p>
                                <div class="info-row phone text-right">
                                    <a href="<?php echo $link; ?>" class="btn btn-primary internal-link">
                                        Details <i class="fa fa-angle-right fa-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($view==3) : ?>  

                <?php if ($i%2==0) : ?>
                    <div class="row">
                <?php endif; ?>

                <div class="col-sm-6 col-xs-12 col idxitem grid-two-col item">
                    <div id="<?php echo $id; ?>" class="item-wrap">
                        <div class="property-item item-grid">
                            <div class="figure-block">
                                <figure class="item-thumb">
                                        <div class="label-wrap label-right hide-on-list">
                                        <?php
                                            $term_status = wp_get_post_terms( get_the_ID(), 'propertymls_status', array("fields" => "all"));
                                            if( !empty($term_status) ) {
                                                foreach( $term_status as $status ) {
                                                    $status_id = $status->term_id;
                                                    $status_name = $status->name;
                                                    echo '<span class="label-status label-status-'.intval($status_id).' label label-default">'.esc_attr($status_name).'</span>';
                                                }
                                            }
                                        ?>
                                        </div>
                                        <a class="hover-effect internal-link" href="<?php echo $link; ?>">
                                            <img width="385" height="258" src="<?php echo $img_url; ?>" class="attachment-houzez-property-thumb-imagsize-houzez-property-thumb-image wp-post-image" alt="" loading="lazy">
                                        </a>
                                        <div class="detail">
                                            <ul class="list-inline">
                                                <li class="cap-price"><span class="price-start" style="display: block;"> </span> <?php echo houzez_get_property_price($price); ?></li>
                                            </ul>
                                        </div>
                                </figure>
                            </div>
                            <div class="item-body">
                                <div class="body-left">
                                    <div class="info-row">
                                        <h3 class="property-title">
                                            <a class="internal-link" href="<?php echo $link; ?>"><?php echo $title; ?></a>
                                        </h3>
                                        <address class="property-address"><?php echo $address; ?></address>
                                    </div>
                                    <div class="table-list full-width info-row">
                                        <div class="cell">
                                            <div class="info-row amenities">
                                                <p>
                                                    <span class="h-beds">Beds: <?php echo $beds; ?></span>
                                                    <span class="h-baths">Baths: <?php echo $baths; ?></span>
                                                    <span class="h-area">Sq Ft: <?php echo $sqft; ?></span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="cell">
                                            <div class="phone">
                                                <a href="<?php echo $link; ?>" class="btn btn-primary internal-link">
                                                    Details <i class="fa fa-angle-right fa-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($i!=0 && $i%2==1) : ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>


            <?php if ($view == 4):  ?>

                <?php if ($i%3 == 0): ?>
                    <div class="row">
                <?php endif; ?>

                <div class="col-sm-4 col-xs-12 col idxitem grid-three-col item">
                    <div id="<?php echo $id; ?>" class="item-wrap">
                        <div class="property-item item-grid">
                            <div class="figure-block">
                                <figure class="item-thumb">
                                    <span class="label-featured label label-success">Featured</span>
                                    <div class="label-wrap label-right hide-on-list">
                                        <?php
                                            $term_status = wp_get_post_terms( get_the_ID(), 'propertymls_status', array("fields" => "all"));
                                            if( !empty($term_status) ) {
                                                foreach( $term_status as $status ) {
                                                    $status_id = $status->term_id;
                                                    $status_name = $status->name;
                                                    echo '<span class="label-status label-status-'.intval($status_id).' label label-default">'.esc_attr($status_name).'</span>';
                                                }
                                            }
                                        ?>
                                        <?php
                                            $term_label = wp_get_post_terms( get_the_ID(), 'propertymls_label', array("fields" => "all"));
                                            if( !empty($term_label) ) {
                                                foreach( $term_label as $label ) {
                                                    $label_id = $label->term_id;
                                                    $label_name = $label->name;
                                                    $label_slug = $label->slug;
                                                    echo '<span class="label-status label-status-'.intval($label_id).' label label-'.$label_slug.'">'.esc_attr($label_name).'</span>';
                                                }
                                            }
                                        ?>
                                    </div>
                                    <a class="hover-effect internal-link" href="<?php echo $link; ?>">
                                        <img width="385" height="258" src="<?php echo $img_url; ?>" class="attachment-houzez-property-thumb-image size-houzez-property-thumb-image wp-post-image" alt="" loading="lazy">
                                    </a>
                                    <div class="detail">
                                        <ul class="list-inline">
                                            <li class="cap-price"><span class="price-start" style="display: block;"> </span> <?php echo houzez_get_property_price($price); ?></li>
                                        </ul>
                                    </div>
                                </figure>
                            </div>
                            <div class="item-body">
                                <div class="body-left">
                                    <div class="info-row">
                                        <h3 class="property-title">
                                            <a class="internal-link" href="<?php echo $link; ?>"><?php echo $title; ?></a>
                                        </h3>
                                        <address class="property-address"><?php echo $address; ?></address>
                                    </div>
                                    <div class="table-list full-width info-row">
                                        <div class="cell">
                                            <div class="info-row amenities">
                                                <p>
                                                    <span class="h-beds">Beds: <?php echo $beds; ?></span>
                                                    <span class="h-baths">Baths: <?php echo $baths; ?></span>
                                                    <span class="h-area">Sq Ft: <?php echo $sqft; ?></span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="cell">
                                            <div class="phone">
                                                <a href="<?php echo $link; ?>" class="btn btn-primary internal-link">
                                                    Details <i class="fa fa-angle-right fa-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($i!=0 && $i%3==2): ?>
                    </div>
                <?php endif;?>

            <?php endif; ?>
            <?php 
            $i++;
        }
        ?>

        <?php  if ($view==2) :  ?>
            </div>
        <?php endif; ?>


        <?php 
        wp_reset_postdata();
        ?>
            <?php if($count>=12): ?>
            <div id="mls-pagination" class="paginationbox-neighborhood text-center">
                <?php wv_pagination( $count , $paged); ?>
            </div>
            <?php endif ?>
        <?php
    }else{
        echo '<p>Not Found Featured</p>';
    }               
    
    die(); 

        } //end of if empty slug (For featured properties)

       
      // Code for Condo grid  
    elseif ($element == '#condogrid') {


        $argstotal = array(
            'post_type'             => 'condos',
            'posts_per_page'        => -1,
            'post_status'           => array('publish'),           
        );

    if( !empty($status) ){
        $argstotal['tax_query'] =  array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'condo_area',
                'field'    => 'slug',
                'terms'    => $slug,
            ),
            // array(
            //     'taxonomy' => 'condo_status',
            //     'field'    => 'slug',
            //     'terms'    => $status,
            // ),
            //array(
                //'taxonomy' => 'property_type',
               // 'field'    => 'slug',                
                //'terms'    => 'single-family-residence',
                //'terms'    => 'single-family-homes',

           // ),
        );           
    }else{
        $argstotal['tax_query'] = array(
            array(
                'taxonomy' => 'condo_area',
                'field' => 'slug',
                'terms' => $slug,
            ),
            // array(
            //     'taxonomy' => 'condo_area',
            //     'field'    => 'slug',            
            //     //'terms'    => 'single-family-homes',
            // ),          
        ); 
    }

    // if(!empty($order)){
    //     $argstotal['order'] = $order;       
    // }

    // if(!empty($orderby) && $orderby=='Price'){
    //     $argstotal['meta_key'] = 'mls_property_price';
    //     $argstotal['orderby'] = 'meta_value_num';       
    // }else{
    //     $argstotal['orderby'] = $orderby;   
    // }

    $querytotal = new WP_Query( $argstotal );
    $count = $querytotal->post_count;
    //var_dump('Total first count: '.$count);
    /*---------------------------------*/

    $args = array(
            'post_type'             => 'condos',
            'posts_per_page'        => 12,
            'post_status'           => array('publish'),
            'paged'                 => $paged,            
        );

    if( !empty($status) ){
        $args['tax_query'] =  array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'condo_area',
                'field'    => 'slug',
                'terms'    => $slug,
            ),
            
        );           
    }else{
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'condo_area',
                'field' => 'slug',
                'terms' => $slug
            ),
            
        ); 
    }

    // if(!empty($order)){
    //     $args['order'] = $order;       
    // }

    // if(!empty($orderby) && $orderby=='Price'){
    //     $args['meta_key'] = 'mls_property_price';
    //     $args['orderby'] = 'meta_value_num';       
    // }else{
    //     $args['orderby'] = $orderby;   
    // }

    $query = new WP_Query( $args );
    //$count = $query->post_count;
    //var_dump('Total second count: '.$count);?>


<script>
    function cleanSpace(string){
	    var res = string.replace(/ /g, "-");   
	    return res.toLowerCase();
	}

    function loadCondoGridInfoMLS(BuildingName, element) {
		var page = 1;
		var results_per_page = 200; 
		var page_first_result = (page-1) * results_per_page;
		var prices = [];
		var beds = [];
		var sqft = [];
		var forRent = 0;
		var forSale = 0;
		var html_label = '';
		var total_units = "";

		var _this = $(element);
		
		if (BuildingName.indexOf("Private: ") > 0){
	    	BuildingName = BuildingName.substr(9, BuildingName.length);
	    }
		console.log(BuildingName);
		// var url_endpoint_for_sale = endpoint+"?access_token="+access_token+"&$top=50"+orderby+"&$filter=MlsStatus eq 'Active' "+filterbeds+"and StructureType/all(a: contains(a,'Condo')) and PropertySubType eq 'Condominium' and BuildingName eq '"+apartmentName+"' and PropertyType eq 'Residential'";
		// console.log('Url endpoint for sale button: '+url_endpoint_for_sale);
	
		var url_endpoint = "https://api.bridgedataoutput.com/api/v2/miamire/listings?access_token=103a9efb0c687b2b6af63cc6f3f4177c&offset="+page_first_result+"&limit="+results_per_page+"&UnparsedAddress="+BuildingName+"&StructureType=Condo&PropertySubType=Condominium&MlsStatus=Active&PropertyType=Residential";
		console.log('url_endpoint: '+url_endpoint);
		$.ajax({
	        url: url_endpoint,
	        contentType: "application/json",
	        dataType: 'json',
	        success: function(result){
	        	total_units = result.bundle.length;
	        	console.log('total_units: '+total_units);
	        	if (total_units > 0) {
	        		for(var i = 0; i < total_units; i++) {
	        		
		        		var ListPrice = result.bundle[i].ListPrice;
		        		var SqFt = result.bundle[i].LivingArea;
						var Beds = result.bundle[i].BedroomsTotal;
						var MlsStatus = result.bundle[i].MlsStatus;

						//if (MlsStatus == 'Active') {
							prices.push(ListPrice);
							sqft.push(SqFt);
							beds.push(Beds);
						//}	

						var lb = result.bundle[0].PropertyType;
			            if (lb=='Residential Lease') {
			            	forRent++;
			            }
			            if (lb=='Residential') {
			            	forSale++;
			            }				
						
					}

					var label = result.bundle[0].PropertyType;
		            if (label=='Residential Lease') {
		            	label='For Rent';
		            }
		            if (label=='Residential') {
		            	label='For Sale';
		            }

		            var tag = result.bundle[0].MlsStatus;
		            if(tag=='Closed Sale'){
						tag = 'Sold';
					}else if(tag == 'Active'){
						tag = '';
					}
					
					if (label!='') {
	                	html_label += '<span class="label-status label-'+cleanSpace(label)+' label label-default">'+label+'</span>';
	                }
	                /*if (tag!='') {
	    				html_label += '<span class="label-status label-'+tag+' label label-default">'+tag+'</span>';
	    			}*/

	    			//if(forRent > forSale){
	    				//var numUnits = forRent;
	    			//}else{
	    				var numUnits = forSale;
	    			//}

					// var address = result.bundle[0].UnparsedAddress;
					// var description = result.bundle[0].PublicRemarks;
					// var city = result.bundle[0].City;
					// var state = result.bundle[0].StateOrProvince;
					// var zipcode = result.bundle[0].PostalCode;
					// var county = result.bundle[0].CountyOrParish;
					// var country = result.bundle[0].Country;				
					var total_floors = result.bundle[0].StoriesTotal;
					var YearBuilt = result.bundle[0].YearBuilt;
					var condo_id = result.bundle[0].ListingId;
					

					if (_this.find('span.label-default').length == 0){
					    _this.find(".label-wrap").html(html_label);
					}

					if (_this.find('span.h-year').length == 0){
					    _this.find(".amenities p").append('<span class="h-year">Built in '+YearBuilt+'</span>');
					}

					if (_this.find('span.h-units').length == 0){
					    _this.find(".amenities p").append('<span class="h-units">Units: '+total_units+'</span>');
					}

					if (_this.find('span.h-floors').length == 0){
					    _this.find(".amenities p").append('<span class="h-floors">Floors: '+total_floors+'</span>');
					}

					if (_this.find('span.numunits').length == 0){
					    _this.find(".btn-primary.internal-link").prepend('<span class="numunits">'+numUnits+'</span>');
					}

					console.log("Price: "+ListPrice);

					//if (_this.find('span.numbprice').length == 0){
					   // _this.find("span.numbprice").html(formatCurrency(ListPrice));
					//}
	        	}else{
	        		console.log('No se encontraron Condos con ese nombre');
	        	}
			},
			complete: function () {				
			},
	        beforeSend: function (xhr) {
	        }
	  	})
	};

    if ( $("#condogrid").length ){
		console.log('condogrid encontrado');
		$( "#condogrid .idxitem" ).each(function() {
			var BuildingName = $( this ).find( ".property-address" ).text();
			var trimStr = $.trim(BuildingName);
			console.log('Address: '+trimStr);
			
				loadCondoGridInfoMLS(trimStr, this); 
	
		});
	}

</script>
 <?php  if ($query->have_posts()){ ?>

            <?php  if ($view==2) :  ?>
                <div class="property-listing list-view">
            <?php endif; ?>

            <?php
            $i=0;
            while ( $query->have_posts() ){ 
                $query->the_post();
                $id = get_the_ID();
                $title = get_the_title();
                $link = get_the_permalink();
                $img_url = get_the_post_thumbnail_url(get_the_ID(),'full');
                $year_built   = get_post_meta( get_the_ID(), 'year_built', true );
                $price = get_post_meta( get_the_ID(), 'price_from', true );
                $address = get_post_meta( get_the_ID(), 'short_address', true );
                $units = get_post_meta( get_the_ID(), 'units', true );
                $floors = get_post_meta( get_the_ID(), 'floors', true );
                $units_for_sale = get_post_meta( get_the_ID(), 'units_for_sale', true );
                $units_for_rent = get_post_meta( get_the_ID(), 'units_for_rent', true );
                ?>


                <?php if ($view==2) : ?>
                    <div id="<?php echo $id; ?>" class="item-wrap infobox_trigger">
                        <div class="property-item table-list">
                            <div class="table-cell">
                                <div class="figure-block">
                                    <figure class="item-thumb">
                                        <a class="hover-effect" href="<?php echo $link; ?>">
                                            <img width="385" height="258" src="<?php echo $img_url; ?>" class="attachment-houzez-property-thumb-imagsize-houzez-property-thumb-image wp-post-image" alt="" loading="lazy">
                                        </a>
                                    </figure>
                                </div>
                            </div>
                            <div class="item-body table-cell">
                                <div class="body-left table-cell">
                                    <div class="info-row">
                                        <div class="label-wrap hide-on-grid">
                                            <?php
                                            $term_status = wp_get_post_terms( get_the_ID(), 'condo_status', array("fields" => "all"));

                                            if( !empty($term_status) ) {
                                                foreach( $term_status as $status ) {
                                                    $status_id = $status->term_id;
                                                    $status_name = $status->name;
                                                    echo '<span class="label-status label-status-'.intval($status_id).' label label-default">'.esc_attr($status_name).'</span>';
                                                }
                                            }
                                            if ($status_name=='For Sale') {
                                                $numunits = $units_for_sale;
                                            }else if ($status_name=='For Rent') {
                                                $numunits = $units_for_rent;
                                            }else{
                                                $numunits = '';
                                                $status_name = 'Details';
                                            }

                                            ?>
                                        </div>
                                        <h2 class="property-title">
                                            <a href="<?php echo $link; ?>"><?php echo $title; ?></a>
                                        </h2>
                                        <address class="property-address"><?php echo $address; ?></address>
                                    </div>
                                    <div class="info-row amenities hide-on-grid">
                                        <p>
                                            <span class="h-beds">Built in <?php echo $year_built; ?></span>
                                            <span class="h-beds">Units: <?php echo $units; ?></span>
                                            <span class="h-beds">Floors: <?php echo $floors; ?></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="body-right table-cell hidden-gird-cell">
                                    <p class="item-price text-right">From <?php echo houzez_get_property_price($price); ?></p>
                                    <div class="info-row phone text-right">
                                        <a href="<?php echo $link; ?>" class="btn btn-primary internal-link"><?php echo $status_name; ?> <i class="fa fa-angle-right fa-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($view==3) : ?>  

                    <?php if ($i%2==0) : ?>
                        <div class="row">
                    <?php endif; ?>

                    <div class="col-sm-6 col-xs-12 col idxitem grid-two-col item">
                        <div id="<?php echo $id; ?>" class="item-wrap">
                            <div class="property-item item-grid">
                                <div class="figure-block">
                                    <figure class="item-thumb">
                                            <div class="label-wrap label-right hide-on-list">
                                            <?php
                                            $term_status = wp_get_post_terms( get_the_ID(), 'condo_status', array("fields" => "all"));

                                            if( !empty($term_status) ) {
                                                foreach( $term_status as $status ) {
                                                    $status_id = $status->term_id;
                                                    $status_name = $status->name;
                                                    echo '<span class="label-status label-status-'.intval($status_id).' label label-default">'.esc_attr($status_name).'</span>';
                                                }
                                            }
                                            if ($status_name=='For Sale') {
                                                $numunits = $units_for_sale;
                                            }else if ($status_name=='For Rent') {
                                                $numunits = $units_for_rent;
                                            }else{
                                                $numunits = '';
                                                $status_name = 'Details';
                                            }
                                            ?>
                                            </div>
                                            <a class="hover-effect internal-link" href="<?php echo $link; ?>">
                                                <img width="385" height="258" src="<?php echo $img_url; ?>" class="attachment-houzez-property-thumb-imagsize-houzez-property-thumb-image wp-post-image" alt="" loading="lazy">
                                            </a>
                                            <div class="detail">
                                                <ul class="list-inline">
                                                    <li class="cap-price"><span class="price-start" style="display: block;">From </span> <?php echo houzez_get_property_price($price); ?></li>
                                                </ul>
                                            </div>
                                    </figure>
                                </div>
                                <div class="item-body">
                                    <div class="body-left">
                                        <div class="info-row">
                                            <h3 class="property-title">
                                                <a class="internal-link" href="<?php echo $link; ?>"><?php echo $title; ?></a>
                                            </h3>
                                            <address class="property-address"><?php echo $address; ?></address>
                                        </div>
                                        <div class="table-list full-width info-row">
                                            <div class="cell">
                                                <div class="info-row amenities">
                                                    <p>
                                                        
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="cell">
                                                <div class="phone">
                                                    <a href="<?php echo $link; ?>" class="btn btn-primary internal-link"> <?php echo $status_name; ?> <i class="ffa-angle-right fa-right"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($i!=0 && $i%2==1) : ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>


                <?php if ($view == 4): ?>

                    <?php if ($i%3 == 0): ?>
                        <div class="row">
                    <?php endif; ?>

                    <div class="col-sm-4 col-xs-12 col idxitem grid-three-col item condo-tab">
                        <div id="<?php echo $id; ?>" class="item-wrap">
                            <div class="property-item item-grid">
                                <div class="figure-block">
                                    <figure class="item-thumb">
                                        <div class="label-wrap label-right hide-on-list">
                                            <?php
                                            $term_status = wp_get_post_terms( get_the_ID(), 'condo_status', array("fields" => "all"));

                                            if( !empty($term_status) ) {
                                                foreach( $term_status as $status ) {
                                                    $status_id = $status->term_id;
                                                    $status_name = $status->name;
                                                    echo '<span class="label-status label-status-'.intval($status_id).' label label-default">'.esc_attr($status_name).'</span>';
                                                }
                                            }
                                            if ($status_name=='For Sale') {
                                                $numunits = $units_for_sale;
                                            }else if ($status_name=='For Rent') {
                                                $numunits = $units_for_rent;
                                            }else{
                                                $numunits = '';
                                                $status_name = 'Details';
                                            }
                                            ?>
                                        </div>
                                        <a class="hover-effect internal-link" href="<?php echo $link; ?>">
                                            <img width="385" height="258" src="<?php echo $img_url; ?>" class="attachment-houzez-property-thumb-image size-houzez-property-thumb-image wp-post-image" alt="" loading="lazy">
                                        </a>
                                        <div class="detail">
                                            <ul class="list-inline">
                                                <li class="cap-price"><span class="price-start" style="display: block;">From </span> <?php echo houzez_get_property_price($price); ?></li>
                                            </ul>
                                        </div>
                                    </figure>
                                </div>
                                <div class="item-body">
                                    <div class="body-left">
                                        <div class="info-row">
                                            <h3 class="property-title">
                                                <a class="internal-link" href="<?php echo $link; ?>"><?php echo $title; ?></a>
                                            </h3>
                                            <address class="property-address"><?php echo $address; ?></address>
                                        </div>
                                        <div class="table-list full-width info-row">
                                            <div class="cell">
                                                <div class="info-row amenities">
                                                    <p>
                                                    
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="cell">
                                                <div class="phone">
                                                    <a href="<?php echo $link; ?>" class="btn btn-primary internal-link"> <?php echo $status_name; ?> <i class="fa fa-angle-right fa-right"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($i!=0 && $i%3==2): ?>
                        </div>
                    <?php endif;?>

                <?php endif; ?>
                <?php 
                $i++;
            } // end of while
            ?>

            <?php  if ($view==2) :  ?>
                </div>
            <?php endif; ?>


            <?php 
            wp_reset_postdata();

            if($i>=12): //var_dump('$count: '.$count)?>
                <div id="mls-pagination-condo" class="paginationbox-neighborhood text-center">
                    <?php wv_pagination( $count , $paged); ?>
                <!--start Pagination--> 
            </div>
            <?php endif; 
            } // end of if
            else{
            echo '<p>Not Found</p>';
            }
                                    											
                                      
                     
    die(); 

    } //end of Condo grid

     else {
        // code for other single family homes

        $argstotal = array(
            'post_type'             => 'propertymls',
            'posts_per_page'        => -1,
            'post_status'           => array('publish'),           
        );

    if( !empty($status) ){
        $argstotal['tax_query'] =  array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'propertymls_area',
                'field'    => 'slug',
                'terms'    => $slug,
            ),
            array(
                'taxonomy' => 'propertymls_status',
                'field'    => 'slug',
                'terms'    => $status,
            ),
            array(
                'taxonomy' => 'propertymls_type',
                'field'    => 'slug',                
                // 'terms'    => 'single-family-residence',
                'terms'    => 'single-family-homes',

            ),
        );           
    }else{
        $argstotal['tax_query'] = array(
            array(
                'taxonomy' => 'propertymls_area',
                'field' => 'slug',
                'terms' => $slug
            ),
            array(
                'taxonomy' => 'propertymls_type',
                'field'    => 'slug',
                //'terms'    => 'single-family-residence',
                'terms'    => 'single-family-homes',
            ),          
        ); 
    }

    if(!empty($order)){
        $argstotal['order'] = $order;       
    }

    if(!empty($orderby) && $orderby=='Price'){
        $argstotal['meta_key'] = 'mls_property_price';
        $argstotal['orderby'] = 'meta_value_num';       
    }else{
        $argstotal['orderby'] = $orderby;   
    }

    $querytotal = new WP_Query( $argstotal );
    $count = $querytotal->post_count;
    //var_dump('Total first count: '.$count);
    /*---------------------------------*/

    $args = array(
            'post_type'             => 'propertymls',
            'posts_per_page'        => 12,
            'post_status'           => array('publish'),
            'paged'                 => $paged,            
        );

    if( !empty($status) ){
        $args['tax_query'] =  array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'propertymls_area',
                'field'    => 'slug',
                'terms'    => $slug,
            ),
            array(
                'taxonomy' => 'propertymls_status',
                'field'    => 'slug',
                'terms'    => $status,
            ),
            array(
                'taxonomy' => 'propertymls_type',
                'field'    => 'slug',
                //'terms'    => 'single-family-residence',
                'terms'    => 'single-family-homes',
            ), 
        );           
    }else{
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'propertymls_area',
                'field' => 'slug',
                'terms' => $slug
            ),
            array(
                'taxonomy' => 'propertymls_type',
                'field'    => 'slug',
                // 'terms'    => 'single-family-residence',
                'terms'    => 'single-family-homes',
            ),           
        ); 
    }

    if(!empty($order)){
        $args['order'] = $order;       
    }

    if(!empty($orderby) && $orderby=='Price'){
        $args['meta_key'] = 'mls_property_price';
        $args['orderby'] = 'meta_value_num';       
    }else{
        $args['orderby'] = $orderby;   
    }

    $query = new WP_Query( $args );
    //$count_two = $query->post_count;
    //var_dump('Total second count: '.$count_two);
    if ($query->have_posts()){ ?>
        <?php  if ($view==2) :  ?>
            <div class="property-listing list-view">
        <?php endif; ?>

        <?php
        $i=0;
        ?>
        <input type="hidden" id="cant_rows" name="cant_rows" value="<?php echo $count; ?>">
        <?php 
        while ( $query->have_posts() ){ 
            $query->the_post();
            $id             = get_the_ID();
            $title          = get_the_title();
            $link           = get_the_permalink();
            $img_url        = get_the_post_thumbnail_url(get_the_ID(),'full');
            $year_built     = get_post_meta( get_the_ID(), 'mls_property_year', true );
            $price          = get_post_meta( get_the_ID(), 'mls_property_price', true );
            $address        = get_post_meta( get_the_ID(), 'mls_property_address', true );
            $beds           = get_post_meta( get_the_ID(), 'mls_property_bedrooms', true );
            $baths          = get_post_meta( get_the_ID(), 'mls_property_bathrooms', true );
            $sqft           = get_post_meta( get_the_ID(), 'mls_property_size', true );
            ?>

            <?php if ($view==2) : ?>
                <div id="<?php echo $id; ?>" class="item-wrap infobox_trigger">
                    <div class="property-item table-list">
                        <div class="table-cell">
                            <div class="figure-block">
                                <figure class="item-thumb">
                                    <a class="hover-effect" href="<?php echo $link; ?>">
                                        <img width="385" height="258" src="<?php echo $img_url; ?>" class="attachment-houzez-property-thumb-imagsize-houzez-property-thumb-image wp-post-image" alt="" loading="lazy">
                                    </a>
                                </figure>
                            </div>
                        </div>
                        <div class="item-body table-cell">
                            <div class="body-left table-cell">
                                <div class="info-row">
                                    <div class="label-wrap hide-on-grid">
                                        <?php
                                            $term_status = wp_get_post_terms( get_the_ID(), 'propertymls_status', array("fields" => "all"));
                                            if( !empty($term_status) ) {
                                                foreach( $term_status as $status ) {
                                                    $status_id = $status->term_id;
                                                    $status_name = $status->name;
                                                    echo '<span class="label-status label-status-'.intval($status_id).' label label-default">'.esc_attr($status_name).'</span>';
                                                }
                                            }
                                        ?>
                                    </div>
                                    <h2 class="property-title">
                                        <a href="<?php echo $link; ?>"><?php echo $title; ?></a>
                                    </h2>
                                    <address class="property-address"><?php echo $address; ?></address>
                                </div>
                                <div class="info-row amenities hide-on-grid">
                                    <p>
                                        <span class="h-beds">Beds: <?php echo $beds; ?></span>
                                        <span class="h-baths">Baths: <?php echo $baths; ?></span>
                                        <span class="h-area">Sq Ft: <?php echo $sqft; ?></span>
                                    </p>
                                </div>
                            </div>
                            <div class="body-right table-cell hidden-gird-cell">
                                <p class="item-price text-right">From <?php echo houzez_get_property_price($price); ?></p>
                                <div class="info-row phone text-right">
                                    <a href="<?php echo $link; ?>" class="btn btn-primary internal-link">
                                        Details <i class="fa fa-angle-right fa-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($view==3) : ?>  

                <?php if ($i%2==0) : ?>
                    <div class="row">
                <?php endif; ?>

                <div class="col-sm-6 col-xs-12 col idxitem grid-two-col item">
                    <div id="<?php echo $id; ?>" class="item-wrap">
                        <div class="property-item item-grid">
                            <div class="figure-block">
                                <figure class="item-thumb">
                                        <div class="label-wrap label-right hide-on-list">
                                        <?php
                                            $term_status = wp_get_post_terms( get_the_ID(), 'propertymls_status', array("fields" => "all"));
                                            if( !empty($term_status) ) {
                                                foreach( $term_status as $status ) {
                                                    $status_id = $status->term_id;
                                                    $status_name = $status->name;
                                                    echo '<span class="label-status label-status-'.intval($status_id).' label label-default">'.esc_attr($status_name).'</span>';
                                                }
                                            }
                                        ?>
                                        </div>
                                        <a class="hover-effect internal-link" href="<?php echo $link; ?>">
                                            <img width="385" height="258" src="<?php echo $img_url; ?>" class="attachment-houzez-property-thumb-imagsize-houzez-property-thumb-image wp-post-image" alt="" loading="lazy">
                                        </a>
                                        <div class="detail">
                                            <ul class="list-inline">
                                                <li class="cap-price"><span class="price-start" style="display: block;"> </span> <?php echo houzez_get_property_price($price); ?></li>
                                            </ul>
                                        </div>
                                </figure>
                            </div>
                            <div class="item-body">
                                <div class="body-left">
                                    <div class="info-row">
                                        <h3 class="property-title">
                                            <a class="internal-link" href="<?php echo $link; ?>"><?php echo $title; ?></a>
                                        </h3>
                                        <address class="property-address"><?php echo $address; ?></address>
                                    </div>
                                    <div class="table-list full-width info-row">
                                        <div class="cell">
                                            <div class="info-row amenities">
                                                <p>
                                                    <span class="h-beds">Beds: <?php echo $beds; ?></span>
                                                    <span class="h-baths">Baths: <?php echo $baths; ?></span>
                                                    <span class="h-area">Sq Ft: <?php echo $sqft; ?></span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="cell">
                                            <div class="phone">
                                                <a href="<?php echo $link; ?>" class="btn btn-primary internal-link">
                                                    Details <i class="fa fa-angle-right fa-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($i!=0 && $i%2==1) : ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>


            <?php if ($view == 4): ?>

                <?php if ($i%3 == 0): ?>
                    <div class="row">
                <?php endif; ?>

                <div class="col-sm-4 col-xs-12 col idxitem grid-three-col item">
                    <div id="<?php echo $id; ?>" class="item-wrap">
                        <div class="property-item item-grid">
                            <div class="figure-block">
                                <figure class="item-thumb">
                                    <div class="label-wrap label-right hide-on-list">
                                        <?php
                                            $term_status = wp_get_post_terms( get_the_ID(), 'propertymls_status', array("fields" => "all"));
                                            if( !empty($term_status) ) {
                                                foreach( $term_status as $status ) {
                                                    $status_id = $status->term_id;
                                                    $status_name = $status->name;
                                                    echo '<span class="label-status label-status-'.intval($status_id).' label label-default">'.esc_attr($status_name).'</span>';
                                                }
                                            }
                                        ?>
                                    </div>
                                    <a class="hover-effect internal-link" href="<?php echo $link; ?>">
                                        <img width="385" height="258" src="<?php echo $img_url; ?>" class="attachment-houzez-property-thumb-image size-houzez-property-thumb-image wp-post-image" alt="" loading="lazy">
                                    </a>
                                    <div class="detail">
                                        <ul class="list-inline">
                                            <li class="cap-price"><span class="price-start" style="display: block;"> </span> <?php echo houzez_get_property_price($price); ?></li>
                                        </ul>
                                    </div>
                                </figure>
                            </div>
                            <div class="item-body">
                                <div class="body-left">
                                    <div class="info-row">
                                        <h3 class="property-title">
                                            <a class="internal-link" href="<?php echo $link; ?>"><?php echo $title; ?></a>
                                        </h3>
                                        <address class="property-address"><?php echo $address; ?></address>
                                    </div>
                                    <div class="table-list full-width info-row">
                                        <div class="cell">
                                            <div class="info-row amenities">
                                                <p>
                                                    <span class="h-beds">Beds: <?php echo $beds; ?></span>
                                                    <span class="h-baths">Baths: <?php echo $baths; ?></span>
                                                    <span class="h-area">Sq Ft: <?php echo $sqft; ?></span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="cell">
                                            <div class="phone">
                                                <a href="<?php echo $link; ?>" class="btn btn-primary internal-link">
                                                    Details <i class="fa fa-angle-right fa-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($i!=0 && $i%3==2): ?>
                    </div>
                <?php endif;?>

            <?php endif; ?>
            <?php 
            $i++;
        } //end of while
        ?>

        <?php  if ($view==2) :  ?>
            </div>
        <?php endif; ?>


        <?php 
        wp_reset_postdata();
        ?>
            <?php if($count>=12): ?>
            <div id="mls-pagination" class="paginationbox-neighborhood text-center">
                <?php wv_pagination( $count , $paged); ?>
            </div>
            <?php endif ?>
        <?php
    } //end of if
    else{
        echo '<p>Not Found Single Family</p>';
    }               

    die();  

    }


}  // end of loadPropertymlsByTabs function

add_action( 'wp_ajax_loadPropertymlsByTabs', 'loadPropertymlsByTabs' );
add_action( 'wp_ajax_nopriv_loadPropertymlsByTabs', 'loadPropertymlsByTabs' );


/*-----------------------------------------------------------------------------------*/
/*  Blokhausre Print Property MLS
/*-----------------------------------------------------------------------------------*/
add_action( 'wp_ajax_nopriv_blokhausre_print_propertymls', 'blokhausre_print_propertymls' );
add_action( 'wp_ajax_blokhausre_print_propertymls', 'blokhausre_print_propertymls' );

if( !function_exists('blokhausre_print_propertymls')) {
    function blokhausre_print_propertymls () {

        if(!isset($_POST['propid'])|| !is_numeric($_POST['propid'])){
            exit();
        }

        $propertyID = intval($_POST['propid']);
        $the_post= get_post( $propertyID );

        if( $the_post->post_type != 'propertymls'  ) {
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

        $print_logo         = houzez_option( 'print_page_logo', false, 'url' );

        $image_id           = get_post_thumbnail_id( $propertyID );
        $full_img           = wp_get_attachment_image_src($image_id, 'houzez-single-big-size');
        $full_img           = $full_img [0];

        $title              = get_the_title( $propertyID );
        $prop_excerpt       = $the_post->post_content;

        $property_status    = houzez_taxonomy_simple_2('propertymls_status', $propertyID);
        $property_type      = houzez_taxonomy_simple_2('propertymls_type', $propertyID);

        $prop_images          = get_post_meta( $propertyID, 'mls_url_img_list', false );
        $prop_address         = $_POST['address'];

        $prop_id = get_post_meta( $propertyID, 'mls_property_id', true );
        $prop_price = $_POST['price'];

        if(!isset($_POST['description'])){
            $property_description = $_POST['description'];
            var_dump('!isset: '.$property_description);
        }else{
            $property_description = get_the_content( $propertyID );
            var_dump('else: '.$property_description);
        }
        $prop_size = get_post_meta( $propertyID, 'mls_property_size', true );
        $land_area = get_post_meta( $propertyID, 'mls_property_land', true );
        $bedrooms = get_post_meta( $propertyID, 'mls_property_bedrooms', true );
        $bathrooms = get_post_meta( $propertyID, 'mls_property_bathrooms', true );
        $year_built = get_post_meta( $propertyID, 'mls_property_year', true );
        $garage = get_post_meta( $propertyID, 'mls_property_garage', true );
        $garage_size = get_post_meta( $propertyID, 'mls_property_garage_size', true );
        $prop_floor_plan      = get_post_meta( $propertyID, 'mls_floor_plans_enable', true );
        $floor_plans = get_field('floor_plans',$propertyID);
        $additional_features_enable = get_post_meta( $propertyID, 'mls_additional_features_enable', true );
        $additional_features = get_post_meta( $propertyID, 'additional_features', true );
        $price          = get_post_meta( $propertyID, 'mls_property_price', true );

        $agent_display_option = get_post_meta( $propertyID, 'fave_agent_display_option', true );
        $prop_agent_display = get_post_meta( $propertyID, 'fave_agents', true );
        $prop_agent_num = $agent_num_call = $prop_agent_email = '';

        if( $prop_agent_display != '-1' && $agent_display_option == 'agent_info' ) {
            $prop_agent_id = get_post_meta( $propertyID, 'fave_agents', true );
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
            $author_id = get_post_field ('post_author', $propertyID);
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

        $print_agent = houzez_option('print_agent');
        $print_description = houzez_option('print_description');
        $print_details = 1;
        $print_details_additional = houzez_option('print_details_additional');
        $print_features = houzez_option('print_features');
        $print_floorplans = houzez_option('print_floorplans');
        $print_gallery = houzez_option('print_gallery');
        $print_gr_code = houzez_option('print_gr_code');
        ?>

        <section id="section-body">
            <!--start detail content-->
            <section class="section-detail-content">
                <div class="detail-bar print-detail">
                    <div class="detail-block">
                        <div class="print-header">
                            <div class="print-header-left">
                                <a href="#" class="print-logo">
                                    <img src="<?php echo esc_url($print_logo); ?>" alt="logo" style="width: 25%;">
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
                                <?php echo $prop_price; ?>
                            </div>
                        </div>
                        <div class="print-banner">
                            <div class="print-main-image">
                                <?php if( !empty($full_img) ) { ?>
                                    <img src="<?php echo esc_url( $full_img ); ?>" alt="<?php echo esc_attr($title); ?>">
                                    
                                    <?php if($print_gr_code != 0) { ?>
                                    <img class="qr-image" src="https://chart.googleapis.com/chart?chs=105x104&cht=qr&chl=<?php echo esc_url( get_permalink($propertyID) ); ?>&choe=UTF-8" title="<?php echo esc_attr($title); ?>" />
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
                            <p><?php echo $property_description; ?></p>
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
                                        echo '<li><strong>'.esc_html__( 'Property ID:', 'houzez').'</strong> '.$prop_id.'</li>';
                                    }
                                    if( !empty( $prop_price ) ) {
                                        echo '<li><strong>'.esc_html__( 'Price:', 'houzez'). '</strong> '.$price.'</li>';
                                    }
                                    if( !empty( $prop_size ) ) {
                                        echo '<li><strong>'.esc_html__( 'Property Size:', 'houzez'). '</strong> '.$prop_size.'</li>';
                                    }
                                    if( !empty( $land_area ) ) {
                                        echo '<li><strong>'.esc_html__( 'Land Area:', 'houzez'). '</strong> '.$land_area.'</li>';
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
                                                $data_value = get_post_meta( $propertyID, 'mls_'.$value->field_id, true );
                                                $field_title = $value->label;
                                                
                                                $field_title = houzez_wpml_translate_single_string($field_title);

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
                                $prop_features = wp_get_post_terms( $propertyID, 'property_feature', array("fields" => "all"));
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

                        <?php if ($floor_plans) :?>
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
                                                <img src="<?php echo esc_url( $plan['fave_plan_image']['url'] ); ?>" alt="img" width="400" height="436">
                                            <?php } ?>

                                            <?php if( !empty( $plan['fave_plan_description'] ) ) { ?>
                                                <p><?php echo esc_attr( $plan['fave_plan_description'] ); ?></p>
                                            <?php } ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif ?>


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

add_filter( 'use_widgets_block_editor', '__return_false' );


function wpa_show_permalinks( $post_link, $post ){
    if ( is_object( $post ) && $post->post_type == 'propertymls' ){
        $type_terms = wp_get_object_terms( $post->ID, 'propertymls_type' );
        $city_terms = wp_get_object_terms( $post->ID, 'propertymls_city' );
        $area_terms = wp_get_object_terms( $post->ID, 'propertymls_area' );
        $listingId  = get_post_meta( $post->ID, 'mls_property_id');

        $type_slug = $type_terms[0]->slug;
        $city_slug = $city_terms[0]->slug;
        $area_slug = $area_terms[0]->slug;

        if ( isset($type_slug) && isset($city_slug) && isset($area_slug)) {
            $new_url = $type_slug.'/'.$city_slug.'/'.$area_slug; 
        }

        if ( isset($type_slug) && isset($city_slug) && !isset($area_slug) ) {
            $new_url = $type_slug.'/'.$city_slug; 
        }

        if ( isset($type_slug) && !isset($city_slug) && isset($area_slug) ) {
            $new_url = $type_slug.'/'.$area_slug;  
        }

        if ( !isset($type_slug) && isset($city_slug) && isset($area_slug) ) {
            $new_url = $city_terms[0]->slug.'/'.$area_terms[0]->slug;
        }

        if ( isset($new_url) ) {
            return str_replace( 'propertymls' , $new_url , $post_link );
        }else{
            return $post_link;
        }
    }
    return $post_link;
}
//add_filter( 'post_type_link', 'wpa_show_permalinks', 1, 2 );

function add_new_zone_search_results_widgets() {

    register_sidebar(array(
        'name' => esc_html__('Search Results From MLSr', 'blokhausre'),
        'id' => 'search-result-mls',
        'description' => esc_html__('Widgets in this area will be shown in search result page.', 'blokhausre'),
        'before_widget' => '<div id="%1$s" class="widget widget-wrap %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<div class="widget-header"><h3 class="widget-title">',
        'after_title' => '</h3></div>',
    ));
    
}
add_action( 'widgets_init', 'add_new_zone_search_results_widgets' );


add_action( 'wp_ajax_nopriv_blokhausre_get_auto_complete_search', 'blokhausre_get_auto_complete_search' );
add_action( 'wp_ajax_blokhausre_get_auto_complete_search', 'blokhausre_get_auto_complete_search' );

if ( !function_exists( 'blokhausre_get_auto_complete_search' ) ) {

    function blokhausre_get_auto_complete_search() {

        global $wpdb;
        $key = $_POST['key'];
        $keyword_field = houzez_option('keyword_field');
        $blokhausre_local = houzez_get_localization();
        $response = '';

        if( $keyword_field != 'prop_city_state_county' ) {

            if ( $keyword_field == "prop_title" ) {

                $table = $wpdb->posts;
                $data = $wpdb->get_results( "SELECT DISTINCT * FROM $table WHERE (post_type='propertymls' or post_type='property') and post_status='publish' and (post_title LIKE '%$key%' OR post_content LIKE '%$key%')" );

                if ( sizeof( $data ) != 0 ) {

                    $search_url = add_query_arg( 'keywordsearch', $key, blokhausre_get_search_template_link() );

                    echo '<ul>';

                    foreach ( $data as $post ) {

                        $propID = $post->ID;
                        // echo $prop_thumb = get_the_post_thumbnail( $propID );
                        if ( get_post_type( $propID ) == 'propertymls' ){
                            $prop_beds  = get_post_meta( $propID, 'mls_property_bedrooms', true );
                            $prop_baths = get_post_meta( $propID, 'mls_property_bathrooms', true );
                            $prop_size  = get_post_meta( $propID, 'mls_property_size', true );
                            $prop_type  = blokhausre_taxonomy_simple('propertymls_type',$propID);
                        }else if ( get_post_type( $propID ) == 'property' ){
                            $prop_beds  = get_post_meta( $propID, 'fave_property_bedrooms', true );
                            $prop_baths = get_post_meta( $propID, 'fave_property_bathrooms', true );
                            $prop_size  = get_post_meta( $propID, 'fave_property_size', true );
                            $prop_type  = blokhausre_taxonomy_simple('property_type',$propID);
                        }
                        

                        $prop_img   = get_the_post_thumbnail_url( $propID, array ( 40, 40 ) );

                        if ( empty( $prop_img ) ) {
                            $prop_img = 'http://placehold.it/40x40';
                        }

                        ?>
                        <li class="media" data-text="<?php echo $post->post_title; ?>">
                            <div class="media-left">
                                <a href="<?php the_permalink( $propID ); ?>" class="media-object"><img src="<?php echo $prop_img; ?>" width="40" height="40"></a>
                            </div>
                            <div class="media-body">
                                <h4 class="media-heading"> <?php echo $post->post_title; ?> </h4>
                                <ul class="amenities">
                                    <?php if ( !empty( $prop_beds ) ) : ?>
                                        <li> <?php echo $blokhausre_local['beds']; ?>: <?php echo $prop_beds; ?></li>
                                    <?php endif; ?>
                                    <?php if ( !empty( $prop_baths ) ) : ?>
                                        <li><?php echo $blokhausre_local['baths']; ?>: <?php echo $prop_baths; ?></li>
                                    <?php endif; ?>
                                    <?php if ( !empty( $prop_size ) ) : ?>
                                        <li> <?php echo 'Sq Ft: ' . $prop_size; ?></li>
                                    <?php endif; ?>
                                    <?php if ( !empty( $prop_type ) ) : ?>
                                        <li> <?php echo $prop_type; ?> </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </li>
                        <?php

                    }

                    echo '</ul>';

                    echo '<div class="search-footer">';
                        echo '<span class="search-count"> ' . sizeof( $data ) . ' '.$blokhausre_local['listins_found'].' </span>';
                        echo '<a target="_blank" href="' . $search_url . '" class="search-result-view"> '.$blokhausre_local['view_all_results'].' </a>';
                    echo '</div>';

                } else {

               ?>
               <div class="result">
                   <p> <?php echo $blokhausre_local['auto_result_not_found']; ?> </p>
               </div>
               <?php

                }

            } else if ( $keyword_field == "prop_address" ) {

                $posts_table = $wpdb->posts;
                $postmeta_table = $wpdb->postmeta;
                $data = $wpdb->get_results( "SELECT DISTINCT meta.meta_value FROM $postmeta_table AS meta INNER JOIN $posts_table AS post ON meta.post_id=post.ID AND post.post_type='propertymls' and post.post_status='publish' AND meta.meta_value LIKE '%$key%'AND ( meta.meta_key='mls_property_location' OR meta.meta_key='mls_property_zip' OR meta.meta_key='mls_property_address' OR meta.meta_key='mls_property_id' )" );

                if ( sizeof( $data ) != 0 ) {

                    echo '<ul>';

                    foreach ( $data as $title ) {

                        ?>
                        <li class="media" data-text="<?php echo $title->meta_value; ?>">
                            <div class="media-body">
                                <h4 class="media-heading"> <?php echo $title->meta_value; ?> </h4>
                            </div>
                        </li>
                        <?php

                    }

                    echo '</ul>';

                } else {

               ?>
               <div class="result">
                   <p> <?php echo $blokhausre_local['auto_result_not_found']; ?> </p>
               </div>
               <?php

           }

            }

        } else {

            $terms_table = $wpdb->terms;
            $term_taxonomy = $wpdb->term_taxonomy;
            $data = $wpdb->get_results( "SELECT DISTINCT * FROM $terms_table as term INNER JOIN $term_taxonomy AS term_taxonomy
                ON term.term_id=term_taxonomy.term_id AND term.name LIKE '%$key%' AND ( term_taxonomy.taxonomy = 'propertymls_area' OR term_taxonomy.taxonomy = 'propertymls_city' OR term_taxonomy.taxonomy = 'propertymls_state' )" );

            if ( sizeof( $data ) != 0 ) {

                echo '<ul>';

                foreach ( $data as $term ) {

                    $term_img = get_tax_meta($term->term_id, 'mls_prop_type_image');
                    $term_type = explode( 'propertymls_', $term->taxonomy );
                    $term_type = $term_type[1];
                    $prop_count = $term->count;

                    if ( empty( $term_img ) ) {
                       $term_img = '<img src="http://placehold.it/40x40" width="40" height="40">';
                   } else {
                        $term_img = wp_get_attachment_image( $term_img['id'], array( 40, 40 ) );
                   }

                    if ( $term_type == 'city' ) {
                        $term_type = $blokhausre_local['auto_city'];
                    } elseif ( $term_type == 'area' ) {
                        $term_type = $blokhausre_local['auto_area'];
                    } else {
                        $term_type = $blokhausre_local['auto_state'];
                    }

                    ?>
                    <li class="media" data-text="<?php echo $term->name; ?>">
                        <div class="media-left">
                            <a href="<?php echo get_term_link( $term ); ?>" class="media-object"><?php echo $term_img; ?></a>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading"> <?php echo $term->name; ?> </h4>
                            <address class="address">
                                <?php if ( !empty( $term_type ) ) { ?>
                                    <?php echo $term_type; ?>
                                <?php } ?>
                                <?php if ( !empty( $prop_count ) ) : ?>
                                     - <?php echo $prop_count . ' ' . $blokhausre_local['auto_listings']; ?>
                                <?php endif; ?>
                            </address>
                        </div>
                        <a href="<?php echo get_term_link( $term ); ?>" class="search-view"> <?php echo $blokhausre_local['auto_view_lists']; ?> </a>
                    </li>
                    <?php

                }

                echo '</ul>';

            } else {

               ?>
               <div class="result">
                   <p> <?php echo $blokhausre_local['auto_result_not_found']; ?> </p>
               </div>
               <?php

           }

        }

        wp_die();

    }

}

/*-----------------------------------------------------------------------------------*/
// Properties search 2
/*-----------------------------------------------------------------------------------*/
if( !function_exists('blokhausre_property_search_2') ) {
    function blokhausre_property_search_2($search_query)
    {

        $tax_query = array();
        $meta_query = array();
        $allowed_html = array();
        $keyword_array = '';

        $keyword_field = houzez_option('keyword_field');
        $beds_baths_search = houzez_option('beds_baths_search');
        $show_featured_on_top = houzez_option('show_featured_on_top');
        $property_id_prefix = houzez_option('property_id_prefix');

        $search_criteria = '=';
        if( $beds_baths_search == 'greater') {
            $search_criteria = '>=';
        }

        $search_location = isset($_GET['search_location']) ? esc_attr($_GET['search_location']) : false;
        $use_radius = 'on';
        $search_lat = isset($_GET['lat']) ? (float)$_GET['lat'] : false;
        $search_long = isset($_GET['lng']) ? (float)$_GET['lng'] : false;
        $search_radius = isset($_GET['radius']) ? (int)$_GET['radius'] : false;

        $search_query = apply_filters('houzez_radius_filter', $search_query, $search_lat, $search_long, $search_radius, $use_radius, $search_location);

        if ( (isset($_GET['keywordsearch']) && $_GET['keywordsearch'] != '') || (isset($_GET['keyword']) && $_GET['keyword'] != '') ) {
            //echo '<p>'.$keyword_field.'</p>';
            if ($keyword_field == 'prop_address') {
                $meta_keywork = wp_kses(stripcslashes($_GET['keywordsearch']), $allowed_html);
                $address_array = array(
                    'key' => 'mls_property_location',
                    'value' => $meta_keywork,
                    'type' => 'CHAR',
                    'compare' => 'LIKE',
                );

                $street_array = array(
                    'key' => 'mls_property_address',
                    'value' => $meta_keywork,
                    'type' => 'CHAR',
                    'compare' => 'LIKE',
                );

                $zip_array = array(
                    'key' => 'mls_property_zip',
                    'value' => $meta_keywork,
                    'type' => 'CHAR',
                    'compare' => '=',
                );

                $propid_array = array(
                    'key' => 'mls_property_id',
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

            } else if ($keyword_field == 'prop_city_state_county') {
                $taxlocation[] = sanitize_title(wp_kses($_GET['keywordsearch'], $allowed_html));

                $_tax_query = Array();
                $_tax_query['relation'] = 'OR';

                $_tax_query[] = array(
                    'taxonomy' => 'propertymls_area',
                    'field' => 'slug',
                    'terms' => $taxlocation
                );

                $_tax_query[] = array(
                    'taxonomy' => 'propertymls_city',
                    'field' => 'slug',
                    'terms' => $taxlocation
                );

                $_tax_query[] = array(
                    'taxonomy' => 'propertymls_state',
                    'field' => 'slug',
                    'terms' => $taxlocation
                );
                $tax_query[] = $_tax_query;

            } else {
                $keyword = trim($_GET['keyword']);
                $keyword2 = trim($_GET['keywordsearch']);
                //echo '<p>'.$keyword.'</p>';
                if (!empty($keyword2)) {
                    $search_query['s'] = $keyword2;
                }else{
                    $search_query['s'] = $keyword;
                }
            }
        }

        // bedrooms logic
        if (isset($_GET['bedrooms']) && !empty($_GET['bedrooms']) && $_GET['bedrooms'] != 'any') {
            $bedrooms = sanitize_text_field($_GET['bedrooms']);
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => 'mls_property_bedrooms',
                    'value' => $bedrooms,
                    'type' => 'CHAR',
                    'compare' => $search_criteria,
                ),
                array(
                    'key' => 'fave_property_bedrooms',
                    'value' => $bedrooms,
                    'type' => 'CHAR',
                    'compare' => $search_criteria,
                )
            );
        }

        // Property ID
        if (isset($_GET['property_id']) && !empty($_GET['property_id'])) {
            $propid = sanitize_text_field($_GET['property_id']);
            $propid = str_replace($property_id_prefix, "", $propid);
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => 'mls_property_id',
                    'value' => $propid,
                    'type' => 'char',
                    'compare' => '=',
                ),
                array(
                    'key' => 'fave_property_id',
                    'value' => $propid,
                    'type' => 'char',
                    'compare' => '=',
                )
            );
        }

        // bathrooms logic
        if (isset($_GET['bathrooms']) && !empty($_GET['bathrooms']) && $_GET['bathrooms'] != 'any') {
            $bathrooms = sanitize_text_field($_GET['bathrooms']);
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => 'mls_property_bathrooms',
                    'value' => $bathrooms,
                    'type' => 'CHAR',
                    'compare' => $search_criteria,
                ),
                array(
                    'key' => 'fave_property_bathrooms',
                    'value' => $bathrooms,
                    'type' => 'CHAR',
                    'compare' => $search_criteria,
                )
                
            );
        }

        // min and max price logic
        if (isset($_GET['min-price']) && !empty($_GET['min-price']) && $_GET['min-price'] != 'any' && isset($_GET['max-price']) && !empty($_GET['max-price']) && $_GET['max-price'] != 'any') {
            $min_price = doubleval(blokhausre_clean($_GET['min-price']));
            $max_price = doubleval(blokhausre_clean($_GET['max-price']));

            if ($min_price > 0 && $max_price > $min_price) {
                $meta_query[] = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'mls_property_price',
                        'value' => array($min_price, $max_price),
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN',
                    ),
                    array(
                        'key' => 'fave_property_price',
                        'value' => array($min_price, $max_price),
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN',
                    )
                );
            }
        } else if (isset($_GET['min-price']) && !empty($_GET['min-price']) && $_GET['min-price'] != 'any') {
            $min_price = doubleval(blokhausre_clean($_GET['min-price']));
            if ($min_price > 0) {
                $meta_query[] = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'mls_property_price',
                        'value' => $min_price,
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    ),
                    array(
                        'key' => 'fave_property_price',
                        'value' => $min_price,
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    )
                );
            }
        } else if (isset($_GET['max-price']) && !empty($_GET['max-price']) && $_GET['max-price'] != 'any') {
            $max_price = doubleval(blokhausre_clean($_GET['max-price']));
            if ($max_price > 0) {
                $meta_query[] = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'mls_property_price',
                        'value' => $max_price,
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ),
                    array(
                        'key' => 'fave_property_price',
                        'value' => $max_price,
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    )                    
                );
            }
        }

        //Custom Fields
        if(class_exists('Houzez_Fields_Builder')) {
            $fields_array = Houzez_Fields_Builder::get_form_fields();
            if(!empty($fields_array)):
                foreach ( $fields_array as $value ):
                    $field_title = $value->label;
                    $field_name = $value->field_id;
                    $is_search = $value->is_search;

                    if($is_search == 'yes') {
                        if(isset($_GET[$field_name]) && !empty($_GET[$field_name])) {
                            $meta_query[] = array(
                                'relation' => 'OR',
                                array(
                                    'key' => 'mls_'.$field_name,
                                    'value' => $_GET[$field_name],
                                    'type' => 'CHAR',
                                    'compare' => '=',
                                ),
                                array(
                                    'key' => 'fave_'.$field_name,
                                    'value' => $_GET[$field_name],
                                    'type' => 'CHAR',
                                    'compare' => '=',
                                )
                            );
                        }
                    }

                endforeach; endif;
        }

        $multi_currency = blokhausre_option('multi_currency');
        if($multi_currency == 1 ) {
            if(!empty($_GET['currency'])) {
                $meta_query[] = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'mls_currency',
                        'value' => $_GET['currency'],
                        'type' => 'CHAR',
                        'compare' => '=',
                    ),
                    array(
                        'key' => 'fave_currency',
                        'value' => $_GET['currency'],
                        'type' => 'CHAR',
                        'compare' => '=',
                    )
                );
            }
        }


        // min and max area logic
        if (isset($_GET['min-area']) && !empty($_GET['min-area']) && isset($_GET['max-area']) && !empty($_GET['max-area'])) {
            $min_area = intval($_GET['min-area']);
            $max_area = intval($_GET['max-area']);

            if ($min_area > 0 && $max_area > $min_area) {
                $meta_query[] = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'mls_property_size',
                        'value' => array($min_area, $max_area),
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN',
                    ),
                    array(
                        'key' => 'fave_property_size',
                        'value' => array($min_area, $max_area),
                        'type' => 'NUMERIC',
                        'compare' => 'BETWEEN',
                    )
                );
            }

        } else if (isset($_GET['max-area']) && !empty($_GET['max-area'])) {
            $max_area = intval($_GET['max-area']);
            if ($max_area > 0) {
                $meta_query[] = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'mls_property_size',
                        'value' => $max_area,
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    ),
                    array(
                        'key' => 'fave_property_size',
                        'value' => $max_area,
                        'type' => 'NUMERIC',
                        'compare' => '<=',
                    )
                );
            }
        } else if (isset($_GET['min-area']) && !empty($_GET['min-area'])) {
            $min_area = intval($_GET['min-area']);
            if ($min_area > 0) {
                $meta_query[] = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'mls_property_size',
                        'value' => $min_area,
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    ),
                    array(
                        'key' => 'fave_property_size',
                        'value' => $min_area,
                        'type' => 'NUMERIC',
                        'compare' => '>=',
                    )                    
                );
            }
        }

        //Date Query
        $publish_date = isset($_GET['publish_date']) ? $_GET['publish_date'] : '';
        if (!empty($publish_date)) {
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


        // Taxonomies
        if (isset($_GET['status']) && !empty($_GET['status']) && $_GET['status'] != 'all') {
            $tax_query[] = array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'propertymls_status',
                    'field' => 'slug',
                    'terms' => $_GET['status']
                ),
                array(
                    'taxonomy' => 'property_status',
                    'field' => 'slug',
                    'terms' => $_GET['status']
                )                
            );
        }

        if (isset($_GET['type']) && !empty($_GET['type']) && $_GET['type'] != 'all') {
            $tax_query[] = array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'propertymls_type',
                    'field' => 'slug',
                    'terms' => $_GET['type']
                ),
                array(
                    'taxonomy' => 'property_type',
                    'field' => 'slug',
                    'terms' => $_GET['type']
                )                
            );
        }

        if (isset($_GET['country']) && !empty($_GET['country']) && $_GET['country'] != 'all') {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => 'propertymls_country',
                    'value' => $_GET['country'],
                    'type' => 'CHAR',
                    'compare' => '=',
                ),
                array(
                    'key' => 'property_country',
                    'value' => $_GET['country'],
                    'type' => 'CHAR',
                    'compare' => '=',
                )                
            );
        }

        if (isset($_GET['state']) && !empty($_GET['state']) && $_GET['state'] != 'all') {
            $tax_query[] = array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'propertymls_state',
                    'field' => 'slug',
                    'terms' => $_GET['state']
                ),
                array(
                    'taxonomy' => 'property_state',
                    'field' => 'slug',
                    'terms' => $_GET['state']
                )
            );
        }

        if (isset($_GET['location']) && !empty($_GET['location']) && $_GET['location'] != 'all') {
            $tax_query[] = array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'propertymls_city',
                    'field' => 'slug',
                    'terms' => $_GET['location']
                ),
                array(
                    'taxonomy' => 'property_city',
                    'field' => 'slug',
                    'terms' => $_GET['location']
                )
            );
        }

        if (isset($_GET['label']) && !empty($_GET['label']) && $_GET['label'] != 'all') {
            $tax_query[] = array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'propertymls_label',
                    'field' => 'slug',
                    'terms' => $_GET['label']
                ),
                array(
                    'taxonomy' => 'property_label',
                    'field' => 'slug',
                    'terms' => $_GET['label']
                )
            );
        }

        if (isset($_GET['area']) && !empty($_GET['area']) && $_GET['area'] != 'all') {
            $tax_query[] = array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'propertymls_area',
                    'field' => 'slug',
                    'terms' => $_GET['area']
                ),
                array(
                    'taxonomy' => 'property_area',
                    'field' => 'slug',
                    'terms' => $_GET['area']
                )
            );
        }

        if (isset($_GET['feature']) && !empty($_GET['feature'])) {
            if (is_array($_GET['feature'])) {
                $features = $_GET['feature'];

                foreach ($features as $feature):
                    $tax_query[] = array(
                        'relation' => 'OR',
                        array(
                            'taxonomy' => 'propertymls_feature',
                            'field' => 'slug',
                            'terms' => $feature
                        ),
                        array(
                            'taxonomy' => 'property_feature',
                            'field' => 'slug',
                            'terms' => $feature
                        )
                    );
                endforeach;
            }
        }

        $meta_count = count($meta_query);

        if ($meta_count > 0 || !empty($keyword_array)) {
            $search_query['meta_query'] = array(
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

        if ($tax_count > 0) {
            $search_query['tax_query'] = $tax_query;
        }
        if($show_featured_on_top != 0 ) {
            $search_query['orderby'] = 'meta_value date';
            $search_query['meta_key'] = 'fave_featured';
            $search_query['order'] = 'DESC';
        }
        //echo '<pre>';
        //print_r($search_query);
        return $search_query;
    }
}
add_filter('blokhausre_search_parameters_2', 'blokhausre_property_search_2');


/*-----------------------------------------------------------------------------------*/
// Properties Pagination
/*-----------------------------------------------------------------------------------*/

function wv_pagination($count_rows,$page) {
    if(empty($page))$page = 1;
    $results_per_page = 12;        
    $number_of_result = $count_rows;
    $paging_info = get_paging_info($number_of_result,$results_per_page,$page);
    $current = $paging_info['curr_page'];
    $prevlink = $current - 1;
    $nextlink = $current + 1; 
    
    if ($number_of_result>$results_per_page) {
    
        if($current > 1){
          echo '<a href="?page='.$prevlink.'"><i class="fa fa-angle-left"></i> Prev</a>';
        }
        
        //$max is equal to number of links shown
        $max = 15;
        if ($max>$paging_info['pages']) {
            $max = $paging_info['pages'];
        }

        //$sp = 0;
        if( $current < $max )
          $sp = 1;
        else if( $current >= ($paging_info['pages'] - floor($max/2)) )
          $sp = ( $paging_info['pages'] - $max ) + 1;
        else if( $current >= $max )
          $sp = $current - floor($max/2);

        if($current >= $max){
          echo '<a href="?page=1">1</a> ...';
        }


        for($i = $sp; $i <= ($sp + $max - 1); $i++){
          if($current == $i){
            echo '<a href="?page='.$i.'" class="active">'.$i.'</a>';
          }else  {   
            echo '<a href="?page='.$i.'">'.$i.'</a>'; 
          }  
        }
        if($current < ($paging_info['pages'] - floor($max / 2))){
          echo ' ... <a href = "?page='.$paging_info['pages'].'">'.$paging_info['pages'].'</a>';
        }
       
        if($current < $paging_info['pages']){
          echo '<a href="?page='.$nextlink.'">Next <i class="fa fa-angle-right"></i></a>';
        }
    }
}


function get_paging_info($tot_rows,$post_per_page,$curr_page){
    $pages = ceil($tot_rows/$post_per_page); // calc pages

    $data = array(); // start out array
    $data['si']        = ($curr_page * $post_per_page) - $post_per_page; // what row to start at
    $data['pages']     = $pages;                   // add the pages
    $data['curr_page'] = $curr_page;               // Whats the current page

    return $data; //return the paging data
}


function get_addressTest($attr, $content = null){

    extract(shortcode_atts(array(
            'featured'           => '1',
            'count'              => '50',
        ), $attr));
    ob_start();

    /*$condosList = array();
        $args = array(
            'posts_per_page'   => -1,
            'post_type'        => 'condos',
            'post_status'      => 'publish',
        );
        $query = new WP_Query( $args );
        while ( $query->have_posts() ) {
            $query->the_post(); 
            $condosList[] = get_the_ID();
        }
    
    echo '<p>'.var_dump($condosList).'</p>';*/


    $args = array(
        'posts_per_page'   => $count,
        'post_type'        => 'propertymls',
        'post_status'      => 'publish',
        'meta_query'       => array(
            array(
                'key'       => 'mls_featured',
                'value'     => $featured,
                'compare'   => '=',
            ), 
        ),
    );
    $query = new WP_Query( $args );
    while ( $query->have_posts() ) {
        $query->the_post(); 
        echo '<p>'.get_the_title().'</p>';
    }
    

    return ob_get_clean();
}
add_shortcode( 'wv_testing_shortcode', 'get_addressTest' );



function displayCarouselFeatured($attr, $content = null){

    extract(shortcode_atts(array(
            'featured'           => '1',
            'count'              => '50',
        ), $attr));
    ob_start();

    $args = array(
        'posts_per_page'   => $count,
        'post_type'        => 'propertymls',
        'post_status'      => 'publish',
        'meta_query'       => array(
            array(
                'key'       => 'mls_featured',
                'value'     => $featured,
                'compare'   => '=',
            ), 
        ),
        'tax_query' => array(
            array(
                'taxonomy' => 'propertymls_label',
                'field'    => 'slug',
                'terms'    => 'hot',
            ),
        ),
    );

    $query = new WP_Query( $args );

    if ($query->have_posts()) :?>
    </a>
        <div id="featured-carousel" class="carousel owl-carousel owl-theme">
        <?php 
            while ( $query->have_posts() ) :
            $query->the_post(); 
            $addressCondo = get_post_meta( get_the_ID(), 'short_address', true);

            $price = get_post_meta( get_the_ID(), 'mls_property_price', true);
            $url =  get_the_permalink( get_the_ID() );
            $title = get_the_title();
            $address = get_post_meta( get_the_ID(), 'mls_property_address', true);
            $num_photos = 0;

            if ( has_post_thumbnail() ) {
                $img = get_the_post_thumbnail_url(get_the_ID(),'full');
            }else{
                $img = '/wp-content/uploads/2016/03/new-york.jpg';
            }

            $SqFt = get_post_meta( get_the_ID(), 'mls_property_size', true);
            $Baths = get_post_meta( get_the_ID(), 'mls_property_bathrooms', true);
            $Beds = get_post_meta( get_the_ID(), 'mls_property_bedrooms', true);
            $id = get_post_meta( get_the_ID(), 'mls_property_id', true);

        ?>
            <div class="item">
                <div id="<?php echo $id; ?>" class="item-wrap">
                    <div class="property-item item-grid">
                        <div class="figure-block">
                            <figure class="item-thumb">
                                    <span class="label-featured label label-success">Featured</span>
                                    <?php 
                                        $term_labels = wp_get_post_terms( get_the_ID(), 'propertymls_label', array("fields" => "all"));
                                        if( !empty($term_labels) ) {
                                            foreach( $term_labels as $label ) {
                                                $label_id = $label->term_id;
                                                $label_name = $label->name;
                                                echo '<span class="label label-'.intval($label_id).' label-color-25">'.esc_attr($label_name).'</span>';
                                            }
                                        }else{
                                            echo '<span id="propertymls_label"></span>';
                                        }

                                        $term_status = wp_get_post_terms( get_the_ID(), 'propertymls_status', array("fields" => "all"));
                                        if( !empty($term_status) ) {
                                            foreach( $term_status as $status ) {
                                                $status_id = $status->term_id;
                                                $status_name = $status->name;
                                                echo '<span class="label label-status label-status-'.intval($status_id).' label-default">'.esc_attr($status_name).'</span>';
                                            }
                                        }else{
                                            echo '<span id="propertymls_status"></span>';
                                        }
                                    ?>
                                    <div class="price hide-on-list">
                                        <span class="item-price"><?php echo houzez_get_property_price($price); ?></span>
                                    </div>
                                    <a class="hover-effect internal-link" href="<?php echo $url; ?>">
                                        <img width="385" height="258" src="<?php echo $img; ?>" class="attachment-houzez-property-thumb-image size-houzez-property-thumb-image wp-post-image" alt="" loading="lazy">
                                    </a>
                            </figure>
                        </div>
                        <div class="item-body">
                            <div class="body-left">
                                <div class="info-row">
                                    <h3 class="property-title">
                                        <a class="internal-link" href="<?php echo $url; ?>"><?php echo $title; ?></a>
                                    </h3>
                                    <address class="property-address"><?php echo $address; ?></address>
                                </div>
                                <div class="table-list full-width info-row">
                                    <div class="cell">
                                        <div class="info-row amenities">
                                            <p>
                                                <span class="h-beds">Beds: <?php echo $Beds; ?></span>
                                                <span class="h-baths">Baths: <?php echo $Baths; ?></span>
                                                <span class="h-area">Sq Ft: <?php echo $SqFt; ?></span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="cell">
                                        <div class="phone">
                                            <a href="<?php echo $url; ?>" class="btn btn-primary internal-link"> Details 
                                                <i class="fa fa-angle-right fa-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        <?php endwhile; ?>
        </div>
        <div id="boxfeatured" style="display: none;"></div>
    <?php endif;

    return ob_get_clean();
}
add_shortcode( 'blokhausre_display_carousel', 'displayCarouselFeatured' );


?>