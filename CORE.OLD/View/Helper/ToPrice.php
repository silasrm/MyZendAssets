<?php

class CORE_View_Helper_ToPrice
{
  public function toPrice( $value )
  {
    return number_format($value, 2, ",", ".");
  }
}