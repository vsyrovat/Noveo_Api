<?php declare(strict_types=1);

namespace App\Domain\Entity;

use App\Framework\Changeset\Annotations\Api;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Persistence\Repository\UserRepository")
 * @ORM\Table(name="`user`")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="bigint")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Group")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=false)
     * @Api()
     */
    public $group;

    /**
     * @ORM\Column(name="first_name", type="string", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Api()
     */
    public $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Api()
     */
    public $lastName;

    /**
     * @ORM\Column(name="email", type="string", nullable=false, unique=true)
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Api
     */
    public $email;

    /**
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     * @Assert\NotNull()
     * @Assert\Type("boolean")
     * @Api()
     */
    public $isActive;

    /**
     * @ORM\Column(name="created_at", type="datetime_immutable", nullable=false)
     */
    private $createdAt;

    public function __construct(string $firstName, string $lastName, string $email, bool $isActive, Group $group)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->isActive = $isActive;
        $this->group = $group;

        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return (int)$this->id;
    }
}