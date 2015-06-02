<?php

namespace SLN\RegisterBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="SLN\RegisterBundle\Entity\Repository\LicenseeRepository")
 * @ORM\Table(name="licensee")
 * @ORM\HasLifecycleCallbacks()
 */
 class Licensee {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     *
     @Assert\NotBlank(message="Merci d'entrer un nom.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max="100",
     *     minMessage="Le nom est trop court.",
     *     maxMessage="Le nom est trop long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $nom;

    /**
     * @ORM\Column(type="string", length=100)
     *
     @Assert\NotBlank(message="Merci d'entrer un prénom.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=3,
     *     max="100",
     *     minMessage="Le prénom est trop court.",
     *     maxMessage="Le prénom est trop long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    protected $prenom;

    /**
     * @ORM\Column(type="integer")
    @Assert\Choice(callback = "getGenders", message="Merci de sélectionner une valeur", groups={"Registration", "Profile"})
     */
    protected $sexe;
    
    public static function getGenders() {
        return array(0 => "Homme", 1 => "Femme");
    }

    /**
     * @ORM\Column(type="date")
     *
     @Assert\NotBlank(message="Merci d'entrer la date de naissance.", groups={"Registration", "Profile"})
     */
    protected $naissance;

    /**
     * @ORM\Column(type="string", length=7, nullable=True)
     */
    protected $iuf;
    
    /**
     * @ORM\Column(type="date", nullable=True)
     */
    protected $date_licence;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $inscription_ok;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $attestation_ok;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $photo_ok;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $certificat_ok;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $paiement_ok;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $autorisation_photos;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="licensees")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Groupe", inversedBy="licensees")
     * @ORM\JoinColumn(name="groupe_id", referencedColumnName="id", nullable=True)
     */
    protected $groupe;

    public function __construct()
    {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
        $this->setAutorisationPhotos(True);
        $this->setInscriptionOk(False);
        $this->setPhotoOk(False);
        $this->setCertificatOk(False);
        $this->setAttestationOk(False);
        $this->setPaiementOk(False);
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedValue()
    {
       $this->setUpdated(new \DateTime());
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Licensee
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     * @return Licensee
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string 
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set naissance
     *
     * @param \DateTime $naissance
     * @return Licensee
     */
    public function setNaissance($naissance)
    {
        $this->naissance = $naissance;

        return $this;
    }

    /**
     * Get naissance
     *
     * @return \DateTime 
     */
    public function getNaissance()
    {
        return $this->naissance;
    }

    /**
     * Set iuf
     *
     * @param string $iuf
     * @return Licensee
     */
    public function setIuf($iuf)
    {
        $this->iuf = $iuf;

        return $this;
    }

    /**
     * Get iuf
     *
     * @return string 
     */
    public function getIuf()
    {
        return $this->iuf;
    }

    /**
     * Set attestation_ok
     *
     * @param boolean $attestationOk
     * @return Licensee
     */
    public function setAttestationOk($attestationOk)
    {
        $this->attestation_ok = $attestationOk;

        return $this;
    }

    /**
     * Get attestation_ok
     *
     * @return boolean 
     */
    public function getAttestationOk()
    {
        return $this->attestation_ok;
    }

    /**
     * Set photo_ok
     *
     * @param boolean $photoOk
     * @return Licensee
     */
    public function setPhotoOk($photoOk)
    {
        $this->photo_ok = $photoOk;

        return $this;
    }

    /**
     * Get photo_ok
     *
     * @return boolean 
     */
    public function getPhotoOk()
    {
        return $this->photo_ok;
    }

    /**
     * Set certificat_ok
     *
     * @param boolean $certificatOk
     * @return Licensee
     */
    public function setCertificatOk($certificatOk)
    {
        $this->certificat_ok = $certificatOk;

        return $this;
    }

    /**
     * Get certificat_ok
     *
     * @return boolean 
     */
    public function getCertificatOk()
    {
        return $this->certificat_ok;
    }

    /**
     * Set autorisation_photos
     *
     * @param boolean $autorisationPhotos
     * @return Licensee
     */
    public function setAutorisationPhotos($autorisationPhotos)
    {
        $this->autorisation_photos = $autorisationPhotos;

        return $this;
    }

    /**
     * Get autorisation_photos
     *
     * @return boolean 
     */
    public function getAutorisationPhotos()
    {
        return $this->autorisation_photos;
    }

    /**
     * Set date_licence
     *
     * @param \DateTime $dateLicence
     * @return Licensee
     */
    public function setDateLicence($dateLicence)
    {
        $this->date_licence = $dateLicence;

        return $this;
    }

    /**
     * Get date_licence
     *
     * @return \DateTime 
     */
    public function getDateLicence()
    {
        return $this->date_licence;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Licensee
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Licensee
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set user
     *
     * @param \SLN\RegisterBundle\Entity\User $user
     * @return Licensee
     */
    public function setUser(\SLN\RegisterBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \SLN\RegisterBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set sexe
     *
     * @param integer $sexe
     * @return Licensee
     */
    public function setSexe($sexe)
    {
        $this->sexe = $sexe;

        return $this;
    }

    /**
     * Get sexe
     *
     * @return integer 
     */
    public function getSexe()
    {
        return $this->sexe;
    }

    /**
     * Set inscription_ok
     *
     * @param boolean $inscriptionOk
     * @return Licensee
     */
    public function setInscriptionOk($inscriptionOk)
    {
        $this->inscription_ok = $inscriptionOk;

        return $this;
    }

    /**
     * Get inscription_ok
     *
     * @return boolean 
     */
    public function getInscriptionOk()
    {
        return $this->inscription_ok;
    }

    /**
     * Set paiement_ok
     *
     * @param boolean $paiementOk
     * @return Licensee
     */
    public function setPaiementOk($paiementOk)
    {
        $this->paiement_ok = $paiementOk;

        return $this;
    }

    /**
     * Get paiement_ok
     *
     * @return boolean 
     */
    public function getPaiementOk()
    {
        return $this->paiement_ok;
    }

    /**
     * Set groupe
     *
     * @param \SLN\RegisterBundle\Entity\Groupe $groupe
     * @return Licensee
     */
    public function setGroupe(\SLN\RegisterBundle\Entity\Groupe $groupe = null)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return \SLN\RegisterBundle\Entity\Groupe 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }

}
