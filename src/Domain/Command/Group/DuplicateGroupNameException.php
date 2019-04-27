<?php declare(strict_types=1);

namespace App\Domain\Command\Group;

class DuplicateGroupNameException extends \RuntimeException
{
    public function __construct(string $groupName)
    {
        $message = "Group '$groupName' already exists";
        parent::__construct($message);
    }
}