<?php

require("toodledo_oauth2.php");

$toodledo = new Toodledo_OAuth2();

?>
<html>
<body>

client id: <b><?=$toodledo->clientID?></b><br />
<a href="<?= $toodledo->getAuthURL(); ?>">Authorize...</a><br /><br />


<?php
	
	//If we have an authorization code, exchange it for an access token and refresh token
	if(!empty($_REQUEST['code'])) {
		echo "Exchanging authorization_code for access_token.<br />";
		$tokens = $toodledo->getAccessTokenFromAuthCode($_REQUEST['code'],$_REQUEST['state']);

		$access_token = $tokens['access_token']; //this will be your short-lived token to use with the API to make requests.
		$refresh_token = $tokens['refresh_token']; //this will be your long-lived token to get more access_tokens when they expire
		$expiration = $tokens['expires_in']; //this will tell you when the access_token expires

		echo "Access Token: <b>".$access_token."</b> expires in ".$expiration." sec<br />";
		echo "Refresh Token: <b>".$refresh_token."</b> <a href='?refresh=".$refresh_token."'>Use Refresh Token</a><br />";
	}

	//if we are using a refresh token
	if(!empty($_REQUEST['refresh'])) {
		echo "Exchanging refresh_token for access_token.<br />";
		$tokens = $toodledo->getAccessTokenFromRefreshToken($_REQUEST['refresh']);

		$access_token = $tokens['access_token']; //this will be your short-lived token to use with the API to make requests.
		$refresh_token = $tokens['refresh_token']; //this will be your long-lived token to get more access_tokens when they expire
		$expiration = $tokens['expires_in']; //this will tell you when the access_token expires

		echo "Access Token: <b>".$access_token."</b> expires in ".$expiration." sec<br />";
		echo "Refresh Token: <b>".$refresh_token."</b> <a href='?refresh=".$refresh_token."'>Use Refresh Token</a><br />";
	}

	//if we already have an access token
	if(!empty($_REQUEST['access_token'])) {
		$access_token = $_REQUEST['access_token'];
		echo "Access Token: <b>".$access_token."</b><br />";
	}

	//if we have a valid access token, make an API call
	if(!empty($access_token)) {
		echo "<hr />Using access token to request user info.<br />";
		$data = $toodledo->getResource("http://api.toodledo.com/3/account/get.php",$access_token);
		$user = json_decode($data,true);
		echo($user['email']);
		echo "<br /><br /><a href='?access_token=".$access_token."'>Request resource again</a>";
	}

?>

</body>
</html>	