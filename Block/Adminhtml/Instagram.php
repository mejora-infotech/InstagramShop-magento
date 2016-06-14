<?php


class Mejora_Instashop_Block_Adminhtml_Instagram extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_instagram";
	$this->_blockGroup = "instashop";
	$this->_headerText = Mage::helper("instashop")->__("Instagram Manager");
	$this->_addButtonLabel = Mage::helper("instashop")->__("Add New Item");
	parent::__construct();
	$this->_removeButton('add');
	}

}