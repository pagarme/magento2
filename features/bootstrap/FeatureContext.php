<?php


use Mundipagg\Core\Test\Functional\Features\Bootstrap\CoreFeature;


/**
 * Features context.
 */
class FeatureContext extends CoreFeature
{


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
