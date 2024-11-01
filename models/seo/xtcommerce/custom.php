<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoXtcommerceCustom
{
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        $categoryIds = $cart->duplicateFieldValueFromList($categories['object'], 'categories_id');
        $cat_id_con = $cart->arrayToInCondition($categoryIds, false);
        $ext_query = array(
            'bluegate_seo_url' => "SELECT * FROM _DBPRF_bluegate_seo_url WHERE categories_id IN " . $cat_id_con
        );
        return $ext_query;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        return false;
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        $bluegate_seo_url= $cart->getListFromListByField($categoriesExt['object']['bluegate_seo_url'], 'categories_id', $category['categories_id']);
        if ($bluegate_seo_url) {
            foreach ($bluegate_seo_url as $seo_url) {
                $path = $seo_url['url_text'];
                $cart->insertTable(LEUR_TABLE, array(
                    'link' => $path,
                    'type' => 'category',
                    'type_id' => $category_id_desc
                ));
            }
        }
    }

    public function prepareImportProducts($cart){
        return false;
    }

    public function getProductsExtQuery($cart, $products){
        $productIds = $cart->duplicateFieldValueFromList($products['object'], 'products_id');
        $pro_ids_query = $cart->arrayToInCondition($productIds, false);
        $ext_query = array(
            'bluegate_seo_url' => "SELECT * FROM _DBPRF_bluegate_seo_url WHERE products_id IN " . $pro_ids_query,
        );
        return $ext_query;
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        return false;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $bluegate_seo_url = $cart->getListFromListByField($productsExt['object']['bluegate_seo_url'], 'products_id', $product['products_id']);
        if ($bluegate_seo_url) {
            foreach ($bluegate_seo_url as $seo_url) {
                $path = $seo_url['url_text'] . '.html';
                $cart->insertTable(LEUR_TABLE, array(
                    'link' => 'products/' . $path,
                    'type' => 'product',
                    'type_id' => $product_id_desc
                ));
            }
        }
    }
}
