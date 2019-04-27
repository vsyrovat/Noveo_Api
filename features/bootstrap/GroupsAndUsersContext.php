<?php declare(strict_types=1);

use App\Domain\Entity\Group;
use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;

class GroupsAndUsersContext implements Context
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function readGroupByName(string $groupName): ?Group
    {
        return $this->em->getRepository(Group::class)->findOneBy(['name' => $groupName]);
    }

    /**
     * @Given group :groupName does not exists
     */
    function groupDoesNotExists(string $groupName)
    {
        $group = $this->readGroupByName($groupName);
        if ($group) {
            $this->em->remove($group);
            $this->em->flush();
        }
    }

    /** @Then group :groupName should exists */
    public function groupShouldExists(string $groupName)
    {
        $group = $this->readGroupByName($groupName);
        Assert::assertNotNull($group, "Group $groupName is not exist");
    }
}