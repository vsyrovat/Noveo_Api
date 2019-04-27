<?php declare(strict_types=1);

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Gherkin\Node\PyStringNode;
use Behatch\HttpCall\HttpCallResult;
use PHPUnit\Framework\Assert;

class JsonContext extends \Behatch\Context\JsonContext
{
    /**
     * Print failed response
     * @AfterStep
     * @param AfterStepScope $scope
     */
    public function printFailedResponse(AfterStepScope $scope)
    {
        if (!$scope->getTestResult()->isPassed()) {
            if ($this->httpCallResultPool->getResult() instanceof HttpCallResult) {
                parent::printLastJsonResponse();
            } else {
                echo 'Last HTTP Result is unavailable';
            }
        }
        ob_flush();
    }

    public function theJsonNodeShouldBeEqualToValue(string $node, $expected)
    {
        $json = $this->getJson();

        $actual = $this->inspector->evaluate($json, $node);
        $actual = json_decode(json_encode($actual), true);

        Assert::assertSame(JSON_ERROR_NONE, json_last_error(), 'Invalid Json for expected ('.json_last_error_msg().')');

        Assert::assertEquals($expected, $actual);
    }

    /**
     * @Then the JSON node :node should be equal to JSON:
     */
    public function theJsonNodeShouldBeEqualToJson(string $node, PyStringNode $rawJson)
    {
        $json = $this->getJson();

        $actual = $this->inspector->evaluate($json, $node);
        $actual = json_decode(json_encode($actual), true);

        $expected = \json_decode($rawJson->getRaw(), true);
        Assert::assertSame(JSON_ERROR_NONE, json_last_error(), 'Invalid Json for expected ('.json_last_error_msg().')');

        Assert::assertEquals($expected, $actual);
    }
}