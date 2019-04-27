<?php declare(strict_types=1);

namespace App\Domain\Exception;

class DuplicateUserEmail extends \RuntimeException
{
    public function __construct(string $email)
    {
        $message = "User with email {$email} already exists";
        parent::__construct($message);
    }
}