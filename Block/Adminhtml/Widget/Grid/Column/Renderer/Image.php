<?php
class Mejora_Instashop_Block_Adminhtml_Widget_Grid_Column_Renderer_Image
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $html = '';
        $html .= "<image src=".$row->getImage()." width=\"100\" height=\"100\">";
        return $html;
    }
}