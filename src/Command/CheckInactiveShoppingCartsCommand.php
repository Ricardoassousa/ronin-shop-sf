<?php

namespace App\Command;

use App\Entity\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
     *
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
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

        // Find all active carts that are older than 30 days
        $shoppingCarts = $this->em->getRepository(Cart::class)->findInactiveCartsOlderThan30Days();

        // Check if there are any carts to expire
        if (empty($shoppingCarts)) {
            $io->success('No active carts to expire.');
            return Command::SUCCESS;
        }

        // Change the status of each cart to "expired" and persist
        $expiredCartsCount = 0;
        foreach ($shoppingCarts as $cart) {
            $cart->setStatus(Cart::STATUS_EXPIRED);
            $this->em->persist($cart); // Mark the cart for update in the database
            $expiredCartsCount++;
        }

        // Save all changes to the database
        $this->em->flush();

        // Show a success message
        $io->success(sprintf('%d inactive shopping carts older than 30 days have been expired.', $expiredCartsCount));

        return Command::SUCCESS;
    }

}