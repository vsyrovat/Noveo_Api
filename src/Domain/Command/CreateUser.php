<?php declare(strict_types=1);

namespace App\Domain\Command;

use App\Domain\Entity\Group;
use App\Domain\Entity\User;
use App\Domain\Exception\DuplicateUserEmail;
use App\Domain\Exception\GroupNotFound;
use Doctrine\ORM\EntityManagerInterface;

class CreateUser
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $email
     */
    private function assertEmailNotExists(string $email): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user !== null) {
            throw new DuplicateUserEmail($email);
        }
    }

    /**
     * @throws GroupNotFound
     * @throws DuplicateUserEmail
     */
    public function execute(string $firstName, string $lastName, string $email, bool $isActive, int $groupId): User
    {
        $user = $this->em->transactional(function () use ($email, $firstName, $lastName, $isActive, $groupId){
            $this->assertEmailNotExists($email);
            $group = $this->em->getRepository(Group::class)->findOrThrow($groupId);
            $user = new User($firstName, $lastName, $email, $isActive, $group);
            $this->em->persist($user);
            $this->em->flush();
            return $user;
        });

        return $user;
    }
}