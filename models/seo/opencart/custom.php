<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoOpencartCustom
{
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        return false;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        return false;
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        $path = $category['category_id'];
        if ($category['parent_id']) {
            $path = $category['parent_id'] . "_" . $path;
        }
        $path = "index.php?route=product/category&path=" . $path;
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
        $productIds = $cart->duplicateFieldValueFromList($products['object'], 'product_id');
        $product_id = $cart->arrayToInCondition($productIds);
        $ext_query = array(
            "product_parent_cat" => "SELECT pc.*, c.parent_id FROM _DBPRF_product_to_category as pc LEFT JOIN _DBPRF_category as c ON pc.category_id = c.category_id WHERE pc.product_id IN {$product_id}"
        );
        return $ext_query;
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        return false;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $product_id = $product['product_id'];
        $cat_desc = $cart->getListFromListByField($productsExt['object']['product_parent_cat'], 'product_id', $product['product_id']);
        if ($cat_desc) {
            foreach ($cat_desc as $row) {
                $path = $row['category_id'];
                if ($row['parent_id']) {
                    $path = $row['parent_id'] . '_' . $path;
                }
                $path = "index.php?route=product/product&path=" . $path . "&product_id=" . $product_id;
                $cart->insertTable(LEUR_TABLE, array(
                    'link' => $path,
                    'type' => 'product',
                    'type_id' => $product_id_desc
                ));
            }
        }
        $cart->insertTable(LEUR_TABLE, array(
            'link' => "index.php?route=product/product&product_id=" . $product_id,
            'type' => 'product',
            'type_id' => $product_id_desc
        ));
    }
}
