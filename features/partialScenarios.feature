Feature: Partial Scenarios

  #I call a partial scenario
  @javascript @smartStep
  Scenario: I call a partial scenario
    Given I am on "http://127.0.0.1/admin"
    Then I wait for text "Username" to appear, for 20 seconds