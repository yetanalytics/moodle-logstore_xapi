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
 * Transformer utility for cleaning HTML from strings.
 *
 * @package   logstore_xapi
 * @copyright Daniel Bell <daniel@yetanalytics.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\utils;

/**
 * Transformer utility for generating note object for note_created and note_updated events
 *
 * @param array $config
 * @param array $subject
 * @param string $lang
 * @param array $note
 * @return object
 */
function note_object($config, $lang, $subject, $note) {
  return [
    'id' => $config['app_url'].'/notes/view.php?id='.$note->id,
    'definition' => [
      'name' => [$lang => get_string_html_removed($note->subject)],
      'type' =>  'http://activitystrea.ms/note',
      'description' => [$lang => get_string_html_removed($note->content)],
      'extensions' => [
        "https://xapi.edlm/profiles/edlm-lms/concepts/activity-extensions/note-type" => "course",
        "https://xapi.edlm/profiles/edlm-lms/concepts/activity-extensions/note-subject" =>
          get_user($config,$subject)
      ]
    ]
  ];
}
