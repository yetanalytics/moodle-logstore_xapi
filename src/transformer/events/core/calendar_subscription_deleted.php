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
 * Transform for course viewed event.
 *
 * @package   logstore_xapi
 * @copyright Daniel Bell <daniel@yetanalytics.com>
 *            Milt Reder <milt@yetanalytics.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\events\core;

use src\transformer\utils as utils;
use src\transformer\utils\get_activity as activity;

/**
 * undefined
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $event The event to be transformed.
 * @return array
 */

function calendar_subscription_deleted(array $config, \stdClass $event) {
    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->userid);
    $course = $event->courseid == 0 ? null : $repo->read_record_by_id('course', $event->courseid);
    $lang = is_null($course) ? $config['source_lang'] : utils\get_course_lang($course);
    $object_id = $config['app_url'].'/calendar/subscription?id='.$event->objectid;

    $statement = [
        'actor' => utils\get_user($config,$user),
        'verb' => ['id' => 'http://activitystrea.ms/delete',
                   'display' => [
                       'en' => 'Deleted'
                   ]],
        'object' => [
            'id' => $object_id,
            'definition' => [
                'type' => 'https://xapi.edlm/profiles/edlm-lms/concepts/activity-types/calendar-subscription'
            ],
        ],
        'context' => [
            'language' => $lang,
            'contextActivities' =>  [
                'category' => [activity\site($config)],
            ],
            'extensions' => utils\extensions\base($config, $event, $course)
        ]];

        if ($course){
            $statement = utils\add_parent($config, $statement, $course);
        }

        return [$statement];
}
