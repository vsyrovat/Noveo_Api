<?php declare(strict_types=1);

namespace App\Persistence\Repository;

use App\Domain\Entity\Group;
use App\Domain\Exception\GroupNotFound;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Group find($id, $lockMode = null, $lockVersion = null)
 */
class GroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    public function findOrThrow(int $id): Group
    {
        $group = $this->find($id);
        if ($group === null) {
            throw new GroupNotFound($id);
        }
        return $group;
    }
}