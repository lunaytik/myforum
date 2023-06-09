<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post as Poster;
use App\Controller\PostUserController;
use App\Controller\UploadPostController;
use App\Repository\PostRepository;
use App\State\PostCollectionProvider;
use App\State\PostItemProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/post_detail/{id}',
            requirements: ['id' => '\d+'],
            normalizationContext: ['groups' => 'post:item'],
            provider: PostItemProvider::class,
        ),
        new Get(
            normalizationContext: ['groups' => 'post:item'],
        ),
        new GetCollection(
            controller: PostUserController::class,
            normalizationContext: ['groups' => 'post:list'],
        ),
        new Poster(
            inputFormats: ['multipart' => ['multipart/form-data']],
            controller: UploadPostController::class,
            denormalizationContext: ['groups' => 'post:create'],
            deserialize: false
        ),
        new Delete()
    ],
    order: ['createdAt' => 'DESC'],
    paginationEnabled: true,
    paginationItemsPerPage: 20,
)]
//#[ApiFilter(SearchFilter::class, properties: ['likedPosts.user' => 'exact'])]
class Post
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['post:list', 'post:item', 'comment:list', 'post:create'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank()]
    #[Groups(['post:list', 'post:item', 'post:create'])]
    private ?string $title = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank()]
    #[Groups(['post:list', 'post:item', 'post:create'])]
    private ?string $text = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank()]
    #[Groups(['post:list', 'post:item', 'post:create'])]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class, cascade: ['persist', 'remove'])]
    #[Groups(['post:item'])]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'post', targetEntity: LikedPost::class, cascade: ['persist', 'remove'])]
    private Collection $likedPosts;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['post:list', 'post:item'])]
    private ?string $image = null;

    #[Groups(['post:create'])]
    #[SerializedName('file')]
    private ?UploadedFile $file = null;


    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->likedPosts = new ArrayCollection();
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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, LikedPost>
     */
    public function getLikes(): Collection
    {
        return $this->likedPosts;
    }

    public function addLikedPost(LikedPost $likedPost): self
    {
        if (!$this->likedPosts->contains($likedPost)) {
            $this->likedPosts->add($likedPost);
            $likedPost->setPost($this);
        }

        return $this;
    }

    public function removeLikedPost(LikedPost $likedPost): self
    {
        if ($this->likedPosts->removeElement($likedPost)) {
            // set the owning side to null (unless already changed)
            if ($likedPost->getPost() === $this) {
                $likedPost->setPost(null);
            }
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }



    #[Groups(['post:list', 'post:item'])]
    #[SerializedName('createdAt')]
    public function getCreatedAtTimestampable(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
}
