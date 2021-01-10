<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=UtilisateurRepository::class)
 * @UniqueEntity(
 *      fields = "email",
 *      message = "Cette adresse e-mail est deja utlisée."
 * )
 */
class Utilisateur implements UserInterface, \Serializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $motDePasse;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="utilisateurs")
     */
    private $agence;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getMotDePasse(): ?string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): self
    {
        $this->motDePasse = $motDePasse;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAgence(): ?agence
    {
        return $this->agence;
    }

    public function setAgence(?agence $agence): self
    {
        $this->agence = $agence;

        return $this;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return $this->motDePasse;
    }

    public function getRoles()
    {
        if($this->type == 0){
            return array('ROLE_ADMIN');
        }else{
            return array('ROLE_AGENT');
        }
    }

    public function eraseCredentials()
    {

    }

    public function getSalt()
    {
        return null;
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->motDePasse,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->motDePasse,
        ) = unserialize($serialized, array('allowed_classes' => false));
    }
}
