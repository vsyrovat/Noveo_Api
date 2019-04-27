<?php declare(strict_types=1);

namespace App\Domain\Command\Group;

use App\Domain\Entity\Group;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class CreateGroup
{
    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * @throws DuplicateGroupNameException
     */
    public function execute(string $groupName): Group
    {
        $this->em->beginTransaction();

        $existsGroup = $this->em->getRepository(Group::class)->findOneBy(['name' => $groupName]);
        if ($existsGroup !== null) {
            throw new DuplicateGroupNameException($groupName);
        }

        try {
            $group = new Group($groupName);
            $this->em->persist($group);
            $this->em->flush();
            $this->em->commit();
            return $group;
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}