<?php declare(strict_types=1);


namespace TrustPaymentsPayment\Core\Api\WebHooks\Command;

use Symfony\Component\{
	Console\Command\Command,
	Console\Input\InputInterface,
	Console\Output\OutputInterface};
use TrustPaymentsPayment\Core\Api\WebHooks\Service\WebHooksService;

/**
 * Class WebHooksCommand
 *
 * @package TrustPaymentsPayment\Core\Api\WebHooks\Command
 */
class WebHooksCommand extends Command {

	/**
	 * @var string
	 */
	protected static $defaultName = 'trustpayments:webhooks:install';

	/**
	 * @var \TrustPaymentsPayment\Core\Api\WebHooks\Service\WebHooksService
	 */
	protected $webHooksService;

	/**
	 * WebHooksCommand constructor.
	 *
	 * @param \TrustPaymentsPayment\Core\Api\WebHooks\Service\WebHooksService $webHooksService
	 */
	public function __construct(WebHooksService $webHooksService)
	{
		parent::__construct(self::$defaultName);
		$this->webHooksService = $webHooksService;
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface   $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 *
	 * @return int
	 * @throws \TrustPayments\Sdk\ApiException
	 * @throws \TrustPayments\Sdk\Http\ConnectionException
	 * @throws \TrustPayments\Sdk\VersioningException
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$output->writeln('Install TrustPaymentsPayment webhooks...');
		$this->webHooksService->install();
		return 0;
	}

	/**
	 * Configures the current command.
	 */
	protected function configure()
	{
		$this->setDescription('Install TrustPaymentsPayment webhooks.')
			 ->setHelp('This command installs TrustPaymentsPayment webhooks.');
	}

}