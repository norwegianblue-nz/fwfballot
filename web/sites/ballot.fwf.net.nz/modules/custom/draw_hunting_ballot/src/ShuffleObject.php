<?php

namespace Drupal\draw_hunting_ballot;

class ShuffleObject



{
  private $obj;
      
  public function __construct($obj)
  {
    $this->obj = $obj;
  }
  
  public function shuffleObjects()
  {
    $objarray = (array)$this->obj;
    shuffle($objarray);
    return (object)$objarray;
  }
}
