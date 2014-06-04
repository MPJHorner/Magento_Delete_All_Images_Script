<?php
/* PHP Script to use Magento API to remove all images */
/* Version 1 .0 */
/* Uses PHP Soapclient & SOAP V2 Api */


/* Credential Configuration */
$username = 'username'; //Magento SOAP Username
$password = 'password'; //Magento SOAP Password
$url = 'http://www.yourdomain.com/index.php/api/v2_soap/?wsdl'; //Magento SOAP URL
/* Configuration */

/* System Configuration */
set_time_limit(0); //Ensure script doesn't timeout


class MagentoConnection{
	public $username;
	public $password;
	public $url;
	
	function __construct($username, $password, $url){
		$this->username = $username;
		$this->password = $password;
		$this->url = $url;
	}
	
	function GetSession(){
		$this->client = new SoapClient($this->url);
		$this->session = $this->client->login($this->username,$this->password);
	}
	
	function GetProductList(){
		return $this->ProductList = $this->client->catalogProductList($this->session);
	}
	
	function GetProductImageList($ProductID){
		return $this->client->catalogProductAttributeMediaList($this->session, $ProductID);
	}
	
	function DeleteProductImage($ProductID, $Image){
		return $this->client->catalogProductAttributeMediaRemove($this->session, $ProductID, $Image);
	}
	
	function DeleteAllImages(){
		//Loop Through Products
		foreach($this->ProductList as $Product):
			//Get Images For Product
			$Product->Images = $this->GetProductImageList($Product->product_id);
				//Loop Through Images
				foreach($Product->Images as $Image):
					//Delete Images
					$this->DeleteProductImage($Product->product_id, $Image->file);
				endforeach;
		endforeach;
	}
}

//Setup Magento Client
$Connection = new MagentoConnection($username, $password, $url);
$Connection->GetSession();
$Connection->GetProductList();
$Connection->DeleteAllImages();
?>