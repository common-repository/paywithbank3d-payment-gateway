jQuery( function( $ ) {
    'use strict';
    let wc_paywithbank3d_admin = {
        /**
         * Initialize.
         */
        init: function() {
            // Toggle api key settings.
                $( '#woocommerce_paywithbank3d_testmode' ).change( function() {
                let test_secret_key = $( '#woocommerce_paywithbank3d_test_secret_key' ).parents( 'tr' ).eq( 0 ),
                    test_public_key = $( '#woocommerce_paywithbank3d_test_public_key' ).parents( 'tr' ).eq( 0 ),
                    live_secret_key = $( '#woocommerce_paywithbank3d_live_secret_key' ).parents( 'tr' ).eq( 0 ),
                    live_public_key = $( '#woocommerce_paywithbank3d_live_public_key' ).parents( 'tr' ).eq( 0 );
                if ( $( this ).is( ':checked' ) ) {
                    test_secret_key.show();
                    test_public_key.show();
                    live_secret_key.hide();
                    live_public_key.hide();
                } else {
                    test_secret_key.hide();
                    test_public_key.hide();
                    live_secret_key.show();
                    live_public_key.show();
                }
            }).change();

            $( '.wc-paywithbank3d-metadata' ).change( function() {
                if ( $( this ).is( ':checked' ) ) {
                    $( '.wc-paywithbank3d-meta-order-id, .wc-paywithbank3d-meta-name, .wc-paywithbank3d-meta-email, .wc-paywithbank3d-meta-phone' ).closest( 'tr' ).show();
                } else {
                    $( '.wc-paywithbank3d-meta-order-id, .wc-paywithbank3d-meta-name, .wc-paywithbank3d-meta-email, .wc-paywithbank3d-meta-phone' ).closest( 'tr' ).hide();
                }
            }).change();

            $('.wc-paywithbank3d-color').wpColorPicker();
        }

    };
    wc_paywithbank3d_admin.init();
} );