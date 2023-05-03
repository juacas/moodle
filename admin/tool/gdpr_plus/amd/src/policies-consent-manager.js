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
 * @module    tool_gdpr_plus/src/policies-consent-manager
 * @class     PoliciesConsentManager
 * @package   tool_gdpr_plus
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Repository from './repository';
import {exception as displayException} from 'core/notification';

/**
 * Defaut settings for the constructor
 */
const DEFAULT_SETTINGS = {};

const POLICY_CHECKBOXES_SELECTOR = ".policies-settings-container input.policy-checkbox";
/**
 * Policy Consent Manager class
 */
export default class PoliciesConsentManager {
    constructor(alloptions = {}) {
        const {containerId, ...otheroptions} = alloptions;
        this.options = Object.assign(DEFAULT_SETTINGS, otheroptions);
        this.container = document.getElementById(containerId);

        this.container.querySelectorAll('.tool_gdpr_plus_action').forEach((element) => {
            const action = element.dataset.action;
            const ACTION_DISPATCHER = {
                "accept-selected": this.__acceptSelected,
                "reject-nonessential": this.__rejectNonEssential,
                "accept-all": this.__acceptAll
            };
            element.addEventListener("click", ACTION_DISPATCHER[action].bind(this));
        });
        if (!this.options.policyAgreed) {
            this.open();
        }
        const showpopup = this.container.querySelector(".show-popup");
        showpopup.addEventListener("click", this.open.bind(this));
    }

    open() {
        this.container.querySelector('.policy-container').classList.add("displayed");
    }

    close() {
        this.container.querySelector('.policy-container').classList.remove("displayed");
    }

    emit(event) {
        super.emit(event, this);
    }

    emitConsentChanged() {
        this.emit("user_cookie_consent_changed", {
            'consent': this._cookie.status,
            'acceptedCategories': this._cookie.acceptedCategories
        });
    }

    __getPoliciesAcceptance(rejectNonMandatory, forceAcceptance) {
        const policyAcceptance = [];
        this.container.querySelectorAll(POLICY_CHECKBOXES_SELECTOR).forEach(
            (policyElement) => {
                const policyVersionKey = policyElement.dataset.id;
                const mandatoryPolicy = (policyElement.dataset.mandatory === '1');
                let accepted = mandatoryPolicy || policyElement.checked;
                if (rejectNonMandatory && !mandatoryPolicy) {
                    accepted = false;
                }
                accepted = accepted || forceAcceptance;
                const acceptance = {
                    'policyversionid': policyVersionKey,
                    'accepted': accepted
                };
                policyAcceptance.push(acceptance);
            }
        );
        return policyAcceptance;
    }

    __acceptSelected() {
        const policiesAcceptances = this.__getPoliciesAcceptance(false, false);
        this.__acceptPolicies(policiesAcceptances);
    }

    __rejectNonEssential() {
        const policiesAcceptances = this.__getPoliciesAcceptance(true, false);
        this.__acceptPolicies(policiesAcceptances);
    }

    __acceptAll() {
        const policiesAcceptances = this.__getPoliciesAcceptance(false, true);
        this.__acceptPolicies(policiesAcceptances);
    }

    __acceptPolicies(policiesAcceptances) {
        const eventname = "grpd_policies_accepted";
        this.__setRemotePolicyAcceptance(policiesAcceptances);
        var e = new Event(eventname);
        e.policies = policiesAcceptances;
        document.dispatchEvent(e);
        this.__resetPolicyUI(policiesAcceptances);
        //PolicyStorage.set(POLICY_ACCEPTED_KEY, true);
        this.close();
    }

    __setRemotePolicyAcceptance(policiesAcceptances) {
        Repository.acceptPolicies(policiesAcceptances).catch(displayException);
    }

    __resetPolicyUI(policiesAcceptances) {
        this.container.querySelectorAll(POLICY_CHECKBOXES_SELECTOR).forEach(
            (policyElement) => {
                const policyAcceptanceFound = policiesAcceptances && policiesAcceptances.find(
                    (elem) => elem.policyid == policyElement.dataset.id
                );
                if (policyAcceptanceFound) {
                    policyElement.value = policyElement.checked = policyAcceptanceFound.accepted;
                }
            }
        );
    }
}
