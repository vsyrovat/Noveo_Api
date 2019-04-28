<?php declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\MinkExtension\Context\MinkContext;
use PHPUnit\Framework\Assert;

class HttpContext implements Context
{
    /** @var RestContext */
    private $restContext;
    /** @var GroupContext */
    private $groupsContext;
    /** @var JsonContext */
    private $jsonContext;
    /** @var MinkContext */
    private $minkContext;

    public static function substituteParameter(PyStringNode $node, string $sign, $value)
    {
        $strings = [];
        foreach ($node->getStrings() as $string) {
            $strings[] = str_replace($sign, $value, $string);
        }
        return new PyStringNode($strings, $node->getLine());
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->restContext = $scope->getEnvironment()->getContext(RestContext::class);
        $this->groupsContext = $scope->getEnvironment()->getContext(GroupContext::class);
        $this->jsonContext = $scope->getEnvironment()->getContext(JsonContext::class);
        $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);
    }

    /** @When API-user sends :method request to :url */
    public function apiUserSendsRequest(string $method, string $url, PyStringNode $body = null, $files = [])
    {
        if ($body) {
            $body = self::substituteParameter($body, '{$1}', $this->groupsContext->getCapturedGroupId());
        }
        $this->restContext->iAddHeaderEqualTo('Content-Type', 'application/json');
        $this->restContext->iAddHeaderEqualTo('Accept', 'application/json');
        $this->restContext->iSendARequestTo($method, $url, $body, $files);
    }

    /** @Then response body should be equal to id of group :groupName */
    public function responseShouldBeEqualToGroupId(string $groupName)
    {
        $group = $this->groupsContext->readGroupByName($groupName);
        $this->restContext->theResponseShouldNotBeEmpty();
        $this->restContext->theResponseShouldBeInJson();
        $this->jsonContext->theJsonNodeShouldBeEqualToValue('data', ['id' => $group->getId()]);
    }

    /** @Then response should be standard JSON-success */
    public function responseShouldBeStandardJsonSuccess()
    {
        $this->restContext->theResponseShouldNotBeEmpty();
        Assert::assertContains($this->minkContext->getSession()->getStatusCode(), [200, 201]);
        $this->restContext->theResponseShouldBeInJson();
    }
}