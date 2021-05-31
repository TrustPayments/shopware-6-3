/* global Shopware */

import '../../component/trustpayments-order-action-completion';
import '../../component/trustpayments-order-action-refund';
import '../../component/trustpayments-order-action-void';
import template from './index.html.twig';
import './index.scss';

const {Component, Mixin, Filter, Context, Utils} = Shopware;
const Criteria = Shopware.Data.Criteria;

Component.register('trustpayments-order-detail', {
	template,

	inject: [
		'TrustPaymentsTransactionService',
		'repositoryFactory'
	],

	mixins: [
		Mixin.getByName('notification')
	],

	data() {
		return {
			transactionData: {
				transactions: [],
				refunds: []
			},
			transaction: {},
			lineItems: [],
			currency: '',
			modalType: '',
			refundAmount: 0,
			refundableAmount: 0,
			isLoading: true,
			orderId: ''
		};
	},

	metaInfo() {
		return {
			title: this.$tc('trustpayments-order.header')
		};
	},


	computed: {
		dateFilter() {
			return Filter.getByName('date');
		},

		relatedResourceColumns() {
			return [
				{
					property: 'paymentConnectorConfiguration.name',
					label: this.$tc('trustpayments-order.transactionHistory.types.payment_method'),
					rawData: true
				},
				{
					property: 'state',
					label: this.$tc('trustpayments-order.transactionHistory.types.state'),
					rawData: true
				},
				{
					property: 'currency',
					label: this.$tc('trustpayments-order.transactionHistory.types.currency'),
					rawData: true
				},
				{
					property: 'authorized_amount',
					label: this.$tc('trustpayments-order.transactionHistory.types.authorized_amount'),
					rawData: true
				},
				{
					property: 'id',
					label: this.$tc('trustpayments-order.transactionHistory.types.transaction'),
					rawData: true
				},
				{
					property: 'customerId',
					label: this.$tc('trustpayments-order.transactionHistory.types.customer'),
					rawData: true
				}
			];
		},

		lineItemColumns() {
			return [
				{
					property: 'uniqueId',
					label: this.$tc('trustpayments-order.lineItem.types.uniqueId'),
					rawData: true,
					visible: false,
					primary: true
				},
				{
					property: 'name',
					label: this.$tc('trustpayments-order.lineItem.types.name'),
					rawData: true
				},
				{
					property: 'quantity',
					label: this.$tc('trustpayments-order.lineItem.types.quantity'),
					rawData: true
				},
				{
					property: 'amountIncludingTax',
					label: this.$tc('trustpayments-order.lineItem.types.amountIncludingTax'),
					rawData: true
				},
				{
					property: 'type',
					label: this.$tc('trustpayments-order.lineItem.types.type'),
					rawData: true
				},
				{
					property: 'taxAmount',
					label: this.$tc('trustpayments-order.lineItem.types.taxAmount'),
					rawData: true
				}
			];
		},

		refundColumns() {
			return [
				{
					property: 'id',
					label: this.$tc('trustpayments-order.refund.types.id'),
					rawData: true,
					visible: true,
					primary: true
				},
				{
					property: 'amount',
					label: this.$tc('trustpayments-order.refund.types.amount'),
					rawData: true
				},
				{
					property: 'state',
					label: this.$tc('trustpayments-order.refund.types.state'),
					rawData: true
				},
				{
					property: 'createdOn',
					label: this.$tc('trustpayments-order.refund.types.createdOn'),
					rawData: true
				}
			];
		}
	},

	watch: {
		'$route'() {
			this.resetDataAttributes();
			this.createdComponent();
		}
	},

	created() {
		this.createdComponent();
	},

	methods: {
		createdComponent() {
			this.orderId = this.$route.params.id;
			const orderRepository = this.repositoryFactory.create('order');
			const orderCriteria = new Criteria(1, 1);
			orderCriteria.addAssociation('transactions');

			orderRepository.get(this.orderId, Context.api, orderCriteria).then((order) => {
				this.order = order;
				this.isLoading = false;
				const trustpaymentsTransactionId = order.transactions[0].customFields.trustpayments_transaction_id;
				this.TrustPaymentsTransactionService.getTransactionData(order.salesChannelId, trustpaymentsTransactionId)
					.then((TrustPaymentsTransaction) => {
						this.currency = TrustPaymentsTransaction.transactions[0].currency;
						TrustPaymentsTransaction.transactions[0].authorized_amount = Utils.format.currency(
							TrustPaymentsTransaction.transactions[0].authorizationAmount,
							this.currency
						);
						TrustPaymentsTransaction.transactions[0].lineItems.forEach((lineItem) => {
							lineItem.amountIncludingTax = Utils.format.currency(
								lineItem.amountIncludingTax,
								this.currency
							);
							lineItem.taxAmount = Utils.format.currency(
								lineItem.taxAmount,
								this.currency
							);
						});
						TrustPaymentsTransaction.refunds.forEach((refund) => {
							refund.amount = Utils.format.currency(
								refund.amount,
								this.currency
							);
						});
						this.lineItems = TrustPaymentsTransaction.transactions[0].lineItems;
						this.transactionData = TrustPaymentsTransaction;
						this.refundAmount = Number(this.transactionData.transactions[0].amountIncludingTax);
						this.refundableAmount = Number(this.transactionData.transactions[0].amountIncludingTax);
						this.transaction = this.transactionData.transactions[0];
					}).catch((errorResponse) => {
					try {
						this.createNotificationError({
							title: this.$tc('trustpayments-order.paymentDetails.error.title'),
							message: errorResponse.message,
							autoClose: false
						});
					} catch (e) {
						this.createNotificationError({
							title: this.$tc('trustpayments-order.paymentDetails.error.title'),
							message: errorResponse.message,
							autoClose: false
						});
					} finally {
						this.isLoading = false;
					}
				});
			});
		},
		downloadPackingSlip() {
			window.open(
				this.TrustPaymentsTransactionService.getPackingSlip(
					Shopware.Context.api,
					this.transaction.metaData.salesChannelId,
					this.transaction.id
				),
				'_blank'
			);
		},

		downloadInvoice() {
			window.open(
				this.TrustPaymentsTransactionService.getInvoiceDocument(
					Shopware.Context.api,
					this.transaction.metaData.salesChannelId,
					this.transaction.id
				),
				'_blank'
			);
		},

		resetDataAttributes() {
			this.transactionData = {
				transactions: [],
				refunds: []
			};
			this.lineItems = [];
			this.isLoading = true;
		},

		spawnModal(modalType) {
			this.modalType = modalType;
		},

		closeModal() {
			this.modalType = '';
		}
	}
});
