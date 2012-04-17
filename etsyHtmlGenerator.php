<?php

class Etsy_Html_Generator {

  /**
   * Font size
   *
   * @var String
   */
  private $font_size;
  
  /**
   * Width
   *
   * @var String
   */
  private $width;
  
  /**
   * Height
   *
   * @var String
   */
  private $height;
  
  /**
   * Tiled Height
   *
   * @var $tiled_height
   */
  private $tiled_height;
  
  /**
   * Listing title length
   *
   * @var int
   */
  private $listing_title_length;
  
  /**
   * Shop name title length
   *
   * @var int
   */
  private $shop_title_length;
  
  /**
   * Image Size
   *
   * @var String
   */
  private $image_size;
  
  /**
   * Constructor of Etsy_Html_Generator class
   *
   * @param String $image_size Image size specified by user
   */
  public function __construct( $image_size ) {
    $this->image_size = $this->check_image_size($image_size);
    $this->set_display_parameters();  
  }
  
  /**
   * Generate treasury listing
   *
   * @param Treasury $obj_treasury Treasury object
   * @param Int $column_count No of columns to diplay in the treasury listing
   * @param String $display_format Display format required
   * @return String
   */
  public function generate_html( $obj_treasury, $column_count, $display_format ) {
    
    $arr_listing_list = $obj_treasury->get_listing_list();
    if( !empty($arr_listing_list) ) {
      $no_of_items = sizeof($arr_listing_list);
      $listings = new ArrayIterator($arr_listing_list);
      $display_format = $this->check_display_value($display_format);      
      if( $this->image_size == "very_large" ) {
        $columns = 1;
      } else {
        $columns = $this->check_column_value($column_count);
      }
      
      $txt_html = $this->get_header_tag_html($obj_treasury);
      
      $txt_html .= $this->get_table_header_html($display_format);
      
      $txt_html .= $this->get_table_rows_html($obj_treasury, $listings, $no_of_items, $columns, $display_format);
      
      // Adding table closure
      $txt_html .= '</tbody></table>';
      
      // Concatenating Links to provider info
      $txt_html .= '<p style="color: rgb(178, 178, 178); font-size: 10px; font-family: sans-serif; margin-left: 10px;"><a href="http://www.stylishhome.com/etsy-treasury-posting-tool"> Treasury tool </a> by <a href="http://www.stylishhome.com">StylishHome</a>.</p>';;
      
      return $txt_html;
    
    }
  }
  
  /**
   * Get H2 tag html
   *
   * @param Treasury $obj_treasury Treasury object
   * @return String
   */
  private function get_header_tag_html( $obj_treasury ) {
    return '<h2 style="font-size: 16px; font-family: sans-serif; margin-left: 10px;"><a style="color: rgb(51, 51, 51); text-decoration: none;" onmouseover="this.style.textDecoration=\'underline\'" onmouseout="this.style.textDecoration=\'none\'" href="http://www.etsy.com/treasury/'.$obj_treasury->get_id().' ">\''.$obj_treasury->get_title().'\'</a> by <a style="color: rgb(51, 51, 51); text-decoration: none;" onmouseover="this.style.textDecoration=\'underline\'" onmouseout="this.style.textDecoration=\'none\'" href="http://www.etsy.com/shop/'.$obj_treasury->get_shop().'">'.$obj_treasury->get_shop().'</a></h2><h2 style="font-size: 16px; font-family: sans-serif; margin-left: 10px;">'.$obj_treasury->get_description().'<br></h2>';  
  }
  
  /**
   * Get Table header
   *
   * @param String $display_format Display format of treasury listing
   * @return String
   */
  private function get_table_header_html( $display_format ) {
    if( $display_format == "tiled" ) {
      return '<table style="width:auto;border-collapse:collapse;border:0;"><tbody>';
    } else {
      return '<table style="border-spacing: 8px; width: auto; border-collapse: separate; line-height: 19px;"><tbody>';
    }
  
  }
  
  /**
   * Get Table rows html
   *
   * @param Treasury $obj_treasury Treasury object
   * @param Array $listings Array of listings
   * @param String $no_of_items No of individual listings
   * @param Int $columns Columns to display
   * @param String $display_format Display format
   * @return String
   */
  private function get_table_rows_html( $obj_treasury, $listings, $no_of_items, $columns, $display_format ) {
     $cnt = 0;
     $table_rows_html = '';
     
      while ($cnt < $no_of_items) {
        
        if ( $cnt == 0 ) {
       	  $table_rows_html .= '<tr>';
       	  $start = 0;
        }
       	else {
       	   $table_rows_html .= '</tr><tr>';
       	   $start = $start + $columns;
       	}
       	
        foreach ( new LimitIterator( $listings, $start, $columns) as $obj_listing ) {
        
          if( $display_format == "tiled" ) {
             $td_html = '<td style="border:0 none; margin:0; padding:0;"><a style="border:0 none; float:left; width:'.$this->width.'; height:'.$this->tiled_height.';" href="http://www.etsy.com/listing/'.$obj_listing->get_listing_id().'"> <img alt="'.$obj_listing->get_title().' - '.$obj_listing->get_shop_name().'" title="'.$obj_listing->get_title().' - '.$obj_listing->get_shop_name().'" width="'.$this->width.'" height="'.$this->tiled_height.'" style="border:0 none;" src="'.$obj_listing->get_image_url().'"/> </a>'; 

          } else {
             $td_html = '<td style="border: 1px solid rgb(236, 236, 236); padding: 6px; text-align: left; width:'.$this->width.'; height:'.$this->height.';"><a style="text-decoration: none;" href="http://www.etsy.com/listing/'.$obj_listing->get_listing_id().'" onmouseover="this.style.textDecoration=\'underline\'" onmouseout="this.style.textDecoration=\'none\'"> <img title="'.$obj_listing->get_title().' - '.$obj_listing->get_shop_name().'" alt="'.$obj_listing->get_title().' - '.$obj_listing->get_shop_name().'" style="border: medium none; padding: 0px;" src="'.$obj_listing->get_image_url().'" width="'.$this->width.'"/><br> </a>'; 

          }
          
          
        if ( $display_format == "complete" ) {  
          $td_html .= '<a style="text-decoration: none;" title="'.$obj_listing->get_title().'" href="http://www.etsy.com/listing/'.$obj_listing->get_listing_id().'" onmouseover="this.style.textDecoration=\'underline\'" onmouseout="this.style.textDecoration=\'none\'"><span style="color: rgb(102, 102, 102); font-size: '.$this->font_size.'; font-family: sans-serif;">'.Util::truncate($obj_listing->get_title(), $this->listing_title_length).'</span></a><br><div style="font-size: '.$this->font_size.'; font-family: sans-serif; float: left; margin-top: 0px; margin-bottom: 0px;"><a style="text-decoration: none; color: rgb(178, 178, 178);" title="'.$obj_listing->get_shop_name().'" href="http://www.etsy.com/shop/'.$obj_listing->get_shop_name().'" onmouseover="this.style.textDecoration=\'underline\'" onmouseout="this.style.textDecoration=\'none\'">'.Util::truncate($obj_listing->get_shop_name(), $this->shop_title_length).'</a></div><div style="color: rgb(120, 192, 66); font-size: '.$this->font_size.'; font-family: sans-serif; float: right; margin-top: 0px; margin-bottom: 0px;">'.'$'.$obj_listing->get_price().'</div>'; }
          
          $td_html .='</td>';
    
          $table_rows_html .= $td_html;
          $cnt++;
          
          if ( $cnt >= $no_of_items )
            break;
        }
      } 
      
      $table_rows_html .= '</tr>';
      
      return $table_rows_html;
  
  }
  
  /**
   * Set display parameters based on image size
   *
   */
    private function set_display_parameters() {
      switch( $this->image_size ) {
        case 'small':
          $this->width = '110px';
          $this->height = '110px';
          $this->tiled_height = '110px';
          $this->font_size = '8px';
          $this->listing_title_length = 22;
          $this->shop_title_length = 16;
          
          break;
        
        case 'medium':
          $this->width = '140px';
          $this->height = '140px';
          $this->tiled_height = '140px';
          $this->font_size = '10px';
          $this->listing_title_length = 26;
          $this->shop_title_length = 16;
          
          break;
        
        case 'large':
          $this->width = '170px';
          $this->height = '170px';
          $this->tiled_height = '135px';
          $this->font_size = '12px';
          $this->listing_title_length = 26;
          $this->shop_title_length = 16;
          
          break;
        
        case 'very_large':
          $this->width = '570px';
          $this->height = '570px';
          $this->tiled_height = '570px';
          $this->listing_title_length = 80;
          $this->shop_title_length = 60;
          
          break;
        
        default:
          $this->width = '170px';
          $this->height = '170px';
          $this->tiled_height = '135px';
          $this->font_size = '12px';
          $this->listing_title_length = 28;
          $this->shop_title_length = 16;
          
          break;
      }
    }
    
    /**
     * Checks whether the column specified is numeric and it is between 2 and 6.
     * If invalid value is found, default value is returned.
     *
     * @param int $column No of columns
     * @return int
     */
    private function check_column_value( $column ) {
      if ( ! is_numeric($column) || ( $column < 2 ) || ( $column > 6) ) {
        return 4;
      }
      
      return $column;
    }
    
    /**
     * Checks display format obtained is valid
     *
     * @param String $display Display format
     * @return String
     */
    private function check_display_value( $display ) {
      if ( ! in_array($display, array('complete', 'image_only', 'tiled')) ) {
        return 'complete';
      }
      
      return $display;
    }
    
    /**
     * Checks whether size obtained is valid
     *
     * @param String $size Image size
     * @return String
     */
    private function check_image_size( $size ) {
      if ( ! in_array($size, array('small', 'medium', 'large', 'very_large')) ) {
        return 'large';
      }
      
      return $size;
    }
}