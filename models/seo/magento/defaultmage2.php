<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoMagentoDefaultmage2
{
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        $categoryIds = $cart->duplicateFieldValueFromList($categories['object'], 'entity_id');
        $cat_id_con = $cart->arrayToInCondition($categoryIds);
        $notice = $cart->getNotice();
        $default_store_src = $notice['config']['default_lang'];
        $ext_query = array(
            'url_rewrite' => "SELECT * FROM _DBPRF_url_rewrite WHERE entity_type = 'category' AND store_id = {$default_store_src} AND entity_id IN {$cat_id_con}"
        );
        return $ext_query;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        return false;
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
		global $wpdb;
		$table_name = $wpdb->prefix . LEUR_TABLE;
        $cat_urls = $cart->getListFromListByField($categoriesExt['object']['url_rewrite'], 'entity_id', $category['entity_id']);
        if ($cat_urls) {
            foreach ($cat_urls as $row) {
                $path = $row['request_path'];
                $sql = "INSERT INTO `" . $table_name . "` (link, type, type_id) VALUES ('" . $path . "', 'category', '" . (int)$category_id_desc . "')";
                try {
                    $wpdb->query($sql);
                } catch (Exception $ex) {
                    
                }
            }
        }
    }

    public function prepareImportProducts($cart){
        return false;
    }

    public function getProductsExtQuery($cart, $products){
        $productIds = $cart->duplicateFieldValueFromList($products['object'], 'entity_id');
        $pro_id_query = $cart->arrayToInCondition($productIds);
        $notice = $cart->getNotice();
        $default_store_src = $notice['config']['default_lang'];
        $ext_query = array(
            'url_rewrite' => "SELECT * FROM _DBPRF_url_rewrite WHERE entity_type = 'product' AND  store_id = {$default_store_src} AND entity_id IN {$pro_id_query}"
        );
        return $ext_query;
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        return false;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $pro_urls = $cart->getListFromListByField($productsExt['object']['url_rewrite'], 'entity_id', $product['entity_id']);
        if ($pro_urls) {
            foreach ($pro_urls as $row) {
                $path = $row['request_path'];
                $cart->insertTable(LEUR_TABLE, array(
                    'link' => $path,
                    'type' => 'product',
                    'type_id' => $product_id_desc
                ));
            }
        }
    }
}
