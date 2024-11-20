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
 * Transform for the forum post deleted event.
 *
 * @package   logstore_xapi
 * @copyright Cliff Casey <cliff@yetanalytics.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\events\mod_forum;

use src\transformer\utils as utils;

/**
 * Transformer for forum post deleted event.
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $event The event to be transformed.
 * @return array
 */
function post_deleted(array $config, \stdClass $event) {
    //debug
    $repo = $config['repo'];
    $user = $repo->read_record_by_id('user', $event->userid);
    $course = $repo->read_record_by_id('course', $event->courseid);
    $lang = utils\get_course_lang($course);

    $post = new \stdClass();
    $post->id = $event->objectid;

    $other = unserialize($event->other);
    $discussionid = $other['discussionid'];
    $post->discussion = $discussionid;
    $discussion = $repo->read_record_by_id('forum_discussions', $discussionid);

    return [[
        'actor' => utils\get_user($config, $user),
        'verb' => [
            'id' => 'http://activitystrea.ms/delete',
            'display' => [
                'en' => 'Deleted'
            ],
        ],
        'object' => utils\get_activity\forum_discussion_post_reply($config, $course, $post),
        'context' => [
            'language' => $lang,
            'extensions' => utils\extensions\base($config, $event, $course),
            'contextActivities' => [
                'parent' => array_merge(
                    [utils\get_activity\course_discussion($config, $course, $discussion)],
                    utils\context_activities\get_parent($config, $event->contextinstanceid, true)
                ),
                'category' => [
                    utils\get_activity\site($config),
                ],
            ],
        ]
    ]];
}
