<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

// ApiResource (API Platform) : Utilisé pour exposer l'entité via une API REST. Si tu utilises API Platform dans ton projet, cette annotation rend l'entité accessible à travers des routes API automatiquement.

// Il expose les routes pour chaque entité (GET, POST, PUT, DELETE).
// Gère les formats JSON et XML.
// Génère automatiquement la documentation de l’API via Swagger.

// UserRepository (Doctrine) : Utilisé pour la gestion des interactions avec la base de données. Il te permet de définir des requêtes personnalisées et d'accéder aux entités via Doctrine ORM.


#[ORM\Entity(repositoryClass: UserRepository::class)]

#[ApiResource]

class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $email;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Post::class)]
    private $posts;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comment::class)]
    private $comments;



    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    // Getter et setter pour $id
    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter et setter pour $name
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    // Getter et setter pour $email
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    // Getter pour $posts (relation avec Post)
    /**
     * @return Collection|Post[]
     */

    // @return Collection|Post[] Cela indique que la méthode peut retourner soit une instance de la classe Collection, soit un tableau (array) d'objets Comment.
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // Déconnecte le post de l'utilisateur s'il est lié
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

        return $this;
    }

    // Getter pour $comments (relation avec Comment)
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
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // Déconnecte le commentaire de l'utilisateur s'il est lié
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

}
