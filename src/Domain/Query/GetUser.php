<?php declare(strict_types=1);

namespace App\Domain\Query;

use App\Domain\Entity\User;
use App\Domain\Exception\UserNotFound;
use App\Persistence\Repository\UserRepository;

class GetUser
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws UserNotFound
     */
    public function execute(int $userId): User
    {
        return $this->userRepository->findOrThrow($userId);
    }
}