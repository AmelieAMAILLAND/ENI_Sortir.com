<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Site;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['events.index'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message:'Email obligatoire.')]
    #[Assert\Email(message:'Format d\'email non valide.')]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message:'Mot de passe obligatoire.')]
    private ?string $password = null;


    #[ORM\Column(length: 255, nullable: true, unique: true)]
    #[Assert\NotBlank(message:'Le pseudo est obligatoire')]
    #[Assert\Length(max: 100, maxMessage: "Votre pseudo est trop long.")]
    #[Groups(['events.index'])]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 100, maxMessage: "Votre prénom est trop long.")]
    private ?string $first_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 100, maxMessage: "Votre nom est trop long.")]
    private ?string $last_name = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(pattern:"/^(\+33|0)[1-9](\d{2}){4}$/", message:"Vérifiez votre numéro de téléphone")]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilePicture = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'planner', orphanRemoval: true)]
    private Collection $plannedEvents;

    #[ORM\ManyToOne(targetEntity: Site::class, inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Site $site = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'registered')]
    private Collection $registeredFor;

    public function __construct()
    {
        $this->plannedEvents = new ArrayCollection();
        $this->registeredFor = new ArrayCollection();
    }
  
    // Getters et Setters 

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(?string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(?string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $lastName): static
    {
        $this->last_name = $lastName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }

    public function getProfilePictureOrDefault(): string
    {
        if ($this->profilePicture) {
            return 'uploads/' . $this->profilePicture;
        }

        return 'images/profile/ajouter_des_photos.png';
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getPlannedEvents(): Collection
    {
        return $this->plannedEvents;
    }

    public function addPlannedEvent(Event $plannedEvent): static
    {
        if (!$this->plannedEvents->contains($plannedEvent)) {
            $this->plannedEvents->add($plannedEvent);
            $plannedEvent->setPlanner($this);
        }

        return $this;
    }

    public function removePlannedEvent(Event $plannedEvent): static
    {
        if ($this->plannedEvents->removeElement($plannedEvent)) {
            // set the owning side to null (unless already changed)
            if ($plannedEvent->getPlanner() === $this) {
                $plannedEvent->setPlanner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getRegisteredFor(): Collection
    {
        return $this->registeredFor;
    }

    public function addRegisteredFor(Event $registeredFor): static
    {
        if (!$this->registeredFor->contains($registeredFor)) {
            $this->registeredFor->add($registeredFor);
            $registeredFor->addRegistered($this);
        }

        return $this;
    }

    public function removeRegisteredFor(Event $registeredFor): static
    {
        if ($this->registeredFor->removeElement($registeredFor)) {
            $registeredFor->removeRegistered($this);
        }

        return $this;
    }
}
