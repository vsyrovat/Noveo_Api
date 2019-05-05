<?php declare(strict_types=1);

namespace App\Domain\Command;

use App\Domain\Entity\Group;
use App\Domain\Entity\User;
use App\Domain\Exception\ValidationException;
use App\Framework\Changeset\ChangesetValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateUser
{
    private $em;
    private $validator;
    private $changesetValidator;

    public function __construct(EntityManagerInterface $em, ValidatorInterface $validator, ChangesetValidator $changesetValidator)
    {
        $this->em = $em;
        $this->validator = $validator;
        $this->changesetValidator = $changesetValidator;
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

            $group = $this->em->getRepository(Group::class)->findOrThrow($changeset['group']);
            $user->group = $group;

            $violations = $this->validator->validate($user);
            if ($violations->count() > 0) {
                throw new ValidationException($violations);
            }

            $this->em->flush();
        });
    }
}