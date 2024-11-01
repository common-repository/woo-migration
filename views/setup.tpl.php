<?php
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

$notice = $this->_notice;
?>
<form action="" method="post" id="form-cart" class="">
    <input type="hidden" name="action" value="le_cart_migration"/>
    <input type="hidden" name="process" value="setup"/>
    <div class="panel">
        <h3>Source Cart Setup</h3>
        <div class="panel-body">
            <div class="form-wrapper">
                <?php $demo_guide = LeCaMg::path() . 'views/demo.tpl.php'; ?>
                <?php if(LeCaMg::DEMO_MODE == true ) include $demo_guide; ?>

                <?php if(!$this->_wooExists() || !$this->_checkCurlEnable() || !$this->_checkMediaWritable() || !$this->_checkFOpenEnable() || !$this->_checkMimeType()):?>
                    <div class="form-group lecm-warning">
                        <p>Warning:</p>
                        <?php if(!$this->_wooExists()){ ?><p>- Plugin WooCommerce not exists or not active. Please install and active it!</p><?php } ?>
                        <?php if(!$this->_checkCurlEnable()){ ?><p> - PHP extend curl not enable, need to enable curl</p><?php } ?>
                        <?php if(!$this->_checkMediaWritable()){ ?><p> - Folder "upload" is not writable, images could not be saved!</p><?php } ?>
                        <?php if(!$this->_checkFOpenEnable()){ ?><p> - PHP variable allow_url_fopen = Off, need to change to allow_url_fopen = On, please click <a href="https://www.google.com/?gws_rd=ssl#q=allow_url_fopen+php" target="_blank">here</a> for detail</p><?php } ?>
                        <?php if(!$this->_checkMimeType()){ ?><p> - PHP Mime type not enable, need enable mime type</p><?php }?>
                    </div>
                <?php endif; ?>
                <?php if(!LeCaMg::DEMO_MODE == true ){?>
                <div class="form-group guide-section">
                    <p class="le-notice-title"><b>Connector Setup</b></p>
                    <ol style="list-style: square; padding-left: 20px;">
                        <li>You need to copy Migration Connector to your Source Store, please find the connector in the module's folder, you should see "woocommerce_connector", please upload this folder to your Source Store's root folder</a>.
                            After finished please make sure Migration Connector can be reached at:
                            http(s)://yourstore.com/woocommerce_connector/connector.php. <br></li>
                        <li>For security, please open connector.php, find this very first line:
                            define('LECM_TOKEN', '123456');<br>
                            And change "123456" to another string, this will be used to enter to form below ( Cart Token )
                            and act like "password" to prevent unauthorized data access to your store. <br /></li>
                        <li>Please read http(s)://yourstore.com/woocommerce_connector/read_me.txt for more details.</li>
                    </ol>
                </div>
                <?php } ?>
                <div class="form-group">
                    <label class="col-lg-5">Cart Type:<span class="required">*</span></label>
                    <div class="col-lg-5">
                        <select name="cart_type">
                            <?php foreach($types as $cart_type => $cart_name): ?>
                                <option value="<?php echo $cart_type; ?>" <?php if($notice['config']['cart_type'] == $cart_type): ?>selected="selected"<?php endif; ?>><?php echo $cart_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p id="error-type" class="lecm-error"><i class="icon-arrow-up"></i>Cart type not correct!</p>
                    </div>
                    <div class="lecm-ok"></div>
                </div>
                <div class="form-group">
                    <label class="col-lg-5">Cart Url:<span class="required">*</span></label>
                    <div class="col-lg-5">
                        <input type="text" name="cart_url" id="cart_url" value="<?php $notice['config']['cart_url']; ?>"/>
                        <p id="error-url" class="lecm-error"><i class="icon-arrow-up"></i> Can not reach connector!</p>
                        <p id="error-http" class="lecm-error"><i class="icon-arrow-up"></i>Please enter a valid URL. Protocol is required (http://, https:// or ftp://)</p>
                        <p class="help-block" style="font-size: 11px;">Please enter correct Source Cart Url as it will be registered as the migration domain. Example: http://example.com</p>
                    </div>
                    <div class="lecm-ok"></div>
                </div>
                <div class="form-group">
                    <label class="col-lg-5">Token:<span class="required">*</span></label>
                    <div class="col-lg-5">
                        <input type="text" name="cart_token" value="<?php $notice['config']['cart_token']; ?>"/>
                        <p id="error-token" class="lecm-error"><i class="icon-arrow-up"></i> Cart token not correct!</p>
                    </div>
                    <div class="lecm-ok"></div>
                </div>
            </div>
        </div>
        <div class="lecm-footer">
            <div id="form-cart-loading" class="lecm-loading">Connecting ...</div>
            <div id="form-cart-submit" class="lecm-submit">Next &raquo;</div>
            <?php if (LeCaMg::DEMO_MODE): ?>
                <div id="clear-data-loading" class="lecm-clear-loading"> Clearing Data ... </div>
                <div id="clear-data" class="lecm-clear"> <i class="lecm-clear-warning"></i> Clear Current Data</div>
                <div class="lecm-clear-retry">
                    <div id="try-clear-data">Retry</div>
                </div>
            <?php endif; ?>
        </div>
        <div class="cls"></div>
    </div>
</form>