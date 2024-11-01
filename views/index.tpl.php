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
    (function($){
        $(document).ready(function(){
            $.LECM({
                urlRun: '<?php echo admin_url('admin-ajax.php'); ?>',
                fnResume : '<?php echo $notice['fn_resume'];?>',
                timeDelay: '<?php echo $notice['setting']['delay'] * 1000; ?>',
                autoRetry: '<?php echo $notice['setting']['retry'] * 1000; ?>',
                importText: 'Imported',
                errorText: 'Errors'
            });
        });
    })(jle);
</script>
<div class="wrap">
    <div id="lecm-wrap">
        <div id="lecm-menu">
            <div id="menu-setup" class="step-menu finished" open="#lecm-cart">
                <strong>1. Source Cart Setup</strong>
            </div>
            <div id="menu-config" class="step-menu" open="#lecm-config">
                <strong>2. Configuration</strong>
            </div>
            <div id="menu-confirm" class="step-menu" open="#lecm-confirm">
                <strong>3. Migration</strong>
            </div>
        </div>
        <div class="cls"></div>
        <div id="lecm-content">
            <div id="lecm-resume">
                <?php if($notice['is_running']){include dirname(__FILE__) . '/resume.tpl.php'; }?>
            </div>
            <div id="lecm-cart">
                <?php include dirname(__FILE__) . '/setup.tpl.php'; ?>
            </div>
            <div id="lecm-config">
            </div>
            <div id="lecm-confirm">
            </div>
            <div id="lecm-import">
            </div>
        </div>
        <div class="lecm-footer" style="text-align: center;">Cart Migration by LitExtension ver <?php echo LeCaMg::VERSION; ?></div>
    </div>
</div>
