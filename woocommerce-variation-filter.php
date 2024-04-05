<?php
/**
 * Plugin Name: Woocommerce Search Customizations
 * Version: 1.0
 * Description:
 * Author: LDninjas.com
 * Author URI: LDninjas.com
 * Plugin URI: LDninjas.com
 * Text Domain: ldnwoo-search-customizations
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class LDNWOO_Search_Customizations
 */
class LDNWOO_Search_Customizations {

    const VERSION = '1.0';

    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {

        if ( is_null( self::$instance ) && ! ( self::$instance instanceof LDNWOO_Search_Customizations ) ) {
            self::$instance = new self;

            self::$instance->setup_constants();
            self::$instance->hooks();
            self::$instance->includes();
        }

        return self::$instance;
    }

    /**
     * defining constants for plugin
     */
    public function setup_constants() {

        /**
         * Directory
         */
        define( 'LDNWOO_DIR', plugin_dir_path ( __FILE__ ) );
        define( 'LDNWOO_DIR_FILE', LDNWOO_DIR . basename ( __FILE__ ) );
        define( 'LDNWOO_INCLUDES_DIR', trailingslashit ( LDNWOO_DIR . 'includes' ) );
        define( 'LDNWOO_TEMPLATES_DIR', trailingslashit ( LDNWOO_DIR . 'templates' ) );
        define( 'LDNWOO_BASE_DIR', plugin_basename(__FILE__));

        /**
         * URLs
         */
        define( 'LDNWOO_URL', trailingslashit ( plugins_url ( '', __FILE__ ) ) );
        define( 'LDNWOO_ASSETS_URL', trailingslashit ( LDNWOO_URL . 'assets/' ) );

        /**
         * Text Domain
         */
        define( 'LDNWOO_TEXT_DOMAIN', 'ldnwoo-search-customizations' );
    }

    /**
     * Plugin requiered files
     */
    public function includes() {

        /**
         * Required all files 
         */
        
        if( file_exists( LDNWOO_INCLUDES_DIR.'post-query.php' ) ) {
            require_once LDNWOO_INCLUDES_DIR . 'post-query.php';
        } 
    }

    /**
     * Plugin Hooks
     */
    public function hooks() {
        add_action( 'pre_get_posts', [ $this, 'search_woocommerce_products'] );
        add_filter( 'posts_search', [ $this, '_my_posts_search_function'], 12, 2);
    }
    function _my_posts_search_function($sql, $query)
    {
        global $wpdb;
        $keyword = $query->get('s');
        if (!$keyword || $sql == '') {
            return $sql;
        }
    
        $exploded = explode( " ", $keyword );
        print_r($exploded);
        $where = '';
        foreach( $exploded as $str ) {
            if( !empty($str) && $str!='–' ) {
                $where .= ! empty( $where )?' and ':'';

                $where .= " {$wpdb->posts}.post_title like '%".$str."%'";
            }
        }

        return $sql = " and (".$where.")";
        
        
    }
    function search_woocommerce_products( $query ) {
        if( ! is_admin() && is_search() && $query->is_main_query() ) {
            if( $_REQUEST['post_type'] == 'product' ) {
                $query->set( 'post_type', array( 'product', 'product_variation' ) );
            }
        }
    }
}

/**
 * Display admin notifications if dependency not found.
 */
function ldnwoo_ready() {
    if( !is_admin() ) {
        return;
    }

    if( ! class_exists( 'SFWD_LMS' ) ) {
        deactivate_plugins ( plugin_basename ( __FILE__ ), true );
        $class = 'notice is-dismissible error';
        $message = __( 'Woocommerce Search Customizations add-on requires woocommerce plugin to be activated', 'ldnwoo-search-customizations' );
        printf ( '<div id="message" class="%s"> <p>%s</p></div>', $class, $message );
    }
}

/**
 * @return bool
 */
function LDNWOO() {
    
    if ( ! class_exists( 'WC_Cart' ) ) {
        add_action( 'admin_notices', 'ldnwoo_ready' );
        return false;
    }

    return LDNWOO_Search_Customizations::instance();
}
add_action( 'plugins_loaded', 'LDNWOO' );