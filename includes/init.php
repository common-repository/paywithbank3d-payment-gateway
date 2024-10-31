<?php

function wc_paywithbank3d_init(){
    require_once dirname( __FILE__ ) . '/notice/notice.php';
    if ( !class_exists( 'WC_Payment_Gateway' ) ) {
        add_action( 'admin_notices', 'wc_paywithbank3d_missing_notice' );
    }
    add_action( 'admin_notices', 'wc_paywithbank3d_testmode_notice' );
    add_filter('plugin_action_links_paywithbank3d-for-woocommerce/paywithbank3d-for-woocommerce.php', 'woo_paywithbank3d_plugin_action_links', 10, 2);
    if ( class_exists( 'WC_Payment_Gateway_CC' ) ) {
        require_once dirname( __FILE__ ) . '/gateway/paywithbank3d-gateway.php';
        add_filter( 'woocommerce_payment_gateways', 'add_paywithbank3d_gateway', 99 );
    }
}


function woo_paywithbank3d_plugin_action_links($links){
    $settings_link = array(
        'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paywithbank3d' ) . '" title="' . __( 'View PayWithBank3D WooCommerce Settings', 'paywithbank3d-for-woocommerce' ) . '">' . __( 'Settings', 'paywithbank3d-for-woocommerce' ) . '</a>',
    );

    return array_merge( $settings_link, $links );
}

function add_paywithbank3d_gateway($methods){
    if ( class_exists( 'WC_Payment_Gateway_CC' ) ) {
        $methods[] = 'WC_Gateway_PayWithBank3D';
    }
    return $methods;
}




