<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert; // pour la validation des contraintes pour la base de données

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[UniqueEntity('name')]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank()]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    #[Assert\Positive()]
    private ?float $price = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    #[Assert\Positive()]
    private ?int $stock = null;

    #[ORM\OneToMany(targetEntity: CommandeArticle::class, mappedBy: 'Article', orphanRemoval: true)]
    private Collection $commandeArticles;

    public function __construct()
    {
        $this->commandeArticles = new ArrayCollection();
    }

 

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @return Collection<int, CommandeArticle>
     */
    public function getCommandeArticles(): Collection
    {
        return $this->commandeArticles;
    }

    public function addCommandeArticle(CommandeArticle $commandeArticle): static
    {
        if (!$this->commandeArticles->contains($commandeArticle)) {
            $this->commandeArticles->add($commandeArticle);
            $commandeArticle->setArticle($this);
        }

        return $this;
    }

    public function removeCommandeArticle(CommandeArticle $commandeArticle): static
    {
        if ($this->commandeArticles->removeElement($commandeArticle)) {
            // set the owning side to null (unless already changed)
            if ($commandeArticle->getArticle() === $this) {
                $commandeArticle->setArticle(null);
            }
        }

        return $this;
    }

    
  

}
