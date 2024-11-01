<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoVirtuemartDefault
{
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        $ext_query = array(
            'virtuemart_categories_en_gb' => "SELECT * FROM _DBPRF_virtuemart_categories_en_gb"
        );
        return $ext_query;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        return false;
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        $notice = $cart->getNotice();
        $slug = $cart->getRowValueFromListByField($categoriesExt['object']['virtuemart_categories_en_gb'], 'virtuemart_category_id', $category['virtuemart_category_id'], 'slug');
        $pathArr = array();
        if ($slug) {
            $pathArr[] = 'products/'. $slug . '.html';
            $cat_parent_id = $cart->getRowValueFromListByField($categoriesExt['object']['virtuemart_category_categories'], 'category_child_id', $category['virtuemart_category_id'], 'category_parent_id');
            if($cat_parent_id){
                $slug_parent = $cart->getRowValueFromListByField($categoriesExt['object']['virtuemart_categories_en_gb'], 'virtuemart_category_id', $cat_parent_id, 'slug');
                $pathArr[] = 'products/' . $slug_parent . '/' . $slug . '.html';
            }
        }
        foreach ($pathArr as $path){
            $cart->insertTable(LEUR_TABLE, array(
                'link' => $path,
                'type' => 'category',
                'type_id' => $category_id_desc
            ));
        }
    }

    public function prepareImportProducts($cart){
        return false;
    }

    public function getProductsExtQuery($cart, $products){
        $virtuemartProductIds = $cart->duplicateFieldValueFromList($products['object'], 'virtuemart_product_id');
        $prod_id_query = $cart->arrayToInCondition($virtuemartProductIds);
        $query = array(
            'virtuemart_products_en_gb' => "SELECT * FROM _DBPRF_virtuemart_products_en_gb WHERE virtuemart_product_id IN {$prod_id_query}",
            'virtuemart_product_categories' => "SELECT * FROM _DBPRF-virtuemart_product_categories WHERE virtuemart_product_id IN {$prod_id_query}"
        );
        return $query;
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        $categoryIds = $cart->duplicateFieldValueFromList($productsExt['object']['virtuemart_product_categories'], 'virtuemart_category_id');
        $category_id_con = $cart->arrayToInCondition($categoryIds);
        $ext_rel_query = array(
            'virtuemart_categories_en_gb' => "SELECT * FROM _DBPRF_virtuemart_categories_en_gb",
            'virtuemart_category_categories' => "SELECT * FROM _DBPRF_virtuemart_category_categories WHERE category_child_id IN {$category_id_con}",
        );
        return $ext_rel_query;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $notice = $cart->getNotice();
        $slug = $cart->getRowValueFromListByField($productsExt['object']['virtuemart_products_en_gb'], 'virtuemart_product_id', $product['virtuemart_product_id'], 'slug');
        if ($slug) {
            $path = 'products/' . $slug . ".html";
            $cart->insertTable(LEUR_TABLE, array(
                'link' => $path,
                'type' => 'product',
                'type_id' => $product_id_desc
            ));
        }
        $proCat = $cart->getListFromListByField($productsExt['object']['virtuemart_product_categories'], 'virtuemart_product_id', $product['virtuemart_product_id']);
        if ($proCat) {
            foreach ($proCat as $pro_cat) {
                $cat_slug1 = $cart->getRowValueFromListByField($productsExt['object']['virtuemart_categories_en_gb'], 'virtuemart_category_id', $pro_cat['virtuemart_category_id'], 'slug');
                if ($cat_slug1) {
                    $path = 'products/' . $cat_slug1 . "/" . $slug . ".html";
                    $cart->insertTable(LEUR_TABLE, array(
                        'link' => $path,
                        'type' => 'product',
                        'type_id' => $product_id_desc
                    ));
                }
                $cat_id_parent2 = $cart->getRowValueFromListByField($productsExt['object']['virtuemart_category_categories'], 'category_child_id', $pro_cat['virtuemart_category_id'], 'category_parent_id');
                if($cat_id_parent2){
                    $cat_slug2 = $cart->getRowValueFromListByField($productsExt['object']['virtuemart_categories_en_gb'], 'virtuemart_category_id', $cat_id_parent2, 'slug');
                    if($cat_slug2){
                        $path = 'products/' . $cat_slug2 . '/' . $cat_slug1 . '/' . $slug . '.html';
                        $cart->insertTable(LEUR_TABLE, array(
                            'link' => $path,
                            'type' => 'product',
                            'type_id' => $product_id_desc
                        ));
                    }
                }
            }
        }
    }
}
