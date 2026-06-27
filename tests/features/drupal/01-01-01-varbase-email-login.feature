@varbase_email @login
Feature: Varbase Email - login page
  Scenario: The login page loads and offers a password reset link
    Given I am an anonymous user
    When I am on "/user/login"
    Then "#user-login-form" should be visible
    And I should see "Log in"
    And I should see "Reset your password"
    And I should not see "Page not found"
    And I should not see "The website encountered an unexpected error"
