<?php declare(strict_types=1);

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behatch\HttpCall\HttpCallResult;

class JsonContext extends \Behatch\Context\JsonContext
{
    /**
     * Print response if step fail
     * @AfterStep
     */
    public function printFailedResponse(AfterStepScope $scope)
    {
        if (!$scope->getTestResult()->isPassed()) {
            if ($this->httpCallResultPool->getResult() instanceof HttpCallResult) {
                parent::printLastJsonResponse();
            } else {
                echo "Last HTTP Result is unavailable\n";
            }
        }
        ob_flush();
    }
}