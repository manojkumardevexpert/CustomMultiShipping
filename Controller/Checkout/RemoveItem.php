<?php

namespace Custom\MultiShipping\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Checkout\Model\Session as CheckoutSession;

class RemoveItem extends Action
{
    protected $cartItemRepository;
    protected $jsonFactory;
    protected $logger;
    protected $cartRepository;
    protected $checkoutSession;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        LoggerInterface $logger,
        CartItemRepositoryInterface $cartItemRepository,
        CartRepositoryInterface $cartRepository,
        CheckoutSession $checkoutSession
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->cartItemRepository = $cartItemRepository;
        $this->cartRepository = $cartRepository;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Remove specific items from cart
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $response = ['success' => false];
        
        try {
            $quoteId = $this->checkoutSession->getQuote()->getId();
            $itemId = $this->getRequest()->getParam('quote_item_id');
            
            if ($quoteId && $itemId) {
                $this->cartItemRepository->deleteById($quoteId, $itemId);
                $response['success'] = true;
                $response['message'] = __('Item removed from cart.');
            } else {
                $response['error'] = __('Quote ID or Item ID is missing.');
            }
        } catch (NoSuchEntityException $e) {
            $response['error'] = __('The cart doesn\'t contain the item.');
        } catch (CouldNotSaveException $e) {
            $response['error'] = __('Failed to remove item from cart.');
        } catch (\Exception $e) {
            $response['error'] = __('An error occurred while processing your request.');
            $this->logger->error('Error removing item from cart: ' . $e->getMessage());
        }

        $resultJson = $this->jsonFactory->create();
        return $resultJson->setData($response);
    }
}
