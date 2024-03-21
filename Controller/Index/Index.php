<?php
namespace Custom\MultiShipping\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Cart;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;

    class Index extends Action
    {
        protected $jsonFactory;
        protected $cart;
        protected $cartRepository;
        protected $quoteItemFactory;
        protected $productRepository;
        
        public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Cart $cart,
        CartRepositoryInterface $cartRepository,
        ItemFactory $quoteItemFactory,
        ProductRepositoryInterface $productRepository // Use the interface here
        ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->cart = $cart;
        $this->cartRepository = $cartRepository;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->productRepository = $productRepository;
        }
        
        public function execute()
        {
        $response = ['success' => false];
        $sku = $this->getRequest()->getParam('quote_item_id'); // Assuming you're passing the SKU as a parameter
        
        $qty = (int) $this->getRequest()->getParam('qty');
        try {
        
        $quote = $this->cart->getQuote();
        
        // Load the product by its SKU
        $product = $this->productRepository->get($sku);
        
        
        
        // Create a new quote item instance using the factory
        $quoteItem = $this->quoteItemFactory->create();
        $quoteItem->setProduct($product);
        $basePrice = $product->getPrice(); // Assuming this retrieves the base price
        $quoteItem->setPrice($basePrice);
        $quoteItem->setQty($qty);
        
        // Add the quote item to the quote
        $quote->addItem($quoteItem);
        
        // Collect totals and save the quote to update the cart
        $quote->collectTotals()->save();
        $this->cartRepository->save($quote);
        $quoteItemId = $quoteItem->getId();
        
        // Load the product associated with the quote item ID
        $quoteItem = $quote->getItemById($quoteItemId);
        $loadedProduct = $quoteItem->getProduct();
        
        // Load the newly created product data by its SKU
        $newProduct = $this->productRepository->get($sku);
        $shippingAddress = $quote->getShippingAddress();
        $response['success'] = true;
        $response['message'] = 'Product added to cart successfully.';
        $response['quote_item_id'] = $quoteItemId;
        $response['product'] = [
            'data' => $newProduct->getData(),
            // Add other product attributes you want to include
        ];
        $response['shipping_address'] = [
                'firstname' => $shippingAddress->getFirstname(),
                'lastname' => $shippingAddress->getLastname(),
                'street' => $shippingAddress->getStreet(),
                'city' => $shippingAddress->getCity(),
                'postcode' => $shippingAddress->getPostcode(),
                'country_id' => $shippingAddress->getCountryId(),
                // Add other address details you want to include
            ];
        
        } catch (\Exception $e) {
        $response['error'] = $e->getMessage();
        }
        
        $resultJson = $this->jsonFactory->create();
        return $resultJson->setData($response);
    }
}