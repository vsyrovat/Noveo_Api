<?php declare(strict_types=1);

namespace App\Domain\Validation;

use App\Domain\Entity\Group;
use App\Domain\Exception\DuplicateGroupNameException;
use App\Domain\Exception\ValidationException;
use App\Persistence\Repository\GroupRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GroupValidator
{
    private $groupRepository;
    private $validator;

    public function __construct(GroupRepository $groupRepository, ValidatorInterface $validator)
    {
        $this->groupRepository = $groupRepository;
        $this->validator = $validator;
    }

    private function assertNameIsUnique(Group $group): void
    {
        $existsGroup = $this->groupRepository->findOneBy(['name' => $group->getName()]);
        if ($existsGroup !== null && $existsGroup->getId() !== $group->getId()) {
            throw new DuplicateGroupNameException($group->getName());
        }
    }

    private function assertValidBySymfonyAnnotations(Group $group): void
    {
        $violations = $this->validator->validate($group);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }
    }

    private function assertUserLimitNotOver(Group $group)
    {
        if ($group->users->count() > Group::MAX_USERS_PER_GROUP) {
            throw new ValidationException([
                'message' => sprintf(
                    'Exceeded user limit %d in group %d (%s)',
                    Group::MAX_USERS_PER_GROUP,
                    $group->getId(),
                    $group->getName()
                )
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    public function assertGroupValid(Group $group): void
    {
        $this->assertValidBySymfonyAnnotations($group);
        $this->assertNameIsUnique($group);
        $this->assertUserLimitNotOver($group);
    }
}