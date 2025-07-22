@editor @editor_tiny @tiny @tiny_multilang2 @javascript
Feature: Test the tiny editor multilang plugin with the fallback other feature enabled or disabled.

  Background: I login as admin and add a text to the description with two paragraphs.
    Given the following config values are set as admin:
      | requiremultilang2 | 0 | tiny_multilang2 |
      | highlight         | 1 | tiny_multilang2 |
      | showfallbackother | 1 | tiny_multilang2 |
    And the following "language packs" exist:
      | language |
      | de       |
    And the "multilang" filter is "on"
    And the "multilang" filter applies to "content"
    And the "multilang2" filter is "on"
    And the "multilang2" filter applies to "content"
    And I log in as "admin"

  Scenario: Toggle the showfallbackother setting.
    Given I log in as "admin"
    And I open my profile in edit mode
    And I wait until the page is ready
    And I click on the "Format > Language" submenu item for the "Description" TinyMCE editor
    Then I should see "English (en)"
    And I should see "Deutsch (de)"
    And I should see "Remove all lang tags"
    And I should see "Fallback (other)"
    When I navigate to "Plugins > Text editors > Multi-Language Content (v2)" in site administration
    And I set the field "s_tiny_multilang2_showfallbackother" to "0"
    And I click on "Save changes" "button"
    And I open my profile in edit mode
    And I wait until the page is ready
    And I click on the "Format > Language" submenu item for the "Description" TinyMCE editor
    Then I should see "English (en)"
    And I should see "Deutsch (de)"
    And I should see "Remove all lang tags"
    And I should not see "Fallback (other)"
