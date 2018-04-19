<?php
/** module/Admin/src/Controller/DeletionTrait.php */

namespace InterpretersOffice\Admin\Controller;

use Zend\View\Model\JsonModel;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

/**
 * attempts to delete an entity
 *
 * suitable for relatively simple entities, i.e., those other
 * than the Event entity
 *
 * @param $entity object
 * @param string $name
 * @return JsonModel
 */
trait DeletionTrait
{
    /**
     * deletes an entity
     *
     * @param  Array  $options
     * @return JsonModel
     */
    public function delete(array $options)
    {
        $entity = $options['entity'];
        $what = $options['what'];
        $name = $options['name'];
        $verbose_name = "The $what <strong>$name</strong>";
        $id = $options['id'];
        if ($entity) {
            try {
                $this->entityManager->remove($entity);
                $this->entityManager->flush();
                $this->flashMessenger()
                      ->addSuccessMessage("$verbose_name has been deleted.");
                $result = 'success';
                $redirect = true;
                $error = [];
            } catch (ForeignKeyConstraintViolationException $e) {
                $result = 'error';
                $redirect = false;
                $error = [ 'message' =>
                    "This $what cannot be deleted because it there are other database records that refer to it.",
                    'code' => $e->getCode(),
                    'exception' => 'foreign_key_constraint',
                ];
            } catch (\Exception $e) {
                $result = 'error';
                $redirect = false;
                $error = [ 'message' =>
                    "Sorry, we hit an unexpected system error.",
                    'code' => $e->getCode(),
                    'exception' => get_class($e)
                ];
            }
        } else {
            $result = 'error';
            $error = ['message' => "$what $name (id $id) not found"];
            $redirect = true;
            $this->flashMessenger()
                  ->addWarningMessage("$verbose_name was not found.");
        }

        return new JsonModel(compact('result', 'error', 'redirect'));
    }
}
