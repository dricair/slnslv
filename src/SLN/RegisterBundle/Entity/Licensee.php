<?php

namespace SLN\RegisterBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="licensee")
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
     */
    protected $nom;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $prenom;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $naissance;

    /**
     * @ORM\Column(type="string", length=7)
     */
    protected $iuf;

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
    protected $autorisation_photos;


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
}
