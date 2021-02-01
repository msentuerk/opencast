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

$string['default'] = 'Enabled by default';
$string['default_help'] = 'If set, this submission method will be enabled by default for all new assignments.';
$string['enabled'] = 'Opencast';
$string['enabled_help'] = 'If enabled, students are able to upload a video opencast field for their submission.';
$string['opencast'] = 'Opencast';
$string['opencastsubmission'] = 'Allow opencast video submission';
$string['pluginname'] = 'Opencast submissions';
$string['video_chunk'] = 'Presenter video';
$string['pres'] = 'Presenter video';
$string['presentation'] = 'Presentation video';
$string['seriesname'] = 'Series name';
$string['apipassword'] = 'Password for API user';
$string['apipassworddesc'] = 'Setup a password for the super user, who does the api calls.';
$string['apipasswordempty'] = 'Password for API user is not setup correctly, go to settings of tool opencast to fix this';
$string['apiurl'] = 'Opencast API url';
$string['apiurldesc'] = 'Setup the base url of the Opencast system, for example: opencast.example.com';
$string['apiurlempty'] = 'Url for Opencast API is not setup correctly, go to settings of tool opencast to fix this';
$string['apiusername'] = 'Username for API calls';
$string['apiusernamedesc'] = 'For all calls to the API moodle uses this user. Authorization is done by adding suitable roles to the call';
$string['apiusernameempty'] = 'Username for Opencast API user is not setup correctly, go to settings of tool opencast to fix this';
$string['connecttimeout'] = 'Connection timeout';
$string['connecttimeoutdesc'] = 'Setup the time in seconds while moodle is trying to connect to opencast until timeout';
$string['maximumsubmissionsize1'] = 'Maximum submission size Presenter Video';
$string['maximumsubmissionsize1_help'] = 'Videos uploaded by students may be up to this size.';
$string['maximumsubmissionsize2'] = 'Maximum submission size Presentation video';
$string['maximumsubmissionsize2_help'] = 'Videos uploaded by students may be up to this size.';
$string['acceptedfiletypes'] = 'Accepted file types';
$string['acceptedfiletypes_help'] = 'Accepted video types can be restricted by entering a list of file extensions. If the field is left empty, then all video types are allowed.';
$string['siteuploadlimit'] = 'Site upload limit';
$string['presentervideocheckbox'] = 'Presenter Video';
$string['presentataionvideocheckbox'] = 'Presentation video';
$string['presentervideocheckbox_help'] = 'You can choose if the student upload Presenter Video # If you select this the Student will be able to upload Presenter Video, Default is checked';
$string['presentataionvideocheckbox_help'] = 'You can choose if the student upload Presenter Video # If you select this the Student will be able to upload Presenter Video, Default is checked';
$string['metadata'] = 'Metadata';
$string['metadataexplanation'] = 'When uploading existing video files to Opencast, you can set several metadata fields. These will be stored together with the video.';
$string['metadata_autocomplete_placeholder'] = 'Enter {$a}';
$string['metadata_autocomplete_noselectionstring'] = 'No {$a} provided!';
$string['title'] = 'Title';
$string['date'] = 'Start Date';
$string['upload'] = 'File Upload';
$string['uploadexplanation'] = 'You have the option to upload a presenter video file and / or a presentation video file.<br />Most likely you will only upload one file, but Opencast is also capable of dealing with two videos at once which will be combined in a media package.';
$string['presentationdesc'] = 'Use the presentation video if you have a video file of a slide presentation recording or a screencast.';
$string['presentation'] = 'Presentation video';
$string['eventassessableuploaded'] = 'An Opencast Video has been uploaded.';
$string['serverconnectionerror'] = 'There was a problem with the connection to the opencast server. Please check your credentials and your network settings.';
$string['privacy:metadata:assignmentid'] = 'Assignment ID';
$string['privacy:metadata:submissionpurpose'] = 'The submission ID that links to submissions for the user.';
$string['privacy:metadata:tablepurpose'] = 'Stores the Opencast submission for each attempt.';
$string['privacy:metadata:titlepurpose'] = 'The title of the submission.';
$string['privacy:metadata:presentervideopurpose'] = 'The Actual presenter Video for this attempt of the assignment..';
$string['privacy:metadata:presentationvideopurpose'] = 'The Actual Presentation Video for this attempt of the assignment..';
$string['privacy:metadata:chunkpurpose'] = 'Videos that are embedded in the text submission and upload to opencast server.';
$string['ocstatesucceeded'] = 'Succeeded';
$string['succeededmessage'] = 'The video has been uploaded successfully';
$string['ocstatefailed'] = 'Failed';
$string['ocstatefailedmessage'] = 'The upload of Video is failed! Please contact the Teacher';
$string['planned'] = 'Planned';
$string['plannedmessage'] = 'The Video will be uploaded';
$string['ocstatecapturing'] = 'Capturing';
$string['ocstateneedscutting'] = 'Needs cutting';
$string['ocstateprocessing'] = 'Processing';
$string['processingmessage'] = 'Video is being processed. Please reload page after few minutes!';
