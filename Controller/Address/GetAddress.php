<?php

namespace Custom\MultiShipping\Controller\Address;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;

class GetAddress extends Action
{
    protected $jsonFactory;
    protected $customerSession;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        $response = ['success' => false];
        if ($this->customerSession->isLoggedIn()) {
            // Get addresses from the customer session or any other source
            $addresses = $this->customerSession->getCustomer()->getAddresses();
            $addressOptions = [];
            foreach ($addresses as $address) {
                $addressOptions[] = [
                    'value' => $address->getId(),
                    'label' => $address->format('oneline')
                ];
            }
            $response['success'] = true;
            $response['addresses'] = $addressOptions;
        } else {
            $response['error'] = 'User is not logged in.';
        }

        $resultJson = $this->jsonFactory->create();
        return $resultJson->setData($response);
    }
}
