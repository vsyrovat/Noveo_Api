<?php declare(strict_types=1);

use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\MinkContext;
use Behatch\HttpCall\Request;
use PHPUnit\Framework\Assert;

class RestContext extends \Behatch\Context\RestContext
{
    /** @var Request */
    protected $request;
    /** @var MinkContext */
    private $minkContext;

    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        if (!$environment instanceof InitializedContextEnvironment) {
            throw new \LogicException('FeatureContext cannot be correctly initialized without access to subcontexts.');
        }

        $this->minkContext = $environment->getContext(MinkContext::class);
    }

    public function theResponseShouldNotBeEmpty()
    {
        $actual = $this->request->getContent();
        $this->assertFalse(null === $actual || "" === $actual, "The response of the current page is empty");
    }

    public function theResponseShouldBeInJson()
    {
        $this->theHeaderShouldBeEqualTo('Content-Type', 'application/json');
        Assert::assertJson($this->minkContext->getSession()->getPage()->getContent());
    }

    public function theResponseDataShouldBeEqualTo(array $expected)
    {
        $responseBody = $this->request->getContent();
        $actual = \json_decode($responseBody, true);
        Assert::assertEquals($expected, $actual);
    }
}