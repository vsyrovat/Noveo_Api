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

    private function compareCurrentDbUsersWith(array $beforeUsers)
    {
        $users = $this->em->getRepository(User::class)->findAll();

        $diff = array_udiff(
            $users,
            $beforeUsers,
            static function (User $a, User $b) {
                return $a->getId() <=> $b->getId();
            }
        );

        return $diff;
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

    public function getCreatedUser()
    {
        $diff = $this->compareCurrentDbUsersWith($this->beforeScanarioUsers);
        Assert::assertCount(1, $diff, 'User was not been created');
        return $diff[0];
    }

    /** @Then user :fullName should be created */
    public function userShouldBeCreated(string $fullName)
    {
        $user = $this->getCreatedUser();
        Assert::assertSame($fullName, "{$user->getFirstName()} {$user->getLastName()}");
    }

    /** @Then response should contain created user id */
    public function responseShouldContainUserId()
    {
        $user = $this->getCreatedUser();
        $this->restContext->theResponseShouldNotBeEmpty();
        $this->restContext->theResponseShouldBeInJson();
        $this->jsonContext->theJsonNodeShouldBeEqualToValue('data', ['id' => $user->getId()]);
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
}