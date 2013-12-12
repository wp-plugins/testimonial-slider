<?php // Hook for adding admin menus
if ( is_admin() ){ // admin actions
  add_action('admin_menu', 'testimonial_slider_settings');
  add_action( 'admin_init', 'register_testimonial_settings' ); 
} 

//Create Set & Export Settings
function testimonial_process_set_requests(){
	global $default_testimonial_slider_settings;
	$scounter=get_option('testimonial_slider_scounter');
	
	$cntr='';
	if(isset($_GET['scounter'])) $cntr = $_GET['scounter'];
	
	if(isset($_POST['create_set'])){
		if ($_POST['create_set']=='Create New Settings Set') {
		  $scounter++;
		  update_option('testimonial_slider_scounter',$scounter);
		  $options='testimonial_slider_options'.$scounter;
		  update_option($options,$default_testimonial_slider_settings);
		  $current_url = admin_url('admin.php?page=testimonial-slider-settings');
		  $current_url = add_query_arg('scounter',$scounter,$current_url);
		  wp_redirect( $current_url );
		  exit;
		}
	}

	//Export Settings
	if(isset($_POST['export'])){
		if ($_POST['export']=='Export') {
			@ob_end_clean();
			
			// required for IE, otherwise Content-Disposition may be ignored
			if(ini_get('zlib.output_compression'))
			ini_set('zlib.output_compression', 'Off');
			
			header('Content-Type: ' . "text/x-csv");
			header('Content-Disposition: attachment; filename="testimonial-settings-set-'.$cntr.'.csv"');
			header("Content-Transfer-Encoding: binary");
			header('Accept-Ranges: bytes');

			/* The three lines below basically make the
			download non-cacheable */
			header("Cache-control: private");
			header('Pragma: private');
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

			$exportTXT='';$i=0;
			$slider_options='testimonial_slider_options'.$cntr;
			$slider_curr=get_option($slider_options);
			foreach($slider_curr as $key=>$value){
				if($i>0) $exportTXT.="\n";
				if(!is_array($value)){
					$exportTXT.=$key.",".$value;
				}
				else {
					$exportTXT.=$key.',';
					$j=0;
					if($value) {
						foreach($value as $v){
							if($j>0) $exportTXT.="|";
							$exportTXT.=$v;
							$j++;
						}
					}
				}
				$i++;
			}
			$exportTXT.="\n";
			$exportTXT.="slider_name,testimonial";
			print($exportTXT); 
			exit();
		}
	}	
}
add_action('load-testimonial-slider_page_testimonial-slider-settings','testimonial_process_set_requests');

// function for adding settings page to wp-admin
function testimonial_slider_settings() {
    // Add a new submenu under Options:
	add_menu_page( 'Testimonial Slider', 'Testimonial Slider', 'manage_options','testimonial-slider-admin', 'testimonial_slider_create_multiple_sliders', testimonial_slider_plugin_url( 'images/testimonial_slider_icon.gif' ) );
	add_submenu_page('testimonial-slider-admin', 'Testimonial Sliders', 'Sliders', 'manage_options', 'testimonial-slider-admin', 'testimonial_slider_create_multiple_sliders');
	add_submenu_page('testimonial-slider-admin', 'Testimonial Slider Settings', 'Settings', 'manage_options', 'testimonial-slider-settings', 'testimonial_slider_settings_page');
}
require_once (dirname (__FILE__) . '/sliders.php');
// This function displays the page content for the Testimonial Slider Options submenu
function testimonial_slider_settings_page() {
global $testimonial_slider,$default_testimonial_slider_settings;
$scounter=get_option('testimonial_slider_scounter');
if (isset($_GET['scounter']))$cntr = $_GET['scounter'];
else $cntr = '';

$new_settings_msg=$imported_settings_message='';

//Reset Settings
if (isset ($_POST['testimonial_reset_settings_submit'])) {
	if ( $_POST['testimonial_reset_settings']!='n' ) {
	  $testimonial_reset_settings=$_POST['testimonial_reset_settings'];
	  $options='testimonial_slider_options'.$cntr;
	  $optionsvalue=get_option($options);
	  if( $testimonial_reset_settings == 'g' ){
		$new_settings_value=$default_testimonial_slider_settings;
		$new_settings_value['setname']=$optionsvalue['setname'];
		update_option($options,$new_settings_value);
	  }
	  else{
		if( $testimonial_reset_settings == '1' ){
			$new_settings_value=get_option('testimonial_slider_options');
			$new_settings_value['setname']=$optionsvalue['setname'];
			update_option($options,	$new_settings_value );
		}
		else{
			$new_option_name='testimonial_slider_options'.$testimonial_reset_settings;
			$new_settings_value=get_option($new_option_name);
			$new_settings_value['setname']=$optionsvalue['setname'];
			update_option($options,	$new_settings_value );
		}
	  }
	}
}

//Import Settings
if (isset ($_POST['import'])) {
	if ($_POST['import']=='Import') {
		global $wpdb;
		$csv_mimetypes = array('text/csv','text/plain','application/csv','text/comma-separated-values','application/excel',
	'application/vnd.ms-excel','application/vnd.msexcel','text/anytext','application/octet-stream','application/txt');
		if ($_FILES['settings_file']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['settings_file']['tmp_name']) && in_array($_FILES['settings_file']['type'], $csv_mimetypes) ) { 
			$imported_settings=file_get_contents($_FILES['settings_file']['tmp_name']); 
			$settings_arr=explode("\n",$imported_settings);
			$slider_settings=array();
			foreach($settings_arr as $settings_field){
				$s=explode(',',$settings_field);
				$inner=explode('|',$s[1]);
				if(count($inner)>1)	$slider_settings[$s[0]]=$inner;
				else $slider_settings[$s[0]]=$s[1];
			}
			$options='testimonial_slider_options'.$cntr;
			
			if( $slider_settings['slider_name'] == 'testimonial' )	{
				update_option($options,$slider_settings);
				$new_settings_msg='<div id="message" class="updated fade" style="clear:left;"><h3>'.__('Settings imported successfully ','testimonial-slider').'</h3></div>';
				$imported_settings_message='<div style="clear:left;color:#006E2E;"><h3>'.__('Settings imported successfully ','testimonial-slider').'</h3></div>';
			}
			else {
				$new_settings_msg=$imported_settings_message='<div id="message" class="error fade" style="clear:left;"><h3>'.__('Settings imported do not match to Testimonial Slider Settings. Please check the file.','testimonial-slider').'</h3></div>';
				$imported_settings_message='<div style="clear:left;color:#ff0000;"><h3>'.__('Settings imported do not match to Testimonial Slider Settings. Please check the file.','testimonial-slider').'</h3></div>';
			}
		}
		else{
			$new_settings_msg=$imported_settings_message='<div id="message" class="error fade" style="clear:left;"><h3>'.__('Error in File, Settings not imported. Please check the file being imported. ','testimonial-slider').'</h3></div>';
			$imported_settings_message='<div style="clear:left;color:#ff0000;"><h3>'.__('Error in File, Settings not imported. Please check the file being imported. ','testimonial-slider').'</h3></div>';
		}
	}
}

//Delete Set
if (isset ($_POST['delete_set'])) {
	if ($_POST['delete_set']=='Delete this Set' and isset($cntr) and !empty($cntr)) {
	  $options='testimonial_slider_options'.$cntr;
	  delete_option($options);
	  $cntr='';
	}
}

$group='testimonial-slider-group'.$cntr;
$testimonial_slider_options='testimonial_slider_options'.$cntr;
$testimonial_slider_curr=get_option($testimonial_slider_options);
if(!isset($cntr) or empty($cntr)){$curr = 'Default';}
else{$curr = $cntr;}
foreach($default_testimonial_slider_settings as $key=>$value){
	if(!isset($testimonial_slider_curr[$key])) $testimonial_slider_curr[$key]='';
}
?>

<div class="wrap" style="clear:both;">
<h2 style="float:left;"><?php _e('Testimonial Slider Settings ','testimonial-slider'); echo $curr; ?> </h2>
<form style="float:left;margin:10px 20px" action="" method="post">
<?php if(isset($cntr) and !empty($cntr)){ ?>
<input type="submit" class="button-primary" value="Delete this Set" name="delete_set"  onclick="return confirmSettingsDelete()" />
<?php } ?>
</form>
<div class="svilla_cl"></div>
<?php echo $new_settings_msg;?>
<?php 
if ($testimonial_slider_curr['disable_preview'] != '1'){
?>
<div id="settings_preview"><h2 style="clear:left;"><?php _e('Preview','testimonial-slider'); ?></h2> 
<?php 
if ($testimonial_slider_curr['preview'] == "0")
	get_testimonial_slider($testimonial_slider_curr['slider_id'],$cntr);
elseif($testimonial_slider_curr['preview'] == "1")
	get_testimonial_slider_category($testimonial_slider_curr['catg_slug'],$cntr);
else
	get_testimonial_slider_recent($cntr);
?></div>
<?php } ?>

<?php echo $new_settings_msg;?>

<div id="testimonial_settings" style="float:left;width:70%;">
<form method="post" action="options.php" id="testimonial_slider_form">
<?php settings_fields($group); ?>

<?php
if(!isset($cntr) or empty($cntr)){}
else{?>
	<table class="form-table">
		<tr valign="top">
		<th scope="row"><h3><?php _e('Setting Set Name','testimonial-slider'); ?></h3></th>
		<td><h3><input type="text" name="<?php echo $testimonial_slider_options;?>[setname]" id="testimonial_slider_setname" class="regular-text" value="<?php echo $testimonial_slider_curr['setname']; ?>" /></h3></td>
		</tr>
	</table>
<?php }
?>
<div id="slider_tabs">
        <ul class="ui-tabs">
            <li style="font-weight:bold;font-size:12px;"><a href="#basic">Basic Settings</a></li>
            <li style="font-weight:bold;font-size:12px;"><a href="#slider_content">Slider Content</a></li>
			<li style="font-weight:bold;font-size:12px;"><a href="#slider_nav">Navigation Settings</a></li>
			<li style="font-weight:bold;font-size:12px;"><a href="#responsive">Responsiveness</a></li>
			<li style="font-weight:bold;font-size:12px;"><a href="#preview">Preview Settings</a></li>
			<li style="font-weight:bold;font-size:12px;"><a href="#cssvalues">Generated CSS</a></li>
        </ul>

<div id="basic">
<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Basic Settings','testimonial-slider'); ?></h2> 
<p><?php _e('Customize the looks of the Slider box wrapping the content slides from here','testimonial-slider'); ?></p> 

<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Type of Slider','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[type]" >
<option value="0" <?php if ($testimonial_slider_curr['type'] == "0"){ echo "selected";}?> ><?php _e('Slides Infinitely','testimonial-slider'); ?></option>
<option value="1" <?php if ($testimonial_slider_curr['type'] == "1"){ echo "selected";}?> ><?php _e('Stops when either end is reached','testimonial-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Slide Transition Effect','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[transition]" >
<option value="scroll" <?php if ($testimonial_slider_curr['transition'] == "scroll"){ echo "selected";}?> ><?php _e('Scroll Horizontally','testimonial-slider'); ?></option>
<option value="fade" <?php if ($testimonial_slider_curr['transition'] == "fade"){ echo "selected";}?> ><?php _e('Fade','testimonial-slider'); ?></option>
<option value="crossfade" <?php if ($testimonial_slider_curr['transition'] == "crossfade"){ echo "selected";}?> ><?php _e('Cross Fade','testimonial-slider'); ?></option>
<option value="cover" <?php if ($testimonial_slider_curr['transition'] == "cover"){ echo "selected";}?> ><?php _e('Cover','testimonial-slider'); ?></option>
<option value="uncover" <?php if ($testimonial_slider_curr['transition'] == "uncover"){ echo "selected";}?> ><?php _e('Uncover','testimonial-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Slide Easing Effect','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[easing]" >
<option value="swing" <?php if ($testimonial_slider_curr['easing'] == "swing"){ echo "selected";}?> ><?php _e('swing','testimonial-slider'); ?></option>
<option value="easeInQuad" <?php if ($testimonial_slider_curr['easing'] == "easeInQuad"){ echo "selected";}?> ><?php _e('easeInQuad','testimonial-slider'); ?></option>
<option value="easeOutQuad" <?php if ($testimonial_slider_curr['easing'] == "easeOutQuad"){ echo "selected";}?> ><?php _e('easeOutQuad','testimonial-slider'); ?></option>
<option value="easeInOutQuad" <?php if ($testimonial_slider_curr['easing'] == "easeInOutQuad"){ echo "selected";}?> ><?php _e('easeInOutQuad','testimonial-slider'); ?></option>
<option value="easeInCubic" <?php if ($testimonial_slider_curr['easing'] == "easeInCubic"){ echo "selected";}?> ><?php _e('easeInCubic','testimonial-slider'); ?></option>
<option value="easeOutCubic" <?php if ($testimonial_slider_curr['easing'] == "easeOutCubic"){ echo "selected";}?> ><?php _e('easeOutCubic','testimonial-slider'); ?></option>
<option value="easeInOutCubic" <?php if ($testimonial_slider_curr['easing'] == "easeInOutCubic"){ echo "selected";}?> ><?php _e('easeInOutCubic','testimonial-slider'); ?></option>
<option value="easeInQuart" <?php if ($testimonial_slider_curr['easing'] == "easeInQuart"){ echo "selected";}?> ><?php _e('easeInQuart','testimonial-slider'); ?></option>
<option value="easeOutQuart" <?php if ($testimonial_slider_curr['easing'] == "easeOutQuart"){ echo "selected";}?> ><?php _e('easeOutQuart','testimonial-slider'); ?></option>
<option value="easeInOutQuart" <?php if ($testimonial_slider_curr['easing'] == "easeInOutQuart"){ echo "selected";}?> ><?php _e('easeInOutQuart','testimonial-slider'); ?></option>
<option value="easeInQuint" <?php if ($testimonial_slider_curr['easing'] == "easeInQuint"){ echo "selected";}?> ><?php _e('easeInQuint','testimonial-slider'); ?></option>
<option value="easeOutQuint" <?php if ($testimonial_slider_curr['easing'] == "easeOutQuint"){ echo "selected";}?> ><?php _e('easeOutQuint','testimonial-slider'); ?></option>
<option value="easeInOutQuint" <?php if ($testimonial_slider_curr['easing'] == "easeInOutQuint"){ echo "selected";}?> ><?php _e('easeInOutQuint','testimonial-slider'); ?></option>
<option value="easeInSine" <?php if ($testimonial_slider_curr['easing'] == "easeInSine"){ echo "selected";}?> ><?php _e('easeInSine','testimonial-slider'); ?></option>
<option value="easeOutSine" <?php if ($testimonial_slider_curr['easing'] == "easeOutSine"){ echo "selected";}?> ><?php _e('easeOutSine','testimonial-slider'); ?></option>
<option value="easeInOutSine" <?php if ($testimonial_slider_curr['easing'] == "easeInOutSine"){ echo "selected";}?> ><?php _e('easeInOutSine','testimonial-slider'); ?></option>
<option value="easeInExpo" <?php if ($testimonial_slider_curr['easing'] == "easeInExpo"){ echo "selected";}?> ><?php _e('easeInExpo','testimonial-slider'); ?></option>
<option value="easeOutExpo" <?php if ($testimonial_slider_curr['easing'] == "easeOutExpo"){ echo "selected";}?> ><?php _e('easeOutExpo','testimonial-slider'); ?></option>
<option value="easeInOutExpo" <?php if ($testimonial_slider_curr['easing'] == "easeInOutExpo"){ echo "selected";}?> ><?php _e('easeInOutExpo','testimonial-slider'); ?></option>
<option value="easeInCirc" <?php if ($testimonial_slider_curr['easing'] == "easeInCirc"){ echo "selected";}?> ><?php _e('easeInCirc','testimonial-slider'); ?></option>
<option value="easeOutCirc" <?php if ($testimonial_slider_curr['easing'] == "easeOutCirc"){ echo "selected";}?> ><?php _e('easeOutCirc','testimonial-slider'); ?></option>
<option value="easeInOutCirc" <?php if ($testimonial_slider_curr['easing'] == "easeInOutCirc"){ echo "selected";}?> ><?php _e('easeInOutCirc','testimonial-slider'); ?></option>
<option value="easeInElastic" <?php if ($testimonial_slider_curr['easing'] == "easeInElastic"){ echo "selected";}?> ><?php _e('easeInElastic','testimonial-slider'); ?></option>
<option value="easeOutElastic" <?php if ($testimonial_slider_curr['easing'] == "easeOutElastic"){ echo "selected";}?> ><?php _e('easeOutElastic','testimonial-slider'); ?></option>
<option value="easeInOutElastic" <?php if ($testimonial_slider_curr['easing'] == "easeInOutElastic"){ echo "selected";}?> ><?php _e('easeInOutElastic','testimonial-slider'); ?></option>
<option value="easeInBack" <?php if ($testimonial_slider_curr['easing'] == "easeInBack"){ echo "selected";}?> ><?php _e('easeInBack','testimonial-slider'); ?></option>
<option value="easeOutBack" <?php if ($testimonial_slider_curr['easing'] == "easeOutBack"){ echo "selected";}?> ><?php _e('easeOutBack','testimonial-slider'); ?></option>
<option value="easeInOutBack" <?php if ($testimonial_slider_curr['easing'] == "easeInOutBack"){ echo "selected";}?> ><?php _e('easeInOutBack','testimonial-slider'); ?></option>
<option value="easeInBounce" <?php if ($testimonial_slider_curr['easing'] == "easeInBounce"){ echo "selected";}?> ><?php _e('easeInBounce','testimonial-slider'); ?></option>
<option value="easeOutBounce" <?php if ($testimonial_slider_curr['easing'] == "easeOutBounce"){ echo "selected";}?> ><?php _e('easeOutBounce','testimonial-slider'); ?></option>
<option value="easeInOutBounce" <?php if ($testimonial_slider_curr['easing'] == "easeInOutBounce"){ echo "selected";}?> ><?php _e('easeInOutBounce','testimonial-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Speed of Transition','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[speed]" id="testimonial_slider_speed" class="small-text" value="<?php echo $testimonial_slider_curr['speed']; ?>" />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('The duration of Slide Animation in milliseconds. Lower value indicates fast animation. Enter numeric values like 5 or 7.','testimonial-slider'); ?>
	</div>
</span>
<br /><small style="color:#FF0000"><?php _e(' (IMP!! Enter value > 0)','testimonial-slider'); ?></small>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Disable Autosliding','testimonial-slider'); ?></th>
<td><input name="<?php echo $testimonial_slider_options;?>[disable_autostep]" type="checkbox" value="1" <?php checked('1', $testimonial_slider_curr['disable_autostep']); ?>  />
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Time between Transitions','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[time]" id="testimonial_slider_time" class="small-text" value="<?php echo $testimonial_slider_curr['time']; ?>" />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('Enter number of secs you want the slider to stop before sliding to next slide. Valid only in case auto-sliding is enabled','testimonial-slider'); ?>
	</div>
</span>
<br /><small style="color:#FF0000"><?php _e(' (IMP!! Enter value > 0)','testimonial-slider'); ?></small>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Number of Testimonials in the Testimonial Slider','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[no_posts]" id="testimonial_slider_no_posts" class="small-text" value="<?php echo $testimonial_slider_curr['no_posts']; ?>" /></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Number of Items Visible in One Set','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[visible]" id="testimonial_slider_visible" class="small-text" value="<?php echo $testimonial_slider_curr['visible']; ?>" /><small><?php _e('(Caution: Do not enter 0)','testimonial-slider'); ?></small></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Number of Items To Scroll while Sliding','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[scroll]" id="testimonial_slider_scroll" class="small-text" value="<?php echo $testimonial_slider_curr['scroll']; ?>" /><small><?php _e('(Caution: Do not enter 0)','testimonial-slider'); ?></small></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Complete Slider Width','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[width]" id="testimonial_slider_width" class="small-text" value="<?php echo $testimonial_slider_curr['width']; ?>" />&nbsp;<?php _e('px','testimonial-slider'); ?><small><?php _e('(If set to 0, will take the container\'s width)','testimonial-slider'); ?></small></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Slide (Item) Width','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[iwidth]" id="testimonial_slider_iwidth" class="small-text" value="<?php echo $testimonial_slider_curr['iwidth']; ?>" />&nbsp;<?php _e('px','testimonial-slider'); ?><small style="color:#FF0000"><?php _e(' (IMP!! Enter numeric value > 0)','testimonial-slider'); ?></small></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Slide (Item) Height','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[height]" id="testimonial_slider_height" class="small-text" value="<?php echo $testimonial_slider_curr['height']; ?>" />&nbsp;<?php _e('px','testimonial-slider'); ?><small style="color:#FF0000"><?php _e(' (IMP!! Enter numeric value > 0)','testimonial-slider'); ?></small></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Slide Background Color','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[bg_color]" id="color_value_1" value="<?php echo $testimonial_slider_curr['bg_color']; ?>" />&nbsp; <img id="color_picker_1" src="<?php echo testimonial_slider_plugin_url( 'images/color_picker.png' ); ?>" alt="<?php _e('Pick the color of your choice','testimonial-slider'); ?>" /><div class="color-picker-wrap" id="colorbox_1"></div> &nbsp; &nbsp; &nbsp; 
<label for="testimonial_slider_bg"><input name="<?php echo $testimonial_slider_options;?>[bg]" type="checkbox" id="testimonial_slider_bg" value="1" <?php checked('1', $testimonial_slider_curr['bg']); ?>  /><?php _e(' Use Transparent Background','testimonial-slider'); ?></label> </td>
</tr>
 
<tr valign="top">
<th scope="row"><?php _e('Slide Border Thickness','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[border]" id="testimonial_slider_border" class="small-text" value="<?php echo $testimonial_slider_curr['border']; ?>" />&nbsp;<?php _e('px (put 0 if no border is required)','testimonial-slider'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Slide Border Color','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[brcolor]" id="color_value_6" value="<?php echo $testimonial_slider_curr['brcolor']; ?>" />&nbsp; <img id="color_picker_6" src="<?php echo testimonial_slider_plugin_url( 'images/color_picker.png' ); ?>" alt="<?php _e('Pick the color of your choice','testimonial-slider'); ?>" /><div class="color-picker-wrap" id="colorbox_6"></div></td>
</tr>

</table>
<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Miscellaneous','testimonial-slider'); ?></h2> 

<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Continue Reading Text','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[more]" class="regular-text" value="<?php echo $testimonial_slider_curr['more']; ?>" /></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Slide Link (\'a\' element) attributes  ','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[a_attr]" class="regular-text code" value="<?php echo htmlentities( $testimonial_slider_curr['a_attr'] , ENT_QUOTES); ?>" />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('eg. target="_blank" rel="external nofollow"','testimonial-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Randomize Slides in Slider','testimonial-slider'); ?></th>
<td><input name="<?php echo $testimonial_slider_options;?>[rand]" type="checkbox" value="1" <?php checked('1', $testimonial_slider_curr['rand']); ?>  />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('check this if you want the testimonials added to appear in random order.','testimonial-slider'); ?>
	</div>
</span>
</td>
</tr>

<?php if(!isset($cntr) or empty($cntr)){?>

<tr valign="top">
<th scope="row"><?php _e('Minimum User Level to add Testimonials to the Slider','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[user_level]" >
<option value="manage_options" <?php if ($testimonial_slider_curr['user_level'] == "manage_options"){ echo "selected";}?> ><?php _e('Administrator','testimonial-slider'); ?></option>
<option value="edit_others_posts" <?php if ($testimonial_slider_curr['user_level'] == "edit_others_posts"){ echo "selected";}?> ><?php _e('Editor and Admininstrator','testimonial-slider'); ?></option>
<option value="publish_posts" <?php if ($testimonial_slider_curr['user_level'] == "publish_posts"){ echo "selected";}?> ><?php _e('Author, Editor and Admininstrator','testimonial-slider'); ?></option>
<option value="edit_posts" <?php if ($testimonial_slider_curr['user_level'] == "edit_posts"){ echo "selected";}?> ><?php _e('Contributor, Author, Editor and Admininstrator','testimonial-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Text to display in the JavaScript disabled browser','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[noscript]" class="regular-text code" value="<?php echo $testimonial_slider_curr['noscript']; ?>" /></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Add Shortcode Support','testimonial-slider'); ?></th>
<td><input name="<?php echo $testimonial_slider_options;?>[shortcode]" type="checkbox" value="1" <?php checked('1', $testimonial_slider_curr['shortcode']); ?>  />&nbsp;<?php _e('check this if you want to use Testimonial Slider Shortcode i.e [testimonialslider]','testimonial-slider'); ?></td>
</tr>
<?php } ?>

<tr valign="top">
<th scope="row"><?php _e('Testimonial Slider Skin','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[stylesheet]" >
<?php 
$directory = TESTIMONIAL_SLIDER_CSS_DIR;
if ($handle = opendir($directory)) {
    while (false !== ($file = readdir($handle))) { 
     if($file != '.' and $file != '..') { ?>
      <option value="<?php echo $file;?>" <?php if ($testimonial_slider_curr['stylesheet'] == $file){ echo "selected";}?> ><?php echo $file;?></option>
 <?php  } }
    closedir($handle);
}
?>
</select>
</td>
</tr>

<?php if(!isset($cntr) or empty($cntr)){?>
<tr valign="top">
<th scope="row"><?php _e('Multiple Slider Feature','testimonial-slider'); ?></th>
<td><label for="testimonial_slider_multiple"> 
<input name="<?php echo $testimonial_slider_options;?>[multiple_sliders]" type="checkbox" id="testimonial_slider_multiple" value="1" <?php checked("1", $testimonial_slider_curr['multiple_sliders']); ?> /> 
 <?php _e('Grant Multiple Slider ability to Testimonial Slider','testimonial-slider'); ?></label></td>
</tr>
<?php } ?>

<tr valign="top">
<th scope="row"><?php _e('Enable FOUC','testimonial-slider'); ?></th>
<td><input name="<?php echo $testimonial_slider_options;?>[fouc]" type="checkbox" value="1" <?php checked('1', $testimonial_slider_curr['fouc']); ?>  />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('check this if you would not want to disable Flash of Unstyled Content in the slider when the page is loaded.','testimonial-slider'); ?>
	</div>
</span>
</td>
</tr>

<?php if(!isset($cntr) or empty($cntr)){?>

<tr valign="top">
<th scope="row"><?php _e('Custom Styles','testimonial-slider'); ?></th>
<td><textarea name="<?php echo $testimonial_slider_options;?>[css]"  rows="5" cols="50" class="regular-text code"><?php echo $testimonial_slider_curr['css']; ?></textarea>
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('custom css styles that you would want to be applied to the slider elements.','testimonial-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Show Promotionals on Admin Page','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[support]" >
<option value="1" <?php if ($testimonial_slider_curr['support'] == "1"){ echo "selected";}?> ><?php _e('Yes','testimonial-slider'); ?></option>
<option value="0" <?php if ($testimonial_slider_curr['support'] == "0"){ echo "selected";}?> ><?php _e('No','testimonial-slider'); ?></option>
</select>
</td>
</tr>
<?php } ?>

</table>
</div>
<?php do_action('testimonial_addon_settings',$cntr,$testimonial_slider_options,$testimonial_slider_curr);?>
</div> <!--Basic Tab Ends-->

<div id="slider_content">
<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Slider Title','testimonial-slider'); ?></h2> 
<p><?php _e('Customize the looks of the main title of the Slideshow from here','testimonial-slider'); ?></p> 
<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Default Title Text','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[title_text]" id="testimonial_slider_title_text" value="<?php echo htmlentities($testimonial_slider_curr['title_text'], ENT_QUOTES); ?>" /></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Pick Slider Title From','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[title_from]" >
<option value="0" <?php if ($testimonial_slider_curr['title_from'] == "0"){ echo "selected";}?> ><?php _e('Default Title Text','testimonial-slider'); ?></option>
<option value="1" <?php if ($testimonial_slider_curr['title_from'] == "1"){ echo "selected";}?> ><?php _e('Slider Name','testimonial-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[title_font]" id="testimonial_slider_title_font" >
<option value="Arial,Helvetica,sans-serif" <?php if ($testimonial_slider_curr['title_font'] == "Arial,Helvetica,sans-serif"){ echo "selected";}?> >Arial,Helvetica,sans-serif</option>
<option value="Verdana,Geneva,sans-serif" <?php if ($testimonial_slider_curr['title_font'] == "Verdana,Geneva,sans-serif"){ echo "selected";}?> >Verdana,Geneva,sans-serif</option>
<option value="Tahoma,Geneva,sans-serif" <?php if ($testimonial_slider_curr['title_font'] == "Tahoma,Geneva,sans-serif"){ echo "selected";}?> >Tahoma,Geneva,sans-serif</option>
<option value="Trebuchet MS,sans-serif" <?php if ($testimonial_slider_curr['title_font'] == "Trebuchet MS,sans-serif"){ echo "selected";}?> >Trebuchet MS,sans-serif</option>
<option value="'Century Gothic','Avant Garde',sans-serif" <?php if ($testimonial_slider_curr['title_font'] == "'Century Gothic','Avant Garde',sans-serif"){ echo "selected";}?> >'Century Gothic','Avant Garde',sans-serif</option>
<option value="'Arial Narrow',sans-serif" <?php if ($testimonial_slider_curr['title_font'] == "'Arial Narrow',sans-serif"){ echo "selected";}?> >'Arial Narrow',sans-serif</option>
<option value="'Arial Black',sans-serif" <?php if ($testimonial_slider_curr['title_font'] == "'Arial Black',sans-serif"){ echo "selected";}?> >'Arial Black',sans-serif</option>
<option value="'Gills Sans MT','Gills Sans',sans-serif" <?php if ($testimonial_slider_curr['title_font'] == "'Gills Sans MT','Gills Sans',sans-serif"){ echo "selected";} ?> >'Gills Sans MT','Gills Sans',sans-serif</option>
<option value="'Lucida Sans Unicode', 'Lucida Grand', sans-serif;" <?php if ($testimonial_slider_curr['title_font'] == "'Lucida Sans Unicode', 'Lucida Grand', sans-serif;"){ echo "selected";} ?> >'Lucida Sans Unicode', 'Lucida Grand', sans-serif;</option>
<option value="'Times New Roman',Times,serif" <?php if ($testimonial_slider_curr['title_font'] == "'Times New Roman',Times,serif"){ echo "selected";}?> >'Times New Roman',Times,serif</option>
<option value="Georgia,serif" <?php if ($testimonial_slider_curr['title_font'] == "Georgia,serif"){ echo "selected";}?> >Georgia,serif</option>
<option value="Garamond,serif" <?php if ($testimonial_slider_curr['title_font'] == "Garamond,serif"){ echo "selected";}?> >Garamond,serif</option>
<option value="'Century Schoolbook','New Century Schoolbook',serif" <?php if ($testimonial_slider_curr['title_font'] == "'Century Schoolbook','New Century Schoolbook',serif"){ echo "selected";}?> >'Century Schoolbook','New Century Schoolbook',serif</option>
<option value="'Bookman Old Style',Bookman,serif" <?php if ($testimonial_slider_curr['title_font'] == "'Bookman Old Style',Bookman,serif"){ echo "selected";}?> >'Bookman Old Style',Bookman,serif</option>
<option value="'Comic Sans MS',cursive" <?php if ($testimonial_slider_curr['title_font'] == "'Comic Sans MS',cursive"){ echo "selected";}?> >'Comic Sans MS',cursive</option>
<option value="'Courier New',Courier,monospace" <?php if ($testimonial_slider_curr['title_font'] == "'Courier New',Courier,monospace"){ echo "selected";}?> >'Courier New',Courier,monospace</option>
<option value="'Copperplate Gothic Bold',Copperplate,fantasy" <?php if ($testimonial_slider_curr['title_font'] == "'Copperplate Gothic Bold',Copperplate,fantasy"){ echo "selected";}?> >'Copperplate Gothic Bold',Copperplate,fantasy</option>
<option value="Impact,fantasy" <?php if ($testimonial_slider_curr['title_font'] == "Impact,fantasy"){ echo "selected";}?> >Impact,fantasy</option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Color','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[title_fcolor]" id="color_value_2" value="<?php echo $testimonial_slider_curr['title_fcolor']; ?>" />&nbsp; <img id="color_picker_2" src="<?php echo testimonial_slider_plugin_url( 'images/color_picker.png' ); ?>" alt="<?php _e('Pick the color of your choice','testimonial-slider'); ?>" /><div class="color-picker-wrap" id="colorbox_2"></div></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Size','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[title_fsize]" id="testimonial_slider_title_fsize" class="small-text" value="<?php echo $testimonial_slider_curr['title_fsize']; ?>" />&nbsp;<?php _e('px','testimonial-slider'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Style','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[title_fstyle]" id="testimonial_slider_title_fstyle" >
<option value="bold" <?php if ($testimonial_slider_curr['title_fstyle'] == "bold"){ echo "selected";}?> ><?php _e('Bold','testimonial-slider'); ?></option>
<option value="bold italic" <?php if ($testimonial_slider_curr['title_fstyle'] == "bold italic"){ echo "selected";}?> ><?php _e('Bold Italic','testimonial-slider'); ?></option>
<option value="italic" <?php if ($testimonial_slider_curr['title_fstyle'] == "italic"){ echo "selected";}?> ><?php _e('Italic','testimonial-slider'); ?></option>
<option value="normal" <?php if ($testimonial_slider_curr['title_fstyle'] == "normal"){ echo "selected";}?> ><?php _e('Normal','testimonial-slider'); ?></option>
</select>
</td>
</tr>
</table>
<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Gravtar/Customer Image','testimonial-slider'); ?></h2> 
<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Default Avatar URL','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[default_avatar]" class="regular-text code" value="<?php echo $testimonial_slider_curr['default_avatar']; ?>" /></td>
</tr>

<tr valign="top"> 
<th scope="row"><?php _e('Image Width','testimonial-slider'); ?></th> 
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[img_width]" class="small-text" value="<?php echo $testimonial_slider_curr['img_width']; ?>" />&nbsp;<?php _e('px','testimonial-slider'); ?> </td> 
</tr> 

<tr valign="top">
<th scope="row"><?php _e('Max. Image Height','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[img_height]" class="small-text" value="<?php echo $testimonial_slider_curr['img_height']; ?>" />&nbsp;<?php _e('px','testimonial-slider'); ?> </td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Border Thickness','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[img_border]" id="testimonial_slider_img_border" class="small-text" value="<?php echo $testimonial_slider_curr['img_border']; ?>" />&nbsp;<?php _e('px  (put 0 if no border is required)','testimonial-slider'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Border Color','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[img_brcolor]" id="color_value_4" value="<?php echo $testimonial_slider_curr['img_brcolor']; ?>" />&nbsp; <img id="color_picker_4" src="<?php echo testimonial_slider_plugin_url( 'images/color_picker.png' ); ?>" alt="<?php _e('Pick the color of your choice','testimonial-slider'); ?>" /><div class="color-picker-wrap" id="colorbox_4"></div></td>
</tr>

</table>
<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Customer\'s name','testimonial-slider'); ?></h2> 
<p><?php _e('Customize the Customer\'s Name field looks','testimonial-slider'); ?></p> 
<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Font','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[ptitle_font]" id="testimonial_slider_ptitle_font" >
<option value="Arial,Helvetica,sans-serif" <?php if ($testimonial_slider_curr['ptitle_font'] == "Arial,Helvetica,sans-serif"){ echo "selected";}?> >Arial,Helvetica,sans-serif</option>
<option value="Verdana,Geneva,sans-serif" <?php if ($testimonial_slider_curr['ptitle_font'] == "Verdana,Geneva,sans-serif"){ echo "selected";}?> >Verdana,Geneva,sans-serif</option>
<option value="Tahoma,Geneva,sans-serif" <?php if ($testimonial_slider_curr['ptitle_font'] == "Tahoma,Geneva,sans-serif"){ echo "selected";}?> >Tahoma,Geneva,sans-serif</option>
<option value="Trebuchet MS,sans-serif" <?php if ($testimonial_slider_curr['ptitle_font'] == "Trebuchet MS,sans-serif"){ echo "selected";}?> >Trebuchet MS,sans-serif</option>
<option value="'Century Gothic','Avant Garde',sans-serif" <?php if ($testimonial_slider_curr['ptitle_font'] == "'Century Gothic','Avant Garde',sans-serif"){ echo "selected";}?> >'Century Gothic','Avant Garde',sans-serif</option>
<option value="'Arial Narrow',sans-serif" <?php if ($testimonial_slider_curr['ptitle_font'] == "'Arial Narrow',sans-serif"){ echo "selected";}?> >'Arial Narrow',sans-serif</option>
<option value="'Arial Black',sans-serif" <?php if ($testimonial_slider_curr['ptitle_font'] == "'Arial Black',sans-serif"){ echo "selected";}?> >'Arial Black',sans-serif</option>
<option value="'Gills Sans MT','Gills Sans',sans-serif" <?php if ($testimonial_slider_curr['ptitle_font'] == "'Gills Sans MT','Gills Sans',sans-serif"){ echo "selected";} ?> >'Gills Sans MT','Gills Sans',sans-serif</option>
<option value="'Lucida Sans Unicode', 'Lucida Grand', sans-serif;" <?php if ($testimonial_slider_curr['ptitle_font'] == "'Lucida Sans Unicode', 'Lucida Grand', sans-serif;"){ echo "selected";} ?> >'Lucida Sans Unicode', 'Lucida Grand', sans-serif;</option>
<option value="'Times New Roman',Times,serif" <?php if ($testimonial_slider_curr['ptitle_font'] == "'Times New Roman',Times,serif"){ echo "selected";}?> >'Times New Roman',Times,serif</option>
<option value="Georgia,serif" <?php if ($testimonial_slider_curr['ptitle_font'] == "Georgia,serif"){ echo "selected";}?> >Georgia,serif</option>
<option value="Garamond,serif" <?php if ($testimonial_slider_curr['ptitle_font'] == "Garamond,serif"){ echo "selected";}?> >Garamond,serif</option>
<option value="'Century Schoolbook','New Century Schoolbook',serif" <?php if ($testimonial_slider_curr['ptitle_font'] == "'Century Schoolbook','New Century Schoolbook',serif"){ echo "selected";}?> >'Century Schoolbook','New Century Schoolbook',serif</option>
<option value="'Bookman Old Style',Bookman,serif" <?php if ($testimonial_slider_curr['ptitle_font'] == "'Bookman Old Style',Bookman,serif"){ echo "selected";}?> >'Bookman Old Style',Bookman,serif</option>
<option value="'Comic Sans MS',cursive" <?php if ($testimonial_slider_curr['ptitle_font'] == "'Comic Sans MS',cursive"){ echo "selected";}?> >'Comic Sans MS',cursive</option>
<option value="'Courier New',Courier,monospace" <?php if ($testimonial_slider_curr['ptitle_font'] == "'Courier New',Courier,monospace"){ echo "selected";}?> >'Courier New',Courier,monospace</option>
<option value="'Copperplate Gothic Bold',Copperplate,fantasy" <?php if ($testimonial_slider_curr['ptitle_font'] == "'Copperplate Gothic Bold',Copperplate,fantasy"){ echo "selected";}?> >'Copperplate Gothic Bold',Copperplate,fantasy</option>
<option value="Impact,fantasy" <?php if ($testimonial_slider_curr['ptitle_font'] == "Impact,fantasy"){ echo "selected";}?> >Impact,fantasy</option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Color','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[ptitle_fcolor]" id="color_value_3" value="<?php echo $testimonial_slider_curr['ptitle_fcolor']; ?>" />&nbsp; <img id="color_picker_3" src="<?php echo testimonial_slider_plugin_url( 'images/color_picker.png' ); ?>" alt="<?php _e('Pick the color of your choice','testimonial-slider'); ?>" /><div class="color-picker-wrap" id="colorbox_3"></div></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Size','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[ptitle_fsize]" id="testimonial_slider_ptitle_fsize" class="small-text" value="<?php echo $testimonial_slider_curr['ptitle_fsize']; ?>" />&nbsp;<?php _e('px','testimonial-slider'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Style','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[ptitle_fstyle]" id="testimonial_slider_ptitle_fstyle" >
<option value="bold" <?php if ($testimonial_slider_curr['ptitle_fstyle'] == "bold"){ echo "selected";}?> ><?php _e('Bold','testimonial-slider'); ?></option>
<option value="bold italic" <?php if ($testimonial_slider_curr['ptitle_fstyle'] == "bold italic"){ echo "selected";}?> ><?php _e('Bold Italic','testimonial-slider'); ?></option>
<option value="italic" <?php if ($testimonial_slider_curr['ptitle_fstyle'] == "italic"){ echo "selected";}?> ><?php _e('Italic','testimonial-slider'); ?></option>
<option value="normal" <?php if ($testimonial_slider_curr['ptitle_fstyle'] == "normal"){ echo "selected";}?> ><?php _e('Normal','testimonial-slider'); ?></option>
</select>
</td>
</tr>
</table>
<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Customer\'s Company/Site','testimonial-slider'); ?></h2> 
<p><?php _e('Customize the Customer\'s Company/Site field looks','testimonial-slider'); ?></p> 
<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Font','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[psite_font]" id="testimonial_slider_psite_font" >
<option value="Arial,Helvetica,sans-serif" <?php if ($testimonial_slider_curr['psite_font'] == "Arial,Helvetica,sans-serif"){ echo "selected";}?> >Arial,Helvetica,sans-serif</option>
<option value="Verdana,Geneva,sans-serif" <?php if ($testimonial_slider_curr['psite_font'] == "Verdana,Geneva,sans-serif"){ echo "selected";}?> >Verdana,Geneva,sans-serif</option>
<option value="Tahoma,Geneva,sans-serif" <?php if ($testimonial_slider_curr['psite_font'] == "Tahoma,Geneva,sans-serif"){ echo "selected";}?> >Tahoma,Geneva,sans-serif</option>
<option value="Trebuchet MS,sans-serif" <?php if ($testimonial_slider_curr['psite_font'] == "Trebuchet MS,sans-serif"){ echo "selected";}?> >Trebuchet MS,sans-serif</option>
<option value="'Century Gothic','Avant Garde',sans-serif" <?php if ($testimonial_slider_curr['psite_font'] == "'Century Gothic','Avant Garde',sans-serif"){ echo "selected";}?> >'Century Gothic','Avant Garde',sans-serif</option>
<option value="'Arial Narrow',sans-serif" <?php if ($testimonial_slider_curr['psite_font'] == "'Arial Narrow',sans-serif"){ echo "selected";}?> >'Arial Narrow',sans-serif</option>
<option value="'Arial Black',sans-serif" <?php if ($testimonial_slider_curr['psite_font'] == "'Arial Black',sans-serif"){ echo "selected";}?> >'Arial Black',sans-serif</option>
<option value="'Gills Sans MT','Gills Sans',sans-serif" <?php if ($testimonial_slider_curr['psite_font'] == "'Gills Sans MT','Gills Sans',sans-serif"){ echo "selected";} ?> >'Gills Sans MT','Gills Sans',sans-serif</option>
<option value="'Lucida Sans Unicode', 'Lucida Grand', sans-serif;" <?php if ($testimonial_slider_curr['psite_font'] == "'Lucida Sans Unicode', 'Lucida Grand', sans-serif;"){ echo "selected";} ?> >'Lucida Sans Unicode', 'Lucida Grand', sans-serif;</option>
<option value="'Times New Roman',Times,serif" <?php if ($testimonial_slider_curr['psite_font'] == "'Times New Roman',Times,serif"){ echo "selected";}?> >'Times New Roman',Times,serif</option>
<option value="Georgia,serif" <?php if ($testimonial_slider_curr['psite_font'] == "Georgia,serif"){ echo "selected";}?> >Georgia,serif</option>
<option value="Garamond,serif" <?php if ($testimonial_slider_curr['psite_font'] == "Garamond,serif"){ echo "selected";}?> >Garamond,serif</option>
<option value="'Century Schoolbook','New Century Schoolbook',serif" <?php if ($testimonial_slider_curr['psite_font'] == "'Century Schoolbook','New Century Schoolbook',serif"){ echo "selected";}?> >'Century Schoolbook','New Century Schoolbook',serif</option>
<option value="'Bookman Old Style',Bookman,serif" <?php if ($testimonial_slider_curr['psite_font'] == "'Bookman Old Style',Bookman,serif"){ echo "selected";}?> >'Bookman Old Style',Bookman,serif</option>
<option value="'Comic Sans MS',cursive" <?php if ($testimonial_slider_curr['psite_font'] == "'Comic Sans MS',cursive"){ echo "selected";}?> >'Comic Sans MS',cursive</option>
<option value="'Courier New',Courier,monospace" <?php if ($testimonial_slider_curr['psite_font'] == "'Courier New',Courier,monospace"){ echo "selected";}?> >'Courier New',Courier,monospace</option>
<option value="'Copperplate Gothic Bold',Copperplate,fantasy" <?php if ($testimonial_slider_curr['psite_font'] == "'Copperplate Gothic Bold',Copperplate,fantasy"){ echo "selected";}?> >'Copperplate Gothic Bold',Copperplate,fantasy</option>
<option value="Impact,fantasy" <?php if ($testimonial_slider_curr['psite_font'] == "Impact,fantasy"){ echo "selected";}?> >Impact,fantasy</option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Color','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[psite_fcolor]" id="color_value_31" value="<?php echo $testimonial_slider_curr['psite_fcolor']; ?>" />&nbsp; <img id="color_picker_31" src="<?php echo testimonial_slider_plugin_url( 'images/color_picker.png' ); ?>" alt="<?php _e('Pick the color of your choice','testimonial-slider'); ?>" /><div class="color-picker-wrap" id="colorbox_31"></div></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Size','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[psite_fsize]" id="testimonial_slider_psite_fsize" class="small-text" value="<?php echo $testimonial_slider_curr['psite_fsize']; ?>" />&nbsp;<?php _e('px','testimonial-slider'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Style','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[psite_fstyle]" id="testimonial_slider_psite_fstyle" >
<option value="bold" <?php if ($testimonial_slider_curr['psite_fstyle'] == "bold"){ echo "selected";}?> ><?php _e('Bold','testimonial-slider'); ?></option>
<option value="bold italic" <?php if ($testimonial_slider_curr['psite_fstyle'] == "bold italic"){ echo "selected";}?> ><?php _e('Bold Italic','testimonial-slider'); ?></option>
<option value="italic" <?php if ($testimonial_slider_curr['psite_fstyle'] == "italic"){ echo "selected";}?> ><?php _e('Italic','testimonial-slider'); ?></option>
<option value="normal" <?php if ($testimonial_slider_curr['psite_fstyle'] == "normal"){ echo "selected";}?> ><?php _e('Normal','testimonial-slider'); ?></option>
</select>
</td>
</tr>
</table>
<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Testimonial Content','testimonial-slider'); ?></h2> 
<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e('Font','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[content_font]" id="testimonial_slider_content_font" >
<option value="Arial,Helvetica,sans-serif" <?php if ($testimonial_slider_curr['content_font'] == "Arial,Helvetica,sans-serif"){ echo "selected";}?> >Arial,Helvetica,sans-serif</option>
<option value="Verdana,Geneva,sans-serif" <?php if ($testimonial_slider_curr['content_font'] == "Verdana,Geneva,sans-serif"){ echo "selected";}?> >Verdana,Geneva,sans-serif</option>
<option value="Tahoma,Geneva,sans-serif" <?php if ($testimonial_slider_curr['content_font'] == "Tahoma,Geneva,sans-serif"){ echo "selected";}?> >Tahoma,Geneva,sans-serif</option>
<option value="Trebuchet MS,sans-serif" <?php if ($testimonial_slider_curr['content_font'] == "Trebuchet MS,sans-serif"){ echo "selected";}?> >Trebuchet MS,sans-serif</option>
<option value="'Century Gothic','Avant Garde',sans-serif" <?php if ($testimonial_slider_curr['content_font'] == "'Century Gothic','Avant Garde',sans-serif"){ echo "selected";}?> >'Century Gothic','Avant Garde',sans-serif</option>
<option value="'Arial Narrow',sans-serif" <?php if ($testimonial_slider_curr['content_font'] == "'Arial Narrow',sans-serif"){ echo "selected";}?> >'Arial Narrow',sans-serif</option>
<option value="'Arial Black',sans-serif" <?php if ($testimonial_slider_curr['content_font'] == "'Arial Black',sans-serif"){ echo "selected";}?> >'Arial Black',sans-serif</option>
<option value="'Gills Sans MT','Gills Sans',sans-serif" <?php if ($testimonial_slider_curr['content_font'] == "'Gills Sans MT','Gills Sans',sans-serif"){ echo "selected";} ?> >'Gills Sans MT','Gills Sans',sans-serif</option>
<option value="'Lucida Sans Unicode', 'Lucida Grand', sans-serif;" <?php if ($testimonial_slider_curr['content_font'] == "'Lucida Sans Unicode', 'Lucida Grand', sans-serif;"){ echo "selected";} ?> >'Lucida Sans Unicode', 'Lucida Grand', sans-serif;</option>
<option value="'Times New Roman',Times,serif" <?php if ($testimonial_slider_curr['content_font'] == "'Times New Roman',Times,serif"){ echo "selected";}?> >'Times New Roman',Times,serif</option>
<option value="Georgia,serif" <?php if ($testimonial_slider_curr['content_font'] == "Georgia,serif"){ echo "selected";}?> >Georgia,serif</option>
<option value="Garamond,serif" <?php if ($testimonial_slider_curr['content_font'] == "Garamond,serif"){ echo "selected";}?> >Garamond,serif</option>
<option value="'Century Schoolbook','New Century Schoolbook',serif" <?php if ($testimonial_slider_curr['content_font'] == "'Century Schoolbook','New Century Schoolbook',serif"){ echo "selected";}?> >'Century Schoolbook','New Century Schoolbook',serif</option>
<option value="'Bookman Old Style',Bookman,serif" <?php if ($testimonial_slider_curr['content_font'] == "'Bookman Old Style',Bookman,serif"){ echo "selected";}?> >'Bookman Old Style',Bookman,serif</option>
<option value="'Comic Sans MS',cursive" <?php if ($testimonial_slider_curr['content_font'] == "'Comic Sans MS',cursive"){ echo "selected";}?> >'Comic Sans MS',cursive</option>
<option value="'Courier New',Courier,monospace" <?php if ($testimonial_slider_curr['content_font'] == "'Courier New',Courier,monospace"){ echo "selected";}?> >'Courier New',Courier,monospace</option>
<option value="'Copperplate Gothic Bold',Copperplate,fantasy" <?php if ($testimonial_slider_curr['content_font'] == "'Copperplate Gothic Bold',Copperplate,fantasy"){ echo "selected";}?> >'Copperplate Gothic Bold',Copperplate,fantasy</option>
<option value="Impact,fantasy" <?php if ($testimonial_slider_curr['content_font'] == "Impact,fantasy"){ echo "selected";}?> >Impact,fantasy</option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Color','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[content_fcolor]" id="color_value_5" value="<?php echo $testimonial_slider_curr['content_fcolor']; ?>" />&nbsp; <img id="color_picker_5" src="<?php echo testimonial_slider_plugin_url( 'images/color_picker.png' ); ?>" alt="Pick the color of your choice','testimonial-slider'); ?>" /><div class="color-picker-wrap" id="colorbox_5"></div></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Size','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[content_fsize]" id="testimonial_slider_content_fsize" class="small-text" value="<?php echo $testimonial_slider_curr['content_fsize']; ?>" />&nbsp;<?php _e('px','testimonial-slider'); ?></td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Font Style','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[content_fstyle]" id="testimonial_slider_content_fstyle" >
<option value="bold" <?php if ($testimonial_slider_curr['content_fstyle'] == "bold"){ echo "selected";}?> ><?php _e('Bold','testimonial-slider'); ?></option>
<option value="bold italic" <?php if ($testimonial_slider_curr['content_fstyle'] == "bold italic"){ echo "selected";}?> ><?php _e('Bold Italic','testimonial-slider'); ?></option>
<option value="italic" <?php if ($testimonial_slider_curr['content_fstyle'] == "italic"){ echo "selected";}?> ><?php _e('Italic','testimonial-slider'); ?></option>
<option value="normal" <?php if ($testimonial_slider_curr['content_fstyle'] == "normal"){ echo "selected";}?> ><?php _e('Normal','testimonial-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Pick content From','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[content_from]" id="testimonial_slider_content_from" >
<option value="slider_content" <?php if ($testimonial_slider_curr['content_from'] == "slider_content"){ echo "selected";}?> ><?php _e('Slider Content Custom field','testimonial-slider'); ?></option>
<option value="excerpt" <?php if ($testimonial_slider_curr['content_from'] == "excerpt"){ echo "selected";}?> ><?php _e('Post Excerpt','testimonial-slider'); ?></option>
<option value="content" <?php if ($testimonial_slider_curr['content_from'] == "content"){ echo "selected";}?> ><?php _e('From Content','testimonial-slider'); ?></option>
</select>
</td>
</tr>
<tr valign="top">
<th scope="row"><?php _e('Maximum content(testimonial) size (in words)','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[content_limit]" id="testimonial_slider_content_limit" class="small-text" value="<?php echo $testimonial_slider_curr['content_limit']; ?>" />&nbsp;<?php _e('words','testimonial-slider'); ?>
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('Keep empty to select complete Content','testimonial-slider'); ?>
	</div>
</span>
</td>
</tr>

</table>

</div>
</div> <!-- slider_content tab ends-->

<div id="slider_nav">
<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:10px 0">
<h2><?php _e('Navigational Buttons','testimonial-slider'); ?></h2> 

<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('Show Navigation Buttons','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[navnum]" >
<option value="0" <?php if ($testimonial_slider_curr['navnum'] == "0"){ echo "selected";}?> ><?php _e('No','testimonial-slider'); ?></option>
<option value="1" <?php if ($testimonial_slider_curr['navnum'] == "1"){ echo "selected";}?> ><?php _e('Bottom of Slider','testimonial-slider'); ?></option>
<option value="2" <?php if ($testimonial_slider_curr['navnum'] == "2"){ echo "selected";}?> ><?php _e('Top of Slider','testimonial-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Navigation Buttons Folder','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[buttons]" >
<?php 
$directory = TESTIMONIAL_SLIDER_CSS_DIR.$testimonial_slider['stylesheet'].'/buttons/';
if ($handle = opendir($directory)) {
    while (false !== ($file = readdir($handle))) { 
     if($file != '.' and $file != '..') { ?>
      <option value="<?php echo $file;?>" <?php if ($testimonial_slider_curr['buttons'] == $file){ echo "selected";}?> ><?php echo $file;?></option>
 <?php  } }
    closedir($handle);
}
?>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Navigation Button Width','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[navimg_w]" id="testimonial_slider_navimg_w" class="small-text" value="<?php echo $testimonial_slider_curr['navimg_w']; ?>" />&nbsp;px</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Navigation Button Height','testimonial-slider'); ?></th>
<td><input type="text" name="<?php echo $testimonial_slider_options;?>[navimg_h]" id="testimonial_slider_navimg_h" class="small-text" value="<?php echo $testimonial_slider_curr['navimg_h']; ?>" />&nbsp;px</td>
</tr>

<tr valign="top"> 
<th scope="row"><?php _e('Hide Prev/Next navigation arrows','testimonial-slider'); ?></th> 
<td><label for="testimonial_slider_prev_next"> 
<input name="<?php echo $testimonial_slider_options;?>[prev_next]" type="checkbox" id="testimonial_slider_prev_next" value="1" <?php checked("1", $testimonial_slider_curr['prev_next']); ?> /> 
</td>
</tr>

</table>

</div>

</div><!-- slider_nav tab ends-->

<div id="responsive">
<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Responsive Design Settings','testimonial-slider'); ?></h2> 

<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e('Enable Responsive Design','testimonial-slider'); ?></th>
<td><input name="<?php echo $testimonial_slider_options;?>[responsive]" type="checkbox" value="1" <?php checked('1', $testimonial_slider_curr['responsive']); ?>  />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('check this if you want to enable the responsive layout for Testimonial (you should be using Responsive/Fluid WordPress theme for this feature to work!)','testimonial-slider'); ?>
	</div>
</span>
</td>
</tr>
</table>
</div>

</div> <!--#responsive-->

<div id="preview">
<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:0;">
<h2><?php _e('Preview on Settings Panel','testimonial-slider'); ?></h2> 

<table class="form-table">

<tr valign="top"> 
<th scope="row"><label for="testimonial_slider_disable_preview"><?php _e('Disable Preview Section','testimonial-slider'); ?></label></th> 
<td> 
<input name="<?php echo $testimonial_slider_options;?>[disable_preview]" type="checkbox" id="testimonial_slider_disable_preview" value="1" <?php checked("1", $testimonial_slider_curr['disable_preview']); ?> />
<span class="moreInfo">
	&nbsp; <span class="trigger"> ? </span>
	<div class="tooltip">
	<?php _e('If disabled, the \'Preview\' of Slider on this Settings page will be removed.','testimonial-slider'); ?>
	</div>
</span>
</td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Testimonial Template Tag for Preview','testimonial-slider'); ?></th>
<td><select name="<?php echo $testimonial_slider_options;?>[preview]" >
<option value="2" <?php if ($testimonial_slider_curr['preview'] == "2"){ echo "selected";}?> ><?php _e('Recent Testimonials Slider','testimonial-slider'); ?></option>
<option value="1" <?php if ($testimonial_slider_curr['preview'] == "1"){ echo "selected";}?> ><?php _e('Category Testimonial Slider','testimonial-slider'); ?></option>
<option value="0" <?php if ($testimonial_slider_curr['preview'] == "0"){ echo "selected";}?> ><?php _e('Custom Slider with Slider ID','testimonial-slider'); ?></option>
</select>
</td>
</tr>

<tr valign="top"> 
<th scope="row"><?php _e('Preview Slider Params','testimonial-slider'); ?></th> 
<td><fieldset><legend class="screen-reader-text"><span><?php _e('Preview Slider Params','testimonial-slider'); ?></span></legend> 
<label for="<?php echo $testimonial_slider_options;?>[slider_id]"><?php _e('Slider ID in case of Custom Slider','testimonial-slider'); ?></label>
<input type="text" name="<?php echo $testimonial_slider_options;?>[slider_id]" class="small-text" value="<?php echo $testimonial_slider_curr['slider_id']; ?>" /> 
<br />  <br />
<label for="<?php echo $testimonial_slider_options;?>[catg_slug]"><?php _e('Category Slug in case of Category Slider','testimonial-slider'); ?></label>
<input type="text" name="<?php echo $testimonial_slider_options;?>[catg_slug]" class="regular-text code" style="width:90%;" value="<?php echo $testimonial_slider_curr['catg_slug']; ?>" /> 
</fieldset></td> 
</tr> 

</table>
</div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Shortcode for Testimonial Slider','testimonial-slider'); ?></h2> 
<p><?php _e('Paste the below shortcode on Page/Post Edit Panel to get the slider as shown in the above Preview','testimonial-slider'); ?></p><br />
<?php if($cntr=='') $s_set='1'; else $s_set=$cntr;
if ($testimonial_slider_curr['preview'] == "0")
	echo '[testimonialslider id="'.$testimonial_slider_curr['slider_id'].'" set="'.$s_set.'"]';
elseif($testimonial_slider_curr['preview'] == "1")
	echo '[testimonialcategory catg_slug="'.$testimonial_slider_curr['catg_slug'].'" set="'.$s_set.'"]';
else
	echo '[testimonialrecent set="'.$s_set.'"]';
?>
</div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Template Tag for Testimonial Slider','testimonial-slider'); ?></h2> 
<p><?php _e('Paste the below template tag in your theme template file like index.php or page.php at required location to get the slider as shown in the above Preview','testimonial-slider'); ?></p><br />
<?php 
if ($testimonial_slider_curr['preview'] == "0")
	echo '<code>&lt;?php if(function_exists("get_testimonial_slider")){get_testimonial_slider($slider_id="'.$testimonial_slider_curr['slider_id'].'",$set="'.$s_set.'");}?&gt;</code>';
elseif($testimonial_slider_curr['preview'] == "1")
	echo '<code>&lt;?php if(function_exists("get_testimonial_slider_category")){get_testimonial_slider_category($catg_slug="'.$testimonial_slider_curr['catg_slug'].'",$set="'.$s_set.'");}?&gt;</code>';
else
	echo '<code>&lt;?php if(function_exists("get_testimonial_slider_recent")){get_testimonial_slider_recent($set="'.$s_set.'");}?&gt;</code>';
?>
</div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('Shortcode for Testimonials List','testimonial-slider'); ?></h2> 
<p><?php _e('Paste the below shortcode on Page/Post Edit Panel to get the list of Testimonials in the above Preview Testimonial Slider','testimonial-slider'); ?></p><br />
<?php if($cntr=='') $s_set='1'; else $s_set=$cntr;
if ($testimonial_slider_curr['preview'] == "0")
	echo '[testimonialCustomList id="'.$testimonial_slider_curr['slider_id'].'" set="'.$s_set.'"]';
elseif($testimonial_slider_curr['preview'] == "1")
	echo '[testimonialListCategory catg_slug="'.$testimonial_slider_curr['catg_slug'].'" set="'.$s_set.'"]';
else
	echo '[testimonialList set="'.$s_set.'"]';
?>
</div>

</div><!-- preview tab ends-->

<div id="cssvalues">
<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:3px 0">
<h2><?php _e('CSS Generated thru these settings','testimonial-slider'); ?></h2> 
<p><?php _e('Save Changes for the settings first and then view this data. You should paste this CSS in your \'custom\' stylesheets if you use other than \'default\' value for the Stylesheet folder. After pasting, you can edit these CSS values.','testimonial-slider'); ?></p> 
<?php $testimonial_slider_css = testimonial_get_inline_css($cntr,$echo='1'); ?>
<div style="font-family:monospace;font-size:13px;background:#ddd;">
.testimonial_slider_set<?php echo $cntr;?>{<?php echo $testimonial_slider_css['testimonial_slider'];?>} <br />
.testimonial_slider_set<?php echo $cntr;?> .testimonial_slideri{<?php echo $testimonial_slider_css['testimonial_slideri'];?>} <br />
.testimonial_slider_set<?php echo $cntr;?> .testimonial_avatar img{<?php echo $testimonial_slider_css['testimonial_avatar_img'] ;?>} <br />
.testimonial_slider_set<?php echo $cntr;?> .testimonial_avatar{<?php echo $testimonial_slider_css['testimonial_by'] ;?>} <br />
.testimonial_slider_set<?php echo $cntr;?> .testimonial_site, .testimonial_slider_set<?php echo $cntr;?> .testimonial_site a{<?php echo $testimonial_slider_css['testimonial_site_a'] ;?>} <br />
.testimonial_slider_set<?php echo $cntr;?> .testimonial_quote{<?php echo $testimonial_slider_css['testimonial_quote'] ;?>} <br />
.testimonial_slider_set<?php echo $cntr;?> .testimonial_nav a{<?php echo $testimonial_slider_css['testimonial_nav_a'] ;?>} <br />
.testimonial_slider_set<?php echo $cntr;?> .testimonial_next{<?php echo $testimonial_slider_css['testimonial_next'];?>} <br />
.testimonial_slider_set<?php echo $cntr;?> .testimonial_prev{<?php echo $testimonial_slider_css['testimonial_prev'];?>} 
</div>
</div>
</div> <!--#cssvalues-->

<div class="svilla_cl"></div><div class="svilla_cr"></div>
</div> <!--end of tabs -->

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
<input type="hidden" name="<?php echo $testimonial_slider_options;?>[active_tab]" id="testimonial_activetab" value="<?php echo $testimonial_slider_curr['active_tab']; ?>" />
</form>

<!--Form to reset Settings set-->
<form style="float:left;" action="" method="post">
<table class="form-table">
<tr valign="top">
<th scope="row"><?php _e('Reset Settings to','testimonial-slider'); ?></th>
<td><select name="testimonial_reset_settings" id="testimonial_slider_reset_settings" >
<option value="n" selected ><?php _e('None','testimonial-slider'); ?></option>
<option value="g" ><?php _e('Global Default','testimonial-slider'); ?></option>

<?php 
for($i=1;$i<=$scounter;$i++){
	if ($i==1){
	  echo '<option value="'.$i.'" >'.__('Default Settings Set','testimonial-slider').'</option>';
	}
	else {
	  if($settings_set=get_option('testimonial_slider_options'.$i)){
		echo '<option value="'.$i.'" >'.$settings_set['setname'].' (ID '.$i.')</option>';
	  }
	}
}
?>

</select>
</td>
</tr>
</table>

<p class="submit">
<input name="testimonial_reset_settings_submit" type="submit" class="button-primary" value="<?php _e('Reset Settings') ?>" />
</p>
</form>

<div class="svilla_cl"></div>

<div style="border:1px solid #ccc;padding:10px;background:#fff;margin:0;" id="import">
<?php echo $imported_settings_message;?>
<h3><?php _e('Import Settings Set by uploading a Settings File','testimonial-slider'); ?></h3>
<form style="margin-right:10px;font-size:14px;" action="" method="post" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="30000" />
<input type="file" name="settings_file" id="settings_file" style="font-size:13px;width:50%;padding:0 5px;" />
<input type="submit" value="Import" name="import"  onclick="return confirmSettingsImport()" title="<?php _e('Import Settings from a file','testimonial-slider'); ?>" class="button-primary" />
</form>
</div>

</div> <!--end of float left -->

<div id="poststuff" class="metabox-holder has-right-sidebar" style="float:left;width:28%;max-width:350px;min-width:inherit;"> 
<?php $url = testimonial_sslider_admin_url( array( 'page' => 'testimonial-slider-admin' ) );?>
<form style="margin-right:10px;font-size:14px;width:100%;" action="" method="post">
<a href="<?php echo $url; ?>" title="<?php _e('Go to Sliders page where you can re-order the slide posts, delete the slides from the slider etc.','testimonial-slider'); ?>" class="svilla_button svilla_gray_button"><?php _e('Go to Sliders Admin','testimonial-slider'); ?></a>
<input type="submit" class="svilla_button" value="Create New Settings Set" name="create_set"  onclick="return confirmSettingsCreate()" /> <br />
<input type="submit" value="Export" name="export" title="<?php _e('Export this Settings Set to a file','testimonial-slider'); ?>" class="svilla_button" />
<a href="#import" title="<?php _e('Go to Import Settings Form','testimonial-slider'); ?>" class="svilla_button">Import</a>
<div class="svilla_cl"></div>
</form>
<div class="svilla_cl"></div>

<div class="postbox" style="margin:10px 0;"> 
			  <h3 class="hndle"><span></span><?php _e('Available Settings Sets','testimonial-slider'); ?></h3> 
			  <div class="inside">
<?php 
for($i=1;$i<=$scounter;$i++){
   if ($i==1){
      echo '<h4><a href="'.testimonial_sslider_admin_url( array( 'page' => 'testimonial-slider-settings' ) ).'" title="(Settings Set ID '.$i.')">Default Settings (ID '.$i.')</a></h4>';
   }
   else {
      if($settings_set=get_option('testimonial_slider_options'.$i)){
		echo '<h4><a href="'.testimonial_sslider_admin_url( array( 'page' => 'testimonial-slider-settings' ) ).'&scounter='.$i.'" title="(Settings Set ID '.$i.')">'.$settings_set['setname'].' (ID '.$i.')</a></h4>';
	  }
   }
}
?>
</div></div>

<div class="postbox"> 
<div style="background:#eee;line-height:200%"><a style="text-decoration:none;font-weight:bold;font-size:100%;color:#990000" href="http://guides.slidervilla.com/testimonial-slider/" title="Click here to read how to use the plugin and frequently asked questions about the plugin" target="_blank"> ==> Usage Guide and General FAQs</a></div>
</div>

<?php if ($testimonial_slider['support'] == "1"){ ?>
    
     		<div class="postbox"> 
     		  <div class="inside">
				<div style="margin:10px auto;">
							<a href="http://slidervilla.com/" title="Premium WordPress Slider Plugins" target="_blank"><img src="<?php echo testimonial_slider_plugin_url('images/slidervilla.jpg');?>" alt="Premium WordPress Slider Plugins" /></a>
				</div>
            </div></div>
			
			<div class="postbox"> 
			  <h3 class="hndle"><span></span><?php _e('Recommended Themes','testimonial-slider'); ?></h3> 
			  <div class="inside">
                     <div style="margin:10px 5px">
                        <a href="http://slidervilla.com/go/elegantthemes/" title="Recommended WordPress Themes" target="_blank"><img src="<?php echo testimonial_slider_plugin_url('images/elegantthemes.gif');?>" alt="Recommended WordPress Themes" style="width:100%;" /></a>
                        <p><a href="http://slidervilla.com/go/elegantthemes/" title="Recommended WordPress Themes" target="_blank">Elegant Themes</a> are attractive, compatible, affordable, SEO optimized WordPress Themes and have best support in community.</p>
                        <p><strong>Beautiful themes, Great support!</strong></p>
                        <p><a href="http://slidervilla.com/go/elegantthemes/" title="Recommended WordPress Themes" target="_blank">For more info visit ElegantThemes</a></p>
                     </div>
               </div></div>
          
			<div class="postbox"> 
			  <h3 class="hndle"><span><?php _e('About this Plugin:','testimonial-slider'); ?></span></h3> 
			  <div class="inside">
                <ul>
                <li><a href="http://slidervilla.com/testimonial/" title="<?php _e('Testimonial Slider Homepage','testimonial-slider'); ?>
" ><?php _e('Plugin Homepage','testimonial-slider'); ?></a></li>
				<li><a href="http://support.slidervilla.com/" title="<?php _e('Support Forum','testimonial-slider'); ?>
" ><?php _e('Support Forum','testimonial-slider'); ?></a></li>
				<li><a href="http://guides.slidervilla.com/testimonial-slider/" title="<?php _e('Usage Guide','testimonial-slider'); ?>
" ><?php _e('Usage Guide','testimonial-slider'); ?></a></li>
				<li><strong>Current Version: 1.0.1</strong></li>
                </ul> 
              </div> 
			</div> 
	<?php } ?>
                 
 </div> <!--end of poststuff --> 

<div style="clear:left;"></div>
<div style="clear:right;"></div>

</div> <!--end of float wrap -->
<?php	
}

function register_testimonial_settings() { // whitelist options
  $scounter=get_option('testimonial_slider_scounter');
  for($i=1;$i<=$scounter;$i++){
	   if ($i==1){
		  register_setting( 'testimonial-slider-group', 'testimonial_slider_options' );
	   }
	   else {
	      $group='testimonial-slider-group'.$i;
		  $options='testimonial_slider_options'.$i;
		  register_setting( $group, $options );
	   }
  }
}
?>