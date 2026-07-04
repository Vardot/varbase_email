@varbase_email @mailer
Feature: Varbase Email - Symfony Mailer administration
  Background:
    Given I am a logged in user with the "Webmaster" user

  Scenario: The Symfony Mailer settings page is reachable with Varbase Email enabled
    When I go to "/admin/config/system/mailer"
    Then I should see "Mailer"
    And I should not see "Access denied"
    And I should not see "The website encountered an unexpected error"
