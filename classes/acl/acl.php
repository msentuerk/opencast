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


namespace assignsubmission_opencast\acl;

use context_course;

defined('MOODLE_INTERNAL') || die();

/**
 * Diese Datei ist/wird für das Setzen der Rollen und Rechte der jeweiligen Video/Series zuständig sein.
 */

/**
 * @package assignsubmission_opencast
 * @copyright 2020 Beuth Hochschule fuer Technik Berlin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class acl
{
    /**
     * return an array of user that will add this video
     * @todo add table assign_opencast_roles
     * @todo add configuration of roles for the admin
     */
    public function get_roles() {
        return array (
            1 =>
                array (
                    'id' => '1',
                    'rolename' => 'ROLE_ADMIN',
                    'actions' =>
                        array (
                            0 => 'write',
                            1 => 'read',
                        ),
                    'permanent' => '1',
                ),
            2 =>
                array (
                    'id' => '2',
                    'rolename' => 'ROLE_GROUP_MH_DEFAULT_ORG_EXTERNAL_APPLICATIONS',
                    'actions' =>
                        array (
                            0 => 'write',
                            1 => 'read',
                        ),
                    'permanent' => '1',
                ),
            3 =>
                array (
                    'id' => '3',
                    'rolename' => '[COURSEID]_Student',
                    'actions' =>
                        array (
                            0 => 'write',
                            1 => 'read',
                        ),
                    'permanent' => '1',
                ),
            4 =>
                array (
                    'id' => '4',
                    'rolename' => '[COURSEID]_Learner',
                    'actions' =>
                        array (
                            0 => 'read',
                        ),
                    'permanent' => '1',
                ),
        );
    }

    /**
     * return an array of user that will add this video
     * @todo add table assign_opencast_roles
     * @todo add configuration of roles for the admin
     */
    public function get_roles_serie() {
        return array (
            1 =>
                array (
                    'id' => '1',
                    'rolename' => 'ROLE_ADMIN',
                    'actions' =>
                        array (
                            0 => 'write',
                            1 => 'read',
                        ),
                    'permanent' => '1',
                ),
            2 =>
                array (
                    'id' => '2',
                    'rolename' => 'ROLE_GROUP_MH_DEFAULT_ORG_EXTERNAL_APPLICATIONS',
                    'actions' =>
                        array (
                            0 => 'write',
                            1 => 'read',
                        ),
                    'permanent' => '1',
                ),
            3 =>
                array (
                    'id' => '3',
                    'rolename' => '[COURSEID]_Instructor',
                    'actions' =>
                        array (
                            0 => 'write',
                            1 => 'read',
                        ),
                    'permanent' => '1',
                ),
            4 =>
                array (
                    'id' => '4',
                    'rolename' => '[COURSEID]_Learner',
                    'actions' =>
                        array (
                            0 => 'read',
                            1 => 'write',
                        ),
                    'permanent' => '1',
                ),
            5 =>
                array (
                    'id' => '5',
                    'rolename' => '[COURSEID]_Student',
                    'actions' =>
                        array (
                            0 => 'read',
                            1 => 'write',
                        ),
                    'permanent' => '1',
                ),
        );
    }
}