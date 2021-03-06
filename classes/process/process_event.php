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

namespace assignsubmission_opencast\process;

use local_chunkupload\local\chunkupload_file;

defined('MOODLE_INTERNAL') || die();

class process_event {

    private $acl = array();            // Access control.
    private $metadatafields = array(); // Meta data.
    private $presentation = null;      // Video file.
    private $presenter = null;      // Video file.

    /**
     * Add a id-value pair as metadata for flavour dublincore/episode
     *
     * @param int $id
     * @param string $value
     */

    public function add_meta_data($id, $value) {
        $this->metadatafields[] = array('id' => $id, 'value' => $value);
    }

    /**
     * Get dublincore/episode metadata for the event.
     *
     * @return string json encoded metadata.
     */
    public function get_meta_data() {

        $metadata = array();
        $metadata['label'] = "Opencast Series Dublincore";
        $metadata['flavor'] = "dublincore/episode";
        $metadata['fields'] = $this->metadatafields;

        return json_encode(array($metadata));
    }

    /**
     * Set presentation as a stored file from moodle.
     *
     * @param int $fileid
     */
    public function set_presentation($fileid) {
        $fs = get_file_storage();
        $this->presentation = $fs->get_file_by_id($fileid);
    }

    /**
     * Set presenter as a chunkupload file from moodle.
     *
     * @param string $chunkuploadid
     * @throws \moodle_exception
     */
    public function set_chunkupload_presenter($chunkuploadid) {
        if (!class_exists('\local_chunkupload\chunkupload_form_element')) {
            throw new \moodle_exception("local_chunkupload is not installed. This should never happen.");
        }
        $this->presenter = new chunkupload_file($chunkuploadid);
    }

    /**
     * Set presentation as a chunkupload file from moodle.
     *
     * @param string $chunkuploadid
     * @throws \moodle_exception
     */
    public function set_chunkupload_presentation($chunkuploadid) {
        if (!class_exists('\local_chunkupload\chunkupload_form_element')) {
            throw new \moodle_exception("local_chunkupload is not installed. This should never happen.");
        }
        $this->presentation = new chunkupload_file($chunkuploadid);
    }

    /**
     * Get the presentation (i. e. the video file).
     *
     * @return \stored_file
     */
    public function get_presentation() {
        return $this->presentation;
    }

    /**
     * Set presenter as a stored file from moodle.
     *
     * @param int $fileid
     */
    public function set_presenter($fileid) {
        $fs = get_file_storage();
        $this->presenter = $fs->get_file_by_id($fileid);
    }

    /**
     * Get the presenter (i. e. the video file).
     *
     * @return \stored_file
     */
    public function get_presenter() {
        return $this->presenter;
    }
    //End adding presenter option

    /**
     * Set the acl data for this event.
     *
     * @param string $jsonacl acl string as received from opencast.
     * @throws \moodle_exception
     */
    public function set_json_acl($jsonacl) {

        $this->acl = json_decode($jsonacl);

        if (!is_array($this->acl)) {
            throw new \moodle_exception('invalidacldata', 'block_opencast');
        }
    }

    /**
     * Add a acl rule.
     *
     * @param boolean $allow
     * @param string $action
     * @param string $role
     */
    public function add_acl($allow, $action, $role) {

        $this->remove_acl($action, $role);
        $this->acl[] = (object) array('allow' => $allow, 'role' => $role, 'action' => $action);
    }

    /**
     * Returns true if a given acl role exists.
     * @param $allow
     * @param $action
     * @param $role
     */
    public function has_acl($allow, $action, $role) {
        $role = (object) array('allow' => $allow, 'role' => $role, 'action' => $action);
        return in_array($role, $this->acl);
    }

    /**
     * Add a acl rule.
     *
     * @param boolean $allow
     * @param string $action
     * @param string $role
     */
    public function remove_acl($action, $role) {

        foreach ($this->acl as $key => $acl) {
            if (($acl->action == $action) && ($acl->role == $role)) {
                unset($this->acl[$key]);
            }
        }
    }

    /**
     * Get the acl rules as a json object.
     *
     * @return string.
     */
    public function get_json_acl() {
        return json_encode(array_values($this->acl));
    }

    /**
     * Return the processing workflow as a json object.
     *
     * @return string
     */
    public function get_processing() {

        $uploadworkflow = get_config('block_opencast', 'uploadworkflow');
        if (empty($uploadworkflow)) {
            $uploadworkflow = 'ng-schedule-and-upload';
        }

        $publistoengage = get_config('block_opencast', 'publishtoengage');
        $publistoengage = "true";

        $processing = array();
        $processing['workflow'] = $uploadworkflow;
        $processing['configuration'] = array(
            "flagForCutting" => "false",
            "flagForReview" => "false",
            "publishToEngage" => $publistoengage,
            "publishToHarvesting" => "false",
            "straightToPublishing" => "true"
        );
        return json_encode($processing);
    }

    /**
     * Get all form params to create a new event in opencast via api.
     *
     * @return array form params.
     */
    public function get_form_params() {

        $params = array();
        $params['acl'] = $this->get_json_acl();
        $params['metadata'] = $this->get_meta_data();
        //Handling presentation & presenter
        if ($this->get_presenter()) {
            $params['presenter'] = $this->get_presenter();
        } 
        if ($this->get_presentation()) {
            $params['presentation'] = $this->get_presentation();
        }
        $params['processing'] = $this->get_processing();

        return $params;
    }

}