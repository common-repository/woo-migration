<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoOscommerceCustom
{
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        $result = false;
        $notice = $cart->getNotice();
        $catparentIds = $cart->duplicateFieldValueFromList($categories['object'], 'parent_id');
        $catIds = $cart->duplicateFieldValueFromList($categories['object'], 'categories_id');
        if($catparentIds || $catIds){
            $cat_parent_id_con = $cart->arrayToInCondition($catparentIds);
            $cat_id_con = $cart->arrayToInCondition($catIds);
            $result = array(
                'categories_1' => "SELECT * FROM _DBPRF_categories WHERE categories_id IN {$cat_parent_id_con}",
                'categoriesdescription' => "SELECT * FROM _DBPRF_categories_description WHERE categories_id IN {$cat_id_con}"
            );
        }
        return $result;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        $result = false;
        $notice = $cart->getNotice();
        return false;
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        $cat_desc = $cart->getRowFromListByField($categoriesExt['object']['categoriesdescription'], 'categories_id', $category['categories_id']);
        $path_body = $this->getPathSeo($cat_desc['categories_name']);
        $path = $path_body . '-c-' . $category['categories_id'] . '.html';
        if($category['parent_id']){
            $path = $path_body . '-c-' . $category['parent_id'] . '_' . $category['categories_id'] . '.html';
            $cat2 = $cart->getRowFromListByField($categoriesExt['object']['categories_1'], 'categories_id', $category['parent_id']);
            if($cat2['parent_id']){
                $path = $path_body . '-c-' . $cat2['parent_id'] . '_' . $category['parent_id'] . '_' . $category['categories_id'] . '.html';
            }
        }
        
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
        $proIds = $cart->duplicateFieldValueFromList($products['object'], 'products_id');
        if($proIds){
            $pro_id_con = $cart->arrayToInCondition($proIds);
            $result = array(
                'productsdescription' => "SELECT * FROM _DBPRF_products_description WHERE products_id IN {$pro_id_con}",
            );
        }
        return $result;
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        $result = false;
        $notice = $cart->getNotice();
        return $result;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $product_desc = $cart->getRowFromListByField($productsExt['object']['productsdescription'], 'products_id', $product['products_id']);
        $path_body = $this->getPathSeo($product_desc['products_name']);
        $path = $path_body . '-p-' . $product['products_id'] . '.html';
        $cart->insertTable(LEUR_TABLE, array(
            'link' => $path,
            'type' => 'product',
            'type_id' => $product_id_desc
        ));
    }
    
    protected function getPathSeo($name){
        $path = preg_replace('/[^A-Za-z ]/', '', strtolower($name));
        $path = str_replace(' new', '', $path);
        $path = str_replace('new ', '', $path);
        $path = preg_replace('/\s+/', ' ',$path);
        $path = str_replace(' ', '-', $path);
        return $path;
    }
}
