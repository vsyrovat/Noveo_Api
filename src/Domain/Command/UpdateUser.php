<?php declare(strict_types=1);

namespace App\Domain\Command;

use App\Domain\Entity\User;
use App\Domain\Exception\ValidationException;
use App\Framework\Changeset\ChangesetValidator;
use App\Persistence\Repository\GroupRepository;
use App\Persistence\Repository\UserRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateUser
{
    private $validator;
    private $userRepository;
    private $groupRepository;
    private $changesetValidator;

    public function __construct(ValidatorInterface $validator, UserRepository $userRepository, GroupRepository $groupRepository, ChangesetValidator $changesetValidator)
    {
        $this->validator = $validator;
        $this->userRepository = $userRepository;
        $this->groupRepository = $groupRepository;
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

        $group = $this->groupRepository->findOrThrow($changeset['group']);
        $user->group = $group;

        $violations = $this->validator->validate($user);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->userRepository->save($user);
    }
}