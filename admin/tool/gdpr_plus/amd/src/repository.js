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
 * Policies consent
 *
 * Derived from https://github.com/klaxit/cookie-consent
 * version 0.3.4
 *
 * @module    tool_gdpr_plus/src/repository
 * @class     PoliciesConsentManager
 * @package   tool_gdpr_plus
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call as fetchMany} from 'core/ajax';

/**
 * Fetch the list of recordings from the server.
 *
 * @param   {Array} policiesAcceptance The instance ID
 * @returns {Promise}
 */
export const acceptPolicies = (policiesAcceptance) => {
    const args = {
        'policies': policiesAcceptance
    };
    return fetchMany([{methodname: 'tool_gdpr_plus_accept_policies', args}])[0];
};
