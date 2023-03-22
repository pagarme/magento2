<?php
    namespace Pagarme\Pagarme\Helper;

    class MultiBuyerDataAssign {
        
        /**
         * @param $info
         * @param $additionalData
        */
        public function setCcMultiBuyer($info, $additionalData)
        {
            $info->setAdditionalInformation('cc_buyer_checkbox', $additionalData->getCcBuyerCheckbox());
            if ($additionalData->getCcBuyerCheckbox()) {
                $info->setAdditionalInformation('cc_buyer_name', $additionalData->getCcBuyerName());
                $info->setAdditionalInformation('cc_buyer_email', $additionalData->getCcBuyerEmail());
                $info->setAdditionalInformation('cc_buyer_document', $additionalData->getCcBuyerDocument());
                $info->setAdditionalInformation('cc_buyer_street_title', $additionalData->getCcBuyerStreetTitle());
                $info->setAdditionalInformation('cc_buyer_street_number', $additionalData->getCcBuyerStreetNumber());
                $info->setAdditionalInformation('cc_buyer_street_complement', $additionalData->getCcBuyerStreetComplement());
                $info->setAdditionalInformation('cc_buyer_zipcode', $additionalData->getCcBuyerZipcode());
                $info->setAdditionalInformation('cc_buyer_neighborhood', $additionalData->getCcBuyerNeighborhood());
                $info->setAdditionalInformation('cc_buyer_city', $additionalData->getCcBuyerCity());
                $info->setAdditionalInformation('cc_buyer_state', $additionalData->getCcBuyerState());
                $info->setAdditionalInformation('cc_buyer_home_phone', $additionalData->getCcBuyerHomePhone());
                $info->setAdditionalInformation('cc_buyer_mobile_phone', $additionalData->getCcBuyerMobilePhone());
            }
        }

        /**
         * @param $info
         * @param $additionalData
        */
        public function setBilletMultiBuyer($info, $additionalData)
        {
            $info->setAdditionalInformation('billet_buyer_checkbox', $additionalData->getBilletBuyerCheckbox());
            if ($additionalData->getBilletBuyerCheckbox()) {
                $info->setAdditionalInformation('billet_buyer_name', $additionalData->getBilletBuyerName());
                $info->setAdditionalInformation('billet_buyer_email', $additionalData->getBilletBuyerEmail());
                $info->setAdditionalInformation('billet_buyer_document', $additionalData->getBilletBuyerDocument());
                $info->setAdditionalInformation('billet_buyer_street_title', $additionalData->getBilletBuyerStreetTitle());
                $info->setAdditionalInformation('billet_buyer_street_number', $additionalData->getBilletBuyerStreetNumber());
                $info->setAdditionalInformation('billet_buyer_street_complement', $additionalData->getBilletBuyerStreetComplement());
                $info->setAdditionalInformation('billet_buyer_zipcode', $additionalData->getBilletBuyerZipcode());
                $info->setAdditionalInformation('billet_buyer_neighborhood', $additionalData->getBilletBuyerNeighborhood());
                $info->setAdditionalInformation('billet_buyer_city', $additionalData->getBilletBuyerCity());
                $info->setAdditionalInformation('billet_buyer_state', $additionalData->getBilletBuyerState());
                $info->setAdditionalInformation('billet_buyer_home_phone', $additionalData->getBilletBuyerHomePhone());
                $info->setAdditionalInformation('billet_buyer_mobile_phone', $additionalData->getBilletBuyerMobilePhone());
            }
        }


        /**
         * @param $info
         * @param $additionalData
         */
        public function setTwoCcMultiBuyer($info, $additionalData)
        {
            $info->setAdditionalInformation('cc_buyer_checkbox_first', $additionalData->getCcBuyerCheckboxFirst());
            if ($additionalData->getCcBuyerCheckboxFirst()) {
                $info->setAdditionalInformation('cc_buyer_name_first', $additionalData->getCcBuyerNameFirst());
                $info->setAdditionalInformation('cc_buyer_email_first', $additionalData->getCcBuyerEmailFirst());
                $info->setAdditionalInformation('cc_buyer_document_first', $additionalData->getCcBuyerDocumentFirst());
                $info->setAdditionalInformation('cc_buyer_street_title_first', $additionalData->getCcBuyerStreetTitleFirst());
                $info->setAdditionalInformation('cc_buyer_street_number_first', $additionalData->getCcBuyerStreetNumberFirst());
                $info->setAdditionalInformation('cc_buyer_street_complement_first', $additionalData->getCcBuyerStreetComplementFirst());
                $info->setAdditionalInformation('cc_buyer_zipcode_first', $additionalData->getCcBuyerZipcodeFirst());
                $info->setAdditionalInformation('cc_buyer_neighborhood_first', $additionalData->getCcBuyerNeighborhoodFirst());
                $info->setAdditionalInformation('cc_buyer_city_first', $additionalData->getCcBuyerCityFirst());
                $info->setAdditionalInformation('cc_buyer_state_first', $additionalData->getCcBuyerStateFirst());
                $info->setAdditionalInformation('cc_buyer_home_phone_first', $additionalData->getCcBuyerHomePhoneFirst());
                $info->setAdditionalInformation('cc_buyer_mobile_phone_first', $additionalData->getCcBuyerMobilePhoneFirst());
            }

            $info->setAdditionalInformation('cc_buyer_checkbox_second', $additionalData->getCcBuyerCheckboxSecond());
            if ($additionalData->getCcBuyerCheckboxSecond()) {
                $info->setAdditionalInformation('cc_buyer_name_second', $additionalData->getCcBuyerNameSecond());
                $info->setAdditionalInformation('cc_buyer_email_second', $additionalData->getCcBuyerEmailSecond());
                $info->setAdditionalInformation('cc_buyer_document_second', $additionalData->getCcBuyerDocumentSecond());
                $info->setAdditionalInformation('cc_buyer_street_title_second', $additionalData->getCcBuyerStreetTitleSecond());
                $info->setAdditionalInformation('cc_buyer_street_number_second', $additionalData->getCcBuyerStreetNumberSecond());
                $info->setAdditionalInformation('cc_buyer_street_complement_second', $additionalData->getCcBuyerStreetComplementSecond());
                $info->setAdditionalInformation('cc_buyer_zipcode_second', $additionalData->getCcBuyerZipcodeSecond());
                $info->setAdditionalInformation('cc_buyer_neighborhood_second', $additionalData->getCcBuyerNeighborhoodSecond());
                $info->setAdditionalInformation('cc_buyer_city_second', $additionalData->getCcBuyerCitySecond());
                $info->setAdditionalInformation('cc_buyer_state_second', $additionalData->getCcBuyerStateSecond());
                $info->setAdditionalInformation('cc_buyer_home_phone_second', $additionalData->getCcBuyerHomePhoneSecond());
                $info->setAdditionalInformation('cc_buyer_mobile_phone_second', $additionalData->getCcBuyerMobilePhoneSecond());
            }
        }

        /**
         * @param $info
         * @param $additionalData
         */
        public function setPixMultibuyer($info, $additionalData)
        {
            $info->setAdditionalInformation('pix_buyer_checkbox', $additionalData->getPixBuyerCheckbox());
            if ($additionalData->getPixBuyerCheckbox()) {
                $info->setAdditionalInformation('pix_buyer_name', $additionalData->getPixBuyerName());
                $info->setAdditionalInformation('pix_buyer_email', $additionalData->getPixBuyerEmail());
                $info->setAdditionalInformation('pix_buyer_document', $additionalData->getPixBuyerDocument());
                $info->setAdditionalInformation('pix_buyer_street_title', $additionalData->getPixBuyerStreetTitle());
                $info->setAdditionalInformation('pix_buyer_street_number', $additionalData->getPixBuyerStreetNumber());
                $info->setAdditionalInformation('pix_buyer_street_complement', $additionalData->getBilletBuyerStreetComplement());
                $info->setAdditionalInformation('pix_buyer_zipcode', $additionalData->getPixBuyerZipcode());
                $info->setAdditionalInformation('pix_buyer_neighborhood', $additionalData->getPixBuyerNeighborhood());
                $info->setAdditionalInformation('pix_buyer_city', $additionalData->getPixBuyerCity());
                $info->setAdditionalInformation('pix_buyer_state', $additionalData->getPixBuyerState());
                $info->setAdditionalInformation('pix_buyer_home_phone', $additionalData->getPixBuyerHomePhone());
                $info->setAdditionalInformation('pix_buyer_mobile_phone', $additionalData->getPixBuyerMobilePhone());
            }
        }
    }

