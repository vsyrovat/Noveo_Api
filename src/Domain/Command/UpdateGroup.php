<?php declare(strict_types=1);

namespace App\Domain\Command;

use App\Domain\Entity\Group;
use App\Domain\Exception\GroupNotFound;
use Doctrine\ORM\EntityManagerInterface;

class UpdateGroup
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function execute(int $id, string $name): void
    {
        $this->em->transactional(function () use ($id, $name) {
            $group = $this->em->getRepository(Group::class)->find($id);
            if ($group === null) {
                throw new GroupNotFound($id);
            }

            $group->setName($name);
            $this->em->flush();
        });
    }
}
