<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();
class LeCaMgSeoXcartCustom{
    
    public function prepareImportCategories($cart){
        return false;
    }

    public function getCategoriesExtQuery($cart, $categories){
        $result = false;
        $notice = $cart->getNotice();       
        return $result;
    }

    public function getCategoriesExtRelQuery($cart, $categories, $categoriesExt){
        return false;
    }

    public function categorySeo($cart, $category_id_desc, $convert, $category, $categoriesExt){
        $path = 'home.php?cat=' . $category['categoryid'];
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
        $result = false;
        $notice = $cart->getNotice();
        $proIds = $cart->duplicateFieldValueFromList($products['object'], 'productid');
        if($proIds){
            $pro_id_con = $cart->arrayToInCondition($proIds);
            $result = array(
                'products_categories' => "SELECT * FROM _DBPRF_products_categories WHERE productid IN {$pro_id_con}",
            );
        }
        return $result;
    }

    public function getProductsExtRelQuery($cart, $products, $productsExt){
        return false;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $products_categories = $cart->getListFromListByField($productsExt['object']['products_categories'], 'productid', $product['productid']);
        if($products_categories){
            foreach ($products_categories as $product_categorie){
                for($i=1; $i<=5; $i++){
                    $path = 'product.php?productid='.$product['productid'].'&cat='.$product_categorie['categoryid'].'&page='.$i;
                    $cart->insertTable(LEUR_TABLE, array(
                        'link' => $path,
                        'type' => 'product',
                        'type_id' => $product_id_desc
                    ));
                }
            }
        }else{
            for($i=1; $i<=5; $i++){
                $path = 'product.php?productid='.$product['productid'].'&cat=0&page='.$i;
                $cart->insertTable(LEUR_TABLE, array(
                    'link' => $path,
                    'type' => 'product',
                    'type_id' => $product_id_desc
                ));
            }
        }
    }


}
?>