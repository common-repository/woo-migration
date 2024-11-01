<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();
class LeCaMgSeoXcartDefault{
    
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        $result = false;
        $notice = $cart->getNotice();
        $catIds = $cart->duplicateFieldValueFromList($categories['object'], 'categoryid');
        if($catIds){
            $cat_id_con = $cart->arrayToInCondition($catIds);
            $result = array(
                'clean_urls' => "SELECT * FROM _DBPRF_clean_urls WHERE resource_type = 'C' AND resource_id IN {$cat_id_con}",
            );
        }
        return $result;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        return false;
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        $cat_seo = $cart->getRowFromListByField($categoriesExt['object']['clean_urls'], 'resource_id', $category['categoryid']);
        $path = $cat_seo['clean_url'] . '.html';
        $cart->insertTable(LEUR_TABLE, array(
            'link' => $path,
            'type' => 'category',
            'type_id' => $category_id_desc
        ));
    }

    public function prepareImportProducts($cart){
        return false;
    }

    public function getProductsExtQuery($cart, $products){
        $result = false;
        $notice = $cart->getNotice();
        $proIds = $cart->duplicateFieldValueFromList($products['object'], 'productid');
        if($proIds){
            $pro_id_con = $cart->arrayToInCondition($proIds);
            $result = array(
                'clean_urls' => "SELECT * FROM _DBPRF_clean_urls WHERE resource_type = 'P' AND resource_id IN {$pro_id_con}",
            );
        }
        return $result;
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        return false;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $product_seo = $cart->getRowFromListByField($productsExt['object']['clean_urls'], 'resource_id', $product['productid']);
        $path = $product_seo['clean_url'] . '.html';
        $cart->insertTable(LEUR_TABLE, array(
            'link' => $path,
            'type' => 'product',
            'type_id' => $product_id_desc
        ));
    }


}
?>