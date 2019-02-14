<?php
/*
Plugin Name: REST API Data Localizer
Description: Localizes a store of normalized WP REST API response data to a registered script. Useful for sharing data between Wordpress and front-end frameworks like Vue.js, React, and Angular that leverage the WP REST API.
Author:      Brandon Shiluk
Author URI:  https://github.com/bucky355
Version:     1.0.0
Text Domain: rest-api-data-localizer
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( !class_exists( 'RADL' ) ) {

    class RADL
    {
        /**
         * Version
         * @var string
         */
        public static $version = '1.0.0';

        /**
         * Tracks all data requested - rendered output to be localized
         * @var Store
         */
        private static $store;

        public function __construct( $name, $script_handle, array $schema )
        {
            if ( !defined( 'ABSPATH' ) ) {
                return;
            }
            self::$store = new RADL\Store( $name, $script_handle, $schema );
            add_action( 'wp_footer', array( 'RADL', 'localize' ) );
        }

        public static function get( $key_path, $args = array() )
        {
            return self::$store->get( $key_path, $args );
        }

        public static function endpoint( $name, array $preload = array() )
        {
            return new RADL\Store\Key\Endpoint( $name, $preload );
        }

        public static function callback( callable $callable )
        {
            return new RADL\Store\Key\Callback( $callable );
        }

        public static function localize()
        {
            $rendered = self::$store->rendered();
            wp_localize_script( self::$store->script_handle, self::$store->name, $rendered );
        }

        private static function autoloader( $class_name )
        {
            if ( strpos( $class_name, 'RADL' ) === 0 ) {
                require plugin_dir_path( __DIR__ ) . str_replace( '\\', '/', str_replace( '_', '-', str_replace( 'radl', 'rest-api-data-localizer', strtolower( $class_name ) ) ) ) . '.php';
            }
        }

        public static function init()
        {
            if ( !defined( 'RADL_LOADED' ) ) {
                spl_autoload_register( array( 'RADL', 'autoloader' ) );
                define( 'RADL_LOADED', true );
            }
        }

    }

    RADL::init();

}
