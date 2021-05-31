<?php declare(strict_types=1);


namespace TrustPaymentsPayment\Core\Api\PaymentMethodConfiguration\Command;

use Shopware\Core\Framework\Context;
use Symfony\Component\{
	Console\Command\Command,
	Console\Input\InputInterface,
	Console\Output\OutputInterface};
use TrustPaymentsPayment\Core\Util\PaymentMethodUtil;

/**
 * Class PaymentMethodDefaultCommand
 *
 * @package TrustPaymentsPayment\Core\Api\PaymentMethodConfiguration\Command
 */
class PaymentMethodDefaultCommand extends Command {

	/**
	 * @var string
	 */
	protected static $defaultName = 'trustpayments:payment-method:default';

	/**
	 * @var \TrustPaymentsPayment\Core\Util\PaymentMethodUtil
	 */
	protected $paymentMethodUtil;

	/**
	 * PaymentMethodDefaultCommand constructor.
	 *
	 * @param \TrustPaymentsPayment\Core\Util\PaymentMethodUtil $paymentMethodUtil
	 */
	public function __construct(PaymentMethodUtil $paymentMethodUtil)
	{
		parent::__construct(self::$defaultName);
		$this->paymentMethodUtil = $paymentMethodUtil;
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output->writeln('Set TrustPaymentsPayment as default payment method...');
		$context = Context::createDefaultContext();
		$this->paymentMethodUtil->setTrustPaymentsAsDefaultPaymentMethod($context);
		$this->paymentMethodUtil->disableSystemPaymentMethods($context);
		return 0;
	}

	/**
	 * Configures the current command.
	 */
	protected function configure()
	{
		$this->setDescription('Sets TrustPaymentsPayment as default payment method.')
			 ->setHelp('This command updates TrustPaymentsPayment as default payment method for all SalesChannels.');
	}

}