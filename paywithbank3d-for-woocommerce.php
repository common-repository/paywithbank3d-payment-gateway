<?php
/**
 * Plugin Name: PayWithBank3D Payment Gateway For WooCommerce
 * Plugin URI: https://paywithbank3d.com
 * Description: PayWithBank3D Payment Gateway allows you to accept local and International payment via Verve Card, MasterCard & Visa Card On Your WooCommerce Store
 * Version: 1.0.0
 * Author: Edward Paul
 * Author URI: https://medium.com/@infinitypaul
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * WC requires at least: 3.0.0
 * WC tested up to: 3.8
 * Text Domain: paywithbank3d-for-woocommerce
 * Domain Path: /languages
 */

if(!defined( 'ABSPATH' ) ){
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
include 'includes/utility.php';
include 'includes/init.php';

define( 'PAYWITHBANK3D_PG_VERSION', '1.0.0' );
define('PAYWITHBANK3D_PLUGIN_URL', __FILE__);
define( 'PAYWITHBANK3D_PG_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );

//WC_PAYWITHBANK3D_VERSION
//WC_PAYWITHBANK3D_URL

add_action( 'plugins_loaded', 'wc_paywithbank3d_init', 99 );