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
 * Transformer fn for chapter created event.
 *
 * @package   logstore_xapi
 * @copyright Milt Reder <milt@yetanalytics.com>
 *
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\events\mod_book;

use src\transformer\utils as utils;

/**
 * Transformer fn for chapter created event.
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $event The event to be transformed.
 * @return array
 */

function chapter_created(array $config, \stdClass $event) {
    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->userid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $chapter = $repo->read_record_by_id('book_chapters', $event->objectid);
    $lang = utils\get_course_lang($course);

    return[[
        'actor' => utils\get_user($config, $user),
        'verb' => [
            'id' => 'http://activitystrea.ms/create',
            'display' => [
                'en' => 'Created'
            ],
        ],
        'object' => utils\get_activity\book_chapter(
            $config, $course, $chapter, $event->contextinstanceid
        ),
        'context' => [
            ...utils\get_context_base($config, $event, $lang, $course),
            'contextActivities' => [
                'parent' => utils\context_activities\get_parent(
                    $config,
                    $event->contextinstanceid,
                    true
                ),
                'category' => [
                    utils\get_activity\site($config),
                ],
            ],
        ]
    ]];
}
