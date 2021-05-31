<?php declare(strict_types=1);

namespace TrustPaymentsPayment\Core\Storefront\Account\Subscriber;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\Account\Order\AccountOrderPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TrustPaymentsPayment\Core\Settings\Service\SettingsService;

/**
 * Class AccountOrderSubscriber
 *
 * @package TrustPaymentsPayment\Core\Storefront\Account\Subscriber
 */
class AccountOrderSubscriber implements EventSubscriberInterface {

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * @var \TrustPaymentsPayment\Core\Settings\Service\SettingsService
	 */
	private $settingsService;

	/**
	 * CheckoutSubscriber constructor.
	 *
	 * @param \TrustPaymentsPayment\Core\Settings\Service\SettingsService $settingsService
	 */
	public function __construct(SettingsService $settingsService)
	{
		$this->settingsService = $settingsService;
	}

	/**
	 * @param \Psr\Log\LoggerInterface $logger
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
			AccountOrderPageLoadedEvent::class => ['onAccountOrderPageLoaded', 1],
		];
	}


	/**
	 * Pass settings to template
	 *
	 * @param \Shopware\Storefront\Page\Account\Order\AccountOrderPageLoadedEvent $event
	 */
	public function onAccountOrderPageLoaded(AccountOrderPageLoadedEvent $event): void
	{
		$trustpaymentsSettings = new ArrayStruct();
		$trustpaymentsSettings->set(SettingsService::CONFIG_STOREFRONT_INVOICE_DOWNLOAD_ENABLED, false);
		try {
			$settings = $this->settingsService->getValidSettings($event->getSalesChannelContext()->getSalesChannel()->getId());
			if (is_null($settings)) {
				$this->logger->notice('Disabling invoice downloads');
			} else {
				$trustpaymentsSettings->set(SettingsService::CONFIG_STOREFRONT_INVOICE_DOWNLOAD_ENABLED, $settings->isStorefrontInvoiceDownloadEnabled());
			}

		} catch (\Exception $e) {
			$this->logger->error($e->getMessage());
		}

		$event->getPage()->addExtension('trustpaymentsSettings', $trustpaymentsSettings);
	}
}