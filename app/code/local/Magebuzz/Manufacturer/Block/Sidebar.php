<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/

class Magebuzz_Manufacturer_Block_Sidebar extends Magebuzz_Manufacturer_Block_Manufacturer
{
  public function _construct()
  {
    $this->setTemplate('magebuzz/manufacturer/sidebar.phtml');
    return parent::_construct();
  }
}
