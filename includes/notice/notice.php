<?php

function wc_paywithbank3d_missing_notice(){
    echo '<div class="error"><p><strong>' . sprintf( __( 'PayWithBank3D requires WooCommerce to be installed and active. Click %s to install WooCommerce.', 'paywithbank3d-for-woocommerce' ), '<a href="' . admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=772&height=539' ) . '" class="thickbox open-plugin-details-modal">here</a>' ) . '</strong></p></div>';
}

function wc_paywithbank3d_testmode_notice(){
    $paywithbank3d_settings = get_option( 'woocommerce_paywithbank3d_settings' );
    $test_mode         = isset( $paywithbank3d_settings['testmode'] ) ? $paywithbank3d_settings['testmode'] : '';
    if ( 'yes' === $test_mode ) {
        /* translators: 1. Paystack settings page URL link. */
        echo '<div class="update-nag">' . sprintf( __( 'PayWithBank3D test mode is still enabled, Click <strong><a href="%s">here</a></strong> to disable it when you want to start accepting live payment on your site.', 'paywithbank3d-for-woocommerce' ), esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paywithbank3d' ) ) ) . '</div>';
    }
}