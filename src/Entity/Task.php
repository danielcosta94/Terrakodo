<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 * @ORM\Table(name="tasks")
 */
class Task implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(max = 255, maxMessage = "Name of task cannot be longer than {{ limit }} characters")
     * @Assert\NotBlank
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max = 255, maxMessage = "Description of task cannot be longer than {{ limit }} characters")
     */
    private ?string $description;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Range(min = 1, max = 5, notInRangeMessage = "You must choose between {{ min }} and {{ max }} priority levels")
     */
    private ?int $priority_level;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\Date
     */
    private ?\DateTimeInterface $date_completion;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPriorityLevel(): ?int
    {
        return $this->priority_level;
    }

    public function setPriorityLevel(int $priority_level): self
    {
        $this->priority_level = $priority_level;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDateCompletion(): \DateTimeInterface
    {
        return $this->date_completion;
    }

    /**
     * @param \DateTimeInterface $date_completion
     * @return Task
     */
    public function setDateCompletion(\DateTimeInterface $date_completion): Task
    {
        $this->date_completion = $date_completion;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'priority_level' => $this->priority_level,
            'date_completion' => $this->date_completion,
        ];
    }
}
