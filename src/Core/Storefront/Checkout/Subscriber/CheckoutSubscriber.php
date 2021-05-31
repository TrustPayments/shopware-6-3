<?php declare(strict_types=1);

namespace TrustPaymentsPayment\Core\Storefront\Checkout\Subscriber;

use Psr\Log\LoggerInterface;
use Shopware\Core\{
	Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates,
	Checkout\Order\OrderEntity,
	Content\MailTemplate\Service\Event\MailBeforeValidateEvent};
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TrustPaymentsPayment\Core\{
	Api\Transaction\Service\OrderMailService,
	Checkout\PaymentHandler\TrustPaymentsPaymentHandler,
	Settings\Service\SettingsService,
	Util\PaymentMethodUtil};

/**
 * Class CheckoutSubscriber
 *
 * @package TrustPaymentsPayment\Storefront\Checkout\Subscriber
 */
class CheckoutSubscriber implements EventSubscriberInterface {

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * @var \TrustPaymentsPayment\Core\Util\PaymentMethodUtil
	 */
	private $paymentMethodUtil;

	/**
	 * @var \TrustPaymentsPayment\Core\Settings\Service\SettingsService
	 */
	private $settingsService;

	/**
	 * CheckoutSubscriber constructor.
	 *
	 * @param \TrustPaymentsPayment\Core\Settings\Service\SettingsService $settingsService
	 * @param \TrustPaymentsPayment\Core\Util\PaymentMethodUtil           $paymentMethodUtil
	 */
	public function __construct(SettingsService $settingsService, PaymentMethodUtil $paymentMethodUtil)
	{
		$this->settingsService   = $settingsService;
		$this->paymentMethodUtil = $paymentMethodUtil;
	}

	/**
	 * @param \Psr\Log\LoggerInterface $logger
	 *
	 * @internal
	 * @required
	 *
	 */
	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			CheckoutConfirmPageLoadedEvent::class => ['onConfirmPageLoaded', 1],
			MailBeforeValidateEvent::class        => ['onMailBeforeValidate', 1],
		];
	}

	/**
	 * Stop order emails being sent out
	 *
	 * @param \Shopware\Core\Content\MailTemplate\Service\Event\MailBeforeValidateEvent $event
	 */
	public function onMailBeforeValidate(MailBeforeValidateEvent $event): void
	{
		$templateData = $event->getTemplateData();
		
		/**
		 * @var $order \Shopware\Core\Checkout\Order\OrderEntity
		 */
		$order = !empty($templateData['order']) && $templateData['order'] instanceof OrderEntity ? $templateData['order'] : null;

		if (!empty($order) && $order->getAmountTotal() > 0){

			$isTrustPaymentsEmailSettingEnabled = $this->settingsService->getSettings($order->getSalesChannelId())->isEmailEnabled();

			if (!$isTrustPaymentsEmailSettingEnabled) { //setting is disabled
				return;
			}

			$orderTransactionLast = $order->getTransactions()->last();
			if (empty($orderTransactionLast) || empty($orderTransactionLast->getPaymentMethod())) { // no payment method available
				return;
			}

			$isTrustPaymentsPM = TrustPaymentsPaymentHandler::class == $orderTransactionLast->getPaymentMethod()->getHandlerIdentifier();
			if (!$isTrustPaymentsPM) { // not our payment method
				return;
			}

			$isOrderTransactionStateOpen = in_array(
				$orderTransactionLast->getStateMachineState()->getTechnicalName(), [
				OrderTransactionStates::STATE_OPEN,
				OrderTransactionStates::STATE_IN_PROGRESS,
			]);

			if (!$isOrderTransactionStateOpen) { // order payment status is open or in progress
				return;
			}

			$isTrustPaymentsEmail = isset($templateData[OrderMailService::EMAIL_ORIGIN_IS_TRUSTPAYMENTS]);

			if (!$isTrustPaymentsEmail) {
				$this->logger->info('Email disabled for ', ['orderId' => $order->getId()]);
				$event->stopPropagation();
			}
		}
	}

	/**
	 * @param \Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent $event
	 */
	public function onConfirmPageLoaded(CheckoutConfirmPageLoadedEvent $event): void
	{
		try {
			$settings = $this->settingsService->getValidSettings($event->getSalesChannelContext()->getSalesChannel()->getId());
			if (is_null($settings)) {
				$this->logger->notice('Removing payment methods because settings are invalid');
				$this->removeTrustPaymentsPaymentMethodFromConfirmPage($event);
			}

		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());
			$this->removeTrustPaymentsPaymentMethodFromConfirmPage($event);
		}
	}

	/**
	 * @param \Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent $event
	 */
	private function removeTrustPaymentsPaymentMethodFromConfirmPage(CheckoutConfirmPageLoadedEvent $event): void
	{
		$paymentMethodCollection = $event->getPage()->getPaymentMethods();
		$paymentMethodIds        = $this->paymentMethodUtil->getTrustPaymentsPaymentMethodIds($event->getContext());
		foreach ($paymentMethodIds as $paymentMethodId) {
			$paymentMethodCollection->remove($paymentMethodId);
		}
	}
}