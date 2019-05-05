<?php declare(strict_types=1);

namespace App\Domain\Command;

use App\Domain\Entity\Group;
use App\Domain\Entity\User;
use App\Domain\Exception\ValidationException;
use App\Domain\Validation\GroupValidator;
use App\Domain\Validation\UserValidator;
use App\Framework\Changeset\ChangesetValidator;
use Doctrine\ORM\EntityManagerInterface;

class UpdateUser
{
    private $em;
    private $changesetValidator;
    private $userValidator;
    private $groupValidator;

    public function __construct(EntityManagerInterface $em,
                                ChangesetValidator $changesetValidator,
                                UserValidator $userValidator,
                                GroupValidator $groupValidator
    ) {
        $this->em = $em;
        $this->changesetValidator = $changesetValidator;
        $this->userValidator = $userValidator;
        $this->groupValidator = $groupValidator;
    }

    /**
     * @throws ValidationException
     */
    public function execute(int $id, array $changeset): void
    {
        $this->em->transactional(function () use ($id, $changeset) {
            $this->changesetValidator->assertChangesetValid($changeset, User::class);

            $user = $this->em->getRepository(User::class)->findOrThrow($id);

            $user->firstName = $changeset['firstName'];
            $user->lastName = $changeset['lastName'];
            $user->email = $changeset['email'];
            $user->isActive = $changeset['isActive'];

            $oldGroup = $user->group;
            $group = $this->em->getRepository(Group::class)->findOrThrow($changeset['group']);
            $user->group = $group;

            $this->userValidator->assertUserValid($user);
            $this->groupValidator->assertGroupValid($group);
            $this->groupValidator->assertGroupValid($oldGroup);

            $this->em->flush();
        });
    }
}