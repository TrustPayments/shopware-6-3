<?php declare(strict_types=1);

namespace TrustPaymentsPayment;

use Shopware\Core\{
	Framework\Plugin,
	Framework\Plugin\Context\ActivateContext,
	Framework\Plugin\Context\DeactivateContext,
	Framework\Plugin\Context\UninstallContext,
	Framework\Plugin\Context\UpdateContext
};
use TrustPaymentsPayment\Core\{
	Api\WebHooks\Service\WebHooksService,
	Util\Traits\TrustPaymentsPaymentPluginTrait
};


// expect the vendor folder on Shopware store releases
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
	require_once dirname(__DIR__) . '/vendor/autoload.php';
}

/**
 * Class TrustPaymentsPayment
 *
 * @package TrustPaymentsPayment
 */
class TrustPaymentsPayment extends Plugin {

	use TrustPaymentsPaymentPluginTrait;

	/**
	 * @param \Shopware\Core\Framework\Plugin\Context\UninstallContext $uninstallContext
	 */
	public function uninstall(UninstallContext $uninstallContext): void
	{
		parent::uninstall($uninstallContext);
		$this->disablePaymentMethods($uninstallContext->getContext());
		$this->removeConfiguration($uninstallContext->getContext());
		$this->deleteUserData($uninstallContext);
	}

	/**
	 * @param \Shopware\Core\Framework\Plugin\Context\ActivateContext $activateContext
	 */
	public function activate(ActivateContext $activateContext): void
	{
		parent::activate($activateContext);
		$this->enablePaymentMethods($activateContext->getContext());
	}

	/**
	 * @param \Shopware\Core\Framework\Plugin\Context\DeactivateContext $deactivateContext
	 */
	public function deactivate(DeactivateContext $deactivateContext): void
	{
		parent::deactivate($deactivateContext);
		$this->disablePaymentMethods($deactivateContext->getContext());
	}


	/**
	 * @param \Shopware\Core\Framework\Plugin\Context\UpdateContext $updateContext
	 *
	 * @throws \TrustPayments\Sdk\ApiException
	 * @throws \TrustPayments\Sdk\Http\ConnectionException
	 * @throws \TrustPayments\Sdk\VersioningException
	 */
	public function postUpdate(UpdateContext $updateContext): void
	{
		parent::postUpdate($updateContext);
		/**
		 * @var \TrustPaymentsPayment\Core\Api\WebHooks\Service\WebHooksService $webHooksService
		 */
		$webHooksService = $this->container->get(WebHooksService::class);
		$webHooksService->install();
	}

}