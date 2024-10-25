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
 * Helper for getting basic interaction activity def data.
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $question The question object.
 * @param string $lang The language.
 */
function get_def_base(array $config, \stdClass $question, string $lang) {
    return [
        'type' => 'http://adlnet.gov/expapi/activities/cmi.interaction',
        'name' => [
            $lang => $question->name,
        ],
        'description' => [
            $lang => utils\get_string_html_removed($question->questiontext),
        ],
    ];
}

/**
 * Transformer util for creating essay definitions
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $question The question object.
 * @param string $lang The language.
 */
function get_essay_definition(array $config, \stdClass $question, string $lang) {
    return array_merge(
        get_def_base($config, $question, $lang),
        [
            'interactionType' => 'long-fill-in',
        ]
    );
}

/**
 * Transformer util for creating multichoice definitions.
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $question The question object.
 * @param string $lang The language.
 * @param ?string $interactiontype The type of interaction.
 * @param ?string $rightanswer The correct answer, not always available.
 * @return array
 */
function get_multichoice_definition(
    array $config,
    \stdClass $question,
    string $lang,
        ?string $interactiontype = 'choice',
        ?string $rightanswer = null
) {
    if ($config['send_response_choices']) {
        $repo = $config['repo'];
        $answers = $repo->read_records('question_answers', [
            'question' => $question->id
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

        if (!is_null($rightanswer)) {
            switch ($interactiontype) {
            case 'sequencing':
                $selections = explode('} {', rtrim(ltrim($rightanswer, '{'), '}'));
                $correctresponsepattern = implode ('[,]', $selections);
                break;
            default:
                $selections = explode('; ', utils\get_string_html_removed($rightanswer));
                $correctresponsepattern = implode ('[,]', $selections);
                break;
            }
        }

        $def = array_merge(
            get_def_base($config, $question, $lang),
            [
                'interactionType' => $interactiontype,
                'correctResponsesPattern' => [$correctresponsepattern],
                // Need to pull out id's that are appended during array_map so json parses it correctly as an array.
                'choices' => array_values($choices)
            ]
        );

        if (!is_null($correctresponsepattern)) {
            $def['correctResponsesPattern'] = [$correctresponsepattern];
        }

        return $def;
    } else {
        return array_merge(
            get_def_base($config, $question, $lang),
            [
                'interactionType' => $interactiontype
            ]
        );
    }


}

/**
 * Transformer util for creating match definitions
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $question The question object.
 * @param string $lang The language.
 */
function get_match_definition(array $config, \stdClass $question, string $lang) {
    return array_merge(
        get_def_base($config, $question, $lang),
        [
            'interactionType' => 'matching',
        ]
    );
}

/**
 * Transformer util for creating numerical definitions
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $question The question object.
 * @param string $lang The language.
 */
function get_numerical_definition(array $config, \stdClass $question, string $lang) {
    return array_merge(
        get_def_base($config, $question, $lang),
        [
            'interactionType' => 'numeric',
        ]
    );
}

/**
 * Transformer util for creating shortanswer definitions
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $question The question object.
 * @param string $lang The language.
 */
function get_shortanswer_definition(array $config, \stdClass $question, string $lang) {
    return array_merge(
        get_def_base($config, $question, $lang),
        [
            'interactionType' => 'fill-in',
        ]
    );
}

/**
 * Transformer util for creating true/false definitions
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $question The question object.
 * @param string $lang The language.
 */
function get_true_false_definition(array $config, \stdClass $question, string $lang) {
    return array_merge(
        get_def_base($config, $question, $lang),
        [
            'interactionType' => 'true-false',
        ]
    );
}

/**
 * Generic handler for question definitions.
 *
 * @param array $config The transformer config settings.
 * @param \stdClass $question The question.
 * @param string $lang The language to use.
 * @return array
 */
function get_definition(array $config, \stdClass $question, string $lang) {
    switch ($question->qtype) {
    case 'essay':
        return get_essay_definition($config, $question, $lang);
    case 'gapselect':
        return get_multichoice_definition(
            $config, $question, $lang, 'sequencing'
        );
    case 'truefalse':
        return get_true_false_definition($config, $question, $lang);
    case 'randomsamatch':
    case 'match':
        return get_match_definition($config, $question, $lang);
    case 'shortanswer':
        return get_shortanswer_definition($config, $question, $lang);
    case 'multichoice':
    case 'multichoiceset':
        return get_multichoice_definition(
            $config, $question, $lang, 'choice'
        );
    case 'numerical':
        return get_numerical_definition($config, $question, $lang);
    default:
        return [];
    }
}
