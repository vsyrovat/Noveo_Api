<?php declare(strict_types=1);

namespace App\Domain\Query;

use App\Domain\Entity\User;
use App\Persistence\Repository\UserRepository;

class GetUsers
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @return User[]
     */
    public function execute(): array
    {
        return $this->userRepository->findAll();
    }
}