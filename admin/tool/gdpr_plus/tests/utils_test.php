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

namespace tool_gdpr_plus;

use tool_policy\api;
use tool_policy\policy_version;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for the update_course class.
 *
 * @package   tool_gdpr_plus
 * @copyright 2021 - CALL Learning - Laurent David <laurent@call-learning.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utils_test extends \advanced_testcase {
    /**
     * @var array $policyversions
     */
    protected $policyversions = [];
    /**
     * Sample policies for test
     */
    const SAMPLE_POLICIES = [
        [
            'name' => 'Site policy',
            'content' => 'full text2',
            'summary' => 'short text2',
            'status' => 'active',
            'optional' => '0',
            'audience' => 'all'
        ],
        [
            'name' => 'This cookies policy',
            'content' => 'full text3',
            'summary' => 'short text3',
            'status' => 'active',
            'optional' => '1',
            'audience' => 'all'
        ],
        [
            'name' => 'This privacy policy',
            'content' => 'full text4',
            'summary' => 'short text4',
            'status' => 'active',
            'optional' => '1',
            'audience' => 'loggedin'
        ],
    ];

    /**
     * Setup policies for test case
     *
     * @return void
     */
    public function setUp(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator()->get_plugin_generator('tool_gdpr_plus');
        $this->policyversions = [];
        foreach (self::SAMPLE_POLICIES as $policy) {
            $pversion = $generator->create_policy((object) $policy)->to_record();
            $this->policyversions[] = $pversion->id;
        }
    }

    /**
     * Test helper function
     *
     * @return void
     */
    public function test_is_loggedin_no_guest() {
        $this->resetAfterTest();
        $this->assertFalse(utils::is_loggedin_no_guest());
        $this->setGuestUser();
        $this->assertFalse(utils::is_loggedin_no_guest());
        $this->setAdminUser();
        $this->assertTrue(utils::is_loggedin_no_guest());
    }

    /**
     * Test that acceptance policy is reflected in the relevant setting (either session or else)
     *
     * @param string $accept
     *
     * @dataProvider data_generator_policy_acceptance
     */
    public function test_set_policies_acceptances_guest($accept) {
        global $_SESSION;
        $this->setGuestUser();
        $presignupcache = \cache::make('core', 'presignup');
        $this->assertFalse($presignupcache->get(utils::CACHE_KEY_POLICIES_ACCEPTED));
        $policyacceptance = $this->prepare_acceptance_for_policies($accept);
        utils::set_policies_acceptances($policyacceptance);
        $acceptedpoliciesid = array_map(function($p) {
            return $p['policyversionid'];
        }, $policyacceptance);
        $this->assertEquals($acceptedpoliciesid, $presignupcache->get(utils::CACHE_KEY_POLICIES_ID_AGREED));
    }

    /**
     * Test that acceptance policy leads to full acceptance
     *
     * @param string $accept
     * @param bool $agreed
     *
     * @dataProvider data_generator_policy_acceptance
     */
    public function test_set_policies_acceptances_user($accept, $agreed) {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $acceptances = api::get_user_acceptances($user->id);
        $this->assertEmpty($acceptances);
        $policyacceptance = $this->prepare_acceptance_for_policies($accept);
        utils::set_policies_acceptances($policyacceptance);
        $acceptances = api::get_user_acceptances($user->id);
        $this->assertCount(count($policyacceptance), $acceptances);
    }

    /**
     * Test that acceptance policy leads to full acceptance
     *
     * @param string $accept
     *
     * @dataProvider data_generator_policy_acceptance
     */
    public function test_set_policies_acceptances_user_switch($accept) {
        $user1 = $this->getDataGenerator()->create_user();

        $this->setUser($user1);
        $acceptances = api::get_user_acceptances($user1->id);
        $this->assertEmpty($acceptances);

        $policyacceptance = $this->prepare_acceptance_for_policies($accept);
        utils::set_policies_acceptances($policyacceptance);
        $acceptances = api::get_user_acceptances($user1->id);
        $this->assertCount(count($policyacceptance), $acceptances);

        $user2 = $this->getDataGenerator()->create_user();
        $this->setUser($user2);
        $acceptances = api::get_user_acceptances($user2->id);
        $this->assertEmpty($acceptances);
    }

    /**
     * Test case for policy acceptance
     *
     * @return array[]
     */
    public function data_generator_policy_acceptance() {
        return [
            'accept none' => ['accept' => 'none', 'agreed' => false],
            'accept all' => ['accept' => 'all', 'agreed' => true],
            'accept mandatory only' => ['accept' => 'all', 'agreed' => true],
            'accept nonmandatory only' => ['accept' => 'nonmandatory', 'agreed' => false],
        ];
    }

    /**
     * Prepare data for acceptance
     *
     * @param string $mode
     * @return array
     */
    protected function prepare_acceptance_for_policies($mode) {
        $acceptance = [];
        switch ($mode) {
            case 'all':
                foreach ($this->policyversions as $policyversionid) {
                    $acceptance[] = ['policyversionid' => $policyversionid, 'accepted' => true];
                }
                break;
            case 'none':
                break;
            case 'nonmandatory':
                foreach ($this->policyversions as $index => $policyversionid) {
                    if (self::SAMPLE_POLICIES[$index]['optional'] == policy_version::AGREEMENT_OPTIONAL) {
                        $acceptance[] = ['policyversionid' => $policyversionid, 'accepted' => true];
                    }
                }
                break;
            case 'mandatory':
                foreach ($this->policyversions as $index => $policyversionid) {
                    if (self::SAMPLE_POLICIES[$index]['optional'] == policy_version::AGREEMENT_COMPULSORY) {
                        $acceptance[] = ['policyversionid' => $policyversionid, 'accepted' => true];
                    }
                }
                break;

        }
        return $acceptance;
    }

    /**
     * Test helper function
     *
     * @return void
     */
    public function test_retrieve_policies_with_acceptance_guest() {
        $this->setGuestUser();
        $this->assertFalse(utils::has_policy_been_agreed());
        $policyacceptance = $this->prepare_acceptance_for_policies('all');
        utils::set_policies_acceptances($policyacceptance);
        $policies = api::list_current_versions(policy_version::AUDIENCE_GUESTS);
        $policieswithacceptance = utils::retrieve_policies_with_acceptance($policies);
        $this->assertCount(2, $policieswithacceptance);
        $this->assertEquals([true, true], array_map(function($policy) {
            return $policy->policyagreed;
        }, $policieswithacceptance));
    }

    /**
     * Test helper function
     *
     * @return void
     */
    public function test_retrieve_policies_with_acceptance_user() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->assertFalse(utils::has_policy_been_agreed());
        $policyacceptance = $this->prepare_acceptance_for_policies('all');
        utils::set_policies_acceptances($policyacceptance);
        $policies = api::list_current_versions(policy_version::AUDIENCE_ALL);
        $policieswithacceptance = utils::retrieve_policies_with_acceptance($policies);
        $this->assertCount(3, $policieswithacceptance);
        $this->assertEquals([true, true, true], array_map(function($policy) {
            return $policy->policyagreed;
        }, $policieswithacceptance));
    }

    /**
     * Test that acceptance policy is reflected in the relevant setting (either session or else)
     *
     * @param string $accept
     * @param bool $agreed
     *
     * @dataProvider data_generator_policy_acceptance
     */
    public function test_has_policy_been_agreed_guest($accept, $agreed) {
        $this->setGuestUser();
        $this->assertFalse(utils::has_policy_been_agreed());
        $policyacceptance = $this->prepare_acceptance_for_policies($accept);
        utils::set_policies_acceptances($policyacceptance);
        $this->assertEquals($agreed, utils::has_policy_been_agreed());
    }

    /**
     * Test that acceptance policy is reflected in the relevant setting (either session or else)
     *
     * @param string $accept
     * @param bool $agreed
     *
     * @dataProvider data_generator_policy_acceptance
     */
    public function test_has_policy_been_agreed_user($accept, $agreed) {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->assertFalse(utils::has_policy_been_agreed());
        $policyacceptance = $this->prepare_acceptance_for_policies($accept);
        utils::set_policies_acceptances($policyacceptance);
        $this->assertEquals($agreed, utils::has_policy_been_agreed());
    }

    /**
     * Test that acceptance policy leads to full acceptance
     *
     * @param string $accept
     * @param bool $agreed
     *
     * @dataProvider data_generator_policy_acceptance
     */
    public function test_has_policy_been_agreed_switch($accept, $agreed) {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->assertFalse(utils::has_policy_been_agreed());
        $policyacceptance = $this->prepare_acceptance_for_policies($accept);
        utils::set_policies_acceptances($policyacceptance);
        $this->assertEquals($agreed, utils::has_policy_been_agreed());

        $user2 = $this->getDataGenerator()->create_user();
        $this->setUser($user2);
        $this->assertFalse(utils::has_policy_been_agreed());
    }
}
