<?php

defined( 'ABSPATH' ) || exit;

return array(
    'enabled'                          => array(
        'title'       => __( 'Enable/Disable', 'paywithbank3d-for-woocommerce' ),
        'label'       => __( 'Enable PayWithBank3D', 'paywithbank3d-for-woocommerce' ),
        'type'        => 'checkbox',
        'description' => __( 'Enable PayWithBank3D as a payment option on the checkout page.', 'paywithbank3d-for-woocommerce' ),
        'default'     => 'no',
        'desc_tip'    => true,
    ),
    'title'                            => array(
        'title'       => __( 'Title', 'paywithbank3d-for-woocommerce' ),
        'type'        => 'text',
        'description' => __( 'This controls the payment method title which the user sees during checkout.', 'paywithbank3d-for-woocommerce' ),
        'default'     => __( 'PayWithBank3D GateWay', 'paywithbank3d-for-woocommerce' ),
        'desc_tip'    => true,
    ),
    'description'                      => array(
        'title'       => __( 'Description', 'paywithbank3d-for-woocommerce' ),
        'type'        => 'textarea',
        'description' => __( 'This controls the payment method description which the user sees during checkout.', 'paywithbank3d-for-woocommerce' ),
        'default'     => __( 'Make payment using your debit and credit cards', 'paywithbank3d-for-woocommerce' ),
        'desc_tip'    => true,
    ),
    'testmode'                         => array(
        'title'       => __( 'Test mode', 'paywithbank3d-for-woocommerce' ),
        'label'       => __( 'Enable Test Mode', 'paywithbank3d-for-woocommerce' ),
        'type'        => 'checkbox',
        'description' => __( 'Test mode enables you to test payments before going live. <br />Once the LIVE MODE is enabled on your PayWithBank3D account uncheck this.', 'paywithbank3d-for-woocommerce' ),
        'default'     => 'yes',
        'desc_tip'    => true,
    ),

    'test_secret_key'                  => array(
        'title'       => __( 'Test Secret Key', 'paywithbank3d-for-woocommerce' ),
        'type'        => 'text',
        'description' => __( 'Enter your Test Secret Key here', 'paywithbank3d-for-woocommerce' ),
        'default'     => '',
    ),
    'test_public_key'                  => array(
        'title'       => __( 'Test Public Key', 'paywithbank3d-for-woocommerce' ),
        'type'        => 'text',
        'description' => __( 'Enter your Test Public Key here.', 'paywithbank3d-for-woocommerce' ),
        'default'     => '',
    ),
    'live_secret_key'                  => array(
        'title'       => __( 'Live Secret Key', 'paywithbank3d-for-woocommerce' ),
        'type'        => 'text',
        'description' => __( 'Enter your Live Secret Key here.', 'paywithbank3d-for-woocommerce' ),
        'default'     => '',
    ),
    'live_public_key'                  => array(
        'title'       => __( 'Live Public Key', 'paywithbank3d-for-woocommerce' ),
        'type'        => 'text',
        'description' => __( 'Enter your Live Public Key here.', 'paywithbank3d-for-woocommerce' ),
        'default'     => '',
    ),
    'custom_metadata'                  => array(
        'title'       => __( 'Custom Metadata', 'paywithbank3d-for-woocommerce' ),
        'label'       => __( 'Enable Custom Metadata', 'paywithbank3d-for-woocommerce' ),
        'type'        => 'checkbox',
        'class'       => 'wc-paywithbank3d-metadata',
        'description' => __( 'If enabled, you will be able to send more information about the order to PayWithBank3D.', 'paywithbank3d-for-woocommerce' ),
        'default'     => 'no',
        'desc_tip'    => true,
    ),
    'meta_name'                        => array(
        'title'       => __( 'Customer Name', 'paywithbank3d-for-woocommerce' ),
        'label'       => __( 'Send Customer Name', 'paywithbank3d-for-woocommerce' ),
        'type'        => 'checkbox',
        'class'       => 'wc-paywithbank3d-meta-name',
        'description' => __( 'If checked, the customer full name will be sent to PayWithBank3D', 'paywithbank3d-for-woocommerce' ),
        'default'     => 'yes',
        'desc_tip'    => true,
    ),
    'meta_email'                       => array(
        'title'       => __( 'Customer Email', 'paywithbank3d-for-woocommerce' ),
        'label'       => __( 'Send Customer Email', 'paywithbank3d-for-woocommerce' ),
        'type'        => 'checkbox',
        'class'       => 'wc-paywithbank3d-meta-email',
        'description' => __( 'If checked, the customer email address will be sent to PayWithBank3D', 'paywithbank3d-for-woocommerce' ),
        'default'     => 'yes',
        'desc_tip'    => true,
    ),
    'meta_phone'                       => array(
        'title'       => __( 'Customer Phone', 'paywithbank3d-for-woocommerce' ),
        'label'       => __( 'Send Customer Phone', 'paywithbank3d-for-woocommerce' ),
        'type'        => 'checkbox',
        'class'       => 'wc-paywithbank3d-meta-phone',
        'description' => __( 'If checked, the customer phone will be sent to PayWithBank3D', 'paywithbank3d-for-woocommerce' ),
        'default'     => 'yes',
        'desc_tip'    => true,
    ),
    'meta_color' => array(
        'title' => __('Gateway Color', 'paywithbank3d-for-woocommerce'),
        'label'       => __( 'Payment Color', 'paywithbank3d-for-woocommerce' ),
        'type'        => 'text',
        'class'       => 'wc-paywithbank3d-color',
        'default'     => '#aa0066',
        'description' => __( 'You Can Set The Color That Matches Your Theme', 'paywithbank3d-for-woocommerce' ),
        'desc_tip'    => true,
    )
);