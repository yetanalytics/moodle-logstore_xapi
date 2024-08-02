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
 * Transform for calendar subscription updated event.
 *
 * @package   logstore_xapi
 * @copyright Daniel Bell <daniel@yetanalytics.com>
 *            
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\events\core;

use src\transformer\utils as utils;
use src\transformer\utils\get_activity as activity;

/**
 * Transformer fn for calendar_subscription_updated event
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $event The event to be transformed.
 * @return array
 */

function calendar_subscription_updated(array $config, \stdClass $event) {
    
    global $CFG;
    $repo = $config['repo'];
    if (isset($event->objecttable) && isset($event->objectid)) {
        $event_object = $repo->read_record_by_id($event->objecttable, $event->objectid);
    } else {
        $event_object = array();
    }

    $course = (isset($event->courseid) && $event->courseid != 0) ? $repo->read_record_by_id('course', $event->courseid) : null;
    $lang = $course ? utils\get_course_lang($course): 'en';
    $user=$repo->read_record_by_id('user',$event->userid);

    $object_id = is_null($event_object->url) ? $config['app_url'].'/calendar/subscription?id='.$event_object->id : $event_object->url;
    
    $statement = [
        'actor' => utils\get_user($config,$user),
        'verb' => ['id' => 'https://w3id.org/xapi/acrossx/verbs/edited',
                   'display' => ['en' => 'Edited']],
        'object' => [
            'id' =>  $object_id,
            'definition' => [
                'name' => [$lang => $event_object->name],
                'type' => 'https://xapi.edlm/profiles/edlm-lms/concepts/activity-types/calendar-subscription'
            ],
        ],
        'context' => [
            'language' => $lang,
            'contextActivities' => [
                'category' => [activity\site($config)],
            ],
            'extensions' => utils\extensions\base($config, $event, $course)
        ]];

    if ($course){
        $statement = utils\add_parent($config,$statement,$course);
    }
    
    return [$statement];
}
