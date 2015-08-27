<?php
/**
 * This is the Google search API without any authorization keys.
 *
 * See COPYING for license information.
 *
 * @author Mateusz Manaj <mmanaj@softgraf.pl>
 * @copyright Copyright (c) 2015
 * @package GoogleSearch
 */

namespace GoogleSearch;

class GoogleSearchElementLink
{
    public $Link = "";
    public $Label = NULL;
    public $Parts = Array();

    public function __construct($link, $label = NULL)
    {
        $this->Label = $label;
        $this->Link = $link;

        $this->Parts = parse_url($link);
        $this->Parts['query'] = !isset($this->Parts['query']) ? "" : $this->Parts['query'];
        parse_str($this->Parts['query'], $this->Parts['query']);

        $this->Parts['query'] = (object)$this->Parts['query'];
        $this->Parts = (object)$this->Parts;
    }
}

?>