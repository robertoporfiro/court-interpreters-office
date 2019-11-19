<?php
/** module/Notes/src/Entity/NoteInterface.php */
namespace InterpretersOffice\Admin\Notes\Entity;
use DateTime;

/**
 * interface for MOTD|MOTW entities
 */
interface NoteInterface
{

    public function getContent(): string;

    public function getDate(): DateTime;

    public function setTaskAssignments(Array $tasks) : object;

    public function getTaskAssignments() : Array ;

    public function  getTaskAssignmentsJson() : string;

}
