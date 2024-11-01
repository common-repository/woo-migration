<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoShoppDefault
{
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        $result = false;
        $notice = $cart->getNotice();
        $parentIds = $cart->duplicateFieldValueFromList($categories['object'], 'parent');
        if($parentIds){
            $parent_id_con = $cart->arrayToInCondition($parentIds);
            $result = array(
                'terms' => "SELECT * FROM _DBPRF_terms WHERE term_id IN {$parent_id_con}",
            );
        }
        return $result;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        return array();
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        $path = $category['slug'];
        if($category['parent']){
            $parent_path = $cart->getRowFromListByField($categoriesExt['object']['terms'], 'term_id', $category['parent']);
            $path = $parent_path['slug'] . '/' . $path;
        }
        $cart->insertTable(LEUR_TABLE, array(
            'link' => 'full-product-line/' . $path,
            'type' => 'category',
            'type_id' => $category_id_desc
        ));
    }

    public function prepareImportProducts($cart){
        return false;
    }

    public function getProductsExtQuery($cart, $products){
        return array();
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        
        return array();
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $seo_path = $product['post_name'];
        $cart->insertTable(LEUR_TABLE, array(
            'link' => 'catalog/' . $seo_path,
            'type' => 'product',
            'type_id' => $product_id_desc
        ));
    }
    
}
