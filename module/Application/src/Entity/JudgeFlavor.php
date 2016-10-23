<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity representing a judge "flavor".
 *
 * For federal court, that means either USMJ or USDJ. This should be set up just
 * once at installation time.
 *
 * @ORM\Entity
 * @ORM\Table(name="judge_flavors",uniqueConstraints={@ORM\UniqueConstraint(name="unique_judge_flavor",columns={"flavor"})})
 */
class JudgeFlavor
{
    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=60,options={"nullable":false})
     *
     * @var string
     */
    protected $flavor;

    /**
     * returns a string representation of this JudgeFlavor.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getFlavor();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get flavor.
     *
     * @return string
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * set flavor.
     *
     * @return JudgeFlavor
     */
    public function setFlavor($flavor)
    {
        $this->flavor = $flavor;

        return $this;
    }
}
