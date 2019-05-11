<?php


use Mundipagg\Core\Test\Functional\Features\Bootstrap\CoreFeature;


/**
 * Features context.
 */
class FeatureContext extends CoreFeature
{




    /**
     *
     * @When   /^(?:|I )wait for text "(?P<text>(?:[^"]|\\")*)" to appear, for (?P<wait>(?:\d+)*) seconds$/
     * @param  $text
     * @param  $wait
     * @throws \Exception
     */
    public function iWaitForTextToAppearForNSeconds($text, $wait)
    {
        $this->spin(
            function ($context) use ($text) {
                try {
                    $context->assertPageContainsText($text);
                    return true;
                }
                catch(ResponseTextException $e) {
                    // NOOP
                }
                return false;
            }, $wait
        );
    }

    /**
     *
     * @when /^(?:|I )follow the element "(?P<element>(?:[^"]|\\")*)" href$/
     */
    public function iFollowTheElementHref($element)
    {
        $session = $this->getSession();

        $locator = $this->fixStepArgument($element);
        $xpath = $session->getSelectorsHandler()->selectorToXpath('css', $locator);
        $element = $this->getSession()->getPage()->find('xpath', $xpath);
        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not find element'));
        }

        $href = $element->getAttribute('href');
        $this->visit($href);
    }


    /**
     *
     * @Given  /^I fill in "([^"]*)" with a random email$/
     * @param  $element
     * @throws \Exception
     */

    public function iFillInWithARandomEmail($field)
    {
        $field = $this->replacePlaceholdersByTokens($field);
        $field = $this->fixStepArgument($field);
        $value = rand(900000, 9999999) . "@test.com";
        $this->getSession()->getPage()->fillField($field, $value);
    }

    /**
     *
     * @Given  /^I fill in "([^"]*)" with the fixed email$/
     * @param  $element
     * @throws \Exception
     */

    public function iFillInWithTheFixedEmail($field)
    {

        $field = $this->replacePlaceholdersByTokens($field);
        $field = $this->fixStepArgument($field);
        $value = self::$featureHash . "@test.com";
        $this->getSession()->getPage()->fillField($field, $value);
    }



    /**
     *
     * @When   /^(?:|I )wait for text "(?P<text>(?:[^"]|\\")*)" to appear$/
     * @Then   /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" appear$/
     * @param  $text
     * @throws \Exception
     */
    public function iWaitForTextToAppear($text)
    {
        $this->spin(
            function (FeatureContext $context) use ($text) {
                try {
                    $context->assertPageContainsText($text);
                    return true;
                }
                catch(ResponseTextException $e) {
                    // NOOP
                }
                return false;
            }
        );
    }




    /**
     *
     * @Given /^document should open in new tab$/
     */
    public function documentShouldOpenInNewTab()
    {
        $session     = $this->getSession();
        $windowNames = $session->getWindowNames();
        if(sizeof($windowNames) < 2) {
            throw new \ErrorException("Expected to see at least 2 windows opened");
        }

        //You can even switch to that window
        $session->switchToWindow($windowNames[1]);
    }

    /**
     * Some forms do not have a Submit button just pass the ID
     *
     * @Given /^I submit the form with id "([^"]*)"$/
     */
    public function iSubmitTheFormWithId($arg)
    {
        $node = $this->getSession()->getPage()->find('css', $arg);
        if($node) {
            $this->getSession()->executeScript("jQuery('$arg').submit();");
        } else {
            throw new Exception('Element not found');
        }
    }

    /**
     *
     * @Given /^I use jquery to click on element "([^"]*)"$/
     */
    public function iUseJqueryToClickOnElement($arg)
    {
        $node = $this->getSession()->getPage()->find('css', $arg);
        if($node) {
            $this->getSession()->executeScript("jQuery('$arg').click();");
        } else {
            throw new Exception('Element not found');
        }
    }

    /**
     *
     * @Given /^a new session$/
     */
    public function newSession()
    {
        $this->getSession()->reset();
        //throw new Exception("as");
    }

    /**
     *
     * @Given /^I define failure screenshot dir as "([^"]*)"$/
     */
    public function setScreenshotDir($dir)
    {
        $this->screenshotDir = $dir;
    }

    /**
     *
     * @Given /^I save a screenshot to "([^"]*)" file$/
     */
    public function screenshot($filename)
    {
        $driver =  $this->getSession()->getDriver();
        $data = $driver->getScreenshot();
        $file = fopen($filename, "w");
        fwrite($file, $data);
        fclose($file);
    }
}
