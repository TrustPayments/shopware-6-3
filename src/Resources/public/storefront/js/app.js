/* global window */
// noinspection ThisExpressionReferencesGlobalObjectJS
(function (window) {
    /**
     * TrustPaymentsCheckout
     * @type {
     *      {
     *          payment_method_handler_name: string,
     *          payment_method_iframe_class: string,
     *          init: init,
     *          validationCallBack: validationCallBack,
     *          payment_method_handler_status: string,
     *          submitPayment: (function(*): boolean),
     *          payment_method_iframe_prefix: string,
     *          payment_form_id: string,
     *          payment_method_handler_prefix: string,
     *          payment_method_tabs: string,
     *          getIframe: (function(): boolean
     *      }
     * }
     */
    const TrustPaymentsCheckout = {
        /**
         * Variables
         */
        payment_panel_id: 'trustpayments-payment-panel',
        payment_method_iframe_id: 'trustpayments-payment-iframe',
        payment_method_handler_name: 'trustpayments_payment_handler',
        payment_method_handler_status: 'input[name="trustpayments_payment_handler_validation_status"]',
        payment_form_id: 'confirmOrderForm',
        button_cancel_id: 'trustpaymentsOrderCancel',
        loader_id: 'trustpaymentsLoader',
        checkout_url: null,
        checkout_url_id: 'checkoutUrl',
        cart_recreate_url: null,
        cart_recreate_url_id: 'cartRecreateUrl',
        handler: null,

        /**
         * Initialize plugin
         */
        init: function () {
            TrustPaymentsCheckout.activateLoader(true);
            this.checkout_url = document.getElementById(this.checkout_url_id).value;
            this.cart_recreate_url = document.getElementById(this.cart_recreate_url_id).value;

            document.getElementById(this.button_cancel_id).addEventListener('click', this.recreateCart, false);
            document.getElementById(this.payment_form_id).addEventListener('submit', this.submitPayment, false);

            TrustPaymentsCheckout.getIframe();
        },

        activateLoader: function (activate) {
            const buttons = document.querySelectorAll('button');
            if (activate) {
                for (let i = 0; i < buttons.length; i++) {
                    buttons[i].disabled = true;
                }
            } else {
                for (let i = 0; i < buttons.length; i++) {
                    buttons[i].disabled = false;
                }
            }
        },

        recreateCart: function (e) {
            window.location.href = TrustPaymentsCheckout.cart_recreate_url;
            e.preventDefault();
        },

        /**
         * Submit form
         *
         * @param event
         * @return {boolean}
         */
        submitPayment: function (event) {
            TrustPaymentsCheckout.activateLoader(true);
            TrustPaymentsCheckout.handler.validate();
            event.preventDefault();
            return false;
        },

        /**
         * Get iframe
         */
        getIframe: function () {
            const paymentPanel = document.getElementById(TrustPaymentsCheckout.payment_panel_id);
            const paymentMethodConfigurationId = paymentPanel.dataset.id;
            const iframeContainer = document.getElementById(TrustPaymentsCheckout.payment_method_iframe_id);

            if (!TrustPaymentsCheckout.handler) { // iframe has not been loaded yet
                // noinspection JSUnresolvedFunction
                TrustPaymentsCheckout.handler = window.IframeCheckoutHandler(paymentMethodConfigurationId);
                // noinspection JSUnresolvedFunction
                TrustPaymentsCheckout.handler.setValidationCallback((validationResult) => {
                    TrustPaymentsCheckout.hideErrors();
                    TrustPaymentsCheckout.validationCallBack(validationResult);
                });
                TrustPaymentsCheckout.handler.setInitializeCallback(() => {
                    let loader = document.getElementById(TrustPaymentsCheckout.loader_id);
                    loader.parentNode.removeChild(loader);
                    TrustPaymentsCheckout.activateLoader(false);
                });
                TrustPaymentsCheckout.handler.setHeightChangeCallback((height)=>{
                    if(height < 1){ // iframe has no fields
                        TrustPaymentsCheckout.handler.submit();
                    }
                });
                TrustPaymentsCheckout.handler.create(iframeContainer);
            }
        },

        /**
         * validation callback
         * @param validationResult
         */
        validationCallBack: function (validationResult) {
            if (validationResult.success) {
                document.querySelector(this.payment_method_handler_status).value = true;
                TrustPaymentsCheckout.handler.submit();
            } else {
                document.body.scrollTop = 0;
                document.documentElement.scrollTop = 0;

                if (validationResult.errors) {
                    TrustPaymentsCheckout.showErrors(validationResult.errors);
                }
                document.querySelector(this.payment_method_handler_status).value = false;
                TrustPaymentsCheckout.activateLoader(false);
            }
        },

        showErrors: function(errors) {
            let alert = document.createElement('div');
            alert.setAttribute('class', 'alert alert-danger');
            alert.setAttribute('role', 'alert');
            alert.setAttribute('id', 'trustpayments-errors');
            document.getElementsByClassName('flashbags')[0].appendChild(alert);

            let alertContentContainer = document.createElement('div');
            alertContentContainer.setAttribute('class', 'alert-content-container');
            alert.appendChild(alertContentContainer);

            let alertContent = document.createElement('div');
            alertContent.setAttribute('class', 'alert-content');
            alertContentContainer.appendChild(alertContent);

            if (errors.length > 1) {
                let alertList = document.createElement('ul');
                alertList.setAttribute('class', 'alert-list');
                alertContent.appendChild(alertList);
                for (let index = 0; index < errors.length; index++) {
                    let alertListItem = document.createElement('li');
                    alertListItem.textContent = errors[index];
                    alertList.appendChild(alertListItem);
                }
            } else {
                alertContent.textContent = errors[0];
            }
        },

        hideErrors: function() {
            let errorElement = document.getElementById('trustpayments-errors');
            if (errorElement) {
                errorElement.parentNode.removeChild(errorElement);
            }
        }
    };

    window.TrustPaymentsCheckout = TrustPaymentsCheckout;

}(typeof window !== "undefined" ? window : this));

/**
 * Vanilla JS over JQuery
 */
window.addEventListener('load', function (e) {
    TrustPaymentsCheckout.init();
    window.history.pushState({}, document.title, TrustPaymentsCheckout.cart_recreate_url);
    window.history.pushState({}, document.title, TrustPaymentsCheckout.checkout_url);
}, false);

/**
 * This only works if the user has interacted with the page
 * @link https://stackoverflow.com/questions/57339098/chrome-popstate-not-firing-on-back-button-if-no-user-interaction
 */
window.addEventListener('popstate', function (e) {
    if (window.history.state == null) { // This means it's page load
        return;
    }
    window.location.href = TrustPaymentsCheckout.cart_recreate_url;
}, false);