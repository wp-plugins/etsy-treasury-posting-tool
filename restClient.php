<?php

/**
 * REST client class
 *
 */
class Rest_Client {
  
  /**
   * Constant storing API URI
   *
   */
  const API_URI = "http://openapi.etsy.com/v2/public/";
  
  /**
   * Constant storing API key
   *
   */
  const API_KEY = "7niwrw9ideo36xtfq1l6wgs2";
  
  /**
   * cURL handle
   *
   * @var unknown_type
   */
  private $curl;
  
  /**
  * Constant storing CURLOPT_USERAGENT
  *
  */
  const USER_AGENT = 'RESTClient';
  
  /**
   * Constructor for Rest_Client class
   *
   */
  public function __construct() {
    $this->curl = curl_init(); 
  }
  
  /**
   * Run GET operation
   *
   * @param String $url
   * @return Array
   */
  public function get( $url ) {
    $result = "";
    
    curl_setopt($this->curl, CURLOPT_URL, $url);
    curl_setopt($this->curl, CURLOPT_USERAGENT, self::USER_AGENT);
    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, TRUE);
    $result = curl_exec($this->curl);
    
    return json_decode( $result );
  }
  
  /**
   * Destructor of Rest_Client class
   *
   */
  public function __destruct() {
    curl_close($this->curl);
  }
  
  
}