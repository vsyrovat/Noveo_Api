<?php declare(strict_types=1);

namespace App\Domain\Command;

use App\Persistence\Repository\UserRepository;

class UpdateUser
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(int $id, string $firstName, string $lastName, string $email, bool $isActive): void
    {
        $user = $this->userRepository->findOrThrow($id);
        $user->firstName = $firstName;
        $user->lastName = $lastName;
        $user->email = $email;
        $user->isActive = $isActive;

        $this->userRepository->save($user);
    }
}