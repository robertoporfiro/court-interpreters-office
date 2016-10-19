<?php

// module/Application/src/Application/Entity/Hat.php

/**
 * class representing the "hat" a person wears, e.g., 
 * staff interpreter, contract interpreter, AUSA, USPO, 
 * defense attorney, etc
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

//uniqueConstraints={@UniqueConstraint(name="search_idx", columns={"name", "email"})})

/** @ORM\Entity  @ORM\Table(name="hats",uniqueConstraints={@ORM\UniqueConstraint(name="hat_idx",columns={"name"})}) */

class Hat
{

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=50,nullable=false)
     * @var string
     */
    protected $name;
    
    /**
     * @ORM\Column(type="boolean",nullable=false,name="can_be_anonymous",options={"default":false})
     * 
     * @var boolean true if this Hat does not have to be identified
     */
    
    protected $anonymous = false;
    
    /**
     * returns string representation of the entity
     * 
     * @return string
     */

    public function __toString() {
        return $this->name;
    }
    
    /**
     * de facto alias for getName()
     * @return string
     */
    public function getHat() {
        return $this->name;
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
     * Set name
     *
     * @param string $type
     *
     * @return Hat
     */
    public function setName($type)
    {
        $this->name = $type;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * get anonymous
     * 
     * @return boolean
     */
    function getAnonymous() {
        return $this->anonymous;
    }
    
    /**
     * set anonymous property
     * @return Hat
     */
    function setAnonymous($flag) {
        $this->anonymous = $flag;
        return $this;
    }
    
    /**
     * proxies to getAnonymous()
     */
    function anonymous() {
        return $this->getAnonymous();
    }
}
