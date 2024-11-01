<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoPrestashopDefault
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
        $notice = $cart->getNotice();
        $cat_desc = $cart->getListFromListByField($categoriesExt['object']['categories_lang'], 'id_category', $category['id_category']);
        $cat_def = $cart->getRowValueFromListByField($cat_desc, 'id_lang', $notice['config']['default_lang'], 'link_rewrite');
        if ($cat_def) {
            $path = $category['id_category'] . "-" . $cat_def;
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
        return false;
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        $categoryIds = $cart->duplicateFieldValueFromList($productsExt['object']['category_product'], 'id_category');
        $category_id_con = $cart->arrayToInCondition($categoryIds);
        $ext_rel_query = array(
            'category_lang' => "SELECT * FROM _DBPRF_category_lang WHERE id_category IN {$category_id_con}",
        );
        return $ext_rel_query;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $notice = $cart->getNotice();
        $pro_desc = $cart->getListFromListByField($productsExt['object']['product_lang'], 'id_product', $product['id_product']);
        $pro_def = $cart->getRowValueFromListByField($pro_desc, 'id_lang', $notice['config']['default_lang'], 'link_rewrite');
        if ($pro_def) {
            $path = $product['id_product'] . "-" . $pro_def . ".html";
            $cart->insertTable(LEUR_TABLE, array(
                'link' => $path,
                'type' => 'product',
                'type_id' => $product_id_desc
            ));
        }
        $proCat = $cart->getListFromListByField($productsExt['object']['category_product'], 'id_product', $product['id_product']);
        if ($proCat && $pro_def) {
            foreach ($proCat as $pro_cat) {
                $category = $cart->getListFromListByField($productsExt['object']['category_lang'], 'id_category', $pro_cat['id_category']);
                $cat_def = $cart->getRowValueFromListByField($category, 'id_lang', $notice['config']['default_lang'], 'link_rewrite');
                if ($cat_def) {
                        $path = $cat_def . "/" . $product['id_product'] . "-" . $pro_def . ".html";
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
