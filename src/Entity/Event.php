<?php

namespace App\Entity;

use App\Listeners\EventListener;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\EntityListeners([EventListener::class])]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('name')]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30, unique: true)]
    #[Assert\NotBlank(message: "Nom de la sortie obligatoire")]
    #[Assert\Length(min: 4, max: 30, minMessage: "Il faut au moins {{ limit }} caractères", maxMessage: "Pas plus de {{ limit }} caractères")]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan('today', message:'La sortie doit avoir lieu après l\'instant présent.')]
    private ?\DateTimeInterface $dateTimeStart = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $duration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\LessThan(propertyPath: 'dateTimeStart', message: 'On ne doit pas pouvoir s\'inscrire après le début de la sortie.')]
    private ?\DateTimeInterface $registrationDeadline = null;

    #[ORM\Column]
    #[Assert\Positive(message: 'Un nombre de participants négatif, vraiment ?')]
    #[Assert\LessThan(1000000, message: 'Il n\'y aura pas assez de place !')]
    private ?int $maxNbRegistration = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $infoEvent = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: 'Vous n\'avez pas renseigné d\'état')]
    private ?string $state = null;

    #[ORM\ManyToOne(inversedBy: 'plannedEvents')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Il faut un organisateur')]
    private ?User $planner = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'registeredFor')]
    private Collection $registered;


    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $annulation = null;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'event')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Il faut un lieu')]
    private ?Place $place = null;


    public function __construct()
    {
        $this->registered = new ArrayCollection();
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

    public function getDateTimeStart(): ?\DateTimeInterface
    {
        return $this->dateTimeStart;
    }

    public function setDateTimeStart(\DateTimeInterface $dateTimeStart): static
    {
        $this->dateTimeStart = $dateTimeStart;

        return $this;
    }

    public function getDuration(): ?\DateTimeInterface
    {
        return $this->duration;
    }

    public function setDuration(?\DateTimeInterface $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getRegistrationDeadline(): ?\DateTimeInterface
    {
        return $this->registrationDeadline;
    }

    public function setRegistrationDeadline(\DateTimeInterface $registrationDeadline): static
    {
        $this->registrationDeadline = $registrationDeadline;

        return $this;
    }

    public function getMaxNbRegistration(): ?int
    {
        return $this->maxNbRegistration;
    }

    public function setMaxNbRegistration(int $maxNbRegistration): static
    {
        $this->maxNbRegistration = $maxNbRegistration;

        return $this;
    }

    public function getInfoEvent(): ?string
    {
        return $this->infoEvent;
    }

    public function setInfoEvent(?string $infoEvent): static
    {
        $this->infoEvent = $infoEvent;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {

        $this->state = $state;

        return $this;
    }

    public function getPlanner(): ?User
    {
        return $this->planner;
    }

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

    public function getAnnulation(): ?string
    {
        return $this->annulation;
    }

    public function setAnnulation(?string $annulation): self
    {
        $this->annulation = $annulation;

        return $this;
    }

    public function addRegistered(User $registered): static
    {
        if (!$this->registered->contains($registered)) {
            $this->registered->add($registered);
        }

        return $this;
    }

    public function removeRegistered(User $registered): static
    {
        $this->registered->removeElement($registered);

        return $this;
    }

    public function getDurationInSeconds(): int
    {
        return $this->duration->format('H')*3600+$this->duration->format('i')*60;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): static
    {
        $this->place = $place;

        return $this;
    }
}
