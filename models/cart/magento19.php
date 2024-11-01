<?php 

/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgCartMagento19 extends LeCaMgCart
{
    public function displayConfig(){
        $parent = parent::displayConfig();
        if($parent['result'] != "success"){
            return $parent;
        }
        $response = $this->_defaultResponse();
        $default_config = $this->getConnectorData($this->getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize(array(
                "core_store" => "SELECT * FROM _DBPRF_core_store WHERE store_id != 0",
                "currencies" => "SELECT * FROM _DBPRF_core_config_data WHERE path = 'currency/options/default'",
                "eav_entity_type" => "SELECT * FROM _DBPRF_eav_entity_type",
            )),
        ));
        if(!$default_config || $default_config['result'] != "success"){
            return $this->errorConnector(false);
        }
        $object = $default_config['object'];
        if ($object && $object['core_store'] && $object['eav_entity_type']) {
            $this->_notice['config']['default_lang'] = $this->_getDefaultLanguage($object['core_store']);
            foreach ($object['eav_entity_type'] as $row) {
                $this->_notice['extend'][$row['entity_type_code']] = $row['entity_type_id'];
            }
        }
        if ($object['currencies']) {
            $this->_notice['config']['default_currency'] = isset($object['currencies']['0']['value']) ? $object['currencies']['0']['value'] : 'USD';
        } else {
            $this->_notice['config']['default_currency'] = 'USD';
        }
        $data = $this->getConnectorData($this->getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize(array(
                "core_store" => "SELECT * FROM _DBPRF_core_store WHERE code != 'admin'",
                "currencies" => "SELECT * FROM _DBPRF_core_config_data WHERE path = 'currency/options/allow'",
                "sales_order_status" => "SELECT * FROM _DBPRF_sales_order_status",
            ))
        ));
        if(!$data || $data['result'] != "success"){
            return $this->errorConnector(false);
        }
        $obj = $data['object'];
        $language_data = $currency_data = $order_status_data = array();
        foreach ($obj['core_store'] as $language_row) {
            $lang_id = $language_row['store_id'];
            $lang_name = $language_row['name'] . "(" . $language_row['code'] . ")";
            $language_data[$lang_id] = $lang_name;
        }
        if ($obj['currencies']) {
            $currencies = explode(',', $obj['currencies'][0]['value']);
            foreach ($currencies as $currency_row) {
                $currency_id = $currency_row;
                $currency_name = $currency_row;
                $currency_data[$currency_id] = $currency_name;
            }
        } else {
            $currency_data['USD'] = 'USD';
        }
        foreach ($obj['sales_order_status'] as $order_status_row) {
            $order_status_id = $order_status_row['status'];
            $order_status_name = $order_status_row['label'];
            $order_status_data[$order_status_id] = $order_status_name;
        }
        $cat_data = array(0 => 'Default Root Category');
        $this->_notice['config']['config_support']['country_map'] = false;
        $this->_notice['config']['config_support']['customer_group_map'] = false;
        $this->_notice['config']['cat_data'] = false; //$cat_data;
        $this->_notice['config']['language_data'] = $language_data;
        $this->_notice['config']['currency_data'] = $currency_data;
        $this->_notice['config']['order_status_data'] = $order_status_data;
        $this->_custom->displayConfigCustom($this);
        $response['result'] = 'success';
        return $response;
    }

    public function displayConfirm(){
        $parent = parent::displayConfirm();
        if($parent['result'] != "success"){
            return $parent;
        }
        $this->_custom->displayConfirmCustom($this);
        return array(
            'result' => "success"
        );
    }

    public function displayImport(){
        $parent = parent::displayImport();
        if($parent['result'] != "success"){
            return $parent;
        }
        $response = $this->_defaultResponse();
        $data = $this->getConnectorData($this->getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize(array(
                'taxes' => "SELECT COUNT(1) AS count FROM _DBPRF_tax_class WHERE class_type = 'PRODUCT' AND class_id > {$this->_notice['taxes']['id_src']}",
                'manufacturers' => "SELECT COUNT(1) AS count FROM _DBPRF_eav_attribute as ea 
                                                LEFT JOIN _DBPRF_eav_attribute_option as eao ON ea.attribute_id = eao.attribute_id
                                                WHERE attribute_code = 'manufacturer' AND eao.option_id > {$this->_notice['manufacturers']['id_src']}",
                'categories' => "SELECT COUNT(1) AS count FROM _DBPRF_catalog_category_entity WHERE entity_id > {$this->_notice['categories']['id_src']} AND level > 1",
                'products' => "SELECT COUNT(1) AS count FROM _DBPRF_catalog_product_entity as cpe WHERE cpe.type_id != 'bundle' AND cpe.entity_id NOT IN (SELECT child_id FROM _DBPRF_catalog_product_relation) AND cpe.entity_id > {$this->_notice['products']['id_src']}",
                'customers' => "SELECT COUNT(1) AS count FROM _DBPRF_customer_entity WHERE entity_id > {$this->_notice['customers']['id_src']}",
                'orders' => "SELECT COUNT(1) AS count FROM _DBPRF_sales_flat_order WHERE entity_id > {$this->_notice['orders']['id_src']}",
                'reviews' => "SELECT COUNT(1) AS count FROM _DBPRF_review WHERE review_id > {$this->_notice['reviews']['id_src']}"
            ))
        ));
        if (!$data || $data['result'] != 'success') {
            return $this->errorConnector(false);
        }
        $real_totals = array();
        foreach ($data['object'] as $type => $rows) {
            $total = $this->arrayToCount($rows, 'count');
            $real_totals[$type] = $total;
        }
        $totals = $this->_limit($real_totals);
        $recent = $this->getRecentNotice();
        foreach($totals as $type => $count){
            $this->_notice[$type]['total'] = $count;
        }
        if(!$this->_notice['config']['add_option']['add_new']){
            $delete = $this->deleteImport();
            if($delete === false){
                return $this->errorDatabase(false);
            }
        }
        $this->_custom->displayImportCustom($this);
        $response['result'] = "success";
        return $response;
    }

    protected function _getTaxesMainQuery(){
        $id_src = $this->_notice['taxes']['id_src'];
        $limit = $this->_notice['setting']['taxes'];
        $query = "SELECT * FROM _DBPRF_tax_class WHERE class_type = 'PRODUCT' AND class_id > {$id_src} ORDER BY class_id ASC LIMIT {$limit}";
        return $query;
    }

    protected function _getTaxesExtraQuery($taxes){
        $taxClassIds = $this->duplicateFieldValueFromList($taxes['object'], 'class_id');
        $tax_class_id_con = $this->arrayToInCondition($taxClassIds);
        $ext_query = array(
            'tax_calculation' => "SELECT * FROM _DBPRF_tax_calculation WHERE product_tax_class_id IN {$tax_class_id_con}",
        );
        return $ext_query;
    }

    protected function _getTaxesExtraRelQuery($taxes, $taxesExt){
        $taxRateIds = $this->duplicateFieldValueFromList($taxesExt['object']['tax_calculation'], 'tax_calculation_rate_id');
        $tax_rate_id = $this->arrayToInCondition($taxRateIds);
        $ext_rel_query = array(
            'tax_calculation_rate' => "SELECT tcr.*, dcr.code as region_code FROM _DBPRF_tax_calculation_rate as tcr LEFT JOIN _DBPRF_directory_country_region as dcr ON tcr.tax_region_id = dcr.region_id WHERE tax_calculation_rate_id IN {$tax_rate_id}",
        );
        return $ext_rel_query;
    }

    public function getTaxId($tax, $taxesExt){
        if(LeCaMgCustom::TAX_ID){
            return $this->_custom->getTaxIdCustom($this, $tax, $taxesExt);
        }
        return $tax['class_id'];
    }

    public function convertTax($tax, $taxesExt){
        if(LeCaMgCustom::TAX_CONVERT){
            return $this->_custom->convertTaxCustom($this, $tax, $taxesExt);
        }
        $tax_data = array(
            'name' => $tax['class_name']
        );
        $custom = $this->_custom->convertTaxCustom($this, $tax, $taxesExt);
        if($custom){
            $tax_data = array_merge($tax_data, $custom);
        }
        return array(
            'result' => "success",
            'data' => $tax_data
        );
    }

    public function afterSaveTax($tax_id_desc, $convert, $tax, $taxesExt){
        if(parent::afterSaveTax($tax_id_desc, $convert, $tax, $taxesExt)){
            return false;
        }
        $taxRelate = $this->getListFromListByField($taxesExt['object']['tax_calculation'], 'product_tax_class_id', $tax['class_id']);
        $tax_rate_ids = $this->duplicateFieldValueFromList($taxRelate, 'tax_calculation_rate_id');
        $taxRates = $this->getListFromListByListField($taxesExt['object']['tax_calculation_rate'], 'tax_calculation_rate_id', $tax_rate_ids);
        if($taxRates){
            foreach($taxRates as $tax_rate){
                if ($tax_rate['tax_region_id']) {
                    $state = $tax_rate['region_code'];
                } else {
                    $state = '';
                }
                $data = array(
                    'tax_rate_country' => $tax_rate['tax_country_id'],
                    'tax_rate_state' => $state,
                    'tax_rate' => $tax_rate['rate'],
                    'tax_rate_name' => $tax_rate['code'],
                    'tax_rate_priority' => 1,
                    'tax_rate_compound' => false,
                    'tax_rate_shipping' => true,
                    'tax_rate_order' => true,
                    'tax_rate_class' => $tax_id_desc
                );
                $this->wooTaxRate($data);
            }
        }
    }


    protected function _getManufacturersMainQuery(){
        $id_src = $this->_notice['manufacturers']['id_src'];
        $limit = $this->_notice['setting']['manufacturers'];
        $query = "SELECT eao.* FROM _DBPRF_eav_attribute as ea 
                                                LEFT JOIN _DBPRF_eav_attribute_option as eao ON ea.attribute_id = eao.attribute_id
                                                WHERE attribute_code = 'manufacturer' AND eao.option_id > {$id_src} ORDER BY eao.option_id ASC LIMIT {$limit}";
        return $query;
    }

    protected function _getManufacturersExtraQuery($manufacturers){
        $manuOptIds = $this->duplicateFieldValueFromList($manufacturers['object'], 'option_id');
        $option_id = $this->arrayToInCondition($manuOptIds);
        $ext_query = array(
            'eav_attribute_option_value' => "SELECT * FROM _DBPRF_eav_attribute_option_value WHERE option_id IN {$option_id}",
        );
        return $ext_query;
    }

    protected function _getManufacturersExtraRelQuery($manufacturers, $manufacturersExt){
        return array();
    }

    public function getManufacturerId($manufacturer, $manufacturersExt){
        if(LeCaMgCustom::MANUFACTURER_ID){
            return $this->_custom->getManufacturerIdCustom($this, $manufacturer, $manufacturersExt);
        }
        return $manufacturer['option_id'];
    }

    public function convertManufacturer($manufacturer, $manufacturersExt){
        if(LeCaMgCustom::MANUFACTURER_CONVERT){
            return $this->_custom->convertManufacturerCustom($this, $manufacturer, $manufacturersExt);
        }
        return array(
            'result' => "success",
            'data' => array()
        );
    }

    protected function _getCategoriesMainQuery(){
        $id_src = $this->_notice['categories']['id_src'];
        $limit = $this->_notice['setting']['categories'];
        $query = "SELECT * FROM _DBPRF_catalog_category_entity WHERE entity_id > {$id_src} AND level > 1 ORDER BY entity_id ASC LIMIT {$limit}";
        return $query;
    }

    protected function _getCategoriesExtraQuery($categories){
        $categoryIds = $this->duplicateFieldValueFromList($categories['object'], 'entity_id');
        $cat_id_con = $this->arrayToInCondition($categoryIds);
        $ext_query = array(
            'catalog_category_entity_datetime' => "SELECT * FROM _DBPRF_catalog_category_entity_datetime WHERE entity_id IN {$cat_id_con}",
            'catalog_category_entity_decimal' => "SELECT * FROM _DBPRF_catalog_category_entity_decimal WHERE entity_id IN {$cat_id_con}",
            'catalog_category_entity_int' => "SELECT * FROM _DBPRF_catalog_category_entity_int WHERE entity_id IN {$cat_id_con}",
            'catalog_category_entity_text' => "SELECT * FROM _DBPRF_catalog_category_entity_text WHERE entity_id IN {$cat_id_con}",
            'catalog_category_entity_varchar' => "SELECT * FROM _DBPRF_catalog_category_entity_varchar WHERE entity_id IN {$cat_id_con}",
            'eav_attribute' => "SELECT * FROM _DBPRF_eav_attribute WHERE entity_type_id = {$this->_notice['extend']['catalog_category']}",
        );
        return $ext_query;
    }

    protected function _getCategoriesExtraRelQuery($categories, $categoriesExt){
        return array();
    }

    public function getCategoryId($category, $categoriesExt){
        if(LeCaMgCustom::CATEGORY_ID){
            return $this->_custom->getCategoryIdCustom($this, $category, $categoriesExt);
        }
        return $category['entity_id'];
    }

    public function convertCategory($category, $categoriesExt){
        if(LeCaMgCustom::CATEGORY_CONVERT){
            return $this->_custom->convertCategoryCustom($this, $category, $categoriesExt);
        }
        if($category['level'] == 2){
            $cat_parent_id = 0;
        } else {
            $parent_id = $this->_getCategoryParentId($category['path']);
            $cat_parent_id = $this->getIdDescCategory($parent_id);
            if(!$cat_parent_id){
                $parent_ipt = $this->_importCategoryParent($parent_id);
                if($parent_ipt['result'] == 'error'){
                    return $parent_ipt;
                } else if($parent_ipt['result'] == 'warning'){
                    return array(
                        'result' => 'warning',
                        'msg' => $this->consoleWarning("Category Id = " . $category['entity_id'] . " import failed. Error: Could not import parent category id = " . $parent_id)
                    );
                } else {
                    $cat_parent_id = $parent_ipt['id_desc'];
                }
            }
        }
        $attribute = array();
        foreach ($categoriesExt['object']['eav_attribute'] as $row) {
            $attribute[$row['attribute_code']] = $row['attribute_id'];
        }
        $lang_map = $this->_notice['config']['languages'];
        $varchar = $this->getListFromListByField($categoriesExt['object']['catalog_category_entity_varchar'], 'entity_id', $category['entity_id']);
        $text = $this->getListFromListByField($categoriesExt['object']['catalog_category_entity_text'], 'entity_id', $category['entity_id']);
        $names = $this->getListFromListByField($varchar, 'attribute_id', $attribute['name']);
        $name_def = $this->getRowValueFromListByField($names, 'store_id', '0', 'value');
        $name_map = $this->getRowValueFromListByField($names, 'store_id', $lang_map, 'value');
        $name_std = $name_map ? $name_map : $name_def;
        $descriptions = $this->getListFromListByField($text, 'attribute_id', $attribute['description']);
        $description_def = $this->getRowValueFromListByField($descriptions, 'store_id', '0', 'value');
        $description_map = $this->getRowValueFromListByField($descriptions, 'store_id', $lang_map, 'value');
        $description_std = $description_map ? $description_map : $description_def;
        $cat_data = array(
            'name' => $name_std,
            'slug' => sanitize_title($name_std),
            'term_group' => 0,
            'taxonomy' => 'product_cat',
            'description' => $description_std,
            'parent' => $cat_parent_id
        );
        $cat_data['meta'] = array(
            'order' => $category['position']
        );
        $images = $this->getListFromListByField($varchar, 'attribute_id', $attribute['image']);
        $image_path = $this->getRowValueFromListByField($images, 'store_id', '0', 'value');
        if($image_path){
            $path = $this->downloadImage($this->getUrlSuffix($this->_notice['config']['image_category']), $image_path, self::IMG_DIR, false, true);
            if($path){
                $img_id = $this->wpImage(self::IMG_DIR . "/" . $path);
                if($img_id){
                    $cat_data['meta']['thumbnail_id'] = $img_id;
                }
            }
        }
        $custom = $this->_custom->convertCategoryCustom($this, $category, $categoriesExt);
        if($custom){
            $cat_data = array_merge($cat_data, $custom);
        }
        return array(
            'result' => "success",
            'data' => $cat_data
        );
    }

    protected function _getProductsMainQuery(){
        $id_src = $this->_notice['products']['id_src'];
        $limit = $this->_notice['setting']['products'];
        $query = "SELECT * FROM _DBPRF_catalog_product_entity as cpe WHERE cpe.type_id != 'bundle' AND cpe.entity_id NOT IN (SELECT child_id FROM _DBPRF_catalog_product_relation) AND cpe.entity_id > {$id_src} ORDER BY cpe.entity_id ASC LIMIT {$limit}";
        return $query;
    }
    
    public function getProductsExtra($products){
        $productsExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getProductsExtraQuery($products);
		if($this->_seo){
            $seo_ext_query = $this->_seo->getProductsExtQuery($this, $products);
            if($seo_ext_query){
                $ext_query = array_merge($ext_query, $seo_ext_query);
            }
        }
        $cus_ext_query = $this->_custom->getProductsExtQueryCustom($this, $products);
        if($cus_ext_query){
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if($ext_query){
            $productsExt = $this->getConnectorData($this->getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if(!$productsExt || $productsExt['result'] != 'success'){
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getProductsExtraRelQuery($products, $productsExt);
			if($this->_seo){
                $seo_ext_rel_query = $this->_seo->getProductsExtRelQuery($this, $products, $productsExt);
                if($seo_ext_rel_query){
                    $ext_rel_query = array_merge($ext_rel_query, $seo_ext_rel_query);
                }
            }
            $cus_ext_rel_query = $this->_custom->getProductsExtRelQueryCustom($this, $products, $productsExt);
            if($cus_ext_rel_query){
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if($ext_rel_query){
                $productsExtRel = $this->getConnectorData($this->getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if(!$productsExtRel || $productsExtRel['result'] != 'success'){
                    return $this->errorConnector(true);
                }
                $productsExt = $this->syncConnectorObject($productsExt, $productsExtRel);
                // Additional
                $ext_rel_rel_query = $this->_getProductsExtraRelRelQuery($products, $productsExt);
                if ($ext_rel_rel_query) {
                    $productsExtRelRel = $this->getConnectorData($this->getUrlConnector('query'), array(
                        'serialize' => true,
                        'query' => serialize($ext_rel_rel_query)
                    ));
                    if (!$productsExtRelRel || $productsExtRelRel['result'] != 'success') {
                        return $this->errorConnector(true);
                    }
                    $productsExt = $this->syncConnectorObject($productsExt, $productsExtRelRel);
                }
            }
        }
        return $productsExt;
    }

    protected function _getProductsExtraQuery($products){
        $productIds = $this->duplicateFieldValueFromList($products['object'], 'entity_id');
        $pro_id_query = $this->arrayToInCondition($productIds);
        $ext_query = array(
            'catalog_product_relation' => "SELECT * FROM _DBPRF_catalog_product_relation WHERE parent_id IN {$pro_id_query}",
            'catalog_product_super_attribute' => "SELECT * FROM _DBPRF_catalog_product_super_attribute WHERE product_id IN {$pro_id_query}",
            'eav_attribute' => "SELECT * FROM _DBPRF_eav_attribute WHERE entity_type_id = {$this->_notice['extend']['catalog_product']}",
            'tag_relation' => "SELECT * FROM _DBPRF_tag_relation WHERE product_id IN {$pro_id_query}",
            'catalog_product_link' => "SELECT * FROM _DBPRF_catalog_product_link WHERE ( product_id IN {$pro_id_query} OR linked_product_id IN {$pro_id_query} ) AND link_type_id IN (4,5)",
        );
        return $ext_query;
    }

    protected function _getProductsExtraRelQuery($products, $productsExt){
        $productIds = $this->duplicateFieldValueFromList($products['object'], 'entity_id');
        $productChildIds = $this->duplicateFieldValueFromList($productsExt['object']['catalog_product_relation'], 'child_id');
        $pro_child_id = $this->arrayToInCondition($productChildIds);
        $productAllIds = array_merge($productIds, $productChildIds);
        $superAttrIds = $this->duplicateFieldValueFromList($productsExt['object']['catalog_product_super_attribute'], 'product_super_attribute_id');
        $pro_id_con = $this->arrayToInCondition($productAllIds);
        $super_attr_id = $this->arrayToInCondition($superAttrIds);
        $attrIds = $this->duplicateFieldValueFromList($productsExt['object']['eav_attribute'], 'attribute_id');
        $attr_id = $this->arrayToInCondition($attrIds);
        $tagIds = $this->duplicateFieldValueFromList($productsExt['object']['tag_relation'], 'tag_id');
        $tag_ids_query = $this->arrayToInCondition($tagIds);
        $ext_rel_query = array(
            'catalog_product_entity' => "SELECT * FROM _DBPRF_catalog_product_entity WHERE entity_id IN {$pro_child_id}",
            'catalog_product_entity_datetime' => "SELECT * FROM _DBPRF_catalog_product_entity_datetime WHERE entity_id IN {$pro_id_con}",
            'catalog_product_entity_decimal' => "SELECT * FROM _DBPRF_catalog_product_entity_decimal WHERE entity_id IN {$pro_id_con}",
            'catalog_product_entity_int' => "SELECT * FROM _DBPRF_catalog_product_entity_int WHERE entity_id IN {$pro_id_con}",
            'catalog_product_entity_text' => "SELECT * FROM _DBPRF_catalog_product_entity_text WHERE entity_id IN {$pro_id_con}",
            'catalog_product_entity_varchar' => "SELECT * FROM _DBPRF_catalog_product_entity_varchar WHERE entity_id IN {$pro_id_con}",
            'catalog_product_entity_gallery' => "SELECT * FROM _DBPRF_catalog_product_entity_gallery WHERE entity_id IN {$pro_id_con}",
            'catalog_product_entity_media_gallery' => "SELECT * FROM _DBPRF_catalog_product_entity_media_gallery WHERE entity_id IN {$pro_id_con}",
            'catalog_product_entity_tier_price' => "SELECT * FROM _DBPRF_catalog_product_entity_tier_price WHERE entity_id IN {$pro_id_con}",
            'catalog_product_option' => "SELECT * FROM _DBPRF_catalog_product_option WHERE product_id IN {$pro_id_con}",
            'catalog_category_product' => "SELECT * FROM _DBPRF_catalog_category_product WHERE product_id IN {$pro_id_con}",
            'catalog_product_relation' => "SELECT * FROM _DBPRF_catalog_product_relation WHERE parent_id IN {$pro_id_con}",
            'catalog_product_super_attribute_label' => "SELECT * FROM _DBPRF_catalog_product_super_attribute_label WHERE product_super_attribute_id IN {$super_attr_id}",
            'catalog_product_super_attribute_pricing' => "SELECT * FROM _DBPRF_catalog_product_super_attribute_pricing WHERE product_super_attribute_id IN {$super_attr_id}",
            'catalog_eav_attribute' => "SELECT * FROM _DBPRF_catalog_eav_attribute WHERE attribute_id IN {$attr_id}",
            'cataloginventory_stock_item' => "SELECT * FROM _DBPRF_cataloginventory_stock_item WHERE product_id IN {$pro_id_con}",
            'tag' => "SELECT * FROM _DBPRF_tag WHERE tag_id IN {$tag_ids_query}",
        );
        return $ext_rel_query;
    }
    
    protected function _getProductsExtraRelRelQuery($products, $productsExt) {
        $optionAttrIds = $this->duplicateFieldValueFromList($productsExt['object']['catalog_product_entity_int'], 'value');
        $option_attr_id = $this->arrayToInCondition($optionAttrIds);
        $galleryIds = $this->duplicateFieldValueFromList($productsExt['object']['catalog_product_entity_media_gallery'], 'value_id');
        $gallery_value_id = $this->arrayToInCondition($galleryIds);
        $optionCusIds = $this->duplicateFieldValueFromList($productsExt['object']['catalog_product_option'], 'option_id');
        $option_cus_id = $this->arrayToInCondition($optionCusIds);
        //Addition
        $multi = $this->getListFromListByField($productsExt['object']['eav_attribute'], 'frontend_input', 'multiselect');
        $multi_ids = $this->duplicateFieldValueFromList($multi, 'attribute_id');
        $all_option = array();
        if ($multi_ids) {
            $multi_opt = $this->getListFromListByListField($productsExt['object']['catalog_product_entity_varchar'], 'attribute_id', $multi_ids);
            foreach ($multi_opt as $row) {
                $new_options = explode(',', $row['value']);
                $all_option = array_merge($all_option, $new_options);
            }
        }
        $all_option_query = $this->arrayToInCondition($all_option);
        $ext_rel_rel_query = array(
            'eav_attribute_option_value' => "SELECT * FROM _DBPRF_eav_attribute_option_value WHERE option_id IN {$option_attr_id} OR option_id IN {$all_option_query}",
            'catalog_product_entity_media_gallery_value' => "SELECT * FROM _DBPRF_catalog_product_entity_media_gallery_value WHERE value_id IN {$gallery_value_id}",
            'catalog_product_option_title' => "SELECT * FROM _DBPRF_catalog_product_option_title WHERE option_id IN {$option_cus_id}",
            'catalog_product_option_price' => "SELECT * FROM _DBPRF_catalog_product_option_price WHERE option_id IN {$option_cus_id}",
            'catalog_product_option_type_value' => "SELECT a.*, b.*, c.price, c.price_type FROM _DBPRF_catalog_product_option_type_value as a 
                                                                                            LEFT JOIN _DBPRF_catalog_product_option_type_title as b ON a.option_type_id = b.option_type_id
                                                                                            LEFT JOIN _DBPRF_catalog_product_option_type_price as c ON b.option_type_id = c.option_type_id AND b.store_id = c.store_id
                                                                                            WHERE a.option_id IN {$option_cus_id}
                                                                                            ",
        );
        return $ext_rel_rel_query;
    }

    public function getProductId($product, $productsExt){
        if(LeCaMgCustom::PRODUCT_ID){
            return $this->_custom->getProductIdCustom($this, $product, $productsExt);
        }
        return $product['entity_id'];
    }

    public function convertProduct($product, $productsExt){
        if(LeCaMgCustom::PRODUCT_CONVERT){
            return $this->_custom->convertProductCustom($this, $product, $productsExt);
        }
        $attribute = array();
        foreach ($productsExt['object']['eav_attribute'] as $row) {
            $attribute[$row['attribute_code']] = $row['attribute_id'];
        }
        $lang_map = $this->_notice['config']['languages'];
        $varchar = $this->getListFromListByField($productsExt['object']['catalog_product_entity_varchar'], 'entity_id', $product['entity_id']);
        $text = $this->getListFromListByField($productsExt['object']['catalog_product_entity_text'], 'entity_id', $product['entity_id']);
        $int = $this->getListFromListByField($productsExt['object']['catalog_product_entity_int'], 'entity_id', $product['entity_id']);
        $decimal = $this->getListFromListByField($productsExt['object']['catalog_product_entity_decimal'], 'entity_id', $product['entity_id']);
        $datetime = $this->getListFromListByField($productsExt['object']['catalog_product_entity_datetime'], 'entity_id', $product['entity_id']);
        
        $names = $this->getListFromListByField($varchar, 'attribute_id', $attribute['name']);
        $pro_name = $this->getRowValueFromListByField($names, 'store_id', '0', 'value');
        $pro_name_map = $this->getRowValueFromListByField($names, 'store_id', $lang_map, 'value');
        $pro_name_std = $pro_name_map ? $pro_name_map : $pro_name;
        $descriptions = $this->getListFromListByField($text, 'attribute_id', $attribute['description']);
        $pro_desc = $this->getRowValueFromListByField($descriptions, 'store_id', '0', 'value');
        $pro_desc_map = $this->getRowValueFromListByField($descriptions, 'store_id', $lang_map, 'value');
        $pro_desc_std = $pro_desc_map ? $pro_desc_map : $pro_desc;
        $pro_desc_std = $this->_changeImgSrcInText($pro_desc_std);
        $short_descriptions = $this->getListFromListByField($text, 'attribute_id', $attribute['short_description']);
        $pro_short_desc = $this->getRowValueFromListByField($short_descriptions, 'store_id', '0', 'value');
        $pro_short_desc_map = $this->getRowValueFromListByField($short_descriptions, 'store_id', $lang_map, 'value');
        $pro_short_desc_std = $pro_short_desc_map ? $pro_short_desc_map : $pro_short_desc;
        $thumbnail_id = $product_image_gallery = "";
        $image_path = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['image'], 'value');
        $image_label = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['image_label'], 'value');
        if($image_path){
            $path = $this->downloadImage($this->getUrlSuffix($this->_notice['config']['image_product']), $image_path, self::IMG_DIR, false, true);
            if($path){
                $thumbnail_id = $this->wpImage(self::IMG_DIR . "/" . $path, $image_label);
            }
        }
        $gallery = $this->getListFromListByField($productsExt['object']['catalog_product_entity_media_gallery'], 'entity_id', $product['entity_id']);
        if($gallery){
            $pro_img = array();
            foreach($gallery as $pro_img_src){
                if ($pro_img_src['value'] == $image_path) {continue;}
                $path = $this->downloadImage($this->getUrlSuffix($this->_notice['config']['image_product']), $pro_img_src['value'], self::IMG_DIR, false, true);
                if($path){
                    $gal_label = $this->getRowValueFromListByField($productsExt['object']['catalog_product_entity_media_gallery_value'], 'value_id', $pro_img_src['value_id'], 'label');
                    $img_id = $this->wpImage(self::IMG_DIR . "/" . $path, $gal_label);
                    if($img_id){
                        $pro_img[] = $img_id;
                    }
                }
            }
            if($pro_img){
                $product_image_gallery = implode(',', $pro_img);
            }
        }
		$created_date = $product['created_at'] ? $product['created_at'] : '0000-00-00 00:00:00';
		$updated_date = $product['updated_at'] ? $product['updated_at'] : '0000-00-00 00:00:00';
        $product_data = array(
            'post_author' => $this->wpCurrentUserId(),
            'post_date' => $created_date,
            'post_date_gmt' => $created_date,
            'post_content' => $pro_desc_std ? $pro_desc_std : 'No description.',
            'post_title' => $pro_name_std,
            'post_excerpt' => $pro_short_desc_std ? $pro_short_desc_std : 'No description.',
            'post_status' => $this->getRowValueFromListByField($int, 'attribute_id', $attribute['status'], 'value') == '1' ? "publish" : "draft",
            'comment_status' => "open",
            'ping_status' => "closed",
            'post_password' => "",
            'post_name' => sanitize_title($pro_name_std),
            'to_ping' => "",
            'pinged' => "",
            'post_modified' => $updated_date,
            'post_modified_gmt' => $updated_date,
            'post_content_filtered' => "",
            'post_parent' => isset($product['parent_id_desc']) ? $product['parent_id_desc'] : 0,
            'guid' => substr(site_url("?product=" . sanitize_title($pro_name_std)),0,255),
            'menu_order' => 0,
            'post_type' => "product",
            'post_mime_type' => "",
            'comment_count' => 0
        );
        $stock = $this->getRowFromListByField($productsExt['object']['cataloginventory_stock_item'], 'product_id', $product['entity_id']);
        $stock_qty = floatval($stock['qty']);
        if ($stock['backorders'] == 1 && !$stock['use_config_backorders']) {
            $backorder = "yes";
        } elseif ($stock['backorders'] == 2 && !$stock['use_config_backorders']) {
            $backorder = "notify";
        } else {
            $backorder = "no";
        }
        $tax_class_id = $this->getRowValueFromListByField($int, 'attribute_id', $attribute['tax_class_id'], 'value');
        $weight = $this->getRowValueFromListByField($decimal, 'attribute_id', $attribute['weight'], 'value');
        $price = $this->getRowValueFromListByField($decimal, 'attribute_id', $attribute['price'], 'value');
        $special_price = $this->getRowValueFromListByField($decimal, 'attribute_id', $attribute['special_price'], 'value');
        $special_from_date = strtotime($this->getRowValueFromListByField($datetime, 'attribute_id', $attribute['special_from_date'], 'value'));
        $special_to_date = strtotime($this->getRowValueFromListByField($datetime, 'attribute_id', $attribute['special_to_date'], 'value'));
        $meta = array(
            '_product_attributes' => serialize(array()),
            '_thumbnail_id' => $thumbnail_id,
            '_visibility' => "visible",
            '_stock_status' => $stock['is_in_stock'] == '1' || !$stock['manage_stock'] ? "instock" : "outofstock",
            'total_sales' => 0,
            '_downloadable' => $product['type_id'] == 'downloadable' ? "yes" : "no",
            '_virtual' => $product['type_id'] == 'virtual' ? "yes" : "no",
            '_regular_price' => $price,
            '_sale_price' => $special_price,
            '_tax_status' => $this->getValueTax($tax_class_id) ? "taxable" : "none",
            '_tax_class' => $this->getValueTax($tax_class_id),
            '_featured' => "no",
            '_purchase_note' => "",
            '_weight' => $weight,
            '_length' => "",
            '_width' => "",
            '_height' => "",
            '_sku' => $product['sku'],
            '_sale_price_dates_from' => $special_price && $special_from_date ? $special_from_date : "",
            '_sale_price_dates_to' => $special_price && $special_to_date ? $special_to_date : "",
            '_price' => $special_price ? $special_price : $price,
            '_sold_individually' => "",
            '_manage_stock' => $stock['manage_stock'] || $stock['use_config_manage_stock'] ? "yes" : "no",
            '_backorders' => $backorder,
            '_stock' => $stock_qty,
            '_upsell_ids' => serialize(array()),
            '_crosssell_ids' => serialize(array()),
            '_min_variation_price' => "",
            '_max_variation_price' => "",
            '_min_price_variation_id' => "",
            '_max_price_variation_id' => "",
            '_min_variation_regular_price' => "",
            '_max_variation_regular_price' => "",
            '_min_regular_price_variation_id' => "",
            '_max_regular_price_variation_id' => "",
            '_min_variation_sale_price' => "",
            '_max_variation_sale_price' => "",
            '_min_sale_price_variation_id' => "",
            '_max_sale_price_variation_id' => "",
            '_default_attributes' => serialize(array()),
            '_product_image_gallery' => $product_image_gallery
        );
        $product_data['meta'] = $meta;
        $custom = $this->_custom->convertProductCustom($this, $product, $productsExt);
        if($custom){
            $product_data = array_merge($product_data, $custom);
        }
        return array(
            'result' => "success",
            'data' => $product_data
        );
    }

    public function afterSaveProduct($product_id_desc, $convert, $product, $productsExt){
        if(parent::afterSaveProduct($product_id_desc, $convert, $product, $productsExt)){
            return false;
        }
        foreach ($productsExt['object']['eav_attribute'] as $row) {
            $attribute[$row['attribute_code']] = $row['attribute_id'];
        }
        $varchar = $this->getListFromListByField($productsExt['object']['catalog_product_entity_varchar'], 'entity_id', $product['entity_id']);
        $text = $this->getListFromListByField($productsExt['object']['catalog_product_entity_text'], 'entity_id', $product['entity_id']);
        $int = $this->getListFromListByField($productsExt['object']['catalog_product_entity_int'], 'entity_id', $product['entity_id']);
        $decimal = $this->getListFromListByField($productsExt['object']['catalog_product_entity_decimal'], 'entity_id', $product['entity_id']);
        $datetime = $this->getListFromListByField($productsExt['object']['catalog_product_entity_datetime'], 'entity_id', $product['entity_id']);
        $proCatSrc = $this->getListFromListByField($productsExt['object']['catalog_category_product'], 'product_id', $product['entity_id']);
        if($proCatSrc){
            foreach($proCatSrc as $pro_cat_src){
                $cat_id = $this->getValueCategory($pro_cat_src['category_id']);
                if($cat_id){
                    $relationship = array(
                        'object_id' => $product_id_desc,
                        'term_taxonomy_id' => $cat_id,
                        'term_order' => 0
                    );
                    $this->wpTermRelationship($relationship);
                }
            }
        }
        if($product['type_id'] == 'configurable'){
            $product_type = "variable";
        } elseif($product['type_id'] == 'grouped') {
            $product_type = "grouped";
        } elseif($product['type_id'] == 'simple') {
            $optionAll = $this->getListFromListByField($productsExt['object']['catalog_product_option'], 'product_id', $product['entity_id']);
            $optionCombi = $this->getListFromListByListField($optionAll, 'type', array('drop_down', 'radio'));
            if ($optionCombi) {
                $product_type = "variable";
            } else {
                $product_type = "simple";
            }
        } else {
            $product_type = "simple";
        }
        $productType = $this->getProductType();
        if(isset($productType[$product_type])){
            $taxonomy_id = $productType[$product_type];
            $relationship = array(
                'object_id' => $product_id_desc,
                'term_taxonomy_id' => $taxonomy_id,
                'term_order' => 0
            );
            $this->wpTermRelationship($relationship);
        }
        //Grouped
        if ($product_type == 'grouped') {
            $child_products = $this->getListFromListByField($productsExt['object']['catalog_product_relation'], 'parent_id', $product['entity_id']);
            foreach ($child_products as $child) {
                $child_info = $this->getRowFromListByField($productsExt['object']['catalog_product_entity'], 'entity_id', $child['child_id']);
                if ($child_info) {
                    $child_info['parent_id_desc'] = $product_id_desc;
                    $child_convert = $this->convertProduct($child_info, $productsExt);
                    $child_data = $child_convert['data'];
                    $child_id_desc = $this->product($child_data);
                    if (!$child_id_desc) {
                        continue;
                    }
                    $this->afterSaveProduct($child_id_desc, $child_convert, $child_info, $productsExt);
                }
            }
        }
        // product attributes
        $pro_attr = array();
        $childrenSrc = array();
        $position = 0;
        if ($product['type_id'] == 'simple') {
            //$optionAll = $this->getListFromListByField($productsExt['object']['catalog_product_option'], 'product_id', $product['entity_id']);
            //$optionCombi = $this->getListFromListByListField($optionAll, 'type', array('drop_down', 'radio'));
            if ($optionCombi) {
                $optionIdSrc = $this->duplicateFieldValueFromList($optionCombi, 'option_id');
                foreach ($optionIdSrc as $option_id_src) {
                    $children_src = array();
                    $title = $this->getListFromListByField($productsExt['object']['catalog_product_option_title'], 'option_id', $option_id_src);
                    $option_src_label = $this->getRowValueFromListByField($title, 'store_id', '0', 'title');
                    $pro_attr_code = sanitize_title($option_src_label);
                    //$pro_attr_code = $this->getValueImport(self::TYPE_ATTR, null, null, "pa_" . $option_src_name);
                    //$pro_attr_exists = false;
                    /* if (!$pro_attr_code) {
                      // check if exists
                      $pro_attr_data = array(
                      'attribute_name' => $option_src_name,
                      'attribute_label' => $option_src_label,
                      'attribute_type' => "select",
                      'attribute_orderby' => "menu_order",
                      'attribute_public' => 1,
                      );
                      $pro_attr_id = $this->wooAttributeTaxonomy($pro_attr_data);
                      if ($pro_attr_id) {
                      $pro_attr_exists = true;
                      $pro_attr_code = "pa_" . $option_src_name;
                      $this->attributeSuccess($option_id_src, $pro_attr_id, $pro_attr_code);
                      } else {
                      $pro_attr_code = $option_src_name;
                      }
                      } else {
                      $pro_attr_exists = true;
                      }
                      $optionValueSrcFilter = $this->getListFromListByField($productsExt['object']['catalog_product_option_type_value'], 'option_id', $option_id_src);
                      if (!$optionValueSrcFilter) {
                      continue;
                      }
                      $optionValueIdSrc = $this->duplicateFieldValueFromList($optionValueSrcFilter, 'option_type_id');
                      if ($pro_attr_exists) {
                      $option_src_name = $pro_attr_code;
                      foreach ($optionValueIdSrc as $option_value_id_src) {
                      $optionSrc = $this->getListFromListByField($optionValueSrcFilter, 'option_type_id', $option_value_id_src);
                      $optionPrice = $this->getRowFromListByField($optionSrc, 'store_id', '0');
                      if ($optionPrice['price_type'] == 'fixed') {
                      $price = $optionPrice['price'];
                      } else {
                      $price = $optionPrice['price'] * $convert['meta']['_price'] / 100;
                      }
                      $option_value_name = $this->getRowValueFromListByField($optionSrc, 'store_id', '0', 'title');
                      $slug = sanitize_title($option_value_name);
                      $option_value_id_desc = $this->getIdDescImport(self::TYPE_ATTR_VALUE, null, $slug);
                      if ($option_value_id_desc) {
                      $slug = $this->getValueAttributeValue($option_value_id_src);
                      $relationship = array(
                      'object_id' => $product_id_desc,
                      'term_taxonomy_id' => $option_value_id_desc,
                      'term_order' => 0
                      );
                      $this->wpTermRelationship($relationship);
                      $children_src[] = array(
                      'options_id' => $option_id_src,
                      'options_code' => $option_src_name,
                      'options_values_id' => $option_value_id_src,
                      'options_values_code' => $slug,
                      'price' => $price
                      );
                      continue;
                      }
                      $option_value_data = array(
                      'name' => $option_value_name,
                      'slug' => $slug,
                      'term_group' => 0,
                      'taxonomy' => $option_src_name,
                      'description' => $option_value_name,
                      'parent' => 0,
                      'count' => 0
                      );
                      $option_value_id_desc = $this->category($option_value_data);
                      if (!$option_value_id_desc) {
                      continue;
                      }
                      $this->attributeValueSuccess($option_value_id_src, $option_value_id_desc, $slug);
                      $relationship = array(
                      'object_id' => $product_id_desc,
                      'term_taxonomy_id' => $option_value_id_desc,
                      'term_order' => 0
                      );
                      $this->wpTermRelationship($relationship);
                      $children_src[] = array(
                      'options_id' => $option_id_src,
                      'options_code' => $option_src_name,
                      'options_values_id' => $option_value_id_src,
                      'options_values_code' => $slug,
                      'price' => $price
                      );
                      }
                      $pro_attr[$option_src_name] = array(
                      'name' => $option_src_name,
                      'value' => "",
                      'position' => $position,
                      'is_visible' => true,
                      'is_variation' => true,
                      'is_taxonomy' => true
                      );
                      $childrenSrc[$option_id_src] = $children_src;
                      $position++;
                      } else { */
                    $optionValueSrcFilter = $this->getListFromListByField($productsExt['object']['catalog_product_option_type_value'], 'option_id', $option_id_src);
                    if (!$optionValueSrcFilter) {
                        continue;
                    }
                    $optionValueIdSrc = $this->duplicateFieldValueFromList($optionValueSrcFilter, 'option_type_id');
                    $option_value_data = array();
                    foreach ($optionValueIdSrc as $option_value_id_src) {
                        $optionSrc = $this->getListFromListByField($optionValueSrcFilter, 'option_type_id', $option_value_id_src);
                        $optionPrice = $this->getRowFromListByField($optionSrc, 'store_id', '0');
                        if ($optionPrice['price_type'] == 'fixed') {
                            $price = $optionPrice['price'];
                        } else {
                            $price = $optionPrice['price'] * $convert['meta']['_price'] / 100;
                        }
                        $option_value_name = $this->getRowValueFromListByField($optionSrc, 'store_id', '0', 'title');
                        $option_value_data[] = $option_value_name;
                        $children_src[] = array(
                            'options_id' => $option_id_src,
                            'options_code' => $pro_attr_code,
                            'options_values_id' => $option_value_id_src,
                            'options_values_code' => sanitize_title($option_value_name),
                            'price' => $price
                        );
                    }
                    $pro_attr[$pro_attr_code] = array(
                        'name' => $option_src_label,
                        'value' => implode(' | ', $option_value_data),
                        'position' => (string)$position,
                        'is_visible' => 1,
                        'is_variation' => 1,
                        'is_taxonomy' => 0
                    );
                    $position++;
                    $childrenSrc[$option_id_src] = $children_src;
                    //}
                }
//                if ($pro_attr) {
//                    $this->updateTable(self::WP_POST_META, array(
//                        'meta_value' => serialize($pro_attr)
//                            ), array(
//                        'post_id' => $product_id_desc,
//                        'meta_key' => "_product_attributes"
//                    ));
//                }
            }
        } elseif ($product['type_id'] == 'configurable') {
            $children = $this->getListFromListByField($productsExt['object']['catalog_product_relation'], 'parent_id', $product['entity_id']);
            $childrenIds = $this->duplicateFieldValueFromList($children, 'child_id');
            $attributeIds = $this->getListFromListByField($productsExt['object']['catalog_product_super_attribute'], 'product_id', $product['entity_id']);
            if ($childrenIds && $attributeIds) {
                $optionIdSrc = $this->duplicateFieldValueFromList($attributeIds, 'attribute_id');
                foreach ($childrenIds as $child) {
                    $decimal_child = $this->getListFromListByField($productsExt['object']['catalog_product_entity_decimal'], 'entity_id', $child);
                    $stock_child = $this->getRowFromListByField($productsExt['object']['cataloginventory_stock_item'], 'product_id', $child);
                    $weight_child = $this->getRowValueFromListByField($decimal_child, 'attribute_id', $attribute['weight'], 'value');
                    $child_sku = $this->getRowValueFromListByField($productsExt['object']['catalog_product_entity'], 'entity_id', $child, 'sku');
                    $child_opt_vals = $this->getListFromListByField($productsExt['object']['catalog_product_entity_int'], 'entity_id', $child);
                    foreach ($optionIdSrc as $option_id_src) {
                        $children_src = array();
                        $pro_attr_code = $this->getValueAttribute($option_id_src);
                        $pro_attr_exists = false;
                        $option_src_label = "";
                        if (!$pro_attr_code) {
                            $optionSrc = $this->getRowFromListByField($productsExt['object']['eav_attribute'], 'attribute_id', $option_id_src);
                            $option_src_label = $optionSrc['frontend_label'];
                            $option_src_name = sanitize_title($optionSrc['attribute_code']);
                            // check if exists
                            $pro_attr_data = array(
                                'attribute_name' => $option_src_name,
                                'attribute_label' => $option_src_label,
                                'attribute_type' => "select",
                                'attribute_orderby' => "menu_order",
                                'attribute_public' => 1,
                            );
                            $pro_attr_id = $this->wooAttributeTaxonomy($pro_attr_data);
                            if ($pro_attr_id) {
                                $pro_attr_exists = true;
                                $pro_attr_code = "pa_" . $option_src_name;
                                $this->attributeSuccess($option_id_src, $pro_attr_id, $pro_attr_code);
                            } else {
                                $pro_attr_code = $option_src_name;
                            }
                        } else {
                            $pro_attr_exists = true;
                        }
                        $option_value_id_src = $this->getRowValueFromListByField($child_opt_vals, 'attribute_id', $option_id_src, 'value');
                        if (!$option_value_id_src) {
                            continue;
                        }
                        $superAttrId = $this->getRowValueFromListByField($attributeIds, 'attribute_id', $option_id_src, 'product_super_attribute_id');
                        $superPrice = $this->getListFromListByField($productsExt['object']['catalog_product_super_attribute_pricing'], 'product_super_attribute_id', $superAttrId);
                        $priceValue = $this->getRowFromListByField($superPrice, 'value_index', $option_value_id_src);
                        if ($priceValue) {
                            if (!$priceValue['is_percent']) {
                                $price = $priceValue['pricing_value'];
                            } else {
                                $price = $priceValue['pricing_value'] * $convert['meta']['_price'] / 100;
                            }
                        } else {
                            $price = 0;
                        }
                        if ($pro_attr_exists) {
                            $option_src_name = $pro_attr_code;
                            $option_value_id_desc = $this->getIdDescAttributeValue($option_value_id_src);
                            if ($option_value_id_desc) {
                                $slug = $this->getValueAttributeValue($option_value_id_src);
                                $relationship = array(
                                    'object_id' => $product_id_desc,
                                    'term_taxonomy_id' => $option_value_id_desc,
                                    'term_order' => 0
                                );
                                if (!$this->selectTableRow(self::WP_TERM_RELATION, $relationship)) {
                                    $this->wpTermRelationship($relationship);
                                }
                                $children_src = array(
                                    'options_id' => $option_id_src,
                                    'options_code' => $option_src_name,
                                    'options_values_id' => $option_value_id_src,
                                    'options_values_code' => $slug,
                                    'price' => $price,
                                    'sku' => $child_sku,
                                    'weight' => $weight_child,
                                    'stock' => $stock_child
                                );
                            } else {
                                $optionValueSrc = $this->getListFromListByField($productsExt['object']['eav_attribute_option_value'], 'option_id', $option_value_id_src);
                                $option_value_name = $this->getRowValueFromListByField($optionValueSrc, 'store_id', '0', 'value');
                                $slug = sanitize_title($option_value_name);
                                $option_value_data = array(
                                    'name' => $option_value_name,
                                    'slug' => $slug,
                                    'term_group' => 0,
                                    'taxonomy' => $option_src_name,
                                    'description' => $option_value_name,
                                    'parent' => 0,
                                    'count' => 0
                                );
                                $option_value_id_desc = $this->category($option_value_data);
                                if (!$option_value_id_desc) {
                                    continue;
                                }
                                $this->attributeValueSuccess($option_value_id_src, $option_value_id_desc, $slug);
                                $relationship = array(
                                    'object_id' => $product_id_desc,
                                    'term_taxonomy_id' => $option_value_id_desc,
                                    'term_order' => 0
                                );
                                $this->wpTermRelationship($relationship);
                                $children_src = array(
                                    'options_id' => $option_id_src,
                                    'options_code' => $option_src_name,
                                    'options_values_id' => $option_value_id_src,
                                    'options_values_code' => $slug,
                                    'price' => $price,
                                    'sku' => $child_sku,
                                    'weight' => $weight_child,
                                    'stock' => $stock_child
                                );
                            }
                            $childrenSrc[$child][] = $children_src;
                        }
                    }
                }
                foreach ($optionIdSrc as $option_id_src) {
                    $pro_attr_code = $this->getValueAttribute($option_id_src);
                    if ($pro_attr_code) {
                        $pro_attr[$pro_attr_code] = array(
                            'name' => $pro_attr_code,
                            'value' => "",
                            'position' => (string)$position,
                            'is_visible' => 1,
                            'is_variation' => 1,
                            'is_taxonomy' => 1
                        );
                        $position++;
                    }
                }
//                if ($pro_attr) {
//                    $this->updateTable(self::WP_POST_META, array(
//                        'meta_value' => serialize($pro_attr)
//                            ), array(
//                        'post_id' => $product_id_desc,
//                        'meta_key' => "_product_attributes"
//                    ));
//                }
            }
        }
        // import child
        $combinations = array();
        if ($product['entity_id'] != 2 && $product['entity_id'] != 3){
            if ($product['type_id'] == 'simple') {
                $combinations = $this->combinationArray($childrenSrc);
            } elseif ($product['type_id'] == 'configurable') {
                $combinations = $childrenSrc;
            }
        }
        if ($combinations){
            foreach($combinations as $key => $combination){
                $children_data = array(
                    'post_author' => $this->wpCurrentUserId(),
                    'post_date' => $convert['post_date'],
                    'post_date_gmt' => $convert['post_date_gmt'],
                    'post_content' => "",
                    'post_title' => "Variation " . $key . " of " . $convert['post_title'],
                    'post_excerpt' => "",
                    'post_status' => "publish",
                    'comment_status' => "open",
                    'ping_status' => "open",
                    'post_password' => "",
                    'post_name' => $convert['post_name'] . "-" . $key,
                    'to_ping' => "",
                    'pinged' => "",
                    'post_modified' => $convert['post_modified'],
                    'post_modified_gmt' => $convert['post_modified_gmt'],
                    'post_content_filtered' => "",
                    'post_parent' => $product_id_desc,
                    'guid' => site_url("/?product_variation=" . $convert['post_name'] . "-" . $key),
                    'menu_order' => 0,
                    'post_type' => "product_variation",
                    'post_mime_type' => "",
                    'comment_count' => 0
                );
                $price = $convert['meta']['_price'];
                $meta = array(
                    '_stock_status' => "instock",
                    '_sku' => isset($combination[0]['sku']) && $combination[0]['sku'] ? $combination[0]['sku'] : "",
                    '_thumbnail_id' => 0,
                    '_virtual' => "no",
                    '_downloadable' => "no",
                    '_weight' => isset($combination[0]['weight']) ? $combination[0]['weight'] : "",
                    '_length' => "",
                    '_width' => "",
                    '_height' => "",
                    '_manage_stock' => "no",
                    '_regular_price' => 0,
                    '_sale_price' => "",
                    '_sale_price_dates_from' => "",
                    '_sale_price_dates_to' => "",
                    '_price' => 0,
                    '_download_limit' => "",
                    '_download_expiry' => "",
                    '_downloadable_files' => ""
                );
                if (isset($combination[0]['stock'])) {
                    $meta['_stock_status'] = $combination[0]['stock']['is_in_stock'] == '1' ? "instock" : "outofstock";
                    $meta['_manage_stock'] = $combination[0]['stock']['manage_stock'] ? "yes" : "no";
                    $meta['_stock'] = floatval($combination[0]['stock']['qty']);
                }
                foreach($combination as $children_src){
                    $meta_key = "attribute_" . $children_src['options_code'];
                    $meta_value = $children_src['options_values_code'];
                    $meta[$meta_key] = $meta_value;
                    $price += $children_src['price'];
                }
                $meta['_price'] = $price;
                $meta['_regular_price'] = $price;
                $children_data['meta'] = $meta;
                $children_id = $this->product($children_data);
            }
        }
        //Attribute not variable
        $allow = array ('select', 'text', 'textarea', 'boolean', 'multiselect');
        $selectAttrs = array('multiselect', 'select');
        $cusAttributes = $this->getListFromListByField($productsExt['object']['eav_attribute'], 'is_user_defined', '1');
        if ($cusAttributes) {
            foreach ($cusAttributes as $attribute) {
                if (!in_array($attribute['frontend_input'], $allow)) {
                    continue;
                }
                $option_id_src = $attribute['attribute_id'];
                if ($option_value = $this->getRowFromListByField($$attribute['backend_type'], 'attribute_id', $option_id_src)) {
                    if (!$option_value['value']) {
                        continue;
                    }
                    $pro_attr_code = $this->getValueAttribute($option_id_src);
                    $pro_attr_exists = false;
                    $option_src_label = $attribute['frontend_label'];
                    $option_src_name = sanitize_title($attribute['attribute_code']);
                    if (isset($pro_attr[$option_src_name]) || isset($pro_attr['pa_'.$option_src_name])) continue;
                    if (!$pro_attr_code) {
                        // check if exists
                        $pro_attr_data = array(
                            'attribute_name' => $option_src_name,
                            'attribute_label' => $option_src_label,
                            'attribute_type' => in_array($attribute['frontend_input'], $selectAttrs) ? "select" : "text",
                            'attribute_orderby' => "menu_order",
                            'attribute_public' => 1,
                        );
                        $pro_attr_id = $this->wooAttributeTaxonomy($pro_attr_data);
                        if ($pro_attr_id) {
                            $pro_attr_exists = true;
                            $pro_attr_code = "pa_" . $option_src_name;
                            $this->attributeSuccess($option_id_src, $pro_attr_id, $pro_attr_code);
                        } else {
                            $pro_attr_code = $option_src_name;
                        }
                    } else {
                        $pro_attr_exists = true;
                    }
                    if ($attribute['frontend_input'] == 'select') {
                        $option_value_id_srcs[] = $option_value['value'];
                    } elseif ($attribute['frontend_input'] == 'multiselect') {
                        $option_value_id_srcs = explode(',', $option_value['value']);
                    } else {
                        $option_value_id_srcs[] = 0;
                    }
                    if ($pro_attr_exists) {
                        $option_src_name = $pro_attr_code;
                        $check = false;
                        foreach ($option_value_id_srcs as $option_value_id_src) {
                            $option_value_id_desc = $this->getIdDescAttributeValue($option_value_id_src);
                            if ($option_value_id_desc && $option_value_id_src) {
                                $slug = $this->getValueAttributeValue($option_value_id_src);
                                $relationship = array(
                                    'object_id' => $product_id_desc,
                                    'term_taxonomy_id' => $option_value_id_desc,
                                    'term_order' => 0
                                );
                                $this->wpTermRelationship($relationship);
                            } else {
                                $option_value_name = in_array($attribute['frontend_input'], $selectAttrs) ? $this->getRowValueFromListByField($productsExt['object']['eav_attribute_option_value'], 'option_id', $option_value_id_src, 'value') : $option_value['value'];
                                if (!$option_value_name && $attribute['frontend_input'] != 'boolean') {
                                    continue;
                                }
                                $slug = sanitize_title($option_value_name);
                                $option_value_data = array(
                                    'name' => $attribute['frontend_input'] == 'boolean' ? 'Yes' : $option_value_name,
                                    'slug' => $slug,
                                    'term_group' => 0,
                                    'taxonomy' => $option_src_name,
                                    'description' => in_array($attribute['frontend_input'], $selectAttrs) ? $option_value_name : null,
                                    'parent' => 0,
                                    'count' => 0
                                );
                                $option_value_id_desc = $this->category($option_value_data);
                                if (!$option_value_id_desc) {
                                    continue;
                                }
                                if (in_array($attribute['frontend_input'], $selectAttrs)) {
                                    $this->attributeValueSuccess($option_value_id_src, $option_value_id_desc, $slug);
                                }
                                $relationship = array(
                                    'object_id' => $product_id_desc,
                                    'term_taxonomy_id' => $option_value_id_desc,
                                    'term_order' => 0
                                );
                                $this->wpTermRelationship($relationship);
                            }
                            $check = true;
                        }
                        if (!$check) {
                            continue;
                        }
                        $pro_attr[$option_src_name] = array(
                            'name' => $option_src_name,
                            'value' => "",
                            'position' => (string) $position,
                            'is_visible' => 1,
                            'is_variation' => 0,
                            'is_taxonomy' => 1
                        );
                        $position++;
                    }
                }
            }
        }
        if ($pro_attr) {
            $this->updateTable(self::WP_POST_META, array(
                'meta_value' => serialize($pro_attr)
                    ), array(
                'post_id' => $product_id_desc,
                'meta_key' => "_product_attributes"
            ));
        }
        //Tag
        $proTags = $this->getListFromListByField($productsExt['object']['tag_relation'], 'product_id', $product['entity_id']);
        if ($proTags) {
            foreach ($proTags as $tag) {
                $pro_tag = $this->getRowFromListByField($productsExt['object']['tag'], 'tag_id', $tag['tag_id']);
                if ($pro_tag) {
                    $tag_id_desc = $this->getIdDescTag($pro_tag['tag_id']);
                    if (!$tag_id_desc) {
                        $slug = sanitize_title($pro_tag['name']);
                        $tag_data = array(
                            'name' => $pro_tag['name'],
                            'slug' => $slug,
                            'term_group' => 0,
                            'taxonomy' => 'product_tag',
                            'description' => '',
                            'parent' => 0,
                            'count' => 1,
                            'meta' => array('product_count_product_tag' => 1)
                        );
                        $tag_id_desc = $this->category($tag_data);
                        if (!$tag_id_desc) {
                            continue;
                        }
                        $this->tagSuccess($pro_tag['tag_id'], $tag_id_desc, $slug);
                    }
                    $tag_relationship = array(
                        'object_id' => $product_id_desc,
                        'term_taxonomy_id' => $tag_id_desc,
                        'term_order' => 0
                    );
                    $this->wpTermRelationship($tag_relationship);
                }
            }
        }
        //Linked
        $linkProductsAll = $this->getListFromListByField($productsExt['object']['catalog_product_link'], 'product_id', $product['entity_id']);
        if ($linkProductsAll) {
            $linkedTypes = array(
                4 => '_upsell_ids',
                5 => '_crosssell_ids'
            );
            foreach ($linkedTypes as $key => $value) {
                $linkedProducts = $this->getListFromListByField($linkProductsAll, 'link_type_id', $key);
                if ($linkedProducts) {
                    $link_ids_desc = array();
                    $link_products = $this->duplicateFieldValueFromList($linkedProducts, 'linked_product_id');
                    foreach ($link_products as $link_id) {
                        $l_product_id_desc = $this->getIdDescProduct($link_id);
                        if ($l_product_id_desc) {
                            $link_ids_desc[] = (int) $l_product_id_desc;
                        }
                    }
                    if ($link_ids_desc) {
                        $where = array(
                            'post_id' => $product_id_desc,
                            'meta_key' => $value
                        );
                        $data = array(
                            'meta_value' => serialize($link_ids_desc)
                        );
                        $this->wpPostMetaUpdate($data, $where);
                    }
                }
            }
        }
        $linkReverseProductsAll = $this->getListFromListByField($productsExt['object']['catalog_product_link'], 'linked_product_id', $product['entity_id']);
        if ($linkReverseProductsAll) {
            $linkedTypes = array(
                4 => '_upsell_ids',
                5 => '_crosssell_ids'
            );
            foreach ($linkedTypes as $key => $value) {
                $linkedProducts = $this->getListFromListByField($linkReverseProductsAll, 'link_type_id', $key);
                if ($linkedProducts) {
                    $link_ids_desc = array();
                    $link_products = $this->duplicateFieldValueFromList($linkedProducts, 'product_id');
                    foreach ($link_products as $link_id) {
                        $l_product_id_desc = $this->getIdDescProduct($link_id);
                        if ($l_product_id_desc) {
                            $where = array(
                                'post_id' => $l_product_id_desc,
                                'meta_key' => $value,
                            );
                            $metaValueImported = $this->selectTableRow(self::WP_POST_META, $where);
                            if (!$metaValueImported) {
                                continue;
                            }
                            $linked_array = array_merge(unserialize($metaValueImported['meta_value']), array((int)$product_id_desc));
                            $data = array(
                                'meta_value' => serialize($linked_array)
                            );
                            $this->wpPostMetaUpdate($data, $where);
                        }
                    }
                }
            }
        }
    }

    protected function _getCustomersMainQuery(){
        $id_src = $this->_notice['customers']['id_src'];
        $limit = $this->_notice['setting']['customers'];
        $query = "SELECT * FROM _DBPRF_customer_entity WHERE entity_id > {$id_src} ORDER BY entity_id ASC LIMIT {$limit}";
        return $query;
    }

    protected function _getCustomersExtraQuery($customers){
        $customerIds = $this->duplicateFieldValueFromList($customers['object'], 'entity_id');
        $customer_ids_query = $this->arrayToInCondition($customerIds);
        $ext_query = array(
            'eav_attribute' => "SELECT * FROM _DBPRF_eav_attribute WHERE entity_type_id = {$this->_notice['extend']['customer']} OR entity_type_id = {$this->_notice['extend']['customer_address']}",
            'customer_entity_datetime' => "SELECT * FROM _DBPRF_customer_entity_datetime WHERE entity_id IN {$customer_ids_query}",
            'customer_entity_decimal' => "SELECT * FROM _DBPRF_customer_entity_decimal WHERE entity_id IN {$customer_ids_query}",
            'customer_entity_int' => "SELECT * FROM _DBPRF_customer_entity_int WHERE entity_id IN {$customer_ids_query}",
            'customer_entity_text' => "SELECT * FROM _DBPRF_customer_entity_text WHERE entity_id IN {$customer_ids_query}",
            'customer_entity_varchar' => "SELECT * FROM _DBPRF_customer_entity_varchar WHERE entity_id IN {$customer_ids_query}",
            'customer_address_entity' => "SELECT * FROM _DBPRF_customer_address_entity WHERE parent_id IN {$customer_ids_query}",
            'newsletter_subscriber' => "SELECT * FROM _DBPRF_newsletter_subscriber WHERE customer_id IN {$customer_ids_query}",
        );
        return $ext_query;
    }

    protected function _getCustomersExtraRelQuery($customers, $customersExt){
        $addressIds = $this->duplicateFieldValueFromList($customersExt['object']['customer_address_entity'], 'entity_id');
        $address_id = $this->arrayToInCondition($addressIds);
        $ext_rel_query = array(
            'customer_address_entity_datetime' => "SELECT * FROM _DBPRF_customer_address_entity_datetime WHERE entity_id IN {$address_id}",
            'customer_address_entity_decimal' => "SELECT * FROM _DBPRF_customer_address_entity_decimal WHERE entity_id IN {$address_id}",
            'customer_address_entity_int' => "SELECT c.*, r.code FROM _DBPRF_customer_address_entity_int as c LEFT JOIN _DBPRF_directory_country_region as r ON c.value = r.region_id WHERE entity_id IN {$address_id}",
            'customer_address_entity_text' => "SELECT * FROM _DBPRF_customer_address_entity_text WHERE entity_id IN {$address_id}",
            'customer_address_entity_varchar' => "SELECT * FROM _DBPRF_customer_address_entity_varchar WHERE entity_id IN {$address_id}",
        );
        return $ext_rel_query;
    }

    public function getCustomerId($customer, $customersExt){
        if(LeCaMgCustom::CUSTOMER_ID){
            return $this->_custom->getCustomerIdCustom($this, $customer, $customersExt);
        }
        return $customer['entity_id'];
    }

    public function convertCustomer($customer, $customersExt){
        if(LeCaMgCustom::CUSTOMER_CONVERT){
            return $this->_custom->convertCustomerCustom($this, $customer, $customersExt);
        }
        $attribute = array();
        foreach ($customersExt['object']['eav_attribute'] as $row) {
            if ($row['entity_type_id'] == $this->_notice['extend']['customer']) {
                $attribute[$row['attribute_code']] = $row['attribute_id'];
            }
        }
        $varchar = $this->getListFromListByField($customersExt['object']['customer_entity_varchar'], 'entity_id', $customer['entity_id']);
        $int = $this->getListFromListByField($customersExt['object']['customer_entity_int'], 'entity_id', $customer['entity_id']);
        
        $f_name = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['firstname'], 'value');
        $m_name = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['middlename'], 'value');
        $l_name = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['lastname'], 'value');
        $p_name = trim(preg_replace('/\s+/', ' ',$f_name . ' ' . $m_name . ' ' . $l_name));
        $customer_data = array(
            'user_login' => $customer['email'],
            'user_pass' => $password = $this->getRowValueFromListByField($varchar, 'attribute_id', $attribute['password_hash'], 'value'),
            'user_nicename' => $customer['email'],
            'user_email' => $customer['email'],
            'user_registered' => $customer['created_at'],
            'user_status' => 0,
            'display_name' => $p_name
        );
        $meta = array(
            'nickname' => $customer['email'],
            'first_name' => $f_name,
            'last_name' => $l_name,
            'rich_editing' => "true",
            'comment_shortcuts' => "false",
            'admin_color' => "fresh",
            'use_ssl' => false,
            'show_admin_bar_front' => "true",
            'wp_capabilities' => serialize(array('customer' => true)),
            'wp_user_level' => 0,
        );
        $address = $this->getListFromListByField($customersExt['object']['customer_address_entity'], 'parent_id', $customer['entity_id']);
        if($address){
            $def_billing_id = $this->getRowValueFromListByField($int, 'attribute_id', $attribute['default_billing'], 'value');
            $def_shipping_id = $this->getRowValueFromListByField($int, 'attribute_id', $attribute['default_shipping'], 'value');
            
            if (!$def_billing_id) {
                $def_billing_id = $address[0]['entity_id'];
            }
            if (!$def_shipping_id) {
                $def_shipping_id = $address[0]['entity_id'];
            }
            $attribute_addr = array();
            foreach ($customersExt['object']['eav_attribute'] as $row) {
                if ($row['entity_type_id'] == $this->_notice['extend']['customer_address']) {
                    $attribute_addr[$row['attribute_code']] = $row['attribute_id'];
                }
            }
            $varchar_bill = $this->getListFromListByField($customersExt['object']['customer_address_entity_varchar'], 'entity_id', $def_billing_id);
            $text_bill = $this->getListFromListByField($customersExt['object']['customer_address_entity_text'], 'entity_id', $def_billing_id);
            $int_bill = $this->getListFromListByField($customersExt['object']['customer_address_entity_int'], 'entity_id', $def_billing_id);
            $varchar_ship = $this->getListFromListByField($customersExt['object']['customer_address_entity_varchar'], 'entity_id', $def_shipping_id);
            $text_ship = $this->getListFromListByField($customersExt['object']['customer_address_entity_text'], 'entity_id', $def_shipping_id);
            $int_ship = $this->getListFromListByField($customersExt['object']['customer_address_entity_int'], 'entity_id', $def_shipping_id);
            $street_bill = preg_split('/\R/', $this->getRowValueFromListByField($text_bill, 'attribute_id', $attribute_addr['street'], 'value'));
            $street_ship = preg_split('/\R/', $this->getRowValueFromListByField($text_ship, 'attribute_id', $attribute_addr['street'], 'value'));
            $meta['billing_country'] = $this->getRowValueFromListByField($varchar_bill, 'attribute_id', $attribute_addr['country_id'], 'value');
            $meta['billing_first_name'] = $this->getRowValueFromListByField($varchar_bill, 'attribute_id', $attribute_addr['firstname'], 'value');
            $meta['billing_last_name'] = $this->getRowValueFromListByField($varchar_bill, 'attribute_id', $attribute_addr['lastname'], 'value');
            $meta['billing_company'] = $this->getRowValueFromListByField($varchar_bill, 'attribute_id', $attribute_addr['company'], 'value');
            $meta['billing_address_1'] = $street_bill[0];
            $meta['billing_address_2'] = isset($street_bill[1]) ? $street_bill[1] : '';
            $meta['billing_city'] = $this->getRowValueFromListByField($varchar_bill, 'attribute_id', $attribute_addr['city'], 'value');
            $meta['billing_state'] = $this->getRowValueFromListByField($int_bill, 'attribute_id', $attribute_addr['region_id'], 'code');
            $meta['billing_postcode'] = $this->getRowValueFromListByField($varchar_bill, 'attribute_id', $attribute_addr['postcode'], 'value');
            $meta['billing_email'] = $customer['email'];
            $meta['billing_phone'] = $this->getRowValueFromListByField($varchar_bill, 'attribute_id', $attribute_addr['telephone'], 'value');
            $meta['shipping_country'] = $this->getRowValueFromListByField($varchar_ship, 'attribute_id', $attribute_addr['country_id'], 'value');
            $meta['shipping_first_name'] = $this->getRowValueFromListByField($varchar_ship, 'attribute_id', $attribute_addr['firstname'], 'value');
            $meta['shipping_last_name'] = $this->getRowValueFromListByField($varchar_ship, 'attribute_id', $attribute_addr['lastname'], 'value');
            $meta['shipping_company'] = $this->getRowValueFromListByField($varchar_ship, 'attribute_id', $attribute_addr['company'], 'value');
            $meta['shipping_address_1'] = $street_ship[0];
            $meta['shipping_address_2'] = isset($street_ship[1]) ? $street_ship[1] : '';
            $meta['shipping_city'] = $this->getRowValueFromListByField($varchar_ship, 'attribute_id', $attribute_addr['city'], 'value');
            $meta['shipping_state'] = $this->getRowValueFromListByField($int_ship, 'attribute_id', $attribute_addr['region_id'], 'code');
            $meta['shipping_postcode'] = $this->getRowValueFromListByField($varchar_ship, 'attribute_id', $attribute_addr['postcode'], 'value');
        }
        $customer_data['meta'] = $meta;
        $custom = $this->_custom->convertCustomerCustom($this, $customer, $customersExt);
        if($custom){
            $customer_data = array_merge($customer_data, $custom);
        }
        return array(
            'result' => "success",
            'data' => $customer_data
        );
    }

    protected function _getOrdersMainQuery(){
        $id_src = $this->_notice['orders']['id_src'];
        $limit = $this->_notice['setting']['orders'];
        $query = "SELECT * FROM _DBPRF_sales_flat_order WHERE entity_id > {$id_src} ORDER BY entity_id ASC LIMIT {$limit}";
        return $query;
    }

    protected function _getOrdersExtraQuery($orders){
        $orderIds = $this->duplicateFieldValueFromList($orders['object'], 'entity_id');
        $order_id_query = $this->arrayToInCondition($orderIds);
        $ext_query = array(
            'sales_flat_order_item' => "SELECT * FROM _DBPRF_sales_flat_order_item WHERE parent_item_id IS NULL AND order_id IN {$order_id_query}",
            'sales_flat_order_address' => "SELECT * FROM _DBPRF_sales_flat_order_address WHERE parent_id IN {$order_id_query}",
            'sales_flat_order_status_history' => "SELECT * FROM _DBPRF_sales_flat_order_status_history WHERE parent_id IN {$order_id_query} ORDER BY entity_id ASC",
            'sales_flat_order_payment' => "SELECT * FROM _DBPRF_sales_flat_order_payment WHERE parent_id IN {$order_id_query}",
        );
        return $ext_query;
    }

    protected function _getOrdersExtraRelQuery($orders, $ordersExt){
        return array();
    }

    public function getOrderId($order, $ordersExt){
        if(LeCaMgCustom::ORDER_ID){
            return $this->_custom->getOrderIdCustom($this, $order, $ordersExt);
        }
        return $order['entity_id'];
    }

    public function convertOrder($order, $ordersExt){
        if(LeCaMgCustom::ORDER_CONVERT){
            return $this->_custom->convertOrderCustom($this, $order, $ordersExt);
        }
        $customer_id = $this->getIdDescCustomer($order['customer_id']);
        if(!$customer_id){
            $customer_id = 0;
        }
        $order_status_src = $order['status'];
        $order_key = uniqid('order_');
        $order_data = array(
            'post_author' => $customer_id,
            'post_date' => $order['created_at'],
            'post_date_gmt' => $order['created_at'],
            'post_content' => '',
            'post_title' => 'Order',
            'post_excerpt' => '',
            'post_status' => isset($this->_notice['config']['order_status'][$order_status_src]) ? $this->_notice['config']['order_status'][$order_status_src] : " ",
            'comment_status' => 'open',
            'ping_status' => 'closed',
            'post_password' => $order_key,
            'post_name' => 'order',
            'to_ping' => '',
            'pinged' => '',
            'post_modified' => $order['updated_at'],
            'post_modified_gmt' => $order['updated_at'],
            'post_content_filtered' => '',
            'post_parent' => 0,
            'guid' => '',
            'menu_order' => 0,
            'post_type' => 'shop_order',
            'post_mime_type' => '',
            'comment_count' => ''
        );
        $address_order = $this->getListFromListByField($ordersExt['object']['sales_flat_order_address'], 'parent_id', $order['entity_id']);
        $billing = $this->getRowFromListByField($address_order, 'address_type', 'billing');
        $shipping = $this->getRowFromListByField($address_order, 'address_type', 'shipping');
        if (!$shipping) {
            $shipping = $billing;
        }
        $meta = array(
            '_order_key' => "wc_" . $order_key,
            '_order_currency' => get_woocommerce_currency(),
            '_prices_include_tax' => 'no',
            '_customer_user' => $customer_id,
            '_order_shipping' => $order['shipping_amount'],
            '_billing_country' => $billing['country_id'],
            '_billing_first_name' => $billing['firstname'],
            '_billing_last_name' => $billing['lastname'],
            '_billing_company' => $billing['company'],
            '_billing_address_1' => $billing['street'],
            '_billing_address_2' => '',
            '_billing_city' => $billing['city'],
            '_billing_state' => $billing['region'],
            '_billing_postcode' => $billing['postcode'],
            '_billing_email' => $billing['email'],
            '_billing_phone' => $billing['telephone'],
            '_shipping_country' => $shipping['country_id'],
            '_shipping_first_name' => $shipping['firstname'],
            '_shipping_last_name' => $shipping['lastname'],
            '_shipping_company' => $shipping['company'],
            '_shipping_address_1' => $shipping['street'],
            '_shipping_address_2' => '',
            '_shipping_city' => $shipping['city'],
            '_shipping_state' => $shipping['region'],
            '_shipping_postcode' => $shipping['postcode'],
            '_payment_method' => 'cheque',
            '_payment_method_title' => $this->getRowValueFromListByField($ordersExt['object']['sales_flat_order_payment'], 'parent_id', $order['entity_id'], 'method'),
            '_cart_discount' => $order['discount_amount'],
            '_cart_discount_tax' => 0,
            '_order_tax' => $order['tax_amount'],
            '_order_shipping_tax' => 0,
            '_order_total' => $order['grand_total'],
        );
        $order_data['meta'] = $meta;
        $custom = $this->_custom->convertOrderCustom($this, $order, $ordersExt);
        if($custom){
            $order_data = array_merge($order_data, $custom);
        }
        return array(
            'result' => "success",
            'data' => $order_data
        );
    }

    public function afterSaveOrder($order_id_desc, $convert, $order, $ordersExt){
        if(parent::afterSaveOrder($order_id_desc, $convert, $order, $ordersExt)){
            return false;
        }
        $guid = site_url("?post_type=shop_order&p=" . $order_id_desc);
        $this->updateTable(self::WP_POSTS, array(
            'guid' => $guid
        ), array(
            'ID' => $order_id_desc
        ));
        $orderProducts = $this->getListFromListByField($ordersExt['object']['sales_flat_order_item'], 'order_id', $order['entity_id']);
        foreach($orderProducts as $ord_pro){
            $order_item_data = array(
                'order_item_name' => $ord_pro['name'],
                'order_item_type' => 'line_item',
                'order_id' => $order_id_desc
            );
            $order_item_id = $this->wooOrderItem($order_item_data);
            if(!$order_item_id){
                continue ;
            }
            $product_id = $this->getIdDescProduct($ord_pro['product_id']);
            if(!$product_id){
                $product_id = 0;
            }
            $meta = array(
                '_qty' => $ord_pro['qty_ordered'],
                '_tax_class' => '',
                '_product_id' => $product_id,
                '_variation_id' => '',
                '_line_subtotal' => $ord_pro['row_total'],
                '_line_total' => $ord_pro['row_total_incl_tax'],
                '_line_subtotal_tax' => $ord_pro['tax_amount'],
                '_line_tax' => $ord_pro['tax_amount'],
                '_line_tax_data' => serialize(array(
                    'total' => 0,
                    'subtotal' => 0
                )),
            );
            $proOption = $ord_pro['product_options'];
            if($proOption){
                $options = unserialize($proOption);
                if (isset($options['options'])) {
                    foreach ($options['options'] as $pro_attr) {
                        $key = $pro_attr['label'];
                        $value = $pro_attr['value'];
                        $meta[$key] = $value;
                    }
                }
                if (isset($options['attributes_info'])) {
                    foreach ($options['attributes_info'] as $pro_attr) {
                        $key = $pro_attr['label'];
                        $value = $pro_attr['value'];
                        $meta[$key] = $value;
                    }
                }
            }
            foreach($meta as $meta_key => $meta_value){
                $data = array(
                    'order_item_id' => $order_item_id,
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value
                );
                $this->wooOrderMeta($data);
            }
        }
        $order_item_data = array(
            'order_item_name' => $order['shipping_description'],
            'order_item_type' => "shipping",
            'order_id' => $order_id_desc
        );
        $order_item_id = $this->wooOrderItem($order_item_data);
        if($order_item_id){
            $meta = array(
                'method_id' => "flat_rate",
                'cost' => $order['shipping_amount'],
                'taxes' => serialize(array())
            );
            foreach($meta as $meta_key => $meta_value){
                $data = array(
                    'order_item_id' => $order_item_id,
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value
                );
                $this->wooOrderMeta($data);
            }
        }
        if(intval($order['tax_amount'])){
            $tax_title = 'Tax Amount';
            $order_item_data = array(
                'order_item_name' => $tax_title,
                'order_item_type' => "tax",
                'order_id' => $order_id_desc
            );
            $order_item_id = $this->wooOrderItem($order_item_data);
            if($order_item_id){
                $order_items_meta = array(
                    array(
                        'order_item_id' => $order_item_id,
                        'meta_key' => 'rate_id',
                        'meta_value' => ''
                    ),
                    array(
                        'order_item_id' => $order_item_id,
                        'meta_key' => 'label',
                        'meta_value' => $tax_title
                    ),
                    array(
                        'order_item_id' => $order_item_id,
                        'meta_key' => 'compound',
                        'meta_value' => ''
                    ),
                    array(
                        'order_item_id' => $order_item_id,
                        'meta_key' => 'shipping_tax_amount',
                        'meta_value' => ''
                    ),
                    array(
                        'order_item_id' => $order_item_id,
                        'meta_key' => 'tax_amount',
                        'meta_value' => $order['tax_amount']
                    ),
                );
                foreach($order_items_meta as $order_item_meta){
                    $this->wooOrderMeta($order_item_meta);
                }
            }
        }
        $ordHistory = $this->getListFromListByField($ordersExt['object']['sales_flat_order_status_history'], 'parent_id', $order['entity_id']);
        $payment_method = $this->getRowValueFromListByField($ordersExt['object']['sales_flat_order_payment'], 'parent_id', $order['entity_id'], 'method');
        foreach($ordHistory as $key => $ord_his){
            $cmt_data = array(
                'comment_post_ID' => $order_id_desc,
                'comment_author' => "WooCommerce",
                'comment_author_email' => "",
                'comment_author_url' => "",
                'comment_author_IP' => "",
                'comment_date' => $ord_his['created_at'],
                'comment_date_gmt' => $ord_his['created_at'],
                'comment_karma' => 0,
                'comment_approved' => true,
                'comment_agent' => "WooCommerce",
                'comment_type' => "order_note",
                'comment_parent' => 0,
                'user_id' => 0
            );
            if($key == 0){
                $cmt_data['comment_content'] = "<b>Reference order #".$order['increment_id']."</b><br />";
                $cmt_data['comment_content'] .= "<b>Payment method: </b>".$payment_method."<br />";
                $cmt_data['comment_content'] .= "<b>Shipping method: </b> ".$order['shipping_description']."<br /><br />";
                $cmt_data['comment_content'] .= $ord_his['comment'];
            } else {
                $cmt_data['comment_content'] = $ord_his['comment'];
            }
            $cmt_id = $this->wpComment($cmt_data);
            if($cmt_id){
                $meta = array(
                    'comment_id' => $cmt_id,
                    'meta_key' => 'is_customer_note',
                    'meta_value' => $ord_his['is_customer_notified'] ? 1 : 0
                );
                $this->wpCommentMeta($meta);
            }
        }
    }

    protected function _getReviewsMainQuery(){
        $id_src = $this->_notice['reviews']['id_src'];
        $limit = $this->_notice['setting']['reviews'];
        $query = "SELECT * FROM _DBPRF_review WHERE review_id > {$id_src} ORDER BY review_id ASC LIMIT {$limit}";
        return $query;
    }

    protected function _getReviewsExtraQuery($reviews){
        $reviewIds = $this->duplicateFieldValueFromList($reviews['object'], 'review_id');
        $review_id_query = $this->arrayToInCondition($reviewIds);
        $ext_query = array(
            'review_detail' => "SELECT * FROM _DBPRF_review_detail WHERE review_id IN {$review_id_query}",
            'rating_option_vote' => "SELECT * FROM _DBPRF_rating_option_vote WHERE review_id IN {$review_id_query}",
        );
        return $ext_query;
    }

    protected function _getReviewsExtraRelQuery($reviews, $reviewsExt){
        return array();
    }

    public function getReviewId($review, $reviewsExt){
        if(LeCaMgCustom::REVIEW_ID){
            return $this->_custom->getReviewIdCustom($this, $review, $reviewsExt);
        }
        return $review['review_id'];
    }

    public function convertReview($review, $reviewsExt){
        if(LeCaMgCustom::REVIEW_CONVERT){
            return $this->_custom->convertReviewCustom($this, $review, $reviewsExt);
        }
        $product_id = $this->getIdDescProduct($review['entity_pk_value']);
        if(!$product_id){
            return array(
                'result' => 'warning',
                'msg' => $this->consoleWarning("Review Id = " . $review['review_id'] . " import failed. Error: Product Id = " . $review['entity_pk_value'] . " not imported!")
            );
        }
        $review_detail = $this->getRowFromListByField($reviewsExt['object']['review_detail'], 'review_id', $review['review_id']);
        $review_data = array(
            'comment_post_ID' => $product_id,
            'comment_author' => $review_detail['nickname'],
            'comment_date' => $review['created_at'],
            'comment_date_gmt' => $review['created_at'],
            'comment_content' => $review_detail['detail'],
            'comment_karma' => 0,
            'comment_approved' => $review['status_id'] == 1 ? 1 : 0,
            'comment_parent' => 0,
        );
        $customer_id = $review_detail['customer_id'] ? $this->getIdDescCustomer($review_detail['customer_id']) : null;
        if(!$customer_id){
            $customer_id = 0;
        }
        $review_data['user_id'] = $customer_id;
        $rating_votes = $this->getListFromListByField($reviewsExt['object']['rating_option_vote'], 'review_id', $review['review_id']);
        if ($rating_votes) {
            $rating = $i = 0;
            foreach ($rating_votes as $row) {
                $rating += $row['value'];
                $i++;
            }
            $rating_avg = $rating / $i;
            $review_data['meta'] = array(
                'rating' => $rating_avg
            );
        }
        $custom = $this->_custom->getReviewIdCustom($this, $review, $reviewsExt);
        if($custom){
            $review_data = array_merge($review_data, $custom);
        }
        return array(
            'result' => "success",
            'data' => $review_data
        );
    }

    /**
     * TODO: Extends
     */

    protected function _importCategoryParent($parent_id){
        $categories = $this->getConnectorData($this->getUrlConnector('query'), array(
            'query' => "SELECT * FROM _DBPRF_catalog_category_entity WHERE entity_id = {$parent_id} AND level > 1"
        ));
        if(!$categories || $categories['result'] != 'success'){
            return $this->errorConnector();
        }
        $categoriesExt = $this->getCategoriesExtra($categories);
        if($categoriesExt['result'] != 'success'){
            return $categoriesExt;
        }
        $category = $categories['object'][0];
        $convert = $this->convertCategory($category, $categoriesExt);
        if($convert['result'] != 'success'){
            return array(
                'result' => 'warning',
            );
        }
        $data = $convert['data'];
        $category_ipt = $this->category($data, true);
        if($category_ipt){
            $this->categorySuccess($parent_id, $category_ipt['term_id'], $category_ipt['term_taxonomy_id']);
            $this->afterSaveCategory($category_ipt['term_id'], $data, $category, $categoriesExt);
            return array(
                'result' => 'success',
                'id_desc' => $category_ipt['term_id']
            );
        }
        return array(
            'result' => 'warning'
        );
    }
    
    protected function _getDefaultLanguage($stores) {
        if (isset($stores['0']['sort_order'])) {
            $sort_order = $stores['0']['sort_order'];
        } else {
            return 1;
        }
        foreach ($stores as $store) {
            if ($store['sort_order'] < $sort_order) {
                $sort_order = $store['sort_order'];
            }
        }
        $default_lang = 1;
        foreach ($stores as $store) {
            if ($store['sort_order'] == $sort_order) {
                $default_lang = $store['store_id'];
                break;
            }
        }
        return $default_lang;
    }
    
    protected function _changeImgSrcInText($html) {
        $html = parent::_changeImgSrcInText($html);
        if (!$this->_notice['config']['add_option']['img_des']) {
            return $html;
        }
        $links = array();
        preg_match_all('/<img[^>]+>/i', $html, $img_tags);
        foreach ($img_tags[0] as $img) {
            preg_match('/src=["\']({{media url=(.*?)}})["\']/', $img, $src);
            if (!isset($src[1])) {
                continue;
            }
            $links[] = $src[1];
        }
        foreach ($links as $link) {
            preg_match('/{{media url="(.*?)"}}/', $link, $img);
            if (!isset($img[1])) {
                continue;
            }
            $new_link = $this->_getImgDesUrlImport($this->_notice['config']['cart_url'] . "/media/" . $img[1]);
            $html = str_replace($link, $new_link, $html);
        }
        return $html;
    }
    
    protected function _getCategoryParentId($path) {
        $array = explode("/", $path);
        $array = array_reverse($array);
        return $array[1];
    }
}