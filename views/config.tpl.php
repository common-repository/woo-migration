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
<form action="" method="post" id="form-config" class="">
    <input type="hidden" name="action" value="le_cart_migration"/>
    <input type="hidden" name="process" value="config"/>
    <div class="panel">
        <h3>Configuration</h3>
        <div class="panel-body">
            <div class="form-wrapper">
                <?php if ($notice['config']['config_support']['category_map']): ?>
                    <div class="form-group">
                        <div class="group-title">Root Category</div>
                        <div class="group-guide">Choose a Category to put migrated categories from Source Cart into.
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-5">
                            <select name="cat">
                                <?php foreach ($notice['config']['cat_data'] as $cat_src_id => $cat_src_name): ?>
                                    <option value="<?php echo $cat_src_id; ?>"><?php echo $cat_src_name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="lecm-arrow"></div>
                        <div class="col-lg-5">
                            <label>Default Category</label>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['config']['config_support']['lang_map']): ?>
                    <div class="form-group">
                        <div class="group-title">Languages Mapping</div>
                        <div class="group-guide">If your Source Cart is multilingual, corresponding languages( or
                            storeviews) should
                            be available in Target WooCommerce Store. You may need to create additional languages in
                            WooCommerce Store to
                            avoid data loss.
                        </div>
                    </div>
                    <div class="form-group lang-check">
                        <div class="col-lg-5">
                            <select name="languages">
                                <?php foreach ($notice['config']['language_data'] as $lang_src_id => $lang_src_name): ?>
                                    <option value="<?php echo $lang_src_id ?>"><?php echo $lang_src_name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="lecm-arrow"></div>
                        <div class="col-lg-5">
                            <label>Default Language</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-5">
                            <p id="error-lang" class="lecm-error"><i class="icon-arrow-up"></i> Can not be the same
                                language</p>
                        </div>
                        <div class="col-lg-5">
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['config']['config_support']['currency_map']): ?>
                    <div class="form-group">
                        <div class="group-title">Currencies Mapping</div>
                        <div class="group-guide">Please assign Source Cart currencies to proper Target WooCommerce Store
                            currencies.
                            Creating currencies in Target WooCommerce Store may be needed.
                        </div>
                    </div>
                    <?php $currencies = get_woocommerce_currencies(); ?>
                    <?php foreach ($notice['config']['currency_data'] as $cur_src_id => $cur_src_name): ?>
                        <div class="form-group">
                            <div class="col-lg-5">
                                <label><?php echo $cur_src_name; ?></label>
                            </div>
                            <div class="lecm-arrow"></div>
                            <div class="col-lg-5">
                                <select name="currencies[<?php echo $cur_src_id ?>]">
                                    <?php foreach ($currencies as $cur_id => $cur_name) { ?>
                                        <option value="<?php echo $cur_id ?>"><?php echo $cur_name; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($notice['config']['config_support']['order_status_map']): ?>
                    <div class="form-group">
                        <div class="group-title">Orders Status Mapping</div>
                        <div class="group-guide">Please assign Source Cart order statuses to proper Target WooCommerce
                            Store order
                            statuses. Creating additional order statues in Target WooCommerce Store may be needed.
                        </div>
                    </div>
                    <?php $order_status = wc_get_order_statuses(); ?>
                    <?php foreach ($notice['config']['order_status_data'] as $ord_stt_src_id => $ord_stt_src_name): ?>
                        <div class="form-group">
                            <div class="col-lg-5">
                                <label><?php echo $ord_stt_src_name; ?></label>
                            </div>
                            <div class="lecm-arrow"></div>
                            <div class="col-lg-5">
                                <select name="order_status[<?php echo $ord_stt_src_id ?>]">
                                    <?php foreach ($order_status as $ord_stt_id => $ord_stt_name) { ?>
                                        <option value="<?php echo $ord_stt_id; ?>"><?php echo $ord_stt_name; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($notice['config']['config_support']['country_map']): ?>
                    <div class="form-group">
                        <div class="group-title">Countries Mapping</div>
                    </div>
                    <?php
                    $wc_countries = new WC_Countries();
                    $countries = $wc_countries->get_countries();
                    ?>
                    <?php foreach ($notice['config']['country_data'] as $country_src_id => $country_src_name): ?>
                        <div class="form-group">
                            <div class="col-lg-5">
                                <label><?php echo $country_src_name; ?></label>
                            </div>
                            <div class="lecm-arrow"></div>
                            <div class="col-lg-5">
                                <select name="countries[<?php echo $country_src_id ?>]">
                                    <?php foreach ($countries as $country_id => $country_name) { ?>
                                        <option value="<?php echo $country_id; ?>"><?php echo $country_name; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($notice['config']['config_support']['customer_group_map']): ?>
                    <div class="form-group">
                        <div class="group-title">Customer Group Mapping</div>
                    </div>
                    <?php
                    $wp_roles = LeCaMg::getGlobal('wp_roles');
                    $roles = $wp_roles->get_names();
                    ?>
                    <?php foreach ($notice['config']['customer_group_data'] as $cus_grp_src_id => $cus_grp_src_name): ?>
                        <div class="form-group">
                            <div class="col-lg-5">
                                <label><?php echo $cus_grp_src_name; ?></label>
                            </div>
                            <div class="lecm-arrow"></div>
                            <div class="col-lg-5">
                                <select name="customer_group[<?php echo $cus_grp_src_id ?>]">
                                    <?php foreach ($roles as $role_id => $role_name) { ?>
                                        <option value="<?php echo $role_id; ?>"><?php echo $role_name; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="form-group">
                    <div class="group-title">Entities to Migrate</div>
                    <div class="group-guide">Select entities to migrate.</div>
                    <div id="entity-section">
                        <div class="lv0">
                            <div><input type="checkbox" id="select-all"/><label class="entity-label select-all">Select
                                    all</label></div>
                        </div>
                        <div class="lv0">
                            <?php if ($notice['config']['import_support']['taxes']): ?>
                                <div><input type="checkbox" name="taxes" class="lv1"/><label
                                        class="entity-label">Taxes</label></div>
                            <?php endif; ?>
                            <?php if ($notice['config']['import_support']['manufacturers']): ?>
                                <div><input type="checkbox" name="manufacturers" class="lv1"/><label
                                        class="entity-label">Manufacturers</label></div>
                            <?php endif; ?>
                            <?php if ($notice['config']['import_support']['categories']): ?>
                                <div><input type="checkbox" name="categories" class="lv1"/><label class="entity-label">Categories</label>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="lv0">
                            <?php if ($notice['config']['import_support']['products']): ?>
                                <div><input type="checkbox" name="products" class="lv1"/><label class="entity-label">Products</label>
                                </div>
                                <?php if ($notice['config']['import_support']['reviews']) { ?>
                                    <div>
                                        <div><input type="checkbox" name="reviews" class="lv2"/><label
                                                class="entity-label">Reviews</label></div>
                                    </div>
                                <?php } ?>
                            <?php endif; ?>
                        </div>
                        <div class="lv0">
                            <?php if ($notice['config']['import_support']['customers']): ?>
                                <div><input type="checkbox" name="customers" class="lv1"/><label class="entity-label">Customers</label>
                                </div>
                            <?php endif; ?>
                            <?php if ($notice['config']['import_support']['orders']): ?>
                                <div><input type="checkbox" name="orders" class="lv2"/><label class="entity-label">Orders</label>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="lv0">
                            <?php if ($notice['config']['import_support']['pages']): ?>
                                <div><input type="checkbox" name="pages" class="lv1"/><label class="entity-label">Pages</label></div>
                            <?php endif; ?>
                            <?php if ($notice['config']['import_support']['postCat']): ?>
                                <div><input type="checkbox" name="postCat" class="lv2"/><label class="entity-label">Post Categories</label></div>
                            <?php endif; ?>
                        </div>
                        <div class="lv0">
                            <?php if ($notice['config']['import_support']['posts']): ?>
                                <div><input type="checkbox" name="posts" class="lv1"/><label class="entity-label">Posts</label></div>
                                <?php if ($notice['config']['import_support']['comments']) { ?>
                                    <div>
                                        <div><input type="checkbox" name="comments" class="lv2"/><label class="entity-label">Comments</label></div>
                                    </div>
                                <?php } ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p id="error-entity" class="lecm-error"><i class="icon-arrow-up"></i> You must select at least one entity!</p>
                </div>
                <div class="form-group">
                    <div class="group-title">Additional Options</div>
                    <div id="addition-section">
                        <div><input type="checkbox" name="add_new"
                                    <?php if (LeCaMg::DEMO_MODE): ?>disabled="disabled"<?php endif; ?>/><label
                                class="entity-label">Migrate recent data (adds new entities only)</label></div>
                        <div><input type="checkbox" name="clear_shop"/><label class="entity-label">Clear current data on
                                Target Store before Migration</label></div>
                        <div><input type="checkbox" name="img_des"/><label class="entity-label">Transfer images from
                                Product descriptions to Target Store</label></div>
                        <div><input type="checkbox" name="seo" id="choose-seo"
                                    <?php if (LeCaMg::DEMO_MODE || !$seoPlugin): ?>disabled="disabled"<?php endif; ?>/><label
                                class="entity-label">Migrate categories and products SEO URLs</label></div>
                    </div>
                </div>
                <div class="form-group" id="seo_plugin" style="display: none;">
                    <label class="col-lg-5">Choose Plugin Seo In Source Cart</label>
                    <div class="lecm-arrow"></div>
                    <div class="col-lg-5">
                        <select name="seo_plugin">
                            <?php if ($seoPlugin): ?>
                                <?php foreach ($seoPlugin as $seo_plugin_model => $seo_plugin_label): ?>
                                    <option
                                        value="<?php echo $seo_plugin_model ?>"><?php echo $seo_plugin_label; ?></option>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <option value="">-- Select Plugin --</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="lecm-footer">
            <div id="form-config-loading" class="lecm-loading">Processing ...</div>
            <div id="form-config-submit" class="lecm-submit">Next &raquo;</div>
        </div>
        <a id="form-config-back" class="lecm-back">&laquo; Back to previous step</a>
        <div class="cls"></div>
    </div>
</form>
