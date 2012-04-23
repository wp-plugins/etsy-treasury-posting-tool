<?php
/*
Plugin Name: Etsy Treasury
Plugin URI:  http://stylishhome.com/etsy-treasury-posting-tool
Description: Inserts images from Etsy Treasury using short code method.
Version: 0.1 
Author: Renjith Valsa
Author URI: http://stylishhome.com
*/

/*
Copyright 2012 Renjith Valsa (email : renjith@qburst.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/




require_once('util.php');
require_once('treasury.php');
require_once('imageItem.php');
require_once('etsyHtmlGenerator.php');


//tells WP to register the sh-etsy-treasury shortcode
add_shortcode("sh-etsy-treasury", "sh_etsy_treasury_handler");
add_filter( 'tiny_mce_before_init', 'sh_tiny_mce_before_init' );
add_filter( 'mce_buttons', 'sh_mce_buttons' );
add_action('admin_head', 'sh_tinymce_ajax_hack');
add_action('wp_ajax_get_tinymce_popup', 'sh_process_ajax_request');
add_action('wp_ajax_get_treasurylist', 'sh_get_treasurylist_callback');
add_action('wp_ajax_nopriv_get_treasurylist', 'sh_get_treasurylist_callback');



//Insert table during activation
function sh_etsy_treasury_install() {
  // Create table
	global $wpdb;
	$table = $wpdb->prefix . "etsy_treasury";
	if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
		$sql = "CREATE TABLE $table (
            treasury_id varchar(40) NOT NULL,
            display_format varchar(10) NOT NULL,
            image_size varchar(10) NOT NULL,
            display_columns tinyint(1) NOT NULL,
            html text NOT NULL,
            PRIMARY KEY (treasury_id,display_format,image_size,display_columns));
				";
		$wpdb->query($sql);
  }
}

// Remove table during deactivation
function sh_etsy_treasury_uninstall() {
	// Remove Table
		global $wpdb;
		$table = $wpdb->prefix . "etsy_treasury";
		$sql = "DROP TABLE $table;";
		$wpdb->query($sql);
}

//Setup activation and deactivation
register_activation_hook(__FILE__, 'sh_etsy_treasury_install');
register_deactivation_hook(__FILE__, 'sh_etsy_treasury_uninstall');

function sh_etsy_treasury_handler( $attr ) {
  //run function that actually does the work of the plugin
  $listings = sh_get_treasury_listings($attr);
  //send back text to replace shortcode in post
  return $listings;
}

function sh_get_treasury_listings( $attr ) {
  // Set default values in case attributes are missing
  $treasury_id = isset($attr['treasury']) ? $attr['treasury'] : 'NzU1MzMxOXwxOTEwNTA3MDIz';
  
  $size = $attr['size'];
  $display_format = $attr['display'];
  $column_count = $attr['columns'];
  
  $txt_html = sh_fetch_treasury($treasury_id, $display_format, $size, $column_count);
  
  if($txt_html) {
    return $txt_html;
  }
  else {
    //Build Treasury object
    $obj_treasury = new Treasury( $treasury_id, $size );
    
    //Get treasury listing html
    $obj_html_generator = new Etsy_Html_Generator($size);
    $txt_html = $obj_html_generator->generate_html($obj_treasury, $column_count, $display_format);
    sh_add_treasury_html($treasury_id, $display_format, $size, $column_count, $txt_html);
  }
  
  return $txt_html;
}

function sh_tinymce_ajax_hack() {
  $sh_base_dir = WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__), "" ,plugin_basename(__FILE__));
	echo '<script>
			var sh_code_uri = "admin-ajax.php?action=get_tinymce_popup&width=630&height=450";
			var img_uri = "'.$sh_base_dir.'sh-icon.png";
	</script>';
}

function sh_mce_buttons( $mce_buttons ) {
  $mce_buttons[] = 'sh';
  return $mce_buttons;
}

function sh_tiny_mce_before_init( $initArray ) {
	    $initArray['setup'] = <<<JS
[function(ed) {
  ed.addButton('sh', {
      title : 'StylishHome Etsy Treasury ShortCode Generator',
      image : img_uri,
      onclick : function() {
      	tb_show('StylishHome Etsy Treasury ShortCode Generator', sh_code_uri);
		//ed.selection.setContent('<h2>' + ed.selection.getContent() + '</h2>');
      }
 });
}][0]
JS;
  return $initArray;
}

// Ajax popup for Shortcode generator
function sh_process_ajax_request() {
		echo '<div style="text-align: center; margin-bottom:10px;"><h3>StylishHome Etsy Treasury ShortCode Generator</h3></div>
		<div class="QR_code_form"><form method="get" name="qr_code_gen">
		<table border="0" cellpadding="1" cellspacing="1" style="width: 100%;">
			<tbody>
			<tr>
			<td></td>
			<td id="message" style="color:#D8000C;"></td>
			</tr>
        <tr>
					<td style="width:150px;">
						'.__('Treasury ID','sqrc').'</td>
					<td>
					<input id="sh_treasury_id" name="sh_treasury_id" style="width: 260px;"/></td>
				</tr>
        <tr>
					<td style="width:150px;">
					</td>
					<td>
					Copy and paste the Etsy Treasury ID into the box. The Treasury ID is within the URL at the top of your browser when viewing a Treasury on Etsy. An example Treasury ID is shown in bold here: http://www.etsy.com/treasury/<strong>MTQwNjE5NjF8MjI1MDQ3MTY2MQ</strong>/wooden-you-like-some-of-these
					</td>
				</tr>
				<tr>
					<td style="width:150px;">
						'.__('Display Format','sqrc').':</td>
					<td>
						<select name="sh_display_format" id="sh_display_format" style="width:285px">
              <option value="complete">&nbsp;Image, Image Name, Store Name, Price</option>
              <option value="image_only">&nbsp;Images Only</option>
              <option value="tiled">&nbsp;Tiled Image Display</option>
						</select></td>
				</tr>
				<tr>
					<td style="width:150px;">
						'.__('Image Size','sqrc').':</td>
					<td>
						<select name="sh_image_size" id="sh_image_size" style="width:93px" onchange="hideSizeCols()">
              <option value="small">&nbsp;Small</option>
              <option value="medium">&nbsp;Medium</option>
              <option selected="" value="large">&nbsp;Large</option>
              <option value="very_large">&nbsp;Very Large</option>
						</select></td>
				</tr>
				<tr id="columns_row">
					<td style="width:150px;">
						'.__('Display Columns','sqrc').':</td>
					<td>
						<select name="sh_display_columns" id="sh_display_columns" style="width:45px">
              <option value="2">&nbsp;2</option>
              <option value="3">&nbsp;3</option>
              <option selected="" value="4">&nbsp;4</option>
              <option value="5">&nbsp;5</option>
              <option value="6">&nbsp;6</option>
						</select></td>
				</tr>
			<tr>	
	<td>	</td><td>	<input name="preview-submit" id="preview-submit" type="button" value="'.__('Preview','sqrc').'" /><input style="background: none repeat scroll 0 0 #E1ECC1;border: 1px solid #BBC895; color: #3D4132;display: inline;text-shadow: 0 1px 0 #F2F7E5;" name="qr-submit" id="qr-submit" type="button" value="'.__('Add ShortCode','sqrc').'" /></td></tr>
			</tbody>
		</table>
		</form>
      <div id="loading" style="display:none;">
        <p>Please wait while the treasury listings are pulled from Etsy ...</p>
      </div>
			<div id="result"></div>

		</div>
		<script>
		jQuery("#qr-submit").hide();
    function hideSizeCols() {
      var display_element = document.getElementById("sh_image_size");
      var displayVal = display_element.options[display_element.selectedIndex].value;
      if(displayVal == "very_large") {
        jQuery("#columns_row").hide(); 
      } 
      else{
        jQuery("#columns_row").show();
      }
    } 

		jQuery(document).ready(function() {
			jQuery("#qr-submit").click(function(){
				var treasury = jQuery("#sh_treasury_id").val();
				var size = jQuery("#sh_image_size").val();
				var columns = jQuery("#sh_display_columns").val();
				var display = jQuery("#sh_display_format").val();
				var shortcode = \'[\';
				shortcode += \'sh-etsy-treasury\'; 
				shortcode += \' treasury="\';
				shortcode += treasury;
				shortcode += \'" size="\';
				shortcode += size;
        shortcode += \'" columns="\';
        shortcode += columns;
        shortcode += \'" display="\';
        shortcode += display;
				shortcode += \'"]\';
				tinyMCE.activeEditor.execCommand("mceInsertContent", 0, shortcode);
				// closes Thickbox
				tb_remove();
			});
				jQuery("#preview-submit").click(function()	{
		if(jQuery("#sh_treasury_id").val()=="") {
			jQuery(".QR_code_form #message").text("Please enter treasury id.");
			return false;
		} else {
			var email = jQuery(\'#sh_treasury_id\').val();
			var display_format = jQuery(\'#sh_display_format\').val();
			var image = jQuery(\'#sh_image_size\').val();
			var columns = jQuery(\'#sh_display_columns\').val();
				var data = {
					action: \'get_treasurylist\',
					email: email,
					display_format: display_format,
					image: image,
					columns: columns
				};
				jQuery("#loading").show();
        jQuery(".QR_code_form #result").empty();
        jQuery("#qr-submit").hide();
				jQuery.post("admin-ajax.php", data,
				function(response){
				jQuery("#loading").hide();
				jQuery("#qr-submit").show();
				jQuery(".QR_code_form #message").empty();
					jQuery(".QR_code_form #result").html(response);
				});		
				return false;
			
		} 
 
	});});
		</script>
		';
		die();
}

// Preview listing
function sh_get_treasurylist_callback() {
  $treasury_id = $_POST['email'];
  $display_format = $_POST['display_format'];
  $image = $_POST['image'];
  $columns = $_POST['columns'];
    
  //Get treasury listing html
  $txt_html = sh_fetch_treasury($treasury_id, $display_format, $image, $columns);
  
  if( $txt_html ) {
    echo $txt_html;
  }
  else {
    //Build Treasury object
    $obj_treasury = new Treasury( $treasury_id, $size );
    
    $treasuryTitle = $obj_treasury->get_title();  
    if(empty($treasuryTitle)) {
      echo "<p>This treasury is not present in Etsy.</p>";
    } else {
      $obj_html_generator = new Etsy_Html_Generator($image);
      $txt_html = $obj_html_generator->generate_html($obj_treasury, $columns, $display_format);
      sh_add_treasury_html($treasury_id, $display_format, $image, $columns, $txt_html);
      echo $txt_html;
    }
  }
  die();
}

//Fetches treasury data from the database
function sh_fetch_treasury( $treasury_id, $display_format, $image_size, $display_columns ) {
  global $wpdb;
  
  // Get html field
	$result = $wpdb->get_results( $wpdb->prepare( "SELECT html FROM {$wpdb->prefix}etsy_treasury WHERE treasury_id = '%s' AND display_format = '%s' AND image_size = '%s' AND display_columns = '%d'", array( $treasury_id, $display_format, $image_size, $display_columns) ) );
	

	if(!empty($result)) {
	  $obj_html = current($result);
	  $html = $obj_html->html;
	  return $html;
	}
	else {
	  return false;
	}
	
}

//Adds new treasury info to the db
function sh_add_treasury_html( $treasury_id, $display_format, $image_size, $display_columns, $html ) {
  global $wpdb;
  
  //Inserts treasury display information and treasury html
  $result = $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}etsy_treasury (treasury_id, display_format, image_size, display_columns, html) VALUES ('%s', '%s', '%s', '%d', '%s')", array( $treasury_id, $display_format, $image_size, $display_columns, $html ) ) );
  
  
}

?>
