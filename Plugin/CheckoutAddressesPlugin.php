<?php

namespace Custom\MultiShipping\Plugin;

class CheckoutAddressesPlugin
{
    public function afterGetItems(\Magento\Multishipping\Block\Checkout\Addresses $subject, $result)
    {
        $consolidatedItems = [];

        foreach ($result as $item) {
            $productId = $item->getProductId();
            $addressId = $item->getCustomerAddressId();
            $key = $productId . '_' . $addressId;

            if (isset($consolidatedItems[$key])) {
                $existingItem = $consolidatedItems[$key];
                $newQty = $existingItem->getQty() + $item->getQty();
                $existingItem->setQty($newQty);
            } else {
                // If the product and customer address combination is new, add the item
                $consolidatedItems[$key] = clone $item;
            }
        }

        return array_values($consolidatedItems);
    }
}