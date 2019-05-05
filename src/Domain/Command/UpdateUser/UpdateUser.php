<?php declare(strict_types=1);

namespace App\Domain\Command\UpdateUser;

use App\Domain\Exception\ValidationException;
use App\Persistence\Repository\UserRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateUser
{
    private $validator;
    private $userRepository;

    public function __construct(ValidatorInterface $validator, UserRepository $userRepository)
    {
        $this->validator = $validator;
        $this->userRepository = $userRepository;
    }

    /**
     * @throws ValidationException
     */
    public function execute(int $id, UpdateUserChangeset $changeset): void
    {
        $user = $this->userRepository->findOrThrow($id);
        $user->firstName = $changeset->firstName;
        $user->lastName = $changeset->lastName;
        $user->email = $changeset->email;
        $user->isActive = $changeset->isActive;

        $violations = $this->validator->validate($user);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->userRepository->save($user);
    }
}