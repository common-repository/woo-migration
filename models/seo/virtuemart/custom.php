<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoVirtuemartCustom
{
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        return false;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        $catParentIds = $cart->duplicateFieldValueFromList($categoriesExt['object']['virtuemart_category_categories'], 'category_parent_id');
        $allCatParentIdsCon =  $cart->arrayToInCondition($catParentIds);
        $ext_query = array(
            'virtuemart_categories_fr_parent' => "SELECT * FROM _DBPRF_virtuemart_categories_fr_fr WHERE virtuemart_category_id IN {$allCatParentIdsCon}",
            'virtuemart_category_categories_parent' => "SELECT * FROM _DBPRF_virtuemart_category_categories WHERE category_child_id IN {$allCatParentIdsCon}",
        );
        return $ext_query;
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        $cat_slug = $cart->getRowValueFromListByField($categoriesExt['object']['virtuemart_categories_fr_fr'], 'virtuemart_category_id', $category['virtuemart_category_id'], 'slug');
        $seo_path_all = array($cat_slug);
        $parent = $cart->getRowFromListByField($categoriesExt['object']['virtuemart_category_categories'], 'category_child_id', $category['virtuemart_category_id']);
        if($parent || $parent['category_parent_id'] != 0){
            $parent_lv1_slug = $cart->getRowValueFromListByField($categoriesExt['object']['virtuemart_categories_fr_parent'], 'virtuemart_category_id', $parent['category_parent_id'], 'slug');
            if($parent_lv1_slug){
                $seo_path_all[] = $parent_lv1_slug;
            }
            $parent_lv2 = $cart->getRowFromListByField($categoriesExt['object']['virtuemart_category_categories_parent'], 'category_child_id', $parent['category_parent_id']);
            if($parent_lv2 && $parent_lv2['category_parent_id'] != 0){
                $tmp = $this->_getCategoriesParent($cart, $parent_lv2['category_parent_id'], array(), 1);
                $seo_path_all = array_merge($seo_path_all, $tmp);
            }
            $seo_path_all = array_reverse($seo_path_all);
            $path = implode('/', $seo_path_all);
        }else{
            $path = $cat_slug;
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
        return false;
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        $catIds = $cart->duplicateFieldValueFromList($productsExt['object']['virtuemart_product_categories'], 'virtuemart_category_id');
        $catIdsCon =  $cart->arrayToInCondition($catIds);
        $ext_query = array(
            'virtuemart_categories_fr' => "SELECT * FROM _DBPRF_virtuemart_categories_fr_fr WHERE virtuemart_category_id IN {$catIdsCon}",
            'virtuemart_category_categories' => "SELECT * FROM _DBPRF_virtuemart_category_categories WHERE category_child_id IN {$catIdsCon}",
        );
        return $ext_query;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $result = $allPath = array();
        $notice = $cart->getNotice();
        $pro_slug = $cart->getRowValueFromListByField($productsExt['object']['virtuemart_products_fr_fr'], 'virtuemart_product_id', $product['virtuemart_product_id'], 'slug');
        $catSrc = $cart->getListFromListByField($productsExt['object']['virtuemart_product_categories'], 'virtuemart_product_id', $product['virtuemart_product_id']);
        if($catSrc){
            foreach($catSrc as $cat_src){
                $cat_src_lv1 = $cart->getRowValueFromListByField($productsExt['object']['virtuemart_categories_fr'], 'virtuemart_category_id', $cat_src['virtuemart_category_id'], 'slug');
                $parent_cat = $cart->getRowFromListByField($productsExt['object']['virtuemart_category_categories'], 'category_child_id', $cat_src['virtuemart_category_id']);
                $seo_path_cat = array($cat_src_lv1);
                if($parent_cat && $parent_cat['category_parent_id'] != 0){
                    $tmp = $this->_getCategoriesParent($cart, $parent_cat['category_parent_id'], array(), 2);
                    $seo_path_cat = array_merge($seo_path_cat, $tmp);
                }
                $seo_path_cat = array_reverse($seo_path_cat);
                $path_cat = implode('/', $seo_path_cat);
                $allPath[] = $path_cat . '/' . $pro_slug . '.html';
            }
        }else{
            $allPath[] = $pro_slug . '.html';
        }
        foreach($allPath as $path){
            $result[] = array(
                'request_path' => $path
            );
        }
        if($result){
            foreach($result as $row){
                $cart->insertTable(LEUR_TABLE, array(
                    'link' => $row['request_path'],
                    'type' => 'product',
                    'type_id' => $product_id_desc
                ));
            }
        }
    }

    protected function _getCategoriesParent($cart, $cat_parent_id, $data, $count_to_stop){
        $query = array(
            'virtuemart_categories_fr' => "SELECT * FROM _DBPRF_virtuemart_categories_fr_fr WHERE virtuemart_category_id = $cat_parent_id",
            'virtuemart_category_categories' => "SELECT * FROM _DBPRF_virtuemart_category_categories WHERE category_child_id = $cat_parent_id",
        );
        $result = $cart->getConnectorData($cart->getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize($query),
        ));
        if(!$result || $result['result'] != 'success'){
            return array(
                'result' => 'error'
            );
        }
        $obj = $result['object']['virtuemart_categories_fr'][0];
        $data[] = $obj['slug'];
        $parent = $cart->getRowFromListByField($result['object']['virtuemart_category_categories'], 'category_child_id', $cat_parent_id);
        $count_to_stop--;
        if($parent && $parent['category_parent_id'] != 0 && $count_to_stop > 0){
            $data = $this->_getCategoriesParent($cart, $parent['category_parent_id'], $data, $count_to_stop);
        }
        return $data;
    }
}
