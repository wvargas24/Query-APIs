<?php
/**
 * Widget Name: Advanced Search
 * Version: 1.0
 * Author: Waqas Riaz
 * Author URI: http://favethemes.com/
 *
 * Created by PhpStorm.
 * User: waqasriaz
 * Date: 20/01/16
 * Time: 10:51 PM
 */

class BLOKHAUSRE_advanced_search extends WP_Widget {

    /**
     * Register widget
     **/
    public function __construct() {

        parent::__construct(
            'blokhausre_advanced_search', // Base ID
            esc_html__( 'BLOKHAUSRE: Advanced Search', 'blokhausre' ), // Name
            array( 'description' => esc_html__( 'Advanced Search', 'blokhausre' ), ) // Args
        );

    }


    /**
     * Front-end display of widget
     **/
    public function widget( $args, $instance ) {

        global $before_widget, $after_widget, $before_title, $after_title, $post;
        extract( $args );

        $allowed_html_array = array(
            'div' => array(
                'id' => array(),
                'class' => array()
            ),
            'h3' => array(
                'class' => array()
            )
        );

        $title = apply_filters('widget_title', $instance['title'] );

        echo wp_kses( $before_widget, $allowed_html_array );

        if ( $title ) echo wp_kses( $before_title, $allowed_html_array ) . $title . wp_kses( $after_title, $allowed_html_array );

        blokhausre_advanced_search_widget();

        echo wp_kses( $after_widget, $allowed_html_array );

    }


    /**
     * Sanitize widget form values as they are saved
     **/
    public function update( $new_instance, $old_instance ) {

        $instance = array();

        /* Strip tags to remove HTML. For text inputs and textarea. */
        $instance['title'] = strip_tags( $new_instance['title'] );

        return $instance;

    }


    /**
     * Back-end widget form
     **/
    public function form( $instance ) {

        /* Default widget settings. */
        $defaults = array(
            'title' => 'Find Your Home'
        );
        $instance = wp_parse_args( (array) $instance, $defaults );

        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e('Title:', 'blokhausre'); ?></label>
            <input type="text" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
        </p>

        <?php
    }

}

if ( ! function_exists( 'BLOKHAUSRE_advanced_search_loader' ) ) {
    function BLOKHAUSRE_advanced_search_loader (){
        register_widget( 'BLOKHAUSRE_advanced_search' );
    }
    add_action( 'widgets_init', 'BLOKHAUSRE_advanced_search_loader', 1 );
}

if( !function_exists('blokhausre_advanced_search_widget') ) {
    function blokhausre_advanced_search_widget()
    {

        $search_template = blokhausre_get_search_template_link();
        $adv_show_hide = houzez_option('adv_show_hide');
        $keyword_field = houzez_option('keyword_field');
        $adv_search_price_slider = houzez_option('adv_search_price_slider');

        $state_city_area_dropdowns = houzez_option('state_city_area_dropdowns');
        if( $state_city_area_dropdowns != 0 ) {
            $hide_empty = true;
        } else {
            $hide_empty = false;
        }

        $blokhausre_local = houzez_get_localization();

        if ($keyword_field == 'prop_title') {
            $keyword_field_placeholder = $blokhausre_local['keyword_text'];

        } else if ($keyword_field == 'prop_city_state_county') {
            $keyword_field_placeholder = $blokhausre_local['city_state_area'];

        } else if ($keyword_field == 'prop_address') {
            $keyword_field_placeholder = $blokhausre_local['search_address'];

        } else {
            $keyword_field_placeholder = $blokhausre_local['enter_location'];
        }
        $location = $type = $status = $state = $searched_country = $area = $label = '';

        if (isset($_GET['status'])) {
            $status = $_GET['status'];
        }
        if (isset($_GET['type'])) {
            $type = $_GET['type'];
        }
        if (isset($_GET['area'])) {
            $area = $_GET['area'];
        }
        if (isset($_GET['location'])) {
            $location = $_GET['location'];
        }
        if (isset($_GET['label'])) {
            $label = $_GET['label'];
        }

        if (isset($_GET['state'])) {
            $state = $_GET['state'];
        }
        if (isset($_GET['country'])) {
            $searched_country = $_GET['country'];
        }

        $keyword_field = houzez_option('keyword_field');
        ?>
        <div class="widget-range">
            <div class="widget-body">
                <form autocomplete="off" method="get" action="<?php echo esc_url($search_template); ?>">
                    <div class="range-block rang-form-block">
                        <div class="row">
                            <?php if ($adv_show_hide['keyword'] != 1) { ?>
                                <div class="col-sm-12 col-xs-12 keyword_search">
                                    <div class="form-group">
                                        <input type="text" class="blokhausre_geocomplete form-control"
                                               value="<?php echo isset ($_GET['keyword']) ? $_GET['keyword'] : ''; ?>"
                                               name="keywordsearch" placeholder="<?php echo $keyword_field_placeholder; ?>">

                                        <div id="auto_complete_ajax" class="auto-complete"></div>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($adv_show_hide['countries'] != 1) { ?>
                                <div class="col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <select name="country" class="selectpicker" data-live-search="false"
                                                data-live-search-style="begins">
                                            <?php
                                            // All Option
                                            echo '<option value="">' . $blokhausre_local['all_countries'] . '</option>';

                                            countries_dropdown($searched_country);
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($adv_show_hide['states'] != 1) { ?>
                                <div class="col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <select name="state" class="selectpicker" data-live-search="false">
                                            <?php
                                            // All Option
                                            echo '<option value="">' . $blokhausre_local['all_states'] . '</option>';

                                            $prop_state = get_terms(
                                                array(
                                                    "propertymls_state"
                                                ),
                                                array(
                                                    'orderby' => 'name',
                                                    'order' => 'ASC',
                                                    'hide_empty' => $hide_empty,
                                                    'parent' => 0
                                                )
                                            );
                                            blokhausre_hirarchical_options('propertymls_state', $prop_state, $state);
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($adv_show_hide['cities'] != 1) { ?>
                                <div class="col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <select name="location" class="selectpicker" data-live-search="true">
                                            <?php
                                            // All Option
                                            echo '<option value="">' . $blokhausre_local['all_cities'] . '</option>';

                                            $prop_city = get_terms(
                                                array(
                                                    "propertymls_city"
                                                ),
                                                array(
                                                    'orderby' => 'name',
                                                    'order' => 'ASC',
                                                    'hide_empty' => $hide_empty,
                                                    'parent' => 0
                                                )
                                            );
                                            blokhausre_hirarchical_options('propertymls_city', $prop_city, $location);
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($adv_show_hide['areas'] != 1) { ?>
                                <div class="col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <select name="area" class="selectpicker" data-live-search="true">
                                            <?php
                                            // All Option
                                            echo '<option value="">' . $blokhausre_local['all_areas'] . '</option>';

                                            $prop_area = get_terms(
                                                array(
                                                    "propertymls_area"
                                                ),
                                                array(
                                                    'orderby' => 'name',
                                                    'order' => 'ASC',
                                                    'hide_empty' => $hide_empty,
                                                    'parent' => 0
                                                )
                                            );
                                            blokhausre_hirarchical_options('propertymls_area', $prop_area, $area);
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($adv_show_hide['label'] != 1) { ?>
                                <div class="col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <select class="selectpicker" name="label" data-live-search="false">
                                            <?php
                                            // All Option
                                            echo '<option value="">' . $blokhausre_local['all_labels'] . '</option>';

                                            $prop_label = get_terms(
                                                array(
                                                    "propertymls_label"
                                                ),
                                                array(
                                                    'orderby' => 'name',
                                                    'order' => 'ASC',
                                                    'hide_empty' => false,
                                                    'parent' => 0
                                                )
                                            );
                                            blokhausre_hirarchical_options('propertymls_label', $prop_label, $label);
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($adv_show_hide['property_id'] != 1) { ?>
                                <div class="col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <input type="text" class="form-control"
                                               value="<?php echo isset ($_GET['property_id']) ? $_GET['property_id'] : ''; ?>"
                                               name="property_id"
                                               placeholder="<?php echo $blokhausre_local['property_id']; ?>">
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($adv_show_hide['beds'] != 1) { ?>
                                <div class="col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <select name="bedrooms" class="selectpicker" data-live-search="false" title="">
                                            <option value=""><?php echo $blokhausre_local['beds']; ?></option>
                                            <?php blokhausre_number_list('bedrooms'); ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($adv_show_hide['baths'] != 1) { ?>
                                <div class="col-sm-6 col-xs-12">
                                    <div class="form-group">
                                        <select name="bathrooms" class="selectpicker" data-live-search="false" title="">
                                            <option value=""><?php echo $blokhausre_local['baths']; ?></option>
                                            <?php blokhausre_number_list('bathrooms'); ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($adv_show_hide['type'] != 1) { ?>
                                <div class="col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <select name="type" class="selectpicker" data-live-search="false">
                                            <?php
                                            // All Option
                                            echo '<option value="">' . $blokhausre_local['all_types'] . '</option>';

                                            $prop_type = get_terms(
                                                array(
                                                    "propertymls_type"
                                                ),
                                                array(
                                                    'orderby' => 'name',
                                                    'order' => 'ASC',
                                                    'hide_empty' => true,
                                                    'parent' => 0
                                                )
                                            );
                                            blokhausre_hirarchical_options('propertymls_type', $prop_type, $type);
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php if ($adv_show_hide['status'] != 1) { ?>
                                <div class="col-sm-12 col-xs-12">
                                    <div class="form-group">
                                        <select class="selectpicker" id="widget_status" name="status"
                                                data-live-search="false">
                                            <?php
                                            // All Option
                                            echo '<option value="">' . $blokhausre_local['all_status'] . '</option>';

                                            $prop_status = get_terms(
                                                array(
                                                    "propertymls_status"
                                                ),
                                                array(
                                                    'orderby' => 'name',
                                                    'order' => 'ASC',
                                                    'hide_empty' => false,
                                                    'parent' => 0
                                                )
                                            );
                                            blokhausre_hirarchical_options('propertymls_status', $prop_status, $status);
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>

                            <?php
                            if(class_exists('Houzez_Fields_Builder')) {
                                $fields_array = Houzez_Fields_Builder::get_form_fields();
                                            
                                if(!empty($fields_array)) {
                                    foreach ( $fields_array as $value ) {
                                        $field_title = $value->label;
                                        
                                        $field_title = blokhausre_wpml_translate_single_string($field_title);
                                        
                                        $field_name = $value->field_id;
                                        $field_type = $value->type;
                                        $is_search = $value->is_search;

                                        if(isset($_GET[$field_name])) {
                                            $get_field_name = $_GET[$field_name];
                                        } else {
                                            $get_field_name = '';
                                        }

                                            if( $is_search == 'yes' ) { 

                                                if($adv_show_hide[$field_name] != 1 ) {
                                                    if($field_type == 'select') { ?>

                                                        <div class="col-sm-12 col-xs-12">
                                                            <div class="form-group">
                                                                <select name="<?php echo esc_attr($field_name);?>" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="">
                                                                <option value=""><?php echo $field_title; ?></option>
                                                                <?php
                                                                $options = unserialize($value->fvalues);
                                                                
                                                                foreach ($options as $key => $val) {

                                                                    $val = blokhausre_wpml_translate_single_string($val);
                                                                    echo '<option '.selected( $key, $get_field_name, false).' value="'.$key.'">'.$val.'</option>';
                                                                }
                                                                ?>
                                                                </select>
                                                            </div>
                                                        </div>

                                                    <?php
                                                    } else {
                                                    ?>

                                                    <div class="col-sm-12 col-xs-12">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control <?php echo esc_attr($field_name);?>" value="<?php echo isset ( $_GET[$field_name] ) ? $_GET[$field_name] : ''; ?>" name="<?php echo esc_attr($field_name);?>" placeholder="<?php esc_attr_e($field_title);?>">
                                                        </div>
                                                    </div>

                                                    <?php
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            ?>

                            <?php 
                                $multi_currency = houzez_option('multi_currency');
                                if($multi_currency == 1 ) {
                                    if(class_exists('Houzez_Currencies')) {

                                        $searched_currency = isset($_GET['currency']) ? $_GET['currency'] : '';

                                        $currencies = Houzez_Currencies::get_currency_codes();
                                        if($currencies) {
                                            echo '<div class="col-sm-12 col-xs-6">';
                                            echo '<div class="form-group">';
                                            echo '<select name="currency" class="selectpicker" data-live-search="false" data-live-search-style="begins" title="">';
                                            echo '<option value="">'.$blokhausre_local['currency_label'].'</option>';
                                            echo '<option value="">'.$blokhausre_local['any'].'</option>';
                                            foreach ($currencies as $currency) {
                                                echo '<option '.selected( $currency->currency_code, $searched_currency, false).' value="'.$currency->currency_code.'">'.$currency->currency_code.'</option>'; 
                                            }
                                            echo '</select>';
                                            echo '</div>';
                                            echo '</div>';
                                        }
                                    }
                                }
                            ?>

                            <?php if ($adv_search_price_slider != 1) { ?>
                                <?php if ($adv_show_hide['min_price'] != 1) { ?>
                                    <div class="col-sm-6 col-xs-12">
                                        <div class="form-group prices-for-all">
                                            <select name="min-price" class="selectpicker" data-live-search="false"
                                                    data-live-search-style="begins" title="">
                                                <option value=""><?php echo $blokhausre_local['min_price']; ?></option>
                                                <?php blokhausre_adv_searches_min_price(); ?>
                                            </select>
                                        </div>
                                        <div class="form-group hide prices-only-for-rent">
                                            <select name="min-price" disabled class="selectpicker"
                                                    data-live-search="false" data-live-search-style="begins" title="">
                                                <option value=""><?php echo $blokhausre_local['min_price']; ?></option>
                                                <?php blokhausre_adv_searches_min_price_rent_only(); ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php } ?>

                                <?php if ($adv_show_hide['max_price'] != 1) { ?>
                                    <div class="col-sm-6 col-xs-12">
                                        <div class="form-group prices-for-all">
                                            <select name="max-price" class="selectpicker" data-live-search="false"
                                                    data-live-search-style="begins" title="">
                                                <option value=""><?php echo $blokhausre_local['max_price']; ?></option>
                                                <?php blokhausre_adv_searches_max_price() ?>
                                            </select>
                                        </div>
                                        <div class="form-group hide prices-only-for-rent">
                                            <select name="max-price" disabled class="selectpicker"
                                                    data-live-search="false" data-live-search-style="begins" title="">
                                                <option value=""><?php echo $blokhausre_local['max_price']; ?></option>
                                                <?php blokhausre_adv_searches_max_price_rent_only() ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>

                        </div>
                    </div>

                    <?php if ($adv_search_price_slider != 0) { ?>
                        <?php if ($adv_show_hide['price_slider'] != 1) { ?>
                            <div class="range-block">
                                <h4><?php echo $blokhausre_local['price_range']; ?></h4>

                                <div id="slider-price"></div>
                                <div class="clearfix range-text">
                                    <input type="text" name="min-price" class="pull-left range-input text-left"
                                           id="min-price" readonly>
                                    <input type="text" name="max-price" class="pull-right range-input text-right"
                                           id="max-price" readonly>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>


                    <?php if ($adv_show_hide['area_slider'] != 1) { ?>
                        <div class="range-block">
                            <h4><?php echo $blokhausre_local['area_size']; ?></h4>

                            <div id="slider-size"></div>
                            <div class="clearfix range-text">
                                <input type="text" name="min-area" class="pull-left range-input text-left" id="min-size"
                                       readonly>
                                <input type="text" name="max-area" class="pull-right range-input text-right"
                                       id="max-size" readonly>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($adv_show_hide['other_features'] != 1) { ?>
                        <div class="row">
                            <div class="col-sm-12 col-xs-12">
                                <label class="advance-trigger"><i
                                        class="fa fa-plus-square"></i> <?php echo $blokhausre_local['other_feature']; ?>
                                </label>
                            </div>
                            <div class="col-sm-12 col-xs-12">
                                <div class="features-list field-expand">
                                    <div class="clearfix"></div>
                                    <?php get_template_part('template-parts/advanced-search/search-features'); ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="range-block rang-form-block">
                        <div class="row">
                            <div class="col-sm-12 col-xs-12">
                                <button type="submit" class="btn btn-secondary btn-block"><i
                                        class="fa fa-search fa-left"></i><?php echo $blokhausre_local['search']; ?></button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
        <?php
    }
}