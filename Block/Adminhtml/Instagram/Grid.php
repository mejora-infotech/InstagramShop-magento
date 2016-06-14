<?php

class Mejora_Instashop_Block_Adminhtml_Instagram_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("instagramGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{			
			//echo Mage::getStoreConfig('instashop/instashop_group/access_token',Mage::app()->getStore());
			//Mage::getModel('core/config')->saveConfig('instashop/instashop_group/access_token', 67);
			//exit;			
			//Mage::helper('instashop')->storeAccessTokenIfRequired();			
			
			$collection = Mage::getModel("instashop/instagram")->getCollection();
			
			$data = Mage::helper('instashop')->getInstagramData();
			
			//echo json_encode($data);
			
			//exit;			
			
			$json='{
			  "pagination": {},
			  "meta": {
				"code": 200
			  },
			  "data": [
				{
				  "attribution": null,
				  "tags": [],
				  "type": "image",
				  "location": null,
				  "comments": {
					"count": 0
				  },
				  "filter": "Lo-fi",
				  "created_time": "1460027121",
				  "link": "https://www.instagram.com/p/BD5Y-M9GSv3/",
				  "likes": {
					"count": 0
				  },
				  "images": {
					"low_resolution": {
					  "url": "https://scontent.cdninstagram.com/t51.2885-15/s320x320/e35/12965634_562198837292174_1475824200_n.jpg?ig_cache_key=MTIyMzExODYwMTQ0MTU4NjE2Nw%3D%3D.2",
					  "width": 320,
					  "height": 320
					},
					"thumbnail": {
					  "url": "https://scontent.cdninstagram.com/t51.2885-15/s150x150/e35/12965634_562198837292174_1475824200_n.jpg?ig_cache_key=MTIyMzExODYwMTQ0MTU4NjE2Nw%3D%3D.2",
					  "width": 150,
					  "height": 150
					},
					"standard_resolution": {
					  "url": "https://scontent.cdninstagram.com/t51.2885-15/s640x640/sh0.08/e35/12965634_562198837292174_1475824200_n.jpg?ig_cache_key=MTIyMzExODYwMTQ0MTU4NjE2Nw%3D%3D.2",
					  "width": 640,
					  "height": 640
					}
				  },
				  "users_in_photo": [],
				  "caption": {
					"created_time": "1460027121",
					"text": "Betta flare",
					"from": {
					  "username": "sumit8586",
					  "profile_picture": "https://igcdn-photos-e-a.akamaihd.net/hphotos-ak-xft1/t51.2885-19/11906329_960233084022564_1448528159_a.jpg",
					  "id": "3079446609",
					  "full_name": "Sumit Bose"
					},
					"id": "17846445976118775"
				  },
				  "user_has_liked": false,
				  "id": "1223118601441586167_3079446609",
				  "user": {
					"username": "sumit8586",
					"profile_picture": "https://igcdn-photos-e-a.akamaihd.net/hphotos-ak-xft1/t51.2885-19/11906329_960233084022564_1448528159_a.jpg",
					"id": "3079446609",
					"full_name": "Sumit Bose"
				  }
				}
			  ]
			}';			
			
			//$decoded_data=json_decode($json);
			
			//$data=$decoded_data->data;

			$instagram_data_ids=$this->getInstagramDataIds($data);
			
			$this->getModifiedCollection($collection,$data);
			
			$collection=Mage::getModel("instashop/instagram")->getCollection();
			
			$collection=$this->getModifiedCollection($collection,$data);

			$collection=$this->removeCollectionItems($collection, $instagram_data_ids);
			
			$this->setCollection($collection);
			
			$this->setFilterVisibility(false);
			/*
			foreach($collection->getItems() as $a) {
				print_r($a);
			}
			exit;
			*/
			
			return parent::_prepareCollection();
		}
		
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
					"header" => Mage::helper("instashop")->__("ID"),
					"align" =>"right",
					"width" => "50px",
					"type" => "number",
					"index" => "id",
				));
                
				$this->addColumn("image", array(
					"header" => Mage::helper("instashop")->__("Image"),
					'renderer' => 'instashop/adminhtml_widget_grid_column_renderer_image',
					"width" => "50px",
					"index" => "image",
					'filter' => false,
				));
				
				$this->addColumn('product_id', array(
					'header' => Mage::helper('instashop')->__('Product id'),
					'align' => 'center',
					'renderer' => 'instashop/adminhtml_widget_grid_column_renderer_inline',
					'index' => 'product_id',
				));
					
				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return '#';
		}


		/*
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('id');
			$this->getMassactionBlock()->setFormFieldName('ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			
			return $this;
		}
		*/
		
		protected function getModifiedCollection($collection,$data) {
			
			$collection_of_things = new Varien_Data_Collection();
			
			$added_images_post_ids=array();
			
			foreach($data as $single_media) {
				if($single_media->type=='image') {
					$thumbnail=$single_media->images->thumbnail->url;
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
		
		
		

}