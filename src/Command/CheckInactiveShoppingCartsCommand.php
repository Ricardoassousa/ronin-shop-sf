<?php

namespace App\Command;

use App\Entity\Cart;
use App\Logger\CartLogger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * Command to check and expire inactive shopping carts older than 30 days.
 */
class CheckInactiveShoppingCartsCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:check-inactive-shopping-carts';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Checks inactive shopping carts and expires them if they are older than 30 days.';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var CartLogger
     */
    private $cartLogger;

    /**
     *
     */
    public function __construct(EntityManagerInterface $em, CartLogger $cartLogger)
    {
        parent::__construct();
        $this->em = $em;
        $this->cartLogger = $cartLogger;
    }

    /**
     * Configures the command by setting any arguments or options.
     */
    protected function configure(): void
    {
        // No need to add extra arguments or options
    }

    /**
     * Executes the command.
     *
     * This method checks for active shopping carts that are older than 30 days
     * and marks them as expired. It persists these changes in the database.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $shoppingCarts = $this->em->getRepository(Cart::class)->findInactiveCartsOlderThan30Days();

            if (empty($shoppingCarts)) {
                $io->success('No active carts to expire.');
                $this->cartLogger->log(
                    'No inactive carts to expire',
                    [
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::INFO
                );
                return Command::SUCCESS;
            }

            $expiredCartsCount = 0;
            foreach ($shoppingCarts as $cart) {
                $cart->setStatus(Cart::STATUS_EXPIRED);
                $this->em->persist($cart);
                $expiredCartsCount++;
            }

            $this->em->flush();

            $io->success(sprintf('%d inactive shopping carts older than 30 days have been expired.', $expiredCartsCount));

            $this->cartLogger->log(
                'Expired inactive carts',
                [
                    'expired_count' => $expiredCartsCount,
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            return Command::SUCCESS;

        } catch (Throwable $e) {
            $this->cartLogger->log(
                'Unexpected error in CheckInactiveShoppingCartsCommand',
                [
                    'exception' => $e
                ],
                LogLevel::ERROR
            );

            $io->error('An unexpected error occurred while expiring carts.');

            return Command::FAILURE;
        }
    }

}