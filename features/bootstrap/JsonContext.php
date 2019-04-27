<?php declare(strict_types=1);

use PHPUnit\Framework\Assert;

class JsonContext extends \Behatch\Context\JsonContext
{
    public function theJsonNodeShouldBeEqualToValue(string $node, $expected)
    {
        $json = $this->getJson();

        $actual = $this->inspector->evaluate($json, $node);
        $actual = json_decode(json_encode($actual), true);

        Assert::assertSame(JSON_ERROR_NONE, json_last_error(), 'Invalid Json for expected ('.json_last_error_msg().')');

        Assert::assertEquals($expected, $actual);
    }
}