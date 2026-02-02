<?php

namespace App\Service;

use App\Entity\Product;
use App\Logger\AnalyticsLogger;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Exception\LogicException;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Service responsible for generating unique slugs for products.
 *
 * This service ensures that each product slug is unique in the database.
 * It also logs all operations for analytics purposes, including conflicts and final results.
 */
class SlugGenerator
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SluggerInterface
     */
    private $slugger;

    /**
     * @var AnalyticsLogger
     */
    private $analyticsLogger;

    /**
     * SlugGenerator constructor.
     *
     * @param EntityManagerInterface $em
     * @param SluggerInterface $slugger
     * @param AnalyticsLogger $analyticsLogger
     */
    public function __construct(EntityManagerInterface $em, SluggerInterface $slugger, AnalyticsLogger $analyticsLogger)
    {
        $this->em = $em;
        $this->slugger = $slugger;
        $this->analyticsLogger = $analyticsLogger;
    }

    /**
     * Generates a unique slug for a given product name.
     *
     * The method will attempt to generate a slug and, if a conflict is found in the database,
     * it will append a numeric suffix. If a unique slug cannot be generated after a maximum
     * number of attempts, a LogicException is thrown.
     *
     * @param string $name
     * @return string
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function generate(string $name): string
    {
        if (empty(trim($name))) {
            $this->analyticsLogger->log(
                'Cannot generate slug for empty name',
                [
                    'base_name' => $name,
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::ERROR
            );

            throw new InvalidArgumentException('Product name cannot be empty.');
        }

        $baseSlug = $this->slugger->slug($name)->lower();
        $slug = $baseSlug;
        $index = 1;
        $maxAttempts = 50;

        $this->analyticsLogger->log(
            'Generating slug for product',
            [
                'base_name' => $name,
                'initial_slug' => $baseSlug,
                'source' => [
                    'method' => __METHOD__,
                    'line' => __LINE__
                ]
            ],
            LogLevel::INFO
        );

        while ($this->em->getRepository(Product::class)->findOneBySlug($slug)) {
            $slug = $baseSlug . '-' . $index;
            $index++;

            $this->analyticsLogger->log(
                'Slug conflict found, trying new slug',
                [
                    'attempt_slug' => $slug,
                    'base_slug' => $baseSlug,
                    'index' => $index - 1,
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::DEBUG
            );

            if ($index > $maxAttempts) {
                $this->analyticsLogger->log(
                    'Failed to generate unique slug after maximum attempts',
                    [
                        'base_name' => $name,
                        'max_attempts' => $maxAttempts,
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::ERROR
                );

                throw new LogicException('Cannot generate a unique slug for this product name.');
            }
        }

        $this->analyticsLogger->log(
            'Slug successfully generated',
            [
                'final_slug' => $slug,
                'source' => [
                    'method' => __METHOD__,
                    'line' => __LINE__
                ]
            ],
            LogLevel::INFO
        );

        return $slug;
    }

}