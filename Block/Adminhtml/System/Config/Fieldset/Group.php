<?php
/**
 * Class Group
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Block\Adminhtml\System\Config\Fieldset;


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
