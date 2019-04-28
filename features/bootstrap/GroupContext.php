<?php declare(strict_types=1);

use App\Domain\Entity\Group;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\MinkExtension\Context\MinkContext;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;

class GroupContext implements Context
{
    private $em;
    private $captureGroup = false;
    private $capturedGroupId;
    /** @var HttpContext */
    private $httpContext;
    /** @var RestContext */
    private $restContext;
    /** @var MinkContext */
    private $minkContext;
    /** @var JsonContext */
    private $jsonContext;
    private $beforeScanarioGroups;
    private $store;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getCapturedGroupId()
    {
        return $this->capturedGroupId;
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->httpContext = $scope->getEnvironment()->getContext(HttpContext::class);
        $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);
        $this->restContext = $scope->getEnvironment()->getContext(RestContext::class);
        $this->jsonContext = $scope->getEnvironment()->getContext(JsonContext::class);
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

    /** @When I create a group */
    public function whenICreateAGroup()
    {
        $this->beforeScanarioGroups = $this->em->getRepository(Group::class)->findAll();

        $this->restContext->iAddHeaderEqualTo('Content-Type', 'application/json');
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/json');

        $body = new PyStringNode([/** @lang JSON */ <<<'JSON'
{
    "name": "Spaceship operators"
}
JSON
        ], 0);
        $this->restContext->iSendARequestToWithBody('POST', '/groups/', $body);
    }

    /** @Then a group was created */
    public function thenAGroupWasCreated()
    {
        $diff = array_values(array_udiff(
            $this->em->getRepository(Group::class)->findAll(),
            $this->beforeScanarioGroups,
            static function (Group $a, Group $b) {
                return $a->getId() <=> $b->getId();
            }
        ));

        Assert::assertCount(1, $diff, 'A group was not created');
        Assert::assertNotCount(2, $diff, 'Unexpected shit happens');
        Assert::assertSame('Spaceship operators', $diff[0]->getName());

        $this->store = ['group1' => $diff[0]];
    }

    /** @Then I see a group */
    public function thenISeeAGroup()
    {
        $this->minkContext->assertResponseStatus(201);
        $this->jsonContext->theResponseShouldBeInJson();
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/json');

        $schema = /** @lang JSON */ <<<'JSON'
{
  "type": "object",
  "required": ["success", "data"],
  "properties": {
    "success": {"enum": [true]},
    "data": {
      "type": "object",
      "required": ["id", "name"],
      "properties": {
        "id": {"enum": [{group1.id}]},
        "name": {"pattern": "^Spaceship operators$"}
      }
    }
  }
}
JSON;
        $schema = new PyStringNode([$schema], 0);
        $schema = HttpContext::substituteParameter($schema, '{group1.id}', $this->store['group1']->getId());
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema($schema);
    }

    /** @Given there is a group */
    public function givenThereIsAGroup()
    {
        $group = new Group('Stargate moderators');
        $this->em->persist($group);
        $this->em->flush();
        $this->store['group1'] = $group;
    }

    /** @When I create group with same name */
    public function whenICreateGroupWithSameName()
    {
        $this->restContext->iAddHeaderEqualTo('Content-Type', 'application/json');
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/json');

        $body = new PyStringNode([/** @lang JSON */ <<<'JSON'
{
    "name": "Stargate moderators"
}
JSON
        ], 0);
        $this->restContext->iSendARequestToWithBody('POST', '/groups/', $body);
    }

    /** @Then request is invalid */
    public function thenRequestIsInvalid()
    {
        $this->minkContext->assertResponseStatus(400);
    }
}