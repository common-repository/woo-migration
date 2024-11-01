<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();
class LeCaMgSeoOxideshopDefault{
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        $result = false;
        $notice = $cart->getNotice();
        $catIds = $cart->duplicateFieldValueFromList($categories['object'], 'OXID');
        if($catIds){
            $cat_id_con = $cart->arrayToInCondition($catIds);
            $result = array(
                'seo_names' => "SELECT * FROM _DBPRF_oxseo WHERE OXTYPE = 'oxcategory' AND OXOBJECTID IN {$cat_id_con}",
            );
        }
        return $result;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        return false;
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        $cat_seo = $cart->getListFromListByField($categoriesExt['object']['seo_names'], 'OXOBJECTID', $category['OXID']);
		if($cat_seo){
			foreach($cat_seo as $row){
				$path = $row['OXSEOURL'];
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
        $result = false;
        $notice = $cart->getNotice();
        $proIds = $cart->duplicateFieldValueFromList($products['object'], 'OXID');
        if($proIds){
            $pro_id_con = $cart->arrayToInCondition($proIds);
            $result = array(
                'seo_names' => "SELECT * FROM _DBPRF_oxseo WHERE OXTYPE = 'oxarticle' AND OXOBJECTID IN {$pro_id_con}",
            );
        }
        return $result;
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        $result = false;
        $notice = $cart->getNotice();
        $catIds = $cart->duplicateFieldValueFromList($productsExt['object']['seo_names'], 'OXPARAMS');
        $catIds = $this->_splitParentId($catIds);
        if($catIds){
            $cat_id_con = $cart->arrayToInCondition($catIds);
            $result = array(
                'seo_names_2' => "SELECT * FROM _DBPRF_oxseo WHERE OXTYPE = 'oxarticle' AND OXPARAMS IN {$cat_id_con}"
            );
        }
        return $result;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $product_seo = $cart->getListFromListByField($productsExt['object']['seo_names'], 'OXOBJECTID', $product['OXID']);
		if($product_seo){
			foreach($product_seo as $row){
				$path = $row['OXSEOURL'];
				$cart->insertTable(LEUR_TABLE, array(
					'link' => $path,
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
?>