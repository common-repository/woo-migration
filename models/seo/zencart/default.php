<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoZencartDefault
{
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        $result = false;
        $parentIds = $cart->duplicateFieldValueFromList($categories['object'], 'parent_id');
        $parentIds = $this->_filterParentId($parentIds);
        if($parentIds){
            $parent_id_con = $cart->arrayToInCondition($parentIds);
            $result = array(
                'seo_categories' => "SELECT categories_id, parent_id FROM _DBPRF_categories WHERE categories_id IN {$parent_id_con}"
            );
        }
        return $result;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        $result = false;
        if(isset($categoriesExt['object']['seo_categories'])){
            $parentIds = $cart->duplicateFieldValueFromList($categoriesExt['object']['seo_categories'], 'parent_id');
            $parentIds = $this->_filterParentId($parentIds);
            if($parentIds){
                $parent_id_con = $cart->arrayToInCondition($parentIds);
                $result = array(
                    'seo_categories_2' => "SELECT categories_id, parent_id FROM _DBPRF_categories WHERE categories_id IN {$parent_id_con}"
                );
            }
        }
        return $result;
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        $result = false;
        $parent_cat_id_lv1 = $parent_cat_id_lv2 = 0;
        $parent_cat_data_all = array(
            'result' => 'success',
            'categories' => array(),
        );
        $parent_cat_path_all = array(
            'result' => 'success',
            'ip' => ''
        );
        $parent_cat_id = $category['parent_id'];
        $ip = $category['categories_id'];
        if($parent_cat_id){
            $ip = $parent_cat_id . "_" . $ip;
            $parent_cat_data = $cart->getRowFromListByField($categoriesExt['object']['seo_categories'], 'categories_id', $parent_cat_id);
            if($parent_cat_data){
                $parent_cat_id_lv1 = $parent_cat_data['parent_id'];
                if($parent_cat_id_lv1){
                    $ip = $parent_cat_id_lv1 . "_" . $ip;
                    $parent_cat_data_lv1 = $cart->getRowFromListByField($categoriesExt['object']['seo_categories_2'], 'categories_id', $parent_cat_id_lv1);
                    if($parent_cat_data_lv1){
                        $parent_cat_id_lv2 = $parent_cat_data_lv1['parent_id'];
                        if($parent_cat_id_lv2){
                            $ip = $parent_cat_id_lv2 . "_" . $ip;
                            $parent_cat_data_all = $this->_getCategoriesParent($cart, $parent_cat_id_lv2, $parent_cat_data_all);
                            if($parent_cat_data_all['result'] == 'success'){
                                $parent_cat_path_all = $this->_pathCategory($cart, $parent_cat_id_lv2, $parent_cat_data_all, $parent_cat_path_all);
                                if($parent_cat_path_all['result'] == 'success'){
                                    $ip = $parent_cat_path_all['ip'] . $ip;
                                }
                            }
                        }
                    }
                }
            }
        }
        $path = '?main_page=index&cPath=' . $ip;
        if($path){
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
        $catIds = $cart->duplicateFieldValueFromList($productsExt['object']['products_to_categories'], 'categories_id');
        $cat_id_con = $cart->arrayToInCondition($catIds);
        $result = array(
            'seo_categories' => "SELECT categories_id, parent_id FROM _DBPRF_categories WHERE categories_id IN {$cat_id_con}",
        );
        return $result;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $result = $data = array();
        $path = '?main_page=product_info&products_id=' . $product['products_id'];
        $data[] = $path;
        $proToCat = $cart->getListFromListByField($productsExt['object']['products_to_categories'], 'products_id', $product['products_id']);
        $catIds = $cart->duplicateFieldValueFromList($proToCat, 'categories_id');
        $cat_data_all = $this->_getCategoriesParent($cart, $catIds, array(
            'result' => 'success',
            'categories' => array()
        ));
        if($cat_data_all['result'] == 'success'){
            foreach($catIds as $cat_id){
                $ip = $cat_id;
                $seo_cat = $cart->getRowFromListByField($productsExt['object']['seo_categories'], 'categories_id', $cat_id);
                if($seo_cat){
                    $parent_cat_id = $seo_cat['parent_id'];
                    if($parent_cat_id){
                        $ip = $parent_cat_id . "_" . $ip;
                        $al = $this->_pathCategory($cart, $cat_id, $cat_data_all, array(
                            'result' => 'success',
                            'ip' => ''
                        ));
                        if($al['result'] == 'success'){
                            $ip = $al['ip'] . $cat_id;
                        }
                    }
                }
                $path = '?main_page=product_info&cPath=' . $ip . '&products_id=' . $product['products_id'];
                $data[] = $path;
            }
        }
        $data = array_unique($data);
        if($data){
            foreach($data as $path){
                $cart->insertTable(LEUR_TABLE, array(
                    'link' => $path,
                    'type' => 'product',
                    'type_id' => $product_id_desc
                ));
            }
        }
    }

    ############################################### Extend function ################################################

    protected function _filterParentId($catIds){
        if(!$catIds){
            return false;
        }
        $data = array();
        foreach($catIds as $cat_id){
            if($cat_id){
                $data[] = $cat_id;
            }
        }
        return $data;
    }

    protected function _getCategoriesParent($cart, $catIds, $data){
        if($data['result'] == 'error'){
            return $data;
        }
        if(!is_array($catIds)){
            $catIds = array($catIds);
        }
        $catIds = $this->_filterParentId($catIds);
        $catIds = array_unique($catIds);
        $cat_id_con = $cart->arrayToInCondition($catIds);
        $result = $cart->getConnectorData($cart->getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize(array(
                'categories' => "SELECT categories_id, parent_id FROM _DBPRF_categories WHERE categories_id IN {$cat_id_con}",
            )),
        ));
        if(!$result || $result['result'] != 'success'){
            return array(
                'result' => 'error'
            );
        }
        foreach($result['object']['categories'] as $row){
            $data['categories'][] = $row;
        }
        $parentCatIds = $cart->duplicateFieldValueFromList($result['object']['categories'], 'parent_id');
        $parentCatIds = $this->_filterParentId($parentCatIds);
        if($parentCatIds){
            $data = $this->_getCategoriesParent($cart, $parentCatIds, $data);
        }
        return $data;
    }

    protected function _pathCategory($cart, $cat_id, $data, $result){
        if($result['result'] == 'error'){
            return $result;
        }
        $parent_cat = $cart->getRowFromListByField($data['categories'], 'categories_id', $cat_id);
        if(!$parent_cat){
            return array(
                'result' => 'error'
            );
        }
        $parent_cat_id = $parent_cat['parent_id'];
        if($parent_cat_id == 0){
            return $result;
        }
        $result['ip'] = $parent_cat_id . "_" . $result['ip'];
        $result = $this->_pathCategory($cart, $parent_cat_id, $data, $result);
        return $result;
    }
}
