<?php
/** module/InterpretersOffice/src/View/Helper/Defendants.php */

namespace InterpretersOffice\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * View helper for displaying defendant names
 *
 * @author david
 */
class Defendants extends AbstractHelper
{
    /**
     * defendant names
     *
     * @var Array
     */
    protected $defendants;

    /**
     * gets defendant names
     *
     * @return array
     */
    protected function getDefendants()
    {

        $data = $this->getView()->data;
        if (! (is_array($data) && isset($data['defendants']))) {
            // try something else
            $defendants = $this->getView()->defendants;
            if (! $defendants) {
                return false;
            } else {
                $data = ['defendants' => $defendants];
            }
        }
        $this->defendants = $data['defendants'];

        return $this->defendants;
    }

    /**
     * Invokes this helper to display defendant names
     *
     * We presume a view variable $defendants, an array in the form
     * <code>
     * [ event_id =>
     *     [ 0=>
     *         [
     *              surnames=>"Some Surname",
     *              given_names=> "Given Names"
     *         ],
     *         ...
     *      ]
     *  ]
     * </code>
     * @param  int $id of event
     * @return string
     */
    public function __invoke($id)
    {
         $return = '' ;

        if (! $this->getDefendants() or ! isset($this->defendants[$id])) {
            return $return;
        }
        $count = count($this->defendants[$id]);
        if ($count <= 3) {
            foreach ($this->defendants[$id] as $n) {
                $return .= '<div class="defendant-name">' . $this->getView()->escapeHtml($n['surnames']);
                $return .= sprintf('<span class="d-none d-md-inline">, %s</span>', $this->getView()->escapeHtml($n['given_names']));
                $return .='</div>';
            }
             return $return;
        }
        $return .= '<div class="defendant-name">' . $this->getView()->escapeHtml($this->defendants[$id][0]['surnames'])
                .  sprintf('<span class="d-none d-md-inline">, %s</span>', $this->getView()->escapeHtml($this->defendants[$id][0]['given_names']))
                .'</div>';
        $x = $count - 1;

        $return .= '<a class="expand-deftnames" href="#">'. "[$x more]"  .'</a>';

        for ($i = 1; $i < $count - 1; $i++) {
            $return .= '<div class="defendant-name" style="display:none">'
                . $this->getView()->escapeHtml($this->defendants[$id][$i]['surnames'])
                .  sprintf('<span class="d-none d-md-inline">, %s</span>', $this->getView()->escapeHtml($this->defendants[$id][$i]['given_names']))                
                .'</div>';
        }
        $return .= '<a class="collapse-deftnames" style="display:none" href="#">'. "[less]"  .'</a>';

        return $return;
    }
}
