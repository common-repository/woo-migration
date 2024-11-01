<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoCscartDefault
{
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        $result = false;
        $notice = $cart->getNotice();
        $catIds = $cart->duplicateFieldValueFromList($categories['object'], 'category_id');
        if($catIds){
            $cat_id_con = $cart->arrayToInCondition($catIds);
            $result = array(
                'seo_names' => "SELECT * FROM _DBPRF_seo_names WHERE type = 'c' AND lang_code = '{$notice['config']['default_lang']}' AND object_id IN {$cat_id_con}",
                'seo_redirects' => "SELECT * FROM _DBPRF_seo_redirects WHERE type = 'c' AND lang_code = '{$notice['config']['default_lang']}' AND object_id IN {$cat_id_con}"
            );
        }
        return $result;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        $result = false;
        $notice = $cart->getNotice();
        $parentIds = $cart->duplicateFieldValueFromList($categoriesExt['object']['seo_names'], 'path');
        $parentIds = $this->_splitParentId($parentIds);
        if($parentIds){
            $parent_id_con = $cart->arrayToInCondition($parentIds);
            $result = array(
                'seo_names_2' => "SELECT * FROM _DBPRF_seo_names WHERE type = 'c' AND lang_code = '{$notice['config']['default_lang']}' AND object_id IN {$parent_id_con}"
            );
        }
        return $result;
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        $cat_seo = $cart->getRowFromListByField($categoriesExt['object']['seo_names'], 'object_id', $category['category_id']);
        $path = $cat_seo['name'];
        if($cat_seo['path']) {
            $parent_path = explode('/', $cat_seo['path']);
            $parent_path = array_reverse($parent_path);
            foreach ($parent_path as $parent) {
                $parent_seo = $cart->getRowFromListByField($categoriesExt['object']['seo_names_2'], 'object_id', $parent);
                $path = $parent_seo['name'] . '/' . $path;
            }
        }
        $cart->insertTable(LEUR_TABLE, array(
            'link' => $path,
            'type' => 'category',
            'type_id' => $category_id_desc
        ));
        $path_redirect = $cart->getListFromListByField($categoriesExt['object']['seo_redirects'], 'object_id', $category['category_id']);
        if ($path_redirect) {
            foreach ($path_redirect as $row) {
                $seo_path = ltrim($row['src'], '/');
                $cart->insertTable(LEUR_TABLE, array(
                    'link' => $seo_path,
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
        $result = false;
        $notice = $cart->getNotice();
        $proIds = $cart->duplicateFieldValueFromList($products['object'], 'product_id');
        if($proIds){
            $pro_id_con = $cart->arrayToInCondition($proIds);
            $result = array(
                'seo_names' => "SELECT * FROM _DBPRF_seo_names WHERE type = 'p' AND lang_code = '{$notice['config']['default_lang']}' AND object_id IN {$pro_id_con}",
                'seo_redirects' => "SELECT * FROM _DBPRF_seo_redirects WHERE type = 'p' AND lang_code = '{$notice['config']['default_lang']}' AND object_id IN {$pro_id_con}"
            );
        }
        return $result;
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        $result = false;
        $notice = $cart->getNotice();
        $catIds = $cart->duplicateFieldValueFromList($productsExt['object']['seo_names'], 'path');
        $catIds = $this->_splitParentId($catIds);
        if($catIds){
            $cat_id_con = $cart->arrayToInCondition($catIds);
            $result = array(
                'seo_names_2' => "SELECT * FROM _DBPRF_seo_names WHERE type = 'c' AND lang_code = '{$notice['config']['default_lang']}' AND object_id IN {$cat_id_con}"
            );
        }
        return $result;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $product_seo = $cart->getRowFromListByField($productsExt['object']['seo_names'], 'object_id', $product['product_id']);
        $path = $product_seo['name'];
        if($product_seo['path']) {
            $parent_path = explode('/', $product_seo['path']);
            $parent_path = array_reverse($parent_path);
            foreach ($parent_path as $parent) {
                $parent_seo = $cart->getRowFromListByField($productsExt['object']['seo_names_2'], 'object_id', $parent);
                $path = $parent_seo['name'] . '/' . $path;
            }
        }
        $cart->insertTable(LEUR_TABLE, array(
            'link' => $path,
            'type' => 'product',
            'type_id' => $product_id_desc
        ));
        $path_redirect = $cart->getListFromListByField($productsExt['object']['seo_redirects'], 'object_id', $product['product_id']);
        if ($path_redirect) {
            foreach ($path_redirect as $row) {
                $seo_path = ltrim($row['src'], '/');
                $cart->insertTable(LEUR_TABLE, array(
                    'link' => $seo_path,
                    'type' => 'product',
                    'type_id' => $product_id_desc
                ));
            }
        }
    }
    
    protected function _splitParentId($catIds){
        if(!$catIds){
            return false;
        }
        $data = array();
        foreach($catIds as $cat_id){
            $parents = explode('/', $cat_id);
            foreach ($parents as $value) {
                $data[] = $value;
            }
        }
        $data = array_unique($data);
        return $data;
    }
}
