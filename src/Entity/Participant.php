<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ParticipantRepository::class)
 * @UniqueEntity(fields={"mail"})
 * @UniqueEntity(fields={"username"})
 * @ORM\Table(name="participants")
 */
class Participant implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\Length(
     *     min="2",
     *     minMessage="{{ limit }} caractères au minimum requis, svp !",
     *     max=30,
     *     maxMessage="{{ limit }} caractères requis au maximum, svp !")
     * @Assert\NotBlank(message="Le pseudo est obligatoire !")
     * @ORM\Column(type="string", length=30, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\Length(
     *     min="2",
     *     minMessage="{{ limit }} caractères au minimum requis, svp !",
     *     max=50,
     *     maxMessage="{{ limit }} caractères requis au maximum, svp !")
     * @Assert\NotBlank(message="Le nom est obligatoire !")
     * @ORM\Column(type="string", length=50)
     */
    private $nom;

    /**
     * @Assert\Length(
     *     min="2",
     *     minMessage="{{ limit }} caractères au minimum requis, svp !",
     *     max=50,
     *     maxMessage="{{ limit }} caractères requis au maximum, svp !")
     * @Assert\NotBlank(message="Le prénom est obligatoire !")
     * @ORM\Column(type="string", length=50)
     */
    private $prenom;

    /**
     * @Assert\Length(
     *     min="2",
     *     minMessage="{{ limit }} caractères au minimum requis, svp !",
     *     max=10,
     *     maxMessage="{{ limit }} caractères requis au maximum, svp !")
     * @Assert\NotBlank(message="Le telephone est obligatoire !")
     * @ORM\Column(type="string", length=10)
     */
    private $telephone;

    /**
     * @Assert\Length(
     *     min="3",
     *     minMessage="{{ limit }} caractères au minimum requis, svp !",
     *     max=50,
     *     maxMessage="{{ limit }} caractères requis au maximum, svp !")
     * @Assert\NotBlank(message="L'email est obligatoire !")
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $mail;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $administrateur;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $actif;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    private $photo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $resetToken;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="participants")
     */
    private $site;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Sortie", inversedBy="participants")
     */
    private $sorties;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Sortie", mappedBy="organisateur")
     */
    private $sortiesOrganisees;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
        $this->sortiesOrganisees = new ArrayCollection();
        if ($this->getPhoto() == null){
            $this->setPhoto("imageProfilDefaut.jpg");
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(?string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(?bool $administrateur): self
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @return Collection|Sortie[]
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSortie(Sortie $sortie): self
    {
        if($sortie->getParticipants()->count() <= $sortie->getNbInscriptionsMax()){
            if (!$this->sorties->contains($sortie)) {
                $this->sorties[] = $sortie;
            }
        }

        return $this;
    }

    public function removeSortie(Sortie $sortie): self
    {
        if ($this->sorties->contains($sortie)) {
            $this->sorties->removeElement($sortie);
        }

        return $this;
    }

    /**
     * @return Collection|Sortie[]
     */
    public function getSortiesOrganisees(): Collection
    {
        return $this->sortiesOrganisees;
    }

    public function addSortieOrganisee(Sortie $sortieOrganisee): self
    {
        if (!$this->sortiesOrganisees->contains($sortieOrganisee)) {
            $this->sortiesOrganisees[] = $sortieOrganisee;
            $sortieOrganisee->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSortieOrganisee(Sortie $sortieOrganisee): self
    {
        if ($this->sortiesOrganisees->contains($sortieOrganisee)) {
            $this->sortiesOrganisees->removeElement($sortieOrganisee);
            // set the owning side to null (unless already changed)
            if ($sortieOrganisee->getOrganisateur() === $this) {
                $sortieOrganisee->setOrganisateur(null);
            }
        }

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
