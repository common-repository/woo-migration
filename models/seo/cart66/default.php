<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgSeoCart66Default
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
        $path = $category['slug'] . '/';
        global $wpdb;
        $table_name = $wpdb->prefix . LEUR_TABLE;
        if($path){
            $sql = "INSERT INTO `" . $table_name . "` (link, type, type_id) VALUES ('" . $path . "', 'category', '" . (int)$category_id_desc . "')";
            try {
                $wpdb->query($sql);
            } catch (Exception $ex) {

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
        return false;
    }

    public function productSeo($cart, $product_id_desc, $convert, $product, $productsExt){
        $path = sanitize_title($product['name']) . '/';
        global $wpdb;
        $table_name = $wpdb->prefix . LEUR_TABLE;
        if($data){
            foreach($data as $path){
                $sql = "INSERT INTO `" . $table_name . "` (link, type, type_id) VALUES ('" . $path . "', 'product', '" . (int)$product_id_desc . "')";
                try {
                    $wpdb->query($sql);
                } catch (Exception $ex) {

                }
            }
        }
    }    
}
