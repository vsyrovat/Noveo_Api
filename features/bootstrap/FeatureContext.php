<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Doctrine\ORM\EntityManagerInterface;

/**
 * This context class contains the definitions of the steps used by the demo 
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 * 
 * @see http://behat.org/en/latest/quick_start.html
 */
class FeatureContext implements Context
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function substituteParameter(PyStringNode $node, string $sign, $value)
    {
        $strings = [];
        foreach ($node->getStrings() as $string) {
            $strings[] = str_replace($sign, $value, $string);
        }
        return new PyStringNode($strings, $node->getLine());
    }

    /**
     * @BeforeScenario
     */
    public function createTransaction()
    {
        $this->em->beginTransaction();
    }

    /**
     * @AfterScenario
     */
    public function rollbackTransaction()
    {
        $this->em->rollback();
    }
}
