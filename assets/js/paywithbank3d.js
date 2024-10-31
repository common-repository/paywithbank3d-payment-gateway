jQuery( function( $ ) {
    //console.log('hi');
    let paywithbank3d_submit = false;
    jQuery( '#paywithbank3d-payment-button' ).click( function(e) {
        //e.preventDefault();
        return wcPayWithBank3DFormHandler();
    } );

    jQuery( '#paywithbank3d_form form#order_review' ).submit( function() {
        return wcPayWithBank3DFormHandler();
    } );

    function wcPayWithBank3DFormHandler() {
        if ( paywithbank3d_submit ) {
            paywithbank3d_submit = false;
            return true;
        }
        var $form = $( 'form#payment-form, form#order_review' ),
            paywithbank3d_txnref = $form.find( 'input.paywithbank3d_txnref' );
        //console.log(paywithbank3d_txnref);
        paywithbank3d_txnref.val( '' );
        const amount = Number( wc_paywithbank3d_params.amount );
        var paywithbank3d_callback = function( response ) {
            //console.log(response);
            $form.append( '<input type="hidden" class="paywithbank3d_txnref" name="paywithbank3d_txnref" value="' + response.reference + '"/>' );
            $( '#paywithbank3d_form a' ).hide();
            paywithbank3d_submit = true;

            $form.submit();
            $( 'body' ).block( {
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                },
                css: {
                    cursor: "wait"
                }
            } );
        };
        //console.log(wc_paywithbank3d_params);
        let payment = Bank3D.createPayment( {
            "reference": wc_paywithbank3d_params.txnref,
            "currencyCode": wc_paywithbank3d_params.currency,
            "merchantKey": wc_paywithbank3d_params.key,
            "amount": amount,
            "email": wc_paywithbank3d_params.email,
            "phone": wc_paywithbank3d_params.meta_phone,
            "color": wc_paywithbank3d_params.color,
            "mode": wc_paywithbank3d_params.mode,
            "callback" : paywithbank3d_callback,
            "onClose": function() {
                $( this.el ).unblock();
                location.reload();
            }
        } );
        //console.log('me');
        payment.open();
        return false;

    }
} );