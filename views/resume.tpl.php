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
<form action="" method="post" id="form-resume" class="">
    <input type="hidden" name="action" value="le_cart_migration"/>
    <input type="hidden" name="process" value="resume"/>
    <div class="panel">
        <h3>Resume</h3>
        <div class="panel-body">
            <div class="form-wrapper">
                <div class="section-title">Source Cart</div>
                <div class="form-group">
                    <label class="col-lg-5">Cart type:</label>
                    <div class="col-lg-5"><?php echo $types[$notice['config']['cart_type']]; ?></div>
                </div>
                <div class="form-group">
                    <label class="col-lg-5">Cart url:</label>
                    <div class="col-lg-5"><?php echo $notice['config']['cart_url']; ?></div>
                </div>

                <?php if($notice['config']['config_support']['category_map']): ?>
                    <div class="section-title">Root category</div>
                    <div class="form-group">
                        <label class="col-lg-5"><?php echo $notice['config']['cat_data'][$notice['config']['cat']];?></label>
                        <div class="col-lg-5">Default Category</div>
                    </div>
                <?php endif; ?>

                <?php if($notice['config']['config_support']['lang_map']):?>
                    <div class="group-title">Languages Mapping</div>
                    <div class="form-group">
                        <label class="col-lg-5"><?php echo $notice['config']['language_data'][$notice['config']['languages']]; ?>:</label>
                        <div class="col-lg-5">Default Language</div>
                    </div>
                <?php endif; ?>

                <?php if($notice['config']['config_support']['currency_map']):?>
                    <div class="group-title">Currencies Mapping</div>
                    <?php $currencies = get_woocommerce_currencies(); ?>
                    <?php foreach($notice['config']['currencies'] as $currency_src => $currency_desc): ?>
                        <div class="form-group">
                            <label class="col-lg-5"><?php echo $notice['config']['currency_data'][$currency_src]; ?>:</label>
                            <div class="col-lg-5"><?php echo $currencies[$currency_desc];?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if($notice['config']['config_support']['order_status_map']): ?>
                    <div class="group-title">Orders Status Mapping</div>
                    <?php $order_status = wc_get_order_statuses(); ?>
                    <?php foreach($notice['config']['order_status'] as $order_status_src => $order_status_desc):?>
                        <div class="form-group">
                            <label class="col-lg-5"><?php echo $notice['config']['order_status_data'][$order_status_src]; ?>:</label>
                            <div class="col-lg-5"><?php echo $order_status[$order_status_desc]; ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if($notice['config']['config_support']['country_map']):?>
                    <div class="group-title">Countries Mapping</div>
                    <?php
                    $wc_countries = new WC_Countries();
                    $countries = $wc_countries->get_countries();
                    ?>
                    <?php foreach($notice['config']['countries'] as $country_src => $country_desc): ?>
                        <div class="form-group">
                            <label class="col-lg-5"><?php echo $notice['config']['country_data'][$country_src]; ?>:</label>
                            <div class="col-lg-5"><?php echo $countries[$country_desc]; ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if($notice['config']['config_support']['customer_group_map']): ?>
                    <div class="group-title">Customer Group Mapping</div>
                    <?php
                    $wp_roles = LeCaMg::getGlobal('wp_roles');
                    $roles = $wp_roles->get_names();
                    ?>
                    <?php foreach($notice['config']['customer_group'] as $customer_group_src => $customer_group_desc): ?>
                        <div class="form-group">
                            <label class="col-lg-5"><?php echo $notice['config']['customer_group_data'][$customer_group_src]; ?></label>
                            <div class="col-lg-5"><?php echo $roles[$customer_group_desc]; ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="group-title">Additional Options</div>
                <div class="form-group">
                    <?php if($notice['config']['add_option']['add_new']): ?><div>- Migrate recent data (adds new entities only) </div><?php endif;?>
                    <?php if($notice['config']['add_option']['clear_shop']): ?><div>- Clear current data on Target Store before Migration </div><?php endif;?>
                    <?php if($notice['config']['add_option']['img_des']): ?><div>- Transfer images from Product descriptions to Target Store </div><?php endif;?>
                    <?php if($notice['config']['add_option']['seo']): ?><div>- Migrate categories and products SEO URLs </div><?php endif;?>
                </div>
                <div class="group-title">Previous Process</div>
                <div class="form-group">
                    <?php foreach($notice['config']['import'] as $import_type => $import_val): ?>
                        <?php if($import_val): ?>
                            <div>- <?php echo $notice[$import_type]['imported']; ?>/<?php echo $notice[$import_type]['total']; ?> <?php echo $import_type; ?> completed, <?php echo $notice[$import_type]['error']; ?> errors <?php if($notice[$import_type]['finish']){?><span class="lecm-finished"></span><?php } ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="lecm-footer">
            <div id="form-resume-loading" class="lecm-loading">Processing ...</div>
            <div id="form-resume-submit" class="lecm-submit">Resume</div>
        </div>
        <div class="cls"></div>
    </div>
</form>