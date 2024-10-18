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
 * Transformer utilities for creating Question xAPI Activity object definitions.
 *
 * @package   logstore_xapi
 * @copyright Jerret Fowler <jerrett.fowler@gmail.com>
 *            Ryan Smith <https://www.linkedin.com/in/ryan-smith-uk/>
 *            David Pesce <david.pesce@exputo.com>
 *            Milt Reder <milt@yetanalytics.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace src\transformer\utils\get_activity\definition\question;

use src\transformer\utils as utils;

/**
 * Transformer util for creating essay definitions
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $question The question object.
 * @param string $lang The language.
 */
function get_essay_definition(array $config, \stdClass $question, string $lang) {
    return [
        'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
        'name' => [
            $lang => utils\get_string_html_removed($question->questiontext)
        ],
        'interactionType' => 'long-fill-in',
    ];
}

/**
 * Transformer util for creating multichoice definitions.
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $questionattempt The questionattempt object.
 * @param \stdClass $question The question object.
 * @param string $lang The language.
 * @param string $interactiontype The type of interaction.
 * @return array
 */
function get_multichoice_definition(
    array $config,
    \stdClass $questionattempt,
    \stdClass $question,
    string $lang,
    string $interactiontype = 'choice'
) {
    if ($config['send_response_choices']) {
        $repo = $config['repo'];
        $answers = $repo->read_records('question_answers', [
            'question' => $questionattempt->questionid
        ]);
        $choices = array_map(function ($answer) use ($lang) {
            return [
                "id" => "$answer->id",
                "description" => [
                    $lang => utils\get_string_html_removed($answer->answer)
                ]
            ];
        }, $answers);

        $correctresponsepattern;
        switch ($interactiontype) {
        case 'sequencing':
            $selections = explode('} {', rtrim(ltrim($questionattempt->rightanswer, '{'), '}'));
            $correctresponsepattern = implode ('[,]', $selections);
            break;
        default:
            $selections = explode('; ', utils\get_string_html_removed($questionattempt->rightanswer));
            $correctresponsepattern = implode ('[,]', $selections);
            break;
        }

        return [
            'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
            'name' => [
                $lang => utils\get_string_html_removed($question->questiontext),
            ],
            'interactionType' => $interactiontype,
            'correctResponsesPattern' => [$correctresponsepattern],
            // Need to pull out id's that are appended during array_map so json parses it correctly as an array.
            'choices' => array_values($choices)
        ];
    }

    return [
        'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
        'name' => [
            $lang => utils\get_string_html_removed($question->questiontext),
        ],
        'interactionType' => $interactiontype
    ];
}

/**
 * Transformer util for creating match definitions
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $question The question object.
 * @param string $lang The language.
 */
function get_match_definition(array $config, \stdClass $question, string $lang) {
    return [
        'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
        'name' => [
            $lang => utils\get_string_html_removed($question->questiontext)
        ],
        'interactionType' => 'matching',
    ];
}

/**
 * Transformer util for creating numerical definitions
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $question The question object.
 * @param string $lang The language.
 */
function get_numerical_definition(array $config, \stdClass $question, string $lang) {
    return [
        'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
        'name' => [
            $lang => utils\get_string_html_removed($question->questiontext)
        ],
        'interactionType' => 'numeric',
    ];
}

/**
 * Transformer util for creating shortanswer definitions
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $question The question object.
 * @param string $lang The language.
 */
function get_shortanswer_definition(array $config, \stdClass $question, string $lang) {
    return [
        'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
        'name' => [
            $lang => utils\get_string_html_removed($question->questiontext)
        ],
        'interactionType' => 'fill-in',
    ];
}

/**
 * Transformer util for creating true/false definitions
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $question The question object.
 * @param string $lang The language.
 */
function get_true_false_definition(array $config, \stdClass $question, string $lang) {
    return [
        'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
        'name' => [
            $lang => utils\get_string_html_removed($question->questiontext),
        ],
        'interactionType' => 'true-false',
    ];
}
