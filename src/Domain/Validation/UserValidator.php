<?php declare(strict_types=1);

namespace App\Domain\Validation;

use App\Domain\Entity\User;
use App\Domain\Exception\DuplicateUserEmail;
use App\Domain\Exception\ValidationException;
use App\Persistence\Repository\UserRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserValidator
{
    private $userRepository;
    private $validator;

    public function __construct(UserRepository $userRepository, ValidatorInterface $validator)
    {
        $this->userRepository = $userRepository;
        $this->validator = $validator;
    }

    private function assertValidBySymfonyAnnotations(User $user): void
    {
        $violations = $this->validator->validate($user);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }
    }

    private function assertEmailIsUnique(User $user): void
    {
        $existsUser = $this->userRepository->findOneBy(['email' => $user->email]);
        if ($existsUser !== null && $existsUser->getId() !== $user->getId()) {
            throw new DuplicateUserEmail($user->email);
        }
    }

    /**
     * @throws ValidationException
     */
    public function assertUserValid(User $user): void
    {
        $this->assertValidBySymfonyAnnotations($user);
        $this->assertEmailIsUnique($user);
    }
}