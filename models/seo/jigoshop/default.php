<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoJigoshopDefault
{
    const SEO_CAT_PARENT_PATH = false;

    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        if(self::SEO_CAT_PARENT_PATH){
            $catParentIds = $cart->duplicateFieldValueFromList($categories['object'], 'parent');
            $cat_parent_ids_con = $cart->arrayToInCondition($catParentIds);
            $ext_query = array(
                'seo_categories' => "SELECT * FROM _DBPRF_term_taxonomy as tx LEFT JOIN _DBPRF_terms AS t ON t.term_id = tx.term_id WHERE tx.taxonomy = 'product_cat' AND tx.term_id IN {$cat_parent_ids_con}"
            );
            return $ext_query;
        }
        return false;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        if(self::SEO_CAT_PARENT_PATH) {
            $catParentIds = $cart->duplicateFieldValueFromList($categoriesExt['object']['seo_categories'], 'parent');
            $cat_parent_ids_con = $cart->arrayToInCondition($catParentIds);
            $ext_rel_query = array(
                'seo_categories_2' => "SELECT * FROM _DBPRF_term_taxonomy as tx LEFT JOIN _DBPRF_terms AS t ON t.term_id = tx.term_id WHERE tx.taxonomy = 'product_cat' AND tx.term_id IN {$cat_parent_ids_con}"
            );
            return $ext_rel_query;
        }
        return false;
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        global $wpdb;
        $table_name = $wpdb->prefix . LEUR_TABLE;
        if(self::SEO_CAT_PARENT_PATH){
            $seo_path_all = array();
            if($category['parent'] > 0){
                $seo_cat = $cart->getRowFromListByField($categoriesExt['object']['seo_categories'], 'term_id', $category['parent']);
                if($seo_cat){
                    $seo_path_all[] = $seo_cat['slug'];
                }
                if($seo_cat['parent'] > 0){
                    $seo_cat_second = $cart->getRowFromListByField($categoriesExt['object']['seo_categories_2'], 'term_id', $seo_cat['parent']);
                    if($seo_cat_second){
                        $seo_path_all[] = $seo_cat_second['slug'];
                    }
                    if($seo_cat_second['parent'] > 0){
                        $tmp = $this->_getCategoriesParent($cart, $seo_cat_second['parent'], array());
                        $seo_path_all = array_merge($seo_path_all, $tmp);
                    }
                }
            }
            $seo_path_all = array_reverse($seo_path_all);
            $seo_path_all = implode('/', $seo_path_all);
            if($seo_path_all){
                $seo_path_all = $seo_path_all . '/';
            }
            $path = "shop/product-category/" . $seo_path_all . $category['slug'];
        }else{
            $path = "shop/product-category/" . $category['slug'];
        }
        $sql = "INSERT INTO `" . $table_name . "` (link, type, type_id) VALUES ('" . $path . "', 'category', '" . (int)$category_id_desc . "')";
        try {
            $wpdb->query($sql);
        } catch (Exception $ex) {

        }
    }

    public function prepareImportProducts($cart){
        return false;
    }

    public function getProductsExtQuery($cart, $products){
        return false;
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        return false;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $pro_url = array();
        $proTermRelationship = $cart->getListFromListByField($productsExt['object']['product_term_relationship'], 'object_id', $product['ID']);
        $proCat = $cart->getListFromListByField($proTermRelationship, 'taxonomy', 'wpsc_product_category');
        if ($proCat) {
            foreach ($proCat as $pro_cat) {
                $pro_url[] = "shop/product/" . $pro_cat['slug'] . '/' . $product['post_name'];
            }
        }else{
            $pro_url[] = "shop/product/" . $product['post_name'];
        }
        if($pro_url){
            foreach($pro_url as $path){
                $cart->insertTable(LEUR_TABLE, array(
                    'link' => $path,
                    'type' => 'product',
                    'type_id' => $product_id_desc
                ));
            }
        }
    }

    protected function _getCategoriesParent($cart, $cat_parent_id, $data){
        $query = array(
            'categories' => "SELECT * FROM _DBPRF_term_taxonomy as tx
                      LEFT JOIN _DBPRF_terms AS t ON t.term_id = tx.term_id
                      WHERE tx.taxonomy = 'wpsc_product_category' AND tx.term_id = {$cat_parent_id}",
        );
        $result = $cart->getConnectorData($cart->getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize($query)
        ));
        if(!$result || $result['result'] != 'success'){
            return array(
                'result' => 'error'
            );
        }
        $obj = $result['object']['categories'][0];
        $data[] = $obj['slug'];
        if($obj['parent'] != 0){
            $data = $this->_getCategoriesParent($cart, $obj['parent'], $data);
        }
        return $data;
    }
}
