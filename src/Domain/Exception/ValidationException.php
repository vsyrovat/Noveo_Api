<?php declare(strict_types=1);

namespace App\Domain\Exception;

class ValidationException extends \RuntimeException
{
    /** @var string[] */
    public $violations;

    public function __construct($violations)
    {
        $this->violations = $violations;
        parent::__construct();
    }
}