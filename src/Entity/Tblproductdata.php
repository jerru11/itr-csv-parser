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
     * @var \DateTime|null
     *
     * @ORM\Column(name="dtmAdded", type="datetime", nullable=true)
     */
    private $dtmadded;

    /**
     * @var boolean
     *
     * @ORM\Column(name="boolDiscontinued", type="boolean", nullable=false, options={"default"=false})
     */
    private $booldiscontinued;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="stmTimestamp", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $stmtimestamp = 'CURRENT_TIMESTAMP';

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $intStock;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $fltCost;

    public function getIntStock(): ?int
    {
        return $this->intStock;
    }

    public function setIntStock(?int $intStock): self
    {
        $this->intStock = $intStock;

        return $this;
    }

    public function getFltCost(): ?float
    {
        return $this->fltCost;
    }

    public function setFltCost(?float $fltCost): self
    {
        $this->fltCost = $fltCost;

        return $this;
    }


}
