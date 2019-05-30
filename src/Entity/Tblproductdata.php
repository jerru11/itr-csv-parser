<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tblproductdata
 *
 * @ORM\Table(name="tblProductData", uniqueConstraints={@ORM\UniqueConstraint(name="strProductCode", columns={"strProductCode"})})
 * @ORM\Entity
 */
class Tblproductdata
{

    public function __construct()
    {
        $this->stmtimestamp=new \DateTime();
    }

    /**
     * @var int
     *
     * @ORM\Column(name="intProductDataId", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $intproductdataid;

    /**
     * @var string
     *
     * @ORM\Column(name="strProductName", type="string", length=50, nullable=false)
     */
    private $strproductname;

    /**
     * @var string
     *
     * @ORM\Column(name="strProductDesc", type="string", length=255, nullable=false)
     */
    private $strproductdesc;

    /**
     * @var string
     *
     * @ORM\Column(name="strProductCode", type="string", length=10, nullable=false)
     */
    private $strproductcode;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="stmTimestamp", type="datetime", nullable=false, options={"default": "CURRENT_TIMESTAMP"})
     */
    private $stmtimestamp = '';

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $intstock;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $fltcost;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dtmdiscontinued;

    public function getIntstock(): ?int
    {
        return $this->intstock;
    }

    public function setIntstock(?int $intstock): self
    {
        $this->intstock = $intstock;

        return $this;
    }

    public function getFltcost(): ?float
    {
        return $this->fltcost;
    }

    public function setFltCost(?float $fltcost): self
    {
        $this->fltcost = $fltcost;

        return $this;
    }

    public function getStrProductName(): ?string
    {
        return $this->strproductname;
    }

    public function setStrProductName(?string $strProductName): ?self
    {
        $this->strproductname = $strProductName;
        return $this;
    }

    public function getStrProductCode(): ?string
    {
        return $this->strproductcode;
    }

    public function setStrProductCode(?string $strProductCode): ?self
    {
        $this->strproductcode = $strProductCode;
        return $this;
    }

    public function getStrProductDesc(): ?string
    {
        return $this->strproductdesc;
    }

    public function setStrProductDesc(?string $strProductDesc): ?self
    {
        $this->strproductdesc = $strProductDesc;
        return $this;
    }


    public function getDtmDiscontinued(): ?\DateTimeInterface
    {
        return $this->dtmdiscontinued;
    }

    public function setDtmDiscontinued(?\DateTimeInterface $dtmDiscontinued): self
    {
        $this->dtmdiscontinued = $dtmDiscontinued;

        return $this;
    }

}
