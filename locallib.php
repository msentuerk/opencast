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

use assignsubmission_opencast\api\api_request;
use block_opencast\local\apibridge;

defined('MOODLE_INTERNAL') || die();
// File area for online text submission assignment.
// Test
define('ASSIGNSUBMISSION_OPENCAST_FILEAREA', 'assignsubmission_opencast');

/**
 * Hauptklasse ist für die BHT Moodle Plugin Opencast zuständig!!
 */

/**
 * @package assignsubmission_opencast
 * @copyright 2020 Beuth Hochschule fuer Technik Berlin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_opencast extends assign_submission_plugin{


    /**
     * Get the name of the opencast  submission plugin
     * @return string
     */
    public function get_name() {
        return get_string('opencast', 'assignsubmission_opencast');
    }

    /**
     * Get opencast submission information from the database
     *
     * @param  int $submissionid
     * @return mixed
     */
    private function get_opencast_submission($submissionid) {
        global $DB;
        return $DB->get_record('assign_opencast', array('submission'=>$submissionid));
    }


    /**
     * Get the settings for Opencast submission plugin
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {

        /**
         * @todo 37 Setting
         */

        global $CFG, $COURSE;

        if ($this->assignment->has_instance()) {
            $defaultmaxsubmissionsizebytes = $this->get_config('maxsubmissionsizebytes');
            $defaultfiletypes = $this->get_config('filetypeslist');
        } else {
            $defaultmaxsubmissionsizebytes = get_config('assignsubmission_opencast', 'maxbytes');
            $defaultfiletypes = get_config('assignsubmission_opencast', 'filetypes');
        }
        $defaultfiletypes = (string)$defaultfiletypes;

        $settings = array();
        $options = array();
        for ($i = 1; $i <= get_config('assignsubmission_opencast', 'maxfiles'); $i++) {
            $options[$i] = $i;
        }

        $choices = get_max_upload_sizes($CFG->maxbytes,
            $COURSE->maxbytes,
            get_config('assignsubmission_opencast', 'maxbytes'));

        $settings[] = array('type' => 'select',
            'name' => 'maxsubmissionsizebytes',
            'description' => get_string('maximumsubmissionsize1', 'assignsubmission_opencast'),
            'options'=> $choices,
            'default'=> $defaultmaxsubmissionsizebytes);
        $settings[] = array('type' => 'select',
            'name' => 'maxsubmissionsizebytes',
            'description' => get_string('maximumsubmissionsize2', 'assignsubmission_opencast'),
            'options'=> $choices,
            'default'=> $defaultmaxsubmissionsizebytes);

        $name = get_string('maximumsubmissionsize1', 'assignsubmission_opencast');
        $mform->addElement('select', 'assignsubmission_opencast_maxsizebytes', $name, $choices);
        $mform->addHelpButton('assignsubmission_opencast_maxsizebytes',
            'maximumsubmissionsize1',
            'assignsubmission_opencast');

        $name = get_string('maximumsubmissionsize2', 'assignsubmission_opencast');
        $mform->addElement('select', 'assignsubmission_opencast_maxsizebytes', $name, $choices);
        $mform->addHelpButton('assignsubmission_opencast_maxsizebytes',
            'maximumsubmissionsize2',
            'assignsubmission_opencast');

        /*
        $name = get_string('presentervideocheckbox', 'assignsubmission_opencast');
        $mform->addElement('checkbox', 'presentervideocheckbox', $name, $name);
        $mform->setDefault('presentervideocheckbox', 1);
        $mform->addHelpButton('presentervideocheckbox',
            'presentervideocheckbox',
            'assignsubmission_opencast');

        $name = get_string('presentataionvideocheckbox', 'assignsubmission_opencast');
        $mform->addElement('checkbox', 'presentataionvideocheckbox',$name, $name);
        $mform->setDefault('presentataionvideocheckbox', 1);
        $mform->addHelpButton('presentataionvideocheckbox',
            'presentataionvideocheckbox',
            'assignsubmission_opencast');
         */

        $mform->setDefault('assignsubmission_opencast_maxsizebytes', $defaultmaxsubmissionsizebytes);
        $mform->hideIf('assignsubmission_opencast_maxsizebytes',
            'assignsubmission_opencast_enabled',
            'notchecked');

        $name = get_string('acceptedfiletypes', 'assignsubmission_opencast');
        $mform->addElement('filetypes', 'assignsubmission_opencast_filetypes', $name);
        $mform->addHelpButton('assignsubmission_opencast_filetypes', 'acceptedfiletypes', 'assignsubmission_opencast');
        $mform->setDefault('assignsubmission_opencast_filetypes', $defaultfiletypes);
        $mform->hideIf('assignsubmission_opencast_filetypes', 'assignsubmission_opencast_enabled', 'notchecked');
    }

    /**
     * Save the settings for opencast submission plugin.
     * Whenever a new submission with opencast is created
     * and saved this method will be called.
     * @todo add our setting would be better to manage
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        global $USER;
        $coursid = $this->assignment->get_course()->id;
        $context = \context_course::instance($coursid);

        $serie_title = $this->generate_seriestitle($this->assignment->get_course()->id,$this->assignment->get_instance()->name,$this->assignment->get_course()->shortname);
        if(empty($serie_title)){
            $serie_title = $this->get_default_seriestitle();
        }
        $api_assignsubmission_opencast = \assignsubmission_opencast\api\api_assignsubmission_opencast::get_instance();
        $api_assignsubmission_opencast->create_course_series($this->assignment->get_course()->id, $serie_title,$USER,$context,$this->assignment->get_instance()->id);
        return true;
    }

    /**
     * Add form elements for settings. Whenever a students try to adds a submission with opencast,
     * this method will be called.
     * @todo change this code to opencast submission not opencast (block opencast)
     * @param mixed $submission can be null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return true if elements were added to the form
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
        global $CFG;

        //-- to check if needed
        $use_chunkupload = class_exists('\local_chunkupload\chunkupload_form_element');
        if ($use_chunkupload) {
            \MoodleQuickForm::registerElementType('chunkupload',
                "$CFG->dirroot/local/chunkupload/classes/chunkupload_form_element.php",
                'local_chunkupload\chunkupload_form_element');
        }
        $elements = array();
        $submissionid = $submission ? $submission->id : 0;
        if (!isset($data->title)) {
            $data->title = '';
        }

        // Check if there is any old submission in the database , if yes the form will be field with the following input
        // title + presenter + presentation
        if ($submission) {
            $opencastsubmission = $this->get_opencast_submission($submission->id);
            if ($opencastsubmission) {
                $data->title = $opencastsubmission->title;
                //-- Presenter video not empty in edit
                $data->video_presenter_chunk = $opencastsubmission->chunkuploadpresenter;
                $data->video_presentation_chunk = $opencastsubmission->chunkuploadpresentation;
            }
        }

        //-- Header
        $mform->addElement('header', 'metadata', get_string('metadata', 'assignsubmission_opencast'));
        $explanation = \html_writer::tag('p', get_string('metadataexplanation', 'assignsubmission_opencast'));
        $mform->addElement('html', $explanation);

        //-- Title
        $mform->addElement('text', 'title', get_string('title', 'assignsubmission_opencast'));

        $mform->addRule('title', get_string('required'), 'required');
        $mform->setType('title', PARAM_TEXT);



        $mform->addElement('header', 'upload_filepicker', get_string('upload', 'block_opencast'));
        $explanation = \html_writer::tag('p', get_string('uploadexplanation', 'block_opencast'));
        $mform->addElement('html', $explanation);

        //-- video_presenter_chunk
        $mform->addElement('chunkupload', 'video_presenter_chunk', get_string('pres','assignsubmission_opencast'), null,
            array('maxbytes' => 2 * 1024   * 1024 * 1024));

        //-- file upload
        $presentationdesc = \html_writer::tag('p', get_string('presentationdesc', 'block_opencast'));
        $mform->addElement('html', $presentationdesc);

        //-- video_presentation_chunk
        $mform->addElement('chunkupload', 'video_presentation_chunk', get_string('presentation','assignsubmission_opencast'), null,
            array('maxbytes' => 2 * 1024   * 1024 * 1024));

        return true;
    }

    /**
     * Save data to the database and trigger plagiarism plugin,
     * if enabled, to scan the uploaded content via events trigger
     *
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $USER, $DB;

        $opencastsubmission = $this->get_opencast_submission($submission->id);
        $params = array(
            'context' => context_module::instance($this->assignment->get_course_module()->id),
            'courseid' => $this->assignment->get_course()->id,
            'objectid' => $submission->id,
            'other' => array(
                'pathnamehashes' => array(),
                'content' => trim($data->titile),
            )
        );

        if (!empty($submission->userid) && ($submission->userid != $USER->id)) {
            $params['relateduserid'] = $submission->userid;
        }

        if ($this->assignment->is_blind_marking()) {
            $params['anonymous'] = 1;
        }
        $event = \assignsubmission_opencast\event\assessable_uploaded::create($params);
        $event->trigger();

        unset($params['objectid']);
        unset($params['other']);
        $params['other'] = array(
            'submissionid' => $submission->id,
            'submissionattempt' => $submission->attemptnumber,
            'submissionstatus' => $submission->status,
        );

        if ($opencastsubmission) {

            // in this part an upadate would be done

            // test with same video
            $chunkupload_presenter = $data->video_presenter_chunk;
            $chunkupload_presentation = $data->video_presentation_chunk;

            // Upload video to opencast
            $videos = new stdClass();
            $videos->id = $this->assignment->get_instance()->id;
            $videos->timestarted = time();
            $videos->courseid = $this->assignment->get_course()->id;
            $videos->userid = $USER->id;
            $videos->timecreated = time();
            $videos->chunkupload_presenter = $chunkupload_presenter;
            $videos->chunkupload_presentation = $chunkupload_presentation;
            $videos->metadata = '[
                                    {"id":"title","value":"'. $data->title.'"},
                                    {"id":"startDate","value":"'.date('Y-m-d').'"},
                                    {"id":"startTime","value":"'.date('H:i:s').'Z"}
                                  ]';


            /**
             * @todo Need to check if the serie exist
             * otherwise create new serie for that
             * there is two function for that
             * ensure_course_series_exists
             */
            $serieassignment = $this->get_instance_series($this->assignment->get_instance()->id,$this->assignment->get_course()->id);
            foreach ($serieassignment as $seriesuid){
                $serieidentifier = $seriesuid->series;
            }

            $api_assignsubmission_opencast = \assignsubmission_opencast\api\api_assignsubmission_opencast::get_instance();
            $videoUid = $api_assignsubmission_opencast->create_event($videos, $serieidentifier);
            $uid = json_decode($videoUid, true);

            $opencastsubmission->title = $data->title;
            $opencastsubmission->submission = $submission->id;
            //$opencastsubmission->assignment = $this->assignment->get_instance()->id;
            $opencastsubmission->seriesid = $serieidentifier;
            $opencastsubmission->eventuid = $uid['identifier'];
            $opencastsubmission->chunkuploadpresenter = $chunkupload_presenter;
            $opencastsubmission->chunkuploadpresentation = $chunkupload_presentation;
            $opencastsubmission->chunkuploadpresenterurl = "url";
            $opencastsubmission->chunkuploadpresentationurl = "url2";
            $opencastsubmission->timestarted = time();
            $opencastsubmission->timecreated = time();
            $opencastsubmission->timemodified = NULL;

            $params['objectid'] = $opencastsubmission->id;
            $updatestatus = $DB->update_record('assign_opencast', $opencastsubmission);
            $event = \assignsubmission_opencast\event\submission_updated::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $updatestatus;

        } else {
            // in this part the user add his first submission

            // test with same video
            $chunkupload_presenter = $data->video_presenter_chunk;
            $chunkupload_presentation = $data->video_presentation_chunk;

            // Upload video to opencast
            $videos = new stdClass();
            $videos->id = $this->assignment->get_instance()->id;
            $videos->timestarted = time();
            $videos->courseid = $this->assignment->get_course()->id;
            $videos->userid = $USER->id;
            $videos->timecreated = time();
            $videos->chunkupload_presenter = $chunkupload_presenter;
            $videos->chunkupload_presentation = $chunkupload_presentation;
            $videos->metadata = '[
                                    {"id":"title","value":"'. $data->title.'"},
                                    {"id":"startDate","value":"'.date('Y-m-d').'"},
                                    {"id":"startTime","value":"'.date('H:i:s').'Z"}
                                  ]';

            /**
             * @todo Need to check if the serie exist
             * otherwise create new serie for that
             * there is two function for that
             * ensure_course_series_exists
             */
            $serieassignment = $this->get_instance_series($this->assignment->get_instance()->id,$this->assignment->get_course()->id);
            foreach ($serieassignment as $seriesuid){
                // this is the serieUid that the Teacher have created.
                $serieidentifier = $seriesuid->series;
            }

            $api_assignsubmission_opencast = \assignsubmission_opencast\api\api_assignsubmission_opencast::get_instance();
            $videoUid = $api_assignsubmission_opencast->create_event($videos, $serieidentifier);
            $uid = json_decode($videoUid, true);

            $opencastsubmission = new stdClass();
            $opencastsubmission->title = $data->title;
            $opencastsubmission->submission = $submission->id;
            //$opencastsubmission->assignment = $this->assignment->get_instance()->id;
            $opencastsubmission->seriesid = $serieidentifier;
            $opencastsubmission->eventuid = $uid['identifier'];
            $opencastsubmission->chunkuploadpresenter = $chunkupload_presenter;
            $opencastsubmission->chunkuploadpresentation = $chunkupload_presentation;
            $opencastsubmission->chunkuploadpresenterurl = "url";
            $opencastsubmission->chunkuploadpresentationurl = "url2";
            $opencastsubmission->timestarted = time();
            $opencastsubmission->timecreated = time();
            $opencastsubmission->timemodified = NULL;
            $opencastsubmission->id = $DB->insert_record('assign_opencast', $opencastsubmission);
            $params['objectid'] = $opencastsubmission->id;
            $event = \assignsubmission_opencast\event\submission_created::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $opencastsubmission->id > 0;
        }
    }

    /**
     * Display Video Link and title  in the submission status table
     * @param stdClass $submission
     * @param bool $showviewlink - If the summary has been truncated set this to true
     * @return string
     */
    public function view_summary(stdClass $submission, & $showviewlink) {
        global $CFG;
        $opencastsubmission = $this->get_opencast_submission($submission->id);
        // Always show the view link.
        $showviewlink = true;

        $video = $this->get_video_status($submission->id);

        return $video;
    }

    /**
     * Display the saved Video  from the editor in the view table
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission) {
        global $CFG;
        $title = '';
        $opencastsubmission = $this->get_opencast_submission($submission->id);
        if ($opencastsubmission) {
            $title =  $opencastsubmission->title;
        }

        $video = $this->get_video_status($submission->id);


        return $video;
    }

    /**
     ** @param integer $coursid
     * @param string $assigntitle
     * @param string $shortname
     * @return string
     */
    public function generate_seriestitle($coursid,$assigntitle,$shortname) {
        return $coursid.  "_"  .strtolower($assigntitle). "_" . strtolower($shortname);
    }

    /**
     * Returns the default series name for a course.
     * @return string
     */
    public function get_default_seriestitle() {
        return get_config('assignsubmission_opencast', 'series_name');
    }


    protected function try_get_string($identifier, $component = '', $a = null) {
        if (!get_string_manager()->string_exists($identifier, $component)) {
            return ucfirst($identifier);
        } else {
            return get_string($identifier, $component, $a);
        }
    }

    /**
     * @todo CHECK IF TITLE NEEDED Required Function
     * @param stdClass $submission
     * @return bool
     */
    public function is_empty(stdClass $submission) {
        $opencastsubmission = $this->get_opencast_submission($submission->id);
        $wordcount = 0;
        if (isset($opencastsubmission->title)) {
            $wordcount = count_words(trim($opencastsubmission->title));

        }
        return $wordcount == 0;
    }

    /**
     * @todo CHECK IF TITLE NEEDED Required Function
     * Determine if a submission is empty
     *
     * This is distinct from is_empty in that it is intended to be used to
     * determine if a submission made before saving is empty.
     *
     * @param stdClass $data The submission data
     * @return bool
     */
    public function submission_is_empty(stdClass $data) {
        $wordcount = 0;

        if (isset($data->title)) {
            $wordcount = count_words($data->title);
        }

        return $wordcount == 0;
    }

    /**
     * Return the plugin configs for external functions.
     *
     * @return array the list of settings
     * @since Moodle 3.2
     */
    public function get_config_for_external() {
        return (array) $this->get_config();
    }

    /**
     * @todo delete video  also from opencast need to be done
     * Remove an Opencast submission.
     *
     * @param stdClass $submission The submission
     * @return boolean
     */
    public function remove(stdClass $submission) {
        global $DB;

        $submissionid = $submission ? $submission->id : 0;

        $opencastsubmission = $this->get_opencast_submission($submissionid);
        $submissionData = $DB->get_records('assign_opencast',
            array('submission'=>$submission->id));

        foreach ($submissionData as $data){
            // this is the serieUid that the Teacher have created.
            $videoidentifier = $data->eventuid;
        }

        $api_assignsubmission_opencast = \assignsubmission_opencast\api\api_assignsubmission_opencast::get_instance();
        $res = $api_assignsubmission_opencast->opencast_remove($videoidentifier);


        if ($submissionid) {
            $DB->delete_records('assign_opencast', array('submission' => $submissionid));
        }

        return true;
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        $DB->delete_records('assign_opencast',
            array('assignment'=>$this->assignment->get_instance()->id));

        return true;
    }

    /**
     * Get Serie Uid
     */
    public function get_instance_series($assignmentid,$courseid) {
        global $DB;

        $params = array('assignment'=>$assignmentid, 'courseid'=>$courseid);
        $resultat = $DB->get_records('assign_opencast_series', $params);
        return $resultat;
    }



    /**
     ** @param integer $submission
     */
    public function get_video_status($submission) {
        global $DB;
        global $OUTPUT;
        $mediaArray = array();

        $presenter_video_url = $presentation_video_url = '';

        $opencastsubmission = $this->get_opencast_submission($submission);

        $submissionData = $DB->get_records('assign_opencast',
            array('submission'=>$submission));

        foreach ($submissionData as $data){
            // this is the serieUid that the Teacher have created.
            $videoidentifier = $data->eventuid;
        }

        $api_assignsubmission_opencast = \assignsubmission_opencast\api\api_assignsubmission_opencast::get_instance();
        $video = $api_assignsubmission_opencast->get_opencast_video($videoidentifier);

        $publications = $api_assignsubmission_opencast->get_opencast_publications($videoidentifier);

        foreach ($publications as  $publication){
            foreach ($publication as $media_array) {
                foreach ($media_array->media as $key => $url_array)
                    $mediaArray[$key][$url_array->flavor] = $url_array->url;
            }
        }

        if (array_key_exists('presenter/delivery', $mediaArray[1])) {
            $presenter_video_url = $mediaArray[1]["presenter/delivery"];
        }
        if (array_key_exists('presenter/delivery', $mediaArray[0])) {
            $presenter_video_url = $mediaArray[0]["presenter/delivery"];
        }
        if (array_key_exists('presentation/delivery', $mediaArray[0])) {
            $presentation_video_url = $mediaArray[0]["presentation/delivery"];
        }
        if (array_key_exists('presentation/delivery', $mediaArray[1])) {
            $presentation_video_url = $mediaArray[1]["presentation/delivery"];
        }
        switch ($video->video->processing_state) {
            case 'FAILED' :
                $out =  $OUTPUT->pix_icon('failed', get_string('ocstatefailed', 'assignsubmission_opencast'), 'assignsubmission_opencast');
                $out .=  get_string('ocstatefailedmessage', 'assignsubmission_opencast');
                return $out;
            case 'PLANNED' :
                $out =  $OUTPUT->pix_icon('c/event', get_string('planned', 'assignsubmission_opencast'), 'assignsubmission_opencast');
                $out .=  get_string('plannedmessage', 'assignsubmission_opencast');
                return $out;
            case 'CAPTURING' :
                return $OUTPUT->pix_icon('capturing', get_string('ocstatecapturing', 'assignsubmission_opencast'), 'assignsubmission_opencast');
            case 'NEEDSCUTTING' :
                return $OUTPUT->pix_icon('e/cut', get_string('ocstateneedscutting', 'assignsubmission_opencast'), 'assignsubmission_opencast');
            case 'RUNNING' :
            case 'PAUSED' :
                $out = "<br>";
                $out .=  $OUTPUT->pix_icon('processing', get_string('ocstateprocessing', 'assignsubmission_opencast'), 'assignsubmission_opencast');
                $out .=  get_string('processingmessage', 'assignsubmission_opencast');
                return $out;
            case 'SUCCEEDED' :
                $out = "<br>";
                $out .=  $OUTPUT->pix_icon('succeeded', get_string('ocstatesucceeded', 'assignsubmission_opencast'), 'assignsubmission_opencast');
                $out .=  get_string('succeededmessage', 'assignsubmission_opencast');
                if($presenter_video_url != ''){


                    $out .= "<br>";
                    $out .= "<strong>".get_string('pres', 'assignsubmission_opencast')."</strong>";
                    $out .= "<br>";
                    $out .= "<video width='400' controls>  
                             <source src='$presenter_video_url' type='video/mp4'>
                                 Your browser does not support the video tag.
                             </video>";

                }
                if($presentation_video_url != ''){
                    $out .= "<br>";
                    $out .= "<strong>".get_string('presentation', 'assignsubmission_opencast')."</strong>";
                    $out .= "<br>";
                    $out .= "<video width='400' controls>  
                             <source src='$presenter_video_url' type='video/mp4'>
                             </video>";

                }
                return $out;
        }
        return "";

    }
}