<?php

/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgCart
{
    const CONNECTOR_SUFFIX  = 'woocommerce_connector/connector.php';
    const POST_METHOD       = 'POST';
    const GET_METHOD        = 'GET';
    const IMG_DIR           = '/cm';
    const TYPE_TAX          = 'tax';
    const TYPE_TAX_RATE     = 'tax_rate';
    const TYPE_MANUFACTURER = 'manufacturer';
    const TYPE_CATEGORY     = 'category';
    const TYPE_PRODUCT      = 'product';
    const TYPE_VARIABLE     = 'product_variable';
    const TYPE_ATTR         = 'attr';
    const TYPE_ATTR_VALUE   = 'attr_value';
    const TYPE_TAG          = 'tag';
    const TYPE_CUSTOMER     = 'customer';
    const TYPE_ORDER        = 'order';
    const TYPE_REVIEW       = 'review';
    const TYPE_SHIPPING     = 'shipping';
    const TYPE_PAGE         = 'page';
    const TYPE_POST         = 'post';
    const TYPE_FORMAT       = 'format';
    const TYPE_COMMENT      = 'comment';
    const WP_COMMENT_META   = 'commentmeta';
    const WP_COMMENT        = 'comments';
    const WP_LINKS          = 'links';
    const WP_OPTIONS        = 'options';
    const WP_POST_META      = 'postmeta';
    const WP_POSTS          = 'posts';
    const WP_TERM           = 'terms';
    const WP_TERM_RELATION  = 'term_relationships';
    const WP_TERM_TAXONOMY  = 'term_taxonomy';
    const WP_USER_META      = 'usermeta';
    const WP_USERS          = 'users';
    const WOO_ATTR_TAXONOMY = 'woocommerce_attribute_taxonomies';
    const WOO_DOWNLOAD_PERMISSION = 'woocommerce_downloadable_product_permissions';
    const WOO_ORDER_META    = 'woocommerce_order_itemmeta';
    const WOO_ORDER_ITEM    = 'woocommerce_order_items';
    const WOO_TAX_RATE      = 'woocommerce_tax_rates';
    const WOO_TAX_RATE_LOCATION = 'woocommerce_tax_rate_locations';
    const WOO_TERM_META     = 'woocommerce_termmeta';
    const LECM = 'd9180594744f870aeefb086982e980bb';

    protected $_db;
    protected $_notice;
    protected $_cart_url;
    protected $_cart_token = null;
    protected $_custom = null;
    protected $_seo = null;
    protected $_upload_dir;
    protected $_upload_url;
    protected $_user_id;
    protected $_productType;
    protected $_woo_term_meta_table = 'woocommerce_termmeta';

    protected $_limit_demo = array(
        'taxes' => 10,
        'manufacturers' => 10,
        'categories' => 10,
        'products' => 10,
        'customers' => 10,
        'orders' => 10,
        'reviews' => 0,
        'pages' => 10,
        'postCat' => 10,
        'posts' => 10,
        'comments' => 0,
    );
    protected $_connect = array('oscommerce', 'zencart', 'virtuemart', 'woocommerce', 'xtcommerce', 'opencart', 'xcart', 'loaded', 'wpecommerce', 'cscart', 'prestashop', 'magento', 'cart66', 'marketpress', 'oxideshop','wpestore', 'jigoshop', 'phpurchase', 'pinnaclecart', 'wponlinestore');
    protected $_api = array('bigcommerce', 'shopify');
    protected $_upload = array('volusion');

    public function __construct()
    {
        global $woocommerce;
        $woo_version = $woocommerce->version;
        if($this->_convertVersion($woo_version, 2) > 260){
            $this->_woo_term_meta_table = 'termmeta';
        }
    }

    /**
     * TODO: Router
     */

    public function getCart($cart_type, $cart_version = '')
    {
        if (!$cart_type) {
            return "cart";
        }
        if ($cart_type == 'oscommerce') {
            return "cart_oscommerce";
        }
        if ($cart_type == 'zencart') {
            return "cart_zencart";
        }
        if ($cart_type == 'xtcommerce') {
            if ($this->_convertVersion($cart_version, 2) < 400) {
                return 'cart_xtcommercev3';
            } else {
                return 'cart_xtcommercev4';
            }
        }
        if ($cart_type == 'magento') {
            if($this->_convertVersionMagento($cart_version, 2) > 199){
                return 'cart_magento21';
            }elseif ($this->_convertVersionMagento($cart_version, 2) > 149) {
                return 'cart_magento19';
            } elseif ($this->_convertVersionMagento($cart_version, 2) > 140) {
                return 'cart_magento14';
            } else {
                return 'cart_magento13';
            }
        }
        if ($cart_type == 'opencart') {
            if ($this->_convertVersion($cart_version, 2) > 149) {
                return 'cart_opencartv15';
            } else {
                return 'cart_opencartv14';
            }
        }
        if ($cart_type == 'wpecommerce') {
            $wp_ver = $this->_convertVersion($cart_version, 2);
            if($wp_ver < 350){
                return 'cart_wpecommercev38';
            }
            if ($wp_ver < 370) {
                return 'cart_wpecommercev36';
            } elseif ($wp_ver < 380) {
                return 'cart_wpecommercev37';
            } else {
                return 'cart_wpecommercev38';
            }
        }
        if ($cart_type == 'woocommerce') {
            if ($this->_convertVersion($cart_version, 2) < 200) {
                return 'cart_woocommercev1';
            } else {
                return 'cart_woocommercev2';
            }
        }
        if ($cart_type == 'prestashop') {
            $pv = $this->_convertVersion($cart_version, 2);
            if ($pv > 149) {
                return 'cart_prestashopv16';
            } else if (139 < $pv && $pv < 150) {
                return 'cart_prestashopv14';
            } else {
                return 'cart_prestashopv13';
            }
        }
        if ($cart_type == 'virtuemart') {
            if ($this->_convertVersion($cart_version, 2) < 200) {
                return 'cart_virtuemartv1';
            } else {
                return 'cart_virtuemartv2';
            }
        }
        if ($cart_type == 'cscart') {
            if ($this->_convertVersion($cart_version, 2) > 299) {
                return 'cart_cscartv4';
            } else {
                return 'cart_cscartv2';
            }
        }
        if ($cart_type == 'cart66') {
            return 'cart_cart66';
        }
        if ($cart_type == 'marketpress') {
            return 'cart_marketpress';
        }
        if ($cart_type == 'xcart') {
            $xc_ver = $this->_convertVersion($cart_version, 2);
            if ($xc_ver < 440) {
                return 'cart_xcartv43';
            } elseif ($xc_ver < 450) {
                return 'cart_xcartv44';
            } elseif ($xc_ver < 500) {
                return 'cart_xcartv46';
            } elseif ($xc_ver < 510){
                return 'cart_xcartv5';
            }
            return 'cart_xcartv51';
        }
        if ($cart_type == 'oxideshop') {
            $this->_notice['config']['cart_version'] = $this->_convertVersion($cart_version, 2);
            return 'cart_oxideshop';
        }
        if ($cart_type == 'wpestore'){
            return 'cart_wpestore';
        }

        if($cart_type == 'shopp'){
            return 'cart_shopp';
        }

        if($cart_type == 'hikashop'){
            $hika_ver = $this->_convertVersion($cart_version, 2);
            if($hika_ver < 300){
                return 'cart_hikashopv2';
            }else{
                return 'cart_hikashopv3';
            }
        }

        if ($cart_type == 'jigoshop'){
            return 'cart_jigoshop';
        }

        if ($cart_type == 'phpurchase'){
            return 'cart_phpurchase';
        }

        if ($cart_type == 'loaded'){
            $cre_ver = $this->_convertVersion($cart_version, 2);
            if ($cre_ver >= 700) {
                return 'cart_loadedcommercev7';
            } else {
                return 'cart_loadedcommercev6';
            }
        }

        if($cart_type == 'cubecart') {
            if($this->_convertVersion($cart_version, 2) > 599){
                return 'cart_cubecartv6';
            } elseif ($this->_convertVersion($cart_version, 2) > 499) {
                return 'cart_cubecartv5';
            } elseif ($this->_convertVersion($cart_version, 2) > 399) {
                return 'cart_cubecartv4';
            } else {
                return 'cart_cubecartv3';
            }
        }

        if ($cart_type == 'pinnaclecart'){
            return 'cart_pinnaclecart';
        }

        if ($cart_type == 'interspire'){
            return 'cart_interspirev6';
        }
        if ($cart_type == 'wponlinestore'){
            return 'cart_wponlinestore';
        }
        if ($cart_type == 'drupalcommerce'){
            return 'cart_drupalcart1x';
        }

        return "cart";
    }

    /**
     * Convert version from string to int
     *
     * @param string $v : String of version split by dot
     * @param int $num : number of result return
     * @return int
     */
    protected function _convertVersion($v, $num)
    {
        $digits = @explode(".", $v);
        $version = 0;
        if (is_array($digits)) {
            foreach ($digits as $k => $v) {
                if ($k <= $num) {
                    $version += (substr($v, 0, 1) * pow(10, max(0, ($num - $k))));
                }
            }
        }
        return $version;
    }

    protected function _convertVersionMagento($v, $num)
    {
        $digits = @explode(".", $v);
        $version = 0;
        if (is_array($digits)) {
            foreach ($digits as $k => $v) {
                if ($k <= $num) {
                    $version += ($v * pow(10, max(0, ($num - $k))));
                }
            }
        }
        return $version;
    }

    public function getSetupType($cart_type)
    {
        if (in_array($cart_type, $this->_connect)) {
            return "connect";
        }
        if (in_array($cart_type, $this->_api)) {
            return "api";
        }
        if (in_array($cart_type, $this->_upload)) {
            return "upload";
        }
    }

    /**
     * TODO: Notice
     */

    public function setNotice($notice, $custom = true)
    {
        $this->_notice = $notice;
        $this->_cart_url = $notice['config']['cart_url'];
        $this->_cart_token = $notice['config']['cart_token'];
        if ($custom) {
            $this->_custom = LeCaMg::getModel('custom');
        }
    }

    public function getNotice()
    {
        return $this->_notice;
    }

    public function defaultNotice()
    {
        $setting = get_option(LeCaMg::LECAMG_SETTING);
        if (!is_array($setting)) {
            $setting = unserialize($setting);
        }
        $setting = $this->validateSetting($setting);
        return array(
            'config' => array(
                'cart_type' => '',
                'cart_url' => '',
                'cart_token' => '',
                'cart_version' => '',
                'table_prefix' => '',
                'charset' => '',
                'image_category' => '',
                'image_product' => '',
                'image_manufacturer' => '',
                'cat' => '',
                'cat_data' => array(),
                'languages' => '',
                'language_data' => array(),
                'currencies' => array(),
                'currency_data' => array(),
                'order_status' => array(),
                'order_status_data' => array(),
                'countries' => array(),
                'country_data' => array(),
                'customer_group' => array(),
                'customer_group_data' => array(),
                'default_lang' => '',
                'default_currency' => '',
                'root_category_id' => '',
                'config_support' => array(
                    'category_map' => false,
                    'lang_map' => true,
                    'currency_map' => false,
                    'order_status_map' => true,
                    'country_map' => false,
                    'customer_group_map' => true
                ),
                'import_support' => array(
                    'taxes' => true,
                    'manufacturers' => false,
                    'categories' => true,
                    'products' => true,
                    'customers' => true,
                    'orders' => true,
                    'reviews' => true,
                    'posts' => false,
                    'comments' => false,
                    'pages' => false,
                    'postCat' => false,
                ),
                'import' => array(
                    'taxes' => false,
                    'manufacturers' => false,
                    'categories' => false,
                    'products' => false,
                    'customers' => false,
                    'orders' => false,
                    'reviews' => false,
                    'posts' => false,
                    'comments' => false,
                    'pages' => false,
                    'postCat' => false,
                ),
                'add_option' => array(
                    'add_new' => false,
                    'clear_shop' => false,
                    'img_des' => false,
                    'seo' => false,
                    'seo_plugin' => ''
                ),
                'limit' => 0
            ),
            'clear_info' => array(
                'result' => 'process',
                'function' => '_clearProducts',
                'msg' => '',
                'limit' => 20
            ),
            'taxes' => array(
                'total' => 0,
                'imported' => 0,
                'id_src' => 0,
                'error' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'manufacturers' => array(
                'total' => 0,
                'imported' => 0,
                'id_src' => 0,
                'error' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'categories' => array(
                'total' => 0,
                'imported' => 0,
                'id_src' => 0,
                'error' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'products' => array(
                'total' => 0,
                'imported' => 0,
                'id_src' => 0,
                'error' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'customers' => array(
                'total' => 0,
                'imported' => 0,
                'id_src' => 0,
                'error' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'orders' => array(
                'total' => 0,
                'imported' => 0,
                'id_src' => 0,
                'error' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'reviews' => array(
                'total' => 0,
                'imported' => 0,
                'id_src' => 0,
                'error' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'posts' => array(
                'total' => 0,
                'imported' => 0,
                'id_src' => 0,
                'error' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'comments' => array(
                'total' => 0,
                'imported' => 0,
                'id_src' => 0,
                'error' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'pages' => array(
                'total' => 0,
                'imported' => 0,
                'id_src' => 0,
                'error' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'postCat' => array(
                'total' => 0,
                'imported' => 0,
                'id_src' => 0,
                'error' => 0,
                'point' => 0,
                'time_start' => 0,
                'finish' => false
            ),
            'setting' => $setting,
            'is_running' => false,
            'fn_resume' => 'importTaxes',
            'extends' => array(),
            'msg_start' => '',
        	'curl' => array(
        		'useragent' => false,
        	),
        );
    }

    public function validateSetting($settings)
    {
        $defaultSetting = array(
            'taxes' => 4,
            'manufacturers' => 4,
            'categories' => 4,
            'products' => 4,
            'customers' => 4,
            'orders' => 4,
            'reviews' => 4,
            'delay' => '0.05',
            'retry' => 30,
            'prefix' => '',
            'license' => ''
        );
        foreach($settings as $key => $value){
            if(in_array($key, array('taxes', 'manufacturers', 'categories', 'products', 'customers', 'orders', 'reviews', 'retry'))){
                if(!$value || !is_int($value)){
                    $value = $defaultSetting[$key];
                }
            }
            if(in_array($key, array('delay'))){
                if(!$value || !is_float($value)){
                    $value = $defaultSetting[$key];
                }
            }
            if(!$value){
                $value = $defaultSetting[$key];
            }
            $settings[$key] = $value;
        }
        return $settings;
    }

    public function saveUserNotice($user_id, $notice)
    {
        if (!$user_id) {
            return false;
        }
        $result = false;
        if (is_array($notice)) {
            $notice = serialize($notice);
        }
        $exists = $this->selectTableRow(LeCaMg::TABLE_USER, array('id' => $user_id));
        if (!$exists) {
            $result = $this->insertTable(LeCaMg::TABLE_USER, array(
                'id' => $user_id,
                'notice' => $notice
            ));
        } else {
            $result = $this->updateTable(LeCaMg::TABLE_USER, array(
                'notice' => $notice,
            ), array(
                'id' => $user_id
            ));
        }
        return $result;
    }

    public function saveRecentNotice($notice)
    {
        $result = false;
        if (is_array($notice)) {
            $notice = serialize($notice);
        }
        $exists = $this->selectTableRow(LeCaMg::TABLE_RECENT, array(
            'domain' => $this->_cart_url
        ));
        if (!$exists) {
            $result = $this->insertTable(LeCaMg::TABLE_RECENT, array(
                'domain' => $this->_cart_url,
                'notice' => $notice
            ));
        } else {
            $result = $this->updateTable(LeCaMg::TABLE_RECENT, array(
                'notice' => $notice
            ), array(
                'domain' => $this->_cart_url
            ));
        }
        return $result;
    }

    public function getUserNotice($user_id)
    {
        if (!$user_id) {
            return false;
        }
        $notice = false;
        $result = $this->selectTableRow(LeCaMg::TABLE_USER, array(
            'id' => $user_id
        ));
        if ($result && $result['notice']) {
            $notice = unserialize($result['notice']);
        }
        return $notice;
    }

    public function getRecentNotice()
    {
        if (!$this->_cart_url) {
            return false;
        }
        $notice = false;
        $result = $this->selectTableRow(LeCaMg::TABLE_RECENT, array(
            'domain' => $this->_cart_url
        ));
        if ($result && $result['notice']) {
            $notice = unserialize($result['notice']);
        }
        return $notice;
    }

    public function deleteUserNotice($user_id)
    {
        if (!$user_id) {
            return true;
        }
        return $this->deleteTable(LeCaMg::TABLE_USER, array(
            'id' => $user_id
        ));
    }

    /**
     * TODO: Import
     */

    public function route($check = false)
    {
        if ($check){
            $check_connect = $this->_checkConnector();
            if ($check_connect['result'] != "success") {
                return $check_connect;
            }
        }
        $cart_type = $this->_notice['config']['cart_type'];
        $cart_version = $this->_notice['config']['cart_version'];
        $cart = $this->getCart($cart_type, $cart_version);
        return array(
            'result' => 'success',
            'msg' => '',
            'cart' => $cart
        );
    }

    public function displayConfig()
    {
        wc_delete_product_transients();
        wc_delete_shop_order_transients();
        delete_transient('wc_attribute_taxonomies');
//        $this->_notice['config']['import_support']['posts'] = true;
//        $this->_notice['config']['import_support']['comments'] = true;
//        $this->_notice['config']['import_support']['pages'] = true;
//        $this->_notice['config']['import_support']['postCat'] = true;
        return array(
            'result' => "success"
        );
    }

    public function displayConfirm()
    {
        $configs = array('languages', 'currencies', 'order_status', 'cat', 'countries', 'customer_group');
        foreach ($configs as $cfg) {
            $this->_notice['config'][$cfg] = isset($_POST[$cfg]) ? $_POST[$cfg] : array();
        }
        $this->_notice['config']['cat'] = 0;
        $imports = array('taxes', 'manufacturers', 'categories', 'products', 'customers', 'orders', 'reviews', 'pages', 'postCat', 'posts', 'comments');
        foreach ($imports as $import) {
            if (isset($_POST[$import]) && $_POST[$import]) {
                $this->_notice['config']['import'][$import] = true;
            } else {
                $this->_notice['config']['import'][$import] = false;
            }
        }
        $addOption = array('add_new', 'clear_shop', 'img_des', 'seo');
        foreach ($addOption as $add_opt) {
            if (isset($_POST[$add_opt]) && $_POST[$add_opt]) {
                $this->_notice['config']['add_option'][$add_opt] = true;
            } else {
                $this->_notice['config']['add_option'][$add_opt] = false;
            }
        }
        $this->_notice['config']['add_option']['seo_plugin'] = isset($_POST['seo_plugin']) ? $_POST['seo_plugin'] : '';
        $this->_notice['config']['root_category_id'] = $this->_notice['config']['cat'];
        return array(
            'result' => "success"
        );
    }

    public function displayImport()
    {
        $response = $this->_defaultResponse();
        $data = $this->getConnectorData($this->getUrlConnector('query'), array(
            'serialize' => true,
            'query' => serialize(array(
                'pages' => "SELECT COUNT(1) FROM _DBPRF_posts WHERE post_type = 'page' AND post_status NOT IN ('inherit','auto-draft') AND ID > {$this->_notice['pages']['id_src']}",
                'postCat' => "SELECT COUNT(1) FROM _DBPRF_term_taxonomy WHERE taxonomy = 'category' AND term_id > {$this->_notice['categories']['id_src']}",
                'posts' => "SELECT COUNT(1) FROM _DBPRF_posts WHERE post_type = 'post' AND post_status NOT IN ('inherit','auto-draft') AND ID > {$this->_notice['posts']['id_src']}",
                'comments' => "SELECT COUNT(1) FROM _DBPRF_comments AS cm,_DBPRF_posts AS p WHERE cm.comment_post_ID = p.ID AND p.post_type = 'post' AND cm.comment_ID > {$this->_notice['comments']['id_src']}",
            ))
        ));
        if (!$data || $data['result'] != 'success') {
            return $this->errorConnector(false);
        }
        $real_totals = array();
        foreach ($data['object'] as $type => $rows) {
            if ($type == 'taxes' && isset($rows[0]['option_value'])) {
                $tax_rules = $this->_createTaxClassFromString($rows[0]['option_value'], $this->_notice['taxes']['id_src']);
                $total = count($tax_rules);
            } else {
                $total = $this->arrayToCount($rows);
            }
            $real_totals[$type] = $total;
        }
        $totals = $this->_limit($real_totals);
        $recent = $this->getRecentNotice();
        foreach ($totals as $type => $count) {
            $this->_notice[$type]['total'] = $count;
        }
        if (!$this->_notice['config']['add_option']['add_new']) {
            $delete = $this->deleteImport();
            if ($delete === false) {
                return $this->errorDatabase(false);
            }
        }
        $this->_custom->displayImportCustom($this);
        $response['result'] = "success";
        return $response;
    }

    public function clearData()
    {
        $db = $this->getDB();
        //clear product
        $product_exists = $this->_checkProductExists();
        if ($product_exists) {
            $table_post = $db->prefix . self::WP_POSTS;
            $table_post_meta = $db->prefix . self::WP_POST_META;
            $del_product_query = "DELETE FROM `" . $table_post . "` WHERE post_type IN ( 'product', 'product_variation', 'post', 'page')";
            $del_product_result = $db->query($del_product_query);
            if (!$del_product_result) {
                return $this->errorClear('product');
            }
            $del_product_meta_query = "DELETE " . $table_post_meta . " FROM " . $table_post_meta . " LEFT JOIN " . $table_post . " ON " . $table_post . ".ID = " . $table_post_meta . ".post_id WHERE " . $table_post . ".ID IS NULL";
            $del_product_meta_result = $db->query($del_product_meta_query);
            if ($del_product_meta_result === false) {
                echo 'Error Clear Product';
            }
        }
        //clear post
        $post_exists = $this->_checkPostExists();
        if ($post_exists) {
            $table_post = $db->prefix . self::WP_POSTS;
            $table_post_meta = $db->prefix . self::WP_POST_META;
            $del_post_query = "DELETE FROM `" . $table_post . "` WHERE post_type = 'post' ";
            $del_post_result = $db->query($del_post_query);
            if (!$del_post_result) {
                return $this->errorClear('post');
            }
            $del_post_meta_query = "DELETE " . $table_post_meta . " FROM " . $table_post_meta . " LEFT JOIN " . $table_post . " ON " . $table_post . ".ID = " . $table_post_meta . ".post_id WHERE " . $table_post . ".ID IS NULL";
            $del_post_meta_result = $db->query($del_post_meta_query);
            if ($del_post_meta_result === false) {
                echo 'Error Clear Post';
            }
        }
        //clear page
        $page_exists = $this->_checkPageExists();
        if ($page_exists) {
            $table_page = $db->prefix . self::WP_POSTS;
            $table_page_meta = $db->prefix . self::WP_POST_META;
            $del_page_query = "DELETE FROM `" . $table_page . "` WHERE post_type = 'page' ";
            $del_page_result = $db->query($del_page_query);
            if (!$del_page_result) {
                return $this->errorClear('post');
            }
            $del_page_meta_query = "DELETE " . $table_page_meta . " FROM " . $table_post_meta . " LEFT JOIN " . $table_post . " ON " . $table_post . ".ID = " . $table_post_meta . ".post_id WHERE " . $table_post . ".ID IS NULL";
            $del_page_meta_result = $db->query($del_page_meta_query);
            if ($del_page_meta_result === false) {
                echo 'Error Clear Page';
            }
        }
        //clear attribute
        $del_transient_attr_query = "DELETE FROM `" . $db->prefix . self::WP_OPTIONS . "` WHERE option_name = '_transient_wc_attribute_taxonomies'";
        $db->query($del_transient_attr_query);
        $product_attribute_query = "SELECT * FROM `" . $db->prefix . self::WOO_ATTR_TAXONOMY . "` ORDER BY attribute_id";
        $product_attribute_result = $db->get_results($product_attribute_query, ARRAY_A);
        if ($product_attribute_result) {
            $attributeId = $this->duplicateFieldValueFromList($product_attribute_result, 'attribute_id');
            $attributeName = $this->duplicateFieldValueFromList($product_attribute_result, 'attribute_name');
            $attributeName = array_map(array($this, 'addAttributePrefix'), $attributeName);
            $attribute_id_con = $this->arrayToInCondition($attributeId, false);
            $del_attribute_query = "DELETE FROM `" . $db->prefix . self::WOO_ATTR_TAXONOMY . "` WHERE attribute_id IN " . $attribute_id_con;
            $del_attribute_result = $db->query($del_attribute_query);
            if ($del_attribute_result === false) {
                echo 'Error Clear Product';
            }
            $attribute_name_con = $this->arrayToInCondition($attributeName, false);
            $wp_term_table = $db->prefix . self::WP_TERM;
            $wp_taxonomy_table = $db->prefix . self::WP_TERM_TAXONOMY;
            $del_term_query = "DELETE " . $wp_taxonomy_table . ", " . $wp_term_table . " FROM " . $wp_taxonomy_table . " LEFT JOIN " . $wp_term_table . " ON " . $wp_term_table . ".term_id = " . $wp_taxonomy_table . ".term_id WHERE " . $wp_taxonomy_table . ".taxonomy IN " . $attribute_name_con;
            $del_term_result = $db->query($del_term_query);
            if ($del_term_result === false) {
                echo 'Error Clear Product';
            }
        }
        //clear shipping
        $wp_term_table = $db->prefix . self::WP_TERM;
        $wp_taxonomy_table = $db->prefix . self::WP_TERM_TAXONOMY;
        $wp_relationship_table = $db->prefix . self::WP_TERM_RELATION;
        $category_query = "SELECT * FROM `" . $wp_taxonomy_table . "` WHERE taxonomy = 'product_shipping_class' ";
        $category_result = $db->get_results($category_query, ARRAY_A);
        if ($category_result) {
            $termId = $this->duplicateFieldValueFromList($category_result, 'term_id');
            $term_id_con = $this->arrayToInCondition($termId, false);
            $taxonomyId = $this->duplicateFieldValueFromList($category_result, 'term_taxonomy_id');
            $taxonomy_id_con = $this->arrayToInCondition($taxonomyId, false);
            $del_taxonomy_query = "DELETE FROM `" . $wp_taxonomy_table . "` WHERE term_id IN " . $term_id_con;
            $del_taxonomy_result = $db->query($del_taxonomy_query);
            if ($del_taxonomy_result === false) {
                echo 'Error Clear Product';
            }
            $del_term_query = "DELETE FROM `" . $wp_term_table . "` WHERE term_id IN " . $term_id_con;
            $del_term_result = $db->query($del_term_query);
            if ($del_term_result === false) {
                echo 'Error Clear Product';
            }
            $del_relationship_query = "DELETE FROM `" . $wp_relationship_table . "` WHERE term_taxonomy_id IN " . $taxonomy_id_con;
            $del_relationship_result = $db->query($del_relationship_query);
            if ($del_relationship_result === false) {
                echo 'Error Clear Product';
            }
        }
        //clear tag
        $category_query = "SELECT * FROM `" . $wp_taxonomy_table . "` WHERE taxonomy = 'product_tag'";
        $category_result = $db->get_results($category_query, ARRAY_A);
        if ($category_result) {
            $termId = $this->duplicateFieldValueFromList($category_result, 'term_id');
            $term_id_con = $this->arrayToInCondition($termId, false);
            $taxonomyId = $this->duplicateFieldValueFromList($category_result, 'term_taxonomy_id');
            $taxonomy_id_con = $this->arrayToInCondition($taxonomyId, false);
            $del_taxonomy_query = "DELETE FROM `" . $wp_taxonomy_table . "` WHERE term_id IN " . $term_id_con;
            $del_taxonomy_result = $db->query($del_taxonomy_query);
            if ($del_taxonomy_result === false) {
                echo 'Error Clear Product';
            }
            $del_term_query = "DELETE FROM `" . $wp_term_table . "` WHERE term_id IN " . $term_id_con;
            $del_term_result = $db->query($del_term_query);
            if ($del_term_result === false) {
                echo 'Error Clear Product';
            }
            $del_relationship_query = "DELETE FROM `" . $wp_relationship_table . "` WHERE term_taxonomy_id IN " . $taxonomy_id_con;
            $del_relationship_result = $db->query($del_relationship_query);
            if ($del_relationship_result === false) {
                echo 'Error Clear Product';
            }
        }
        //clear category
        $category_query = "SELECT * FROM `" . $wp_taxonomy_table . "` WHERE taxonomy = 'product_cat' OR taxonomy = 'category'";
        $category_result = $db->get_results($category_query, ARRAY_A);
        if ($category_result) {
            $termId = $this->duplicateFieldValueFromList($category_result, 'term_id');
            $term_id_con = $this->arrayToInCondition($termId, false);
            $taxonomyId = $this->duplicateFieldValueFromList($category_result, 'term_taxonomy_id');
            $taxonomy_id_con = $this->arrayToInCondition($taxonomyId, false);
            $del_taxonomy_query = "DELETE FROM `" . $wp_taxonomy_table . "` WHERE term_id IN " . $term_id_con;
            $del_taxonomy_result = $db->query($del_taxonomy_query);
            if ($del_taxonomy_result === false) {
                echo 'Error Clear Category';
            }
            $del_term_query = "DELETE FROM `" . $wp_term_table . "` WHERE term_id IN " . $term_id_con;
            $del_term_result = $db->query($del_term_query);
            if ($del_term_result === false) {
                echo 'Error Clear Category';
            }
            $del_relationship_query = "DELETE FROM `" . $wp_relationship_table . "` WHERE term_taxonomy_id IN " . $taxonomy_id_con;
            $del_relationship_result = $db->query($del_relationship_query);
            if ($del_relationship_result === false) {
                echo 'Error Clear Category';
            }
        }
        //clear category meta
        $result = $this->emptyTable($this->_woo_term_meta_table);
        if ($result === false) {
            echo 'Error Clear Category';
        }
        //clear order
        $order_exists = $this->_checkOrderExists();
        if ($order_exists) {
            $table_post = $db->prefix . self::WP_POSTS;
            $table_post_meta = $db->prefix . self::WP_POST_META;
            $del_order_query = "DELETE FROM `" . $table_post . "` WHERE post_type IN ('shop_order', 'shop_order_refund')";
            $del_order_result = $db->query($del_order_query);
            if (!$del_order_result) {
                return $this->errorClear('orders');
            }
            $del_order_meta_query = "DELETE " . $table_post_meta . " FROM " . $table_post_meta . " LEFT JOIN " . $table_post . " ON " . $table_post . ".ID = " . $table_post_meta . ".post_id WHERE " . $table_post . ".ID IS NULL";
            $del_order_meta_result = $db->query($del_order_meta_query);
            if ($del_order_meta_result === false) {
                echo 'Error Clear Order';
            }
            $table_order_note = $db->prefix. self::WP_COMMENT;
            $del_order_note_query = "DELETE FROM ".$table_order_note." WHERE comment_type = 'order_note'";
            $del_order_note_result = $db->query($del_order_note_query);
            if ($del_order_note_result === false){
                echo 'Error Clear Order';
            }
        }
        //clear order item
        $del_order_item = $this->emptyTable(self::WOO_ORDER_ITEM);
        $del_order_item_meta = $this->emptyTable(self::WOO_ORDER_META);
        if ($del_order_item === false || $del_order_item_meta === false) {
            echo 'Error Clear Order';
        }
        //clear customer
        $table_user = $db->base_prefix . self::WP_USERS;
        $table_user_meta = $db->base_prefix . self::WP_USER_META;
        $meta_key_capabilities = $db->base_prefix . 'capabilities';
        $del_user_query = "DELETE " . $table_user . ", " . $table_user_meta . " FROM " . $table_user . " LEFT JOIN " . $table_user_meta . " ON " . $table_user . ".ID = " . $table_user_meta . ".user_id WHERE " . $table_user_meta . ".meta_key IN ('wp_capabilities', '{$meta_key_capabilities}') AND " . $table_user_meta . ".meta_value = '" . 'a:1:{s:8:"customer";i:1;}' . "'";
        $del_user_result = $db->query($del_user_query);
        if ($del_user_result === false) {
            echo 'Error Clear User';
        }
        $del_user_meta_query = "DELETE " . $table_user_meta . " FROM " . $table_user_meta . " LEFT JOIN " . $table_user . " ON " . $table_user_meta . ".user_id = " . $table_user . ".ID WHERE " . $table_user . ".ID IS NULL";
        $del_user_meta_result = $db->query($del_user_meta_query);
        if ($del_user_meta_result === false) {
            echo 'Error Clear User';
        }
        //clear tax
        update_option(LeCaMg::WOO_TAX_SETTING, '');
        $del_tax_rate = $this->emptyTable(self::WOO_TAX_RATE);
        $del_tax_rate_location = $this->emptyTable(self::WOO_TAX_RATE_LOCATION);
        if ($del_tax_rate === false || $del_tax_rate_location === false) {
            echo 'Error Clear Tax';
        }
        //clear review
        $del_cmt = $this->emptyTable(self::WP_COMMENT);
        $del_cmt_meta = $this->emptyTable(self::WP_COMMENT_META);
        if ($del_cmt === false || $del_cmt_meta === false) {
            echo 'Error Clear Review';
        }
        return array(
            'result' => 'success',
            'msg' => ''
        );
    }

    public function clear()
    {
        if (!$this->_notice['config']['add_option']['clear_shop']) {
            return array(
                'result' => "success",
                'msg' => ''
            );
        }
        $function = $this->_notice['clear_info']['function'];
        $clear = $this->$function();
        if ($clear['result'] == 'success') {
            $entity = array();
            foreach ($this->_notice['config']['import'] as $type => $value) {
                if ($value) {
                    $entity[] = ucfirst(($type));
                }
            }
            $msg = "Current " . implode(', ', $entity) . " cleared!";
            $clear['msg'] = $this->consoleSuccess($msg);
            $clear['msg'] .= $this->getMsgStartImport('taxes');
        }
        return $clear;
    }

    public function prepareImportTaxes()
    {
        $this->_custom->prepareImportTaxesCustom($this);
        return true;
    }

    public function getTaxesMain()
    {
        return $this->_getTaxesMainFromConnector();
    }

    public function getTaxesExtra($taxes)
    {
        return $this->_getTaxesExtraFromConnector($taxes);
    }

    public function checkTaxImport($tax, $taxesExt)
    {
        if (LeCaMgCustom::TAX_CHECK) {
            return $this->_custom->checkTaxImportCustom($this, $tax, $taxesExt);
        }
        $id_src = $this->getTaxId($tax, $taxesExt);
        return $this->getValueTax($id_src);
    }

    public function importTax($data, $tax, $taxesExt)
    {
        if (LeCaMgCustom::TAX_IMPORT) {
            return $this->_custom->importTaxCustom($this, $data, $tax, $taxesExt);
        }
        $id_src = $this->getTaxId($tax, $taxesExt);
        $taxIpt = $this->tax($data);
        if ($taxIpt['result'] == 'success') {
            $value = $taxIpt['value'];
            $taxIpt['id_desc'] = $taxIpt['value'];
            $this->taxSuccess($id_src, null, $value);
        } else {
            $msg = "Tax Id = " . $id_src . " import failed.";
            $taxIpt['msg'] = $this->consoleWarning($msg);
        }
        return $taxIpt;
    }

    public function additionTax($convert, $tax, $taxesExt)
    {
        if (LeCaMgCustom::TAX_ADDITION) {
            return $this->_custom->additionTaxCustom($this, $convert, $tax, $taxesExt);
        }
        return array(
            'result' => "success"
        );
    }

    public function afterSaveTax($tax_id_desc, $convert, $tax, $taxesExt)
    {
        $this->_custom->afterSaveTaxCustom($this, $tax_id_desc, $convert, $tax, $taxesExt);
        return LeCaMgCustom::TAX_AFTER_SAVE;
    }

    public function prepareImportManufacturers()
    {
        $this->_custom->prepareImportManufacturersCustom($this);
        return true;
    }

    public function getManufacturersMain()
    {
        return $this->_getManufacturersMainFromConnector();
    }

    public function getManufacturersExtra($manufacturers)
    {
        return $this->_getManufacturersExtraFromConnector($manufacturers);
    }

    public function checkManufacturerImport($manufacturer, $manufacturersExt)
    {
        if (LeCaMgCustom::MANUFACTURER_CHECK) {
            return $this->_custom->checkManufacturerImportCustom($this, $manufacturer, $manufacturersExt);
        }
        $id_src = $this->getManufacturerId($manufacturer, $manufacturersExt);
        return $this->getIdDescManufacturer($id_src);
    }

    public function importManufacturer($data, $manufacturer, $manufacturersExt)
    {
        if (LeCaMgCustom::MANUFACTURER_IMPORT) {
            return $this->_custom->importManufacturerCustom($this, $data, $manufacturer, $manufacturersExt);
        }
        return array(
            'result' => "success"
        );
    }

    public function additionManufacturer($convert, $manufacturer, $manufacturersExt)
    {
        if (LeCaMgCustom::MANUFACTURER_ADDITION) {
            return $this->_custom->additionManufacturerCustom($this, $convert, $manufacturer, $manufacturersExt);
        }
        return array(
            'result' => "success"
        );
    }

    public function afterSaveManufacturer($manufacturer_id_desc, $convert, $manufacturer, $manufacturersExt)
    {
        $this->_custom->afterSaveManufacturerCustom($this, $manufacturer_id_desc, $convert, $manufacturer, $manufacturersExt);
        return LeCaMgCustom::MANUFACTURER_AFTER_SAVE;
    }

    public function prepareImportCategories()
    {
        if ($this->_notice['config']['add_option']['seo'] && $this->_notice['config']['add_option']['seo_plugin']) {
            $seo_plugin = $this->_notice['config']['add_option']['seo_plugin'];
            $this->_seo = LeCaMg::getModel($seo_plugin);
            $this->_seo->prepareImportCategories($this);
        }
        $this->_custom->prepareImportCategoriesCustom($this);
        return true;
    }

    public function getCategoriesMain()
    {
        return $this->_getCategoriesMainFromConnector();
    }

    public function getCategoriesExtra($categories)
    {
        return $this->_getCategoriesExtraFromConnector($categories);
    }

    public function checkCategoryImport($category, $categoriesExt)
    {
        if (LeCaMgCustom::CATEGORY_CHECK) {
            return $this->_custom->checkCategoryImportCustom($this, $category, $categoriesExt);
        }
        $id_src = $this->getCategoryId($category, $categoriesExt);
        return $this->getIdDescCategory($id_src);
    }

    public function importCategory($data, $category, $categoriesExt)
    {
        if (LeCaMgCustom::CATEGORY_IMPORT) {
            return $this->_custom->importCategoryCustom($this, $data, $category, $categoriesExt);
        }
        $response = $this->_defaultResponse();
        $id_src = $this->getCategoryId($category, $categoriesExt);
        $id_desc = $this->category($data, true);
        if ($id_desc) {
            $this->categorySuccess($id_src, $id_desc['term_id'], $id_desc['term_taxonomy_id']);
            $response['result'] = "success";
            $response['id_desc'] = $id_desc['term_id'];
        } else {
            $response['result'] = "warning";
            $msg = "Category Id = " . $id_src . " import failed.";
            $response['msg'] = $this->consoleWarning($msg);
        }
        return $response;
    }

    public function additionCategory($convert, $category, $categoriesExt)
{
    if (LeCaMgCustom::CATEGORY_ADDITION) {
        return $this->_custom->additionCategoryCustom($this, $convert, $category, $categoriesExt);
    }
    return array(
        'result' => "success"
    );
}

    public function afterSaveCategory($category_id_desc, $convert, $category, $categoriesExt)
    {
        if ($this->_seo) {
            $this->_seo->categorySeo($this, $category_id_desc, $convert, $category, $categoriesExt);
        }
        $this->_custom->afterSaveCategoryCustom($this, $category_id_desc, $convert, $category, $categoriesExt);
        return LeCaMgCustom::CATEGORY_AFTER_SAVE;
    }

    public function prepareImportProducts()
    {
        if ($this->_notice['config']['add_option']['seo'] && $this->_notice['config']['add_option']['seo_plugin']) {
            $seo_plugin = $this->_notice['config']['add_option']['seo_plugin'];
            $this->_seo = LeCaMg::getModel($seo_plugin);
            $this->_seo->prepareImportProducts($this);
        }
        $this->_custom->prepareImportProductsCustom($this);
        return true;
    }

    public function getProductsMain()
    {
        return $this->_getProductsMainFromConnector();
    }

    public function getProductsExtra($products)
    {
        return $this->_getProductsExtraFromConnector($products);
    }

    public function checkProductImport($product, $productsExt)
    {
        if (LeCaMgCustom::PRODUCT_CHECK) {
            return $this->_custom->checkProductImportCustom($this, $product, $productsExt);
        }
        $id_src = $this->getProductId($product, $productsExt);
        return $this->getIdDescProduct($id_src);
    }


    public function importProduct($data, $product, $productsExt)
    {
        if (LeCaMgCustom::PRODUCT_IMPORT) {
            return $this->_custom->importProductCustom($this, $data, $product, $productsExt);
        }
        $response = $this->_defaultResponse();
        $id_src = $this->getProductId($product, $productsExt);
        $id_desc = $this->product($data);
        if ($id_desc) {
            $this->productSuccess($id_src, $id_desc);
            $response['result'] = "success";
            $response['id_desc'] = $id_desc;
        } else {
            $response['result'] = "warning";
            $msg = "Product Id = " . $id_src . " import failed.";
            $response['msg'] = $this->consoleWarning($msg);
        }
        return $response;
    }

    public function additionProduct($convert, $product, $productsExt)
    {
        if (LeCaMgCustom::PRODUCT_ADDITION) {
            return $this->_custom->additionProductCustom($this, $convert, $product, $productsExt);
        }
        return array(
            'result' => "success"
        );
    }

    public function afterSaveProduct($product_id_desc, $convert, $product, $productsExt)
    {
        if ($this->_seo) {
            $this->_seo->productSeo($this, $product_id_desc, $convert, $product, $productsExt);
        }
        $this->_custom->afterSaveProductCustom($this, $product_id_desc, $convert, $product, $productsExt);
        return LeCaMgCustom::PRODUCT_AFTER_SAVE;
    }

    public function prepareImportCustomers()
    {
        $this->_custom->prepareImportCustomersCustom($this);
        return true;
    }

    public function getCustomersMain()
    {
        return $this->_getCustomersMainFromConnector();
    }

    public function getCustomersExtra($customers)
    {
        return $this->_getCustomersExtraFromConnector($customers);
    }

    public function checkCustomerImport($customer, $customersExt)
    {
        if (LeCaMgCustom::CUSTOMER_CHECK) {
            return $this->_custom->checkCustomerImportCustom($this, $customer, $customersExt);
        }
        $id_src = $this->getCustomerId($customer, $customersExt);
        return $this->getIdDescCustomer($id_src);
    }

    public function importCustomer($data, $customer, $customersExt)
    {
        if (LeCaMgCustom::CUSTOMER_IMPORT) {
            return $this->_custom->importCustomerCustom($this, $data, $customer, $customersExt);
        }
        $response = $this->_defaultResponse();
        $id_src = $this->getCustomerId($customer, $customersExt);
        $check_email = $this->selectTableRow(self::WP_USERS, array('user_email' => $data['user_email']), 'user_email');
        $check_username = $this->selectTableRow(self::WP_USERS, array('user_login' => $data['user_login']), 'user_login');
        if ($check_email) {
            $msg = "Customer Id = " . $id_src . " import failed. This email address has already been registered";
            return array(
                'result' => 'warning',
                'msg' => $this->consoleWarning($msg)
            );
        }
        if ($check_username) {
            $msg = "Customer Id = " . $id_src . " import failed. This username has already been registered";
            return array(
                'result' => 'warning',
                'msg' => $this->consoleWarning($msg)
            );
        }
        $id_desc = $this->customer($data);
        if ($id_desc) {
            $this->customerSuccess($id_src, $id_desc);
            $response['result'] = "success";
            $response['id_desc'] = $id_desc;
        } else {
            $msg = "Customer Id = " . $id_src . " import failed.";
            $response['result'] = "warning";
            $response['msg'] = $this->consoleWarning($msg);
        }
        return $response;
    }

    public function additionCustomer($convert, $customer, $customersExt)
    {
        if (LeCaMgCustom::CUSTOMER_ADDITION) {
            return $this->_custom->additionCustomerCustom($this, $convert, $customer, $customersExt);
        }
        return array(
            'result' => "success"
        );
    }

    public function afterSaveCustomer($customer_id_desc, $convert, $customer, $customersExt)
    {
        $this->_custom->afterSaveCustomerCustom($this, $customer_id_desc, $convert, $customer, $customersExt);
        return LeCaMgCustom::CUSTOMER_AFTER_SAVE;
    }

    public function prepareImportOrders()
    {
        $this->_custom->prepareImportOrdersCustom($this);
        return true;
    }

    public function getOrdersMain()
    {
        return $this->_getOrdersMainFromConnector();
    }

    public function getOrdersExtra($orders)
    {
        return $this->_getOrdersExtraFromConnector($orders);
    }

    public function checkOrderImport($order, $ordersExt)
    {
        if (LeCaMgCustom::ORDER_CHECK) {
            return $this->_custom->checkOrderImportCustom($this, $order, $ordersExt);
        }
        $id_src = $this->getOrderId($order, $ordersExt);
        return $this->getIdDescOrder($id_src);
    }

    public function importOrder($data, $order, $ordersExt)
    {
        if (LeCaMgCustom::ORDER_IMPORT) {
            return $this->_custom->importOrderCustom($this, $data, $order, $ordersExt);
        }
        $response = $this->_defaultResponse();
        $id_src = $this->getOrderId($order, $ordersExt);
        $id_desc = $this->order($data);
        if ($id_desc) {
            $this->orderSuccess($id_src, $id_desc);
            $response['result'] = "success";
            $response['id_desc'] = $id_desc;
        } else {
            $msg = "Order Id = " . $id_src . " import failed.";
            $response['result'] = "warning";
            $response['msg'] = $this->consoleWarning($msg);
        }
        return $response;
    }

    public function additionOrder($convert, $order, $ordersExt)
    {
        if (LeCaMgCustom::ORDER_ADDITION) {
            return $this->_custom->additionOrderCustom($this, $convert, $order, $ordersExt);
        }
        return array(
            'result' => "success"
        );
    }

    public function afterSaveOrder($order_id_desc, $convert, $order, $ordersExt)
    {
        $this->_custom->afterSaveOrderCustom($this, $order_id_desc, $convert, $order, $ordersExt);
        return LeCaMgCustom::ORDER_AFTER_SAVE;
    }

    public function prepareImportReviews()
    {
        $this->_custom->prepareImportReviewsCustom($this);
        return true;
    }

    public function getReviewsMain()
    {
        return $this->_getReviewMainFromConnector();
    }

    public function getReviewsExtra($reviews)
    {
        return $this->_getReviewExtraFromConnector($reviews);
    }

    public function checkReviewImport($review, $reviewsExt)
    {
        if (LeCaMgCustom::REVIEW_CHECK) {
            return $this->_custom->checkReviewImportCustom($this, $review, $reviewsExt);
        }
        $id_src = $this->getReviewId($review, $reviewsExt);
        return $this->getIdDescReview($id_src);
    }

    public function importReview($data, $review, $reviewsExt)
    {
        if (LeCaMgCustom::REVIEW_IMPORT) {
            return $this->_custom->importReviewCustom($this, $data, $review, $reviewsExt);
        }
        $response = $this->_defaultResponse();
        $id_src = $this->getReviewId($review, $reviewsExt);
        $id_desc = $this->review($data);
        if ($id_desc) {
            $this->reviewSuccess($id_src, $id_desc);
            $response['result'] = "success";
            $response['id_desc'] = $id_desc;
        } else {
            $msg = "Review Id = " . $id_src . " import failed.";
            $response['result'] = "warning";
            $response['msg'] = $this->consoleWarning($msg);
        }
        return $response;
    }

    public function additionReview($convert, $review, $reviewsExt)
    {
        if (LeCaMgCustom::REVIEW_ADDITION) {
            return $this->_custom->additionReviewCustom($this, $convert, $review, $reviewsExt);
        }
        return array(
            'result' => "success"
        );
    }

    public function afterSaveReview($review_id_desc, $convert, $review, $reviewsExt)
    {
        $this->_custom->afterSaveReviewCustom($this, $review_id_desc, $convert, $review, $reviewsExt);
        return LeCaMgCustom::REVIEW_AFTER_SAVE;
    }

    public function prepareImportPages()
    {
        if ($this->_notice['config']['add_option']['seo'] && $this->_notice['config']['add_option']['seo_plugin']) {
            $seo_plugin = $this->_notice['config']['add_option']['seo_plugin'];
            $this->_seo = LeCaMg::getModel($seo_plugin);
            $this->_seo->prepareImportPages($this);
        }
        $this->_custom->prepareImportPagesCustom($this);
        return true;
    }

    public function getPagesMain()
    {
        return $this->_getPagesMainFromConnector();
    }

    protected function _getPagesMainQuery()
    {
        $id_src = $this->_notice['pages']['id_src'];
        $limit = $this->_notice['setting']['products'];
        $query = "SELECT * FROM _DBPRF_posts WHERE ID > {$id_src} AND post_type = 'page' AND post_status NOT IN ('inherit','auto-draft') ORDER BY ID ASC LIMIT ".$limit;
        return $query;
    }

    public function getPagesExtra($pages)
    {
        return $this->_getPagesExtraFromConnector($pages);
    }

    protected function _getPagesExtraQuery($pages)
    {
        $pageIds = $this->duplicateFieldValueFromList($pages['object'], 'ID');
        $page_ids_con = $this->arrayToInCondition($pageIds);
        $ext_query = array(
            "pagemeta" => "SELECT * FROM _DBPRF_postmeta WHERE post_id IN {$page_ids_con}",
        );
        return $ext_query;
    }

    public function getPageId($page, $pagesExt)
    {
        if(LeCaMgCustom::PAGE_ID){
            return $this->_custom->getPagetIdCustom($this, $page, $pagesExt);
        }
        return $page['ID'];
    }

    public function checkPageImport($page, $pagesExt)
    {
        if (LeCaMgCustom::PAGE_CHECK) {
            return $this->_custom->checkPageImportCustom($this, $page, $pagesExt);
        }
        $id_src = $this->getPageId($page, $pagesExt);
        return $this->getIdDescPage($id_src);
    }

    public function convertPage($page, $pagesExt)
    {
        if (LeCaMgCustom::PAGE_CONVERT) {
            return $this->_custom->convertProductCustom($this, $page, $pagesExt);
        }
        $page_desc = $page['post_content'];
        if ($page_desc != '') {
            $page_desc = $this->_changeImgSrcInText($page_desc);
        }
        $page_data = array(
            'post_author' => $this->wpCurrentUserId(),
            'post_date' => $page['post_date'],
            'post_date_gmt' => $page['post_date_gmt'],
            'post_content' => $page_desc,
            'post_title' => $page['post_title'],
            'post_excerpt' => $page['post_excerpt'],
            'post_status' => $page['post_status'],
            'comment_status' => $page['comment_status'],
            'ping_status' => $page['ping_status'],
            'post_password' => $page['post_password'],
            'post_name' => $page['post_name'],
            'to_ping' => $page['to_ping'],
            'pinged' => $page['pinged'],
            'post_modified' => $page['post_modified'],
            'post_modified_gmt' => $page['post_modified_gmt'],
            'post_content_filtered' => $page['post_content_filtered'],
            'post_parent' => 0,
            'guid' => site_url("?page=" . sanitize_title($page['post_name'])),
            'menu_order' => $page['menu_order'],
            'post_type' => "page",
            'post_mime_type' => $page['post_mime_type'],
            'comment_count' => $page['comment_count']
        );
        if($page['post_parent'] > 0 && $post_parent_id_desc = $this->getIdDescPage($page['post_parent'])){
            $page_data['post_parent'] = $post_parent_id_desc;
        }
        $thumbnail_id = "";
        $post_meta = $this->getListFromListByField($pagesExt['object']['pagemeta'], 'post_id', $page['ID']);
        $thumb_id = $this->getRowValueFromListByField($post_meta, 'meta_key', '_thumbnail_id', 'meta_value');
        if($thumb_id){
            $thumb_src = $this->getRowFromListByField($pagesExt['object']['images'], 'ID', $thumb_id);
            if($thumb_src){
                $path = $this->downloadImage($this->getUrlSuffix($this->_notice['config']['image_product']), $thumb_src['meta_value'], self::IMG_DIR, false, true);
                if($path){
                    $thumbnail_id = $this->wpImage(self::IMG_DIR . "/" . $path, $thumb_src['post_title']);
                }
            }
        }
        $meta = array('_thumbnail_id' => $thumbnail_id );
        $page_data['meta'] = $meta;
        return array(
            'result' => "success",
            'data' => $page_data
        );
    }

    public function importPage($data, $page, $pagesExt)
    {
        if (LeCaMgCustom::PAGE_IMPORT) {
            return $this->_custom->importPageCustom($this, $data, $page, $pagesExt);
        }
        $response = $this->_defaultResponse();
        $id_src = $this->getPageId($page, $pagesExt);
        $id_desc = $this->page($data);
        if ($id_desc) {
            $this->pageSuccess($id_src, $id_desc);
            $response['result'] = "success";
            $response['id_desc'] = $id_desc;
        } else {
            $response['result'] = "warning";
            $msg = "Page Id = " . $id_src . " import failed.";
            $response['msg'] = $this->consoleWarning($msg);
        }
        return $response;
    }

    public function additionPage($convert, $page, $pagesExt)
    {
        if (LeCaMgCustom::PAGE_ADDITION) {
            return $this->_custom->additionProductCustom($this, $convert, $page, $pagesExt);
        }
        return array(
            'result' => "success"
        );
    }

    public function afterSavePage($page_id_desc, $convert, $page, $pagesExt)
    {
        if ($this->_seo) {
            $this->_seo->pageSeo($this, $page_id_desc, $convert, $page, $pagesExt);
        }
        $this->_custom->afterSavePageCustom($this, $page_id_desc, $convert, $page, $pagesExt);
        return LeCaMgCustom::PAGE_AFTER_SAVE;
    }

    public function prepareImportPostCat()
    {
        if ($this->_notice['config']['add_option']['seo'] && $this->_notice['config']['add_option']['seo_plugin']) {
            $seo_plugin = $this->_notice['config']['add_option']['seo_plugin'];
            $this->_seo = LeCaMg::getModel($seo_plugin);
            $this->_seo->prepareImportPostCat($this);
        }
        $this->_custom->prepareImportPostCatCustom($this);
        return true;
    }

    public function getPostCatMain()
    {
        return $this->_getPostCatMainFromConnector();
    }

    protected function _getPostCatMainQuery(){
        $id_src = $this->_notice['postCat']['id_src'];
        $limit = $this->_notice['setting']['categories'];
        $query = "SELECT * FROM _DBPRF_term_taxonomy as tx
                          LEFT JOIN _DBPRF_terms AS t ON t.term_id = tx.term_id
                          WHERE tx.taxonomy = 'category' AND tx.term_id > {$id_src} ORDER BY tx.term_id ASC LIMIT {$limit}";
        return $query;
    }

    public function getPostCatExtra($postCat)
    {
        return $this->_getPostCatExtraFromConnector($postCat);
    }

    protected function _getPostCatExtraQuery($postCat){
        return array();
    }

    public function getPostCatId($postCat, $postCatExt){
        if(LeCaMgCustom::POST_CATEGORY_ID){
            return $this->_custom->getCategoryIdCustom($this, $postCat, $postCatExt);
        }
        return $postCat['term_id'];
    }

    public function checkPostCatImport($postCat, $postCatExt)
    {
        if (LeCaMgCustom::POST_CATEGORY_CHECK) {
            return $this->_custom->checkPostCatImportCustom($this, $postCat, $postCatExt);
        }
        $id_src = $this->getPostCatId($postCat, $postCatExt);
        return $this->getIdDescCategory($id_src);
    }

    public function convertPostCat($postCat, $postCatExt){
        if(LeCaMgCustom::POST_CATEGORY_CONVERT){
            return $this->_custom->convertCategoryCustom($this, $postCat, $postCatExt);
        }
        if($postCat['parent'] == 0){
            $cat_parent_id = 0;
        } else {
            $cat_parent_id = $this->getIdDescCategory($postCat['parent']);
            if(!$cat_parent_id){
                $parent_ipt = $this->_importCategoryParent($postCat['parent']);
                if($parent_ipt['result'] == 'error'){
                    return $parent_ipt;
                } else if($parent_ipt['result'] == 'warning'){
                    return array(
                        'result' => 'warning',
                        'msg' => $this->consoleWarning("Category Id = " . $postCat['term_id'] . " import failed. Error: Could not import parent category id = " . $postCat['parent'])
                    );
                } else {
                    $cat_parent_id = $parent_ipt['id_desc'];
                }
            }
        }
        $cat_data = array(
            'name' => $postCat['name'],
            'slug' => $postCat['slug'],
            'term_group' => 0,
            'description' => $postCat['description'],
            'taxonomy' => 'category',
            'parent' => $cat_parent_id
        );
        $custom = $this->_custom->convertPostCatCustom($this, $postCat, $postCatExt);
        if($custom){
            $cat_data = array_merge($cat_data, $custom);
        }
        return array(
            'result' => "success",
            'data' => $cat_data
        );
    }

    public function importPostCat($data, $postCat, $postCatExt)
    {
        if (LeCaMgCustom::POST_CATEGORY_IMPORT) {
            return $this->_custom->importCategoryCustom($this, $data, $postCat, $postCatExt);
        }
        $response = $this->_defaultResponse();
        $id_src = $this->getPostCatId($postCat, $postCatExt);
        $id_desc = $this->post_category($data, true);
        if ($id_desc) {
            $this->categorySuccess($id_src, $id_desc['term_id'], $id_desc['term_taxonomy_id']);
            $response['result'] = "success";
            $response['id_desc'] = $id_desc['term_id'];
        } else {
            $response['result'] = "warning";
            $msg = "Category Id = " . $id_src . " import failed.";
            $response['msg'] = $this->consoleWarning($msg);
        }
        return $response;
    }

    public function additionPostCat($convert, $postCat, $postCatExt)
    {
        if (LeCaMgCustom::POST_CATEGORY_ADDITION) {
            return $this->_custom->additionCategoryCustom($this, $convert, $postCat, $postCatExt);
        }
        return array(
            'result' => "success"
        );
    }

    public function afterSavePostCat($postCat_id_desc, $convert, $postCat, $postCatExt)
    {
        if ($this->_seo) {
            $this->_seo->categorySeo($this, $postCat_id_desc, $convert, $postCat, $postCatExt);
        }
        $this->_custom->afterSavePostCatCustom($this, $postCat_id_desc, $convert, $postCat, $postCatExt);
        return LeCaMgCustom::POST_CATEGORY_AFTER_SAVE;
    }

    public function prepareImportPosts()
    {
        if ($this->_notice['config']['add_option']['seo'] && $this->_notice['config']['add_option']['seo_plugin']) {
            $seo_plugin = $this->_notice['config']['add_option']['seo_plugin'];
            $this->_seo = LeCaMg::getModel($seo_plugin);
            $this->_seo->prepareImportPosts($this);
        }
        $this->_custom->prepareImportPostCustom($this);
        return true;
    }

    public function getPostsMain()
    {
        return $this->_getPostsMainFromConnector();
    }

    protected function _getPostsMainQuery()
    {
        $id_src = $this->_notice['posts']['id_src'];
        $limit = $this->_notice['setting']['products'];
        $query = "SELECT * FROM _DBPRF_posts WHERE ID > {$id_src} AND post_type = 'post' AND post_status NOT IN ('inherit','auto-draft') ORDER BY ID ASC LIMIT ".$limit;
        return $query;
    }

    public function getPostsExtra($posts)
    {
        return $this->_getPostsExtraFromConnector($posts);
    }

    protected function _getPostsExtraQuery($posts)
    {
        $postIds = $this->duplicateFieldValueFromList($posts['object'], 'ID');
        $pro_ids_con = $this->arrayToInCondition($postIds);
        $ext_query = array(
            "term_relationship" => "SELECT * FROM _DBPRF_term_relationships AS tr
                                      LEFT JOIN _DBPRF_term_taxonomy AS tx ON tx.term_taxonomy_id = tr.term_taxonomy_id
                                      LEFT JOIN _DBPRF_terms AS t ON t.term_id = tx.term_id
                                    WHERE tr.object_id IN {$pro_ids_con}",
        );
        return $ext_query;
    }

    protected function _getPostsExtraRelQuery($posts, $postsExt){
        $postIds = $this->duplicateFieldValueFromList($posts['object'], 'ID');
        $pro_ids_con = $this->arrayToInCondition($postIds);
        $ext_rel_query = array(
            "postmeta" => "SELECT * FROM _DBPRF_postmeta WHERE post_id IN {$pro_ids_con}",
        );
        return $ext_rel_query;
    }

    public function getPostId($post, $postsExt)
    {
        if(LeCaMgCustom::POST_ID){
            return $this->_custom->getPagetIdCustom($this, $post, $postsExt);
        }
        return $post['ID'];
    }

    public function convertPost($post, $postsExt)
    {
        if (LeCaMgCustom::POST_CONVERT) {
            return $this->_custom->convertProductCustom($this, $post, $postsExt);
        }
        $pro_desc = $post['post_content'];
        if ($pro_desc != '') {
            $pro_desc = $this->_changeImgSrcInText($pro_desc);
        }
        $post_data = array(
            'post_author' => $this->wpCurrentUserId(),
            'post_date' => $post['post_date'],
            'post_date_gmt' => $post['post_date_gmt'],
            'post_content' => $pro_desc,
            'post_title' => $post['post_title'],
            'post_excerpt' => $post['post_excerpt'],
            'post_status' => $post['post_status'],
            'comment_status' => $post['comment_status'],
            'ping_status' => $post['ping_status'],
            'post_password' => $post['post_password'],
            'post_name' => $post['post_name'],
            'to_ping' => $post['to_ping'],
            'pinged' => $post['pinged'],
            'post_modified' => $post['post_modified'],
            'post_modified_gmt' => $post['post_modified_gmt'],
            'post_content_filtered' => $post['post_content_filtered'],
            'post_parent' => 0,
            'guid' => site_url("?p=" . sanitize_title($post['post_name'])),
            'menu_order' => $post['menu_order'],
            'post_type' => "post",
            'post_mime_type' => $post['post_mime_type'],
            'comment_count' => $post['comment_count']
        );
        $thumbnail_id = "";
        $post_meta = $this->getListFromListByField($postsExt['object']['postmeta'], 'post_id', $post['ID']);
        $thumb_id = $this->getRowValueFromListByField($post_meta, 'meta_key', '_thumbnail_id', 'meta_value');
        if($thumb_id){
            $thumb_src = $this->getRowFromListByField($postsExt['object']['images'], 'ID', $thumb_id);
            if($thumb_src){
                $path = $this->downloadImage($this->getUrlSuffix($this->_notice['config']['image_product']), $thumb_src['meta_value'], self::IMG_DIR, false, true);
                if($path){
                    $thumbnail_id = $this->wpImage(self::IMG_DIR . "/" . $path, $thumb_src['post_title']);
                }
            }
        }
        $meta = array('_thumbnail_id' => $thumbnail_id );
        $post_data['meta'] = $meta;
        return array(
            'result' => "success",
            'data' => $post_data
        );
    }

    public function checkPostImport($post, $postsExt)
    {
        if (LeCaMgCustom::POST_CHECK) {
            return $this->_custom->checkPostImportCustom($this, $post, $postsExt);
        }
        $id_src = $this->getPostId($post, $postsExt);
        return $this->getIdDescPost($id_src);
    }

    public function importPost($data, $post, $postsExt)
    {
        if (LeCaMgCustom::PAGE_IMPORT) {
            return $this->_custom->importPostCustom($this, $data, $post, $postsExt);
        }
        $response = $this->_defaultResponse();
        $id_src = $this->getPostId($post, $postsExt);
        $id_desc = $this->post($data);
        if ($id_desc) {
            $this->postSuccess($id_src, $id_desc);
            $response['result'] = "success";
            $response['id_desc'] = $id_desc;
        } else {
            $response['result'] = "warning";
            $msg = "Post Id = " . $id_src . " import failed.";
            $response['msg'] = $this->consoleWarning($msg);
        }
        return $response;
    }

    public function additionPost($convert, $post, $postsExt)
    {
        if (LeCaMgCustom::POST_ADDITION) {
            return $this->_custom->additionProductCustom($this, $convert, $post, $postsExt);
        }
        return array(
            'result' => "success"
        );
    }

    public function afterSavePost($post_id_desc, $convert, $post, $postsExt)
    {
        if ($this->_seo) {
            $this->_seo->pageSeo($this, $post_id_desc, $convert, $post, $postsExt);
        }
        $term_relationship = $this->getListFromListByField($postsExt['object']['term_relationship'], 'object_id', $post['ID']);
        $catSrc = $this->getListFromListByField($term_relationship, 'taxonomy', 'category');
        if ($catSrc) {
            foreach ($catSrc as $pro_cat) {
                $cat_id = $this->getValueCategory($pro_cat['term_id']);
                if($cat_id){
                    $relationship = array(
                        'object_id' => $post_id_desc,
                        'term_taxonomy_id' => $cat_id,
                        'term_order' => 0
                    );
                    $this->wpTermRelationship($relationship);
                }
            }
        }
        $slug = '';
        $proTags = $this->getListFromListByField($term_relationship, 'taxonomy', 'post_tag');
        if ($proTags) {
            foreach ($proTags as $tag) {
                $tag_id_desc = $this->getIdDescTag($tag['term_id']);
                if (!$tag_id_desc) {
                    $slug = $tag['slug'];
                    $tag_data = array(
                        'name' => $tag['name'],
                        'slug' => $slug,
                        'term_group' => 0,
                        'taxonomy' => 'post_tag',
                        'description' => $tag['description'],
                        'parent' => 0,
                        'count' => $tag['count'],
                        'meta' => array('post_count_post_tag' => 1)
                    );
                    $tag_id_desc = $this->category($tag_data);
                    if (!$tag_id_desc) {
                        continue;
                    }
                    $this->tagSuccess($tag['term_id'], $tag_id_desc, $slug);
                }
                $tag_relationship = array(
                    'object_id' => $post_id_desc,
                    'term_taxonomy_id' => $tag_id_desc,
                    'term_order' => 0
                );
                $this->wpTermRelationship($tag_relationship);
            }
        }

        $postFormat = $this->getListFromListByField($term_relationship, 'taxonomy', 'post_format');
        if ($postFormat){
            foreach ($postFormat as $format){
                $format_id_desc = $this->getIdDescFormat($format['term_id']);
                if (!$format_id_desc){
                    $format_data = array(
                        'name' => $format['name'],
                        'slug' => $format['slug'],
                        'term_group' => 0,
                        'taxonomy' => 'post_format',
                        'description' => $format['description'],
                        'parent' => 0,
                        'count' => $format['count'],
                    );
                    $format_id_desc = $this->category($format_data);
                    if (!$format_id_desc){
                        continue;
                    }
                    $this->formatSuccess($format['term_id'], $format_id_desc, $slug);
                }
            }
            $format_relationship = array(
                'object_id' => $post_id_desc,
                'term_taxonomy_id' => $format_id_desc,
                'term_order' => 0
            );
            $this->wpTermRelationship($format_relationship);
        }
        $db = $this->getDB();
        $term_query = "SELECT * FROM ". $db->prefix.self::WP_TERM_TAXONOMY. " WHERE taxonomy = 'category'";
        $term_result = $this->selectQuery($term_query);
        if ($term_result['result'] != 'success' || !$term_result['data']) {
            return;
        }
        $termTaxonomy = $term_result['data'];
        $count = 0;
        foreach ($termTaxonomy as $term_taxonomy){
            $termRelation_query = "SELECT COUNT(*) FROM " . $db->prefix . self::WP_TERM_RELATION . " WHERE term_taxonomy_id = " . $term_taxonomy['term_taxonomy_id'];
            $termRelation_result = $this->readQuery($termRelation_query);
            if ($termRelation_result['result'] == 'success' && $termRelation_result['data']) {
                $count = $termRelation_result['data'];
            }
            else $count = 0;
            $this->updateTable(self::WP_TERM_TAXONOMY,
                array('count' => $count),
                array(
                    'term_taxonomy_id' => $term_taxonomy['term_taxonomy_id'],
                    'taxonomy' => 'product_cat'
                )
            );
        }
        $this->_custom->afterSavePageCustom($this, $post_id_desc, $convert, $post, $postsExt);
        return LeCaMgCustom::POST_AFTER_SAVE;
    }

    public function prepareImportComments()
    {
        if ($this->_notice['config']['add_option']['seo'] && $this->_notice['config']['add_option']['seo_plugin']) {
            $seo_plugin = $this->_notice['config']['add_option']['seo_plugin'];
            $this->_seo = LeCaMg::getModel($seo_plugin);
            $this->_seo->prepareImportComments($this);
        }
        $this->_custom->prepareImportCommentCustom($this);
        return true;
    }

    public function getCommentsMain()
    {
        return $this->_getCommentsMainFromConnector();
    }

    protected function _getCommentsMainQuery(){
        $id_src = $this->_notice['comments']['id_src'];
        $limit = $this->_notice['setting']['reviews'];
        $query = "SELECT cm.*, p.post_type FROM _DBPRF_comments AS cm
                            LEFT JOIN _DBPRF_posts AS p ON p.ID = cm.comment_post_ID
                            WHERE p.post_type = 'post' AND cm.comment_ID > {$id_src} ORDER BY cm.comment_ID ASC LIMIT {$limit}";
        return $query;
    }

    public function getCommentsExtra($comments)
    {
        return $this->_getCommentsExtraFromConnector($comments);
    }

    protected function _getCommentsExtraQuery($comments){
        return array();
    }

    public function getCommentId($comment, $commentsExt){
        if(LeCaMgCustom::COMMENT_ID){
            return $this->_custom->getCommentIdCustom($this, $comment, $commentsExt);
        }
        return $comment['comment_ID'];
    }

    public function checkCommentImport($comment, $commentsExt)
    {
        if (LeCaMgCustom::COMMENT_CHECK) {
            return $this->_custom->checkCommentImportCustom($this, $comment, $commentsExt);
        }
        $id_src = $this->getCommentId($comment, $commentsExt);
        return $this->getIdDescComment($id_src);
    }

    public function convertComment($comment, $commentsExt){
        if(LeCaMgCustom::COMMENT_CONVERT){
            return $this->_custom->convertCommentCustom($this, $comment, $commentsExt);
        }
        $post_id = $this->getIdDescPost($comment['comment_post_ID']);
        if(!$post_id){
            return array(
                'result' => 'warning',
                'msg' => $this->consoleWarning("Comment Id = " . $comment['comment_ID'] . " import failed. Error: Post Id = " . $comment['comment_post_ID'] . " not imported!")
            );
        }
        $comment_data = array(
            'comment_post_ID' => $post_id,
            'comment_author' => $comment['comment_author'],
            'comment_author_email' => $comment['comment_author_email'],
            'comment_author_url' => $comment['comment_author_url'],
            'comment_author_IP' => $comment['comment_author_IP'],
            'comment_date' => $comment['comment_date'],
            'comment_date_gmt' => $comment['comment_date_gmt'],
            'comment_content' => $comment['comment_content'],
            'comment_karma' => $comment['comment_karma'],
            'comment_approved' => $comment['comment_approved'],
            'comment_agent' => $comment['comment_agent'],
            'comment_type' => $comment['comment_type'],
            'comment_parent' => 0,
            'user_id' => 0
        );
        $customer_id = $this->getIdDescCustomer($comment['customers_id']);
        if(!$customer_id){
            $customer_id = 0;
        }
        $comment_data['user_id'] = $customer_id;
        $custom = $this->_custom->getCommentIdCustom($this, $comment, $commentsExt);
        if($custom){
            $comment_data = array_merge($comment_data, $custom);
        }
        return array(
            'result' => "success",
            'data' => $comment_data
        );
    }

    public function importComment($data, $comment, $commentsExt)
    {
        if (LeCaMgCustom::COMMENT_IMPORT) {
            return $this->_custom->importCommentCustom($this, $data, $comment, $commentsExt);
        }
        $response = $this->_defaultResponse();
        $id_src = $this->getCommentId($comment, $commentsExt);
        $id_desc = $this->comment($data);
        if ($id_desc) {
            $this->commentSuccess($id_src, $id_desc);
            $response['result'] = "success";
            $response['id_desc'] = $id_desc;
        } else {
            $response['result'] = "warning";
            $msg = "Comment Id = " . $id_src . " import failed.";
            $response['msg'] = $this->consoleWarning($msg);
        }
        return $response;
    }

    public function additionComment($convert, $comment, $commentsExt)
    {
        if (LeCaMgCustom::COMMENT_ADDITION) {
            return $this->_custom->additionProductCustom($this, $convert, $comment, $commentsExt);
        }
        return array(
            'result' => "success"
        );
    }

    public function afterSaveComment($comment_id_desc, $convert, $comment, $commentsExt)
    {
        $this->_custom->afterSaveCommentCustom($this, $comment_id_desc, $convert, $comment, $commentsExt);
        return LeCaMgCustom::COMMENT_AFTER_SAVE;
    }

    public function finish()
    {
        wc_delete_product_transients();
        wc_delete_shop_order_transients();
        delete_transient('wc_attribute_taxonomies');
        $cat_ids = $shipping_ids = $attr_val_ids = $tag_ids = array();
        $cats = $this->selectTable(LeCaMg::TABLE_IMPORT, array(
            'type' => self::TYPE_CATEGORY,
            'domain' => $this->_cart_url
        ), 'id_desc');
        $shipping = $this->selectTable(LeCaMg::TABLE_IMPORT, array(
            'type' => self::TYPE_SHIPPING,
            'domain' => $this->_cart_url
        ), 'id_desc');
        $tags = $this->selectTable(LeCaMg::TABLE_IMPORT, array(
            'type' => self::TYPE_TAG,
            'domain' => $this->_cart_url
        ), 'id_desc');
        if ($cats) {
            foreach ($cats as $row) {
                $cat_ids[] = $row['id_desc'];
            }
        }
        if ($tags) {
            foreach ($tags as $row) {
                $tag_ids[] = $row['id_desc'];
            }
        }
        if ($shipping) {
            foreach ($shipping as $row) {
                $shipping_ids[] = $row['id_desc'];
            }
        }
        if (!empty($cat_ids)) {
            clean_term_cache($cat_ids[0], 'product_cat');
            wp_update_term_count($cat_ids, 'product_cat');
        }
        if (!empty($tag_ids)) {
            clean_term_cache($tag_ids[0], 'product_tag');
            wp_update_term_count($tag_ids, 'product_tag');
        }
        if (!empty($shipping_ids)) {
            clean_term_cache($shipping_ids[0], 'product_shipping_class');
            _update_generic_term_count($shipping_ids, 'product_shipping_class');
        }
        return true;
    }

    protected function _limit($total)
    {        
        $limit = 500;
        $this->_notice['config']['limit'] = $limit ? $limit : 0;
        $data = array();
        if (!$limit) {
            foreach ($total as $type => $count) {
                $data[$type] = 0;
            }
            return $data;
        } else {
            if ($limit !== 'unlimited') {
                foreach ($total as $type => $count) {
                    $new_count = ($count < $limit) ? $count : $limit;
                    $total[$type] = $new_count;
                }
            }
        }
        if (LeCaMg::DEMO_MODE) {
            $this->_notice['config']['limit'] = 10;
            $data = array();
            foreach ($total as $type => $count) {
                $data[$type] = ($count > $this->_limit_demo[$type]) ? $this->_limit_demo[$type] : $count;
            }
            return $data;
        }
        return $total;
    }

    protected function _checkLicense()
    {
        return true;
    }

    public function updateLicense()
    {        
        return;
    }

    /**
     * TODO: TRANSFER
     */

    public function tax($data)
    {
        $class_name = $data['name'];
        $class_exists = $this->taxExists($class_name);
        if ($class_exists) {
            return array(
                'result' => "success",
                'id_desc' => '',
                'value' => sanitize_title($class_name),
            );
        } else {
            $class = get_option(LeCaMg::WOO_TAX_SETTING);
            if ($class) {
                $class .= "\n";
            }
            $class .= $class_name;
            update_option(LeCaMg::WOO_TAX_SETTING, $class);
            return array(
                'result' => "success",
                'id_desc' => '',
                'value' => sanitize_title($class_name),
            );
        }
    }

    public function manufacturer($data)
    {
        return array(
            'result' => "success",
            'id_desc' => 1
        );
    }

    public function category($data, $is_cat = false)
    {
        $meta_data = array();
        if (isset($data['meta'])) {
            $meta_data = $data['meta'];
            unset($data['meta']);
        }
        $term_data = $this->getValueFromArray($data, array('name', 'slug', 'term_group'));
        $term_taxonomy_data = $this->getValueFromArray($data, array('taxonomy', 'description', 'parent', 'count'));
        $term_id = $this->wpTerm($term_data);
        if (!$term_id) {
            return false;
        }
        $term_taxonomy_data['term_id'] = $term_id;
        $term_taxonomy_id = $this->wpTermTaxonomy($term_taxonomy_data);
        if (!$term_taxonomy_id) {
            return false;
        }
        if ($meta_data) {
            foreach ($meta_data as $meta_key => $meta_value) {
                $data = array(
                    'woocommerce_term_id' => $term_id,
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value
                );
                $this->wooTermMeta($data);
            }
        }
        if ($is_cat) {
            return array(
                'term_taxonomy_id' => $term_taxonomy_id,
                'term_id' => $term_id
            );
        } else {
            return $term_taxonomy_id;
        }
    }

    public function product($data)
    {
        $product_data = $meta_data = array();
        if (isset($data['meta'])) {
            $meta_data = $data['meta'];
            unset($data['meta']);
        }
        $product_data = $data;
        $product_data['post_date'] = $this->_datetimeToDate($product_data['post_date']);
        $product_data['post_date_gmt'] =  $product_data['post_date'];
        if(!$product_data['post_modified']){
            $product_data['post_modified'] = $product_data['post_date'];
            $product_data['post_modified_gmt'] = $product_data['post_modified'];
        }
        $new_post_name = $product_data['post_name'];
        $i = 1;
        while($check_post_name = $this->selectTableRow(self::WP_POSTS, array('post_name' => $new_post_name, 'post_type' => 'product'))){
            $new_post_name = $new_post_name . '-' . $i;
            $i++;
        }
        $product_data['post_name'] = $new_post_name;
        $product_id = $this->wpPost($product_data);
        if (!$product_id) {
            return false;
        }
        if ($meta_data) {
            foreach ($meta_data as $meta_key => $meta_value) {
                $data = array(
                    'post_id' => $product_id,
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value
                );
                $this->wpPostMeta($data);
            }
        }
        return $product_id;
    }

    public function customer($data)
    {
        $db = $this->getDB();
        $customer_data = $meta_data = array();
        if (isset($data['meta'])) {
            $meta_data = $data['meta'];
            unset($data['meta']);
        }
        $customer_data = $data;
        $customer_id = $this->wpUser($customer_data);
        if (!$customer_id) {
            return false;
        }
        if ($meta_data) {
            foreach ($meta_data as $meta_key => $meta_value) {
                if ($meta_key == 'wp_capabilities') {
                    $meta_key = $db->prefix . 'capabilities';
                }
                $data = array(
                    'user_id' => $customer_id,
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value
                );
                $this->wpUserMeta($data);
            }
        }
        return $customer_id;
    }

    public function order($data)
    {
        $order_data = $meta_data = array();
        if (isset($data['meta'])) {
            $meta_data = $data['meta'];
            unset($data['meta']);
        }
        $order_data = $data;
        $order_data['post_date'] = $this->_datetimeToDate($order_data['post_date']);
        $order_data['post_date_gmt'] =  $order_data['post_date'];
        if(!$order_data['post_modified']){
            $order_data['post_modified'] = $order_data['post_date'];
            $order_data['post_modified_gmt'] = $order_data['post_modified'];
        }
        $order_id = $this->wpPost($order_data);
        if (!$order_id) {
            return false;
        }
        if ($meta_data) {
            foreach ($meta_data as $meta_key => $meta_value) {
                $data = array(
                    'post_id' => $order_id,
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value
                );
                $this->wpPostMeta($data);
            }
        }
        return $order_id;
    }

    public function review($data)
    {
        $review_data = $meta_data = array();
        if (isset($data['meta'])) {
            $meta_data = $data['meta'];
            unset($data['meta']);
        }
        $review_data = $data;
        $review_id = $this->wpComment($review_data);
        if (!$review_id) {
            return false;
        }
        if ($meta_data) {
            foreach ($meta_data as $meta_key => $meta_value) {
                $data = array(
                    'comment_id' => $review_id,
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value
                );
                $this->wpCommentMeta($data);
            }
        }
        return $review_id;
    }

    public function page($data)
    {
        $page_data = $meta_data = array();
        if (isset($data['meta'])) {
            $meta_data = $data['meta'];
            unset($data['meta']);
        }
        $page_data = $data;
        $page_id = $this->wpPost($page_data);
        if (!$page_id) {
            return false;
        }
        if ($meta_data) {
            foreach ($meta_data as $meta_key => $meta_value) {
                $data = array(
                    'post_id' => $page_id,
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value
                );
                $this->wpPostMeta($data);
            }
        }
        return $page_id;
    }

    public function post_category($data, $is_cat = false)
    {
        $term_data = $this->getValueFromArray($data, array('name', 'slug', 'term_group'));
        $term_taxonomy_data = $this->getValueFromArray($data, array('taxonomy', 'description', 'parent', 'count'));
        $term_id = $this->wpTerm($term_data);
        if (!$term_id) {
            return false;
        }
        $term_taxonomy_data['term_id'] = $term_id;
        $term_taxonomy_id = $this->wpTermTaxonomy($term_taxonomy_data);
        if (!$term_taxonomy_id) {
            return false;
        }
        if ($is_cat) {
            return array(
                'term_taxonomy_id' => $term_taxonomy_id,
                'term_id' => $term_id
            );
        } else {
            return $term_taxonomy_id;
        }
    }

    public function post($data)
    {
        $post_data = $meta_data = array();
        if (isset($data['meta'])) {
            $meta_data = $data['meta'];
            unset($data['meta']);
        }
        $post_data = $data;
        $post_id = $this->wpPost($post_data);
        if (!$post_id) {
            return false;
        }
        if ($meta_data) {
            foreach ($meta_data as $meta_key => $meta_value) {
                $data = array(
                    'post_id' => $post_id,
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value
                );
                $this->wpPostMeta($data);
            }
        }
        return $post_id;
    }

    public function comment($data)
    {
        $comment_data = array();
        $comment_data = $data;
        $comment_id = $this->wpComment($comment_data);
        if (!$comment_id) {
            return false;
        }
        return $comment_id;
    }

    /**
     * TODO: Import Connector
     */
    protected function _checkConnector()
    {
        $response = $this->_defaultResponse();
        if (strpos($this->_cart_url, self::CONNECTOR_SUFFIX) !== false) {
            $this->_cart_url = str_replace(self::CONNECTOR_SUFFIX, '', $this->_cart_url);
            $this->_notice['config']['cart_url'] = $this->_cart_url;
        }
        $this->_notice['curl']['useragent'] = true;
        $curl_agent = $this->_requestByPost($this->getUrlConnector('check'), array(), true);
        if(!$curl_agent){
        	$this->_notice['curl']['useragent'] = false;
        }
        $check = $this->getConnectorData($this->getUrlConnector('check'), array(), true);
        if (!$check) {
            $response['result'] = "warning";
            $response['elm'] = "#error-url";
            $response['msg'] = "Cannot reach connector! It should be uploaded at: " . $this->getUrlSuffix(self::CONNECTOR_SUFFIX);
            return $response;
        }
        if ($check['result'] != "success") {
            $response['result'] = "warning";
            $response['elm'] = "#error-token";
            return $response;
        }
        $object = $check['object'];
        if (!$this->_checkCartTypeSync($object['cms'], $this->_notice['config']['cart_type'])) {
            $response['result'] = "warning";
            $response['elm'] = "#error-type";
            return $response;
        }
        $data = $object['connect'];
        if (!$data || $data['result'] != "success") {
            $response['result'] = "warning";
            $response['elm'] = "#error-url";
            $response['msg'] = "Cannot reach database from connector!";
            return $response;
        }
        $this->_notice['config']['cart_version'] = $object['version'];
        $this->_notice['config']['table_prefix'] = $object['table_prefix'];
        $this->_notice['config']['charset'] = $object['charset'];
        $this->_notice['config']['image_product'] = $object['image_product'];
        $this->_notice['config']['image_category'] = $object['image_category'];
        $this->_notice['config']['image_manufacturer'] = $object['image_manufacturer'];
        $this->_notice['extend']['cookie_key'] = isset($object['cookie_key']) ? $object['cookie_key'] : "";
        $response['result'] = 'success';
        return $response;
    }

    protected function _getTaxesMainFromConnector()
    {
        $query = $this->_getTaxesMainQuery();
        $taxes = $this->getConnectorData($this->getUrlConnector('query'), array(
            'query' => $query
        ));
        if (!$taxes || $taxes['result'] != "success") {
            return $this->errorConnector();
        }
        return $taxes;
    }

    protected function _getTaxesExtraFromConnector($taxes)
    {
        $taxesExt = array(
            'result' => "success"
        );
        $ext_query = $this->_getTaxesExtraQuery($taxes);
        $cus_ext_query = $this->_custom->getTaxesExtQueryCustom($this, $taxes);
        if ($cus_ext_query) {
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if ($ext_query) {
            $taxesExt = $this->getConnectorData($this->getUrlConnector('query'), array(
                'serialize' => true,
                "query" => serialize($ext_query)
            ));
            if (!$taxesExt || $taxesExt['result'] != 'success') {
                return $this->errorConnector();
            }
            $ext_rel_query = $this->_getTaxesExtraRelQuery($taxes, $taxesExt);
            $cus_ext_rel_query = $this->_custom->getTaxesExtRelQueryCustom($this, $taxes, $taxesExt);
            if ($cus_ext_rel_query) {
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if ($ext_rel_query) {
                $taxesExtRel = $this->getConnectorData($this->getUrlConnector('query'), array(
                    'serialize' => true,
                    "query" => serialize($ext_rel_query)
                ));
                if (!$taxesExtRel || $taxesExtRel['result'] != 'success') {
                    return $this->errorConnector(true);
                }
                $taxesExt = $this->syncConnectorObject($taxesExt, $taxesExtRel);
            }
        }
        return $taxesExt;
    }

    protected function _getManufacturersMainFromConnector()
    {
        $query = $this->_getManufacturersMainQuery();
        $manufacturers = $this->getConnectorData($this->getUrlConnector('query'), array(
            'query' => $query
        ));
        if (!$manufacturers || $manufacturers['result'] != "success") {
            return $this->errorConnector();
        }
        return $manufacturers;
    }

    protected function _getManufacturersExtraFromConnector($manufacturers)
    {
        $manufacturersExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getManufacturersExtraQuery($manufacturers);
        $cus_ext_query = $this->_custom->getManufacturersExtQueryCustom($this, $manufacturers);
        if ($cus_ext_query) {
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if ($ext_query) {
            $manufacturersExt = $this->getConnectorData($this->getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if (!$manufacturersExt || $manufacturersExt['result'] != 'success') {
                return $this->errorConnector();
            }
            $ext_rel_query = $this->_getManufacturersExtraRelQuery($manufacturers, $manufacturersExt);
            $cus_ext_rel_query = $this->_custom->getManufacturersExtRelQueryCustom($this, $manufacturers, $manufacturersExt);
            if ($cus_ext_rel_query) {
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if ($ext_rel_query) {
                $manufacturersExtRel = $this->getConnectorData($this->getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if (!$manufacturersExtRel || $manufacturersExtRel['result'] != 'success') {
                    return $this->errorConnector();
                }
                $manufacturersExt = $this->syncConnectorObject($manufacturersExt, $manufacturersExtRel);
            }
        }
        return $manufacturersExt;
    }

    protected function _getCategoriesMainFromConnector()
    {
        $query = $this->_getCategoriesMainQuery();
        $categories = $this->getConnectorData($this->getUrlConnector('query'), array(
            'query' => $query
        ));
        if (!$categories || $categories['result'] != "success") {
            return $this->errorConnector();
        }
        if ($this->_notice['config']['add_option']['seo'] && $this->_notice['config']['add_option']['seo_plugin']) {
            $seo_plugin = $this->_notice['config']['add_option']['seo_plugin'];
            $this->_seo = LeCaMg::getModel($seo_plugin);
        }
        return $categories;
    }

    protected function _getCategoriesExtraFromConnector($categories)
    {
        $categoriesExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getCategoriesExtraQuery($categories);
        if ($this->_seo) {
            $seo_ext_query = $this->_seo->getCategoriesExtQuery($this, $categories);
            if ($seo_ext_query) {
                $ext_query = array_merge($ext_query, $seo_ext_query);
            }
        }
        $cus_ext_query = $this->_custom->getCategoriesExtQueryCustom($this, $categories);
        if ($cus_ext_query) {
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if ($ext_query) {
            $categoriesExt = $this->getConnectorData($this->getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if (!$categoriesExt || $categoriesExt['result'] != 'success') {
                return $this->errorConnector();
            }
            $ext_rel_query = $this->_getCategoriesExtraRelQuery($categories, $categoriesExt);
            if ($this->_seo) {
                $seo_ext_rel_query = $this->_seo->getCategoriesExtRelQuery($this, $categories, $categoriesExt);
                if ($seo_ext_rel_query) {
                    $ext_rel_query = array_merge($ext_rel_query, $seo_ext_rel_query);
                }
            }
            $cus_ext_rel_query = $this->_custom->getCategoriesExtRelQueryCustom($this, $categories, $categoriesExt);
            if ($cus_ext_rel_query) {
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if ($ext_rel_query) {
                $categoriesExtRel = $this->getConnectorData($this->getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if (!$categoriesExtRel || $categoriesExtRel['result'] != 'success') {
                    return $this->errorConnector(true);
                }
                $categoriesExt = $this->syncConnectorObject($categoriesExt, $categoriesExtRel);
            }
        }
        return $categoriesExt;
    }

    protected function _getProductsMainFromConnector()
    {
        $query = $this->_getProductsMainQuery();
        $products = $this->getConnectorData($this->getUrlConnector('query'), array(
            'query' => $query
        ));
        if (!$products || $products['result'] != "success") {
            return $this->errorConnector();
        }
        if ($this->_notice['config']['add_option']['seo'] && $this->_notice['config']['add_option']['seo_plugin']) {
            $seo_plugin = $this->_notice['config']['add_option']['seo_plugin'];
            $this->_seo = LeCaMg::getModel($seo_plugin);
        }
        return $products;
    }

    protected function _getProductsExtraFromConnector($products)
    {
        $productsExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getProductsExtraQuery($products);
        if ($this->_seo) {
            $seo_ext_query = $this->_seo->getProductsExtQuery($this, $products);
            if ($seo_ext_query) {
                $ext_query = array_merge($ext_query, $seo_ext_query);
            }
        }
        $cus_ext_query = $this->_custom->getProductsExtQueryCustom($this, $products);
        if ($cus_ext_query) {
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if ($ext_query) {
            $productsExt = $this->getConnectorData($this->getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if (!$productsExt || $productsExt['result'] != 'success') {
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getProductsExtraRelQuery($products, $productsExt);
            if ($this->_seo) {
                $seo_ext_rel_query = $this->_seo->getProductsExtRelQuery($this, $products, $productsExt);
                if ($seo_ext_rel_query) {
                    $ext_rel_query = array_merge($ext_rel_query, $seo_ext_rel_query);
                }
            }
            $cus_ext_rel_query = $this->_custom->getProductsExtRelQueryCustom($this, $products, $productsExt);
            if ($cus_ext_rel_query) {
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if ($ext_rel_query) {
                $productsExtRel = $this->getConnectorData($this->getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if (!$productsExtRel || $productsExtRel['result'] != 'success') {
                    return $this->errorConnector(true);
                }
                $productsExt = $this->syncConnectorObject($productsExt, $productsExtRel);
            }
        }
        return $productsExt;
    }

    protected function _getCustomersMainFromConnector()
    {
        $query = $this->_getCustomersMainQuery();
        $customers = $this->getConnectorData($this->getUrlConnector('query'), array(
            'query' => $query
        ));
        if (!$customers || $customers['result'] != "success") {
            return $this->errorConnector();
        }
        return $customers;
    }

    protected function _getCustomersExtraFromConnector($customers)
    {
        $customersExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getCustomersExtraQuery($customers);
        $cus_ext_query = $this->_custom->getCustomersExtQueryCustom($this, $customers);
        if ($cus_ext_query) {
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if ($ext_query) {
            $customersExt = $this->getConnectorData($this->getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if (!$customersExt || $customersExt['result'] != 'success') {
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getCustomersExtraRelQuery($customers, $customersExt);
            $cus_ext_rel_query = $this->_custom->getCustomerExtRelQueryCustom($this, $customers, $customersExt);
            if ($cus_ext_rel_query) {
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if ($ext_rel_query) {
                $customersExtRel = $this->getConnectorData($this->getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if (!$customersExtRel || $customersExtRel['result'] != 'success') {
                    return $this->errorConnector(true);
                }
                $customersExt = $this->syncConnectorObject($customersExt, $customersExtRel);
            }
        }
        return $customersExt;
    }

    protected function _getOrdersMainFromConnector()
    {
        $query = $this->_getOrdersMainQuery();
        $orders = $this->getConnectorData($this->getUrlConnector('query'), array(
            'query' => $query
        ));
        if (!$orders || $orders['result'] != "success") {
            return $this->errorConnector();
        }
        return $orders;
    }

    protected function _getOrdersExtraFromConnector($orders)
    {
        $ordersExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getOrdersExtraQuery($orders);
        $cus_ext_query = $this->_custom->getOrdersExtQueryCustom($this, $orders);
        if ($cus_ext_query) {
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if ($ext_query) {
            $ordersExt = $this->getConnectorData($this->getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if (!$ordersExt || $ordersExt['result'] != 'success') {
                return $this->errorConnector();
            }
            $ext_rel_query = $this->_getOrdersExtraRelQuery($orders, $ordersExt);
            $cus_ext_rel_query = $this->_custom->getOrdersExtRelQueryCustom($this, $orders, $ordersExt);
            if ($cus_ext_rel_query) {
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if ($ext_rel_query) {
                $ordersExtRel = $this->getConnectorData($this->getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if (!$ordersExtRel || $ordersExtRel['result'] != 'success') {
                    return $this->errorConnector();
                }
                $ordersExt = $this->syncConnectorObject($ordersExt, $ordersExtRel);
            }
        }
        return $ordersExt;
    }

    protected function _getReviewMainFromConnector()
    {
        $query = $this->_getReviewsMainQuery();
        $reviews = $this->getConnectorData($this->getUrlConnector('query'), array(
            'query' => $query
        ));
        if (!$reviews || $reviews['result'] != "success") {
            return $this->errorConnector();
        }
        return $reviews;
    }

    protected function _getReviewExtraFromConnector($reviews)
    {
        $reviewsExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getReviewsExtraQuery($reviews);
        $cus_ext_query = $this->_custom->getReviewsExtQueryCustom($this, $reviews);
        if ($cus_ext_query) {
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if ($ext_query) {
            $reviewsExt = $this->getConnectorData($this->getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if (!$reviewsExt || $reviewsExt['result'] != 'success') {
                return $this->errorConnector();
            }
            $ext_rel_query = $this->_getReviewsExtraRelQuery($reviews, $reviewsExt);
            $cus_ext_rel_query = $this->_custom->getReviewsExtRelQueryCustom($this, $reviews, $reviewsExt);
            if ($cus_ext_rel_query) {
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if ($ext_rel_query) {
                $reviewsExtRel = $this->getConnectorData($this->getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if (!$reviewsExtRel || $reviewsExtRel['result'] != 'success') {
                    return $this->errorConnector();
                }
                $reviewsExt = $this->syncConnectorObject($reviewsExt, $reviewsExtRel);
            }
        }
        return $reviewsExt;
    }

    protected function _getPagesMainFromConnector(){
        $query = $this->_getPagesMainQuery();
        $pages = $this->getConnectorData($this->getUrlConnector('query'), array(
            'query' => $query
        ));
        if (!$pages || $pages['result'] != "success") {
            return $this->errorConnector();
        }
        if ($this->_notice['config']['add_option']['seo'] && $this->_notice['config']['add_option']['seo_plugin']) {
            $seo_plugin = $this->_notice['config']['add_option']['seo_plugin'];
            $this->_seo = LeCaMg::getModel($seo_plugin);
        }
        return $pages;
    }

    protected function _getPagesExtraFromConnector($pages){
        $data = array();
        $ext_query = $this->_getPagesExtraQuery($pages);
        if($this->_seo){
            $seo_ext_query = $this->_seo->getPagesExtQuery($this, $pages);
            if($seo_ext_query){
                $ext_query = array_merge($ext_query, $seo_ext_query);
            }
        }
        $cus_ext_query = $this->_custom->getPagesExtQueryCustom($this, $pages);
        if($cus_ext_query){
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if($ext_query){
            $pagesExt = $this->getConnectorData($this->getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if(!$pagesExt || $pagesExt['result'] != 'success'){
                return $this->errorConnector(true);
            }
            $thumbnailIdList = $this->getListFromListByField($pagesExt['object']['pagemeta'], 'meta_key', '_thumbnail_id');
            $thumbnailIds = $this->duplicateFieldValueFromList($thumbnailIdList, 'meta_value');
            $thumbnailIds_query = $this->arrayToInCondition($thumbnailIds);
            $ext_third_query = array(
                'images' => "SELECT p.ID, p.post_title, pm.meta_value FROM _DBPRF_posts AS p
                                        LEFT JOIN _DBPRF_postmeta AS pm ON pm.post_id = p.ID AND pm.meta_key = '_wp_attached_file'
                                        WHERE p.ID IN {$thumbnailIds_query}"
            );
            $pagesExtThird = $this->getConnectorData($this->getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_third_query)
            ));
            if (!$pagesExtThird || $pagesExtThird['result'] != 'success') {
                return $this->errorConnector(true);
            }
            $data = array_merge($pagesExt['object'], $pagesExtThird['object']);
        }
        return array(
            'result' => 'success',
            'object' => $data
        );
    }

    protected function _getPostCatMainFromConnector()
    {
        $query = $this->_getPostCatMainQuery();
        $postCat = $this->getConnectorData($this->getUrlConnector('query'), array(
            'query' => $query
        ));
        if (!$postCat || $postCat['result'] != "success") {
            return $this->errorConnector();
        }
        if ($this->_notice['config']['add_option']['seo'] && $this->_notice['config']['add_option']['seo_plugin']) {
            $seo_plugin = $this->_notice['config']['add_option']['seo_plugin'];
            $this->_seo = LeCaMg::getModel($seo_plugin);
        }
        return $postCat;
    }

    protected function _getPostCatExtraFromConnector($postCat)
    {
        $postCatExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getPostCatExtraQuery($postCat);
        if ($this->_seo) {
            $seo_ext_query = $this->_seo->getPostCatExtQuery($this, $postCat);
            if ($seo_ext_query) {
                $ext_query = array_merge($ext_query, $seo_ext_query);
            }
        }
        $cus_ext_query = $this->_custom->getPostCatExtQueryCustom($this, $postCat);
        if ($cus_ext_query) {
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if ($ext_query) {
            $postCatExt = $this->getConnectorData($this->getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if (!$postCatExt || $postCatExt['result'] != 'success') {
                return $this->errorConnector();
            }
            $ext_rel_query = $this->_getPostCatExtraRelQuery($postCat, $postCatExt);
            if ($this->_seo) {
                $seo_ext_rel_query = $this->_seo->getPostCatExtRelQuery($this, $postCat, $postCatExt);
                if ($seo_ext_rel_query) {
                    $ext_rel_query = array_merge($ext_rel_query, $seo_ext_rel_query);
                }
            }
            $cus_ext_rel_query = $this->_custom->getPostCatExtRelQueryCustom($this, $postCat, $postCatExt);
            if ($cus_ext_rel_query) {
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if ($ext_rel_query) {
                $postCatExtRel = $this->getConnectorData($this->getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if (!$postCatExtRel || $postCatExtRel['result'] != 'success') {
                    return $this->errorConnector(true);
                }
                $postCatExt = $this->syncConnectorObject($postCat, $postCatExt);
            }
        }
        return  $postCatExt;
    }

    protected function _getPostsMainFromConnector(){
        $query = $this->_getPostsMainQuery();
        $posts = $this->getConnectorData($this->getUrlConnector('query'), array(
            'query' => $query
        ));
        if (!$posts || $posts['result'] != "success") {
            return $this->errorConnector();
        }
        if ($this->_notice['config']['add_option']['seo'] && $this->_notice['config']['add_option']['seo_plugin']) {
            $seo_plugin = $this->_notice['config']['add_option']['seo_plugin'];
            $this->_seo = LeCaMg::getModel($seo_plugin);
        }
        return $posts;
    }

    protected function _getPostsExtraFromConnector($posts){
        $data = array();
        $ext_query = $this->_getPostsExtraQuery($posts);
        if($this->_seo){
            $seo_ext_query = $this->_seo->getPostsExtQuery($this, $posts);
            if($seo_ext_query){
                $ext_query = array_merge($ext_query, $seo_ext_query);
            }
        }
        $cus_ext_query = $this->_custom->getPostsExtQueryCustom($this, $posts);
        if($cus_ext_query){
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if($ext_query){
            $postsExt = $this->getConnectorData($this->getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if(!$postsExt || $postsExt['result'] != 'success'){
                return $this->errorConnector(true);
            }
            $ext_rel_query = $this->_getPostsExtraRelQuery($posts, $postsExt);
            if($this->_seo){
                $seo_ext_rel_query = $this->_seo->getPostsExtRelQuery($this, $posts, $postsExt);
                if($seo_ext_rel_query){
                    $ext_rel_query = array_merge($ext_rel_query, $seo_ext_rel_query);
                }
            }
            $cus_ext_rel_query = $this->_custom->getPostsExtRelQueryCustom($this, $posts, $postsExt);
            if($cus_ext_rel_query){
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if($ext_rel_query){
                $postsExtRel = $this->getConnectorData($this->getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if(!$postsExtRel || $postsExtRel['result'] != 'success'){
                    return $this->errorConnector(true);
                }
                $thumbnailIdList = $this->getListFromListByField($postsExtRel['object']['postmeta'], 'meta_key', '_thumbnail_id');
                $thumbnailIds = $this->duplicateFieldValueFromList($thumbnailIdList, 'meta_value');
                $thumbnailIds_query = $this->arrayToInCondition($thumbnailIds);
                $ext_third_query = array(
                    'images' => "SELECT p.ID, p.post_title, pm.meta_value FROM _DBPRF_posts AS p
                                        LEFT JOIN _DBPRF_postmeta AS pm ON pm.post_id = p.ID AND pm.meta_key = '_wp_attached_file'
                                        WHERE p.ID IN {$thumbnailIds_query}"
                );
                $postsExtThird = $this->getConnectorData($this->getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_third_query)
                ));
                if(!$postsExtThird || $postsExtThird['result'] != 'success'){
                    return $this->errorConnector(true);
                }
                $data = array_merge($postsExt['object'], $postsExtRel['object'], $postsExtThird['object']);
            }
        }
        return array(
            'result' => 'success',
            'object' => $data
        );
    }

    protected function _getCommentsMainFromConnector(){
        $query = $this->_getCommentsMainQuery();
        $comments = $this->getConnectorData($this->getUrlConnector('query'), array(
            'query' => $query
        ));
        if (!$comments || $comments['result'] != "success") {
            return $this->errorConnector();
        }
        return $comments;
    }

    protected function _getCommentsExtraFromConnector($comments){
        $commentsExt = array(
            'result' => 'success'
        );
        $ext_query = $this->_getReviewsExtraQuery($comments);
        $cus_ext_query = $this->_custom->getReviewsExtQueryCustom($this, $comments);
        if ($cus_ext_query) {
            $ext_query = array_merge($ext_query, $cus_ext_query);
        }
        if ($ext_query) {
            $reviewsExt = $this->getConnectorData($this->getUrlConnector('query'), array(
                'serialize' => true,
                'query' => serialize($ext_query)
            ));
            if (!$reviewsExt || $reviewsExt['result'] != 'success') {
                return $this->errorConnector();
            }
            $ext_rel_query = $this->_getReviewsExtraRelQuery($comments, $commentsExt);
            $cus_ext_rel_query = $this->_custom->getReviewsExtRelQueryCustom($this, $comments, $commentsExt);
            if ($cus_ext_rel_query) {
                $ext_rel_query = array_merge($ext_rel_query, $cus_ext_rel_query);
            }
            if ($ext_rel_query) {
                $commentsExtRel = $this->getConnectorData($this->getUrlConnector('query'), array(
                    'serialize' => true,
                    'query' => serialize($ext_rel_query)
                ));
                if (!$commentsExtRel || $commentsExtRel['result'] != 'success') {
                    return $this->errorConnector();
                }
                $commentsExt = $this->syncConnectorObject($comments, $commentsExt);
            }
        }
        return $commentsExt;
    }

    /**
     * TODO: WordPress
     */

    public function getDB()
    {
        if (!$this->_db) {
            $this->_db = LeCaMg::getGlobal('wpdb');
            $this->_db->show_errors(LeCaMg::DEV_MODE);
        }
        return $this->_db;
    }

    public function selectTable($table, $where = array(), $field = '*')
    {
        $db = $this->getDB();
        $table_name = ($table == self::WP_USERS || $table == self::WP_USER_META) ? $db->base_prefix . $table : $db->prefix . $table;
        $query = "SELECT " . $field . " FROM `" . $table_name . "`";
        if ($where && is_array($where)) {
            $condition = $this->arrayToWhereCondition($where);
            $query .= " WHERE " . $condition;
            $query = $db->prepare($query, $where);
        }
        return $db->get_results($query, ARRAY_A);
    }

    public function selectTableRow($table, $where = array(), $field = '*')
    {
        $result = $this->selectTable($table, $where, $field);
        if ($result) {
            $result = $result[0];
        }
        return $result;
    }

    public function insertTable($table, $data, $return_id = false)
    {
        $db = $this->getDB();
        $table_name = ($table == self::WP_USERS || $table == self::WP_USER_META) ? $db->base_prefix . $table : $db->prefix . $table;
        $result = $db->insert($table_name, $data);
        if ($result && $return_id) {
            $result = $db->insert_id;
        }
        return $result;
    }

    public function updateTable($table, $data, $where = array())
    {
        $db = $this->getDB();
        $table_name = ($table == self::WP_USERS || $table == self::WP_USER_META) ? $db->base_prefix . $table : $db->prefix . $table;
        if (!$data || !is_array($data)) {
            return false;
        }
        $bind = array_values($data);
        $set_condition = $this->arrayToSetCondition($data);
        $query = "UPDATE `" . $table_name . "` SET " . $set_condition;
        if ($where && is_array($where)) {
            $where_condition = $this->arrayToWhereCondition($where);
            $query .= " WHERE " . $where_condition;
            $bind = array_merge(array_values($data), array_values($where));
        }
        $query = $db->prepare($query, $bind);
        return $db->query($query);
    }

    public function deleteTable($table, $where = array())
    {
        $db = $this->getDB();
        $table_name = ($table == self::WP_USERS || $table == self::WP_USER_META) ? $db->base_prefix . $table : $db->prefix . $table;
        $query = "DELETE FROM `" . $table_name . "`";
        if ($where && is_array($where)) {
            $where_condition = $this->arrayToWhereCondition($where);
            $query .= " WHERE " . $where_condition;
            $query = $db->prepare($query, $where);
        }
        return $db->query($query);
    }

    public function emptyTable($table)
    {
        $db = $this->getDB();
        $table_name = ($table == self::WP_USERS || $table == self::WP_USER_META) ? $db->base_prefix . $table : $db->prefix . $table;
        $query = "TRUNCATE TABLE `" . $table_name . "`";
        return $db->query($query);
    }

    public function isTableExists($table)
    {
        $db = $this->getDB();
        $table_name = ($table == self::WP_USERS || $table == self::WP_USER_META) ? $db->base_prefix . $table : $db->prefix . $table;
        $query = "SHOW TABLES LIKE '" . esc_sql($table_name) . "'";
        $result = $db->get_results($query, ARRAY_A);
        return $result ? true : false;
    }

    public function arrayToWhereCondition($array = array(), $wp = true)
    {
        if (!$array || !is_array($array)) {
            return '1 = 1';
        }
        if ($wp) {
            $data = array();
            $keys = array_keys($array);
            foreach ($keys as $key) {
                $data[] = "`" . $key . "` = %s";
            }
            $condition = implode(" AND ", $data);
            return $condition;
        } else {
            $data = array();
            foreach ($array as $key => $value) {
                $data[] = "`" . $key . "` = '" . esc_sql($value) . "'";
            }
            $condition = implode(" AND ", $data);
            return $condition;
        }
    }

    public function arrayToSetCondition($array, $wp = true)
    {
        if (!$array || !is_array($array)) {
            return false;
        }
        if ($wp) {
            $data = array();
            $keys = array_keys($array);
            foreach ($keys as $key) {
                $data[] = "`" . $key . "` = %s";
            }
            $condition = implode(", ", $data);
            return $condition;
        } else {
            $data = array();
            foreach ($array as $key => $value) {
                $data[] = "`" . $key . "` = '" . esc_sql($value) . "'";
            }
            $condition = implode(", ", $data);
            return $condition;
        }
    }

    public function arrayToInCondition($array = array(), $wp = false)
    {
        if (!$array) {
            return "(null)";
        }
        if ($wp) {
            $data = array();
            foreach ($array as $value) {
                $data[] = "%s";
            }
            $condition = "(" . implode(',', $data) . ")";
            return $condition;
        } else {
            $data = array();
            foreach ($array as $value) {
                $data[] = "'" . esc_sql($value) . "'";
            }
            $condition = "(" . implode(",", $data) . ")";
            return $condition;
        }
    }

    public function wpCommentMeta($data)
    {
        return $this->insertTable(self::WP_COMMENT_META, $data, true);
    }

    public function wpComment($data)
    {
        return $this->insertTable(self::WP_COMMENT, $data, true);
    }

    public function wpLink($data)
    {
        return $this->insertTable(self::WP_LINKS, $data, true);
    }

    public function wpOption($data)
    {
        return $this->insertTable(self::WP_OPTIONS, $data, true);
    }

    public function wpPostMeta($data)
    {
        return $this->insertTable(self::WP_POST_META, $data, true);
    }

    public function wpPostMetaUpdate($data, $where)
    {
        return $this->updateTable(self::WP_POST_META, $data, $where);
    }

    public function wpPost($data)
    {
        return $this->insertTable(self::WP_POSTS, $data, true);
    }

    public function wpTerm($data)
    {
        return $this->insertTable(self::WP_TERM, $data, true);
    }

    public function wpTermRelationship($data)
    {
        return $this->insertTable(self::WP_TERM_RELATION, $data, true);
    }

    public function wpTermTaxonomy($data)
    {
        return $this->insertTable(self::WP_TERM_TAXONOMY, $data, true);
    }

    public function wpUserMeta($data)
    {
        return $this->insertTable(self::WP_USER_META, $data, true);
    }

    public function wpUser($data)
    {
        return $this->insertTable(self::WP_USERS, $data, true);
    }

    public function wpImage($path, $title = null)
    {
        $real_path = $this->uploadDir() . '/' . ltrim($path, '/');
        $url = $this->uploadUrl() . '/' . ltrim($path, '/');
        $post_data = array(
            'post_author' => $this->wpCurrentUserId(),
            'post_date' => current_time('mysql'),
            'post_date_gmt' => current_time('mysql', true),
            'post_title' => $title ? $title : 'images',
            'post_status' => 'inherit',
            'comment_status' => 'open',
            'ping_status' => 'open',
            'post_name' => basename($path),
            'post_parent' => 0,
            'guid' => $url,
            'menu_order' => 0,
            'post_type' => 'attachment',
            'post_mime_type' => mime_content_type($real_path)
        );
        $post_id = $this->wpPost($post_data);
        if (!$post_id) {
            return false;
        }
        $meta = array(
            'post_id' => $post_id,
            'meta_key' => '_wp_attached_file',
            'meta_value' => ltrim($path, '/')
        );
        $this->wpPostMeta($meta);
        $meta = array(
            'post_id' => $post_id,
            'meta_key' => '_wp_attachment_metadata',
            'meta_value' => serialize(wp_generate_attachment_metadata($post_id, $real_path)),
        );
        $this->wpPostMeta($meta);
        return $post_id;
    }

    public function wpCurrentUserId()
    {
        if (!$this->_user_id) {
            $this->_user_id = get_current_user_id();
        }
        return $this->_user_id;
    }

    /**
     * TODO: WooCommerce
     */

    public function wooAttributeTaxonomy($data)
    {
        return $this->insertTable(self::WOO_ATTR_TAXONOMY, $data, true);
    }

    public function wooDownloadProductPermission($data)
    {
        return $this->insertTable(self::WOO_DOWNLOAD_PERMISSION, $data, true);
    }

    public function wooOrderMeta($data)
    {
        return $this->insertTable(self::WOO_ORDER_META, $data, true);
    }

    public function wooOrderItem($data)
    {
        return $this->insertTable(self::WOO_ORDER_ITEM, $data, true);
    }

    public function wooTaxRate($data)
    {
        return $this->insertTable(self::WOO_TAX_RATE, $data, true);
    }

    public function wooTaxRateLocation($data)
    {
        return $this->insertTable(self::WOO_TAX_RATE_LOCATION, $data, true);
    }

    public function wooTermMeta($data)
    {
        if($this->_woo_term_meta_table == 'termmeta'){
            $data['term_id'] = $data['woocommerce_term_id'];
            unset($data['woocommerce_term_id']);
        }
        return $this->insertTable($this->_woo_term_meta_table, $data, true);
    }

    public function taxExists($class_name)
    {
        $tax_classes = WC_Tax::get_tax_classes();
        return in_array($class_name, (array)$tax_classes);
    }

    public function getProductType()
    {
        if (!$this->_productType) {
            $db = $this->getDB();
            $query = "SELECT * FROM " . $db->prefix . self::WP_TERM_TAXONOMY . " AS tt LEFT JOIN " . $db->prefix . self::WP_TERM . " AS t ON t.term_id = tt.term_id WHERE tt.taxonomy = 'product_type'";
            $result = $db->get_results($query, ARRAY_A);
            if (!$result) {
                return $this->_defaultProductType();
            }
            $data = array();
            foreach ($result as $row) {
                $key = $row['name'];
                $value = $row['term_taxonomy_id'];
                $data[$key] = $value;
            }
            $this->_productType = $data;
        }
        return $this->_productType;
    }

    protected function _defaultProductType()
    {
        return array(
            'simple' => 0,
            'grouped' => 0,
            'variable' => 0,
            'external' => 0
        );
    }

    /**
     * TODO: Clear
     */

    protected function _clearProducts()
    {
        if (!$this->_notice['config']['import']['products']) {
            $this->_notice['clear_info']['function'] = '_clearCategories';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
        $product_exists = $this->_checkProductExists();
        if ($product_exists) {
            $db = $this->getDB();
            $table_post = $db->prefix . self::WP_POSTS;
            $table_post_meta = $db->prefix . self::WP_POST_META;
            $del_product_query = "DELETE FROM `" . $table_post . "` WHERE post_type IN ( 'product', 'product_variation') LIMIT " . $this->_notice['clear_info']['limit'];
            $del_product_result = $db->query($del_product_query);
            if (!$del_product_result) {
                return $this->errorClear('product');
            }
            $del_product_meta_query = "DELETE " . $table_post_meta . " FROM " . $table_post_meta . " LEFT JOIN " . $table_post . " ON " . $table_post . ".ID = " . $table_post_meta . ".post_id WHERE " . $table_post . ".ID IS NULL";
            $del_product_meta_result = $db->query($del_product_meta_query);
            if ($del_product_meta_result === false) {
                return $this->errorClear('product');
            }
            if (!$this->_notice['config']['import']['reviews']) {
                $table_comment = $db->prefix . self::WP_COMMENT;
                $table_comment_meta = $db->prefix . self::WP_COMMENT_META;
                $del_product_comment_query = "DELETE " . $table_comment . " FROM " . $table_comment . " LEFT JOIN " . $table_post . " ON " . $table_post . ".ID = " . $table_comment . ".comment_post_ID WHERE " . $table_post . ".ID IS NULL";
                $del_product_comment_result = $db->query($del_product_comment_query);
                if ($del_product_comment_result === false) {
                    return $this->errorClear('product');
                }
                $del_comment_meta_query = "DELETE " . $table_comment_meta . " FROM " . $table_comment_meta . " LEFT JOIN " . $table_comment . " ON " . $table_comment . ".comment_ID = " . $table_comment_meta . ".comment_id WHERE " . $table_comment . ".comment_ID IS NULL";
                $del_comment_meta_result = $db->query($del_comment_meta_query);
                if ($del_comment_meta_result === false) {
                    return $this->errorClear('product');
                }
            }
            $this->_notice['clear_info']['function'] = '_clearProducts';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        } else {
            $this->_notice['clear_info']['function'] = '_clearAttributes';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
    }

    protected function _clearAttributes()
    {
        $db = $this->getDB();
        $del_transient_attr_query = "DELETE FROM `" . $db->prefix . self::WP_OPTIONS . "` WHERE option_name = '_transient_wc_attribute_taxonomies'";
        $db->query($del_transient_attr_query);
        $product_attribute_query = "SELECT * FROM `" . $db->prefix . self::WOO_ATTR_TAXONOMY . "` ORDER BY attribute_id LIMIT " . $this->_notice['clear_info']['limit'];
        $product_attribute_result = $db->get_results($product_attribute_query, ARRAY_A);
        if ($product_attribute_result) {
            $attributeId = $this->duplicateFieldValueFromList($product_attribute_result, 'attribute_id');
            $attributeName = $this->duplicateFieldValueFromList($product_attribute_result, 'attribute_name');
            $attributeName = array_map(array($this, 'addAttributePrefix'), $attributeName);
            $attribute_id_con = $this->arrayToInCondition($attributeId, false);
            $del_attribute_query = "DELETE FROM `" . $db->prefix . self::WOO_ATTR_TAXONOMY . "` WHERE attribute_id IN " . $attribute_id_con;
            $del_attribute_result = $db->query($del_attribute_query);
            if ($del_attribute_result === false) {
                return $this->errorClear('product');
            }
            $attribute_name_con = $this->arrayToInCondition($attributeName, false);
            $wp_term_table = $db->prefix . self::WP_TERM;
            $wp_taxonomy_table = $db->prefix . self::WP_TERM_TAXONOMY;
            $del_term_query = "DELETE " . $wp_taxonomy_table . ", " . $wp_term_table . " FROM " . $wp_taxonomy_table . " LEFT JOIN " . $wp_term_table . " ON " . $wp_term_table . ".term_id = " . $wp_taxonomy_table . ".term_id WHERE " . $wp_taxonomy_table . ".taxonomy IN " . $attribute_name_con;
            $del_term_result = $db->query($del_term_query);
            if ($del_term_result === false) {
                return $this->errorClear('product');
            }
            $this->_notice['clear_info']['function'] = '_clearAttributes';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        } else {
            $this->_notice['clear_info']['function'] = '_clearShipping';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
    }

    protected function _clearShipping()
    {
        $db = $this->getDB();
        $wp_term_table = $db->prefix . self::WP_TERM;
        $wp_taxonomy_table = $db->prefix . self::WP_TERM_TAXONOMY;
        $wp_relationship_table = $db->prefix . self::WP_TERM_RELATION;
        $category_query = "SELECT * FROM `" . $wp_taxonomy_table . "` WHERE taxonomy = 'product_shipping_class' LIMIT " . $this->_notice['clear_info']['limit'];
        $category_result = $db->get_results($category_query, ARRAY_A);
        if ($category_result) {
            $termId = $this->duplicateFieldValueFromList($category_result, 'term_id');
            $term_id_con = $this->arrayToInCondition($termId, false);
            $taxonomyId = $this->duplicateFieldValueFromList($category_result, 'term_taxonomy_id');
            $taxonomy_id_con = $this->arrayToInCondition($taxonomyId, false);
            $del_taxonomy_query = "DELETE FROM `" . $wp_taxonomy_table . "` WHERE term_id IN " . $term_id_con;
            $del_taxonomy_result = $db->query($del_taxonomy_query);
            if ($del_taxonomy_result === false) {
                return $this->errorClear('product');
            }
            $del_term_query = "DELETE FROM `" . $wp_term_table . "` WHERE term_id IN " . $term_id_con;
            $del_term_result = $db->query($del_term_query);
            if ($del_term_result === false) {
                return $this->errorClear('product');
            }
            $del_relationship_query = "DELETE FROM `" . $wp_relationship_table . "` WHERE term_taxonomy_id IN " . $taxonomy_id_con;
            $del_relationship_result = $db->query($del_relationship_query);
            if ($del_relationship_result === false) {
                return $this->errorClear('product');
            }
            $this->_notice['clear_info']['function'] = '_clearShipping';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        } else {
            $this->_notice['clear_info']['function'] = '_clearTags';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
    }

    protected function _clearTags()
    {
        $db = $this->getDB();
        $wp_term_table = $db->prefix . self::WP_TERM;
        $wp_taxonomy_table = $db->prefix . self::WP_TERM_TAXONOMY;
        $wp_relationship_table = $db->prefix . self::WP_TERM_RELATION;
        $category_query = "SELECT * FROM `" . $wp_taxonomy_table . "` WHERE taxonomy = 'product_tag' OR taxonomy = 'post_tag' OR taxonomy = 'post_format' LIMIT " . $this->_notice['clear_info']['limit'];
        $category_result = $db->get_results($category_query, ARRAY_A);
        if ($category_result) {
            $termId = $this->duplicateFieldValueFromList($category_result, 'term_id');
            $term_id_con = $this->arrayToInCondition($termId, false);
            $taxonomyId = $this->duplicateFieldValueFromList($category_result, 'term_taxonomy_id');
            $taxonomy_id_con = $this->arrayToInCondition($taxonomyId, false);
            $del_taxonomy_query = "DELETE FROM `" . $wp_taxonomy_table . "` WHERE term_id IN " . $term_id_con;
            $del_taxonomy_result = $db->query($del_taxonomy_query);
            if ($del_taxonomy_result === false) {
                return $this->errorClear('product');
            }
            $del_term_query = "DELETE FROM `" . $wp_term_table . "` WHERE term_id IN " . $term_id_con;
            $del_term_result = $db->query($del_term_query);
            if ($del_term_result === false) {
                return $this->errorClear('product');
            }
            $del_relationship_query = "DELETE FROM `" . $wp_relationship_table . "` WHERE term_taxonomy_id IN " . $taxonomy_id_con;
            $del_relationship_result = $db->query($del_relationship_query);
            if ($del_relationship_result === false) {
                return $this->errorClear('product');
            }
            $this->_notice['clear_info']['function'] = '_clearTags';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        } else {
            $this->_notice['clear_info']['function'] = '_clearCategories';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
    }

    protected function _clearCategories()
    {
        if (!$this->_notice['config']['import']['categories']) {
            $this->_notice['clear_info']['function'] = '_clearOrders';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
        $db = $this->getDB();
        $wp_term_table = $db->prefix . self::WP_TERM;
        $wp_taxonomy_table = $db->prefix . self::WP_TERM_TAXONOMY;
        $wp_relationship_table = $db->prefix . self::WP_TERM_RELATION;
        $category_query = "SELECT * FROM `" . $wp_taxonomy_table . "` WHERE taxonomy = 'product_cat' OR taxonomy = 'post_cat' LIMIT " . $this->_notice['clear_info']['limit'];
        $category_result = $db->get_results($category_query, ARRAY_A);
        if ($category_result) {
            $termId = $this->duplicateFieldValueFromList($category_result, 'term_id');
            $term_id_con = $this->arrayToInCondition($termId, false);
            $taxonomyId = $this->duplicateFieldValueFromList($category_result, 'term_taxonomy_id');
            $taxonomy_id_con = $this->arrayToInCondition($taxonomyId, false);
            $del_taxonomy_query = "DELETE FROM `" . $wp_taxonomy_table . "` WHERE term_id IN " . $term_id_con;
            $del_taxonomy_result = $db->query($del_taxonomy_query);
            if ($del_taxonomy_result === false) {
                return $this->errorClear('categories');
            }
            $del_term_query = "DELETE FROM `" . $wp_term_table . "` WHERE term_id IN " . $term_id_con;
            $del_term_result = $db->query($del_term_query);
            if ($del_term_result === false) {
                return $this->errorClear('categories');
            }
            $del_relationship_query = "DELETE FROM `" . $wp_relationship_table . "` WHERE term_taxonomy_id IN " . $taxonomy_id_con;
            $del_relationship_result = $db->query($del_relationship_query);
            if ($del_relationship_result === false) {
                return $this->errorClear('product');
            }
            $this->_notice['clear_info']['function'] = '_clearCategories';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        } else {
            $this->_notice['clear_info']['function'] = '_clearCategoryMeta';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
    }

    protected function _clearCategoryMeta()
    {
        $table_exists = $this->isTableExists($this->_woo_term_meta_table);
        if($table_exists){
            $result = $this->emptyTable($this->_woo_term_meta_table);
            if ($result === false) {
                return $this->errorClear('categories');
            } else {
                $this->_notice['clear_info']['function'] = '_clearOrders';
                return array(
                    'result' => 'process',
                    'msg' => ''
                );
            }
        } else {
            $this->_notice['clear_info']['function'] = '_clearOrders';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
    }

    protected function _clearOrders()
    {
        if (!$this->_notice['config']['import']['orders']) {
            $this->_notice['clear_info']['function'] = '_clearCustomers';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
        $order_exists = $this->_checkOrderExists();
        if ($order_exists) {
            $db = $this->getDB();
            $table_post = $db->prefix . self::WP_POSTS;
            $table_post_meta = $db->prefix . self::WP_POST_META;
            $del_order_query = "DELETE FROM `" . $table_post . "` WHERE post_type IN ('shop_order', 'shop_order_refund')";
            $del_order_result = $db->query($del_order_query);
            if (!$del_order_result) {
                return $this->errorClear('orders');
            }
            $del_order_meta_query = "DELETE " . $table_post_meta . " FROM " . $table_post_meta . " LEFT JOIN " . $table_post . " ON " . $table_post . ".ID = " . $table_post_meta . ".post_id WHERE " . $table_post . ".ID IS NULL";
            $del_order_meta_result = $db->query($del_order_meta_query);
            if ($del_order_meta_result === false) {
                return $this->errorClear('orders');
            }
            $table_order_note = self::WP_COMMENT;
            $del_order_note_query = "DELETE FROM ".$table_order_note." WHERE comment_type = 'order_note'";
            $del_order_note_result = $db->query($del_order_note_query);
            $this->_notice['clear_info']['function'] = '_clearOrderItem';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        } else {
            $this->_notice['clear_info']['function'] = '_clearCustomers';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
    }

    protected function _clearOrderItem()
    {
        $del_order_item = $this->emptyTable(self::WOO_ORDER_ITEM);
        $del_order_item_meta = $this->emptyTable(self::WOO_ORDER_META);
        if ($del_order_item === false || $del_order_item_meta === false) {
            return $this->errorClear('orders');
        }
        $this->_notice['clear_info']['function'] = '_clearCustomers';
        return array(
            'result' => 'process',
            'msg' => ''
        );
    }

    protected function _clearCustomers()
    {
        if (!$this->_notice['config']['import']['customers']) {
            $this->_notice['clear_info']['function'] = '_clearTaxes';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
        $db = $this->getDB();
        $table_user = $db->base_prefix . self::WP_USERS;
        $table_user_meta = $db->base_prefix . self::WP_USER_META;
        $meta_key_capabilities = $db->base_prefix . 'capabilities';
        $del_user_query = "DELETE " . $table_user . " , " . $table_user_meta . " FROM " . $table_user . " LEFT JOIN " . $table_user_meta . " ON " . $table_user . ".ID = " . $table_user_meta . ".user_id WHERE " . $table_user_meta . ".meta_key IN ('wp_capabilities', '{$meta_key_capabilities}') AND " . $table_user_meta . ".meta_value = '" . esc_sql('a:1:{s:8:"customer";b:1;}') . "'";
        $del_user_result = $db->query($del_user_query);
        if ($del_user_result === false) {
            return $this->errorClear('customers');
        }
        $del_user_meta_query = "DELETE " . $table_user_meta . " FROM " . $table_user_meta . " LEFT JOIN " . $table_user . " ON " . $table_user_meta . ".user_id = " . $table_user . ".ID WHERE " . $table_user . ".ID IS NULL";
        $del_user_meta_result = $db->query($del_user_meta_query);
        if ($del_user_meta_result === false) {
            return $this->errorClear('customers');
        }
        $this->_notice['clear_info']['function'] = '_clearTaxes';
        return array(
            'result' => 'process',
            'msg' => ''
        );
    }

    protected function _clearTaxes()
    {
        if (!$this->_notice['config']['import']['taxes']) {
            $this->_notice['clear_info']['function'] = '_clearLinkRewrite';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
        update_option(LeCaMg::WOO_TAX_SETTING, '');
        $del_tax_rate = $this->emptyTable(self::WOO_TAX_RATE);
        $del_tax_rate_location = $this->emptyTable(self::WOO_TAX_RATE_LOCATION);
        if ($del_tax_rate === false || $del_tax_rate_location === false) {
            return $this->errorClear('taxes');
        }
        $this->_notice['clear_info']['function'] = '_clearLinkRewrite';
        return array(
            'result' => 'process',
            'msg' => ''
        );
    }

    protected function _clearLinkRewrite()
    {
        if (!$this->_notice['config']['add_option']['seo'] || !defined('LEUR_TABLE')) {
            $this->_notice['clear_info']['function'] = '_clearManufacturers';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
        $db = $this->getDB();
        $del_seo = $this->emptyTable(LEUR_TABLE);
        if ($del_seo === false) {
            return $this->errorClear('seo');
        }
        $this->_notice['clear_info']['function'] = '_clearManufacturers';
        return array(
            'result' => 'process',
            'msg' => ''
        );
    }

    protected function _clearManufacturers()
    {
        if (!$this->_notice['config']['import']['manufacturers']) {
            $this->_notice['clear_info']['function'] = '_clearReviews';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
        $this->_notice['clear_info']['function'] = '_clearReviews';
        return array(
            'result' => 'process',
            'msg' => ''
        );
    }

    protected function _clearReviews()
    {
        if (!$this->_notice['config']['import']['reviews']) {
            $this->_notice['clear_info']['function'] = '_clearPages';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
        $db = $this->getDB();
        $table_review = $db->prefix. self::WP_COMMENT;
        $table_review_meta = $db->prefix. self::WP_COMMENT_META;
        $del_review_query = "DELETE " . $table_review . ", " . $table_review_meta . " FROM " . $table_review . " LEFT JOIN " . $table_review_meta . " ON " . $table_review . ".comment_ID = " . $table_review_meta . ".comment_id WHERE " . $table_review_meta . ".meta_key = 'rating'";
        $del_review_result = $db->query($del_review_query);
        if ($del_review_result === false) {
            return $this->errorClear('reviews');
        }
        $del_review_meta_query = "DELETE " . $table_review_meta . " FROM " . $table_review_meta . " LEFT JOIN " . $table_review . " ON " . $table_review_meta . ".comment_id = " . $table_review . ".comment_ID WHERE " . $table_review . ".comment_ID IS NULL";
        $del_review_meta_result = $db->query($del_review_meta_query);
        if ($del_review_meta_result === false) {
            return $this->errorClear('reviews');
        }
        $this->_notice['clear_info']['function'] = '_clearPages';
        return array(
            'result' => 'process',
            'msg' => ''
        );
    }

    protected function _clearPages(){
        if (!$this->_notice['config']['import']['pages']) {
            $this->_notice['clear_info']['function'] = '_clearPosts';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
        $page_exists = $this->_checkPageExists();
        if ($page_exists) {
            $db = $this->getDB();
            $table_post = $db->prefix . self::WP_POSTS;
            $table_post_meta = $db->prefix . self::WP_POST_META;
            $del_product_query = "DELETE FROM `" . $table_post . "` WHERE post_type = 'page' LIMIT " . $this->_notice['clear_info']['limit'];
            $del_product_result = $db->query($del_product_query);
            if (!$del_product_result) {
                return $this->errorClear('page');
            }
            $del_product_meta_query = "DELETE " . $table_post_meta . " FROM " . $table_post_meta . " LEFT JOIN " . $table_post . " ON " . $table_post . ".ID = " . $table_post_meta . ".post_id WHERE " . $table_post . ".ID IS NULL";
            $del_product_meta_result = $db->query($del_product_meta_query);
            if ($del_product_meta_result === false) {
                return $this->errorClear('page');
            }
        }
        $this->_notice['clear_info']['function'] = '_clearPosts';
        return array(
            'result' => 'process',
            'msg' => ''
        );
    }

    protected function _clearPosts(){
        if (!$this->_notice['config']['import']['posts']) {
            $this->_notice['clear_info']['function'] = '_clearPostCat';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
        $post_exists = $this->_checkPostExists();
        if ($post_exists) {
            $db = $this->getDB();
            $table_post = $db->prefix . self::WP_POSTS;
            $table_post_meta = $db->prefix . self::WP_POST_META;
            $del_product_query = "DELETE FROM `" . $table_post . "` WHERE post_type = 'post' LIMIT " . $this->_notice['clear_info']['limit'];
            $del_product_result = $db->query($del_product_query);
            if (!$del_product_result) {
                return $this->errorClear('post');
            }
            $del_product_meta_query = "DELETE " . $table_post_meta . " FROM " . $table_post_meta . " LEFT JOIN " . $table_post . " ON " . $table_post . ".ID = " . $table_post_meta . ".post_id WHERE " . $table_post . ".ID IS NULL";
            $del_product_meta_result = $db->query($del_product_meta_query);
            if ($del_product_meta_result === false) {
                return $this->errorClear('post');
            }
            if (!$this->_notice['config']['import']['comments']) {
                $table_comment = $db->prefix . self::WP_COMMENT;
                $table_comment_meta = $db->prefix . self::WP_COMMENT_META;
                $del_product_comment_query = "DELETE " . $table_comment . " FROM " . $table_comment . " LEFT JOIN " . $table_post . " ON " . $table_post . ".ID = " . $table_comment . ".comment_post_ID WHERE " . $table_post . ".ID IS NULL";
                $del_product_comment_result = $db->query($del_product_comment_query);
                if ($del_product_comment_result === false) {
                    return $this->errorClear('post');
                }
                $del_comment_meta_query = "DELETE " . $table_comment_meta . " FROM " . $table_comment_meta . " LEFT JOIN " . $table_comment . " ON " . $table_comment . ".comment_ID = " . $table_comment_meta . ".comment_id WHERE " . $table_comment . ".comment_ID IS NULL";
                $del_comment_meta_result = $db->query($del_comment_meta_query);
                if ($del_comment_meta_result === false) {
                    return $this->errorClear('post');
                }
            }
            $this->_notice['clear_info']['function'] = '_clearPosts';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
        $this->_notice['clear_info']['function'] = '_clearPostCat';
        return array(
            'result' => 'process',
            'msg' => ''
        );
    }

    protected function _clearPostCat()
    {
        if (!$this->_notice['config']['import']['postCat']) {
            $this->_notice['clear_info']['function'] = '_clearComments';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
        $db = $this->getDB();
        $wp_term_table = $db->prefix . self::WP_TERM;
        $wp_taxonomy_table = $db->prefix . self::WP_TERM_TAXONOMY;
        $wp_relationship_table = $db->prefix . self::WP_TERM_RELATION;
        $category_query = "SELECT * FROM `" . $wp_taxonomy_table . "` WHERE taxonomy = 'category' LIMIT " . $this->_notice['clear_info']['limit'];
        $category_result = $db->get_results($category_query, ARRAY_A);
        if ($category_result) {
            $termId = $this->duplicateFieldValueFromList($category_result, 'term_id');
            $term_id_con = $this->arrayToInCondition($termId, false);
            $taxonomyId = $this->duplicateFieldValueFromList($category_result, 'term_taxonomy_id');
            $taxonomy_id_con = $this->arrayToInCondition($taxonomyId, false);
            $del_taxonomy_query = "DELETE FROM `" . $wp_taxonomy_table . "` WHERE term_id IN " . $term_id_con;
            $del_taxonomy_result = $db->query($del_taxonomy_query);
            if ($del_taxonomy_result === false) {
                return $this->errorClear('categories');
            }
            $del_term_query = "DELETE FROM `" . $wp_term_table . "` WHERE term_id IN " . $term_id_con;
            $del_term_result = $db->query($del_term_query);
            if ($del_term_result === false) {
                return $this->errorClear('categories');
            }
            $del_relationship_query = "DELETE FROM `" . $wp_relationship_table . "` WHERE term_taxonomy_id IN " . $taxonomy_id_con;
            $del_relationship_result = $db->query($del_relationship_query);
            if ($del_relationship_result === false) {
                return $this->errorClear('categories');
            }
            $this->_notice['clear_info']['function'] = '_clearComments';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        } else {
            $this->_notice['clear_info']['function'] = '_clearComments';
            return array(
                'result' => 'process',
                'msg' => ''
            );
        }
    }

    protected function _clearComments(){
        if (!$this->_notice['config']['import']['comments']) {
            $this->_notice['clear_info']['function'] = 'finish';
            return array(
                'result' => 'success',
                'msg' => ''
            );
        }
        $db = $this->getDB();
        $table_comment = $db->prefix. self::WP_COMMENT;
        $table_comment_meta = $db->prefix. self::WP_COMMENT_META;
        $del_comment_query = "DELETE " . $table_comment . ", " . $table_comment_meta . " FROM " . $table_comment . " LEFT JOIN " . $table_comment_meta . " ON " . $table_comment . ".comment_ID <> " . $table_comment_meta . ".comment_id WHERE " . $table_comment_meta . ".meta_key <> 'rating' AND ".$table_comment.".comment_type <> 'order_note'";
        $del_comment_result = $db->query($del_comment_query);
        if ($del_comment_result === false) {
            return $this->errorClear('comments');
        }
        $this->_notice['clear_info']['function'] = 'finish';
        return array(
            'result' => 'success',
            'msg' => ''
        );
    }

    protected function _checkProductExists()
    {
        $db = $this->getDB();
        $query = "SELECT ID FROM `" . $db->prefix . self::WP_POSTS . "` WHERE post_type IN ( 'product', 'product_variation') LIMIT 1";
        $result = $db->get_results($query);
        return $result ? true : false;
    }

    protected function _checkPostExists()
    {
        $db = $this->getDB();
        $query = "SELECT ID FROM `" . $db->prefix . self::WP_POSTS . "` WHERE post_type = 'post' LIMIT 1";
        $result = $db->get_results($query);
        return $result ? true : false;
    }

    protected function _checkPageExists()
    {
        $db = $this->getDB();
        $query = "SELECT ID FROM `" . $db->prefix . self::WP_POSTS . "` WHERE post_type = 'page' LIMIT 1";
        $result = $db->get_results($query);
        return $result ? true : false;
    }

    public function addAttributePrefix($name)
    {
        if (!$name) {
            return "";
        }
        $name = "pa_" . $name;
        return $name;
    }

    protected function _checkOrderExists()
    {
        $db = $this->getDB();
        $query = "SELECT ID FROM `" . $db->prefix . self::WP_POSTS . "` WHERE post_type IN ( 'shop_order', 'shop_order_refund') LIMIT 1";
        $result = $db->get_results($query);
        return $result ? true : false;
    }

    /**
     * TODO: Module Database
     */

    public function insertImport($type, $id_src, $id_desc = null, $status = null, $value = null)
    {
        $data = array(
            'domain' => $this->_cart_url,
            'type' => $type,
            'id_src' => $id_src,
            'id_desc' => $id_desc,
            'status' => $status,
            'value' => $value
        );
        return $this->insertTable(LeCaMg::TABLE_IMPORT, $data);
    }

    public function insertUpdate($id_src, $id_desc = null, $value = null)
    {
        $data = array(
            'domain' => $this->_cart_url,
            'id_src' => $id_src,
            'id_desc' => $id_desc,
            'value' => $value,
        );
        return $this->insertTable(LeCaMg::TABLE_UPDATE, $data);
    }

    public function deleteImport()
    {
        return $this->deleteTable(LeCaMg::TABLE_IMPORT, array(
            'domain' => $this->_cart_url
        ));
    }

    public function getIdDescImport($type, $id_src = null, $value = null)
    {
        $where = array(
            'domain' => $this->_cart_url,
            'type' => $type
        );
        if ($id_src !== null) {
            $where['id_src'] = $id_src;
        }
        if ($value !== null) {
            $where['value'] = $value;
        }
        if ($id_src === null && $value === null) return false;
        $result = $this->selectTableRow(LeCaMg::TABLE_IMPORT, $where);
        if ($result) {
            return $result['id_desc'];
        }
        return false;
    }

    public function getValueImport($type, $id_src = null, $id_desc = null, $value = null)
    {
        $where = array(
            'domain' => $this->_cart_url,
            'type' => $type
        );
        if ($id_src !== null) {
            $where['id_src'] = $id_src;
        }
        if ($id_desc !== null) {
            $where['id_desc'] = $id_desc;
        }
        if ($value !== null) {
            $where['value'] = $value;
        }
        if ($id_src === null && $id_desc === null && $value === null) return false;
        $result = $this->selectTableRow(LeCaMg::TABLE_IMPORT, $where);
        if ($result) {
            return $result['value'];
        }
        return false;
    }

    public function getIdDescTax($id_src)
    {
        return $this->getIdDescImport(self::TYPE_TAX, $id_src);
    }

    public function getValueTax($id_src)
    {
        return $this->getValueImport(self::TYPE_TAX, $id_src);
    }

    public function taxSuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_TAX, $id_src, $id_desc, 1, $value);
    }

    public function taxError($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_TAX, $id_src, $id_desc, 0, $value);
    }

    public function getIdDescTaxRate($id_src)
    {
        return $this->getIdDescImport(self::TYPE_TAX_RATE, $id_src);
    }

    public function getValueTaxRate($id_src)
    {
        return $this->getValueImport(self::TYPE_TAX_RATE, $id_src);
    }

    public function taxRateSuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_TAX_RATE, $id_src, $id_desc, 1, $value);
    }

    public function taxRateError($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_TAX_RATE, $id_src, $id_desc, 0, $value);
    }

    public function getIdDescManufacturer($id_src)
    {
        return $this->getIdDescImport(self::TYPE_MANUFACTURER, $id_src);
    }

    public function getValueManufacturer($id_src)
    {
        return $this->getValueImport(self::TYPE_MANUFACTURER, $id_src);
    }

    public function manufacturerSuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_MANUFACTURER, $id_src, $id_desc, 1, $value);
    }

    public function manufacturerError($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_MANUFACTURER, $id_src, $id_desc, 0, $value);
    }

    public function getIdDescCategory($id_src)
    {
        return $this->getIdDescImport(self::TYPE_CATEGORY, $id_src);
    }

    public function getValueCategory($id_src)
    {
        return $this->getValueImport(self::TYPE_CATEGORY, $id_src);
    }

    public function categorySuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_CATEGORY, $id_src, $id_desc, 1, $value);
    }

    public function categoryError($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_CATEGORY, $id_src, $id_desc, 0, $value);
    }

    public function getIdDescProduct($id_src)
    {
        return $this->getIdDescImport(self::TYPE_PRODUCT, $id_src);
    }

    public function getValueProduct($id_src)
    {
        return $this->getValueImport(self::TYPE_PRODUCT, $id_src);
    }

    public function productSuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_PRODUCT, $id_src, $id_desc, 1, $value);
    }

    public function productError($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_PRODUCT, $id_src, $id_desc, 0, $value);
    }

    public function getIdDescAttribute($id_src)
    {
        return $this->getIdDescImport(self::TYPE_ATTR, $id_src);
    }

    public function getValueAttribute($id_src)
    {
        return $this->getValueImport(self::TYPE_ATTR, $id_src);
    }

    public function getValueAttributeByValue($value)
    {
        return $this->getValueImport(self::TYPE_ATTR, null, null, $value);
    }

    public function attributeSuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_ATTR, $id_src, $id_desc, 1, $value);
    }

    public function attributeError($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_ATTR, $id_src, $id_desc, 0, $value);
    }

    public function getIdDescAttributeValue($id_src)
    {
        return $this->getIdDescImport(self::TYPE_ATTR_VALUE, $id_src);
    }

    public function getIdDescAttributeValueByValue($value)
    {
        return $this->getIdDescImport(self::TYPE_ATTR_VALUE, null, $value);
    }

    public function getValueAttributeValue($id_src)
    {
        return $this->getValueImport(self::TYPE_ATTR_VALUE, $id_src);
    }

    public function attributeValueSuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_ATTR_VALUE, $id_src, $id_desc, 1, $value);
    }

    public function attributeValueError($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_ATTR_VALUE, $id_src, $id_desc, 0, $value);
    }

    public function getIdDescTag($id_src)
    {
        return $this->getIdDescImport(self::TYPE_TAG, $id_src);
    }

    public function getIdDescTagByValue($value)
    {
        return $this->getIdDescImport(self::TYPE_TAG, null, $value);
    }

    public function getValueTag($id_src)
    {
        return $this->getValueImport(self::TYPE_TAG, $id_src);
    }

    public function tagSuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_TAG, $id_src, $id_desc, 1, $value);
    }

    public function tagError($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_TAG, $id_src, $id_desc, 0, $value);
    }

    public function getIdDescCustomer($id_src)
    {
        return $this->getIdDescImport(self::TYPE_CUSTOMER, $id_src);
    }

    public function getValueCustomer($id_src)
    {
        return $this->getValueImport(self::TYPE_CUSTOMER, $id_src);
    }

    public function customerSuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_CUSTOMER, $id_src, $id_desc, 1, $value);
    }

    public function customerError($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_CUSTOMER, $id_src, $id_desc, 0, $value);
    }

    public function getIdDescOrder($id_src)
    {
        return $this->getIdDescImport(self::TYPE_ORDER, $id_src);
    }

    public function getValueOrder($id_src)
    {
        return $this->getValueImport(self::TYPE_ORDER, $id_src);
    }

    public function orderSuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_ORDER, $id_src, $id_desc, 1, $value);
    }

    public function orderError($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_ORDER, $id_src, $id_desc, 0, $value);
    }

    public function getIdDescReview($id_src)
    {
        return $this->getIdDescImport(self::TYPE_REVIEW, $id_src);
    }

    public function getValueReview($id_src)
    {
        return $this->getValueImport(self::TYPE_REVIEW, $id_src);
    }

    public function reviewSuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_REVIEW, $id_src, $id_desc, 1, $value);
    }

    public function reviewError($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_REVIEW, $id_src, $id_desc, 0, $value);
    }

    public function getIdDescPage($id_src)
    {
        return $this->getIdDescImport(self::TYPE_PAGE, $id_src);
    }

    public function getValuePage($id_src)
    {
        return $this->getValueImport(self::TYPE_PAGE, $id_src);
    }

    public function pageSuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_PAGE, $id_src, $id_desc, 1, $value);
    }

    public function pageError($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_PAGE, $id_src, $id_desc, 0, $value);
    }

    public function getIdDescPost($id_src)
    {
        return $this->getIdDescImport(self::TYPE_POST, $id_src);
    }

    public function getValuePost($id_src)
    {
        return $this->getValueImport(self::TYPE_POST, $id_src);
    }

    public function postSuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_POST, $id_src, $id_desc, 1, $value);
    }

    public function postError($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_POST, $id_src, $id_desc, 0, $value);
    }

    public function getIdDescComment($id_src){
        return $this->getIdDescImport(self::TYPE_COMMENT, $id_src);
    }

    public function getValueComment($id_src)
    {
        return $this->getValueImport(self::TYPE_COMMENT, $id_src);
    }

    public function commentSuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_COMMENT, $id_src, $id_desc, 1, $value);
    }

    public function commentError($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_COMMENT, $id_src, $id_desc, 0, $value);
    }

    public function getIdDescFormat($id_src)
    {
        return $this->getIdDescImport(self::TYPE_FORMAT, $id_src);
    }

    public function getValueFormat($id_src)
    {
        return $this->getValueImport(self::TYPE_FORMAT, $id_src);
    }

    public function formatSuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_FORMAT, $id_src, $id_desc, 1, $value);
    }

    public function formatError($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_FORMAT, $id_src, $id_desc, 0, $value);
    }

    /**
     * TODO: Connector
     */

    public function getUrlConnector($action)
    {
        $url = rtrim($this->_cart_url, '/') . '/' . self::CONNECTOR_SUFFIX . '?action=' . $action . '&token=' . $this->_cart_token;
        return $url;
    }

    public function getUrlSuffix($suffix)
    {
        $url = rtrim($this->_cart_url, '/') . '/' . ltrim($suffix, '/');
        return $url;
    }

    protected function _addTablePrefix($data)
    {
        if (isset($data['query'])) {
            if ($this->_notice['setting']['prefix']) {
                $prefix = $this->_notice['setting']['prefix'];
            } else {
                $prefix = $this->_notice['config']['table_prefix'];
            }
            if (isset($data['serialize'])) {
                $queries = unserialize($data['query']);
                $add = array();
                foreach ($queries as $table => $query) {
                    $change = str_replace('_DBPRF_', $prefix, $query);
                    $add[$table] = $change;
                }
                $data['query'] = serialize($add);
            } else {
                $query = $data['query'];
                $data['query'] = str_replace('_DBPRF_', $prefix, $query);
            }
        }
        return $data;
    }

    protected function _insertParamCharSet($data)
    {
        $charset = array('utf8', 'cp1251');
        if (in_array($this->_notice['config']['charset'], $charset)) {
            $data['char_set'] = 'utf8';
        }
        return $data;
    }

    public function getConnectorData($url, $data = array(), $check_url = false)
    {
        if ($check_url && !$this->urlExists($url)) {
            return false;
        }
        if ($data) {
            $data = $this->_insertParamCharSet($data);
            $data = $this->_addTablePrefix($data);
        }
        $response = $this->request($url, $data);
        if (!$response) {
            return false;
        }
        if ($this->_notice['setting']['delay']) {
            @sleep($this->_notice['setting']['delay']);
        }
        return unserialize(base64_decode($response));
    }

    public function request($url, $data = array(), $method = self::POST_METHOD, $base64 = true)
    {
        if ($data && $base64) {
            foreach ($data as $key => $value) {
                $data[$key] = base64_encode($value);
            }
        }
        switch ($method) {
            case self::GET_METHOD :
                return $this->requestByGet($url, $data);
            case self::POST_METHOD :
                return $this->requestByPost($url, $data);
            default :
                return $this->requestByPost($url, $data);
        }
        return false;
    }

    public function requestByPost($url, $data = array())
    {
        $options = http_build_query($data);
        $ch = curl_init($url);
        if($this->_notice['curl']['useragent']){
        	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $options);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt_array($ch, array(CURLINFO_HEADER_OUT => true));
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function requestByGet($url, $data = array())
    {
        $options = http_build_query($data);
        if ($options) {
            $url .= "?" . $options;
        }
        $ch = curl_init($url);
        if($this->_notice['curl']['useragent']){
        	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt_array($ch, array(CURLINFO_HEADER_OUT => true));
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function urlExists($url)
    {
        return true;
    }

    public function syncConnectorObject($data, $extra)
    {
        if ($data['object'] && $extra['object']) {
            foreach ($extra['object'] as $key => $rows) {
                if (!isset($data['object'][$key])) {
                    $data['object'][$key] = $rows;
                }
            }
        }
        return $data;
    }

    protected function _checkCartTypeSync($type_src, $type_select)
    {
        $pos = strpos($type_select, $type_src);
        if ($pos === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * TODO: Image
     */

    public function uploadDir($suffix = null)
    {
        if ($suffix) {
            $upload = wp_upload_dir($suffix);
            return $upload['path'];
        }
        if (!$this->_upload_dir) {
            $upload = wp_upload_dir();
            $this->_upload_dir = $upload['basedir'];
        }
        return $this->_upload_dir;
    }

    public function uploadUrl($suffix = null)
    {
        if ($suffix) {
            $upload = wp_upload_dir($suffix);
            return $upload['url'];
        }
        if (!$this->_upload_url) {
            $upload = wp_upload_dir();
            $this->_upload_url = $upload['baseurl'];
        }
        return $this->_upload_url;
    }

    public function downloadImage($url, $path, $location = self::IMG_DIR, $base_name = false, $return_path = false, $insert_extension = false)
    {
        try {
			$path = ltrim($path, '/');
            $src_img = rtrim($url, '/') . '/';
            if ($this->_isUrlEncode($path)) {
                $src_img .= $path;
            } else {
                $src_img .= ltrim($this->_getUrlRealPath($path), '/');
            }
            if (!$this->imageExists($src_img)) {
                return false;
            }
            $desc_location = $this->uploadDir() . $location . '/';
            if ($base_name) {
                $path_save = $this->_createPathToSave(basename($path));
            } else {
                $path_save = $this->_createPathToSave($path);
            }
            if ($insert_extension) {
                $extension = '';
                $src_img .= '?' . $insert_extension;
                $path_save .= $this->_createPathToSave($insert_extension);
                $header = get_headers($src_img, 1);
                if ($header) {
                    $content_type = $header['Content-Type'];
                    $extension = $this->_getImageTypeByContentType($content_type);
                }
                $path_save .= $extension;
            }
            $img_desc = $desc_location . $path_save;
            if (file_exists($img_desc)) {
                $actual_dirname = pathinfo($path_save, PATHINFO_DIRNAME);
                $actual_name = pathinfo($path_save, PATHINFO_FILENAME);
                $original_name = $actual_name;
                $extension = pathinfo($path_save, PATHINFO_EXTENSION);

                $i = 1;
                while (file_exists($desc_location . $path_save)) {
                    $actual_name = (string)$original_name . $i;
                    $path_save = $actual_name . "." . $extension;
                    if ($actual_dirname != '.') {
                        $path_save = $actual_dirname . '/' . $path_save;
                    }
                    $i++;
                }
                $img_desc = $desc_location . $path_save;
            }
            if (!is_dir(dirname($img_desc))) {
                @mkdir(dirname($img_desc), 0777, true);
            }
            $file_path = false;
            if (($path != '')) {
                $fp = fopen($img_desc, 'w');
                $ch = curl_init($src_img);
                if($this->_notice['curl']['useragent']){
                	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
                }
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_TIMEOUT, 20); //10s
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $data = curl_exec($ch);
                curl_close($ch);
                fclose($fp);
            }

            if (file_exists($img_desc)) {
                if ($return_path) {
                    $file_path = $path_save;
                } else {
                    $file_path = $this->uploadUrl() . $location . '/' . ltrim($path_save, '/');
                }
            }
            return $file_path;
        } catch (Exception $e) {
            return false;
        }
    }

    public function imageExists($url)
    {
        $header = @get_headers($url, 1);
        if (!$header) {
            return false;
        }
        $string = $header[0];
        if (strpos($string, "404") || strpos($string, "301")) {
            return false;
        }
        return true;
    }

    protected function _createPathToSave($path)
    {
        $splits = explode('/', $path);
        $data = array();
        foreach ($splits as $key => $split) {
            $split = preg_replace('/[^A-Za-z0-9._\-]/', '-', $split);
            $data[$key] = $split;
        }
        $path = implode('/', $data);
        return $path;
    }

    protected function _getUrlRealPath($path)
    {
        $splits = explode('/', $path);
        $data = array();
        foreach ($splits as $key => $split) {
            $data[$key] = rawurlencode($split);
        }
        $path = implode('/', $data);
        return $path;
    }

    protected function _isUrlEncode($path)
    {
        $is_encoded = @preg_match('~%[0-9A-F]{2}~i', $path);
        return $is_encoded;
    }

    protected function _getImageTypeByContentType($content_type)
    {
        $result = '';
        $mineType = array(
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif',
            'image/pjpeg' => '.jpeg',
            'image/x-icon' => '.ico',
        );
        if ($mineType[$content_type]) {
            $result = $mineType[$content_type];
        }
        return $result;
    }

    protected function _changeImgSrcInText($html)
    {
        if (!$this->_notice['config']['add_option']['img_des']) {
            return $html;
        }
        $links = array();
        preg_match_all('/<img[^>]+>/i', $html, $img_tags);
        foreach ($img_tags[0] as $img) {
            preg_match('/(src(.*?)=(.*?)["\'](.*?)["\'])/', $img, $src);
            if (!isset($src[0])) {
                continue;
            }
            $split = preg_split('/["\']/', $src[0]);
            $links[] = $split[1];
        }
        $links = $this->_filterArrayValueDuplicate($links);
        foreach ($links as $link) {
            if ($new_link = $this->_getImgDesUrlImport($link)) {
                $html = str_replace($link, $new_link, $html);
            }
        }
        return $html;
    }

    protected function _getImgDesUrlImport($url)
    {
        $result = false;
        $insert_extension = false;
        $url = parse_url($url);
        if (isset($url['host'])) {
            $host = $url['scheme'] . '://' . $url['host'];
            $path = substr($url['path'], 1);
            if (isset($url['query'])) {
                $insert_extension = $url['query'];
            }
        } else {
            $host = $this->_cart_url;
            $path = $url['path'];
        }
        if ($path_import = $this->downloadImage($host, $path, self::IMG_DIR, false, false, $insert_extension)) {
            $result = $path_import;
        }
        return $result;
    }

    /**
     * TODO: Extends
     */

    public function getListFromListByField($list, $field, $value)
    {
        $result = array();
        if (!$list || !is_array($list)) {
            return $result;
        }
        foreach ($list as $row) {
            if ($row[$field] == $value) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public function getListFromListByListField($list, $field, $values)
    {
        $result = array();
        if (!$list || !is_array($list)) {
            return $result;
        }
        if (!is_array($values)) {
            $values = array($values);
        }
        foreach ($list as $row) {
            if (in_array($row[$field], $values)) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public function getRowFromListByField($list, $field, $value)
    {
        $result = false;
        if (!$list || !is_array($list)) {
            return $result;
        }
        foreach ($list as $row) {
            if ($row[$field] == $value) {
                $result = $row;
                break;
            }
        }
        return $result;
    }

    public function getRowValueFromListByField($list, $field, $value, $need)
    {
        $result = $this->getRowFromListByField($list, $field, $value);
        if (!$result) {
            return false;
        }
        return $result[$need];
    }

    public function duplicateFieldValueFromList($list, $field)
    {
        $result = array();
        if (!$list || !is_array($list)) {
            return $result;
        }
        foreach ($list as $item) {
            if ($item[$field]) {
                $result[] = $item[$field];
            }
        }
        $result = array_unique($result);
        return $result;
    }

    public function getValueFromArray($data, $keys = array())
    {
        if (!is_array($keys)) {
            return false;
        }
        $result = array();
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $result[$key] = $data[$key];
            }
        }
        return $result;
    }

    protected function _filterArrayValueDuplicate($array)
    {
        $result = array();
        if ($array && !empty($array)) {
            $array_values = array_values($array);
            foreach ($array_values as $key => $value) {
                foreach ($array_values as $key_filter => $value_filter) {
                    if ($key_filter < $key) {
                        if ($value == $value_filter) {
                            unset($array_values[$key]);
                        }
                    }
                }
            }
            $result = array_values($array_values);
        }
        return $result;
    }

    public function arrayToCount($array, $name = false)
    {
        if (empty($array)) {
            return 0;
        }
        $count = 0;
        if ($name) {
            $count = isset($array[0][$name]) ? $array[0][$name] : 0;
        } else {
            $count = isset($array[0][0]) ? $array[0][0] : 0;
        }
        return $count;
    }

    public function getMsgStartImport($type)
    {
        $result = '';
        if (!$type) {
            $result .= $this->consoleSuccess("Finished migration!");
            return $result;
        }
        $types = array('taxes', 'manufacturers', 'categories', 'products', 'customers', 'orders', 'reviews', 'pages', 'postCat', 'posts', 'comments');
        $type_key = array_search($type, $types);
        foreach ($types as $key => $value) {
            if ($type_key <= $key && $this->_notice['config']['import'][$value]) {
                $result .= $this->consoleSuccess('Importing ' . $value . ' ... ');
                break;
            }
        }
        return $result;
    }

    public function consoleError($msg)
    {
        $result = '<p class="error"> - ' . $msg . '</p>';
        return $result;
    }

    public function consoleSuccess($msg)
    {
        $result = '<p class="success"> - ' . $msg . '</p>';
        return $result;
    }

    public function consoleWarning($msg)
    {
        $result = '<p class="warning"> - ' . $msg . '</p>';
        return $result;
    }

    public function errorDatabase($console = true)
    {
        $msg = "Could not save information to WordPress database!";
        if ($console) {
            $msg = $this->consoleError($msg);
        }
        return array(
            'result' => 'error',
            'msg' => $msg
        );
    }

    public function errorConnector($console = true)
    {
        $msg = "Could not connect to Connector!";
        if ($console) {
            $msg = $this->consoleError($msg);
        }
        return array(
            'result' => 'error',
            'msg' => $msg,
        );
    }

    public function errorClear($type)
    {
        $msg = "Could not clear " . $type . "!";
        $msg = $this->consoleError($msg);
        return array(
            'result' => "error",
            'msg' => $msg
        );
    }

    public function createTimeToShow($time)
    {
        $hour = gmdate('H', $time);
        $minute = gmdate('i', $time);
        $second = gmdate('s', $time);
        $result = '';
        if ($hour && $hour > 0) {
            $result .= $hour . ' hours ';
        }
        if ($minute && $minute > 0) {
            $result .= $minute . ' minutes ';
        }
        if ($second && $second > 0) {
            $result .= $second . ' seconds ';
        }
        return $result;
    }

    protected function _defaultResponse()
    {
        return array(
            'result' => '',
            'msg' => '',
            'elm' => '',
            'data' => array()
        );
    }

    public function getNameFromString($name)
    {
        $result = array();
        $parts = explode(' ', $name);
        $result['lastname'] = array_pop($parts);
        $result['firstname'] = implode(" ", $parts);
        return $result;
    }

    public function combinationArray($arrays = array())
    {
        $result = array();
        $arrays = array_values($arrays);
        $sizeIn = sizeof($arrays);
        $size = $sizeIn > 0 ? 1 : 0;
        foreach ($arrays as $array)
            $size = $size * sizeof($array);
        for ($i = 0; $i < $size; $i++) {
            $result[$i] = array();
            for ($j = 0; $j < $sizeIn; $j++)
                array_push($result[$i], current($arrays[$j]));
            for ($j = ($sizeIn - 1); $j >= 0; $j--) {
                if (next($arrays[$j])) {
                    break;
                } elseif (isset ($arrays[$j])) {
                    reset($arrays[$j]);
                }
            }
        }
        return $result;
    }

    public function _cookSpecialDate($datetime)
    {
        if (!$datetime || $datetime == '0000-00-00' || $datetime == '0000-00-00 00:00:00' || $datetime == '0001-01-01 00:00:00' || $datetime == '0001-01-01') {
            return '';
        }
        $date = strtotime($datetime);
        return $date;
    }

    /**
     * Chien sieu nhan
     */
    protected function _datetimeToDate($datetime = false){
        if(!$datetime || $datetime == '0000-00-00' || $datetime == '0000-00-00 00:00:00'){
            return date('Y-m-d H:i:s');
        }
        if($datetime == date('Y-m-d H:i:s',strtotime($datetime))) {
            return $datetime;
        }else{
            $date = date('Y-m-d H:i:s', strtotime($datetime));
            return $date;
        }
    }

    public function getIdDescShipping($id_src)
    {
        return $this->getIdDescImport(self::TYPE_SHIPPING, $id_src);
    }

    public function shippingSuccess($id_src, $id_desc = null, $value = null)
    {
        return $this->insertImport(self::TYPE_SHIPPING, $id_src, $id_desc, 1, $value);
    }

    public function selectQuery($query)
    {
        try {
            $db = $this->getDB();
            $result = $db->get_results($query, ARRAY_A);
            return array(
                'result' => "success",
                'data' => $result
            );
        } catch (Exception $e) {
            return array(
                'result' => 'error'
            );
        }
    }

    public function readQuery($query)
    {
        try {
            $db = $this->getDB();
            $result = $db->get_var($query);
            return array(
                'result' => "success",
                'data' => $result
            );
        } catch (Exception $e) {
            return array(
                'result' => 'error',
                //'msg' => $e->getMessage()
            );
        }
    }

    protected function _importCategoryParent($parent_id)
    {
        $categories = $this->getConnectorData($this->getUrlConnector('query'), array(
            'query' => "SELECT * FROM _DBPRF_term_taxonomy as tx
                          LEFT JOIN _DBPRF_terms AS t ON t.term_id = tx.term_id
                          WHERE (tx.taxonomy = 'product_cat') OR (tx.taxonomy = 'category') AND tx.term_id = {$parent_id}"
        ));
        if (!$categories || $categories['result'] != 'success') {
            return $this->errorConnector();
        }
        $categoriesExt = $this->getCategoriesExtra($categories);
        if ($categoriesExt['result'] != 'success') {
            return $categoriesExt;
        }
        $category = $categories['object'][0];
        $convert = $this->convertCategory($category, $categoriesExt);
        if ($convert['result'] != 'success') {
            return array(
                'result' => 'warning',
            );
        }
        $data = $convert['data'];
        $category_ipt = $this->category($data, true);
        if ($category_ipt) {
            $this->categorySuccess($parent_id, $category_ipt['term_id'], $category_ipt['term_taxonomy_id']);
            $this->afterSaveCategory($category_ipt['term_taxonomy_id'], $data, $category, $categoriesExt);
            return array(
                'result' => 'success',
                'id_desc' => $category_ipt['term_id']
            );
        }
        return array(
            'result' => 'warning'
        );
    }
    
    protected  function _requestByPost($url, $data = array(), $query = true){
    	$options = http_build_query($data);
    	$ch = curl_init($url);
    	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');    	
    	curl_setopt($ch, CURLOPT_HEADER, false);
    	curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $options);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_FAILONERROR, true);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 600);
    	curl_setopt_array($ch, array(CURLINFO_HEADER_OUT => true));
    	$response = curl_exec($ch);
    	curl_close($ch);
    	return $response;
    }        
}

if (!function_exists('mime_content_type')) {

    function mime_content_type($filename)
    {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.', $filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }
}