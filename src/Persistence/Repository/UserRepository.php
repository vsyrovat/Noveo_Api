<?php declare(strict_types=1);

namespace App\Persistence\Repository;

use App\Domain\Entity\User;
use App\Domain\Exception\UserNotFound;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOrThrow(int $id): User
    {
        $user = $this->find($id);
        if ($user === null) {
            throw new UserNotFound($id);
        }
        return $user;
    }

    public function save(User $user): void
    {
        $this->_em->persist($user);
        $this->_em->flush();
    }
}