<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class LDNWOO_Product_query
 */
class LDNWOO_Product_query {
    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {

        if ( is_null( self::$instance ) && ! ( self::$instance instanceof LDNWOO_Product_query ) ) {
            self::$instance = new self;

            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Plugin Hooks
     */
    public function hooks() {
        add_action( 'pre_get_posts', [ $this, 'search_woocommerce_products'] );
        add_filter( 'posts_search', [ $this, 'posts_search_function'], 12, 2);
    }

    /**
     * Add like conditions for title
     */
    function posts_search_function($sql, $query)
    {
        global $wpdb;
        $keyword = $query->get('s');
        if (!$keyword || $sql == '') {
            return $sql;
        }
    
        $exploded = explode( " ", $keyword );

        $where = '';
        foreach( $exploded as $str ) {
            if( !empty($str) && $str!='–' ) {
                $where .= ! empty( $where )?' and ':'';

                $where .= " {$wpdb->posts}.post_title like '%".$str."%'";
            }
        }

        return $sql = " and (".$where.")";
    }

    /**
     * Add product_variation post type in search
     */
    function search_woocommerce_products( $query ) {
        if( ! is_admin() && is_search() && $query->is_main_query() ) {
            if( $_REQUEST['post_type'] == 'product' ) {
                $query->set( 'post_type', array( 'product', 'product_variation' ) );
            }
        }
    }
}

LDNWOO_Product_query::instance();