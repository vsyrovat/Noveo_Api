<?php

use Behat\Behat\Context\Context;
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
