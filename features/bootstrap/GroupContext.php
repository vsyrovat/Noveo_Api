<?php declare(strict_types=1);

use App\Domain\Entity\Group;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\MinkExtension\Context\MinkContext;
use Behatch\Context\RestContext;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;

class GroupContext implements Context
{
    /** @var JsonContext */
    private $jsonContext;
    /** @var MinkContext */
    private $minkContext;
    /** @var RestContext */
    private $restContext;

    private $em;
    private $beforeScenarioGroups;
    private $store;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->jsonContext = $scope->getEnvironment()->getContext(JsonContext::class);
        $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);
        $this->restContext = $scope->getEnvironment()->getContext(RestContext::class);
    }

    /** @Given there is a group */
    public function givenThereIsAGroup()
    {
        $group = new Group('Stargate moderators');
        $this->em->persist($group);
        $this->em->flush();
        $this->store['group1'] = $group;
    }

    /** @Given there is a groups */
    public function givenThereIsAGroups()
    {
        $group1 = new Group('Admins');
        $this->em->persist($group1);
        $this->store['group1'] = $group1;

        $group2 = new Group('Users');
        $this->em->persist($group2);
        $this->store['group2'] = $group2;

        $this->em->flush();
    }

    /** @When I create a group */
    public function whenICreateAGroup()
    {
        $this->beforeScenarioGroups = $this->em->getRepository(Group::class)->findAll();

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

    /** @When I get a list of groups */
    public function whenIGetAListOfGroups()
    {
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/json');
        $this->restContext->iSendARequestTo('GET', '/groups/');
    }

    /** @When I update group info */
    public function whenIUpdateGroupInfo()
    {
        $this->restContext->iAddHeaderEqualTo('Content-Type', 'application/json');
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/json');
        $body = /** @lang JSON */ <<<'JSON'
{
  "name": "Gangsters"
}
JSON;
        $body = new PyStringNode([$body], 0);
        $this->restContext->iSendARequestTo('PUT', sprintf('/groups/%d/', $this->store['group1']->getId()), $body);
    }

    /** @Then a group was created */
    public function thenAGroupWasCreated()
    {
        $diff = array_values(array_udiff(
            $this->em->getRepository(Group::class)->findAll(),
            $this->beforeScenarioGroups,
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
        $schema = FeatureContext::substituteParameter($schema, '{group1.id}', $this->store['group1']->getId());
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema($schema);
    }

    /** @Then request is invalid */
    public function thenRequestIsInvalid()
    {
        $this->minkContext->assertResponseStatus(400);
    }

    /** @Then I see a list of groups */
    public function thenISeeAListOfGroups()
    {
        $this->minkContext->assertResponseStatus(200);
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/json');
        $this->jsonContext->theResponseShouldBeInJson();

        $schema = /** @lang JSON */ <<<'JSON'
{
  "definitions": {
    "group": {
      "type": "object",
      "required": ["id", "name"]
    }
  },

  "type": "object",
  "required": ["success", "data"],
  "properties": {
    "success": {"enum": [true]},
    "data": {
      "type": "array",
      "minItems": 2,
      "maxItems": 2,
      "items": [
        {
          "$ref": "#/definitions/group",
          "properties": {
            "id": {"enum": [{group1.id}]},
            "name": {"pattern": "^Admins$"}
          }
        },
        {
          "$ref": "#/definitions/group",
          "properties": {
            "id": {"enum": [{group2.id}]},
            "name": {"pattern": "^Users$"}
          }
        }
      ]
    }
  }
}
JSON;
        $schema = new PyStringNode([$schema], 0);
        $schema = FeatureContext::substituteParameter($schema, '{group1.id}', $this->store['group1']->getId());
        $schema = FeatureContext::substituteParameter($schema, '{group2.id}', $this->store['group2']->getId());
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema($schema);
    }

    /** @Then group info was updated */
    public function thenGroupInfoWasUpdated()
    {
        $this->minkContext->assertResponseStatus(200);
        $this->jsonContext->theResponseShouldBeInJson();
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/json');

        /** @var Group $group */
        $group = $this->em->getRepository(Group::class)->find($this->store['group1']->getId());
        Assert::assertSame('Gangsters', $group->getName());
    }
}