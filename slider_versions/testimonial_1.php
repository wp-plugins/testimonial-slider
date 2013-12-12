<?php 
function testimonial_global_posts_processor( $posts, $testimonial_slider_curr,$out_echo,$set,$data=array() ){
	global $testimonial_slider,$default_testimonial_slider_settings;
	$testimonial_slider_css = testimonial_get_inline_css($set);
	$html = '';
	$testimonial_sldr_j = 0;
	
	$slider_handle='';
	if ( is_array($data) and isset($data['slider_handle']) ) {
		$slider_handle=$data['slider_handle'];
	}
	
	foreach($default_testimonial_slider_settings as $key=>$value){
		if(!isset($testimonial_slider_curr[$key])) $testimonial_slider_curr[$key]='';
	}
	
	foreach($posts as $post) {
		$id = $post->ID;	
		$post_id = $post->ID;
		$testimonial_by_wrap=$testimonial_quote='';
		
		$slider_content = $post->post_content;
		
		$testimonial_slide_redirect_url = get_post_meta($post_id, 'testimonial_slide_redirect_url', true);
		$_testimonial_sslider_nolink = get_post_meta($post_id,'_testimonial_sslider_nolink',true);
		trim($testimonial_slide_redirect_url);
		if(!empty($testimonial_slide_redirect_url) and isset($testimonial_slide_redirect_url)) {
		   $permalink = $testimonial_slide_redirect_url;
		}
		else{
		   $permalink = get_permalink($post_id);
		}
		if($_testimonial_sslider_nolink=='1'){
		  $permalink='';
		}
			
		$testimonial_sldr_j++;
		
		//Slide link anchor attributes
		$a_attr='';
		$a_attr=get_post_meta($post_id,'testimonial_link_attr',true);
		if( empty($a_attr) and isset( $testimonial_slider_curr['a_attr'] ) ) $a_attr=$testimonial_slider_curr['a_attr'];
		
		$html .= '<div class="testimonial_slideri" '.$testimonial_slider_css['testimonial_slideri'].'>
			<!-- testimonial_slideri -->';

		//Testimonial by
		$_testimonial_by=get_post_meta($post_id, '_testimonial_by', true);
		
		$_testimonial_avatar=get_post_meta($post_id, '_testimonial_avatar', true);
		if( empty($_testimonial_avatar) and isset( $testimonial_slider_curr['default_avatar'] ) ) $_testimonial_avatar=$testimonial_slider_curr['default_avatar'];
		
		$_testimonial_site=get_post_meta($post_id, '_testimonial_site', true);
		$_testimonial_siteurl=get_post_meta($post_id, '_testimonial_siteurl', true);
		if(empty($_testimonial_siteurl)) $testimonial_company=$_testimonial_site;
		else $testimonial_company='<a href="'.$_testimonial_siteurl.'" '.$a_attr.' '.$testimonial_slider_css['testimonial_site_a'].'>'.$_testimonial_site.'</a>';
		
		$testimonial_by_wrap='<div class="testimonial_by_wrap" '.$testimonial_slider_css['testimonial_by_wrap'].'><span class="testimonial_avatar" '.$testimonial_slider_css['testimonial_avatar'].'><img src="'.$_testimonial_avatar.'" '.$testimonial_slider_css['testimonial_avatar_img'].' /></span><span class="testimonial_by" '.$testimonial_slider_css['testimonial_by'].'>'.$_testimonial_by.'</span><span class="testimonial_site" '.$testimonial_slider_css['testimonial_site_a'].'>'.$testimonial_company.'</span></div>';
		
		/*$slider_content = strip_shortcodes( $slider_content );

		$slider_content = stripslashes($slider_content);
		$slider_content = str_replace(']]>', ']]&gt;', $slider_content);

		$slider_content = str_replace("\n","<br />",$slider_content);
		$slider_content = strip_tags($slider_content, $testimonial_slider_curr['allowable_tags']);*/
		if(!$testimonial_slider_curr['content_limit'] or $testimonial_slider_curr['content_limit'] == '' or $testimonial_slider_curr['content_limit'] == ' ') 
		  $slider_excerpt = substr($slider_content,0,$testimonial_slider_curr['content_chars']);
		else 
		  $slider_excerpt = testimonial_slider_word_limiter( $slider_content, $limit = $testimonial_slider_curr['content_limit'] );
		//filter hook
		$slider_excerpt=apply_filters('testimonial_slide_excerpt',$slider_excerpt,$post_id,$testimonial_slider_curr,$testimonial_slider_css);
		$slider_excerpt='<span '.$testimonial_slider_css['testimonial_slider_span'].'> '.$slider_excerpt.'</span>';

		//filter hook
		$slider_excerpt=apply_filters('testimonial_slide_excerpt_html',$slider_excerpt,$post_id,$testimonial_slider_curr,$testimonial_slider_css);
		
		$read_more='';
		if($testimonial_slider_curr['more'] and $testimonial_slider_curr['more']!='' and $permalink!=''){
			$read_more='<p class="more"><a href="'.$permalink.'" '.$testimonial_slider_css['testimonial_slider_p_more'].' '.$a_attr.'>'.$testimonial_slider_curr['more'].'</a></p>';
		}
		
		$testimonial_quote='<div class="testimonial_quote" '.$testimonial_slider_css['testimonial_quote'].'>'.$slider_excerpt.$read_more.'</div>';
		
		$html .= $testimonial_by_wrap.$testimonial_quote;
		$html .= '	<div class="sldr_clearlt"></div><div class="sldr_clearrt"></div><!-- /testimonial_slideri -->
		</div>'; 
	}
	
	$html=apply_filters('testimonial_extract_html',$html,$testimonial_sldr_j,$posts,$testimonial_slider_curr);
	
	if($out_echo == '1') {
	   echo $html;
	}
	$r_array = array( $testimonial_sldr_j, $html);
	$r_array=apply_filters('testimonial_r_array',$r_array,$posts, $testimonial_slider_curr,$set);
	return $r_array;
}

function get_global_testimonial_slider($slider_handle,$r_array,$testimonial_slider_curr,$set,$echo='1',$data=array()){
	global $testimonial_slider,$default_testimonial_slider_settings;
	$testimonial_sldr_j = $r_array[0];
	$testimonial_slider_css = testimonial_get_inline_css($set); 
	$slider_html='';
	
	foreach($default_testimonial_slider_settings as $key=>$value){
		if(!isset($testimonial_slider_curr[$key])) $testimonial_slider_curr[$key]='';
	}
	
	$testimonial_media_queries='';
	$o_visible=$testimonial_slider_curr['visible'];$o_responsive='';$o_width='';
	$responsive_max_width=($testimonial_slider_curr['width']>0)?( $testimonial_slider_curr['width'].'px'  ) : ( '100%' );
    if( $testimonial_slider_curr['responsive'] == '1' ) {
		$testimonial_media_queries='.testimonial_slider_set'.$set.'.testimonial_slider{width:100% !important;max-width:'.$responsive_max_width.';display:block;}.testimonial_slider_set'.$set.' img{max-width:90% !important;}.testimonial_side{width:100% !important;}';
		//filter hook
		$testimonial_media_queries=apply_filters('testimonial_media_queries',$testimonial_media_queries,$testimonial_slider_curr,$set);
		$o_visible='{	min: 1,	max: '.$testimonial_slider_curr['visible'] .'}';
		$o_responsive='responsive: true,';
		$o_width='width: '.$testimonial_slider_curr['iwidth'].',';
	}
	
	if(!isset($testimonial_slider_curr['fouc']) or $testimonial_slider_curr['fouc']=='0' ){
		$fouc='jQuery("html").addClass("testimonial_slider_fouc");
		jQuery(document).ready(function() {
		   jQuery(".testimonial_slider_fouc .testimonial_slider").css({"display" : "block"});
		});';
    }	
	else{
	    $fouc='';
	}		
	if ($testimonial_slider_curr['disable_autostep'] == '1'){ $autostep = "false"; } else { $autostep = $testimonial_slider_curr['time'] * 100; }
	$prevnext='';
	if ($testimonial_slider_curr['prev_next'] != 1){ 
	  $prevnext='next:   "#'.$slider_handle.'_next", 
				 prev:   "#'.$slider_handle.'_prev",';
	}
	$type='';
	if ($testimonial_slider_curr['type'] == "1"){
		$type='circular:false,
					infinite:false,';
	}
	
	$pagination=$nav_top=$nav_bottom='';
	if ($testimonial_slider_curr['navnum'] == "1"){ 
		$nav_top='';
		$nav_bottom='<div id="'.$slider_handle.'_nav" class="testimonial_nav" '.$testimonial_slider_css['testimonial_nav'].'></div>';
		$pagination='pagination  : { container: "#'.$slider_handle.'_nav",
			anchorBuilder: function( nr ) {
				return \'<a href="#" '.$testimonial_slider_css['testimonial_nav_a'].'></a>\';
			} },';
    } 
	if ($testimonial_slider_curr['navnum'] == "2"){ 
		$nav_top='<div id="'.$slider_handle.'_nav" class="testimonial_nav" '.$testimonial_slider_css['testimonial_nav'].'></div>';
		$nav_bottom='';
		$pagination='pagination  : { container: "#'.$slider_handle.'_nav",
			anchorBuilder: function( nr ) {
				return \'<a href="#" '.$testimonial_slider_css['testimonial_nav_a'].'></a>\';
			} },';
    } 
	
	$script='<script type="text/javascript"> '.$fouc;
	if(!empty($testimonial_media_queries)){
			$script.='jQuery(document).ready(function() {jQuery("head").append("<style type=\"text/css\">'. $testimonial_media_queries .'</style>");});';
	}
	$script.='jQuery(document).ready(function() {
			jQuery("#'.$slider_handle.'").testiMonial({
				'.$o_responsive.'
				items: 	{
					'.$o_width.'
					visible     : '.$o_visible.'
				},
				'.$pagination.'
				auto: '.$autostep.','.$type.' '.$prevnext.'
				scroll: {
						items:'.$testimonial_slider_curr['scroll'].',
						fx: "'.$testimonial_slider_curr['transition'].'",
						easing: "'. $testimonial_slider_curr['easing'].'",
						duration: '.$testimonial_slider_curr['speed'] * 100 .',
						pauseOnHover: true
					}
			});
			jQuery("head").append("<style type=\"text/css\">#'.$slider_handle.'_nav a.selected{background-position:-'.$testimonial_slider_curr['navimg_w'].'px 0 !important;}</style>");
			jQuery("#'.$slider_handle.'_wrap").hover( 
				function() { jQuery(this).find(".testimonial_nav_arrow_wrap").show();}, 
				function() { jQuery(this).find(".testimonial_nav_arrow_wrap").hide();} );
			jQuery("#'.$slider_handle.'").touchwipe({
					wipeLeft: function() {
						jQuery("#'.$slider_handle.'").trigger("next", 1);
					},
					wipeRight: function() {
						jQuery("#'.$slider_handle.'").trigger("prev", 1);
					},
					preventDefaultEvents: false
			});				
		});';
	//action hook
	do_action('testimonial_global_script',$slider_handle,$testimonial_slider_curr);
	$script.='</script>';
	
	$stylesheet=$testimonial_slider['stylesheet'];
	if(empty($stylesheet)) $stylesheet = 'default';
	
	$slider_html.=$script.' 
	<noscript><p><strong>'. $testimonial_slider['noscript'] .'</strong></p></noscript>
	<div id="'.$slider_handle.'_wrap" class="testimonial_slider testimonial_slider_set'. $set .' testimonial_slider__'.$stylesheet.'" '.$testimonial_slider_css['testimonial_slider'].'>
		'.$nav_top.'
		<div id="'.$slider_handle.'" class="testimonial_slider_instance">
			'. $r_array[1] .'
		</div>
		'.$nav_bottom.'
		<div class="testimonial_nav_arrow_wrap">
			<a class="testimonial_prev" id="'.$slider_handle.'_prev" href="#" '.$testimonial_slider_css['testimonial_prev'].'><span>prev</span></a>
			<a class="testimonial_next" id="'.$slider_handle.'_next" href="#" '.$testimonial_slider_css['testimonial_next'].'><span>next</span></a>
		</div>
	</div>';
	
	$line_breaks = array("\r\n", "\n", "\r");
	$slider_html = str_replace($line_breaks, "", $slider_html);
	
	if($echo == '1')  {echo $slider_html; }
	else { return $slider_html; }	
}

function testimonial_carousel_posts_on_slider($max_posts, $offset=0, $slider_id = '1',$out_echo = '1',$set='', $data=array() ) {
    global $testimonial_slider,$default_testimonial_slider_settings;
	$testimonial_slider_options='testimonial_slider_options'.$set;
    $testimonial_slider_curr=get_option($testimonial_slider_options);
	if(!isset($testimonial_slider_curr) or !is_array($testimonial_slider_curr) or empty($testimonial_slider_curr)){$testimonial_slider_curr=$testimonial_slider;$set='';}
	
	foreach($default_testimonial_slider_settings as $key=>$value){
		if(!isset($testimonial_slider_curr[$key])) $testimonial_slider_curr[$key]='';
	}
	
	global $wpdb, $table_prefix;
	$table_name = $table_prefix.TESTIMONIAL_SLIDER_TABLE;
	$post_table = $table_prefix."posts";
	$rand = $testimonial_slider_curr['rand'];
	if(isset($rand) and $rand=='1'){
	  $orderby = 'RAND()';
	}
	else {
	  $orderby = 'a.slide_order ASC, a.date DESC';
	}
	
	$posts = $wpdb->get_results("SELECT * FROM 
	                             $table_name a LEFT OUTER JOIN $post_table b 
								 ON a.post_id = b.ID 
								 WHERE ( b.post_status = 'publish' AND b.post_type='testimonial' ) AND a.slider_id = '$slider_id' 
	                             ORDER BY ".$orderby." LIMIT $offset, $max_posts", OBJECT);

	$r_array=testimonial_global_posts_processor( $posts, $testimonial_slider_curr, $out_echo,$set , $data );
	return $r_array;
}

function get_testimonial_slider($slider_id='',$set='',$offset=0, $data=array() ) {
    global $testimonial_slider,$default_testimonial_slider_settings; 
 	$testimonial_slider_options='testimonial_slider_options'.$set;
    $testimonial_slider_curr=get_option($testimonial_slider_options);
	if(!isset($testimonial_slider_curr) or !is_array($testimonial_slider_curr) or empty($testimonial_slider_curr)){$testimonial_slider_curr=$testimonial_slider;$set='';}
	
	foreach($default_testimonial_slider_settings as $key=>$value){
		if(!isset($testimonial_slider_curr[$key])) $testimonial_slider_curr[$key]='';
	}
	
	if( !$offset or empty($offset) or !is_numeric($offset)  ) {
		$offset=0;
	}
	 
	if($testimonial_slider['multiple_sliders'] == '1' and is_singular() and (empty($slider_id) or !isset($slider_id))){
		global $post;
		$post_id = $post->ID;
		$slider_id = get_testimonial_slider_for_the_post($post_id);
	}
	if(empty($slider_id) or !isset($slider_id)){
		$slider_id = '1';
	}
	if(!empty($slider_id)){
		$slider_handle='testimonial_slider_'.$slider_id;
		$data['slider_handle']=$slider_handle;
		$r_array = testimonial_carousel_posts_on_slider($testimonial_slider_curr['no_posts'], $offset, $slider_id, '0', $set, $data); 
		get_global_testimonial_slider($slider_handle,$r_array,$testimonial_slider_curr,$set,$echo='1',$data);
	} //end of not empty slider_id condition
}

//For displaying category specific posts in chronologically reverse order
function testimonial_carousel_posts_on_slider_category($max_posts='5', $catg_slug='', $offset=0, $out_echo = '1', $set='') {
    global $testimonial_slider,$default_testimonial_slider_settings;
	$testimonial_slider_options='testimonial_slider_options'.$set;
    $testimonial_slider_curr=get_option($testimonial_slider_options);
	if(!isset($testimonial_slider_curr) or !is_array($testimonial_slider_curr) or empty($testimonial_slider_curr)){$testimonial_slider_curr=$testimonial_slider;$set='';}
	
	foreach($default_testimonial_slider_settings as $key=>$value){
		if(!isset($testimonial_slider_curr[$key])) $testimonial_slider_curr[$key]='';
	}
	
	global $wpdb, $table_prefix;
	
	$rand = $testimonial_slider_curr['rand'];
	if(isset($rand) and $rand=='1'){
	  $orderby = 'rand';
	}
	else {
	  $orderby = 'post_date';
	}
	
	$posts = get_posts( array(
	'numberposts'     => $max_posts,
    'offset'          => $offset,
	'orderby'		  => $orderby,
    'post_type'       => 'testimonial',
    'post_status'     => 'publish',
	'tax_query' => array(
			array(
				'taxonomy' => 'testimonial_category',
				'field' => 'slug',
				'terms' => $catg_slug
			)
		)
	)
	);
	
	$r_array=testimonial_global_posts_processor( $posts, $testimonial_slider_curr, $out_echo,$set,$data );
	return $r_array;
}

function get_testimonial_slider_category($catg_slug='', $set='', $offset=0, $data=array() ) {
    global $testimonial_slider,$default_testimonial_slider_settings; 
 	$testimonial_slider_options='testimonial_slider_options'.$set;
    $testimonial_slider_curr=get_option($testimonial_slider_options);
	if(!isset($testimonial_slider_curr) or !is_array($testimonial_slider_curr) or empty($testimonial_slider_curr)){$testimonial_slider_curr=$testimonial_slider;$set='';}
	
	foreach($default_testimonial_slider_settings as $key=>$value){
		if(!isset($testimonial_slider_curr[$key])) $testimonial_slider_curr[$key]='';
	}
	
	if( !$offset or empty($offset) or !is_numeric($offset)  ) {
		$offset=0;
	}
	
	$slider_handle='testimonial_slider_'.$catg_slug;
	$data['slider_handle']=$slider_handle;
    $r_array = testimonial_carousel_posts_on_slider_category($testimonial_slider_curr['no_posts'], $catg_slug, $offset, '0', $set, $data); 
	get_global_testimonial_slider($slider_handle,$r_array,$testimonial_slider_curr,$set,$echo='1',$data);
} 

//For displaying recent posts in chronologically reverse order
function testimonial_carousel_posts_on_slider_recent($max_posts='5', $offset=0, $out_echo = '1', $set='', $data=array() ) {
    global $testimonial_slider,$default_testimonial_slider_settings;
	$testimonial_slider_options='testimonial_slider_options'.$set;
    $testimonial_slider_curr=get_option($testimonial_slider_options);
	if(!isset($testimonial_slider_curr) or !is_array($testimonial_slider_curr) or empty($testimonial_slider_curr)){$testimonial_slider_curr=$testimonial_slider;$set='';}
	
	foreach($default_testimonial_slider_settings as $key=>$value){
		if(!isset($testimonial_slider_curr[$key])) $testimonial_slider_curr[$key]='';
	}
	
	$posts = get_posts( array(
	'numberposts'     => $max_posts,
    'offset'          => $offset,
    'post_type'       => 'testimonial',
    'post_status'     => 'publish'	)
	);
	
	$rand = $testimonial_slider_curr['rand'];
	if(isset($rand) and $rand=='1'){
	  shuffle($posts);
	}
	
	
	$r_array=testimonial_global_posts_processor( $posts, $testimonial_slider_curr, $out_echo,$set,$data );
	return $r_array;
}

function get_testimonial_slider_recent($set='', $offset=0, $data=array() ) {
	global $testimonial_slider,$default_testimonial_slider_settings; 
 	$testimonial_slider_options='testimonial_slider_options'.$set;
    $testimonial_slider_curr=get_option($testimonial_slider_options);
	if(!isset($testimonial_slider_curr) or !is_array($testimonial_slider_curr) or empty($testimonial_slider_curr)){$testimonial_slider_curr=$testimonial_slider;$set='';}
	
	foreach($default_testimonial_slider_settings as $key=>$value){
		if(!isset($testimonial_slider_curr[$key])) $testimonial_slider_curr[$key]='';
	}
	
	if( !$offset or empty($offset) or !is_numeric($offset)  ) {
		$offset=0;
	}
	$slider_handle='testimonial_slider_recent';
	$r_array = testimonial_carousel_posts_on_slider_recent($testimonial_slider_curr['no_posts'], $offset, '0', $set, $data);
	get_global_testimonial_slider($slider_handle,$r_array,$testimonial_slider_curr,$set,$echo='1',$data);
} 

require_once (dirname (__FILE__) . '/shortcodes_1.php');
require_once (dirname (__FILE__) . '/widgets_1.php');

function testimonial_slider_enqueue_scripts() {
	wp_enqueue_script( 'testimonial', testimonial_slider_plugin_url( 'js/testimonial.js' ),
		array('jquery'), TESTIMONIAL_SLIDER_VER, false);
	wp_enqueue_script( 'jquery.cycle', testimonial_slider_plugin_url( 'js/jquery.cycle.js' ),
		array('jquery'), TESTIMONIAL_SLIDER_VER, false);
	wp_enqueue_script( 'easing', testimonial_slider_plugin_url( 'js/jquery.easing.js' ),
		false, TESTIMONIAL_SLIDER_VER, false);
	wp_enqueue_script( 'jquery.touchwipe', testimonial_slider_plugin_url( 'js/jquery.touchwipe.js' ),
		array('jquery'), TESTIMONIAL_SLIDER_VER, false);
}

add_action( 'init', 'testimonial_slider_enqueue_scripts' );

function testimonial_slider_enqueue_styles() {	
  global $post, $testimonial_slider, $wp_registered_widgets,$wp_widget_factory;
  if(is_singular()) {
	 $testimonial_slider_style = get_post_meta($post->ID,'testimonial_slider_style',true);
	 if((is_active_widget(false, false, 'testimonial_sslider_wid', true) or isset($testimonial_slider['shortcode']) ) and (!isset($testimonial_slider_style) or empty($testimonial_slider_style))){
	   $testimonial_slider_style='default';
	 }
	 if (!isset($testimonial_slider_style) or empty($testimonial_slider_style) ) {
	     wp_enqueue_style( 'testimonial_slider_headcss', testimonial_slider_plugin_url( 'css/skins/'.$testimonial_slider['stylesheet'].'/style.css' ),
		false, TESTIMONIAL_SLIDER_VER, 'all');
	 }
     else {
	     wp_enqueue_style( 'testimonial_slider_headcss', testimonial_slider_plugin_url( 'css/skins/'.$testimonial_slider_style.'/style.css' ),
		false, TESTIMONIAL_SLIDER_VER, 'all');
	}
  }
  else {
     $testimonial_slider_style = $testimonial_slider['stylesheet'];
	wp_enqueue_style( 'testimonial_slider_headcss', testimonial_slider_plugin_url( 'css/skins/'.$testimonial_slider_style.'/style.css' ),
		false, TESTIMONIAL_SLIDER_VER, 'all');
  }
}
add_action( 'wp', 'testimonial_slider_enqueue_styles' );

//admin settings
function testimonial_slider_admin_scripts() {
global $testimonial_slider;
  if ( is_admin() ){ // admin actions
  // Settings page only
	if ( isset($_GET['page']) && ('testimonial-slider-admin' == $_GET['page'] or 'testimonial-slider-settings' == $_GET['page'] )  ) {
	wp_register_script('jquery', false, false, false, false);
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'testimonial_slider_admin_js', testimonial_slider_plugin_url( 'js/admin.js' ),
		array('jquery'), TESTIMONIAL_SLIDER_VER, false);
	wp_enqueue_style( 'testimonial_slider_admin_css', testimonial_slider_plugin_url( 'css/admin.css' ),
		false, TESTIMONIAL_SLIDER_VER, 'all');
	wp_enqueue_script( 'testimonial', testimonial_slider_plugin_url( 'js/testimonial.js' ),
		array('jquery'), TESTIMONIAL_SLIDER_VER, false);
	wp_enqueue_script( 'jquery.cycle', testimonial_slider_plugin_url( 'js/jquery.cycle.js' ),
		array('jquery'), TESTIMONIAL_SLIDER_VER, false);
	wp_enqueue_script( 'easing', testimonial_slider_plugin_url( 'js/jquery.easing.js' ),
		false, TESTIMONIAL_SLIDER_VER, false);  
	wp_enqueue_style( 'testimonial_slider_admin_head_css', testimonial_slider_plugin_url( 'css/skins/'.$testimonial_slider['stylesheet'].'/style.css' ),
		false, TESTIMONIAL_SLIDER_VER, 'all');
	}
  }
}

add_action( 'admin_init', 'testimonial_slider_admin_scripts' );

function testimonial_slider_admin_head() {
global $testimonial_slider;
if ( is_admin() ){ // admin actions
   
// Sliders & Settings page only
    if ( isset($_GET['page']) && ('testimonial-slider-admin' == $_GET['page'] or 'testimonial-slider-settings' == $_GET['page']) ) {
	  $sliders = testimonial_ss_get_sliders(); 
		global $testimonial_slider;
		$cntr='';
		if(isset($_GET['scounter'])) $cntr = $_GET['scounter'];
		$testimonial_slider_options='testimonial_slider_options'.$cntr;
		$testimonial_slider_curr=get_option($testimonial_slider_options);
		$active_tab=(isset($testimonial_slider_curr['active_tab']))?$testimonial_slider_curr['active_tab']:0;
		if ( isset($_GET['page']) && ('testimonial-slider-admin' == $_GET['page']) && isset($_POST['active_tab']) ) $active_tab=$_POST['active_tab'];
	?>
		<script type="text/javascript">
            // <![CDATA[
        jQuery(document).ready(function() {
                jQuery(function() {
					jQuery("#slider_tabs").tabs({fx: { opacity: "toggle", duration: 300}, active: <?php echo $active_tab;?> }).addClass( "ui-tabs-vertical-left ui-helper-clearfix" );jQuery( "#slider_tabs li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
				<?php 	if ( isset($_GET['page']) && (( 'testimonial-slider-settings' == $_GET['page']) or ('testimonial-slider-admin' == $_GET['page']) ) ) { ?>
					jQuery( "#slider_tabs" ).on( "tabsactivate", function( event, ui ) { jQuery( "#testimonial_activetab, .testimonial_activetab" ).val( jQuery( "#slider_tabs" ).tabs( "option", "active" ) ); });
				<?php 	}
				foreach($sliders as $slider){ ?>
                    jQuery("#sslider_sortable_<?php echo $slider['slider_id'];?>").sortable();
                    jQuery("#sslider_sortable_<?php echo $slider['slider_id'];?>").disableSelection();
			    <?php } ?>
                });
        });
		
        function confirmRemove()
        {
            var agree=confirm("This will remove selected Posts/Pages from Slider.");
            if (agree)
            return true ;
            else
            return false ;
        }
        function confirmRemoveAll()
        {
            var agree=confirm("Remove all Posts/Pages from Testimonial Slider??");
            if (agree)
            return true ;
            else
            return false ;
        }
        function confirmSliderDelete()
        {
            var agree=confirm("Delete this Slider??");
            if (agree)
            return true ;
            else
            return false ;
        }
        function slider_checkform ( form )
        {
          if (form.new_slider_name.value == "") {
            alert( "Please enter the New Slider name." );
            form.new_slider_name.focus();
            return false ;
          }
          return true ;
        }
        </script>
<?php
   } //Sliders page only
   
   // Settings page only
  if ( isset($_GET['page']) && 'testimonial-slider-settings' == $_GET['page']  ) {
		wp_print_scripts( 'farbtastic' );
		wp_print_styles( 'farbtastic' );
?>
<script type="text/javascript">
	// <![CDATA[
jQuery(document).ready(function() {
		jQuery('#colorbox_1').farbtastic('#color_value_1');
		jQuery('#color_picker_1').click(function () {
           if (jQuery('#colorbox_1').css('display') == "block") {
		      jQuery('#colorbox_1').fadeOut("slow"); }
		   else {
		      jQuery('#colorbox_1').fadeIn("slow"); }
        });
		var colorpick_1 = false;
		jQuery(document).mousedown(function(){
		    if (colorpick_1 == true) {
    			return; }
				jQuery('#colorbox_1').fadeOut("slow");
		});
		jQuery(document).mouseup(function(){
		    colorpick_1 = false;
		});
//for second color box
		jQuery('#colorbox_2').farbtastic('#color_value_2');
		jQuery('#color_picker_2').click(function () {
           if (jQuery('#colorbox_2').css('display') == "block") {
		      jQuery('#colorbox_2').fadeOut("slow"); }
		   else {
		      jQuery('#colorbox_2').fadeIn("slow"); }
        });
		var colorpick_2 = false;
		jQuery(document).mousedown(function(){
		    if (colorpick_2 == true) {
    			return; }
				jQuery('#colorbox_2').fadeOut("slow");
		});
		jQuery(document).mouseup(function(){
		    colorpick_2 = false;
		});
//for third color box
		jQuery('#colorbox_3').farbtastic('#color_value_3');
		jQuery('#color_picker_3').click(function () {
           if (jQuery('#colorbox_3').css('display') == "block") {
		      jQuery('#colorbox_3').fadeOut("slow"); }
		   else {
		      jQuery('#colorbox_3').fadeIn("slow"); }
        });
		var colorpick_3 = false;
		jQuery(document).mousedown(function(){
		    if (colorpick_3 == true) {
    			return; }
				jQuery('#colorbox_3').fadeOut("slow");
		});
		jQuery(document).mouseup(function(){
		    colorpick_3 = false;
		});
		//for third=>child color box
			jQuery('#colorbox_31').farbtastic('#color_value_31');
			jQuery('#color_picker_31').click(function () {
			   if (jQuery('#colorbox_31').css('display') == "block") {
				  jQuery('#colorbox_31').fadeOut("slow"); }
			   else {
				  jQuery('#colorbox_31').fadeIn("slow"); }
			});
			var colorpick_31 = false;
			jQuery(document).mousedown(function(){
				if (colorpick_31 == true) {
					return; }
					jQuery('#colorbox_31').fadeOut("slow");
			});
			jQuery(document).mouseup(function(){
				colorpick_31 = false;
			});
//for fourth color box
		jQuery('#colorbox_4').farbtastic('#color_value_4');
		jQuery('#color_picker_4').click(function () {
           if (jQuery('#colorbox_4').css('display') == "block") {
		      jQuery('#colorbox_4').fadeOut("slow"); }
		   else {
		      jQuery('#colorbox_4').fadeIn("slow"); }
        });
		var colorpick_4 = false;
		jQuery(document).mousedown(function(){
		    if (colorpick_4 == true) {
    			return; }
				jQuery('#colorbox_4').fadeOut("slow");
		});
		jQuery(document).mouseup(function(){
		    colorpick_4 = false;
		});
//for fifth color box
		jQuery('#colorbox_5').farbtastic('#color_value_5');
		jQuery('#color_picker_5').click(function () {
           if (jQuery('#colorbox_5').css('display') == "block") {
		      jQuery('#colorbox_5').fadeOut("slow"); }
		   else {
		      jQuery('#colorbox_5').fadeIn("slow"); }
        });
		var colorpick_5 = false;
		jQuery(document).mousedown(function(){
		    if (colorpick_5 == true) {
    			return; }
				jQuery('#colorbox_5').fadeOut("slow");
		});
		jQuery(document).mouseup(function(){
		    colorpick_5 = false;
		});
//for sixth color box
		jQuery('#colorbox_6').farbtastic('#color_value_6');
		jQuery('#color_picker_6').click(function () {
           if (jQuery('#colorbox_6').css('display') == "block") {
		      jQuery('#colorbox_6').fadeOut("slow"); }
		   else {
		      jQuery('#colorbox_6').fadeIn("slow"); }
        });
		var colorpick_6 = false;
		jQuery(document).mousedown(function(){
		    if (colorpick_6 == true) {
    			return; }
				jQuery('#colorbox_6').fadeOut("slow");
		});
		jQuery(document).mouseup(function(){
		    colorpick_6 = false;
		});
		jQuery('#sldr_close').click(function () {
			jQuery('#sldr_message').fadeOut("slow");
		});
});
function confirmSettingsCreate()
        {
            var agree=confirm("Create New Settings Set??");
            if (agree)
            return true ;
            else
            return false ;
}
function confirmSettingsDelete()
        {
            var agree=confirm("Delete this Settings Set??");
            if (agree)
            return true ;
            else
            return false ;
}
</script>
<style type="text/css">
.color-picker-wrap {
		position: absolute;
 		display: none; 
		background: #fff;
		border: 3px solid #ccc;
		padding: 3px;
		z-index: 1000;
	}
</style>
<?php
   } //for testimonial slider option page
 }//only for admin
}
add_action('admin_head', 'testimonial_slider_admin_head');

//get inline css with style attribute attached
function testimonial_get_inline_css($set='',$echo='0'){
    global $testimonial_slider,$default_testimonial_slider_settings;
	$testimonial_slider_options='testimonial_slider_options'.$set;
    $testimonial_slider_curr=get_option($testimonial_slider_options);
	if(!isset($testimonial_slider_curr) or !is_array($testimonial_slider_curr) or empty($testimonial_slider_curr)){$testimonial_slider_curr=$testimonial_slider;$set='';}
	
	foreach($default_testimonial_slider_settings as $key=>$value){
		if(!isset($testimonial_slider_curr[$key])) $testimonial_slider_curr[$key]='';
	}
	
	$testimonial_slider_css=array();
	
	$style_start= ($echo=='0') ? 'style="':'';
	$style_end= ($echo=='0') ? '"':'';
	//testimonial_slider
	$total_width='';
	if(isset($testimonial_slider_curr['width']) and $testimonial_slider_curr['width']!=0) {
		$total_width='width:'. $testimonial_slider_curr['width'].'px;';
		$testimonial_slider_css['testimonial_slider']=$style_start.$total_width.$style_end;
	}
	else{
		$testimonial_slider_css['testimonial_slider']='';
	}

	//testimonial_slideri
	if ($testimonial_slider_curr['bg'] == '1') { $testimonial_slideri_bg = "transparent";} else { $testimonial_slideri_bg = $testimonial_slider_curr['bg_color']; }
	$testimonial_slider_css['testimonial_slideri']=$style_start.'background-color:'.$testimonial_slideri_bg.';border:'.$testimonial_slider_curr['border'].'px solid '.$testimonial_slider_curr['brcolor'].';width:'. $testimonial_slider_curr['iwidth'].'px;height:'. $testimonial_slider_curr['height'].'px;'.$style_end;
	
	$testimonial_slider_css['testimonial_avatar_img']=$style_start.'max-height:'.$testimonial_slider_curr['img_height'].'px;width:'.$testimonial_slider_curr['img_width'].'px;border:'.$testimonial_slider_curr['img_border'].'px solid '.$testimonial_slider_curr['img_brcolor'].';'.$style_end;
	
	if ($testimonial_slider_curr['ptitle_fstyle'] == "bold" or $testimonial_slider_curr['ptitle_fstyle'] == "bold italic" ){$ptitle_fweight = "bold";} else {$ptitle_fweight = "normal";}
	if ($testimonial_slider_curr['ptitle_fstyle'] == "italic" or $testimonial_slider_curr['ptitle_fstyle'] == "bold italic"){$ptitle_fstyle = "italic";} else {$ptitle_fstyle = "normal";}
	$testimonial_slider_css['testimonial_by']=$style_start.'line-height:'. ($testimonial_slider_curr['ptitle_fsize'] + 3) .'px;font-family:'. $testimonial_slider_curr['ptitle_font'].';font-size:'.$testimonial_slider_curr['ptitle_fsize'].'px;font-weight:'.$ptitle_fweight.';font-style:'.$ptitle_fstyle.';color:'.$testimonial_slider_curr['ptitle_fcolor'].';'.$style_end;
	
	if ($testimonial_slider_curr['psite_fstyle'] == "bold" or $testimonial_slider_curr['psite_fstyle'] == "bold italic" ){$psite_fweight = "bold";} else {$psite_fweight = "normal";}
	if ($testimonial_slider_curr['psite_fstyle'] == "italic" or $testimonial_slider_curr['psite_fstyle'] == "bold italic"){$psite_fstyle = "italic";} else {$psite_fstyle = "normal";}
	$testimonial_slider_css['testimonial_site_a']=$style_start.'line-height:'. ($testimonial_slider_curr['psite_fsize'] + 3) .'px;font-family:'. $testimonial_slider_curr['psite_font'].';font-size:'.$testimonial_slider_curr['psite_fsize'].'px;font-weight:'.$psite_fweight.';font-style:'.$psite_fstyle.';color:'.$testimonial_slider_curr['psite_fcolor'].';'.$style_end;
	
	$quote_bg_url='css/skins/'.$testimonial_slider['stylesheet'].'/buttons/'.$testimonial_slider_curr['buttons'].'/quote.png';
	if ($testimonial_slider_curr['content_fstyle'] == "bold" or $testimonial_slider_curr['content_fstyle'] == "bold italic" ){$content_fweight= "bold";} else {$content_fweight= "normal";}
	if ($testimonial_slider_curr['content_fstyle']=="italic" or $testimonial_slider_curr['content_fstyle'] == "bold italic"){$content_fstyle= "italic";} else {$content_fstyle= "normal";}
	$testimonial_slider_css['testimonial_quote']=$style_start.'background:url('.testimonial_slider_plugin_url( $quote_bg_url ) .') left top no-repeat;font-family:'.$testimonial_slider_curr['content_font'].';font-size:'.$testimonial_slider_curr['content_fsize'].'px;font-weight:'.$content_fweight.';font-style:'.$content_fstyle.';color:'. $testimonial_slider_curr['content_fcolor'].';'.$style_end;
	
	//testimonial_nav_a
	$button_url='css/skins/'.$testimonial_slider['stylesheet'].'/buttons/'.$testimonial_slider_curr['buttons'].'/nav.png';
	$testimonial_slider_css['testimonial_nav_a']=$style_start.'background: transparent url('.testimonial_slider_plugin_url( $button_url ) .') no-repeat;width:'.$testimonial_slider_curr['navimg_w'].'px;height:'.$testimonial_slider_curr['navimg_h'].'px;'.$style_end;
	
	//testimonial_next
	$nexturl='css/skins/'.$testimonial_slider['stylesheet'].'/buttons/'.$testimonial_slider_curr['buttons'].'/next.png';
	$testimonial_slider_css['testimonial_next']=$style_start.'background: transparent url('.testimonial_slider_plugin_url( $nexturl ) .') no-repeat 0 0;'.$style_end;
	//testimonial_prev
	$prevurl='css/skins/'.$testimonial_slider['stylesheet'].'/buttons/'.$testimonial_slider_curr['buttons'].'/prev.png';
	$testimonial_slider_css['testimonial_prev']=$style_start.'background: transparent url('.testimonial_slider_plugin_url( $prevurl ) .') no-repeat 0 0;'.$style_end;
	
	//currently empty values
	$testimonial_slider_css['testimonial_by_wrap']='';
	$testimonial_slider_css['testimonial_avatar']='';
	$testimonial_slider_css['testimonial_slider_span']='';
	$testimonial_slider_css['testimonial_nav']='';

	return $testimonial_slider_css;
}

function testimonial_slider_css() {
global $testimonial_slider;
$css=$testimonial_slider['css'];
if($css and !empty($css)){?>
 <style type="text/css"><?php echo $css;?></style>
<?php }
}
add_action('wp_head', 'testimonial_slider_css');
add_action('admin_head', 'testimonial_slider_css');
?>