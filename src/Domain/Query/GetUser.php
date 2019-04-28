<?php declare(strict_types=1);

namespace App\Domain\Query;

use App\Persistence\Repository\UserRepository;

class GetUser
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(int $userId)
    {
        return $this->userRepository->findOrThrow($userId);
    }
}