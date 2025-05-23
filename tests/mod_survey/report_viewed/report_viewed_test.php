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

namespace logstore_xapi\mod_survey;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/admin/tool/log/store/xapi/tests/xapi_test_case.php');

/**
 * Unit test for report viewed event
 *
 * @package   logstore_xapi
 * @copyright Milt Reder <milt@yetanalytics.com>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_viewed_test extends \logstore_xapi\xapi_test_case {

  /**
   * Initialize, skipping if mod_survey is not present.
   *
   * @return void
   */
  public function setUp(): void {
    global $CFG;
    parent::setUp();

    // Skip tests if mod_survey is not present (i.e., Moodle 5.0+).
    if (!is_dir($CFG->dirroot . '/mod/survey')) {
      $this->markTestSkipped('mod_survey is not available in Moodle 5.0 and above.');
    }
  }

  /**
   * Retrieve the directory of the unit test.
   *
   * @return string
   */
  protected function get_test_dir() {
    return __DIR__;
  }

  /**
   * Retrieve the plugin type being tested.
   *
   * @return string
   */
  protected function get_plugin_type() {
    return "core";
  }

  /**
   * Retrieve the plugin name being tested.
   *
   * @return string
   */
  protected function get_plugin_name() {
    return "mod_survey";
  }

  /**
   * Appease auto-detecting of test cases. xapi_test_case has default test cases.
   *
   * @covers ::report_viewed
   * @return void
   */
  public function test_init() {

  }
}
