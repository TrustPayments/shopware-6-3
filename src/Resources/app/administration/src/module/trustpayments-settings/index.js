/* global Shopware */

import './extension/sw-plugin';
import './extension/sw-settings-index';
import './page/trustpayments-settings';
import './component/sw-trustpayments-credentials';
import './component/sw-trustpayments-options';
import './component/sw-trustpayments-storefront-options';
import enGB from './snippet/en-GB.json';
import deDE from './snippet/de-DE.json';

const {Module} = Shopware;

Module.register('trustpayments-settings', {
	type: 'plugin',
	name: 'TrustPayments',
	title: 'trustpayments-settings.general.descriptionTextModule',
	description: 'trustpayments-settings.general.descriptionTextModule',
	color: '#62ff80',
	icon: 'default-action-settings',

	snippets: {
		'de-DE': deDE,
		'en-GB': enGB
	},

	routes: {
		index: {
			component: 'trustpayments-settings',
			path: 'index',
			meta: {
				parentPath: 'sw.settings.index'
			}
		}
	}

});
