<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 */
class Customer
{
    /**
     * Customer's ID
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Customer's lastname
     * @ORM\Column(type="string", length=50)
     */
    private $lastName;

    /**
     * Customer's firstname
     * @ORM\Column(type="string", length=50)
     */
    private $firstName;

    /**
     * Is he subscribed to the newsletter ?
     * @ORM\Column(type="boolean")
     */
    private $newsletter;

    /**
     * User linked to this customer
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="customer", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * All the comments posted by this customer
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="customer", orphanRemoval=true)
     */
    private $comments;

    /**
     * All reports made by this customer
     * @ORM\OneToMany(targetEntity=Report::class, mappedBy="Customer", orphanRemoval=true)
     */
    private $reports;

    /**
     * All images linked to this customers
     * @ORM\OneToMany(targetEntity=Image::class, mappedBy="customer", cascade={"persist", "remove"})
     */
    private $images;

    /**
     * Customer's favourite providers
     * @ORM\ManyToMany(targetEntity=Provider::class, mappedBy="favorite")
     */
    private $favorites;

    /**
     * Customer's avatar
     * @ORM\Column(type="string", length=255)
     */
    private $avatar;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->favorites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getNewsletter(): ?bool
    {
        return $this->newsletter;
    }

    public function setNewsletter(bool $newsletter): self
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setCustomer($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCustomer() === $this) {
                $comment->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Report[]
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports[] = $report;
            $report->setCustomer($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getCustomer() === $this) {
                $report->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setCustomer($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getCustomer() === $this) {
                $image->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Provider[]
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Provider $favorite): self
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites[] = $favorite;
            $favorite->addFavorite($this);
        }

        return $this;
    }

    public function removeFavorite(Provider $favorite): self
    {
        if ($this->favorites->removeElement($favorite)) {
            $favorite->removeFavorite($this);
        }

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }
}
