<?php declare(strict_types=1);

namespace TrustPaymentsPayment\Core\Api\Configuration\Controller;

use Psr\Log\LoggerInterface;
use Shopware\Core\{
	Framework\Context,
	Framework\Routing\Annotation\RouteScope,};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\{
	HttpFoundation\JsonResponse,
	HttpFoundation\Request,
	HttpFoundation\Response,
	Routing\Annotation\Route};
use TrustPaymentsPayment\Core\{
	Api\OrderDeliveryState\Service\OrderDeliveryStateService,
	Api\PaymentMethodConfiguration\Service\PaymentMethodConfigurationService,
	Api\WebHooks\Service\WebHooksService,
	Util\PaymentMethodUtil};

/**
 * Class ConfigurationController
 *
 * This class handles web calls that are made via the TrustPaymentsPayment settings page.
 *
 * @package TrustPaymentsPayment\Core\Api\Config\Controller
 * @RouteScope(scopes={"api"})
 */
class ConfigurationController extends AbstractController {

	/**
	 * @var \TrustPaymentsPayment\Core\Api\WebHooks\Service\WebHooksService
	 */
	protected $webHooksService;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * @var \TrustPaymentsPayment\Core\Util\PaymentMethodUtil
	 */
	private $paymentMethodUtil;

	/**
	 * @var \TrustPaymentsPayment\Core\Api\PaymentMethodConfiguration\Service\PaymentMethodConfigurationService
	 */
	private $paymentMethodConfigurationService;

	/**
	 * ConfigurationController constructor.
	 *
	 * @param \TrustPaymentsPayment\Core\Util\PaymentMethodUtil                                                   $paymentMethodUtil
	 * @param \TrustPaymentsPayment\Core\Api\PaymentMethodConfiguration\Service\PaymentMethodConfigurationService $paymentMethodConfigurationService
	 * @param \TrustPaymentsPayment\Core\Api\WebHooks\Service\WebHooksService                                     $webHooksService
	 */
	public function __construct(
		PaymentMethodUtil $paymentMethodUtil,
		PaymentMethodConfigurationService $paymentMethodConfigurationService,
		WebHooksService $webHooksService
	)
	{
		$this->webHooksService   = $webHooksService;
		$this->paymentMethodUtil = $paymentMethodUtil;

		$this->paymentMethodConfigurationService = $paymentMethodConfigurationService;
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
	 * Set TrustPaymentsPayment as the default payment for a give sales channel
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Shopware\Core\Framework\Context          $context
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 *
	 * @Route(
	 *     "/api/v{version}/_action/trustpayments/configuration/set-trustpayments-as-sales-channel-payment-default",
	 *     name="api.action.trustpayments.configuration.set-trustpayments-as-sales-channel-payment-default",
	 *     methods={"POST"}
	 *     )
	 */
	public function setTrustPaymentsAsSalesChannelPaymentDefault(Request $request, Context $context): JsonResponse
	{
		$salesChannelId = $request->request->get('salesChannelId');
		$salesChannelId = ($salesChannelId == 'null') ? null : $salesChannelId;

		$this->paymentMethodUtil->setTrustPaymentsAsDefaultPaymentMethod($context, $salesChannelId);
		return new JsonResponse([]);
	}

	/**
	 * Register web hooks
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 * @throws \TrustPayments\Sdk\ApiException
	 * @throws \TrustPayments\Sdk\Http\ConnectionException
	 * @throws \TrustPayments\Sdk\VersioningException
	 *
	 * @Route(
	 *     "/api/v{version}/_action/trustpayments/configuration/register-web-hooks",
	 *     name="api.action.trustpayments.configuration.register-web-hooks",
	 *     methods={"POST"}
	 *   )
	 */
	public function registerWebHooks(Request $request): JsonResponse
	{
		$salesChannelId = $request->request->get('salesChannelId');
		$salesChannelId = ($salesChannelId == 'null') ? null : $salesChannelId;

		$result = $this->webHooksService->setSalesChannelId($salesChannelId)->install();

		return new JsonResponse(['result' => $result]);
	}

	/**
	 * Synchronize payment method configurations
	 *
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Shopware\Core\Framework\Context          $context
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 *
	 * @Route(
	 *     "/api/v{version}/_action/trustpayments/configuration/synchronize-payment-method-configuration",
	 *     name="api.action.trustpayments.configuration.synchronize-payment-method-configuration",
	 *     methods={"POST"}
	 *   )
	 */
	public function synchronizePaymentMethodConfiguration(Request $request, Context $context): JsonResponse
	{
		$salesChannelId = $request->request->get('salesChannelId');
		$salesChannelId = ($salesChannelId == 'null') ? null : $salesChannelId;
		$result         = [];
		$status         = Response::HTTP_OK;
		try {
			$result = $this->paymentMethodConfigurationService->setSalesChannelId($salesChannelId)->synchronize($context);
		} catch (\Exception $exception) {
			$status = Response::HTTP_NOT_ACCEPTABLE;
			$result = [
				'errorTitle' => $exception->getMessage(),
				'errorMessage' => $exception->getTraceAsString()
			];
			$this->logger->emergency($exception->getTraceAsString());
		}

		return new JsonResponse(['result' => $result], $status);
	}

	/**
	 * Install OrderDeliveryStates
	 *
	 * @param \Shopware\Core\Framework\Context $context
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 *
	 * @Route(
	 *     "/api/v{version}/_action/trustpayments/configuration/install-order-delivery-states",
	 *     name="api.action.trustpayments.configuration.install-order-delivery-states",
	 *     methods={"POST"}
	 *   )
	 */
	public function installOrderDeliveryStates(Context $context): JsonResponse
	{
		/**
		 * @var \TrustPaymentsPayment\Core\Api\OrderDeliveryState\Service\OrderDeliveryStateService $orderDeliveryStateService
		 */
		$orderDeliveryStateService = $this->container->get(OrderDeliveryStateService::class);
		$orderDeliveryStateService->install($context);

		return new JsonResponse([]);
	}
}