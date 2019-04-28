<?php declare(strict_types=1);

namespace App\Domain\Exception;

class UserNotFound extends \RuntimeException
{
    public function __construct(int $id)
    {
        $message = "User {$id} not found";
        parent::__construct($message);
    }
}