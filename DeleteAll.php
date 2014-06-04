<?php
/* PHP Script to use Magento API to remove all images */
/* Version 1 .0 */
/* Uses PHP Soapclient & SOAP V2 Api */


/* Credential Configuration */
$username = 'username'; //Magento SOAP Username
$password = 'password'; //Magento SOAP Password
$url = 'http://www.yourdomain.com/index.php/api/v2_soap/?wsdl'; //Magento SOAP URL
$http_username = ''; //LEAVE BLANK UNLESS... You require HTTP authentication to access the protected SOAP URL.
$http_password = ''; //LEAVE BLANK UNLESS... You require HTTP authentication to access the protected SOAP URL.
/* Credential Configuration */

/* System Configuration */
$retry_attempts = '3'; //Sets retry attempts when exceptions are caught.
set_time_limit(0); //Ensure script doesn't timeout
$log_to_file = false; //If enabled logging will be written to log file instead of printing and output.
$log_file = 'Log.txt'; //If $log_to_file is configure true, this will be where the file is logged to.
/* System Configuration */

class MagentoConnection{
	public $username;
	public $password;
	public $url;
	public $http_username;
	public $http_password;
	public $retry_attempts;
	public $log_to_file;
	public $log_file;
	public $Deletions;
	
	function __construct($username, $password, $url, $retry_attempts, $http_username, $http_password){
		$this->username = $username;
		$this->password = $password;
		$this->url = $url;
		$this->retry_attempts = $retry_attempts;
		$this->http_username = $http_username;
		$this->http_password = $http_password;
	}
	
	function LogSettings($log_to_file, $log_file){
		$this->log_to_file = $log_to_file;
		$this->log_file = $log_file;
	}
	
	function LogInfo($LogString){
		if($this->log_to_file){
			echo $LogString;
			$LogString = $LogString.PHP_EOL;
			file_put_contents($this->log_file, $LogString, FILE_APPEND | LOCK_EX);
		}
	}
	
	function GetSession(){
		$GetSession_FaultCount = 0;
		GetSession_TryAgain:
		try{
			if($this->http_username != '' || $this->http_password != ''){
				//Connect using HTTP Authentication
				$this->client = new SoapClient($this->url,array('login' => $this->http_username, 'password' => $this->http_password));
				$LogString = 'Connecting to URL(With HTTP Auth):' . $this->url;
				$this->LogInfo($LogString);
			}else{
				//Connect without HTTP Authentication
				$this->client = new SoapClient($this->url);
				$LogString = 'Connecting to URL:' . $this->url. ' ...';
				$this->LogInfo($LogString);
			}
		}catch (Exception $e){
			$GetSession_FaultCount++;
			if($this->retry_attempts < $GetSession_FaultCount){
				$LogString = 'Attempt failed, attempt ' . $GetSession_FaultCount . '. Retrying ...';
				$this->LogInfo($LogString);
				goto GetSession_TryAgain;
			}else{
				$LogString = 'Error using GetSession(new SoapClient) | Error Code:' . $e->faultcode . ' | Error Message:' . $e->getMessage();
				$this->LogInfo($LogString);
			}
		}
		$GetSession_Login_FaultCount = 0;
		GetSession_Login_TryAgain:
		try{	
			$this->session = $this->client->login($this->username,$this->password);
			$LogString = 'Logging in and Creating session ...';
			$this->LogInfo($LogString);
		}catch (Exception $e){
			$GetSession_Login_FaultCount++;
			if($this->retry_attempts < $GetSession_Login_FaultCount){
				$LogString = 'Attempt failed, attempt ' . $GetSession_Login_FaultCount . '. Retrying ...';
				$this->LogInfo($LogString);
				goto GetSession_Login_TryAgain;
			}else{
				$LogString = 'Error using GetSession(login) | Error Code:' . $e->faultcode . ' | Error Message:' . $e->getMessage();
				$this->LogInfo($LogString);
			}
		}
	}
	
	function GetProductList(){
		$GetProductList_FaultCount = 0;
		GetProductList_TryAgain:
		try{
			$this->ProductList = $this->client->catalogProductList($this->session);
			$LogString = 'Retrieving Product List...';
			$this->LogInfo($LogString);
		}catch (Exception $e){
			$GetProductList_FaultCount++;
			if($this->retry_attempts < $GetProductList_FaultCount){
				$LogString = 'Attempt failed, attempt ' . $GetProductList_FaultCount . '. Retrying ...';
				$this->LogInfo($LogString);
				goto GetProductList_TryAgain;
			}else{
				$LogString = 'Error using GetProductList | Error Code:' . $e->faultcode . ' | Error Message:' . $e->getMessage();
				$this->LogInfo($LogString);
			}
		}
	}
	
	function GetProductImageList($ProductID){
		$GetProductImageList_FaultCount = 0;
		GetProductImageList_TryAgain:
		try{
			$this->client->catalogProductAttributeMediaList($this->session, $ProductID);
			$LogString = 'Retrieving ProductID:' . $ProductID . ' Image List...';
			$this->LogInfo($LogString);
		}catch (Exception $e){
			$GetProductImageList_FaultCount++;
			if($this->retry_attempts < $GetProductImageList_FaultCount){
				$LogString = 'Attempt failed, attempt ' . $GetProductImageList_FaultCount . '. Retrying ...';
				$this->LogInfo($LogString);
				goto GetProductImageList_TryAgain;
			}else{
				$LogString = 'Error using GetProductImageList | ProductID:' . $ProductID . ' | Error Code:' . $e->faultcode . ' | Error Message:' . $e->getMessage();
				$this->LogInfo($LogString);
			}
		}
	}
	
	function DeleteProductImage($ProductID, $Image){
		$DeletionResult = false;
		$DeleteProductImage_FaultCount = 0;
		DeleteProductImage_TryAgain:
		try{
			$DeletionResult = $this->client->catalogProductAttributeMediaRemove($this->session, $ProductID, $Image);
			$LogString = 'Deleting ProductID:' . $ProductID . ' Image:' . $Image . ' ...';
			$this->LogInfo($LogString);
		}catch (Exception $e){
			$DeleteProductImage_FaultCount++;
			if($this->retry_attempts < $GetDeleteProductImage_FaultCount){
				$LogString = 'Attempt failed, attempt ' . $DeleteProductImage_FaultCount . '. Retrying ...';
				$this->LogInfo($LogString);
				goto DeleteProductImage_TryAgain;
			}else{
				$LogString = 'Error using DeleteProductImage | ProductID:' . $ProductID . ' | Image:' . $Image . ' | Error Code:' . $e->faultcode . ' | Error Message:' . $e->getMessage();
				$this->LogInfo($LogString);
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
						$LogString = 'Deleted - ProductID:' . $Product->product_id . '. File:' . $Image->file;
						$this->LogInfo($LogString);
					}
				endforeach;
		endforeach;
	}
}

//Setup Magento Client & Delete All Images
$Connection = new MagentoConnection($username, $password, $url, $retry_attempts, $http_username, $http_password);
$Connection->LogSettings($log_to_file, $log_file);
$Connection->GetSession();
$Connection->GetProductList();
$Connection->DeleteAllImages();

//Array of deletions available at.
//var_dump($Connection->Deletions);

?>