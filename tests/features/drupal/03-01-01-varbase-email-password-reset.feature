@varbase_email @frontend @email
Feature: Varbase Email - password reset email pipeline
  As a visitor who forgot their password
  I want to request a password reset
  So that an email is sent through the Varbase Email pipeline

  Scenario: Requesting a reset for a real account sends the email without errors
    Given I am an anonymous user
    When I am on "/user/password"
    And I fill in "Username or email address" with "webmaster"
    And I press "Submit"
    Then I should see "an email will be sent with instructions to reset your password"
    And I should not see "The website encountered an unexpected error"

  Scenario: The system does not reveal whether a non-existing email has an account
    Given I am an anonymous user
    When I am on "/user/password"
    And I fill in "Username or email address" with "not.existing.email@vardot.com"
    And I press "Submit"
    Then I should see "If not.existing.email@vardot.com is a valid account, an email will be sent with instructions to reset your password."
    And I should not see "The website encountered an unexpected error"
