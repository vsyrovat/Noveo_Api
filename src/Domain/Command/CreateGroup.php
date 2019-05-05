<?php declare(strict_types=1);

namespace App\Domain\Command;

use App\Domain\Entity\Group;
use App\Domain\Exception\DuplicateGroupNameException;
use App\Domain\Validation\GroupValidator;
use Doctrine\ORM\EntityManagerInterface;

class CreateGroup
{
    private $em;
    private $groupValidator;

    public function __construct(EntityManagerInterface $em, GroupValidator $groupValidator)
    {
        $this->em = $em;
        $this->groupValidator = $groupValidator;
    }

    /**
     * @throws DuplicateGroupNameException
     */
    public function execute(string $groupName): Group
    {
        $group = $this->em->transactional(function () use ($groupName) {
            $group = new Group($groupName);
            $this->groupValidator->assertGroupValid($group);
            $this->em->persist($group);
            $this->em->flush();
            return $group;
        });

        return $group;
    }
}