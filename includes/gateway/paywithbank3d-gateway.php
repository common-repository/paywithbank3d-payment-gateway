<?php

class WC_Gateway_PayWithBank3D extends WC_Payment_Gateway_CC {

    /**
     * Whether or not logging is enabled
     *
     * @var bool
     */
    public static $log_enabled = false;

    /**
     * Logger instance
     *
     * @var WC_Logger
     */
    public static $log = false;

    /**
     * PayWithBank3D Color.
     *
     * @var string
     */
    public $color;

    /**
     * PayWithBank3D Mode.
     *
     * @var string
     */
    public $mode;
    /**
     * Is test mode active?
     *
     * @var bool
     */
    public $testmode;

    /**
     * PayWithBank3D test public key.
     *
     * @var string
     */
    public $test_public_key;

    /**
     * PayWithBank3D test secret key.
     *
     * @var string
     */
    public $test_secret_key;

    /**
     * PayWithBank3D live public key.
     *
     * @var string
     */
    public $live_public_key;

    /**
     * PayWithBank3D live secret key.
     *
     * @var string
     */
    public $live_secret_key;


    /**
     * Should custom metadata be enabled?
     *
     * @var bool
     */
    public $custom_metadata;


    /**
     * Should the customer name be sent as a custom metadata to PayWithBank3D?
     *
     * @var bool
     */
    public $meta_name;

    /**
     * Should the billing email be sent as a custom metadata to PayWithBank3D?
     *
     * @var bool
     */
    public $meta_email;

    /**
     * Should the billing phone be sent as a custom metadata to PayWithBank3D?
     *
     * @var bool
     */
    public $meta_phone;



    /**
     * API public key
     *
     * @var string
     */
    public $public_key;

    /**
     * API secret key
     *
     * @var string
     */
    public $secret_key;


    public function __construct() {
        $this->id                 = 'paywithbank3d';
        $this->method_title       = __( 'PayWithBank3D', 'paywithbank3d-for-woocommerce' );
        $this->method_description = sprintf( __( 'PayWithBank3D provide merchants with the tools and services needed to accept online payments from local and international customers using Mastercard, Visa, Verve Cards and Bank Accounts. <a href="%1$s" target="_blank">Sign up</a> for a PayWithBank3D account, and <a href="%2$s" target="_blank">get your API keys</a>.', 'paywithbank3d-for-woocommerce' ), 'https://bank3d.ng', 'https://bank3d.ng' );
        $this->has_fields         = true;
        $this->supports           = array(
            'products'
        );

        //Load The Form
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        $this->title       = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        $this->enabled     = $this->get_option( 'enabled' );
        $this->testmode    = $this->get_option( 'testmode' ) === 'yes' ? true : false;

        $this->test_public_key = $this->get_option( 'test_public_key' );
        $this->test_secret_key = $this->get_option( 'test_secret_key' );
        $this->color = $this->get_option( 'meta_color' );

        $this->live_public_key = $this->get_option( 'live_public_key' );
        $this->live_secret_key = $this->get_option( 'live_secret_key' );

        $this->public_key = $this->testmode ? $this->test_public_key : $this->live_public_key;
        $this->secret_key = $this->testmode ? $this->test_secret_key : $this->live_secret_key;
        $this->mode = $this->testmode ? 'test' : 'live';
        self::$log_enabled    = true;

        add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            array(
                $this,
                'process_admin_options',
            )
        );

        add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

        // Payment listener/API hook.
        add_action( 'woocommerce_api_wc_gateway_paywithbank3d', array( $this, 'verify_paywithbank3d_transaction' ) );

        // Webhook listener/API hook.
        add_action( 'woocommerce_api_wc_paywithbank3d_webhook', array( $this, 'process_webhooks' ) );

        if ( ! $this->is_valid_for_use() ) {
            $this->enabled = false;
        }
    }

    public function is_valid_for_use() {
        if ( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_paywithbank3d_supported_currencies', array( 'NGN') ) ) ) {

            $this->msg = sprintf( __( 'PayWithBank3D does not support your store currency. Kindly set it to either NGN (&#8358) <a href="%s">here</a>', 'paywithbank3d-for-woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=general' ) );

            return false;

        }

        return true;
    }


    public function admin_notices() {

        if ( $this->enabled == 'no' ) {
            return;
        }

        // Check required fields.
        if ( ! ( $this->public_key && $this->secret_key ) ) {
            echo '<div class="error"><p>' . sprintf( __( 'Please enter your PayWithBank3D merchant details <a href="%s">here</a> to be able to use the PayWithBank3D WooCommerce plugin.', 'paywithbank3d-for-woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paywithbank3d' ) ) . '</p></div>';
            return;
        }

    }


    public function payment_scripts() {

        if (!is_checkout_pay_page()) {
            return;
        }

        if ( $this->enabled === 'no' ) {
            return;
        }
        $key = sanitize_text_field($_GET['key']);
        $order_key = urldecode( $key);
        $order_id  = absint( get_query_var( 'order-pay' ) );

        $order = wc_get_order( $order_id );

        $payment_method = method_exists( $order, 'get_payment_method' ) ? $order->get_payment_method() : $order->payment_method;

        if ( $this->id !== $payment_method ) {
            return;
        }

        wp_enqueue_script( 'jquery' );

        wp_enqueue_script( 'wc_paywithbank3d', 'https://parkwaycdnstorage.blob.core.windows.net/bank3d/bank3d.min.js', PAYWITHBANK3D_PG_VERSION, false, 1);
        wp_enqueue_script( 'wc_paywithbank3d_v1', plugins_url( 'assets/js/paywithbank3d.js', PAYWITHBANK3D_PLUGIN_URL ), array( 'jquery', 'wc_paywithbank3d' ), PAYWITHBANK3D_PG_VERSION, false );

        $paywithbank3d_params = array(
            'key' => $this->public_key,
            'mode' => $this->mode,
            'color' => $this->color
        );

        if ( is_checkout_pay_page() && get_query_var( 'order-pay' ) ) {
            if(method_exists( $order, 'get_billing_email' )){
                $email =  $order->get_billing_email();
            } else {
                $email = $order->billing_email;
            }
            $amount        = $order->get_total() * 100;
            $txnref        = $order_id . '_' . time();
            if(method_exists( $order, 'get_id' )){
                $the_order_id  =  $order->get_id();
            } else {
                $the_order_id  = $order->id;
            }
            if(method_exists( $order, 'get_order_key' )){
                $the_order_key  =  $order->get_order_key();
            } else {
                $the_order_key  = $order->order_key;
            }

            if ( $the_order_id == $order_id && $the_order_key == $order_key ) {
                $paywithbank3d_params['email']        = $email;
                $paywithbank3d_params['amount']       = $amount;
                $paywithbank3d_params['txnref']       = $txnref;
                $paywithbank3d_params['currency']     = get_woocommerce_currency();
            }

            if ( $this->custom_metadata ) {
                if ( $this->meta_name ) {
                    $first_name = method_exists( $order, 'get_billing_first_name' ) ? $order->get_billing_first_name() : $order->billing_first_name;
                    $last_name  = method_exists( $order, 'get_billing_last_name' ) ? $order->get_billing_last_name() : $order->billing_last_name;

                    $paywithbank3d_params['meta_name'] = $first_name . ' ' . $last_name;
                }

                if ( $this->meta_email ) {

                    $paywithbank3d_params['meta_email'] = $email;

                }

                if ( $this->meta_phone ) {

                    $billing_phone = method_exists( $order, 'get_billing_phone' ) ? $order->get_billing_phone() : $order->billing_phone;

                    $paywithbank3d_params['meta_phone'] = $billing_phone;

                }
            }
            update_post_meta( $order_id, '_paywithbank3d_txn_ref', $txnref );
        }
        wp_localize_script( 'wc_paywithbank3d', 'wc_paywithbank3d_params', $paywithbank3d_params );



    }

    public function admin_scripts() {
        if ( 'woocommerce_page_wc-settings' !== get_current_screen()->id ) {
            return;
        }
        $paywithbank3d_admin_params = array(
            'plugin_url' => PAYWITHBANK3D_PG_URL,
        );
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wc_paywithbank3d_admin', plugins_url( 'assets/js/paywithbank3d_admin.js', PAYWITHBANK3D_PLUGIN_URL ), ['wp-color-picker'], PAYWITHBANK3D_PG_VERSION, true );


        wp_localize_script( 'wc_paywithbank3d_admin', 'wc_paywithbank3d_admin', $paywithbank3d_admin_params );
    }

    public function get_icon() {

        $icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/Secured-by-Bank3D@2x.png', PAYWITHBANK3D_PLUGIN_URL ) ) . '" alt="cards" />';

        return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );

    }

    /**
     * Check if PayWithBank3D gateway is enabled.
     *
     * @return bool
     */
    public function is_available() {

        if ( 'yes' == $this->enabled ) {

            if ( ! ( $this->public_key && $this->secret_key ) ) {

                return false;

            }

            return true;

        }

        return false;

    }

    /**
     * Admin Panel Options.
     */
    public function admin_options() {

        ?>

        <h2><?php _e( 'PayWithBank3D', 'paywithbank3d-for-woocommerce' ); ?>
            <?php
            if ( function_exists( 'wc_back_link' ) ) {
                wc_back_link( __( 'Return to payments', 'paywithbank3d-for-woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=checkout' ) );
            }
            ?>
        </h2>

        <h4>
            <strong><?php printf( __( 'Optional: To avoid situations where bad network makes it impossible to verify transactions, set your webhook URL <a href="%1$s" target="_blank" rel="noopener noreferrer">here</a> to the URL below<span style="color: red"><pre><code>%2$s</code></pre></span>', 'paywithbank3d-for-woocommerce' ), 'https://bank3d.ng', WC()->api_request_url( 'WC_Paywithbank3d_Webhook' ) ); ?></strong>
        </h4>

        <?php

        if ( $this->is_valid_for_use() ) {

            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';

        } else {
            ?>
            <div class="inline error"><p><strong><?php _e( 'PayWithBank3D Payment Gateway Disabled', 'paywithbank3d-for-woocommerce' ); ?></strong>: <?php echo $this->msg; ?></p></div>

            <?php
        }

    }

    /**
     * Initialise Gateway Settings Form Fields.
     */
    public function init_form_fields() {

        $this->form_fields = include 'settings-paywithbank3d.php';;

    }

    /**
     * Displays the payment page.
     *
     * @param $order_id
     */
    public function receipt_page( $order_id ) {

        $order = wc_get_order( $order_id );


            echo '<p>' . __( 'Thank you for your order, please click the button below to pay with PayWithBank3D.', 'paywithbank3d-for-woocommerce' ) . '</p>';

            echo '<div id="paywithbank3d_form"><form id="order_review" method="post" action="' . WC()->api_request_url( 'WC_Gateway_PayWithBank3D' ) . '"></form><button class="button alt" id="paywithbank3d-payment-button">' . __( 'Pay Now', 'paywithbank3d-for-woocommerce' ) . '</button> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'paywithbank3d-for-woocommerce' ) . '</a></div>';

    }

    /**
     * Verify PayWithBank3D payment.
     */
    public function verify_paywithbank3d_transaction() {

        @ob_clean();
        $paywithbank3d_ref = sanitize_key($_REQUEST['paywithbank3d_txnref']);
        if ( isset($paywithbank3d_ref) ) {
            if($this->testmode){
                $url = 'https://staging.paywithbank3d.com/api/payment/verify/'.$paywithbank3d_ref;
            } else {
                $url = 'https://paywithbank3d.com/api/payment/verify/'.$paywithbank3d_ref;
            }
            $headers = array(
                'Authorization' => 'Basic ' . base64_encode( $this->public_key . ':' . $this->secret_key )
            );
            $args = array(
                'headers'    => $headers,
                'timeout'    => 60
            );

            $request = wp_remote_get( $url, $args );
            if ( ! is_wp_error( $request ) && 200 === wp_remote_retrieve_response_code( $request ) ) {
                $paywithbank3d_response = json_decode( wp_remote_retrieve_body( $request ) );

                if ('00' === $paywithbank3d_response->code) {
                    $order_details = explode( '_', $paywithbank3d_response->merchantRef );
                    $order_id      = (int) $order_details[0];
                    $order         = wc_get_order( $order_id );
                    if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {

                        wp_redirect( $this->get_return_url( $order ) );

                        exit;

                    }
                    $order_total      = $order->get_total();
                    $order_currency   = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();
                    $currency_symbol  = get_woocommerce_currency_symbol( $order_currency );
                    $amount_paid      = $paywithbank3d_response->amount / 100;
                    $paywithbank3d_ref     = $paywithbank3d_response->merchantRef;
                    $payment_currency = strtoupper( $paywithbank3d_response->currencyCode );
                    $gateway_symbol   = get_woocommerce_currency_symbol( $payment_currency );
                    if ( $amount_paid < $order_total ) {
                        $order->update_status( 'on-hold', '' );

                        add_post_meta( $order_id, '_paywithbank3d_transaction_id', $paywithbank3d_ref, true );
                        $notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'paywithbank3d-for-woocommerce' ), '<br />', '<br />', '<br />' );
                        $notice_type = 'notice';

                        // Add Customer Order Note
                        $order->add_order_note( $notice, 1 );

                        // Add Admin Order Note
                        $admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>PayWithBank3D Transaction Reference:</strong> %9$s', 'paywithbank3d-for-woocommerce' ), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $paywithbank3d_ref );

                        $order->add_order_note( $admin_order_note );

                        function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

                        wc_add_notice( $notice, $notice_type );

                    }else {
                        if ( $payment_currency !== $order_currency ) {
                            $order->update_status( 'on-hold', '' );

                            update_post_meta( $order_id, '_paywithbank3d_transaction_id', $paywithbank3d_ref );

                            $notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'paywithbank3d-for-woocommerce' ), '<br />', '<br />', '<br />' );
                            $notice_type = 'notice';

                            // Add Customer Order Note
                            $order->add_order_note( $notice, 1 );

                            // Add Admin Order Note
                            $admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>PayWithBank3D Transaction Reference:</strong> %9$s', 'paywithbank3d-for-woocommerce' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $paywithbank3d_ref );
                            $order->add_order_note( $admin_order_note );

                            function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

                            wc_add_notice( $notice, $notice_type );
                        } else {
                            $order->payment_complete( $paywithbank3d_ref );
                            $order->add_order_note( sprintf( __( 'Payment via PayWithBank3D successful (Transaction Reference: %s)', 'paywithbank3d-for-woocommerce' ), $paywithbank3d_ref ) );
                        }
                    }
                    wc_empty_cart();
                } else {
                    $order_details = explode( '_', $paywithbank3d_ref);

                    $order_id = (int) $order_details[0];

                    $order = wc_get_order( $order_id );

                    $order->update_status( 'failed', __( 'Payment was declined by PayWithBank3D.', 'paywithbank3d-for-woocommerce' ) );
                }
            }
            wp_redirect( $this->get_return_url( $order ) );

            exit;
        }
        wp_redirect( wc_get_page_permalink( 'cart' ) );

        exit;
    }


    /**
     * Process Webhook.
     */
    public function process_webhooks() {
        if ( ( strtoupper( $_SERVER['REQUEST_METHOD'] ) != 'POST' )) {
            exit;
        }

        $json = file_get_contents( 'php://input' );
        $data = json_decode( $json );

        $merchantRef = sanitize_text_field($data->merchantRef);
        $currencyCode = sanitize_text_field($data->currencyCode);
        $amount = sanitize_text_field($data->amount);
        $paymentDate = sanitize_text_field($data->paymentDate);
        $code = sanitize_text_field($data->code);
        $hmac = sanitize_text_field($data->hmac);

        $toBeHashed = $merchantRef.$currencyCode.$amount;
        // validate event do all at once to avoid timing attack.
        if ( $hmac !== hash_hmac( 'sha256', $toBeHashed, $this->secret_key ) ) {
            exit;
        }

        if(!is_null($paymentDate) && '00' === $code){
            sleep( 10 );
            $order_details = explode( '_', $merchantRef );
            $order_id = (int) $order_details[0];
            $order = wc_get_order( $order_id );

            $paywithbank3d_txn_ref = get_post_meta( $order_id, '_paywithbank3d_txn_ref', true );

            if ( $merchantRef != $paywithbank3d_txn_ref ) {
                exit;
            }

            pwb3d_http_response_code( 200 );

            if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {
                exit;
            }

            $order_currency = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->get_order_currency();

            $currency_symbol = get_woocommerce_currency_symbol( $order_currency );

            $order_total = $order->get_total();

            $amount_paid = $amount / 100;

            $payment_currency = strtoupper( $currencyCode );

            $paywithbank3d_ref = $merchantRef;

            $gateway_symbol = get_woocommerce_currency_symbol( $payment_currency );

            if ( $amount_paid < $order_total ) {
                $order->update_status( 'on-hold', '' );
                add_post_meta( $order_id, '_transaction_id', $paywithbank3d_ref, true );
                $notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'paywithbank3d-for-woocommerce' ), '<br />', '<br />', '<br />' );
                $notice_type = 'notice';

                // Add Customer Order Note.
                $order->add_order_note( $notice, 1 );

                // Add Admin Order Note.
                $admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>PayWithBank3D Transaction Reference:</strong> %9$s', 'paywithbank3d-for-woocommerce' ), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $paywithbank3d_ref );

                $order->add_order_note( $admin_order_note );

                function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

                wc_add_notice( $notice, $notice_type );

                wc_empty_cart();

            } else {
                if ( $payment_currency !== $order_currency ) {
                    $order->update_status( 'on-hold', '' );
                    update_post_meta( $order_id, '_transaction_id', $paywithbank3d_ref );

                    $notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'paywithbank3d-for-woocommerce' ), '<br />', '<br />', '<br />' );
                    $notice_type = 'notice';

                    // Add Customer Order Note.
                    $order->add_order_note( $notice, 1 );

                    // Add Admin Order Note.
                    $admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Paystack Transaction Reference:</strong> %9$s', 'paywithbank3d-for-woocommerce' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $paywithbank3d_ref );
                    $order->add_order_note( $admin_order_note );

                    function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

                    wc_add_notice( $notice, $notice_type );
                } else {
                    $order->payment_complete( $paywithbank3d_ref );

                    $order->add_order_note( sprintf( __( 'Payment via PayWithBank3D successful (Transaction Reference: %s)', 'paywithbank3d-for-woocommerce' ), $paywithbank3d_ref ) );

                    wc_empty_cart();
                }
            }

            exit;
        }

    }

    public function payment_fields() {

        if ( $this->description ) {
            echo wpautop( wptexturize( $this->description ) );
        }

        if ( ! is_ssl() ) {
            return;
        }
    }

    public function process_payment( $order_id ) {
        if ( isset( $_POST[ 'wc-' . $this->id . '-payment-token' ] ) && 'new' !== $_POST[ 'wc-' . $this->id . '-payment-token' ] ) {
            $token_id = wc_clean( $_POST[ 'wc-' . $this->id . '-payment-token' ] );
            $token    = WC_Payment_Tokens::get( $token_id );

            if ( $token->get_user_id() !== get_current_user_id() ) {

                wc_add_notice( 'Invalid token ID', 'error' );

                return;

            } else {

                $status = $this->process_token_payment( $token->get_token(), $order_id );

                if ( $status ) {

                    $order = wc_get_order( $order_id );

                    return array(
                        'result'   => 'success',
                        'redirect' => $this->get_return_url( $order ),
                    );

                }
            }
        } else {

            $order = wc_get_order( $order_id );

            return array(
                'result'   => 'success',
                'redirect' => $order->get_checkout_payment_url( true ),
            );
        }
    }

    /*
     * Process Token Payment
     */
    public function process_token_payment( $token, $order_id ) {
        return true;
    }



    /**
     * Checks if WC version is less than passed in version.
     *
     * @param string $version Version to check against.
     *
     * @return bool
     */
    public static function is_wc_lt( $version ) {
        return version_compare( WC_VERSION, $version, '<' );
    }

    public static function log( $message, $level = 'info' ) {
        if ( self::$log_enabled ) {
            if ( empty( self::$log ) ) {
                self::$log = wc_get_logger();
            }
            self::$log->log( $level, $message, array( 'source' => 'paywithbank3d' ) );
        }
    }
}