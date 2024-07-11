<?php

namespace App\Entity;

use App\Repository\BorrowingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BorrowingRepository::class)]
class Borrowing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'borrowings')]
    private ?User $user = null;

    #[ORM\Column(options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $borrowingAt = null;

    #[ORM\Column]
    private ?int $total = null;

    /**
     * @var Collection<int, Book>
     */
    #[ORM\ManyToMany(targetEntity: Book::class, inversedBy: 'borrowings')]
    private Collection $books;

    #[ORM\Column]
    private ?\DateTimeImmutable $dueDateAt = null;

    #[ORM\Column(type: "boolean")]
    private bool $isOverdue = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $returnDateAt = null;

    public function __construct()
    {
        $this->borrowingAt = new \DateTimeImmutable();
        $this->books = new ArrayCollection();
    }

    public function isOverdue(): bool 
    {
        return $this->returnDateAt === null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getBorrowingAt(): ?\DateTimeImmutable
    {
        return $this->borrowingAt;
    }

    public function setBorrowingAt(\DateTimeImmutable $borrowingAt): static
    {
        $this->borrowingAt = $borrowingAt;

        return $this;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(int $total): static
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return Collection<int, Book>
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): static
    {
        if (!$this->books->contains($book)) {
            $this->books->add($book);
        }

        return $this;
    }

    public function removeBook(Book $book): static
    {
        $this->books->removeElement($book);

        return $this;
    }

    public function getDueDateAt(): ?\DateTimeImmutable
    {
        return $this->dueDateAt;
    }

    public function setDueDateAt(\DateTimeImmutable $dueDateAt): static
    {
        $this->dueDateAt = $dueDateAt;

        return $this;
    }

    public function getIsOverdue(): bool
    {
        return $this->isOverdue;
    }

    public function setIsOverdue(bool $isOverdue): self
    {
        $this->isOverdue = $isOverdue;
        return $this;
    }

    public function getReturnDateAt(): ?\DateTimeImmutable
    {
        return $this->returnDateAt;
    }

    public function setReturnDateAt(?\DateTimeImmutable $returnDateAt): static
    {
        $this->returnDateAt = $returnDateAt;

        return $this;
    }
}
