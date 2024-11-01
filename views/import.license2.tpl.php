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
<script type="text/javascript">
    (function ($) {
        $(document).ready(function () {
            $("#lecm-tips > li:gt(0)").hide(0);
            setInterval(function () {
                $('#lecm-tips > li:first')
                    .fadeOut(1000)
                    .next()
                    .fadeIn(1000)
                    .end()
                    .appendTo('#lecm-tips');
            }, 10000);
        });
    })(jle);
</script>
<form action="" method="post" id="form-import" class="">
    <input type="hidden" name="action" value="le_cart_migration"/>
    <input type="hidden" name="process" value="import"/>
    <div class="panel">
        <h3>Migration</h3>
        <div class="panel-body">
            <div style="font-weight: bold;">Migration is in progress! Please do not close your browser or remove Source
                Cart Connector file during the migration.
            </div>
            <div style="margin: 10px 0;">
                <p>Source cart: <strong><?php echo $notice['config']['cart_url']; ?></strong></p>                
            </div>
            <ul id="lecm-tips">
                <li class="tips-checkdata" style="display: block;"><span>Some of migrated data may not be properly displayed at storefront right after migration due to configuration peculiarities. Thus, it is highly recommended to check Migration results at the store backend.</span>
                </li>
                <li class="tips-backup" style="display: none;"><span>You should make a backup of your store before Migration.</span>
                </li>
                <li class="tips-clear" style="display: none;"><span>You can clear your Target Store data automatically before proceeding with Migration.</span>
                </li>
                <li class="tips-connector" style="display: none;"><span>Please do not remove Connector file during Migration.</span>
                </li>
                <li class="tips-stop" style="display: none;"><span>You can stop Migration anytime by refreshing this page, it will take you back to Step 1 which now has a "Resume" button.</span>
                </li>
                <li class="tips-seo" style="display: none;"><span>The tool has "SEO Plugin" allowing you to migrate SEO URL's from your Source Store to WooCommerce.</span>
                </li>
                <li class="tips-customfields" style="display: none;"><span>The tool also provide "Custom Fields Plugin" which allows you to migrate all your custom fields from your Source Store to WooCommerce.</span>
                </li>
            </ul>
            <div class="form-wrapper">
                <?php if ($notice['config']['add_option']['clear_shop'] && !$notice['is_running']): ?>
                    <div class="form-group" id="loading-clear"> Clearing shop ...</div>
                <?php endif; ?>

                <?php if ($notice['config']['import']['taxes']): ?>
                    <div class="form-group" id="process-taxes">
                        <label class="control-label col-lg-2">Taxes:</label>
                        <div class="col-lg-10">
                            <div class="process-bar">
                                <div class="process-bar-width"
                                     style="width: <?php echo $notice['taxes']['point']; ?>%;"></div>
                            </div>
                            <div class="console-log">Imported: <?php echo $notice['taxes']['imported']; ?>/<?php echo $notice['taxes']['real_total']; ?>, Errors: <?php echo $notice['taxes']['error']; ?></div>
                        </div>
                        <div class="try-import cls">
                            <div id="try-import-taxes">Retry</div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['config']['import']['manufacturers']): ?>
                    <div class="form-group" id="process-manufacturers">
                        <label class="control-label col-lg-2">Manufacturers:</label>
                        <div class="col-lg-10">
                            <div class="process-bar">
                                <div class="process-bar-width"
                                     style="width: <?php echo $notice['manufacturers']['point']; ?>%;"></div>
                            </div>
                            <div class="console-log">Imported: <?php echo $notice['manufacturers']['imported']; ?>/<?php echo $notice['manufacturers']['real_total']; ?>, Errors: <?php echo $notice['manufacturers']['error']; ?></div>
                        </div>
                        <div class="try-import cls">
                            <div id="try-import-manufacturers">Retry</div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['config']['import']['categories']): ?>
                    <div class="form-group" id="process-categories">
                        <label class="control-label col-lg-2">Categories:</label>
                        <div class="col-lg-10">
                            <div class="process-bar">
                                <div class="process-bar-width"
                                     style="width: <?php echo $notice['categories']['point']; ?>%;"></div>
                            </div>
                            <div class="console-log">Imported: <?php echo $notice['categories']['imported']; ?>/<?php echo $notice['categories']['real_total']; ?>, Errors: <?php echo $notice['categories']['error']; ?></div>
                        </div>
                        <div class="try-import cls">
                            <div id="try-import-categories">Retry</div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['config']['import']['products']): ?>
                    <div class="form-group" id="process-products">
                        <label class="control-label col-lg-2">Products:</label>
                        <div class="col-lg-10">
                            <div class="process-bar">
                                <div class="process-bar-width"
                                     style="width: <?php echo $notice['products']['point']; ?>%;"></div>
                            </div>
                            <div class="console-log">Imported: <?php echo $notice['products']['imported']; ?>/<?php echo $notice['products']['real_total']; ?>, Errors: <?php echo $notice['products']['error']; ?></div>
                            <div style="float: left;padding-top: 10px;margin-left: 10px;">(Limit <?php echo $notice['config']['limit']['products']; ?> products)</div>
                        </div>
                        <div class="try-import cls">
                            <div id="try-import-products">Rerty</div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['config']['import']['customers']): ?>
                    <div class="form-group" id="process-customers">
                        <label class="control-label col-lg-2">Customers:</label>
                        <div class="col-lg-10">
                            <div class="process-bar">
                                <div class="process-bar-width"
                                     style="width: <?php echo $notice['customers']['point']; ?>%;"></div>
                            </div>
                            <div class="console-log">Imported: <?php echo $notice['customers']['imported']; ?>/<?php echo $notice['customers']['real_total']; ?>, Errors: <?php echo $notice['customers']['error']; ?></div>
							<div style="float: left;padding-top: 10px;margin-left: 10px;">(Limit <?php echo $notice['config']['limit']['customers'] ?> customers)</div>
                        </div>
                        <div class="try-import cls">
                            <div id="try-import-customers">Retry</div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['config']['import']['orders']): ?>
                    <div class="form-group" id="process-orders">
                        <label class="control-label col-lg-2">Orders:</label>
                        <div class="col-lg-10">
                            <div class="process-bar">
                                <div class="process-bar-width"
                                     style="width: <?php echo $notice['orders']['point']; ?>%;"></div>
                            </div>
                            <div class="console-log">Imported: <?php echo $notice['orders']['imported']; ?>/<?php echo $notice['orders']['real_total']; ?>, Errors: <?php echo $notice['orders']['error']; ?></div>
                            <div style="float: left;padding-top: 10px;margin-left: 10px;">(Limit <?php echo $notice['config']['limit']['orders']; ?> orders)</div>
                        </div>
                        <div class="try-import cls">
                            <div id="try-import-orders">Retry</div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['config']['import']['reviews']): ?>
                    <div class="form-group" id="process-reviews">
                        <label class="control-label col-lg-2">Reviews:</label>
                        <div class="col-lg-10">
                            <div class="process-bar">
                                <div class="process-bar-width"
                                     style="width: <?php echo $notice['reviews']['point']; ?>%;"></div>
                            </div>
                            <div class="console-log">Imported: <?php echo $notice['reviews']['imported']; ?>
                                /<?php echo $notice['reviews']['real_total']; ?>,
                                Errors: <?php echo $notice['reviews']['error']; ?></div>
                        </div>
                        <div class="try-import cls">
                            <div id="try-import-reviews">Retry</div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['config']['import']['pages']): ?>
                    <div class="form-group" id="process-pages">
                        <label class="control-label col-lg-2">Pages:</label>
                        <div class="col-lg-10">
                            <div class="process-bar">
                                <div class="process-bar-width"
                                     style="width: <?php echo $notice['pages']['point']; ?>%;"></div>
                            </div>
                            <div class="console-log">Imported: <?php echo $notice['pages']['imported']; ?>
                                /<?php echo $notice['pages']['real_total']; ?>,
                                Errors: <?php echo $notice['pages']['error']; ?></div>
                        </div>
                        <div class="try-import cls">
                            <div id="try-import-pages">Retry</div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['config']['import']['postCat']): ?>
                    <div class="form-group" id="process-postCat">
                        <label class="control-label col-lg-2">Post Categories:</label>
                        <div class="col-lg-10">
                            <div class="process-bar">
                                <div class="process-bar-width"
                                     style="width: <?php echo $notice['postCat']['point']; ?>%;"></div>
                            </div>
                            <div class="console-log">Imported: <?php echo $notice['postCat']['imported']; ?>
                                /<?php echo $notice['postCat']['real_total']; ?>,
                                Errors: <?php echo $notice['postCat']['error']; ?></div>
                        </div>
                        <div class="try-import cls">
                            <div id="try-import-postCat">Retry</div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['config']['import']['posts']): ?>
                    <div class="form-group" id="process-posts">
                        <label class="control-label col-lg-2">Posts:</label>
                        <div class="col-lg-10">
                            <div class="process-bar">
                                <div class="process-bar-width"
                                     style="width: <?php echo $notice['posts']['point']; ?>%;"></div>
                            </div>
                            <div class="console-log">Imported: <?php echo $notice['posts']['imported']; ?>
                                /<?php echo $notice['posts']['real_total']; ?>,
                                Errors: <?php echo $notice['posts']['error']; ?></div>
                        </div>
                        <div class="try-import cls">
                            <div id="try-import-posts">Retry</div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($notice['config']['import']['comments']): ?>
                    <div class="form-group" id="process-comments">
                        <label class="control-label col-lg-2">Comments:</label>
                        <div class="col-lg-10">
                            <div class="process-bar">
                                <div class="process-bar-width"
                                     style="width: <?php echo $notice['comments']['point']; ?>%;"></div>
                            </div>
                            <div class="console-log">Imported: <?php echo $notice['comments']['imported']; ?>
                                /<?php echo $notice['comments']['real_total']; ?>,
                                Errors: <?php echo $notice['comments']['error']; ?></div>
                        </div>
                        <div class="try-import cls">
                            <div id="try-import-comments">Retry</div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
            <div class="lecm-csl-wrap">
                <div>Console:</div>
                <div id="lecm-csl-import" class="lecm-console">
                    <?php echo $notice['msg_start']; ?>
                </div>
            </div>
        </div>
        <div class="lecm-footer">
            <div id="form-import-loading" class="lecm-loading">Processing ...</div>
            <div id="try-clear-shop" class="lecm-submit" style="display: none;">Retry Clear Shop</div>
            <div id="form-import-submit" class="lecm-submit" style="display: none;">Clear Transient</div>
        </div>
        <div class="cls"></div>
    </div>
</form>
