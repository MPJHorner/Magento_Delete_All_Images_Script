<?php
/* PHP Script to use Magento API to remove all images */
/* Version 1 .0 */
/* Uses PHP Soapclient & SOAP V2 Api */


/* Credential Configuration */
$username = 'username'; //Magento SOAP Username
$password = 'password'; //Magento SOAP Password
$url = 'http://www.yourdomain.com/index.php/api/v2_soap/?wsdl'; //Magento SOAP URL
/* Credential Configuration */

/* System Configuration */
$retry_attempts = '3'; //Sets retry attempts when exceptions are caught.
set_time_limit(0); //Ensure script doesn't timeout
/* System Configuration */

class MagentoConnection{
	public $username;
	public $password;
	public $url;
	public $retry_attempts;
	public $Deletions;
	
	function __construct($username, $password, $url, $retry_attempts){
		$this->username = $username;
		$this->password = $password;
		$this->url = $url;
		$this->retry_attempts = $retry_attempts;
	}
	
	function GetSession(){
		$GetSession_FaultCount = 0;
		GetSession_TryAgain:
		try{
			$this->client = new SoapClient($this->url);
		}catch (Exception $e){
			$GetSession_FaultCount++;
			if($this->retry_attempts < $GetSession_FaultCount){
				goto GetSession_TryAgain;
			}else{
				echo '<br> Error using GetSession(new SoapClient) | Error Code:' . $e->faultcode . ' | Error Message:' . $e->getMessage();
			}
		}
		$GetSession_Login_FaultCount = 0;
		GetSession_Login_TryAgain:
		try{	
			$this->session = $this->client->login($this->username,$this->password);
		}catch (Exception $e){
			$GetSession_Login_FaultCount++;
			if($this->retry_attempts < $GetSession_Login_FaultCount){
				goto GetSession_Login_TryAgain;
			}else{
				echo '<br> Error using GetSession(login) | Error Code:' . $e->faultcode . ' | Error Message:' . $e->getMessage();
			}
		}
	}
	
	function GetProductList(){
		$GetProductList_FaultCount = 0;
		GetProductList_TryAgain:
		try{
			$this->ProductList = $this->client->catalogProductList($this->session);
		}catch (Exception $e){
			$GetProductList_FaultCount++;
			if($this->retry_attempts < $GetProductList_FaultCount){
				goto GetProductList_TryAgain;
			}else{
				echo '<br> Error using GetProductList | Error Code:' . $e->faultcode . ' | Error Message:' . $e->getMessage();
			}
		}
	}
	
	function GetProductImageList($ProductID){
		$GetProductImageList_FaultCount = 0;
		GetProductImageList_TryAgain:
		try{
			$this->client->catalogProductAttributeMediaList($this->session, $ProductID);
		}catch (Exception $e){
			$GetProductImageList_FaultCount++;
			if($this->retry_attempts < $GetProductImageList_FaultCount){
				goto GetProductImageList_TryAgain;
			}else{
				echo '<br> Error using GetProductImageList | ProductID:' . $ProductID . ' | Error Code:' . $e->faultcode . ' | Error Message:' . $e->getMessage();
			}
		}
	}
	
	function DeleteProductImage($ProductID, $Image){
		$DeletionResult = false;
		$DeleteProductImage_FaultCount = 0;
		DeleteProductImage_TryAgain:
		try{
			$DeletionResult = $this->client->catalogProductAttributeMediaRemove($this->session, $ProductID, $Image);
		}catch (Exception $e){
			$DeleteProductImage_FaultCount++;
			if($this->retry_attempts < $GetDeleteProductImage_FaultCount){
				goto DeleteProductImage_TryAgain;
			}else{
				echo '<br> Error using DeleteProductImage | ProductID:' . $ProductID . ' | Image:' . $Image . ' | Error Code:' . $e->faultcode . ' | Error Message:' . $e->getMessage();
			}
		}
		return $DeletionResult;
	}
	
	function DeleteAllImages(){
		//Loop Through Products
		foreach($this->ProductList as $Product):
			//Get Images For Product
			$Product->Images = $this->GetProductImageList($Product->product_id);
				//Loop Through Images
				foreach($Product->Images as $Image):
					$Deletion_FaultCount = 0;
					Delete_TryAgain:
					//Delete Images
					$DeletionResult = $this->DeleteProductImage($Product->product_id, $Image->file);
					if($DeletionResult != true){
						$Deletion_FaultCount++;
						if($this->retry_attempts < $Deletion_FaultCount){
							goto Delete_TryAgain;
						}
					}else{
						$this->Deletions[] = array($Product->product_id, $Image->file);
					}
				endforeach;
		endforeach;
	}
}

//Setup Magento Client & Delete All Images
$Connection = new MagentoConnection($username, $password, $url. $retry_attempts);
$Connection->GetSession();
$Connection->GetProductList();
$Connection->DeleteAllImages();

//Array of deletions available at.
//var_dump($Connection->Deletions);

?>