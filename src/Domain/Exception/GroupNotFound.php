<?php declare(strict_types=1);

namespace App\Domain\Exception;

class GroupNotFound extends \RuntimeException
{
    public function __construct(int $id)
    {
        $message = "Group $id not found";
        parent::__construct($message);
    }
}