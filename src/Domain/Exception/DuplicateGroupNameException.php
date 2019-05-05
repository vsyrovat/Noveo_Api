<?php declare(strict_types=1);

namespace App\Domain\Exception;

class DuplicateGroupNameException extends ValidationException
{
    public function __construct(string $groupName)
    {
        $message = "Group '$groupName' already exists";
        parent::__construct($message);
    }
}