// Import all necessary Storefront plugins and scss files
import TrustPaymentsCheckoutPlugin
    from './trustpayments-checkout-plugin/trustpayments-checkout-plugin.plugin';

// Register them via the existing PluginManager
const PluginManager = window.PluginManager;
PluginManager.register(
    'TrustPaymentsCheckoutPlugin',
    TrustPaymentsCheckoutPlugin,
    '[data-trustpayments-checkout-plugin]'
);

if (module.hot) {
    // noinspection JSValidateTypes
    module.hot.accept();
}