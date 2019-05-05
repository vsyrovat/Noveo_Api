<?php declare(strict_types=1);

namespace App\Domain\Command;

use App\Domain\Entity\Group;
use App\Domain\Entity\User;
use App\Domain\Exception\DuplicateUserEmail;
use App\Domain\Exception\GroupNotFound;
use App\Domain\Validation\UserValidator;
use App\Framework\Changeset\ChangesetValidator;
use Doctrine\ORM\EntityManagerInterface;

class CreateUser
{
    private $em;
    private $changesetValidator;
    private $userValidator;

    public function __construct(EntityManagerInterface $em, ChangesetValidator $changesetValidator, UserValidator $userValidator)
    {
        $this->em = $em;
        $this->changesetValidator = $changesetValidator;
        $this->userValidator = $userValidator;
    }

    /**
     * @throws GroupNotFound
     * @throws DuplicateUserEmail
     */
    public function execute(array $changeset): User
    {
        $user = $this->em->transactional(function () use ($changeset){
            $this->changesetValidator->assertChangesetValid($changeset, User::class);
            $group = $this->em->getRepository(Group::class)->findOrThrow($changeset['group']);
            $user = new User(
                $changeset['firstName'],
                $changeset['lastName'],
                $changeset['email'],
                $changeset['isActive'],
                $group
            );
            $this->userValidator->assertUserValid($user);
            $this->em->persist($user);
            $this->em->flush();
            return $user;
        });

        return $user;
    }
}