<?php /** module/Notes/src/Entity/MOTD.php */

declare(strict_types=1);

namespace InterpretersOffice\Admin\Notes\Entity;

use Doctrine\ORM\Mapping as ORM;
use InterpretersOffice\Entity\User;
use DateTime;
use JsonSerializable;


/**
 * Entity class representing MOTD
 *
 * @ORM\Entity(repositoryClass="InterpretersOffice\Admin\Notes\Entity\MOTDRepository")
 * @ORM\Table(name="motd",uniqueConstraints={@ORM\UniqueConstraint(name="date_idx",columns={"date"})})
 * @ORM\HasLifecycleCallbacks
 */
class MOTD implements JsonSerializable, NoteInterface
{

    /**
     * entity id.
     *
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="smallint",options={"unsigned":true})
     */
    private $id;

    /**
     * date
     *
     * @ORM\Column(type="date",nullable=false)
     */
    private $date;

    /**
     * content
     *
     * @ORM\Column(type="string",nullable=false,length=2000)
     * @var string
     */
    private $content = '';

    /**
    * timestamp of motd creation.
    *
    * @ORM\Column(type="datetime",nullable=false)
    *
    * @var \DateTime
    */
    private $created;

    /**
     * last User who updated the motd.
     *
     * @ORM\ManyToOne(targetEntity="\InterpretersOffice\Entity\User")
     * @ORM\JoinColumn(nullable=false,name="created_by_id")
     *
     * @var User
     */
    private $created_by;


    /**
     * timestamp of last update.
     *
     * @ORM\Column(type="datetime",nullable=true)
     *
     * @var \DateTime
     */
    private $modified;


    /**
     * last User who updated the motd.
     *
     * @ORM\ManyToOne(targetEntity="\InterpretersOffice\Entity\User")
     * @ORM\JoinColumn(nullable=true,name="modified_by_id")
     *
     * @var User
     */
    private $modified_by;

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
      * Set date.
      *
      * @param \DateTime $date
      *
      * @return MOTD
      */
     public function setDate(DateTime $date)
     {
         $this->date = $date;

         return $this;
     }

     /**
      * Get date.
      *
      * @return \DateTime
      */
     public function getDate() : DateTime
     {
         return $this->date;
     }

     public function jsonSerialize()
     {
         $data = ['content' => $this->getContent()];
         $data['created_by'] = $this->getCreatedBy()->getUserName();
         $data['created'] = $this->getCreated()->format('D d-M-Y g:i a');
         $data['modified_by'] = $this->getModifiedBy() ?
            $this->getModifiedBy()->getUserName() : null;
         $data['modified'] = $this->getModified() ?
            $this->getModified()->format('D d-M-Y g:i a') : null;
         $data['date'] = $this->getDate()->format('l d-M-Y');
         $data['id'] = $this->id;

         return $data;
     }

     /**
      * Set content.
      *
      * @param string $content
      *
      * @return MOTD
      */
     public function setContent(string $content) : MOTD
     {
         $this->content = $content;

         return $this;
     }

     /**
      * Get content.
      *
      * @return string
      */
     public function getContent()  : string
     {
         return $this->content;
     }

     /**
      * Set created.
      *
      * @param \DateTime $created
      *
      * @return MOTD
      */
     public function setCreated(\DateTime $created) : MOTD
     {
         $this->created = $created;

         return $this;
     }

     /**
      * Get created.
      *
      * @return \DateTime
      */
     public function getCreated() : DateTime
     {
         return $this->created;
     }

     /**
      * Set modified.
      *
      * @param \DateTime|null $modified
      *
      * @return MOTD
      */
     public function setModified(\DateTime $modified = null) : MOTD
     {
         $this->modified = $modified;

         return $this;
     }

     /**
      * Get modified.
      *
      * @return \DateTime|null
      */
     public function getModified() : ?DateTime
     {
         return $this->modified;
     }

     /**
      * Set created_by.
      *
      * @param \InterpretersOffice\Entity\User $created_by
      *
      * @return MOTD
      */
     public function setCreatedBy(\InterpretersOffice\Entity\User $created_by) : MOTD
     {
         $this->created_by = $created_by;

         return $this;
     }

     /**
      * Get created_by.
      *
      * @return \InterpretersOffice\Entity\User
      */
     public function getCreatedBy() : User
     {
         return $this->created_by;
     }

     /**
      * Set modified_by.
      *
      * @param User|null $modified_by
      *
      * @return MOTD
      */
     public function setModifiedBy(User $modified_by = null) : MOTD
     {
         $this->modified_by = $modified_by;

         return $this;
     }

     /**
      * Get modified_by.
      *
      * @return User|null
      */
     public function getModifiedBy() : ?User
     {
         return $this->modified_by;
     }
}
