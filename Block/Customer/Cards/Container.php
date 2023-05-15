<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Pagarme\Pagarme\Block\Customer\Cards;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Pagarme\Pagarme\Api\Data\SavedCardInterface;

/**
 * Class Container
 * @package Pagarme\Pagarme\Block\Customer\Cards
 */
class Container extends Template
{
    /**
     * @var SavedCardInterface
     */
    private $card;

    /**
     * Set Card
     *
     * @param SavedCardInterface $card
     * @return $this
     */
    public function setCard(SavedCardInterface $card): Container
    {
        $this->card = $card;
        return $this;
    }

    /**
     * Get card
     * @return SavedCardInterface
     */
    private function getCard(): SavedCardInterface
    {
        return $this->card;
    }

    /**
     * Here we set an order for children during retrieving their HTML
     * @param string $alias
     * @param bool $useCache
     * @return string
     * @throws LocalizedException
     * @since 100.1.1
     */
    public function getChildHtml($alias = '', $useCache = false): string
    {
        $layout = $this->getLayout();
        if ($layout) {
            $name = $this->getNameInLayout();
            foreach ($layout->getChildBlocks($name) as $child) {
                $child->setCard($this->getCard());
            }
        }
        return parent::getChildHtml($alias, $useCache);
    }
}
