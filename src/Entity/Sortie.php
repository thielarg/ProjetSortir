<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SortieRepository::class)
 */
class Sortie
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
     *     max=50,
     *     maxMessage="{{ limit }} caractères requis au maximum, svp !")
     * @Assert\NotBlank(message="nom obligatoire !")
     * @ORM\Column(type="string", length=50)
     */
    private $nom;

    /**
     * @Assert\NotBlank(message="La date et l'heure de début sont obligatoires")
     * @Assert\GreaterThan("now", message="Cette valeur doit être supérieure à la date du jour")
     * @ORM\Column(type="datetime")
     */
    private $dateHeureDebut;

    /**
     * @Assert\NotBlank(message="La durée est obligatoire")
     * @ORM\Column(type="integer")
     */
    private $duree;

    /**
     * @Assert\NotBlank(message="La date limite d'inscription est obligatoire")
     * @Assert\LessThan(propertyPath="dateHeureDebut", message="Cette valeur doit être inférieure à la date de la sortie")
     * @Assert\GreaterThan("now", message="Cette valeur doit être supérieure à la date du jour")
     * @Assert\DateTime
     * @var string A "d-m-Y H:i:s" formatted value
     * @ORM\Column(type="datetime")
     */
    private $dateLimiteInscription;

    /**
     * @Assert\NotBlank(message="Le nombre d'inscriptions max est obligatoire")
     * @ORM\Column(type="integer")
     */
    private $nbInscriptionsMax;

    /**
     * @Assert\Length(
     *     min="2",
     *     minMessage="{{ limit }} caractères au minimum requis, svp !",
     *     max=255,
     *     maxMessage="{{ limit }} caractères requis au maximum, svp !")
     * @Assert\NotBlank(message="Les infos sortie sont obligatoires")
     * @ORM\Column(type="string", length=255)
     */
    private $infosSortie;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $estPublie;

    /**
     * @Assert\Length(
     *     min="2",
     *     minMessage="{{ limit }} caractères au minimum requis, svp !",
     *     max=255,
     *     maxMessage="{{ limit }} caractères requis au maximum, svp !")
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $motifAnnulation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Etat", inversedBy="sorties")
     */
    private $etat;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lieu", inversedBy="sorties")
     * @Assert\NotNull()
     */
    private $lieu;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="sorties")
     */
    private $site;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Participant", mappedBy="sorties")
     */
    private $participants;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Participant", inversedBy="sortiesOrganisees")
     */
    private $organisateur;

    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateHeureDebut(): ?\DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): self
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeInterface $dateLimiteInscription): self
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): self
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(string $infosSortie): self
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getEstPublie(): ?bool
    {
        return $this->estPublie;
    }

    public function setEstPublie(?bool $estPublie): self
    {
        $this->estPublie = $estPublie;

        return $this;
    }

    public function getMotifAnnulation(): ?string
    {
        return $this->motifAnnulation;
    }

    public function setMotifAnnulation(?string $motifAnnulation): self
    {
        $this->motifAnnulation = $motifAnnulation;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): self
    {
        $this->lieu = $lieu;

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
     * @return Collection|Participant[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): self
    {
        if($this->participants->count() <= $this->nbInscriptionsMax){
            if (!$this->participants->contains($participant)) {
                $this->participants[] = $participant;
                $participant->addSortie($this);
            }
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        if ($this->participants->contains($participant)) {
            $this->participants->removeElement($participant);
            $participant->removeSortie($this);
        }

        return $this;
    }

    public function getOrganisateur(): ?Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Participant $organisateur): self
    {
        $this->organisateur = $organisateur;

        return $this;
    }
}
