<?php

namespace App\Entity;

use App\Repository\SiteRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
class Site
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: "nom du site obligatoire")]
    private ?string $name = null;

    /**
     * @var Collection|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'site', fetch: 'EAGER')]
    private Collection $users;


    // Constructeur

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    // Getters et Setters

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @return string
     * Obtenir les noms complets des utilisateurs associés.
     * Retourner sous forme de chaîne de caractères.
     */
    public function getUserNames(): string
    {
        $userNames = $this->getUsers()->map(function ($user) {
            return $user->getFirstName() . ' ' . $user->getLastName();
        })->toArray();

        return implode(', ', $userNames);
    }

    private function getEvents(): Collection
    {
        return $this->events;
    }

    // Fonctions & Méthodes

    /**
     * @return int
     */
    public function getEventCount(): int
    {
        return $this->events->count();
    }

    /**
     * @param Event $event
     * @return $this
     * Retirer un évènement de la collection d'évènements associés à un site.
     * Mettre à jour l'association entre le site et l'évènement.
     */
    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            if ($event->getSite() === $this) {
                $event->setSite(null);
            }
        }
        return $this;
    }

    /**
     * @return string
     * Obtenir les évènements associés à un objet.
     */
    public function getFormattedEvents(): string
    {
        $events = $this->getEvents();
        $details = '';

        foreach ($events as $event) {
            $details .= sprintf('%s: %s' . PHP_EOL, $event->getName(), $event->getState());
        }

        return $details;
    }




}
