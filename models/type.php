<?php

/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgType
{
    public function show(){
        $carts = array(
            'hikashop' => "HikaShop",
            'shopp' => "Shopp",
            'oscommerce' => 'osCommerce',
            'zencart' => 'ZenCart',
            'virtuemart' => 'VirtueMart',
            'woocommerce' => 'WooCommerce',
            'xtcommerce' => 'xt:Commerce/Veyton',
            'opencart' => 'OpenCart',
            'xcart' => 'X-Cart',
            'loaded' => 'CreLoaded/Loaded',
            'wpecommerce' => "Wp eCommerce",
            'cscart' => "CS-Cart",
            'prestashop' => "Prestashop",
            'magento' => "Magento",
            'cart66' => "Cart66",
            'marketpress' => "MarketPress",
            'oxideshop' => 'Oxid eShop',
            'wpestore' => 'WP-eStore',
            'jigoshop' => 'Jigoshop',
            'phpurchase' => 'Phpurchase',
            'cubecart' => 'Cube Cart',
            'pinnaclecart' => 'PinnacleCart',
            'interspire' => 'Interspire',
            'wponlinestore' => 'WP Online Sore',
            'drupalcommerce'  => 'Drupal Commerce'
//            'volusion' => "Volusion",
//            'bigcommerce' => "BigCommerce",
//            'shopify' => "Shopify"
        );
        ksort($carts);
        return $carts;
    }
}