<?php declare(strict_types=1);

namespace App\Domain\Command;

use App\Domain\Entity\Group;
use App\Domain\Validation\GroupValidator;
use Doctrine\ORM\EntityManagerInterface;

class UpdateGroup
{
    private $em;
    private $groupValidator;

    public function __construct(EntityManagerInterface $em, GroupValidator $groupValidator)
    {
        $this->em = $em;
        $this->groupValidator = $groupValidator;
    }

    public function execute(int $id, string $name): void
    {
        $this->em->transactional(function () use ($id, $name) {
            $group = $this->em->getRepository(Group::class)->findOrThrow($id);
            $group->setName($name);
            $this->groupValidator->assertGroupValid($group);
            $this->em->flush();
        });
    }
}
