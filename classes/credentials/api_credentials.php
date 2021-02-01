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

namespace assignsubmission_opencast\credentials;

// Daten in Moodle/Site administration/Plugins/Admin tools/Opencast API

defined('MOODLE_INTERNAL') || die;


/**
 * @package assignsubmission_opencast
 * @copyright 2020 Beuth Hochschule fuer Technik Berlin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api_credentials  {

    /**
     * @todo we will try to implement our configuration
     * get the credentials for tool_opencast
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function get_credentials() {
        $plugin = "assignsubmission_opencast";

        $username = get_config($plugin, 'apiusername');
        $password = get_config($plugin, 'apipassword');;
        $baseurl = get_config($plugin, 'apiurl');
        $timeout = get_config($plugin, 'connecttimeout');;
        

        return array('username'=>$username, 'password'=>$password,'timeout'=>$timeout,'baseurl'=>$baseurl);
    }
}
