<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package assignsubmission_opencast
 * @copyright 2020 Beuth Hochschule fuer Technik Berlin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace assignsubmission_opencast\event;

defined('MOODLE_INTERNAL') || die();

/**
 * @package assignsubmission_opencast
 * @copyright 2020 Beuth Hochschule fuer Technik Berlin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submission_created extends \mod_assign\event\submission_created {


    protected function init()
    {
        parent::init();
        //Field
        $this->data['objecttable'] = 'assign_opencast';
    }

    /**
     * Returns non-localised description of what happened.
     *
     * @return string
     */
    public function get_description() {

        // description what happend
        $descriptionstring = "The user with id '$this->userid' created an Opencast 
         submission  with course module id " . "'$this->contextinstanceid'" ;
        return $descriptionstring;
    }

    protected function validate_data()
    {
        /**
         * @todo validate data maybe required ask herr Löwis
         */
        parent::validate_data();

    }

    public static function get_other_mapping()
    {
        return array('db' => 'assign_opencast', 'restore' => \core\event\base::NOT_MAPPED);
    }
}
