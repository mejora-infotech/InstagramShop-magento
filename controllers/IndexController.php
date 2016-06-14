<?php
class Mejora_Instashop_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {     
	 
	  
    }
	
	public function InstagramAccessTokenSaveAction() {
		if(isset($_GET['code'])) {
			$code=$_GET['code'];

			$url='https://api.instagram.com/oauth/access_token';

			$ar=array(
				'client_id'=>Mage::helper('instashop')->client_id,
				'client_secret'=>Mage::helper('instashop')->client_secret,
				'grant_type'=>Mage::helper('instashop')->grant_type,
				'redirect_uri'=>Mage::helper('instashop')->redirect_uri,
				'code'=>$code
			);

			$post_fields=http_build_query($ar);

			$ch=curl_init();

			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$post_fields);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

			$result=curl_exec($ch);			
			
			$data=json_decode($result);

			if($result!==false) {
				if(isset($data->access_token)) {
					Mage::getModel('core/config')->saveConfig('mejora/mejora_group/access_token', $data->access_token);
					
					echo "Access token saved successfully<br>";
					
					echo "<a href='".Mage::getUrl('adminhtml')."'>Back to admin panel</a>";
				}				
			}			

			if(isset($data->access_token)) {
				//echo 'Access token saved successfully.';
			}
			else {
				echo 'A problem occurred while getting Access token!';
			}
			
		}
		
	}
	
}