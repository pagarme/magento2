<?php
use Mundipagg\Core\Test\Functional\Features\Bootstrap\CoreFeature;

/**
 * Features context.
 */
class FeatureContext extends CoreFeature
{
    private $adminPanelUrl = '/admin';

    /**
     *@When I am on admin panel
     */
    public function clickInElement()
    {
        $this->visit($this->adminPanelUrl);
    }
}
