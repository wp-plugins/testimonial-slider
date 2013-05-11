<?php // This function displays the page content for the Testimonial Slider Import Submenu
function testimonial_slider_import_testimonials() {
global $testimonial_slider;
?>
<div class="wrap" style="clear:both;">

<h2><?php _e('Import Testimonials','testimonial-slider'); ?></h2>
<?php 

if ($_POST['import'] == __('Import','testimonial-slider')) {
	$imported_testimonials_message='';
	$csv_mimetypes = array('text/csv','text/plain','application/csv','text/comma-separated-values','application/excel',
'application/vnd.ms-excel','application/vnd.msexcel','text/anytext','application/octet-stream','application/txt');
	if ($_FILES['csv_file']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['csv_file']['tmp_name']) && in_array($_FILES['csv_file']['type'], $csv_mimetypes) ) { 
		$imported_testimonials=file_get_contents($_FILES['csv_file']['tmp_name']); 
		$testimonials_arr=explode("\n",$imported_testimonials);
		$count=0;
		if($testimonials_arr){
			foreach($testimonials_arr as $testimonial){
				$f=explode(',',$testimonial);
				$testimonial_title=wp_strip_all_tags( $f[0]);
				$testimonial_content=$f[0].'<!--more-->'.$f[1];
				$testimonial_category=isset($_POST['category'])? $_POST['category'] : '';
				// Create Testimonial object
				if(!empty($testimonial_title)){
					$seconds='-30 minutes +'.$count.' seconds';
					$testimonial_post = array(
					  'post_type'		=> 'testimonial',
					  'post_title'  	=> $testimonial_title,
					  'post_content'	=> $testimonial_content,
					  'post_status'		=> 'publish',
					  'post_date' 		=> date('Y-m-d H:i:s', strtotime($seconds)),
					);
					// Insert the Testimonial into the database
					$testimonial_id=wp_insert_post( $testimonial_post  );
					if(!empty($testimonial_category)) wp_set_object_terms($testimonial_id,$testimonial_category,'testimonial_category');
					if($testimonial_id)$count++;
				}
			}
		}
		if($count>0)$imported_testimonials_message='<div style="clear:left;color:#006E2E;"><h3>'.$count.__(' testimonials imported successfully','testimonial-slider').'</h3></div>';
	}
	else{
		$imported_testimonials_message='<div style="clear:left;color:#ff0000;"><h3>'.__('Error in File, Testimonials not imported. Please check the file being imported. ','testimonial-slider').'</h3></div>';
	}
}

?>

<?php echo $imported_testimonials_message;?>
<form style="margin-right:10px;font-size:14px;" action="" method="post" enctype="multipart/form-data">

<table class="form-table">

<tr valign="top">
<th scope="row"><?php _e('CSV file','testimonial-slider'); ?></th>
<td><input type="hidden" name="MAX_FILE_SIZE" value="30000" />
<input type="file" name="csv_file" id="csv_file" style="font-size:13px;width:50%;padding:0 5px;" /><td>
</tr>

<tr valign="top">
<th scope="row"><?php _e('Testimonial Category','testimonial-slider'); ?></th>
<td>
<?php
$taxonomies = array( 
    'testimonial_category'
);

$args = array(
    'orderby'       => 'name', 
    'order'         => 'ASC',
    'hide_empty'    => false, 
    'exclude'       => array(), 
    'exclude_tree'  => array(), 
    'include'       => array(), 
    'fields'        => 'all', 
    'hierarchical'  => true, 
    'child_of'      => 0, 
    'pad_counts'    => false, 
    'cache_domain'  => 'core'
);
$categories=get_terms( $taxonomies, $args );
?>

<select name="category">
	<?php foreach ($categories as $category) { ?>
	  <option value="<?php echo $category->slug;?>"><?php echo $category->name;?></option>
	<?php } ?>
</select>
</td>
</tr>

<tr valign="top">
<td><input type="submit" value="Import" name="import"  onclick="return confirmFileImport()" title="<?php _e('Import Testimonials from a Comma Separated CSV file','testimonial-slider'); ?>" class="button-primary" /></td>
</tr>

</table>

</form>

</div> <!--end of float wrap -->
<?php	
}
?>