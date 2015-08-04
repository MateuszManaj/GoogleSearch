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

class GoogleSearchElementAddress
{
    public $Label = "";
    public $GMapsLink = "";
    public $PhoneNumber = "";

    public function __construct($label, $gmaps_link, $phone_number = "")
    {
        $this->Label = $label;
        $this->GMapsLink = $gmaps_link;
        $this->PhoneNumber = $phone_number;
    }
}

?>