<?php declare(strict_types=1);

namespace App\Domain\Command;

use App\Domain\Entity\Group;
use App\Domain\Entity\User;
use App\Domain\Exception\DuplicateUserEmail;
use App\Domain\Exception\GroupNotFound;
use App\Domain\Validation\UserValidator;
use Doctrine\ORM\EntityManagerInterface;

class CreateUser
{
    private $em;
    private $userValidator;

    public function __construct(EntityManagerInterface $em, UserValidator $userValidator)
    {
        $this->em = $em;
        $this->userValidator = $userValidator;
    }

    /**
     * @throws GroupNotFound
     * @throws DuplicateUserEmail
     */
    public function execute(string $firstName, string $lastName, string $email, bool $isActive, int $groupId): User
    {
        $user = $this->em->transactional(function () use ($email, $firstName, $lastName, $isActive, $groupId){
            $group = $this->em->getRepository(Group::class)->findOrThrow($groupId);
            $user = new User($firstName, $lastName, $email, $isActive, $group);
            $this->userValidator->assertUserValid($user);
            $this->em->persist($user);
            $this->em->flush();
            return $user;
        });

        return $user;
    }
}