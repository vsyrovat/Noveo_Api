<?php declare(strict_types=1);

use App\Domain\Entity\Group;
use App\Domain\Entity\User;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\MinkExtension\Context\MinkContext;
use Behatch\Context\RestContext;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;

class UserContext implements Context
{
    /** @var JsonContext */
    private $jsonContext;
    /** @var MinkContext */
    private $minkContext;
    /** @var RestContext */
    private $restContext;

    private $em;
    private $beforeScenarioUsers;
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

    /** @Given there is a users in a group */
    public function givenThereIsAUsersInAGroup()
    {
        $writers = new Group('Writers');
        $anderson = new User('Victor', 'Anderson', 'victor@anderson.org', true, $writers);
        $browne = new User('Thomas', 'Browne', 'thomas@browne.org', false, $writers);

        $this->em->persist($writers);
        $this->em->persist($anderson);
        $this->em->persist($browne);
        $this->em->flush();

        $this->store = ['writers' => $writers, 'anderson' => $anderson, 'browne' => $browne];
    }

    /** @Given there is a user */
    public function givenThereIsAUser()
    {
        $group1 = new Group('Technical support');
        $this->em->persist($group1);

        $user1 = new User('John', 'Smith', 'john.smith@company.com', true, $group1);
        $this->em->persist($user1);

        $this->em->flush();
        $this->store = ['group1' => $group1, 'user1' => $user1];
    }

    /** @When I get a list of all users */
    public function whenIGetAListOfAllUsers()
    {
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/json');
        $this->restContext->iSendARequestTo('GET', '/users/');
    }

    /** @When I get a user */
    public function whenIGetAUser()
    {
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/json');
        $this->restContext->iSendARequestTo('GET', sprintf('/users/%d/', $this->store['user1']->getId()));
    }

    /** @When I create a user */
    public function whenICreateAUser()
    {
        $this->beforeScenarioUsers = $this->em->getRepository(User::class)->findAll();

        $group = new Group('Chuck Norris');
        $this->em->persist($group);
        $this->em->flush();
        $this->store['group1'] = $group;

        $this->restContext->iAddHeaderEqualTo('Content-Type', 'application/json');
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/json');
        $body = /** @lang JSON */ <<<'JSON'
{
  "firstName": "Chuck",
  "lastName": "Norris",
  "email": "chuck@norris.chucknorris",
  "isActive": true,
  "groupId": {group1.id}
}
JSON;
        $body = new PyStringNode([$body], 0);
        $body = FeatureContext::substituteParameter($body, '{group1.id}', $group->getId());
        $this->restContext->iSendARequestTo('POST', '/users/', $body);
    }

    /** @When I update user info */
    public function whenIUpdateUserInfo()
    {
        $this->restContext->iAddHeaderEqualTo('Content-Type', 'application/json');
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/json');
        $body = /** @lang JSON */ <<<'JSON'
{
  "firstName": "Mary",
  "lastName": "Adams",
  "email": "mary.adams@company.com",
  "isActive": false
}
JSON;
        $body = new PyStringNode([$body], 0);
        $this->restContext->iSendARequestTo('PUT', sprintf('/users/%d/', $this->store['user1']->getId()), $body);
    }

    /** @Then I see a list of all users */
    public function iSeeAListOfAllUsers()
    {
        $this->minkContext->assertResponseStatus(200);
        $this->jsonContext->theResponseShouldBeInJson();
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/json');
        $schema = /** @lang JSON */ <<<'JSON'
{
  "definitions": {
    "group": {
      "type": "object",
      "required": ["id", "name"]
    },
    "user": {
      "type": "object",
      "required": ["id", "group", "firstName", "lastName", "email", "isActive"]
    }
  },

  "type": "object",
  "properties": {
    "success": {"type": "boolean"},
    "data": {
      "type": "array",
      "minItems": 2,
      "maxItems": 2,
      "items": [
        {
          "$ref": "#/definitions/user",
          "properties": {
            "id": {"enum": [{anderson.id}]},
            "firstName": {"pattern": "^Victor$"},
            "lastName": {"pattern": "^Anderson$"},
            "email": {"pattern": "^victor@anderson.org$"},
            "isActive": {"enum": [true]},
            "group": {
              "$ref": "#/definitions/group",
              "properties": {
                "id": {"enum": [{writers.id}]},
                "name": {"pattern": "^Writers$"}
              }
            }
          }
        },
        {
          "$ref": "#/definitions/user",
          "properties": {
            "id": {"enum": [{browne.id}]},
            "firstName": {"pattern": "^Thomas"},
            "lastName": {"pattern": "^Browne$"},
            "email": {"pattern": "^thomas@browne.org$"},
            "isActive": {"enum": [false]},
            "group": {
              "$ref": "#/definitions/group",
              "properties": {
                "id": {"type": "integer", "enum": [{writers.id}]},
                "name": {"pattern": "^Writers$"}
              }
            }
          }
        }
      ]
    }
  }
}
JSON;
        $pySchema = new PyStringNode([$schema], 0);
        $pySchema = FeatureContext::substituteParameter($pySchema, '{writers.id}', $this->store['writers']->getId());
        $pySchema = FeatureContext::substituteParameter($pySchema, '{anderson.id}', $this->store['anderson']->getId());
        $pySchema = FeatureContext::substituteParameter($pySchema, '{browne.id}', $this->store['browne']->getId());
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema($pySchema);
    }

    /** @Then I see a user */
    public function thenISeeAUser()
    {
        $this->minkContext->assertResponseStatus(200);
        $this->jsonContext->theResponseShouldBeInJson();
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/json');
        $schema = /** @lang JSON */ <<<'JSON'
{
  "definitions": {
    "group": {
      "type": "object",
      "required": ["id", "name"]
    },
    "user": {
      "type": "object",
      "required": ["id", "group", "firstName", "lastName", "email", "isActive"]
    }
  },

  "type": "object",
  "properties": {
    "success": {"type": "boolean"},
    "data": {
      "$ref": "#/definitions/user",
      "properties": {
        "id": {"enum": [{user1.id}]},
        "firstName": {"pattern": "^John$"},
        "lastName": {"pattern": "^Smith$"},
        "email": {"pattern": "^john.smith@company.com$"},
        "isActive": {"enum": [true]},
        "group": {
          "$ref": "#/definitions/group",
          "properties": {
            "id": {"enum": [{group1.id}]},
            "name": {"pattern": "^Technical support$"}
          }
        }
      }
    }
  }
}
JSON;
        $pySchema = new PyStringNode([$schema], 0);
        $pySchema = FeatureContext::substituteParameter($pySchema, '{group1.id}', $this->store['group1']->getId());
        $pySchema = FeatureContext::substituteParameter($pySchema, '{user1.id}', $this->store['user1']->getId());
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema($pySchema);
    }

    /** @Then user info was updated */
    public function thenUserInfoWasUpdated()
    {
        $this->minkContext->assertResponseStatus(200);
        $this->jsonContext->theResponseShouldBeInJson();
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/json');

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->find($this->store['user1']->getId());
        Assert::assertSame('Mary', $user->firstName);
        Assert::assertSame('Adams', $user->lastName);
        Assert::assertSame('mary.adams@company.com', $user->email);
        Assert::assertFalse($user->isActive);
    }

    /** @Then the user was created */
    public function thenTheUserWasCreated()
    {
        $diff = array_values(array_udiff(
            $this->em->getRepository(User::class)->findAll(),
            $this->beforeScenarioUsers,
            static function (User $a, User $b) {
                return $a->getId() <=> $b->getId();
            }
        ));
        Assert::assertCount(1, $diff, 'User was not been created');
        $this->store['user1'] = $diff[0];
    }

    /** @Then I see the user */
    public function thenISeeTheUser()
    {
        $this->minkContext->assertResponseStatus(201);
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/json');
        $this->jsonContext->theResponseShouldBeInJson();

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->find($this->store['user1']->getId());
        Assert::assertSame('Chuck', $user->firstName);
        Assert::assertSame('Norris', $user->lastName);
        Assert::assertSame('chuck@norris.chucknorris', $user->email);
        Assert::assertTrue($user->isActive);
    }
}