@tool @tool_gdpr_plus
Feature: Accepting and reviewing acceptance later through the banner
  As a user I can accept all policies, accept only mandatory policies and review my choice later.

  Background:
    Given the following config values are set as admin:
      | sitepolicyhandler | tool_gdpr_plus |
    # This is required for now to prevent the overflow region affecting the action menus.
    And the following "tool_gdpr_plus > gdpr_policies" exist:
      | name                | revision | content    | summary     | status | optional | audience | agreementstyle |
      | This site policy    |          | full text2 | short text2 | active | 0        | all      | 0              |
      | This cookies policy |          | full text3 | short text3 | active | 1        | all      | 1              |
      | This privacy policy |          | full text4 | short text3 | active | 0        | loggedin | 0              |
    And the following "users" exist:
      | username | firstname | lastname | email           |
      | user1    | User      | One      | one@example.com |
      | user2    | User      | Two      | two@example.com |
      | manager  | Max       | Manager  | man@example.com |
    And the following "role assigns" exist:
      | user    | role    | contextlevel | reference |
      | manager | manager | System       |           |

  @javascript
  Scenario Outline: Accept policy on login as <type> and accept the policy directly on the page
    And I am on site homepage
    Given I log in as "<type>"
    And I should see "If you want to continue browsing this website, you need to agree to some of our policies."
    And I click on "Show settings" "button"
    Then I should see "This site policy"
    And I should see "This cookies policy"
    When I click on "a[data-name=\"this-cookies-policy\"]" "css_element"
    Then I click on "I agree to the This cookies policy" "button"
    Then I wait until the page is ready
    And I click on "Show settings" "button"
    Then I should see "This site policy"
    And I should see "This cookies policy"
    And "input[name=\"this-cookies-policy\"][checked]" "css_element" should exist

    Examples:
      | type  |
      | guest |
      | user1 |

  @javascript
  Scenario Outline: Accept policy without login in and keep the settings as is when navigating pages
    And I am on site homepage
    And I should see "If you want to continue browsing this website, you need to agree to some of our policies."
    And I click on "Show settings" "button"
    Then I should see "This site policy"
    And I should see "This cookies policy"
    And I should not see "This privacy policy"
    And "input[name=\"this-site-policy\"][checked]" "css_element" should exist
    And "input[name=\"this-cookies-policy\"]" "css_element" should exist
    And "input[name=\"this-cookies-policy\"][checked]" "css_element" should not exist
    And "input[name=\"this-privacy-policy\"]" "css_element" should not exist
    When I click on "This site policy" "link"
    Then I should see "full text2"
    Then I log in as "<type>"
    # Confirm when navigating, the pop-up policies are displayed.
    Then I click on "Show settings" "button"
    Then I <sitepolicysee> "This site policy"
    And I should see "This cookies policy"
    And I <privacypolicysee> "This privacy policy"
    Then I click on "input[name=\"this-cookies-policy\"]" "css_element"
    Then I click on "Save my choices" "button"
    When I reload the page
    Then I should not see "If you want to continue browsing this website, you need to agree to some of our policies"
    # A link but with button role
    When I click on "Show Policies Banner" "button"
    And I should see "If you want to continue browsing this website, you need to agree to some of our policies."
    Then I click on "Show settings" "button"
    Then I <sitepolicysee> "This site policy"
    And I should see "This cookies policy"
    And I <privacypolicysee> "This privacy policy"
    And "input[name=\"this-site-policy\"][checked]" "css_element" should exist
    And "input[name=\"this-cookies-policy\"][checked]" "css_element" should exist
    Examples:
      | type  | sitepolicysee | privacypolicysee |
      | guest | should see    | should not see   |
      | user1 | should see    | should see       |

  @javascript
  Scenario: Accept policy when login as user and I should be able to save and review it later.
    Given I log in as "user1"
    And I should see "This cookies policy"
    Then I click on "I agree to the This cookies policy" "button"
    And I should see "This site policy"
    And I press "Next"
    And I should see "This privacy policy"
    And I press "Next"
    And I press "Show settings"
    Then I wait until the page is ready
    Then I click on "input[name=\"this-cookies-policy\"]" "css_element"
    Then I click on "Save my choices" "button"
    And I am on site homepage
    And I should not see "If you want to continue browsing this website, you need to agree to some of our policies."
    # A link but with button role
    When I click on "Show Policies Banner" "button"
    And I should see "If you want to continue browsing this website, you need to agree to some of our policies."
    Then I click on "Show settings" "button"
    Then I should see "This site policy"
    And I should see "This cookies policy"
    And I should see "This privacy policy"
    And "input[name=\"this-site-policy\"][checked]" "css_element" should exist
    And "input[name=\"this-privacy-policy\"][checked]" "css_element" should exist
    And "input[name=\"this-cookies-policy\"][checked]" "css_element" should not exist
    Then I click on "input[name=\"this-cookies-policy\"]" "css_element"
    Then I click on "Save my choices" "button"
    Then I log out
    And I log in as "user1"
    And I am on site homepage
    And I should not see "If you want to continue browsing this website, you need to agree to some of our policies."
    When I click on "Show Policies Banner" "button"
    And I should see "If you want to continue browsing this website, you need to agree to some of our policies."
    Then I click on "Show settings" "button"
    And "input[name=\"this-cookies-policy\"][checked]" "css_element" should exist
    Then I click on "input[name=\"this-cookies-policy\"]" "css_element"
    Then I click on "Save my choices" "button"
    Then I log out
    And I log in as "user1"
    When I click on "Show Policies Banner" "button"
    Then I click on "Show settings" "button"
    And "input[name=\"this-cookies-policy\"][checked]" "css_element" should not exist
