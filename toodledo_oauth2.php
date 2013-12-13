<?php
/*
	This is a simple class for authenticating with Toodledo.com's API V3
	http://api.toodledo.com/3/

	This is just an example. You will want to modify this for your needs.

*/

class Toodledo_OAuth2 {
	
	var $clientID = ""; //You fill this in with your client id that you registered at api.toodledo.com
	var $clientSecret = ""; //You fill this in with the secret that you got when you registered
	var $scope = "basic tasks";

	var $authorization_url = "https://api.toodledo.com/3/account/authorize.php";
	var $token_url = "https://api.toodledo.com/3/account/token.php";

	public function __construct($app_version=0, $os_version=0, $device_name='', $device_id='') {
		//These fields are optional, but can give you useful statistics about who is using your app with Toodledo.

		$this->app_version = intval($app_version); //The version of your app that is authenticating. (integer)
		$this->os_version = intval($os_version); //The version of the OS that is running on the user's device. (integer)
		$this->device_name = $device_name; //A string the identifies the make and model of the user's device.
		$this->device_id = $device_id; //A string that uniquly identifies this user. Used for counting total unique users and nothing else.

		$this->state = "xyz"; //this is used to prevent cross site request forgery and should be unique for each request.
		//It is your job to store this state somewhere so that you can compare it to what the server echos back to you
		//after authorizing your app.
	}

	/*
		Returns the authorization url that you will need to redirect your user to.
	*/
	public function getAuthURL() {
		return $this->authorization_url."?response_type=code&client_id=".$this->clientID."&state=".$this->state."&scope=".$this->scope;
	}

	/*
		Exchanges an authorization code for an access_token and refresh_token
	*/
	public function getAccessTokenFromAuthCode($auth_code,$state) {
		//Compare state to make sure it is the same. It is your job to retreive the state from your database and compare it
		//to what was echoed back to you after authorizing.

		if($this->state != $state) return null; //state did not match
		
		//array causes Content-Type:multipart/form-data		
		$post = array("grant_type"=>"authorization_code","code"=>$auth_code,'vers'=>$this->app_version,'os'=>$this->os_version,'device'=>$this->device_name,'udid'=>$this->device_id);
		
		//str causes Content-Type:application/x-www-form-urlencoded  (both work for code exchange)
		//$post = "grant_type=authorization_code&code=".$auth_code;


		$ci = curl_init();
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_USERPWD, $this->clientID . ":" . $this->clientSecret);
		curl_setopt($ci, CURLOPT_POST, TRUE);
		curl_setopt($ci, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ci, CURLOPT_URL, $this->token_url);
		$response = curl_exec($ci);
		curl_close ($ci);

		$response = json_decode($response,true);
		return $response;
	}

	/*
		Exchanges a refresh token for an access_token and new refresh_token
	*/
	public function getAccessTokenFromRefreshToken($refresh_token) {
	
		//array causes Content-Type:multipart/form-data		
		$post = array("grant_type"=>"refresh_token","refresh_token"=>$refresh_token,'vers'=>$this->app_version,'os'=>$this->os_version,'device'=>$this->device_name,'udid'=>$this->device_id);
		
		//str causes Content-Type:application/x-www-form-urlencoded  (both work for token exchange)
		//$post = "grant_type=refresh_token&refresh_token=".$refresh_token;

		$ci = curl_init();
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_USERPWD, $this->clientID . ":" . $this->clientSecret);
		curl_setopt($ci, CURLOPT_POST, TRUE);
		curl_setopt($ci, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ci, CURLOPT_URL, $this->token_url);
		$response = curl_exec($ci);
		curl_close ($ci);

		$response = json_decode($response,true);
		return $response;
	}

	/*
		Uses an access token to request something from the API
	*/
	public function getResource($resource_url,$access_token) {
		$url = $resource_url."?access_token=".$access_token;

		$ci = curl_init();
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_URL, $url);
		$response = curl_exec($ci);
		curl_close ($ci);

		return $response;
	}


	/*
		Uses an access token to post something to the API
	*/
	public function postResource($resource_url,$access_token,$post) {
		$url = $resource_url;

		//if post is a query string it does a application/x-www-form-urlencoded post
		//if post is an array it does a multipart/form-data post
		//access_token cannot be in body of multipart post, so we put it in the url in this case
		if(is_array($post)) {
			$url .= "?access_token=".$this->accessToken;
		} else {
			$post.= "&access_token=".$this->accessToken;
		}

		$ci = curl_init();
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_POST, TRUE);
		curl_setopt($ci, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ci, CURLOPT_URL, $url);
		$response = curl_exec($ci);
		curl_close ($ci);

		return $response;
	}
}
?>