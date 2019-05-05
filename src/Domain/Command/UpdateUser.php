<?php declare(strict_types=1);

namespace App\Domain\Command;

use App\Domain\Entity\User;
use App\Domain\Exception\ValidationException;
use App\Framework\Annotation\ChangesetValidator;
use App\Persistence\Repository\UserRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateUser
{
    private $validator;
    private $userRepository;
    private $changesetValidator;

    public function __construct(ValidatorInterface $validator, UserRepository $userRepository, ChangesetValidator $changesetValidator)
    {
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->changesetValidator = $changesetValidator;
    }

    /**
     * @throws ValidationException
     */
    public function execute(int $id, array $changeset): void
    {
        $this->changesetValidator->assertChangesetValid($changeset, User::class);

        $user = $this->userRepository->findOrThrow($id);

        $user->firstName = $changeset['firstName'];
        $user->lastName = $changeset['lastName'];
        $user->email = $changeset['email'];
        $user->isActive = $changeset['isActive'];

        $violations = $this->validator->validate($user);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->userRepository->save($user);
    }
}