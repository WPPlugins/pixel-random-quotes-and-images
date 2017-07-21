<?php
//global variable for values of new quote upload form when post is failed
$pixel_new_upload = "";
//register menu for admin page and start the main function
add_action('admin_menu', 'pixel_admin_actions');
function pixel_admin_actions() {
	add_plugins_page("Pixel Quotes", __('Pixel Quotes Options', 'quote-pixel'), current_user_can('administrator'),"pixel-quotes", "pixel_admin_page");
	add_action('admin_init', 'pixelquote_admin_style');
	
}
//set css file
function pixelquote_admin_style() {
	wp_enqueue_style("pixelquote_admin_style", plugins_url( 'pixel-random-quotes-and-images/admin-style.css' , dirname(__FILE__) ), false, "1.0", "all");  
}
//switcher or main function
function pixel_admin_page(){
	pixel_install_check();//it has quotes in wp_options table? if not, it is create 3 quotes
	pixel_quote_title();//display the title on admin page
	//modify process for uploaded content
	if (isset($_REQUEST['upload'])){
		if (pixel_quotes_upload_form_datas() == true){//required field is not null?
			pixel_quote_upload();//upload process
			pixel_all_right(__('The new quote is uploaded', 'quote-pixel'));//ok window
		}
	}
	//modify process for changed content
	elseif (isset($_REQUEST['save'])){
		pixel_quote_save();//save process
		pixel_all_right(__('The modified quotes is saved', 'quote-pixel'));//ok window
	}
	//modify process for less content
	elseif (isset($_REQUEST['delete'])){
		if (pixel_quote_is_checked_correct() == true){//there is checked?
			$newarray = pixel_quote_delete_mark();//delte process part 1
			pixel_are_you_sure_window(__('Do you really want to delete the marked quotes?', 'quote-pixel'), $newarray);//need confirm window
		}
	}
	//modify process for less content when confirmed by user
	elseif (isset($_REQUEST['yes'])){
		pixel_quote_delete_confirmed();//delete process part 2
		pixel_all_right(__('The marked quotes is deleted', 'quote-pixel'));//ok window
	}
	pixel_quotes_new();//display upload form
	pixel_quotes_list();//display the quotes list for modify
}
//this function create the default quotes if there is no quotes
function pixel_install_check(){
	global $pixelquotes;
	if ($pixelquotes[0] == ''){
		update_option('pixelquotes_settings', pixel_install_options());//upload default quotes
		$pixelquotes = pixel_get_options();
	}
}
//default quotes
function pixel_install_options(){
	$first[0] = __('If you don\'t know where you are going, any road will get you there.', 'quote-pixel');
	$first[1] = __('Lewis Carroll', 'quote-pixel');
	$second[0] = 'http://images2.wikia.nocookie.net/__cb20090601055761/uncyclopedia/images/b/b0/Xiuhcoatl.png';
	$second[1] = 'The Holy Fire Serpent Xiuhcoatl';
	$third[0] = __('When you are courting a nice girl an hour, seems like a second. When you sit on a red hot cinder a second, seems like an hour. That\'s reltivity.', 'quote-pixel');
	$third[1] = __('Albert Einstein', 'quote-pixel');
	$options = array(
		array($first[0],'http://belicza.com/wordpress',$first[1]),
		array($second[0],'http://uncyclopedia.wikia.com/wiki/File:Xiuhcoatl.png#file',$second[1]),
		array($third[0],'http://belicza.com/wordpress',$third[1])
	);
	return $options;
}
//is there checked fields for delete?
function pixel_quote_is_checked_correct(){
	global $pixelquotes;
	$max = count($pixelquotes);//number of quotes
	$checked = false;
	for ($i = 0; $i < $max; $i++){
		if (isset($_REQUEST['pix_delete_'.$i.''])){//only number chars allowed in this strings
			if ( preg_match( "/[^0-9.]/", $_REQUEST['pix_delete_'.$i.''] ) ){
				return false; //no-number, modified html by a user
			}
			$checked = true;//there is all number and there is some checked field, so ok
		}
	}
	if ($checked == true){
		return true; //all right
	}
	return false; //not checked, fake post
}
//for empty upload post
function pixel_quotes_upload_form_datas(){
	global $pixel_new_upload;
	if ($_REQUEST['pix_quote'] == ''){
		$pixel_new_upload['error'] = "<br /><span>" . __('The Quote field is required', 'quote-pixel') . "</span>";
		$pixel_new_upload['quote'] = $_REQUEST['pix_quote'];
		$pixel_new_upload['author'] = $_REQUEST['pix_author'];
		$pixel_new_upload['link'] = $_REQUEST['pix_link'];
		return false;//required field is empty
	}
	return true;//all right
}
//before upload
function pixel_quote_upload(){
	global $pixelquotes;
	$newid = count($pixelquotes);//max number for extended array
	$pixelquotes[$newid][0] = balanceTags($_REQUEST['pix_quote']);
	$pixelquotes[$newid][1] = esc_attr($_REQUEST['pix_link']);
	$pixelquotes[$newid][2] = esc_attr($_REQUEST['pix_author']);
	update_option('pixelquotes_settings', $pixelquotes);//upload action, upload the full array
}

function pixel_quote_save(){
	global $pixelquotes;
	$max = count($pixelquotes);
	for ($i = 0; $i < $max; $i++){
		$pixelquotes[$i][0] = balanceTags($_REQUEST['pix_quote_'.$i.'']);
		$pixelquotes[$i][1] = esc_attr($_REQUEST['pix_link_'.$i.'']);
		$pixelquotes[$i][2] = esc_attr($_REQUEST['pix_author_'.$i.'']);
	}
	update_option('pixelquotes_settings', $pixelquotes);
}
//which quotes will be deleted?
function pixel_quote_delete_mark(){
	global $pixelquotes;
	$max = count($pixelquotes);//array size
	//there is two array. the old array is bigger array, and the new array is the lesser
	$support_i = 0;
	$support_array;
	for ($i = 0; $i < $max; $i++){
		if (!isset($_REQUEST['pix_delete_'.$i.''])){
			$support_array[$support_i][0] = balanceTags($_REQUEST['pix_quote_'.$i.'']);
			$support_array[$support_i][1] = esc_attr($_REQUEST['pix_link_'.$i.'']);
			$support_array[$support_i][2] = esc_attr($_REQUEST['pix_author_'.$i.'']);
			$support_i++;
		}
	}
	return $support_array;
}
//if user really want to delete
function pixel_quote_delete_confirmed(){
	global $pixelquotes;
	$pixelquotes = unserialize(base64_decode($_REQUEST['postedvalues']));//decode for correct array, becouse the array is crushed in html form
	update_option('pixelquotes_settings', $pixelquotes);//update (delete action)
}
//display plugin's admin page title
function pixel_quote_title(){
	?><table class="qutoes-table pixel_title">
		<tr>
			<th><?php _e('Pixel Quote Plugin', 'quote-pixel') ?></th>
		</tr>
		<tr>
			<td><?php _e('This quotes with links and authors will be displayed in Widget at random.', 'quote-pixel') ?></td>
		</tr>
	</table><?php
}
//confirm any window
function pixel_are_you_sure_window($title, $values){
	?><form action="" method="post">
		<table class="qutoes-table pixel_alert">
			<tr>
				<th><?php echo $title ?></th>
			</tr>
			<tr>
				<td>
					<input id="yes" name="yes" type="submit" value="<?php _e('Yes','quote-pixel') ?>" />
					<input id="no" name="no" type="submit" value="<?php _e('No','quote-pixel') ?>" />
					<input type="hidden" name="postedvalues" value="<?php echo base64_encode(serialize($values)) ?>" />
				</td>
			</tr>
		</table>
	</form><?php
}
//ok window
function pixel_all_right($title){
	?><table class="qutoes-table pixel_ok">
		<tr>
			<th><?php echo $title ?></th>
		</tr>
	</table><?php
}
//upload form
function pixel_quotes_new(){
	global $pixel_new_upload;
	?><form action="" method="post">
		<table class="qutoes-table">
			<tr>
				<th><?php _e('Quote', 'quote-pixel') ?></th>
				<th><?php _e('Author', 'quote-pixel') ?></th>
				<th><?php _e('Link', 'quote-pixel') ?></th>
			</tr>
			<tr class="pix_line">
				<td class="pix_input">
					<textarea name="pix_quote" id="pix_quote" cols="70" rows="2"><?php echo $pixel_new_upload['quote'] ?></textarea>
					<?php echo $pixel_new_upload['error'] ?>
				</td>
				<td class="pix_input">
					<input name="pix_author" id="pix_author" type="text" value="<?php echo $pixel_new_upload['author'] ?>" />
				</td>
				<td class="pix_input">
					<input name="pix_link" id="pix_link" type="text" value="<?php echo $pixel_new_upload['link'] ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<input id="upload" name="upload" type="submit" value="<?php _e('Upload new quote','quote-pixel') ?>" />
				</td><td></td><td></td>
			</tr>
		</table>
	</form><br /><br /><br /><?php
}
//quote list for modify
function pixel_quotes_list(){
	global $pixelquotes;
	?><form action="" method="post">
		<table class="qutoes-table">
			<tr>
				<td>
					<input id="save" name="save" type="submit" value="<?php _e('Save all quotes','quote-pixel') ?>" />
					<input id="delete" name="delete" type="submit" value="<?php _e('Delete marked quotes','quote-pixel') ?>" />
				</td><td></td><td></td><td></td>
			</tr>
			<tr>
				<th><?php _e('Quote', 'quote-pixel') ?></th>
				<th><?php _e('Author', 'quote-pixel') ?></th>
				<th><?php _e('Link', 'quote-pixel') ?></th>
				<th><?php _e('Delete', 'quote-pixel') ?></th>
			</tr><?php
	//one line
	$max = count($pixelquotes);	
	for ($i = 0; $i < $max; $i++){
		?>
			<tr class="pix_line">
				<td class="pix_input">
					<textarea name="pix_quote_<?php echo $i ?>" id="pix_quote_<?php echo $i ?>" cols="70" rows="2"><?php echo stripslashes($pixelquotes[$i][0]) ?></textarea>
				</td>
				<td class="pix_input">
					<input name="pix_author_<?php echo $i ?>" id="pix_author_<?php echo $i ?>" type="text" value="<?php echo stripslashes($pixelquotes[$i][2]) ?>" />
				</td>
				<td class="pix_input">
					<input name="pix_link_<?php echo $i ?>" id="pix_link_<?php echo $i ?>" type="text" value="<?php echo stripslashes($pixelquotes[$i][1]) ?>" />
				</td class="pix_input">
				<td>
					<input name="pix_delete_<?php echo $i ?>" id="pix_delete_<?php echo $i ?>" type="checkbox" value="<?php echo $i ?>" />
				</td>
			</tr>
		<?php
	}
			?><tr>
				<td>
					<input id="save" name="save" type="submit" value="<?php _e('Save all quotes','quote-pixel') ?>" />
					<input id="delete" name="delete" type="submit" value="<?php _e('Delete marked quotes','quote-pixel') ?>" />
				</td><td></td><td></td><td></td>
			</tr>
		</table>
	</form><?php
}
?>