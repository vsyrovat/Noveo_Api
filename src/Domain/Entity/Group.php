<?php declare(strict_types=1);

namespace App\Domain\Entity;

use App\Framework\Changeset\Annotations\Api;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Persistence\Repository\GroupRepository")
 * @ORM\Table(name="`group`")
 */
class Group
{
    public const MAX_USERS_PER_GROUP = 2;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", nullable=false, unique=true)
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Assert\Length(max="30")
     * @Api()
     */
    private $name;

    /**
     * @var User[]
     * @ORM\OneToMany(targetEntity="App\Domain\Entity\User", mappedBy="group")
     */
    public $users;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->users = new ArrayCollection();
    }

    public function getId(): int
    {
        return (int)$this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function addUser(User $user)
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->group = $this;
        }
    }

    public function removeUser(User $user)
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            if ($user->group === $this) {
                $user->group = null;
            }
        }
    }
}