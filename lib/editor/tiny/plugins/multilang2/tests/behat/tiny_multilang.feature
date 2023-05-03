@editor @editor_tiny @tiny @tiny_multilang2 @javascript
Feature: Tiny editor multilang plugin
  To put content in Tiny in different languages, I need to use the mlang button to encapsulate the text in language tags.

  Background: I login as admin and add a text to the description with two paragraphs.
    Given the following config values are set as admin:
      | requiremultilang2 | 0 | tiny_multilang2 |
      | highlight | 1 | tiny_multilang2 |
    And the following "language packs" exist:
      | language |
      | de       |
    And I log in as "admin"
    And I open my profile in edit mode
    And I wait until the page is ready
    And I set the field "Description" to "<p>Some plain text</p><p>Ein anderer Text</p>"
    And I press "Update profile"
    Then I should see "Some plain text"
    And I should see "Ein anderer Text"

  Scenario: I login as admin and add a language tag at the beginning of the description.
    Given I log in as "admin"
    And I open my profile in edit mode
    And I wait until the page is ready
    And I click on the "Format > Language > English (en)" submenu item for the "Description" TinyMCE editor
    And I press "Update profile"
    Then I should see "{mlang en} {mlang}Some plain text"
    And I should see "Ein anderer Text"
    And I should not see "Ein anderer Text{mlang}"

  Scenario: I login as admin and and and later change the language of the first paragraph.
    Given I log in as "admin"
    And I open my profile in edit mode
    And I wait until the page is ready
    And I select the inner "p" element in position "0" of the "Description" TinyMCE editor
    And I click on the "Format > Language > English (en)" submenu item for the "Description" TinyMCE editor
    And I press "Update profile"
    Then I should see "{mlang en}Some plain text{mlang}"
    And I open my profile in edit mode
    And I wait until the page is ready
    And I select the "span" element in position "1" of the "Description" TinyMCE editor
    And I click on the "Format > Language > Fallback (other)" submenu item for the "Description" TinyMCE editor
    And I press "Update profile"
    Then I should see "{mlang other}Some plain text{mlang}"
    And I should not see "{mlang en}"
