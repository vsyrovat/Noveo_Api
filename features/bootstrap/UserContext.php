<?php declare(strict_types=1);

use App\Domain\Entity\Group;
use App\Domain\Entity\User;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\MinkExtension\Context\MinkContext;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;

class UserContext implements Context
{
    private $em;
    /** @var MinkContext */
    private $minkContext;
    /** @var RestContext */
    private $restContext;
    /** @var JsonContext */
    private $jsonContext;
    private $beforeScanarioUsers;
    private $entities;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);
        $this->restContext = $scope->getEnvironment()->getContext(RestContext::class);
        $this->jsonContext = $scope->getEnvironment()->getContext(JsonContext::class);
    }

    /** @BeforeScenario @captureCreateUser */
    public function beforeCreateUserHook(BeforeScenarioScope $scope)
    {
        $this->beforeScanarioUsers = $this->em->getRepository(User::class)->findAll();
    }

    /** @Given there is a users in a group */
    public function thereIsAUsersInAGroup()
    {
        $writers = new Group('Writers');
        $anderson = new User('Victor', 'Anderson', 'victor@anderson.org', true, $writers);
        $browne = new User('Thomas', 'Browne', 'thomas@browne.org', false, $writers);

        $this->em->persist($writers);
        $this->em->persist($anderson);
        $this->em->persist($browne);
        $this->em->flush();

        $this->entities = ['writers' => $writers, 'anderson' => $anderson, 'browne' => $browne];
    }

    /** @When I get a list of all users */
    public function iGetAListOfAllUsers()
    {
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/json');
        $this->restContext->iSendARequestTo('GET', '/users/');
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
      "required": ["id", "group", "first_name", "last_name", "email", "is_active"]
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
            "first_name": {"pattern": "^Victor$"},
            "last_name": {"pattern": "^Anderson$"},
            "email": {"pattern": "^victor@anderson.org$"},
            "is_active": {"enum": [true]},
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
            "first_name": {"pattern": "^Thomas"},
            "last_name": {"pattern": "^Browne$"},
            "email": {"pattern": "^thomas@browne.org$"},
            "is_active": {"enum": [false]},
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
        $pySchema = HttpContext::substituteParameter($pySchema, '{writers.id}', $this->entities['writers']->getId());
        $pySchema = HttpContext::substituteParameter($pySchema, '{anderson.id}', $this->entities['anderson']->getId());
        $pySchema = HttpContext::substituteParameter($pySchema, '{browne.id}', $this->entities['browne']->getId());
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema($pySchema);
    }

    /** @Given there is a user */
    public function givenThereIsAUser()
    {
        $group1 = new Group('Technical support');
        $this->em->persist($group1);

        $user1 = new User('John', 'Smith', 'john.smith@company.com', true, $group1);
        $this->em->persist($user1);

        $this->em->flush();
        $this->entities = ['group1' => $group1, 'user1' => $user1];
    }

    /** @When I get a user */
    public function whenIGetAUser()
    {
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/json');
        $this->restContext->iSendARequestTo('GET', sprintf('/users/%d/', $this->entities['user1']->getId()));
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
      "required": ["id", "group", "first_name", "last_name", "email", "is_active"]
    }
  },

  "type": "object",
  "properties": {
    "success": {"type": "boolean"},
    "data": {
      "$ref": "#/definitions/user",
      "properties": {
        "id": {"enum": [{user1.id}]},
        "first_name": {"pattern": "^John$"},
        "last_name": {"pattern": "^Smith$"},
        "email": {"pattern": "^john.smith@company.com$"},
        "is_active": {"enum": [true]},
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
        $pySchema = HttpContext::substituteParameter($pySchema, '{group1.id}', $this->entities['group1']->getId());
        $pySchema = HttpContext::substituteParameter($pySchema, '{user1.id}', $this->entities['user1']->getId());
        $this->jsonContext->theJsonShouldBeValidAccordingToThisSchema($pySchema);
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
        $this->restContext->iSendARequestTo('PUT', sprintf('/users/%d/', $this->entities['user1']->getId()), $body);
    }

    /** @Then user info was updated */
    public function thenUserInfoWasUpdated()
    {
        $this->minkContext->assertResponseStatus(200);
        $this->jsonContext->theResponseShouldBeInJson();
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/json');

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->find($this->entities['user1']->getId());
        Assert::assertSame('Mary', $user->firstName);
        Assert::assertSame('Adams', $user->lastName);
        Assert::assertSame('mary.adams@company.com', $user->email);
        Assert::assertFalse($user->isActive);
    }

    /** @When I create a user */
    public function whenICreateAUser()
    {
        $this->beforeScanarioUsers = $this->em->getRepository(User::class)->findAll();

        $group = new Group('Chuck Norris');
        $this->em->persist($group);
        $this->em->flush();
        $this->entities['group1'] = $group;

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
        $body = HttpContext::substituteParameter($body, '{group1.id}', $group->getId());
        $this->restContext->iSendARequestTo('POST', '/users/', $body);
    }

    /** @Then the user was created */
    public function thenTheUserWasCreated()
    {
        $diff = array_values(array_udiff(
            $this->em->getRepository(User::class)->findAll(),
            $this->beforeScanarioUsers,
            static function (User $a, User $b) {
                return $a->getId() <=> $b->getId();
            }
        ));
        Assert::assertCount(1, $diff, 'User was not been created');
        $this->entities['user1'] = $diff[0];
    }

    /** @Then I see the user */
    public function thenISeeTheUser()
    {
        $this->minkContext->assertResponseStatus(201);
        $this->restContext->theHeaderShouldBeEqualTo('Content-Type', 'application/json');
        $this->jsonContext->theResponseShouldBeInJson();

        /** @var User $user */
        $user = $this->em->getRepository(User::class)->find($this->entities['user1']->getId());
        Assert::assertSame('Chuck', $user->firstName);
        Assert::assertSame('Norris', $user->lastName);
        Assert::assertSame('chuck@norris.chucknorris', $user->email);
        Assert::assertTrue($user->isActive);
    }
}