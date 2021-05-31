/* global Shopware */

import TrustPaymentsConfigurationService from '../core/service/api/trustpayments-configuration.service';
import TrustPaymentsRefundService from '../core/service/api/trustpayments-refund.service';
import TrustPaymentsTransactionService from '../core/service/api/trustpayments-transaction.service';
import TrustPaymentsTransactionCompletionService
	from '../core/service/api/trustpayments-transaction-completion.service';
import TrustPaymentsTransactionVoidService
	from '../core/service/api/trustpayments-transaction-void.service';


const {Application} = Shopware;

// noinspection JSUnresolvedFunction
Application.addServiceProvider('TrustPaymentsConfigurationService', (container) => {
	const initContainer = Application.getContainer('init');
	return new TrustPaymentsConfigurationService(initContainer.httpClient, container.loginService);
});

// noinspection JSUnresolvedFunction
Application.addServiceProvider('TrustPaymentsRefundService', (container) => {
	const initContainer = Application.getContainer('init');
	return new TrustPaymentsRefundService(initContainer.httpClient, container.loginService);
});

// noinspection JSUnresolvedFunction
Application.addServiceProvider('TrustPaymentsTransactionService', (container) => {
	const initContainer = Application.getContainer('init');
	return new TrustPaymentsTransactionService(initContainer.httpClient, container.loginService);
});

// noinspection JSUnresolvedFunction
Application.addServiceProvider('TrustPaymentsTransactionCompletionService', (container) => {
	const initContainer = Application.getContainer('init');
	return new TrustPaymentsTransactionCompletionService(initContainer.httpClient, container.loginService);
});

// noinspection JSUnresolvedFunction
Application.addServiceProvider('TrustPaymentsTransactionVoidService', (container) => {
	const initContainer = Application.getContainer('init');
	return new TrustPaymentsTransactionVoidService(initContainer.httpClient, container.loginService);
});