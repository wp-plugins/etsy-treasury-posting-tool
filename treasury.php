<?php

require_once('restClient.php');

/**
 * Treasury class
 *
 */
class Treasury {
  
  /**
   * Treasury id
   *
   * @var String
   */
  private $id;
  
  /**
   * Title of the treasury
   *
   * @var String
   */
  private $title;
  
  /**
   * Description of the treasury
   *
   * @var String
   */
  private $description;
  
  /**
   *Shop name
   *
   * @var String
   */
  private $shop;
  
  /**
   * Listing object list (1:N relationship)
   *
   * @var Array
   */
  private $obj_listing_list = array();
  
  /**
   * Constructor of Treasury class
   *
   * @param String $id
   */
  public function __construct( $treasury_id, $image_size ) {
    $this->id = $treasury_id;
    $this->populateObject($image_size);
  }
  
  /**
   * Get id prooperty
   *
   * @return String
   */
  public function get_id() {
    return $this->id;
  }
  /**
   * Set the id property
   *
   * @param String $id
   */
  public function set_id( $id ) {
    $this->id = $id;
  }
  
  /**
   * Get the title property
   *
   * @return String
   */
  public function get_title() {
    return $this->title;    
  }
  
  /**
   * Set the title property
   *
   * @param String $title
   */
  public function set_title( $title ) {
    $this->title = $title;
  }
  
  /**
   * Get the description property
   *
   * @return String
   */
  public function get_description() {
    return $this->description;
  }
  
  /**
   * Set the description property
   *
   * @param String $description
   */
  public function set_description( $description ) {
    $this->description = $description;
  }
  
  /**
   * Get shop
   *
   * @return String
   */
  public function get_shop() {
      return $this->shop;
  }
  
  /**
   * Set shop
   *
   * @param String $shop
   */
  public function set_shop( $shop ) {
      $this->shop = $shop;
  }
  
  /**
   * Get Listing list
   *
   * @return Array
   */
  public function get_listing_list() {
      return $this->obj_listing_list;
  }
  
  /**
   * Set Listing list
   *
   * @param Array $obj_listing_list
   */
  public function set_listing_list( $obj_listing_list ) {
      $this->obj_listing_list = $obj_listing_list;
  }
  
  //Populate treasury object
  private function populateObject( $image_size ) {
    // Fetch results from Etsy
    $obj_result = $this->getTreasury();
    if(!empty($obj_result)) {
      $obj_treasury_details = current($obj_result->results);
      
      $this->set_title($obj_treasury_details->title);
      $this->set_description($obj_treasury_details->description);
      $this->set_shop($obj_treasury_details->user_name);
      
      $this->read_all_image_data($obj_treasury_details->listings, $image_size);
    }
  }
  
  //Get Listng images
  private function read_all_image_data( $arr_listings, $image_size ) {
    $this->obj_listing_list = array();
    $obj_client = new Rest_Client();
    $image_list_uri = $this->get_treasury_images_uri($arr_listings, $image_size);
    $obj_image_list = $obj_client->get($image_list_uri);
    
    $results = $obj_image_list->results;
    
    $image_list = array();
    
    foreach ( $results as $image ) {
      if ( is_array($image->Images) ) {
        $image_list[$image->listing_id] = current($image->Images[0]);
      } else {
        $image_list[$image->listing_id] = '';
      }
    }
    foreach ( $arr_listings as $listing ) {
      $obj_listing_data =  $listing->data;
    
      $obj_image_item = new Image_Item();
      $obj_image_item->set_listing_id($obj_listing_data->listing_id);
      $obj_image_item->set_title($obj_listing_data->title);
      $obj_image_item->set_shop_name($obj_listing_data->shop_name);
      $obj_image_item->set_price($obj_listing_data->price);
      $obj_image_item->set_image_id($obj_listing_data->image_id);
      $obj_image_item->set_image_url($image_list[$obj_listing_data->listing_id]);
    
      array_push($this->obj_listing_list, $obj_image_item);
    }  
  }
  
  /**
   * Get treasury API URI
   *
   * @return String
   */
  private function get_treasury_api_uri() {
    return Rest_Client::API_URI . "treasuries/" . $this->id . "?api_key=" . Rest_Client::API_KEY; 
  }
  
  /**
   * Get treasury images
   *
   * @param Array $arr_listings
   * @param String $image_size
   * @return String
   */
  private function get_treasury_images_uri($arr_listings, $image_size) {
    $str_listing_ids = '';
    $image_uri = Image_Item::LARGE_IMAGE_URI_INDEX;
    
    foreach ( $arr_listings as $listing ) {
      $obj_listing_data = $listing->data;
      $str_listing_ids =  $str_listing_ids . ',' . $obj_listing_data->listing_id;
    }
    
    $str_listing_ids = ltrim($str_listing_ids, ',');
    if ( $image_size == "very_large" ) {
      $image_uri = Image_Item::VERY_LARGE_IMAGE_URI_INDEX;
    }
    else {
      $image_uri = Image_Item::LARGE_IMAGE_URI_INDEX;
    }
    
    return Rest_Client::API_URI . "listings/" . $str_listing_ids . "?fields=listing_id&includes=Images(" . $image_uri . ")&api_key=" . Rest_Client::API_KEY; 
  }
  
  /**
   * Get treasury
   *
   * @return Array
   */
  public function getTreasury() {
    $treasury_uri = $this->get_treasury_api_uri($this->id);
    $obj_client = new Rest_Client();
    $obj_treasury = $obj_client->get($treasury_uri);
    
    return $obj_treasury;
  }

}
