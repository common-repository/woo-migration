<?php 
/**
 * @project: CartMigration
 * @author : LitExtension
 * @url    : http://litextension.com
 * @email  : litextension@gmail.com
 */

defined('ABSPATH') or die();

?>
<script type="text/javascript">
    (function($){
        $(document).on('click', '#le-clear-transient', function(){
			$.ajax({
				url: '<?php echo admin_url('admin-ajax.php'); ?>',
				type: 'POST',
				data: {
					action: 'le_cart_migration',
					process: 'finish'
				},
				dataType: 'json',
				success: function(response, textStatus, jqXHR) {
					alert('Finish clear transient!');
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert('Clear transient failed!');
				}
			});
		});
    })(jle);
</script>
<style type="text/css">
	.wrap .lecm-submit{
		-moz-box-shadow:inset 0px 1px 0px 0px #d9fbbe;
		-webkit-box-shadow:inset 0px 1px 0px 0px #d9fbbe;
		box-shadow:inset 0px 1px 0px 0px #d9fbbe;
		background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #b8e356), color-stop(1, #a5cc52) );
		background:-moz-linear-gradient( center top, #b8e356 5%, #a5cc52 100% );
		filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#b8e356', endColorstr='#a5cc52');
		background-color:#b8e356;
		-webkit-border-top-left-radius:6px;
		-moz-border-radius-topleft:6px;
		border-top-left-radius:6px;
		-webkit-border-top-right-radius:6px;
		-moz-border-radius-topright:6px;
		border-top-right-radius:6px;
		-webkit-border-bottom-right-radius:6px;
		-moz-border-radius-bottomright:6px;
		border-bottom-right-radius:6px;
		-webkit-border-bottom-left-radius:6px;
		-moz-border-radius-bottomleft:6px;
		border-bottom-left-radius:6px;
		text-indent:0;
		border:1px solid #83c41a;
		display:inline-block;
		color:#ffffff;
		font-family:Arial;
		font-size:15px;
		font-weight:bold;
		font-style:normal;
		height:40px;
		line-height:40px;
		text-decoration:none;
		text-align:center;
		text-shadow:1px 1px 0px #86ae47;
		padding: 0 10px;
		min-width: 150px;
		height: 40px;
		cursor: pointer;
	}
	.wrap .lecm-submit:hover{
		background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #a5cc52), color-stop(1, #b8e356) );
		background:-moz-linear-gradient( center top, #a5cc52 5%, #b8e356 100% );
		filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#a5cc52', endColorstr='#b8e356');
		background-color:#a5cc52;color: #fff; text-decoration: none;    
	}
</style>
<div class="wrap">
    <h2>Cart Migration Settings</h2>
    <form action="options.php" method="POST">
        <?php settings_fields(LeCaMg::LECAMG_SETTING); ?>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th><label>Taxes Per Batch</label></th>
                    <td><input type="text" class="regular-text" name="<?php echo LeCaMg::LECAMG_SETTING; ?>[taxes]" value="<?php echo $option['taxes']; ?>"/></td>
                </tr>
                <!-- <tr valign="top">
                    <th><label>Manufacturers Per Batch</label></th>
                    <td><input type="text" class="regular-text" name="<?php echo LeCaMg::LECAMG_SETTING; ?>[manufacturers]" value="<?php echo $option['manufacturers']; ?>"/></td>
                </tr> -->
                <tr valign="top">
                    <th><label>Categories Per Batch</label></th>
                    <td><input type="text" class="regular-text" name="<?php echo LeCaMg::LECAMG_SETTING; ?>[categories]" value="<?php echo $option['categories']; ?>"/></td>
                </tr>
                <tr valign="top">
                    <th><label>Products Per Batch</label></th>
                    <td><input type="text" class="regular-text" name="<?php echo LeCaMg::LECAMG_SETTING; ?>[products]" value="<?php echo $option['products']; ?>"/></td>
                </tr>
                <tr valign="top">
                    <th><label>Customers Per Batch</label></th>
                    <td><input type="text" class="regular-text" name="<?php echo LeCaMg::LECAMG_SETTING; ?>[customers]" value="<?php echo $option['customers']; ?>"/></td>
                </tr>   
                <tr valign="top">
                    <th><label>Orders Per Batch</label></th>
                    <td><input type="text" class="regular-text" name="<?php echo LeCaMg::LECAMG_SETTING; ?>[orders]" value="<?php echo $option['orders']; ?>"/></td>
                </tr> 
                <tr valign="top">
                    <th><label>Reviews Per Batch</label></th>
                    <td><input type="text" class="regular-text" name="<?php echo LeCaMg::LECAMG_SETTING; ?>[reviews]" value="<?php echo $option['reviews']; ?>"/></td>
                </tr> 
                <tr valign="top">
                    <th><label>Delay Time</label></th>
                    <td><input type="text" class="regular-text" name="<?php echo LeCaMg::LECAMG_SETTING; ?>[delay]" value="<?php echo $option['delay']; ?>"/></td>
                </tr> 
                <tr valign="top">
                    <th><label>Auto Retry After</label></th>
                    <td><input type="text" class="regular-text" name="<?php echo LeCaMg::LECAMG_SETTING; ?>[retry]" value="<?php echo $option['retry']; ?>"/></td>
                </tr>
                <tr valign="top">
                    <th><label>Source Cart Database Prefix</label></th>
                    <td><input type="text" class="regular-text" name="<?php echo LeCaMg::LECAMG_SETTING; ?>[prefix]" value="<?php echo $option['prefix']; ?>"/></td>
                </tr>                
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="submit" class="button button-primary" value="Submit Settings"/>
        </p>
    </form>
	<div id="le-clear-transient" class="lecm-submit">Clear Transient</div>
</div>