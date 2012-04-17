<?php
/**
 * Class providing utitlity methods
 *
 */
class Util {
  /**
   * Truncate string to desired length
   *
   * @param String $str String to be truncated
   * @param Int $length Max length of the text
   * @param String $trailing String to display after text is truncated
   * @return String
   */
  public static function truncate ($str, $length=13, $trailing='...') {
    // take off chars for the trailing
    $length-=mb_strlen($trailing);
    if ( mb_strlen($str)> $length ) {
      // string exceeded length, truncate and add trailing dots
      return mb_substr($str,0,$length).$trailing;
    } else {
      // string was already short enough, return the string
      $res = $str;
    }
    
    return $res;
  }
}