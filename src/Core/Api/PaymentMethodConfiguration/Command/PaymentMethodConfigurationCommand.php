<?php declare(strict_types=1);


namespace TrustPaymentsPayment\Core\Api\PaymentMethodConfiguration\Command;

use Shopware\Core\Framework\Context;
use Symfony\Component\{
	Console\Command\Command,
	Console\Input\InputInterface,
	Console\Output\OutputInterface};
use TrustPaymentsPayment\Core\Api\PaymentMethodConfiguration\Service\PaymentMethodConfigurationService;

/**
 * Class PaymentMethodConfigurationCommand
 *
 * @package TrustPaymentsPayment\Core\Api\PaymentMethodConfiguration\Command
 */
class PaymentMethodConfigurationCommand extends Command {

	/**
	 * @var string
	 */
	protected static $defaultName = 'trustpayments:payment-method:configuration';

	/**
	 * @var \TrustPaymentsPayment\Core\Api\PaymentMethodConfiguration\Service\PaymentMethodConfigurationService
	 */
	protected $paymentMethodConfigurationService;

	/**
	 * PaymentMethodConfigurationCommand constructor.
	 *
	 * @param \TrustPaymentsPayment\Core\Api\PaymentMethodConfiguration\Service\PaymentMethodConfigurationService $paymentMethodConfigurationService
	 */
	public function __construct(PaymentMethodConfigurationService $paymentMethodConfigurationService)
	{
		parent::__construct(self::$defaultName);
		$this->paymentMethodConfigurationService = $paymentMethodConfigurationService;
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return int
	 * @throws \TrustPayments\Sdk\ApiException
	 * @throws \TrustPayments\Sdk\Http\ConnectionException
	 * @throws \TrustPayments\Sdk\VersioningException
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output->writeln('Fetch TrustPaymentsPayment space available payment methods...');
		$this->paymentMethodConfigurationService->synchronize(Context::createDefaultContext());
		return 0;
	}

	/**
	 * Configures the current command.
	 */
	protected function configure()
	{
		$this->setDescription('Fetches TrustPaymentsPayment space available payment methods.')
			 ->setHelp('This command fetches TrustPaymentsPayment space available payment methods.');
	}

}