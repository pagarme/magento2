Feature: Partial Scenarios

  #I add a product to shopping cart
  @javascript @smartStep
  Scenario: I add a product to shopping cart
    #Passos aqui
    Given I am on "/index.php/fusion-backpack.html"
    And I use jquery to click on element "#product-addtocart-button"
    Then I wait for text "added" to appear, for 20 seconds

  #I go to checkout
  #@javascript @smartStep
  #Scenario: I go to checkout
    #Given I am on "/index.php/checkout"
    #Then I wait for text "email" to appear, for 20 seconds





