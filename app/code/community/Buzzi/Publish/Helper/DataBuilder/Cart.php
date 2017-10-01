<?php
/**
 * Copyright © Swarming Technology, LLC. All rights reserved.
 */

class Buzzi_Publish_Helper_DataBuilder_Cart
{
    /**
     * @var \Buzzi_Publish_Helper_DataBuilder_Product
     */
    protected $_dataBuilderProduct;

    /**
     * Initialize dependencies
     */
    public function __construct()
    {
        $this->_dataBuilderProduct = Mage::helper('buzzi_publish/dataBuilder_product');
    }

    /**
     * @return \Mage_Catalog_Model_Product
     */
    protected function _createProductModel()
    {
        return Mage::getModel('catalog/product');
    }

    /**
     * @param \Mage_Sales_Model_Quote $quote
     * @param Mage_Sales_Model_Order|null $order
     * @return array
     */
    public function getCartData($quote, $order = null)
    {
        $shippingAddress = $quote->getShippingAddress();
        $totals = $quote->getTotals();
        $payload = [
            'cart_id' => (string)$quote->getId(),
            'order_id' => $order ? (string)$order->getIncrementId() : '',
            'quantity' => (int)$quote->getItemsQty(),
            'order_promo' => $quote->getCouponCode() ? explode(',', (string)$quote->getCouponCode()) : [],
            'currency' => (string)$quote->getQuoteCurrencyCode(),
            'order_subtotal' => (string)$this->_getTotalValue($totals, 'subtotal'),
            'order_shipping' => (string)$this->_getTotalValue($totals, 'shipping'),
            'order_tax' => (string)$this->_getTotalValue($totals, 'tax'),
            'order_discount' => (string)$this->_getTotalValue($totals, 'discount'),
            'order_total' => (string)$this->_getTotalValue($totals, 'grand_total'),
            'shipping_method' => (string)$shippingAddress->getShippingMethod(),
            'shipping_carrier' => (string)$shippingAddress->getShippingDescription()
        ];

        $transport = new Varien_Object(['quote' => $quote, 'order' => $order, 'payload' => $payload]);
        Mage::dispatchEvent('buzzi_publish_cart_build_after', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }

    /**
     * @param array $totals
     * @param string $totalType
     * @return string
     */
    protected function _getTotalValue($totals, $totalType)
    {
        return !empty($totals[$totalType]) ? $totals[$totalType]->getValue() : '';
    }

    /**
     * @param \Mage_Sales_Model_Quote $quote
     * @return array
     */
    public function getCartItemsData($quote)
    {
        $payload = [];

        $items = $quote->getAllVisibleItems();
        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($items as $item) {
            $product = $this->_createProductModel();
            $product->load($item->getProduct()->getId());

            $itemPayload = $this->_dataBuilderProduct->getProductData($product);
            $itemPayload['base_price'] = (string)$item->getPrice();
            $itemPayload['product_sku'] = (string)$item->getSku();
            $itemPayload['quantity'] = (int)$item->getQty();

            $transport = new Varien_Object(['quote_item' => $item, 'product' => $product, 'payload' => $itemPayload]);
            Mage::dispatchEvent('buzzi_publish_cart_item_build_after', ['transport' => $transport]);

            $payload[] = (array)$transport->getData('payload');
        }

        $transport = new Varien_Object(['quote' => $quote, 'payload' => $payload]);
        Mage::dispatchEvent('buzzi_publish_cart_items_build_after', ['transport' => $transport]);

        return (array)$transport->getData('payload');
    }
}
