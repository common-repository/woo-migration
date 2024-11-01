<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

class LeCaMgDisplay
{
    public function __construct() {
        
    }
    
    public static function displaySetting(){
        $option = get_option(LeCaMg::LECAMG_SETTING);
        if(!is_array($option)){
            $option = unserialize($option);
        }
        include LeCaMg::path() . 'views/setting.tpl.php';
    }

    public static function displayCartMigration(){
        $process = new LeCaMgProcess();
        $process->index();
        return ;
    }    
    
    public static function displayAjax(){
        if(!isset($_POST['process'])){
            wp_die();
        }
        $process = new LeCaMgProcess();
        $action = sanitize_text_field($_POST['process']);
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : null;
        $process->$action($type);
        return;
    }
    
}

class LeCaMgProcess
{
    protected $_notice;
    protected $_cart;
    protected $_user_id;
    protected $_import_action = array('taxes', 'manufacturers', 'categories', 'products', 'customers', 'orders', 'reviews', 'pages', 'postCat', 'posts', 'comments');
    protected $_next_action = array(
        'taxes' => 'manufacturers',
        'manufacturers' => 'categories',
        'categories' => 'products',
        'products' => 'customers',
        'customers' => 'orders',
        'orders' => 'reviews',
        'reviews' => 'pages',
        'pages' => 'postCat',
        'postCat' => 'posts',
        'posts' => 'comments',
        'comments' => false
    );
    protected $_simple_action = array(
        'taxes' => 'tax',
        'manufacturers' => 'manufacturer',
        'categories' => 'category',
        'products' => 'product',
        'customers' => 'customer',
        'orders' => 'order',
        'reviews' => 'review',
        'pages' => 'page',
        'postCat' => 'postCat',
        'posts' => 'post',
        'comments' => 'comment',
    );

    protected $_seo = array(
        'cart66' => array(
            'seo_cart66_default' => 'SEO Default',
        ),
        'oscommerce' => array(
            'seo_oscommerce_default' => 'SEO Default',
            'seo_oscommerce_custom' => 'SEO Custom'
        ),
        'zencart' => array(
            'seo_zencart_default' => 'SEO Default',
            'seo_zencart_custom' => 'SEO Custom'
        ),
        'xtcommerce' => array(
            'seo_xtcommerce_default' => 'SEO Default',
            'seo_xtcommerce_custom' => 'SEO Custom'
        ),
        'magento' => array(
            'seo_magento_default' => 'SEO Default',
            'seo_magento_defaultmage2' => 'SEO Default Magento 2.x'
        ),
        'prestashop' => array(
            'seo_prestashop_default' => 'SEO Default',
            'seo_prestashop_custom' => 'SEO Custom'
        ),
        'wpecommerce' => array(
            'seo_wpecommerce_default' => 'SEO Default',
            'seo_wpecommerce_custom' => 'SEO Custom',
        ),
        'cscart' => array(
            'seo_cscart_default' => 'SEO Default',
            'seo_cscart_custom' => 'SEO Custom',
        ),
        'opencart' => array(
            'seo_opencart_default' => 'SEO Default',
            'seo_opencart_custom' => 'SEO Custom',
        ),
        'oxideshop' => array(
            'seo_oxideshop_default' => 'SEO Default',
        ),
        'virtuemart' => array(
//            'seo_virtuemart_default' => 'SEO Default',
            'seo_virtuemart_custom' => 'SEO Custom',
        ),
        'xcart' => array(
            'seo_xcart_default' => 'SEO Default',
            'seo_xcart_custom' => 'SEO Custom',
        ),
        'cubecart' => array(
            'seo_cubecart_default' => 'SEO Default',
        ),
        'shopp' => array(
            'seo_shopp_default' => 'SEO Default',
        ),
        'marketpress' => array(
            'seo_marketpress_default' => 'SEO Default',
        ),
        'pinnaclecart' => array(
            'seo_pinnaclecart_default' => 'SEO Default',
        ),
        'jigoshop' => array(
            'seo_jigoshop_default' => 'SEO Default'
        )
    );

    public function __construct() {
        
    }    

    public function index(){
        $this->_initCart();
        $option = get_option(LeCaMg::LECAMG_SETTING);
        if(!is_array($option)){
            $option = unserialize($option);
        }
        $this->_notice['setting'] = $option;
        $types = LeCaMg::getModel('type')->show();
        include LeCaMg::path() . 'views/index.tpl.php';
    }

    public function setup(){
        $response = $this->_defaultResponse();
        $router = LeCaMg::getModel('cart');
        $delete = $this->_deleteNotice($router);
        if($delete === false){
            $response = $router->errorDatabase(false);
            $this->_responseAjax($response);
            return ;
        }
        $this->_notice = $this->_getNotice($router);
        if(!$this->_notice){
            $this->_notice = $router->defaultNotice();
        }
        $configs = array('cart_type', 'cart_url', 'cart_token');
        foreach($configs as $config){
            $value = isset($_POST[$config]) ? sanitize_text_field($_POST[$config]) : '';
            $value = trim($value, ' ');
            $this->_notice['config'][$config] = rtrim($value, '/');
        }
        $router->setNotice($this->_notice);
        $detected = $router->route(true);
        if($detected['result'] != 'success'){
            $this->_responseAjax($detected);
            return ;
        }
        $cart_name = $detected['cart'];
        $this->_cart = LeCaMg::getModel($cart_name);
        $this->_notice = $router->getNotice();
        $this->_cart->setNotice($this->_notice);
        $result = $this->_cart->displayConfig();
        if($result['result'] != 'success'){
            $this->_responseAjax($result);
            return ;
        }
        $this->_notice = $this->_cart->getNotice();
        $seoPlugin = $this->_getSeoPlugin($this->_notice['config']['cart_type']);
        $html = "";
        ob_start();
        include LeCaMg::path() . 'views/config.tpl.php';
        $html = ob_get_contents();
        ob_end_clean();
        update_option("LEPP_TYPE", $this->_notice['config']['cart_type']);
        $save = $this->_saveNotice($this->_cart);
        if($save === false){
            $response = $router->errorDatabase(false);
            $this->_responseAjax($response);
            return ;
        }
        $response['result'] = 'success';
        $response['html'] = $html;
        $this->_responseAjax($response);
        return ;
    }

    public function config(){
        $types = LeCaMg::getModel('type')->show();
        $this->_initCart();
        $result = $this->_cart->displayConfirm();
        if($result['result'] != 'success'){
            $this->_responseAjax($result);
            return ;
        }
        $this->_notice = $this->_cart->getNotice();
        $html = "";
        ob_start();
        include LeCaMg::path() . 'views/confirm.tpl.php';
        $html = ob_get_contents();
        ob_end_clean();
        $save = $this->_saveNotice($this->_cart);
        if($save === false){
            $response = $this->_cart->errorDatabase(false);
            $this->_responseAjax($response);
            return ;
        }
        $response['result'] = 'success';
        $response['html'] = $html;
        $this->_responseAjax($response);
        return ;
    }

    public function confirm(){
        $types = LeCaMg::getModel('type')->show();
        $this->_initCart();
        $result = $this->_cart->displayImport();
        if($result['result'] != 'success'){
            $this->_responseAjax($result);
            return ;
        }
        $this->_notice = $this->_cart->getNotice();
        if($this->_notice['config']['add_option']['clear_shop']){
            $this->_notice['msg_start'] = $this->_cart->consoleSuccess("Clearing store ...");
        } else {
            $this->_notice['msg_start'] = $this->_cart->getMsgStartImport('taxes');
        }
        $html = "";
        ob_start();
        include LeCaMg::path() . 'views/import.tpl.php';
        $html = ob_get_contents();
        ob_end_clean();
        $save = $this->_saveNotice($this->_cart);
        if($save === false){
            $response = $this->_cart->errorDatabase(false);
            $this->_responseAjax($response);
            return ;
        }
        $response['result'] = 'success';
        $response['html'] = $html;
        $this->_responseAjax($response);
        return ;
    }

    public function resume(){
        $types = LeCaMg::getModel('type')->show();
        $this->_initCart();
        $setting = get_option(LeCaMg::LECAMG_SETTING);
        if(!is_array($setting)){
            $setting = unserialize($setting);
        }
        $this->_notice['setting'] = $setting;
        $this->_notice['msg_start'] = $this->_cart->consoleSuccess("Resuming ...");
        $html = "";
        ob_start();
        include LeCaMg::path() . 'views/import.tpl.php';
        $html = ob_get_contents();
        ob_end_clean();
        $save = $this->_saveNotice($this->_cart);
        if($save === false){
            $response = $this->_cart->errorDatabase(false);
            $this->_responseAjax($response);
            return ;
        }
        $response['result'] = 'success';
        $response['html'] = $html;
        $this->_responseAjax($response);
        return ;
    }

    public function clearData(){
        $this->_initCart();
        $response = $this->_cart->clearData();
        $save = $this->_saveNotice($this->_cart);
        if($save === false){
            $response = $this->_cart->errorDatabase(false);
            $this->_responseAjax($response);
            return ;
        }
        $this->_responseAjax($response);
        return ;
    }

    public function clear(){
        $this->_initCart();
        $response = $this->_cart->clear();
        $this->_notice = $this->_cart->getNotice();
        if($response['result'] == "success"){
            $this->_notice['taxes']['time_start'] = time();
        }
        $save = $this->_saveNotice($this->_cart);
        if($save === false){
            $response = $this->_cart->errorDatabase(false);
            $this->_responseAjax($response);
            return ;
        }
        $this->_responseAjax($response);
        return ;
    }

    public function import($action){
        $this->_initCart();
        $response = $this->_defaultResponse();
        $this->_notice['is_running'] = true;
        if(!$this->_notice['config']['import'][$action]){
            $next_action = $this->_next_action[$action];
            if($next_action && $this->_notice['config']['import'][$next_action]){
                $prepare_next = 'prepareImport' . ucfirst($next_action);
                $this->_cart->$prepare_next();
                $this->_notice[$next_action]['time_start'] = time();
            }
            if($next_action){
                $fn_resume = 'import' . ucfirst($next_action);
                $this->_notice['fn_resume'] = $fn_resume;
            }
            if($action == 'comments'){
                $this->_cart->updateLicense();
                if(!LeCaMg::DEMO_MODE){
                    $this->_cart->saveRecentNotice($this->_notice);
                }
                $this->_notice['is_running'] = false;
                $response['msg'] .= $this->_cart->consoleSuccess('Finished migration!');
            }
            $notice = $this->_cart->getNotice();
            $this->_notice['extend'] = $notice['extend'];
            $save = $this->_saveNotice($this->_cart);
            if ($save === false) {
                $response = $this->_cart->errorDatabase(true);
                $this->_responseAjax($response);
                return ;
            }
            $this->_responseAjax($response);
            return ;
        }
        $total = $this->_notice[$action]['total'];
        $imported = $this->_notice[$action]['imported'];
        $error = $this->_notice[$action]['error'];
        $id_src = $this->_notice[$action]['id_src'];
        $simple_action = $this->_simple_action[$action];
        $next_action = $this->_next_action[$action];
        if($imported < $total){
            $fn_get_main = 'get' . ucfirst($action) . 'Main';
            $fn_get_ext =  'get' . ucfirst($action) . 'Extra';
            $fn_get_id = 'get' . ucfirst($simple_action) . 'Id';
            $fn_check_import = 'check' . ucfirst($simple_action) . 'Import';
            $fn_convert = 'convert' . ucfirst($simple_action);
            $fn_import = 'import' . ucfirst($simple_action);
            $fn_after_save = 'afterSave' . ucfirst($simple_action);
            $fn_addition = 'addition' . ucfirst($simple_action);
            $mains = $this->_cart->$fn_get_main();
            if($mains['result'] != 'success'){
                $this->_responseAjax($mains);
                return ;
            }
            $ext = $this->_cart->$fn_get_ext($mains);
            if($ext['result'] != 'success'){
                $this->_responseAjax($ext);
                return ;
            }
            foreach($mains['object'] as $main){
                if($imported >= $total){
                    break ;
                }
                $id_src = $this->_cart->$fn_get_id($main, $ext);
                $imported++;
                if($this->_cart->$fn_check_import($main, $ext)){
                    continue ;
                }
                $convert = $this->_cart->$fn_convert($main, $ext);
                if($convert['result'] == 'error'){
                    $this->_responseAjax($convert);
                    return ;
                }
                if($convert['result'] == 'warning'){
                    $error++;
                    $response['msg'] .= $convert['msg'];
                    continue ;
                }
                if($convert['result'] == 'pass'){
                    continue ;
                }
                if($convert['result'] == 'wait'){
                    $notice = $this->_cart->getNotice();
                    $this->_notice['extend'] = $notice['extend'];
                    $response['result'] = 'process';
                    $response[$action] = $this->_notice[$action];
                    $save = $this->_saveNotice($this->_cart);
                    if ($save === false) {
                        $response = $this->_cart->errorDatabase(true);
                        $this->_responseAjax($response);
                        return ;
                    }
                    $this->_responseAjax($response);
                    return ;
                }
                if($convert['result'] == 'addition'){
                    $data = $convert['data'];
                    $add_result = $this->_cart->$fn_addition($data, $main, $ext);
                    if($add_result['result'] != 'success'){
                        $notice = $this->_cart->getNotice();
                        $this->_notice['extend'] = $notice['extend'];
                        $response['result'] = 'process';
                        $response[$action] = $this->_notice[$action];
                        $save = $this->_saveNotice($this->_cart);
                        if ($save === false) {
                            $response = $this->_cart->errorDatabase(true);
                            $this->_responseAjax($response);
                            return ;
                        }
                        $this->_responseAjax($response);
                        return ;
                    }
                }
                $data = $convert['data'];
                $import = $this->_cart->$fn_import($data, $main, $ext);
                if($import['result'] == 'error'){
                    $this->_responseAjax($import);
                    return ;
                }
                if($import['result'] != 'success'){
                    $error++;
                    $response['msg'] .= $import['msg'];
                    continue ;
                }
                $id_desc = $import['id_desc'];
                $this->_cart->$fn_after_save($id_desc, $data, $main, $ext);
            }
            $response['result'] = 'process';
            $this->_notice[$action]['point'] = $this->_getPoint($total, $imported);
        } else {
            $response['result'] = 'success';
            $msg_time = $this->_cart->createTimeToShow(time() - $this->_notice[$action]['time_start']);
            $response['msg'] .= $this->_cart->consoleSuccess('Finished importing ' . $action . '! Run time: ' . $msg_time);
            $response['msg'] .= $this->_cart->getMsgStartImport($next_action);
            if($next_action){
                $this->_notice[$next_action]['time_start'] = time();
            }
            $this->_notice[$action]['finish'] = true;
            $this->_notice[$action]['point'] = $this->_getPoint($total, $imported, true);
            if($next_action){
                $this->_notice['fn_resume'] = 'import' . ucfirst($next_action);
            }
            if($next_action && $this->_notice['config']['import'][$next_action]){
                $fn_prepare = 'prepareImport' . ucfirst($next_action);
                $this->_cart->$fn_prepare();
            }
        }
        $this->_notice[$action]['imported'] = $imported;
        $this->_notice[$action]['id_src'] = $id_src;
        $this->_notice[$action]['error'] = $error;
        $response[$action] = $this->_notice[$action];
        $notice = $this->_cart->getNotice();
        $this->_notice['extend'] = $notice['extend'];
        if($action == 'comments' && $response['result'] == 'success'){
            $this->_cart->updateLicense();
            if(!LeCaMg::DEMO_MODE){
                $this->_cart->saveRecentNotice($this->_notice);
            }
            $this->_notice['is_running'] = false;
        }
        $save = $this->_saveNotice($this->_cart);
        if ($save === false) {
            $response = $this->_cart->errorDatabase(true);
            $this->_responseAjax($response);
            return ;
        }
        $this->_responseAjax($response);
        return ;
    }

    public function finish(){
        $this->_initCart();
        $this->_cart->finish();
        $this->_deleteNotice($this->_cart);
        $response = $this->_defaultResponse();
        $response['result'] = "success";
        $response['msg'] = $this->_cart->consoleSuccess("Finish clear transient!");
        $this->_responseAjax($response);
        return ;
    }

    protected function _initCart(){
        $router = LeCaMg::getModel('cart');
        $this->_notice = $this->_getNotice($router);
        if(!$this->_notice){
            $this->_notice = $router->defaultNotice();
        }
        $router->setNotice($this->_notice);
        $detected = $router->route();
        if($detected['result'] != "success"){
            $this->_responseAjax($detected);
            return $this;
        }
        $model_cart = $detected['cart'];
        $this->_cart = LeCaMg::getModel($model_cart);
        $this->_cart->setNotice($this->_notice);
        return $this;
    }

    protected function _getNotice($cart){
        if(LeCaMg::DEMO_MODE){
            $notice = isset($_SESSION['lecm_notice']) ? $_SESSION['lecm_notice'] : false;
            return $notice;
        }
        return $cart->getUserNotice($this->_getUserId());
    }

    protected function _saveNotice($cart){
        if(LeCaMg::DEMO_MODE){
            $_SESSION['lecm_notice'] = $this->_notice;
            return true;
        }
        return $cart->saveUserNotice($this->_getUserId(), $this->_notice);
    }

    protected function _deleteNotice($cart){
        if(LeCaMg::DEMO_MODE){
            unset($_SESSION['lecm_notice']);
            return true;
        }
        return $cart->deleteUserNotice($this->_getUserId());
    }

    protected function _responseAjax($data){
        echo json_encode($data);
        wp_die();
    }

    protected function _defaultResponse(){
        return array(
            'result' => '',
            'msg' => '',
            'html' => '',
            'elm' => ''
        );
    }

    protected function _wooExists(){
        return is_plugin_active(LeCaMg::PLUGIN_WOOCOMMERCE);
    }

    protected function _checkCurlEnable(){
        return function_exists('curl_version');
    }

    protected function _checkMediaWritable(){
        $upload = wp_upload_dir();
        return is_writable($upload['basedir']);
    }

    protected function _checkFOpenEnable(){
        return ini_get('allow_url_fopen');
    }

    protected function _checkMimeType(){
        return function_exists('mime_content_type');
    }

    protected function _getPoint($total, $import, $finish = false)
    {
        if (!$finish && $total == 0) {
            return 0;
        }
        if ($total <= $import) {
            $point = 100;
        } else {
            $percent = $import / $total;
            $point = number_format($percent, 2) * 100;
        }
        return $point;
    }

    protected function _getUserId(){
        if(!$this->_user_id){
            $this->_user_id = get_current_user_id();
        }
        return $this->_user_id;
    }

    protected function _getSeoPlugin($cart_type){
        $seo = isset($this->_seo[$cart_type]) ? $this->_seo[$cart_type] : false;
        if(!$seo){
            return $seo;
        }
        $seo_data = array();
        foreach($seo as $model_name => $label){
            $model = LeCaMg::getModel($model_name);
            if($model){
                $seo_data[$model_name] = $label;
            }
        }
        return $seo_data;
    }

}
