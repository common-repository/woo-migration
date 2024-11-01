<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoPrestashopCustom
{
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        $result = array(
            'lang' => "SELECT * FROM _DBPRF_lang",
        );
        return $result;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        return false;
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        $notice = $cart->getNotice();
        foreach ($categoriesExt['object']['lang'] as $cat_lang) {
            $lang[$cat_lang['id_lang']] = $cat_lang['iso_code'];
        }
        $cat_desc = $cart->getListFromListByField($categoriesExt['object']['categories_lang'], 'id_category', $category['id_category']);
        if ($cat_desc) {
            foreach ($cat_desc as $row) {
                $path = $category['id_category'] . "-" .$row['link_rewrite'];
                if (isset($lang[$row['id_lang']]) && $row['id_lang'] != $notice['config']['default_lang']) {
                    $path = $lang[$row['id_lang']] . '/' . $path;
                }
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
        return false;
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        $categoryIds = $cart->duplicateFieldValueFromList($productsExt['object']['category_product'], 'id_category');
        $category_id_con = $cart->arrayToInCondition($categoryIds);
        $ext_rel_query = array(
            'category_lang' => "SELECT * FROM _DBPRF_category_lang WHERE id_category IN {$category_id_con}",
            'lang' => "SELECT * FROM _DBPRF_lang",
        );
        return $ext_rel_query;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $notice = $cart->getNotice();
        foreach ($productsExt['object']['lang'] as $cat_lang) {
            $lang[$cat_lang['id_lang']] = $cat_lang['iso_code'];
        }
        $pro_desc = $cart->getListFromListByField($productsExt['object']['product_lang'], 'id_product', $product['id_product']);
        if ($pro_desc) {
            foreach ($pro_desc as $row) {
                $path = $product['id_product'] . "-" . $row['link_rewrite'].".html";
                if (isset($lang[$row['id_lang']]) && $row['id_lang'] != $notice['config']['default_lang']) {
                    $path = $lang[$row['id_lang']] . '/' . $path;
                }
                $cart->insertTable(LEUR_TABLE, array(
                    'link' => $path,
                    'type' => 'product',
                    'type_id' => $product_id_desc
                ));
            }
        }
        $proCat = $cart->getListFromListByField($productsExt['object']['category_product'], 'id_product', $product['id_product']);
        if ($proCat) {
            foreach ($proCat as $pro_cat) {
                $category = $cart->getListFromListByField($productsExt['object']['category_lang'], 'id_category', $pro_cat['id_category']);
                if ($category) {
                    foreach ($notice['config']['languages'] as $lang_id => $store_id) {
                        $link_lang_cat = $cart->getRowValueFromListByField($category, 'id_lang', $lang_id, 'link_rewrite');
                        $link_lang_pro = $cart->getRowValueFromListByField($pro_desc, 'id_lang', $lang_id, 'link_rewrite');
                        $path = $link_lang_cat . "/" . $product['id_product'] . "-" . $link_lang_pro . ".html";
                        if (isset($lang[$lang_id]) && $lang_id != $notice['config']['default_lang']) {
                            $path = $lang[$lang_id] . '/' . $path;
                        }
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
