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
 * Feed service.
 *
 * @package   theme_snap
 * @copyright Copyright (c) 2019 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_snap\webservice;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_function_parameters;
use theme_snap\local;

/**
 * Feed service.
 *
 * @package   theme_snap
 * @copyright Copyright (c) 2019 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ws_feed extends external_api {
    /**
     * @return external_function_parameters
     */
    public static function service_parameters() {
        $parameters = [
            'feedid' => new external_value(PARAM_TEXT, 'Feed identifier', VALUE_REQUIRED),
            'page' => new external_value(PARAM_INT, 'Page', VALUE_DEFAULT),
            'pagesize' => new external_value(PARAM_INT, 'Page size', VALUE_DEFAULT),
        ];
        return new external_function_parameters($parameters);
    }

    /**
     * @return external_multiple_structure
     */
    public static function service_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'iconUrl'      => new external_value(PARAM_URL, 'URL of icon'),
                'iconDesc'     => new external_value(PARAM_RAW, 'Description of icon'),
                'iconClass'    => new external_value(PARAM_RAW, 'CSS class of icon'),
                'title'        => new external_value(PARAM_RAW, 'Feed item title'),
                'subTitle'     => new external_value(PARAM_RAW, 'Feed item subtitle'),
                'actionUrl'    => new external_value(PARAM_URL, 'Feed item action url'),
                'description'  => new external_value(PARAM_RAW, 'Feed item description'),
                'extraClasses' => new external_value(PARAM_RAW, 'Feed item extra CSS classes'),
            ])
        );
    }

    /**
     * @param string $feedid
     * @param null|int $page
     * @return array
     */
    public static function service($feedid, $page = 0, $pagesize = 5) {
        $params = self::validate_parameters(self::service_parameters(), [
            'feedid' => $feedid,
            'page' => $page,
            'pagesize' => $pagesize,
        ]);

        self::validate_context(\context_system::instance());

        switch ($params['feedid']) {
            case 'graded':
                $res = local::graded_data();
                break;
            case 'grading':
                $res = local::grading_data();
                break;
            case 'forumposts':
                $res = local::recent_forum_activity_data();
                break;
            case 'messages':
                $limitfrom = $page * $pagesize;
                $res = local::messages_data(false, $limitfrom, $pagesize);
                break;
            default:
                $res = self::test_feed($params['page'], $params['pagesize']);
                break;
        }
        return $res;
    }

    private static function test_feed($page, $pagesize) {
        $numitems = 33;

        $feeditemmodel = [
            'iconUrl'      => '',
            'iconDesc'     => '',
            'iconClass'    => '',
            'title'        => 'Test feed item ',
            'subTitle'     => 'Test feed subtitle ',
            'actionUrl'    => 'http://google.com',
            'description'  => 'A feed test took place',
            'extraClasses' => '',
        ];

        $res = [];
        $startpageidx = (($page * $pagesize) + 1);
        $endpageidx = ((($page + 1) * $pagesize));
        for ($i = $startpageidx; $i <= $numitems && $i <= $endpageidx; $i++) {
            $feeditem = $feeditemmodel;
            $feeditem['title'] .= $i;
            $feeditem['subTitle'] .= $i;
            $res[] = $feeditem;
        }

        return $res;
    }
}
