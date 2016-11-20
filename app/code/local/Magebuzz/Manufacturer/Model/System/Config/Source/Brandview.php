<?php
/*
* Copyright (c) 2016 www.magebuzz.com
*/
class Magebuzz_Manufacturer_Model_System_Config_Source_Brandview
{
    public function toOptionArray()
    {
        return array(
            array('value'=>0, 'label'=>Mage::helper('manufacturer')->__('Show image and name')),
            array('value'=>1, 'label'=>Mage::helper('manufacturer')->__('Show image only')),
            array('value'=>2, 'label'=>Mage::helper('manufacturer')->__('Show name only'))
        );
    }
}
