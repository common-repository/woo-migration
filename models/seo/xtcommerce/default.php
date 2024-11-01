<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoXtcommerceDefault
{
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        $categoryIds = $cart->duplicateFieldValueFromList($categories['object'], 'categories_id');
        $categoryParentIds = $cart->duplicateFieldValueFromList($categories['object'], 'parent_id');
        $categoryAllIds = array_unique(array_merge($categoryIds, $categoryParentIds));
        $cat_id_con = $cart->arrayToInCondition($categoryAllIds, false);
        $ext_query = array(
            'categories_description' => "SELECT * FROM _DBPRF_categories_description WHERE categories_id IN " . $cat_id_con
        );
        return $ext_query;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        return false;
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        $notice = $cart->getNotice();
        $lang_def = $notice['config']['default_lang'];
        $cat_des = $cart->getListFromListByField($categoriesExt['object']['categories_description'], 'categories_id', $category['categories_id']);
        if ($cat_des) {
            $cate_name = $cart->getRowValueFromListByField($cat_des, 'language_id', $lang_def, 'categories_name');
            $path = $this->toUrl($cate_name);
            $id_path = $category['categories_id'];
            if($category['parent_id ']){
                $cat_parent_des = $cart->getListFromListByField($categoriesExt['object']['categories_description'], 'categories_id', $category['parent_id']);
                $cate_parent_name = $cart->getRowValueFromListByField($cat_parent_des, 'language_id', $lang_def, 'categories_name');
                $path = $this->toUrl($cate_parent_name) . '/' . $path;
                $id_path = $category['parent_id'] . '_' . $category['categories_id'];
            }
            $cart->insertTable(LEUR_TABLE, array(
                'link' => $path . ':::' . $id_path . '.html',
                'type' => 'category',
                'type_id' => $category_id_desc
            ));
        }
    }

    public function prepareImportProducts($cart){
        return false;
    }

    public function getProductsExtQuery($cart, $products){
        $productIds = $cart->duplicateFieldValueFromList($products['object'], 'products_id');
        $pro_ids_query = $cart->arrayToInCondition($productIds, false);
        $ext_query = array(
            'products_description' => "SELECT * FROM _DBPRF_products_description WHERE products_id IN " . $pro_ids_query,
            'products_to_categories' => "SELECT * FROM _DBPRF_products_to_categories WHERE products_id IN " . $pro_ids_query
        );
        return $ext_query;
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        $catIds = $cart->duplicateFieldValueFromList($productsExt['object']['products_to_categories'], 'categories_id');
        $cat_ids_query = $cart->arrayToInCondition($catIds, false);
        $ext_rel_query = array(
            'categories_description' => "SELECT * FROM _DBPRF_categories_description WHERE categories_id IN {$cat_ids_query}"
        );
        return $ext_rel_query;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $notice = $cart->getNotice();
        $lang_def = $notice['config']['default_lang'];
        $prod_des = $cart->getListFromListByField($productsExt['object']['products_description'], 'products_id', $product['products_id']);
        $prod_name = $cart->getRowValueFromListByField($prod_des, 'language_id', $lang_def, 'products_name');
        $path = $this->toUrl($prod_name);
        $cart->insertTable(LEUR_TABLE, array(
            'link' => $path . '::' . $product['products_id'] . '.html',
            'type' => 'product',
            'type_id' => $product_id_desc
        ));
        $prod_cats = $cart->getListFromListByField($productsExt['object']['products_to_categories'], 'products_id', $product['products_id']);
        if ($prod_cats) {
            foreach ($prod_cats as $row) {
                $cat_des = $cart->getListFromListByField($productsExt['object']['categories_description'], 'categories_id', $row['categories_id']);
                $cat_name = $cart->getRowValueFromListByField($cat_des, 'language_id', $lang_def, 'categories_name');
                $path = $this->toUrl($cat_name) . '/' . $path;
                $cart->insertTable(LEUR_TABLE, array(
                    'link' => $path . '::' . $product['products_id'] . '.html',
                    'type' => 'product',
                    'type_id' => $product_id_desc
                ));
            }
        }
    }
    
    public function toUrl($name){
        $path = str_replace(' / ', '', $name);
        $path = str_replace('ü', 'ue', $path);
        $path = preg_replace('/[^A-Za-z0-9 -]/', '', $path);
        $path = preg_replace('/\s+/', ' ',$path);
        $path = str_replace(' ', '-', $path);
        $path = str_replace('---', '-', $path);
        return $path;
    }
}
