<?php declare(strict_types=1);

namespace App\Domain\Command\UpdateUser;

class UpdateUserChangeset
{
    public $firstName;
    public $lastName;
    public $email;
    public $isActive;
}