<?php

/**
 * TAM PHP Library Commons
 * 
 * Copyright (c) 2011 Nokia Siemens Networks
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

define("CREATARY_HOST", "https://telcoassetmarketplace.com");
define("CREATARY_API_URL", CREATARY_HOST . "/api/1");

//OAuth Interfaces
define("CREATARY_REQUEST_TOKEN_URL", CREATARY_API_URL . "/oauth/request_token");
define("CREATARY_AUTHORIZE_URL", CREATARY_HOST . "/web/authorize");
define("CREATARY_ACCESS_TOKEN_URL", CREATARY_API_URL . "/oauth/access_token");

//Other Interfaces
define("CREATARY_API_SEND_SMS_URL", CREATARY_API_URL . "/sms/send");
define("CREATARY_API_GET_LOCATION_COORD_URL", CREATARY_API_URL . "/location/getcoord");

require_once dirname(__FILE__) . "/../oauth/OAuthStore.php";
require_once dirname(__FILE__) . "/../oauth/OAuthRequester.php";

class Common 
{
	static private $server = array(
									'consumer_key' => CREATARY_CONSUMER_KEY, 
									'consumer_secret' => CREATARY_CONSUMER_SECRET,
									'server_uri' => CREATARY_HOST,
									'request_token_uri' => CREATARY_REQUEST_TOKEN_URL,
									'authorize_uri' => CREATARY_AUTHORIZE_URL,
									'access_token_uri' => CREATARY_ACCESS_TOKEN_URL,
									'signature_methods' => 'HMAC-SHA1'
								);
								
	static private $curlOptions = array();
								
	static private $initiated = false;								
								
	static function initOAuth ($store = 'MySQL', $options = array(), $curlOptions = array())
	{	
		//  Init the OAuthStore
		$store = OAuthStore::instance($store, $options);
	
		if (!Common::$initiated) {
			try {
				if (!$store instanceof OAuthStoreSession)
				{
					$store->getServer(Common::$server['consumer_key'], null);
				}
			} catch (OAuthException2 $e) {
				// first check if server uri exist but with different/old consumer key
				try {
					$existing = $store->getServerForUri(Common::$server['server_uri'], null);
					
					//exist so we have to delete it first
					$store->deleteServer($existing['consumer_key'], null);
				} catch (OAuthException2 $e) {
				}
				
				// server not found, create it
				$store->updateServer(Common::$server, null);
			}
			
			if (!empty($curlOptions)) 
			{
				Common::$curlOptions = $curlOptions;
			}
		
			Common::$initiated = true;
		}
		
		return $store;
	}
	
	static function requestRequestToken($usrId, $callbackUrl = "")
	{
		// get a request token
		$tokenResultParams = OAuthRequester::requestRequestToken(CREATARY_CONSUMER_KEY, $usrId, 0, 'GET', null, Common::$curlOptions);

		//  redirect to the TAM authorization page, it will redirect back
		$callback = "";
		if (!empty($callbackUrl)) 
		{
			$callback = "&oauth_callback=" . $callbackUrl;
		}
		header("Location: " . CREATARY_AUTHORIZE_URL . "?oauth_token=" . $tokenResultParams['token'] . $callback);
	}
	
	static function requestAccessToken($usrId, $oauthToken, $oauthVerifier = "")
	{
		if (!empty($oauthVerifier)) 
		{
			$getAuthTokenParams = array(
				'oauth_verifier' => $oauthVerifier);
		}
		else 
		{
			$getAuthTokenParams = null;
		}
			
		OAuthRequester::requestAccessToken(CREATARY_CONSUMER_KEY, $oauthToken, $usrId, 'GET', $getAuthTokenParams, Common::$curlOptions);
				
		$store	= OAuthStore::instance();
		// get the stored access token for this user
		$oauth = $store->getSecretsForSignature(CREATARY_ACCESS_TOKEN_URL, $usrId);
		
		return $oauth['token'];
	}
	
	static function storeAccessToken($usrId, $oauthToken, $tokenSecret) 
	{
		$store = OAuthStore::instance();
    	$store->addServerToken(CREATARY_CONSUMER_KEY, 'access', $oauthToken, $tokenSecret, $usrId);
	}
	
	static function doRequest($request, $usr_id, $curlOptions) 
	{
		try {
			$result = $request->doRequest($usr_id, $curlOptions);

			// now we parse the json response from the API call
			$jsonResponse = json_decode($result['body']);
		} catch (OAuthException2 $e) {
			$message = $e->getMessage();
			$messages = preg_split("/: /", $message, 2);
			
			$jsonResponse = json_decode($messages[1]);
		}
		
		return $jsonResponse;
	}
	
	static function resetOAuth () 
	{
		// to be called if the application has updated consumer key without the need of restarting the application server
		Common::$initiated = false;
	}
	
	static function getServerOptions()
	{
		return Common::$server;
	}
	
	static function getCurlOptions()
	{
		return Common::$curlOptions;
	}
}

?>