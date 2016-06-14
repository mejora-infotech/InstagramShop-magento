<?php
class Mejora_Instashop_Model_Mysql4_Instagram extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("instashop/instagram", "id");
    }
}