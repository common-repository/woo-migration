<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoOpencartDefault
{
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        $categoryIds = $cart->duplicateFieldValueFromList($categories['object'], 'category_id');
        $parentIds = $cart->duplicateFieldValueFromList($categories['object'], 'parent_id');
        $allIds = array_merge($categoryIds, $parentIds);
        $category_ids_query = $this->_arrayToInConditionCategory($allIds);
        $parent_ids_query = $cart->arrayToInCondition($parentIds);
        $ext_rel_query = array(
            "url_alias" => "SELECT * FROM _DBPRF_url_alias WHERE query IN {$category_ids_query}",
            "category_parent" => "SELECT * FROM _DBPRF_category WHERE category_id IN {$parent_ids_query}"
        );
        return $ext_rel_query;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        $categoryIds = $cart->duplicateFieldValueFromList($categoriesExt['object']['category_parent'], 'parent_id');
        $category_ids_query = $this->_arrayToInConditionCategory($categoryIds);
        $ext_rel_query = array(
            "url_alias_2" => "SELECT * FROM _DBPRF_url_alias WHERE query IN {$category_ids_query}"
        );
        return $ext_rel_query;
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        $cat_desc = $cart->getRowFromListByField($categoriesExt['object']['url_alias'], 'query', 'category_id='.$category['category_id']);
        if($cat_desc){
            $path = $cat_desc['keyword'];
            if ($category['parent_id']) {
                $p1_desc = $cart->getRowValueFromListByField($categoriesExt['object']['url_alias'], 'query', 'category_id='.$category['parent_id'], 'keyword');
                if ($p1_desc) {
                    $path = $p1_desc . '/' . $path;
                    $p2_id = $cart->getRowValueFromListByField($categoriesExt['object']['category_parent'], 'category_id', $category['parent_id'], 'parent_id');
                    if ($p2_id) {
                        $p2_desc = $cart->getRowValueFromListByField($categoriesExt['object']['url_alias_2'], 'query', 'category_id=' . $p2_id, 'keyword');
                        if ($p2_desc) {
                            $path = $p2_desc . '/' . $path;
                        }
                    }
                }
            }
            $cart->insertTable(LEUR_TABLE, array(
                'link' => $path,
                'type' => 'category',
                'type_id' => $category_id_desc
            ));
            $this->_catUrlSuccess($cart, $category['category_id'], 0, $path);
        }
    }

    public function prepareImportProducts($cart){
        return false;
    }

    public function getProductsExtQuery($cart, $products){
        $productIds = $cart->duplicateFieldValueFromList($products['object'], 'product_id');
        $product_ids_query = $this->_arrayToInConditionProduct($productIds);
        $ext_rel_query = array(
            "url_alias" => "SELECT * FROM _DBPRF_url_alias WHERE query IN {$product_ids_query}"
        );
        return $ext_rel_query;
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        return false;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $pro_desc = $cart->getRowFromListByField($productsExt['object']['url_alias'], 'query', 'product_id='.$product['product_id']);
        $notice = $cart->getNotice();
        if($pro_desc){
            $path = $pro_desc['keyword'];
            $cart->insertTable(LEUR_TABLE, array(
                'link' => $path,
                'type' => 'product',
                'type_id' => $product_id_desc
            ));
            $catParents = $cart->getListFromListByField($productsExt['object']['product_to_category'], 'product_id', $product['product_id']);
            foreach ($catParents as $cat) {
                $cart_url = $this->_getCatUrl($cart, $cat['category_id']);
                if ($cart_url) {
                    $path_cat = $cart_url . '/' . $path;
                    $cart->insertTable(LEUR_TABLE, array(
                        'link' => $path_cat,
                        'type' => 'product',
                        'type_id' => $product_id_desc
                    ));
                }
            }
        }
    }
    
    /**
     * Convert category's array to in condition in mysql query
     */
    protected function _arrayToInConditionCategory($array){
        if(empty($array)){
            return "('null')";
        }
        $result = "('category_id=".implode("','category_id=", $array)."')";
        return $result;
    }
    
    /**
     * Convert product's array to in condition in mysql query
     */
    protected function _arrayToInConditionProduct($array){
        if(empty($array)){
            return "('null')";
        }
        $result = "('product_id=".implode("','product_id=", $array)."')";
        return $result;
    }
    
    protected function _catUrlSuccess($cart, $id_src, $id_desc, $value = null) {
        return $cart->insertImport('cat_url', $id_src, $id_desc, 1, $value);
    }

    protected function _getCatUrl($cart, $id_src) {
        return $cart->getValueImport('cat_url', $id_src);
    }
}
