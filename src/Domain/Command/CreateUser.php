<?php declare(strict_types=1);

namespace App\Domain\Command;

use App\Domain\Entity\User;
use App\Domain\Exception\DuplicateUserEmail;
use App\Domain\Exception\GroupNotFound;
use App\Persistence\Repository\GroupRepository;
use App\Persistence\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class CreateUser
{
    private $em;
    private $groupRepository;
    private $userRepository;

    public function __construct(EntityManagerInterface $em, GroupRepository $groupRepository, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->groupRepository = $groupRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param string $email
     */
    private function assertEmailNotExists(string $email): void
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
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
            $group = $this->groupRepository->findOrThrow($groupId);
            $user = new User($firstName, $lastName, $email, $isActive, $group);
            $this->userRepository->save($user);
            return $user;
        });

        return $user;
    }
}