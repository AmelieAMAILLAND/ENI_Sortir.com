<?php

namespace App\Entity;

use App\Repository\PlaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: PlaceRepository::class)]
class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Le nom de lieu est obligatoire.')]
    #[Assert\Length(max: 50, maxMessage: 'Le nom ne doit pas faire plus de {{ limit }} caractères.')]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le nom de rue est obligatoire.')]
    #[Assert\Length(max: 50, maxMessage: 'La rue ne doit pas faire plus de {{ limit }} caractères.')]
    private ?string $street = null;

    #[ORM\Column(length: 5)]
    #[Assert\Regex(pattern: "/^\d{5}$/", message: "Vérifiez le code postal.")]
    private ?string $zipCode = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le nom de ville est obligatoire.')]
    #[Assert\Length(max: 50, maxMessage: 'La ville ne doit pas faire plus de {{ limit }} caractères.')]
    private ?string $city = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La latitude est obligatoire.')]
    #[Assert\Range(min: -90, max: 90, notInRangeMessage: 'La latitude doit être compris entre {{ min }} et {{ max }}.')]
    private ?float $latitude = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La longitude est obligatoire.')]
    #[Assert\Range(min: -180, max: 180, notInRangeMessage: 'La longitude doit être compris entre {{ min }} et {{ max }}.')]
    private ?float $longitude = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'place', orphanRemoval: true)]
    #[Ignore]
    private Collection $event;

    public function __construct()
    {
        $this->event = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvent(): Collection
    {
        return $this->event;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->event->contains($event)) {
            $this->event->add($event);
            $event->setPlace($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->event->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getPlace() === $this) {
                $event->setPlace(null);
            }
        }

        return $this;
    }
}
