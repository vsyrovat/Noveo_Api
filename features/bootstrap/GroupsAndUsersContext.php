<?php declare(strict_types=1);

use App\Domain\Entity\Group;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;

class GroupsAndUsersContext implements Context
{
    private $em;
    private $captureGroup = false;
    private $capturedGroupId;
    /** @var HttpContext */
    private $httpContext;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /** @BeforeScenario */
    public function beforeScenario()
    {
        foreach ($this->em->getRepository(Group::class)->findAll() as $group) {
            $this->em->remove($group);
        }
        $this->em->flush();
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        if (!$environment instanceof InitializedContextEnvironment) {
            throw new \LogicException('FeatureContext cannot be correctly initialized without access to subcontexts.');
        }

        $this->httpContext = $environment->getContext(HttpContext::class);
    }

    /** @beforeScenario @captureGroupId */
    public function captureGroupId()
    {
        $this->captureGroup = true;
    }

    public function readGroupByName(string $groupName): ?Group
    {
        return $this->em->getRepository(Group::class)->findOneBy(['name' => $groupName]);
    }

    /** @Given group :groupName does not exists */
    function groupDoesNotExists(string $groupName)
    {
        $group = $this->readGroupByName($groupName);
        if ($group) {
            $this->em->remove($group);
            $this->em->flush();
        }
    }

    /** @Given group :groupName exists */
    function groupExists(string $groupName)
    {
        $group = $this->readGroupByName($groupName);
        if (!$group) {
            $group = new Group($groupName);
            $this->em->persist($group);
            $this->em->flush();
        }
        $this->capturedGroupId = $group->getId();
    }

    /** @Then group :groupName should exists */
    public function groupShouldExists(string $groupName)
    {
        $group = $this->readGroupByName($groupName);
        Assert::assertNotNull($group, "Group $groupName is not exist");
    }

    /** @Then group with id :groupId should be named as :groupName */
    public function groupWithIdShouldBeNamedAs(int $groupId, string $groupName)
    {
        $group = $this->em->getRepository(Group::class)->find($groupId);
        Assert::assertSame($groupName, $group);
    }

    /** @When API-user sends PUT request to update mentioned group */
    public function sendPutToCapturedGroup(PyStringNode $body)
    {
        $this->httpContext->apiUserSendsRequest('PUT', "/groups/{$this->capturedGroupId}/", $body);
    }

    /** @Then mentioned group should be named as :groupName */
    public function mentionedGroupShouldBeNamedAs(string $groupName)
    {
        $group = $this->em->getRepository(Group::class)->find($this->capturedGroupId);
        Assert::assertSame($groupName, $group->getName());
    }
}