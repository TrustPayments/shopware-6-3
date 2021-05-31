/* global Shopware */

const ApiService = Shopware.Classes.ApiService;

/**
 * @class TrustPaymentsPayment\Core\Api\Transaction\Controller\RefundController
 */
class TrustPaymentsRefundService extends ApiService {

	/**
	 * TrustPaymentsRefundService constructor
	 *
	 * @param httpClient
	 * @param loginService
	 * @param apiEndpoint
	 */
	constructor(httpClient, loginService, apiEndpoint = 'trustpayments') {
		super(httpClient, loginService, apiEndpoint);
	}

	/**
	 * Refund a transaction
	 *
	 * @param {String} salesChannelId
	 * @param {int} transactionId
	 * @param {float} refundableAmount
	 * @return {*}
	 */
	createRefund(salesChannelId, transactionId, refundableAmount) {

		const headers = this.getBasicHeaders();
		const apiRoute = `_action/${this.getApiBasePath()}/refund/create-refund/`;

		return this.httpClient.post(
			apiRoute,
			{
				salesChannelId: salesChannelId,
				transactionId: transactionId,
				refundableAmount: refundableAmount
			},
			{
				headers: headers
			}
		).then((response) => {
			return ApiService.handleResponse(response);
		});
	}
}

export default TrustPaymentsRefundService;