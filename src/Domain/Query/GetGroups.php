<?php declare(strict_types=1);

namespace App\Domain\Query;

use App\Domain\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;

class GetGroups
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return Group[]
     */
    public function execute(): array
    {
        return $this->em->getRepository(Group::class)->findAll();
    }
}