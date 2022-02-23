

 
<?php
/**
 * Plugin name: Blokhausre Properties MLS
 * Plugin URI: https://kambda.com
 * Description: Get information from external APIs in Blokhausre
 * Author: Wuilly Vargas
 * Author URI: https://kambda.com
 * version: 1.0.0
 * License: GPL2 or later.
 * text-domain: query-apis
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; } 


define( 'BLOKHAUSRE_PLUGIN_URL',               plugin_dir_url( __FILE__ )); // Plugin directory URL
define( 'BLOKHAUSRE_PLUGIN_DIR', 			   plugin_dir_path( __FILE__ ) ); // Plugin directory path
define( 'BLOKHAUSRE_PLUGIN_PATH',              dirname( __FILE__ ));
define( 'BLOKHAUSRE_PLUGIN_IMAGES_URL',        BLOKHAUSRE_PLUGIN_URL  . 'assets/images/');
define( 'BLOKHAUSRE_TEMPLATES',                BLOKHAUSRE_PLUGIN_PATH . '/templates/');
define( 'BLOKHAUSRE_DS',                       DIRECTORY_SEPARATOR);
define( 'BLOKHAUSRE_PLUGIN_BASENAME',          plugin_basename(__FILE__));
define( 'BLOKHAUSRE_VERSION', '1.0.0' );
define( 'BLOKHAUSRE_PLUGIN_CORE_VERSION', '1.0.0' );


require_once 'classes/class-blokhausre-init.php';

register_activation_hook( __FILE__, array( 'Blokhausre', 'blokhausre_plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Blokhausre', 'blokhausre_plugin_deactivate' ) );


// Initialize plugin.
Blokhausre::run();
