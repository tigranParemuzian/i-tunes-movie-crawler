<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=MovieRepository::class)
 */
class Movie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups="movie-add")
     * @Assert\Length(min=5)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups="movie-add")
     * @Assert\Url(groups="movie-add")
     */
    private $link;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(groups="movie-add")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(groups="movie-add")
     */
    private $pubDate;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups="movie-add")
     * @Assert\Url(groups="movie-add")
     */
    private $image;

    /**
     * @ORM\OneToMany(targetEntity=UserLike::class, mappedBy="movie")
     */
    private $userLikes;

    public function __construct()
    {
        $this->userLikes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->id ? $this->getTitle() : 'new Movie';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPubDate(): ?\DateTimeInterface
    {
        return $this->pubDate;
    }

    public function setPubDate(\DateTimeInterface $pubDate): self
    {
        $this->pubDate = $pubDate;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection|UserLike[]
     */
    public function getUserLikes(): Collection
    {
        return $this->userLikes;
    }

    public function addUserLike(UserLike $userLike): self
    {
        if (!$this->userLikes->contains($userLike)) {
            $this->userLikes[] = $userLike;
            $userLike->setMovie($this);
        }

        return $this;
    }

    public function removeUserLike(UserLike $userLike): self
    {
        if ($this->userLikes->removeElement($userLike)) {
            // set the owning side to null (unless already changed)
            if ($userLike->getMovie() === $this) {
                $userLike->setMovie(null);
            }
        }

        return $this;
    }



    public function checkIsLiked(User $user): bool
    {

        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('user', $user));
        $data = $this->userLikes->matching($criteria);

        if($data->isEmpty() === false) {
            return true;
        }
        return false;
    }
}
