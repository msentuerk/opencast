<?php

/**
 * @package assignsubmission_opencast
 * @copyright 2020 Beuth Hochschule fuer Technik Berlin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace assignsubmission_opencast\privacy;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/locallib.php');

use \core_privacy\local\metadata\collection;
use core_privacy\local\request\userlist;
use \core_privacy\local\request\writer;
use \core_privacy\local\request\contextlist;
use \mod_assign\privacy\assign_plugin_request_data;
use mod_assign\privacy\useridlist;

/**
 * Privacy class for requesting user data.
 *
 * @package    assignsubmission_onlinetext
 * @copyright  2018 Adrian Greeve <adrian@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \mod_assign\privacy\assignsubmission_provider,
    \mod_assign\privacy\assignsubmission_user_provider {

    /**
     * Return meta data about this plugin.
     *
     * @param  collection $collection A list of information to add to.
     * @return collection Return the collection after adding to it.
     */
    public static function get_metadata(collection $collection) : collection {
        $detail = [
            'assignment' => 'privacy:metadata:assignmentid',
            'submission' => 'privacy:metadata:submissionpurpose',
            'title' => 'privacy:metadata:titlepurpose',
            'Presenter video' => 'privacy:metadata:presentervideopurpose',
            'Presentation video' => 'privacy:metadata:presentationvideopurpose',
        ];
        $collection->add_database_table('assign_opencast', $detail, 'privacy:metadata:tablepurpose');
        $collection->link_subsystem('local_chunkupload_files', 'privacy:metadata:chunkpurpose');
        return $collection;
    }


    public static function get_context_for_userid_within_submission(int $userid, contextlist $contextlist)
    {
        // TODO: Implement get_context_for_userid_within_submission() method.
    }

    public static function get_student_user_ids(useridlist $useridlist)
    {
        // TODO: Implement get_student_user_ids() method.
    }

    public static function export_submission_user_data(assign_plugin_request_data $exportdata)
    {
        // TODO: Implement export_submission_user_data() method.
    }

    public static function delete_submission_for_context(assign_plugin_request_data $requestdata)
    {
        // TODO: Implement delete_submission_for_context() method.
    }

    public static function delete_submission_for_userid(assign_plugin_request_data $exportdata)
    {
        // TODO: Implement delete_submission_for_userid() method.
    }

    public static function get_userids_from_context(userlist $userlist)
    {
        // TODO: Implement get_userids_from_context() method.
    }

    public static function delete_submissions(assign_plugin_request_data $deletedata)
    {
        // TODO: Implement delete_submissions() method.
    }
}
