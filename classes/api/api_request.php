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

use assignsubmission_opencast\credentials\api_credentials;
use local_chunkupload\chunkupload_form_element;
use local_chunkupload\local\chunkupload_file;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/filelib.php');


/**
 * Diese Klasse wird aus Tools/opencast/api übernommen!
 */

/**
 * @package assignsubmission_opencast
 * @copyright 2020 Beuth Hochschule fuer Technik Berlin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api_request extends \curl {

    private $username;
    private $password;
    private $timeout;
    private $baseurl;


    private static $supportedapilevel;

    public static function get_sort_param($params) {

        if (empty($params)) {
            return '';
        }

        foreach ($params as $key => $sortorder) {
            $sortdir = (SORT_ASC == $sortorder) ? 'ASC' : 'DESC';
            return "&sort={$key}:" . $sortdir;
        }
    }


    /**
     * Constructor of the Opencast API.
     * @param array $settings additional curl settings.
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function __construct($settings = array()) {
        parent::__construct($settings);

        $api_credentials = \assignsubmission_opencast\credentials\api_credentials::get_credentials();

        // Daten aus credentials/api_credentials

        $this->username = $api_credentials['username'];
        $this->password = $api_credentials['password'];
        $this->baseurl = $api_credentials['baseurl'];
        $this->timeout = $api_credentials['timeout'];



        if (empty($this->baseurl)) {
            throw new \moodle_exception('apiurlempty', 'assignsubmission_opencast');
        }

        if (empty($this->username)) {
            throw new \moodle_exception('apiusernameempty', 'assignsubmission_opencast');
        }

        if (empty($this->password)) {
            throw new \moodle_exception('apipasswordempty', 'assignsubmission_opencast');
        }


    }

    /**
     * Get http status code
     *
     * @return int|boolean status code or false if not available.
     */
    public function get_http_code() {

        $info = $this->get_info();
        if (!isset($info['http_code'])) {
            return false;
        }
        return $info['http_code'];
    }

    /**
     * Get an digest authentication header.
     * @param array $runwithroles if set, the request is executed within opencast assuming the user has
     * the specified roles.
     *
     * @return array of authentification headers
     * @throws \moodle_exception
     */
    private function get_authentication_header($runwithroles = array()) {

        $options = array('CURLOPT_HEADER' => true);
        $this->setopt($options);

        // Restrict to Roles.
        if (!empty($runwithroles)) {
            $header[] = "X-RUN-WITH-ROLES: " . implode(', ', $runwithroles);
            $this->setHeader($header);
        }

        $this->setopt('CURLOPT_CONNECTTIMEOUT', $this->timeout);

        $basicauth = base64_encode($this->username . ":" . $this->password);

        $header = array();

        $header[] = sprintf(
            'Authorization: Basic %s', $basicauth
        );

        return $header;
    }

    /**
     * Do a GET call to opencast API.
     *
     * @param string $resource path of the resource.
     * @param array $runwithroles if set, the request is executed within opencast assuming the user has
     * the specified roles.
     * @return string JSON String of result.
     * @throws \moodle_exception
     */
    public function oc_get($resource, $runwithroles = array()) {

        $url = $this->baseurl . $resource;

        $header = $this->get_authentication_header($runwithroles);
        $header[] = 'Content-Type: application/json';
        $this->setHeader($header);
        $this->setopt(array('CURLOPT_HEADER' => false));

        return $this->get($url);
    }

    /**
     * Opencast needs a fileextension for uploaded file, so add a postname
     * (which the core curl module does NOT) to curl_file.
     *
     * @param object|\stored_file $storedfile stored file to be uploaded.
     * @param string $key key identifier within the post params array of the stored file.
     * @throws \moodle_exception
     */
    private function add_postname($storedfile, $key) {

        $curlfile = $this->_tmp_file_post_params[$key];

        // Ensure that file is uploaded as a curl file (PHP 5 > 5.5.0 is needed).
        if (!$curlfile instanceof \CURLFile) {
            throw new \moodle_exception('needphp55orhigher', 'tool_opencast');
        }

        //Extracting filename from $file->file_record->source, make sure to have a string filename!
        $source = @unserialize($storedfile->get_source());
        $filename = '';
        if (is_object($source)) {
            $filename = $source->source;
        } else {
            // If source is not a serialised object, it is a string containing only the filename.
            $filename = $storedfile->get_source();
        }
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        // If extension is empty, add extension base on mimetype.
        if (empty($extension)) {
            $extension = mimeinfo_from_type('extension', $storedfile->get_mimetype());
            $filename .= '.' . $extension;
        }

        // Check mimetype.
        $mimetype = mimeinfo('type', $filename);
        list($mediatype, $subtype) = explode('/', $mimetype);

        if ($mediatype != 'video') {

            $contextid = $storedfile->get_contextid();
            list($context, $course, $cm) = get_context_info_array($contextid);

            $info = new \stdClass();
            $info->coursename = $course->fullname . "(ID: {$course->id})";
            $info->filename = $filename;
            throw new \moodle_exception('wrongmimetypedetected', 'tool_opencast', '', $info);
        }

        $curlfile->postname = $filename;
        $curlfile->mime = $mimetype;
    }

    /**
     * Opencast needs a fileextension for uploaded file, so add a postname
     * (which the core curl module does NOT) to curl_file.
     *
     * @param chunkupload_file $file chunkupload file to be uploaded.
     * @param string $key key identifier within the post params array of the stored file.
     * @throws \moodle_exception
     */
    private function add_postname_chunkupload($file, $key) {

        $curlfile = $this->_tmp_file_post_params[$key];

        // Ensure that file is uploaded as a curl file (PHP 5 > 5.5.0 is needed).
        if (!$curlfile instanceof \CURLFile) {
            throw new \moodle_exception('needphp55orhigher', 'tool_opencast');
        }

        $filename = $file->get_filename();

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        // If extension is empty, add extension base on mimetype.
        if (empty($extension)) {
            $mimetype = file_storage::mimetype_from_file($file->get_fullpath());
            $extension = mimeinfo_from_type('extension', $mimetype);
            $filename .= '.' . $extension;
        }

        // Check mimetype.
        $mimetype = mimeinfo('type', $filename);

        $curlfile->postname = $filename;
        $curlfile->mime = $mimetype;
    }

    /**
     * Do a POST call to opencast API.
     *
     * @param string $resource path of the resource.
     * @param array $params post parameters.
     * @param array $runwithroles if set, the request is executed within opencast assuming the user has
     * the specified roles.
     * @return string JSON String of result.
     * @throws \moodle_exception
     */
    public function oc_post($resource, $params = array(), $runwithroles = array()) {

        $url = $this->baseurl . $resource;

        $header = $this->get_authentication_header($runwithroles);

        $header[] = "Content-Type: multipart/form-data";
        $this->setHeader($header);
        $this->setopt(array('CURLOPT_HEADER' => false));

        $options['CURLOPT_POST'] = 1;

        if (is_array($params)) {
            $this->_tmp_file_post_params = array();
            foreach ($params as $key => $value) {
                if ($value instanceof \stored_file) {
                    $value->add_to_curl_request($this, $key);
                    $this->add_postname($value, $key);
                } elseif (class_exists('\local_chunkupload\local\chunkupload_file') &&
                    $value instanceof \local_chunkupload\local\chunkupload_file) {
                    $value->add_to_curl_request($this, $key);
                    $this->add_postname_chunkupload($value, $key);
                } else {
                    $this->_tmp_file_post_params[$key] = $value;
                }
            }
            $options['CURLOPT_POSTFIELDS'] = $this->_tmp_file_post_params;
            unset($this->_tmp_file_post_params);
        } else {
            // The raw post data.
            $options['CURLOPT_POSTFIELDS'] = $params;
        }
        return $this->request($url, $options);

    }

    /**
     * Do a PUT call to opencast API.
     *
     * @param string $resource path of the resource.
     * @param array $params array of parameters.
     * @param array $runwithroles if set, the request is executed within opencast assuming the user has
     * the specified roles.
     * @return string JSON String of result.
     * @throws \moodle_exception
     */
    public function oc_put($resource, $params = array(), $runwithroles = array()) {

        $url = $this->baseurl . $resource;

        $header = $this->get_authentication_header($runwithroles);
        $this->setHeader($header);
        $this->setopt(array('CURLOPT_HEADER' => false));

        $options['CURLOPT_CUSTOMREQUEST'] = "PUT";
        if (is_array($params)) {
            $this->_tmp_file_post_params = array();
            foreach ($params as $key => $value) {
                $this->_tmp_file_post_params[$key] = $value;
            }
            $options['CURLOPT_POSTFIELDS'] = $this->_tmp_file_post_params;
            unset($this->_tmp_file_post_params);
        } else {
            // The raw post data.
            $options['CURLOPT_POSTFIELDS'] = $params;
        }

        return $this->request($url, $options);
    }

    /**
     * Do a DELETE call to opencast API.
     *
     * @param string $resource path of the resource.
     * @param array $params array of parameters.
     * @param array $runwithroles if set, the request is executed within opencast assuming the user has
     * the specified roles.
     * @return string JSON String of result.
     * @throws \moodle_exception
     */
    public function oc_delete($resource, $params = array(), $runwithroles = array()) {

        $url = $this->baseurl . $resource;

        $header = $this->get_authentication_header($runwithroles);
        $this->setHeader($header);
        $this->setopt(array('CURLOPT_HEADER' => false));

        $options['CURLOPT_CUSTOMREQUEST'] = "DELETE";
        if (is_array($params)) {
            $this->_tmp_file_post_params = array();
            foreach ($params as $key => $value) {
                $this->_tmp_file_post_params[$key] = $value;
            }
            $options['CURLOPT_POSTFIELDS'] = $this->_tmp_file_post_params;
            unset($this->_tmp_file_post_params);
        } else {
            // The raw post data.
            $options['CURLOPT_POSTFIELDS'] = $params;
        }

        return $this->request($url, $options);
    }

    /**
     * Checks if the opencast version support a certain version of the External API.
     * This is necessary for the decision, which opencast endpoints are used throughout this class.
     * @return string|null returns the version as string.
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function supports_api_level($level) {
        if (!self::$supportedapilevel) {

            $resource = '/api/version';

            $api = new api_request();
            $result = json_decode($api->oc_get($resource));

            if ($api->get_http_code() != 200) {
                throw new \moodle_exception('Opencast system not reachable.');
            }
            self::$supportedapilevel = $result->versions;
        }
        return is_array(self::$supportedapilevel) && in_array($level, self::$supportedapilevel);
    }

}