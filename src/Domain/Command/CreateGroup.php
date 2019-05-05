<?php declare(strict_types=1);

namespace App\Domain\Command;

use App\Domain\Entity\Group;
use App\Domain\Exception\DuplicateGroupNameException;
use Doctrine\ORM\EntityManagerInterface;

class CreateGroup
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @throws DuplicateGroupNameException
     */
    public function execute(string $groupName): Group
    {
        $group = $this->em->transactional(function () use ($groupName){
            $existsGroup = $this->em->getRepository(Group::class)->findOneBy(['name' => $groupName]);
            if ($existsGroup !== null) {
                throw new DuplicateGroupNameException($groupName);
            }

            $group = new Group($groupName);
            $this->em->persist($group);
            $this->em->flush();
            return $group;
        });

        return $group;
    }
}