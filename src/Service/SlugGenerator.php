<?php

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

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
     *
     */
    public function __construct(EntityManagerInterface $em, SluggerInterface $slugger)
    {
        $this->em = $em;
        $this->slugger = $slugger;
    }

    public function generate(string $name): string
    {
        $baseSlug = $this->slugger->slug($name)->lower();
        $slug = $baseSlug;
        $index = 1;

        while ($this->em->getRepository(Product::class)->findOneBySlug($slug)) {
            $slug = $baseSlug . '-' . $index;
            $index++;
        }

        return $slug;
    }

}