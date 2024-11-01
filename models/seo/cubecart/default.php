<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoCubecartDefault{
    
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        $result = false;
        $notice = $cart->getNotice();
        $catIds = $cart->duplicateFieldValueFromList($categories['object'], 'cat_id');
        if($catIds){
            $cat_id_con = $cart->arrayToInCondition($catIds);
            $result = array(
                'seo_urls' => "SELECT * FROM _DBPRF_seo_urls WHERE type = 'cat' AND item_id IN {$cat_id_con}",
            );
        }
        return $result;
    }
    
    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){        
        return array();
    }
    
    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        $result = array();
        $notice = $cart->getNotice();
        if ($categoriesExt['object']['seo_urls']){
        	$cat_url = $cart->getRowFromListByField($categoriesExt['object']['seo_urls'], 'item_id', $category['cat_id']);
        	if($cat_url){
        		$path = $cat_url['path'] . '.html';
        		$cart->insertTable(LEUR_TABLE, array(
        				'link' => $path,
        				'type' => 'category',
        				'type_id' => $category_id_desc
        		));
        	}
        }
        return $result;
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
                'seo_urls' => "SELECT * FROM _DBPRF_seo_urls WHERE type = 'prod' AND item_id IN {$pro_id_con}",
            );
        }
        return $result;
    }
    
    public function getProductsExtRelQuery($cart, $products, $productsExt){       
        return array();
    }
    
     public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $result = array();
        $notice = $cart->getNotice();
        if ($productsExt['object']['seo_urls']){
	        $pro_url = $cart->getRowFromListByField($productsExt['object']['seo_urls'], 'item_id', $product['product_id']);	        
	        if($pro_url){
	            $path = $pro_url['path'] . '.html';
	            $cart->insertTable(LEUR_TABLE, array(
	                    'link' => $path,
	                    'type' => 'product',
	                    'type_id' => $product_id_desc
	                ));
	        }
        }
        return $result;
    }
}
