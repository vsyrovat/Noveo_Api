<?php declare(strict_types=1);

use App\Domain\Entity\User;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;

class UserContext implements Context
{
    private $em;
    /** @var RestContext */
    private $restContext;
    /** @var JsonContext */
    private $jsonContext;
    private $beforeScanarioUsers;

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
        $environment = $scope->getEnvironment();

        if (!$environment instanceof InitializedContextEnvironment) {
            throw new \LogicException('FeatureContext cannot be correctly initialized without access to subcontexts.');
        }

        $this->restContext = $environment->getContext(RestContext::class);
        $this->jsonContext = $environment->getContext(JsonContext::class);
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
}