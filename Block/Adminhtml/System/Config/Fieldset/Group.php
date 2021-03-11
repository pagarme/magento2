<?php
/**
 * Class Group
 *
 * @author      Open Source Team
 * @copyright   2021 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

namespace Pagarme\Pagarme\Block\Adminhtml\System\Config\Fieldset;


use Magento\Config\Block\System\Config\Form\Fieldset;

class Group extends Fieldset
{
    /**
     * {@inheritdoc}
     */
    protected function _getHeaderCommentHtml($element)
    {
        $groupConfig = $element->getGroup();

        if (empty($groupConfig['help_url']) || !$element->getComment()) {
            return parent::_getHeaderCommentHtml($element);
        }

        $html = '<div class="comment">' .
            $element->getComment() .
            ' <a target="_blank" href="' .
            $groupConfig['help_url'] .
            '">' .
            __(
                'Help'
            ) . '</a></div>';

        return $html;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isCollapseState($element)
    {
        $extra = $this->_authSession->getUser()->getExtra();

        if (isset($extra['configState'][$element->getId()])) {
            return $extra['configState'][$element->getId()];
        }

        $groupConfig = $element->getGroup();

        if (!empty($groupConfig['expanded'])) {
            return true;
        }

        return false;
    }
}
