<?php
class Mejora_Instashop_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	public $client_id='';
	
	public $client_secret='';
	
	public $grant_type='authorization_code';
	
	//public $redirect_uri='http://localhost/magento17/instashop/index/instagramaccesstokensave';
	public $redirect_uri='http://unnito.com/instashop/index/instagramaccesstokensave';
	
	public function __construct() {
		$this->client_id=Mage::getStoreConfig('mejora/mejora_group/client_id',Mage::app()->getStore());
		$this->client_secret=Mage::getStoreConfig('mejora/mejora_group/client_secret',Mage::app()->getStore());
	}
	
	public function getInstagramData() {
		$access_token=Mage::getStoreConfig('mejora/mejora_group/access_token',Mage::app()->getStore());
		
		if($this->client_id=='' || $this->client_secret=='') {
			$this->showMessage('client id and/or client secret is blank!');
		}
		
		//$access_token='3079446609.dbfed28.af9d95d4a5bf4675b6f809c1a9902a6d';
			
		$url='https://api.instagram.com/v1/users/self/media/recent/?access_token='.$access_token;		
		
		$ch=curl_init();
		
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		
		$json_data=curl_exec($ch);

		if($json_data===false) {
			$this->showMessage('Something went wrong!');
		}	
		
		$decoded_data=json_decode($json_data);	

		//check if expired		
		if(isset($decoded_data->meta->error_type)) {
			if($decoded_data->meta->error_type=='OAuthAccessTokenError' || $decoded_data->meta->error_type=='OAuthAccessTokenException' || $decoded_data->meta->error_type=='OAuthParameterException') {
				if(Mage::app()->getStore()->isAdmin()) {
					echo 'Your instagram access token is either expired or invalid! Please <a href="https://www.instagram.com/oauth/authorize/?client_id='.$this->client_id.'&redirect_uri='.$this->redirect_uri.'&response_type=code">click here</a> to get new access token';
					exit;
				}
				else {
					$this->showMessage();
				}				
			}
			else {
				$this->showMessage('Something went wrong!');
			}
		}		
		
		
		$data=$decoded_data->data;
		
		while(isset($decoded_data->pagination->next_url)) {
			$url=$decoded_data->pagination->next_url;
			
			$json_data=file_get_contents($url);
			
			if($json_data===false) {
				throw new Exception("Instagram api call failed!");
			}
			
			$decoded_data=json_decode($json_data);
			$temp_data=$decoded_data->data;
			
			$data=array_merge($data,$temp_data);
		}
		
		return $data;
	}
	
	/*
	protected function getCurrentUrl() {
		$currentUrl = Mage::helper('core/url')->getCurrentUrl();
		$url = Mage::getSingleton('core/url')->parseUrl($currentUrl);
		$path = $url->getPath();
		
		return $path;
	}
	*/
	
	public function storeAccessTokenIfRequired() {
		if(isset($_GET['code'])) {
			$code=$_GET['code'];

			$url='https://api.instagram.com/oauth/access_token';

			$ar=array(
				'client_id'=>$this->client_id,
				'client_secret'=>$this->client_secret,
				'grant_type'=>$this->grant_type,
				'redirect_uri'=>$this->redirect_uri,
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
					Mage::getModel('core/config')->saveConfig('instashop/instashop_group/access_token', $data->access_token);
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
	
	
	public function getInstagramCollection() {
		
		$collection = Mage::getModel("instashop/instagram")->getCollection();
			
		$data = $this->getInstagramData();			

		$instagram_data_ids=$this->getInstagramDataIds($data);
		
		$this->getModifiedCollection($collection,$data);
		
		$collection=Mage::getModel("instashop/instagram")->getCollection();
		
		$collection=$this->getModifiedCollection($collection,$data);

		$collection=$this->removeCollectionItems($collection, $instagram_data_ids);
		
		$collection=$this->addAdditionalAttribute($collection);
		
		return $collection;
	}
	
	protected function getModifiedCollection($collection,$data) {
			
		$collection_of_things = new Varien_Data_Collection();
		
		$added_images_post_ids=array();
		
		foreach($data as $single_media) {
			if($single_media->type=='image') {
				$thumbnail=$single_media->images->thumbnail->url;
				$large_image=$single_media->images->standard_resolution->url;
				$like=$single_media->likes->count;
				$id=$single_media->id;
				
				$found=0;					
				
				foreach ($collection->getItems() as $key => $product) {
					$thing_1 = new Varien_Object();
					$thing_1->setProduct_id($product->product_id);
					$thing_1->setPost_id($product->post_id);
					$thing_1->setId($product->id);						
					
					if($product->post_id==$id) {
						$added_images_post_ids[]=$product->post_id;
						
						$found=1;							
						$thing_1->setImage($thumbnail);							
						$thing_1->setLarge_image($large_image);							
						$thing_1->setLike($like);							
						$collection_of_things->addItem($thing_1);
					}
					
				}
				
				
				if($found==0) {
					$newproduct='';
					
					$newproduct->product_id='';
					$newproduct->post_id=$id;					
					
					$model=Mage::getModel("instashop/instagram")->setData((array) $newproduct);
					
					$model->save();					
					
					//$collection->addItem($newproduct);
					
				}
				
			}
		}
		
		
		foreach ($collection->getItems() as $key => $product) {
			if(!in_array($product->post_id,$added_images_post_ids)) {
				$thing_1 = new Varien_Object();
				$thing_1->setProduct_id($product->product_id);
				$thing_1->setPost_id($product->post_id);
				$thing_1->setId($product->id);
				
				$collection_of_things->addItem($thing_1);
			}
		}
		
		
		return $collection_of_things;

	}

	protected function getInstagramDataIds($data) {
		
		$ar=array();
		foreach($data as $single_media) {
			if($single_media->type=='image') {
				$ar[]=$single_media->id;
			}
		}
		
		return $ar;			
	}
	
	protected function removeCollectionItems($collection, $instagram_data_ids) {
		foreach($collection->getItems() as $product) {
			if( !in_array($product->post_id, $instagram_data_ids) ) {
				$collection->removeItemByKey($product->getId());
				$model = Mage::getModel('instashop/instagram')->load($product->id);
				$model->delete();
			}
		}	

		return $collection;			
	}
	
	protected function addAdditionalAttribute($collection) {
		$collection_of_things = new Varien_Data_Collection();
		
		$product_ids=array();
		foreach ($collection->getItems() as $key => $product) {
			$product_ids[]=$product->product_id;
		}		
		
		$collection_product = Mage::getModel('catalog/product')
						->getCollection()                
						->addAttributeToFilter('entity_id', array('in' => $product_ids))
						->addAttributeToSelect(array('entity_id','description'));
						
		foreach ($collection->getItems() as $key => $product) {
			foreach ($collection_product->getItems() as $key2 => $product2) {
				if($product2->entity_id==$product->product_id) {
					$thing_1 = new Varien_Object();
					$thing_1->setProduct_id($product->product_id);
					$thing_1->setDescription($product2->description);
					$thing_1->setImage($product->image);
					$thing_1->setLarge_image($product->large_image);
					$thing_1->setLike($product->like);
					
					$collection_of_things->addItem($thing_1);
				}
			}
		}
		
		return $collection_of_things;
	}
	
	protected function showMessage($message='') {
		if(Mage::app()->getStore()->isAdmin()) {
			echo $message;
			exit;
		}
		else {
			echo 'Nothing found!';
			exit;
		}				
	}
	
}
	 