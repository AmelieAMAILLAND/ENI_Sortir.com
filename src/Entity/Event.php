<?php

namespace App\Entity;

use App\Listeners\EventListener;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\EntityListeners([EventListener::class])]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('name')]
class Event
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['events.index'])]
    private ?int $id = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 30, unique: true)]
    #[Assert\NotBlank(message: "Nom de la sortie obligatoire")]
    #[Assert\Length(min: 4, max: 30, minMessage: "Il faut au moins {{ limit }} caractères", maxMessage: "Pas plus de {{ limit }} caractères")]
    #[Groups(['events.index'])]
    private ?string $name = null;

    /**
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan('today', message:'La sortie doit avoir lieu après l\'instant présent.')]
    #[Groups(['events.index'])]
    private ?\DateTimeInterface $dateTimeStart = null;

    /**
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    #[Groups(['events.index'])]
    private ?\DateTimeInterface $duration = null;

    /**
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\LessThan(propertyPath: 'dateTimeStart', message: 'On ne doit pas pouvoir s\'inscrire après le début de la sortie.')]
    #[Groups(['events.index'])]
    private ?\DateTimeInterface $registrationDeadline = null;

    /**
     * @var int|null
     */
    #[ORM\Column]
    #[Assert\Positive(message: 'Un nombre de participants négatif, vraiment ?')]
    #[Assert\LessThan(1000000, message: 'Il n\'y aura pas assez de place !')]
    #[Groups(['events.index'])]
    private ?int $maxNbRegistration = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['events.index'])]
    private ?string $infoEvent = null;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Vous n\'avez pas renseigné d\'état')]
    #[Groups(['events.index'])]
    private ?string $state = null;

    /**
     * @var User|null
     */
    #[ORM\ManyToOne(inversedBy: 'plannedEvents')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Il faut un organisateur')]
    #[Groups(['events.index'])]
    private ?User $planner = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'registeredFor')]
    #[Groups(['events.index'])]
    private Collection $registered;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $annulation = null;

    /**
     * @var Place|null
     */
    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'event')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Il faut un lieu')]
    private ?Place $place = null;


    // Constructeur

    public function __construct()
    {
        $this->registered = new ArrayCollection();
    }

    // Getters & Setters

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
     * @return \DateTimeInterface|null
     */
    public function getDateTimeStart(): ?\DateTimeInterface
    {
        return $this->dateTimeStart;
    }

    /**
     * @param \DateTimeInterface $dateTimeStart
     * @return $this
     */
    public function setDateTimeStart(\DateTimeInterface $dateTimeStart): static
    {
        $this->dateTimeStart = $dateTimeStart;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDuration(): ?\DateTimeInterface
    {
        return $this->duration;
    }

    /**
     * @param \DateTimeInterface|null $duration
     * @return $this
     */
    public function setDuration(?\DateTimeInterface $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getRegistrationDeadline(): ?\DateTimeInterface
    {
        return $this->registrationDeadline;
    }

    /**
     * @param \DateTimeInterface $registrationDeadline
     * @return $this
     */
    public function setRegistrationDeadline(\DateTimeInterface $registrationDeadline): static
    {
        $this->registrationDeadline = $registrationDeadline;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxNbRegistration(): ?int
    {
        return $this->maxNbRegistration;
    }

    /**
     * @param int $maxNbRegistration
     * @return $this
     */
    public function setMaxNbRegistration(int $maxNbRegistration): static
    {
        $this->maxNbRegistration = $maxNbRegistration;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInfoEvent(): ?string
    {
        return $this->infoEvent;
    }

    /**
     * @param string|null $infoEvent
     * @return $this
     */
    public function setInfoEvent(?string $infoEvent): static
    {
        $this->infoEvent = $infoEvent;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return $this
     */
    public function setState(string $state): static
    {

        $this->state = $state;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getPlanner(): ?User
    {
        return $this->planner;
    }

    /**
     * @param User|null $planner
     * @return $this
     */
    public function setPlanner(?User $planner): static
    {
        $this->planner = $planner;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getRegistered(): Collection
    {
        return $this->registered;
    }

    /**
     * @return string|null
     */
    public function getAnnulation(): ?string
    {
        return $this->annulation;
    }

    /**
     * @param string|null $annulation
     * @return $this
     */
    public function setAnnulation(?string $annulation): self
    {
        $this->annulation = $annulation;

        return $this;
    }

    /**
     * @param User $registered
     * @return $this
     */
    public function addRegistered(User $registered): static
    {
        if (!$this->registered->contains($registered)) {
            $this->registered->add($registered);
        }

        return $this;
    }

    /**
     * @param User $registered
     * @return $this
     */
    public function removeRegistered(User $registered): static
    {
        $this->registered->removeElement($registered);

        return $this;
    }

    /**
     * @return int
     */
    public function getDurationInSeconds(): int
    {
        return $this->duration->format('H')*3600+$this->duration->format('i')*60;
    }

    /**
     * @return Place|null
     */
    public function getPlace(): ?Place
    {
        return $this->place;
    }

    /**
     * @param Place|null $place
     * @return $this
     */
    public function setPlace(?Place $place): static
    {
        $this->place = $place;

        return $this;
    }

    /**
     * @param Site|null $site
     * @return $this
     */
    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @return Site|null
     */
    public function getSite(): ?Site
    {
        return $this->site;
    }
}
