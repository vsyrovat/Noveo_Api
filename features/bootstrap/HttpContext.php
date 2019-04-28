<?php declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;

class HttpContext implements Context
{
    public static function substituteParameter(PyStringNode $node, string $sign, $value)
    {
        $strings = [];
        foreach ($node->getStrings() as $string) {
            $strings[] = str_replace($sign, $value, $string);
        }
        return new PyStringNode($strings, $node->getLine());
    }
}