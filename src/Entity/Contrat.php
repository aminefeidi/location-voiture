<?php

namespace App\Entity;

use App\Repository\ContratRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=ContratRepository::class)
 */
class Contrat
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="date")
     */
    private $dateDep;

    /**
     * @ORM\Column(type="date")
     */
    private $dateRet;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="contrats")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity=Voiture::class, inversedBy="contrat")
     * @ORM\JoinColumn(nullable=false)
     */
    private $voiture;

    /**
     * @Assert\Callback
     */
    public function validateDates(ExecutionContextInterface $context, $payload)
    {
        if(date('Y-m-d', strtotime($this->dateRet->format("Y-m-d"))) < date('Y-m-d', strtotime("+1 day", strtotime($this->dateDep->format("Y-m-d"))))){
            $context->buildViolation('Date de retour doit etre superieure au date de depart.')
                ->atPath('dateRet')
                ->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateDep(): ?\DateTimeInterface
    {
        return $this->dateDep;
    }

    public function setDateDep(\DateTimeInterface $dateDep): self
    {
        $this->dateDep = $dateDep;

        return $this;
    }

    public function getDateRet(): ?\DateTimeInterface
    {
        return $this->dateRet;
    }

    public function setDateRet(\DateTimeInterface $dateRet): self
    {
        $this->dateRet = $dateRet;

        return $this;
    }

    public function getClient(): ?client
    {
        return $this->client;
    }

    public function setClient(?client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getVoiture(): ?voiture
    {
        return $this->voiture;
    }

    public function setVoiture(?voiture $voiture): self
    {
        $this->voiture = $voiture;

        return $this;
    }

    public function isExpired(): bool
    {
        return date('Y-m-d', strtotime($this->dateRet->format("Y-m-d"))) < date('Y-m-d', time());
    }

    public function __toString() {
        $dateDepStr = date_format($this->dateDep, 'd/m/Y');
        $dateRetStr = date_format($this->dateRet, 'd/m/Y');
        return strval($this->getVoiture()->getMarque() . ' : ' . $dateDepStr . ' - ' . $dateRetStr);
    }
}
