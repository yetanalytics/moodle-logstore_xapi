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
 * Transform for the quiz attempt becameoverdue event.
 *
 * @package   logstore_xapi
 * @copyright Milt Reder <milt@yetanalytics.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\events\mod_quiz;

use src\transformer\utils as utils;

/**
 * Transform for the quiz attempt becameoverdue event.
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $event The event to be transformed.
 * @return array
 */
function attempt_becameoverdue(array $config, \stdClass $event) {
    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->relateduserid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $attempt = $repo->read_record_by_id('quiz_attempts', $event->objectid);
    $coursemodule = $repo->read_record_by_id('course_modules', $event->contextinstanceid);
    $quiz = $repo->read_record_by_id('quiz', $attempt->quiz);
    $lang = utils\get_course_lang($course);

    return [[
        'actor' => utils\get_user($config, $user),
        'verb' => [
            'id' => 'https://xapi.edlm/profiles/edlm-lms/concepts/verbs/exceeded',
            'display' => [
                'en' => 'Exceeded',
            ],
        ],
        'object' => utils\get_activity\course_quiz($config, $course, $event->contextinstanceid),
        'context' => [
            'language' => $lang,
            'extensions' => utils\extensions\base($config, $event, $course),
            'contextActivities' => [
                'parent' => utils\context_activities\get_parent(
                    $config,
                    $event->contextinstanceid
                ),
                'other' => [
                    utils\get_activity\quiz_attempt($config, $attempt->id, $coursemodule->id),
                ],
                'category' => [
                    utils\get_activity\site($config),
                ]
            ],
        ]
    ]];
}
