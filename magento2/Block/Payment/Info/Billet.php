<?php
/**
 * Class Billet
 *
 * @author      MundiPagg Embeddables Team <embeddables@mundipagg.com>
 * @copyright   2017 MundiPagg (http://www.mundipagg.com)
 * @license     http://www.mundipagg.com Copyright
 *
 * @link        http://www.mundipagg.com
 */

namespace MundiPagg\MundiPagg\Block\Payment\Info;


use Magento\Payment\Block\Info;
use Magento\Framework\DataObject;

class Billet extends Info
{
    const TEMPLATE = 'MundiPagg_MundiPagg::info/billet.phtml';

    public function _construct()
    {
        $this->setTemplate(self::TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = new DataObject([
            (string)__('Print Billet') => $this->getInfo()->getAdditionalInformation('billet_url')
        ]);

        $transport = parent::_prepareSpecificInformation($transport);
        return $transport;
    }

    public function getBilletUrl()
    {
        return $this->getInfo()->getAdditionalInformation('billet_url');
    }

    public function getTitle()
    {
        return $this->getInfo()->getAdditionalInformation('method_title');
    }
}