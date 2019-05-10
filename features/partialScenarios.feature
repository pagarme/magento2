Feature: Partial Scenarios

  #Add sunglasses to card
  @javascript @smartStep
  Scenario: I call a partial scenario
    Given I wait for text "Google" to appear, for 20 seconds
    ##@todo Implement partial scenarios