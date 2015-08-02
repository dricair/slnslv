<?php
/**
  * Represents a Licensee
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\User;

/**
 * Licensee class, representing an inscription.
 *
 * @ORM\Entity(repositoryClass="SLN\RegisterBundle\Entity\Repository\LicenseeRepository")
 * @ORM\Table(name="licensee")
 * @ORM\HasLifecycleCallbacks()
 */
 class Licensee {

    /**
     * @var int $id Id of the Licensee
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $nom Name of the licensee
     * @ORM\Column(type="string", length=100)
     *
     * @Assert\NotBlank(message="Merci d'entrer un nom.", groups={"Registration", "Profile"})
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
     * @var string $prenom Family name
     * @ORM\Column(type="string", length=100)
     *
     * @Assert\NotBlank(message="Merci d'entrer un prénom.", groups={"Registration", "Profile"})
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
     * var int $sexe Integer value for gender
     * @ORM\Column(type="integer")
     * @Assert\Choice(callback = "getGenders", message="Merci de sélectionner une valeur", groups={"Registration", "Profile"})
     */
    protected $sexe;
    
    const HOMME = 0;
    const FEMME = 1;

    /**
     * Return an array to convert integer to string for gender
     *
     * @return string[] List of string for genders.
     */
    public static function getGenders() {
        return array(self::HOMME => "Homme", self::FEMME => "Femme");
    }

    /**
     * @var \Datetime $naissance Birth date
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank(message="Merci d'entrer la date de naissance.", groups={"Registration", "Profile"})
     */
    protected $naissance;

    /**
     * @var string $iuf License number
     * @ORM\Column(type="string", length=7, nullable=True)
     */
    protected $iuf;
    
    /**
     * @var \Datetime $date_licence Date for license creation
     * @ORM\Column(type="date", nullable=True)
     */
    protected $date_licence;

    /**
     * @var bool $officiel True if licensee is an officiel
     * @ORM\Column(type="boolean")
     */
    protected $officiel;

    /**
     * @var bool $bureau True if licensee is member of the Bureau
     * @ORM\Column(type="boolean")
     */
    protected $bureau;

    /**
     * @var bool $inscription_ok True if inscription is ok.
     * @ORM\Column(type="boolean")
     */
    protected $inscription_ok;

    /**
     * @var bool $attestation_ok True if medical attestion is verified
     * @ORM\Column(type="boolean")
     */
    protected $attestation_ok;

    /**
     * @var bool $photo_ok True if provided photo is ok
     * @ORM\Column(type="boolean")
     */
    protected $photo_ok;

    /**
     * @var bool $certificat_ok True if medical attestion is verified
     * @ORM\Column(type="boolean")
     */
    protected $certificat_ok;

    /**
     * @var bool $paiement_ok True if payment is ok.
     * @ORM\Column(type="boolean")
     */
    protected $paiement_ok;

    /**
     * @var bool $autorisation_photos True if authorization for photos is ok
     * @ORM\Column(type="boolean")
     */
    protected $autorisation_photos;

    /**
     * @var \Datetime $created Date of creation
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var \Datetime $updated Date of last update
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @var User $user Connected User class
     * @ORM\ManyToOne(targetEntity="User", inversedBy="licensees")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Assert\NotBlank(message="Un licencié doit être rattaché à un utilisateur.", groups={"Registration", "Profile"})
     */
    protected $user;

    /**
     * @var Groupe $groupe Selected groupe
     * @ORM\ManyToOne(targetEntity="Groupe", inversedBy="licensees")
     * @ORM\JoinColumn(name="groupe_id", referencedColumnName="id", nullable=True)
     */
    protected $groupe;

    /**
     * Return Age in years
     * 
     * @return int Age in years, rounded down.
     */
    public function getAge() {
      $now = new \DateTime();
      return $this->naissance->diff($now)->y;
    }

    /**
     * Return true if licensee if less than 18
     * 
     * @return bool True if licensee is less than 18
     */
    public function isMineur() {
        return $this->getAge() < 18;
    }


    /**
     * Write inscription sheet to a PDF
     *
     * @param TCPDF $pdf PDF to edit.
     * @param Asset[] $assets List of assets to access pictures
     * @param string $title Title if the PDF needs to be started
     *
     * @return TCPDF Generated PDF
     *
     */
    public function inscriptionSheet($pdf, $assets, $title='') {
        assert($this->groupe != Null);

        if ($title != "") {
          $pdf->SetAuthor("Stade Laurentin Natation <slnslv@free.fr>");
          $pdf->SetTitle($title);

          $pdf->setPrintHeader(false);
          $pdf->setPrintFooter(false);
          $pdf->SetFont('dejavusans', '', 10);
          $pdf->SetMargins(10, 10, 10, 10);
          $pdf->SetAutoPageBreak(FALSE, PDF_MARGIN_BOTTOM);
        }

        $year = date('Y');
        $month = date('n');
        if ($month < 5) $year = $year - 1;

        $groupeValues = array(Groupe::ECOLE       => array('title'      => 'Dossier d\'inscription Ecole',
                                                           'certificat' => '1 certificat médical d\'aptitude à la pratique de la natation'),
                              Groupe::COMPETITION => array('title'      => 'Dossier d\'inscription Competition',
                                                           'certificat' => '1 certificat médical d’absence de contre-indication à la 
                                                                            <span style="text-decoration: underline;font-weight: bold;">pratique sportive de la natation en 
                                                                            compétition</span> (Article L3622-2 du code de la santé publique)'),
                              Groupe::LOISIR      => array('title'      => "Dossier d'inscription Loisirs",
                                                           'certificat' => Null)
                             );
        $values = $groupeValues[$this->groupe->getCategorie()];
        $femme = $this->sexe == $this::FEMME;

        $pdf->AddPage();
        $pdf->Image($assets->getUrl('bundles/slnregister/images/logo_club_t.png'), 10, 0, 30);
        $pdf->Image($assets->getUrl('bundles/slnregister/images/titre_club_t.png'), 60, 5, 90);
        if ($this->groupe->getCategorie() == Groupe::COMPETITION)
          $pdf->Image($assets->getUrl('bundles/slnregister/images/logo_ffn.png'), 160, 5, 40);
        else if ($this->groupe->getCategorie() == Groupe::ECOLE)
          $pdf->Image($assets->getUrl('bundles/slnregister/images/ecole_natation.gif'), 160, 5, 40);
          
        $html = sprintf('
<p style="color:#888888;margin:2">Affilié à la F.F.N et agréé E.N.F<br/>
Site: http://stadelaurentinnatatin.fr</p>');
        $pdf->WriteHTMLCell(/*w*/0, /*h*/0, /*x*/10, /*y*/20, $html, /*border*/0, /*ln*/1, /*fill*/0, /*reseth*/true, /*align*/'C', /*autopadding*/false);

        $html = sprintf('
<div style="text-align:center; color:#1f487c; font-weight: bold; font-size: 14; ">%s - SAISON %d-%d</div>
<ul>
  <li>1 photo d\'identité</li>', strtoupper($values['title']), $year, $year+1);

        if ($values['certificat']) {
            $html .= "
  <li>{$values['certificat']}</li>";
        }

        $html .= "
  <li>La présente fiche de renseignements signée</li>
  <li>La feuille de licence de la Fédération Française de Natation remplie et signée</li>
  <li>Le règlement de la cotisation</li>
</ul>";
        $pdf->WriteHTMLCell(/*w*/0, /*h*/0, /*x*/10, /*y*/35, $html, /*border*/1, /*ln*/1, /*fill*/0, /*reseth*/true, /*align*/'C', /*autopadding*/false);

        $html = sprintf('
<div style="text-align:center; color:#1f487c; font-weight: bold; font-size: 14; ">RENSEIGNEMENTS LICENCIE%s</div>
<table border="0" cellspacing="2mm">
<tr>
  <td align="right" width="30mm" color="#888888">Nom %s&nbsp;:</td><td align="left" width="60mm" style="font-weight: bold;">%s</td>
  <td align="right" width="30mm" color="#888888">Prénom&nbsp;:</td><td align="left" width="60mm" style="font-weight: bold;">%s</td>
</tr>
<tr>
  <td align="right" color="#888888">%s</td><td align="left">%s</td>
  <td align="right" color="#888888">Date de naissance&nbsp;:</td><td align="left">%s</td>
</tr>
<tr>
  <td rowspan="2" align="right" color="#888888">Adresse&nbsp;:</td><td rowspan="2" align="left">%s</td>
  <td align="right" color="#888888">Code postal&nbsp;:</td><td align="left">%s</td>
</tr>
<tr>
  <td align="right" color="#888888">Ville&nbsp;:</td><td align="left">%s</td>
</tr>
<tr>
  <td align="right" color="#888888">Téléphone&nbsp;:</td><td align="left">%s</td>
  <td align="right" color="#888888">Portable&nbsp;:</td><td align="left">%s</td>
</tr>
<tr>
  <td align="right" color="#888888">Email&nbsp;:</td><td align="left">%s</td>
  <td align="right" color="#888888">IUF&nbsp;:</td><td align="left">%s</td>
</tr>
</table>
        ', $femme ? "E" : "", $femme ? "de la nageuse" : "du nageur", $this->nom, $this->prenom,
        $this->isMineur() ? "Responsable légal&nbsp;:" : "&nbsp;", $this->isMineur() ? "{$this->user->getPrenom()} {$this->user->getNom()}" : "&nbsp;",
        $this->naissance->format("d/m/Y"), nl2br($this->user->getAdresse()), $this->user->getCodePostal(), $this->user->getVille(),
        $this->user->getTelDomicile(), $this->user->getTelPortable(), $this->user->getEmail(), $this->iuf);
        $pdf->WriteHTMLCell(/*w*/0, /*h*/0, /*x*/10, /*y*/80, $html, /*border*/0, /*ln*/1, /*fill*/0, /*reseth*/true, /*align*/'C', /*autopadding*/false);

        $html = sprintf('
<div style="text-align:center; color:#1f487c; font-weight: bold; font-size: 14; ">ATTESTATION %s</div>
<ul>', $this->isMineur() ? "DES PARENTS POUR UN MINEUR" : "");

        $html .= sprintf('
  <li>Je soussigné(e), %s %s %s, déclare %s au Stade Laurentin Natation,</li>',
                     $this->user->getTitreName(), $this->user->getPrenom(), $this->user->getNom(),
                     $this->isMineur() ? sprintf("avoir inscrit %s %s %s", $femme ? "ma fille" : "mon fils", $this->prenom, $this->nom) : 
                                         sprintf("m'être inscrit%s", $femme ? "e" : ""));

        $html .= sprintf('
  <li>J\'atteste %sêtre à jour dans le paiement de la cotisation,</li>', $values['certificat'] ? "avoir fourni le certificat médical obligatoire et " : "");

        if ($this->isMineur()) {
            $html .= sprintf('
  <li><span %s>J\'accepte que les résultats sportifs, le nom  ainsi que la photo (podium, photo de groupe...) de mon enfant puissent apparaître 
        sur le site internet  du club ou de documents destinés à la recherche de sponsors pour le club,</span></li>
  <li>Je m\'engage à déposer mon enfant à la piscine pour qu\'il soit à l\'heure à son entraînement, et le récupérer à la fin du cours, 
          le club n’étant pas responsable,</li>', 
                     $this->autorisation_photos ? "" : 'style="text-decoration: line-through;"');
        }

        $html .= '
  <li>Au début et à la fin des cours, l’installation et la désinstallation des lignes sont à la charge des adhérents. Merci de votre compréhension.</li>
</ul>

<div style="text-align:center; color: #ce0000; text-decoration: underline; font-weight: bold">J\'ai bien pris note qu\'aucun remboursement ne sera effectué en 
   cours de saison
</div>';
        $pdf->WriteHTMLCell(/*w*/0, /*h*/0, /*x*/10, /*y*/140, $html, /*border*/0, /*ln*/1, /*fill*/0, /*reseth*/true, /*align*/'C', /*autopadding*/false);
        
        $now = new \DateTime();
        $html = sprintf('<p>A %s, le %s</p>', $this->user->getVille(), $now->format("d/m/Y"));
        $pdf->WriteHTMLCell(/*w*/80, /*h*/0, /*x*/10, /*y*/235, $html, /*border*/0, /*ln*/1, /*fill*/0, /*reseth*/true, /*align*/'C', /*autopadding*/false);

        $html = sprintf('<div >Signature %s&nbsp;</div>', $this->isMineur() ? "du responsable légal" : "");
        $pdf->WriteHTMLCell(/*w*/0, /*h*/20, /*x*/100, /*y*/235, $html, /*border*/array('LTRB' => array('width' => 0.2, 'color' => array(0xcc, 0xcc, 0xcc))), 
          /*ln*/1, /*fill*/0, /*reseth*/true, /*align*/'L', /*autopadding*/false);

        $html = sprintf('
<div style="color:#888888;">Cadre réservé</div>
<table border="0" cellspacing="3mm" color="#888888">
<tr>
  <td>Certificat</td> <td>Licence</td ><td>Photo</td> <td>Groupe</td> <td>Chèque</td> <td>Espèces</td>
</tr>
<tr>
  <td style="border: 1px solid #888888;">&nbsp;</td> <td style="border: 1px solid #888888;">&nbsp;</td> 
  <td style="border: 1px solid #888888;">&nbsp;</td> <td style="border: 1px solid #888888;">%s</td> 
  <td style="border: 1px solid #888888;">&nbsp;</td> <td style="border: 1px solid #888888;">&nbsp;</td>
</tr>
</table>
', $this->groupe->getNom());
        $pdf->WriteHTMLCell(/*w*/0, /*h*/0, /*x*/10, /*y*/265, $html, /*border*/array('LTRB' => array('width' => 0.2, 'color' => array(0xcc, 0xcc, 0xcc))), 
          /*ln*/1, /*fill*/0, /*reseth*/true, /*align*/'C', /*autopadding*/false);
        
        $pdf->EndPage();
        
        return $pdf;
    }

    /** @ignore */
    public function __construct()
    {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
        $this->setNaissance(new \DateTime("2000-01-01"));
        $this->setAutorisationPhotos(True);
        $this->setInscriptionOk(False);
        $this->setBureau(false);
        $this->setOfficiel(false);
        $this->setPhotoOk(False);
        $this->setCertificatOk(False);
        $this->setAttestationOk(False);
        $this->setPaiementOk(False);
    }

    /**
     * @ignore
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
     * @param DateTime $naissance
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
     * @return DateTime 
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
     * @param DateTime $dateLicence
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
     * @return DateTime 
     */
    public function getDateLicence()
    {
        return $this->date_licence;
    }

    /**
     * Set created
     *
     * @param DateTime $created
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
     * @return DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param DateTime $updated
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
     * @return DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return Licensee
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User 
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
     * @param Groupe $groupe
     * @return Licensee
     */
    public function setGroupe(Groupe $groupe = null)
    {
        $this->groupe = $groupe;

        return $this;
    }

    /**
     * Get groupe
     *
     * @return Groupe 
     */
    public function getGroupe()
    {
        return $this->groupe;
    }


    /**
     * Set officiel
     *
     * @param boolean $officiel
     * @return Licensee
     */
    public function setOfficiel($officiel)
    {
        $this->officiel = $officiel;

        return $this;
    }

    /**
     * Get officiel
     *
     * @return boolean 
     */
    public function getOfficiel()
    {
        return $this->officiel;
    }

    /**
     * Set bureau
     *
     * @param boolean $bureau
     * @return Licensee
     */
    public function setBureau($bureau)
    {
        $this->bureau = $bureau;

        return $this;
    }

    /**
     * Get bureau
     *
     * @return boolean 
     */
    public function getBureau()
    {
        return $this->bureau;
    }
}
