<?php

/**
 * TAM PHP Library for SMS Interface
 * https://code.telcoassetmarketplace.com/devcommunity/index.php/menudocumentation/menuapireference/menusmsinterface
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

require_once dirname(__FILE__) . "/common.php";

class SMSApi
{								
	static function sendSMS ($usr_id, $body, $from = null, $transaction_id = null)
	{   
		$apiParams = array ('body'=>$body);
		if (!empty($from)) 
		{
			$apiParams['from'] = $from;
		}
		if (!empty($transaction_id)) 
		{
			$apiParams['transaction_id'] = $transaction_id;
		}
		$body = json_encode($apiParams);
		
		$curlOptions = array(
			CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json'
				),
			CURLOPT_POSTFIELDS => $body);
			
		foreach (Common::getCurlOptions() as $key => $option) 
		{
			$curlOptions[$key] = $option;
		}
		
		$request = new OAuthRequester(CREATARY_API_SEND_SMS_URL, 'POST');
		
		return Common::doRequest($request, $usr_id, $curlOptions);
	}

}

?>