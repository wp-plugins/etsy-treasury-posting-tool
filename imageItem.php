<?php

require_once('restClient.php');

class Image_Item {
  
  /**
   * Constant storing large image index
   *
   */
  const LARGE_IMAGE_URI_INDEX = 'url_170x135';
  
  /**
   * Constant storing very large image index
   *
   */
  const VERY_LARGE_IMAGE_URI_INDEX = 'url_570xN';
  
  /**
   * Title 
   *
   * @var String
   */
  private $title;
  
  /**
   * Listing id
   *
   * @var String
   */
  private $listing_id;
  
  /**
   * Shop name
   *
   * @var String
   */
  private $shop_name;
  
  /**
   * Image id
   *
   * @var int
   */
  private $image_id;
  
  /**
   * Image object (1:1 relationship)
   *
   * @var Image
   */
  private $image_url;
  
  /**
   * Image size
   *
   * @var int
   */
  private $image_size;
  
  /**
   * Price
   *
   * @var float
   */
  private $price;
  
  
  /**
   * Get title
   *
   * @return unknown
   */
  public function get_title() {
    return $this->title;
  }
  
  /**
   * Set title
   *
   * @param String $title
   */
  public function set_title( $title ) {
    $this->title = $title;
  }
  
  /**
   * Get listing id
   *
   * @return unknown
   */
  public function get_listing_id() {
    return $this->listing_id;
  }
  
  /**
   * Set listing id
   *
   * @param String $listing_id
   */
  public function set_listing_id( $listing_id ) {
    $this->listing_id = $listing_id;
  }
  
  /**
   * Get shop name
   *
   * @return unknown
   */
  public function get_shop_name() {
    return $this->shop_name;
  }
  
  /**
   * Set shop name
   *
   */
  public function set_shop_name( $shop_name ) {
    $this->shop_name = $shop_name;
  }
  
  /**
   * Get image id
   *
   * @return int
   */
  public function get_image_id() {
    return $this->image_id;
  }
  
  /**
   *Set image id
   *
   * @param int $image_id
   */
  public function set_image_id( $image_id ) {
    $this->image_id = $image_id;
  }
  
  /**
   * Get image url
   *
   * @return String
   */
  public function get_image_url() {
    return $this->image_url;
  }
  
  /**
   * Set image url
   *
   * @param String $image_url
   */
  public function set_image_url( $image_url ) {
    $this->image_url = $image_url;
  }
  
  /**
   * Get price
   *
   * @return float
   */
  public function get_price() {
      return $this->price;
  }
  
  /**
   * Set price
   *
   * @param float $price
   */
  public function set_price( $price ) {
      $this->price = $price;
  }
  
  /**
  * Get listing image api URI
  *
  * @param String $listing_id
  * @param String $image_id
  * @return String
  */
  private function get_listing_image_uri() {
    return Rest_Client::API_URI . "listings/" . $this->listing_id . "/images/" . $this->image_id . "?api_key=" . Rest_Client::API_KEY;
  }
  
  /**
   * Get Listing Image
   *
   * @param String $listing_id
   * @param String $image_id
   * @return Array
   */
  public function get_listing_image() {
    $uri = $this->get_listing_image_uri();
    $client = new Rest_Client();
    $obj_image_list = $client->get($uri);
    $obj_image_list_results = $obj_image_list->results;
    
    
    return current($obj_image_list_results);
  }
  
}