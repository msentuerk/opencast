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

namespace assignsubmission_opencast\api;

use assignsubmission_opencast\acl\acl;
use assignsubmission_opencast\api\api_request;
use assignsubmission_opencast\mapping\seriesopencastmapping;

defined('MOODLE_INTERNAL') || die();

/**
 * Alle Kommunikationen zwischen Moodle und Opencast finden hier statt.
 */


/**
 * @package assignsubmission_opencast
 * @copyright 2020 Beuth Hochschule fuer Technik Berlin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api_assignsubmission_opencast {

    private $config;

    private function __construct() {
        $this->config = get_config('assignsubmission_opencast');
    }

    /**
     * Get an instance of an object of this class. Create as a singleton.
     *
     * @staticvar \api_assignsubmission_opencast $api_assignsubmission_opencast
     *
     * @param boolean $forcenewinstance true, when a new instance should be created.
     *
     * @return $api_assignsubmission_opencast
     */
    public static function get_instance($forcenewinstance = false) {
        static $api_assignsubmission_opencast;

        if (isset($api_assignsubmission_opencast) && !$forcenewinstance) {
            return $api_assignsubmission_opencast;
        }

        $api_assignsubmission_opencast = new api_assignsubmission_opencast();

        return $api_assignsubmission_opencast;
    }


    /**
     * API call to create a series for given course. Inspired by blocks/opencast/apibridge
     * @param int $courseid
     * @param string $seriestitle
     * @return bool tells if the creation of the series was successful.
     */
    public function create_course_series($courseid, $seriestitle = null ,$user , $context,$assignmentid) {
        $params = [];

        $metadata = array();
        $metadata['label'] = "Opencast Series Dublincore";
        $metadata['flavor'] = "dublincore/series";
        $metadata['fields'] = [];

        $metadata['fields'][] = array('id' => 'title', 'value' => $seriestitle);

        $params['metadata'] = json_encode(array($metadata));

        //-- get the roles
        $access = new \assignsubmission_opencast\acl\acl();
        $acl = array();
        $roles = $access->get_roles_serie();
        foreach ($roles as $role) {
            foreach ($role['actions'] as $action) {
                $acl[] = (object) array('allow' => true, 'action' => $action,
                    'role' => $this->replace_placeholders($role['rolename'], $courseid)[0]);
            }
        }
        $params['acl'] = json_encode(array_values($acl));
        $params['theme'] = '';

        //-- this one is for the API OBJECT
        $api_request = new api_request();
        $result = $api_request->oc_post('/api/series', $params);
        if ($api_request->get_http_code() >= 400 | $api_request->get_http_code() < 200) {
            throw new \moodle_exception('serverconnectionerror', 'assignsubmission_opencast');
        }

        $series = json_decode($result);

        if (isset($series) && object_property_exists($series, 'identifier')) {

            $mapping = new seriesopencastmapping();
            $mapping->set('courseid', $courseid);
            $mapping->set('series', $series->identifier);
            $mapping->set('assignment', $assignmentid);
            $mapping->create();
            return true;
        }
        return false;
    }

    /**
     * API call to create an event
     * @return object series object of NULL, if group does not exist.
     */
    public function create_event($video, $seriesidentifier) {
        global $DB;

        $event = new \assignsubmission_opencast\process\process_event();
        $acl   = new \assignsubmission_opencast\acl\acl();
        $roles = $acl->get_roles();
        foreach ($roles as $role) {
            foreach ($role['actions'] as $action) {
                $event->add_acl(true, $action, $this->replace_placeholders($role['rolename'], $video->courseid)[0]);
            }
        }
        if ($video->chunkupload_presenter) {
            $event->set_chunkupload_presenter($video->chunkupload_presenter);
        }
        if ($video->chunkupload_presentation) {
            $event->set_chunkupload_presentation($video->chunkupload_presentation);
        }

        if ($video->metadata) {
            foreach (json_decode($video->metadata) as $metadata ) {
                $event->add_meta_data($metadata->id, $metadata->value);
            }
        }

        //-- Serie
        $event->add_meta_data('isPartOf', $seriesidentifier);
        $params = $event->get_form_params();

        $api = new api_request();
        $result = $api->oc_post('/api/events', $params);

        if ($api->get_http_code() >= 400) {
            throw new \moodle_exception('serverconnectionerror', 'assignsubmission_opencast');
        }

        return $result;
    }

    private function replace_placeholders($name, $courseid) {
        $coursename = get_course($courseid)->fullname;
        $title = str_replace('[COURSENAME]', $coursename, $name);
        $title = str_replace('[COURSEID]', $courseid, $title);
        $result = array();
        $result []= $title;
        return $result;
    }


    public function get_opencast_video($identifier) {

        $resource = '/api/events/' . $identifier;

        $withroles = array();

        $api = new api_request();

        $video = $api->oc_get($resource, $withroles);

        $result = new \stdClass();
        $result->video = false;
        $result->error = 0;

        if ($api->get_http_code() != 200) {
            $result->error = $api->get_http_code();

            return $result;
        }

        if (!$video = json_decode($video)) {
            return $result;
        }



        $result->video = $video;

        return $result;
    }


    public function get_opencast_media($identifier) {

        $resource = '/api/events/' . $identifier . '/media';

        $withroles = array();

        $api = new api_request();

        $video = $api->oc_get($resource, $withroles);

        $result = new \stdClass();
        $result->video = false;
        $result->error = 0;

        if ($api->get_http_code() != 200) {
            $result->error = $api->get_http_code();

            return $result;
        }

        if (!$video = json_decode($video)) {
            return $result;
        }



        $result->video = $video;

        return $result;
    }


    public function get_opencast_publications($identifier) {

        $resource = '/api/events/' . $identifier . '/publications';

        $withroles = array();

        $api = new api_request();

        $video = $api->oc_get($resource, $withroles);

        $result = new \stdClass();
        $result->video = false;
        $result->error = 0;

        if ($api->get_http_code() != 200) {
            $result->error = $api->get_http_code();

            return $result;
        }

        if (!$video = json_decode($video)) {
            return $result;
        }



        $result->video = $video;

        return $result;
    }

    public function opencast_remove($identifier) {

        $resource = '/api/events/' . $identifier;


        $withroles = array();

        $api = new api_request();

        $video = $api->oc_delete($resource, $withroles);


        $result = new \stdClass();
        $result->video = false;
        $result->error = 0;

        if ($api->get_http_code() != 200) {
            $result->error = $api->get_http_code();

            return $result;
        }

        if (!$video = json_decode($video)) {
            return $result;
        }

        return $result;
    }

}