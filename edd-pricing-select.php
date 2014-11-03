<?php
/**
 * Plugin Name:     Easy Digital Downloads - Pricing Select
 * Plugin URI:      http://section214.com
 * Description:     Converts EDD variable pricing from radios to a simple dropdown
 * Version:         1.0.0
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 *
 * @package         EDD\PricingSelect
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 * @copyright       Copyright (c) 2014, Daniel J Griffiths
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


if( ! class_exists( 'EDD_Pricing_Select' ) ) {


    /**
     * Main EDD_Pricing_Select class
     *
     * @since       1.0.0
     */
    class EDD_Pricing_Select {


        /**
         * @var         EDD_Pricing_Select $instance The one true EDD_Pricing_Select
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      self::$instance The one true EDD_Pricing_Select
         */
        public static function instance() {
            if( ! self::$instance ) {
                self::$instance = new EDD_Pricing_Select();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {
            remove_action( 'edd_purchase_link_top', 'edd_purchase_variable_pricing', 10, 1 );
            add_action( 'edd_purchase_link_top', array( $this, 'variable_pricing' ), 10, 1 );
        }


        /**
         * Create our new interface for variable pricing
         *
         * @access      public
         * @since       1.0.0
         * @param       int $download_id The ID of a given download
         * @return      void
         */
        public function variable_pricing( $download_id ) {
            // Bail if this download doesn't have variable pricing
            if( ! edd_has_variable_prices( $download_id ) ) {
                return;
            }

            // Get the pricing options for this product
            $prices = apply_filters( 'edd_purchase_variable_prices', edd_get_variable_prices( $download_id ), $download_id );
            $type   = edd_single_price_option_mode( $download_id ) ? 'checkbox' : 'radio';

            do_action( 'edd_before_price_options', $download_id );

            echo '<div class="edd_price_options">';

            if( $prices ) {
                echo '<select name="edd_options[price_id][]">';

                foreach( $prices as $key => $price ) {
                    printf(
                        '<option for="%1$s" name="edd_options[price_id][]" id="%1$s" class="%2$s" value="%3$s" %5$s>%4$s</option>',
                        esc_attr( 'edd_price_option_' . $download_id . '_' . $key ),
                        esc_attr( 'edd_price_option_' . $download_id ),
                        esc_attr( $key ),
                        esc_html( $price['name'] . ' - ' . edd_currency_filter( edd_format_amount( $price['amount'] ) ) ),
                        selected( isset( $_GET['price_option'] ), $key, false )
                    );
                    do_action( 'edd_after_price_option', $key, $price, $download_id );
                }

                echo '</select>';
            }

            do_action( 'edd_after_price_options_list', $download_id, $prices, $type );

            echo '</div>';

            do_action( 'edd_after_price_options', $download_id );
        }
    }
}


/**
 * The main function responsible for returning the one true EDD_Pricing_Select
 * instance to functions everywhere.
 *
 * @since       1.0.0
 * @return      EDD_Pricing_Select The one true EDD_Pricing_Select
 */
function edd_pricing_select() {
    if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
        if( ! class_exists( 'EDD_Extension_Activation' ) ) {
            require_once 'includes/class.extension-activation.php';
        }

        $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run;

        return EDD_Pricing_Select::instance();
    } else {
        return EDD_Pricing_Select::instance();
    }
}
add_action( 'plugins_loaded', 'edd_pricing_select' );
