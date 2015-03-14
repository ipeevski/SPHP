<?php
class Paypal
{
	private $vars;
	private $response;
	
	private $endpoint;
	
	private $items;
	
	private $proxy_address;
	private $proxy_port;
	
	public function __construct($defaults = array(), $version = '3.0', $endpoint = 'https://api-3t.paypal.com/nvp')
	{
		$this->vars['version'] = $version;
		$this->vars['ipaddress'] = $_SERVER['REMOTE_ADDR'];
		$this->endpoint = $endpoint;
		
		if (!empty($defaults))
		{
			$this->vars = $defaults;
		}
	}
	
	public function testing()
	{
		$this->endpoint = str_replace('.paypal', '.sandbox.paypal', $this->endpoint);
	}
	
	/**
	 * Pass in PayPal credentials.
	 * REQUIRED.
	 *
	 * @param string $user
	 * @param string $pass
	 * @param string $signature
	 */
	public function login($user, $pass, $signature)
	{
		$this->vars['user'] = $user;
		$this->vars['pwd'] = $pass;
		$this->vars['signature'] = $signature;
	}
	
	public function setName($first, $last)
	{
		$this->vars['firstname'] = $first;
		$this->vars['lastname'] = $last;
	}
	
	public function setCC($number, $type, $date, $cvv2)
	{
		$this->vars['acct'] = $number;
		$this->vars['CreditCardType'] = $type;
		$this->vars['expdate'] = str_replace('/', '', $date);
		$this->vars['cvv2'] = $cvv2;
	}
	
	public function setAddress($street, $city, $state, $zip)
	{
		$this->vars['street'] = $street;
		$this->vars['city'] = $city;
		$this->vars['state'] = $state;
		$this->vars['zip'] = $zip;
		$this->vars['countrycode'] = 'US';
	}
	
	/**
	 * Set the ammount to be charged and the currency for the ammount
	 *
	 * @param double $total
	 * @param string $currency a currency code. Can be one of (AUD, CAD, EUR, GBP, JPY, USD)
	 */
	public function setTotal($total, $currency = 'USD')
	{
		$this->vars['amt'] = $total;
		$this->vars['currencycode'] = $currency;
	}
	
	public function addItem($name, $qty, $amount, $tax)
	{
		$line = count($this->items);
		$this->items[] = $amount;
		$this->vars['L_NUMBER'.$line] = ($line + 1);
		$this->vars['L_NAME'.$line] = $name;
		$this->vars['L_QTY'.$line] = $qty;
		$this->vars['L_AMT'.$line] = $amount;
		$this->vars['L_TAXAMT'.$line] = $tax;
		if (!isset($this->vars['ITEMAMT'])) {
			$this->vars['ITEMAMT'] = 0;
			$this->vars['TAXAMT'] = 0;
			// If an item is set, tax should be set too.
			$this->setShipping(0);
		}
		$this->vars['ITEMAMT'] += ($amount * $qty);
		$this->vars['TAXAMT'] += ($tax * $qty); 
	}
	
	public function setDescription($desc, $custom)
	{
		$this->vars['desc'] = $desc;
		$this->vars['custom'] = $custom;
	}
	
	public function setInvoice($invoice)
	{
		$this->vars['INVNUM'] = $invoice;
	}
	
	public function setShipping($shipping, $handling = 0)
	{
		$this->vars['SHIPPINGAMT'] = $shipping;
		$this->vars['HANDLINGAMT'] = $handling;
	}

	/**
	 * Set a callback URL to be pinged on a successful transaction.
	 *
	 * @param string $url
	 */
	public function setNotify($url)
	{
		$this->vars['notifyURL'] = $url;
	}
		
	public function pay($method = 'doDirectPayment', $type = 'Sale')
	{
		$this->vars['method'] = $method;
		$this->vars['paymentAction'] = $type;
		$this->response = $this->hash_call();
		
		return $this->successful();
	}
	
	public function successful()
	{
		if (strtolower($this->response['ACK']) == 'success') {
			return true;
		}
		
		if (strtolower($this->response['ACK']) == 'successwithwarning') {
			return true;
		}
		
		return false;
	}
	
	public function validate()
	{
		// Strict
		return ($this->response['AVS'] == 'X' and $this->response['CVV2MATCH'] == 'M');
		// Fuzzy
		// $this->response['CVV2MATCH'] != 'N'
		// Fails:
		// (!in_array($this->response['AVS'], array('C', 'E', 'N'))
	}
	
	public function getResponse()
	{
		return $this->response;
	}
	
	public function getError()
	{
		return $this->response['L_LONGMESSAGE0'];
	}
	
	public function getErrorCode()
	{
		return $this->response['L_ERRORCODE0'];
	}
	
	public function getID()
	{
		return $this->response['TRANSACTIONID'];
	}
	
	public function getTotal()
	{
		return $this->response['AMT'];
	}
	
	public function setProxy($address, $port = 8080)
	{
		$this->proxy_address = $address;
		$this->proxy_port = $port;
	}
	
	/**
	  * hash_call: Function to perform the API call to PayPal using API signature
	  * @methodName is name of API  method.
	  * @nvpStr is nvp string.
	  * returns an associtive array containing the response from the server.
	*/
	private function hash_call()
	{
		//setting the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
	    //if USE_PROXY constant set to TRUE in Constants.php, then only proxy will be enabled.
	   //Set proxy name to PROXY_HOST and port number to PROXY_PORT in constants.php 
		if (!empty($this->proxy_address)) {
			curl_setopt($ch, CURLOPT_PROXY, $this->proxy_address.':'.$this->proxy_port);
		} 
	
		//NVPRequest for submitting to server
		$vars = array();
		foreach ($this->vars as $key => $var) {
			$vars[] = strtoupper($key).'='.urlencode($var);
		}
		$nvpreq = implode('&', $vars);
	
		//setting the nvpreq as POST FIELD to curl
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
	
		//getting response from server
		$response = curl_exec($ch);
	
		//convrting NVPResponse to an Associative Array
		$nvpResArray = $this->deformatNVP($response);
		$nvpReqArray = $this->deformatNVP($nvpreq);
		$_SESSION['nvpReqArray'] = $nvpReqArray;
	
		if (curl_errno($ch)) {
			// moving to display page to display curl errors
			  $_SESSION['curl_error_no'] = curl_errno($ch) ;
			  $_SESSION['curl_error_msg'] = curl_error($ch);
			  $location = "APIError.php";
			  header("Location: $location");
		 } else {
			 //closing the curl
				curl_close($ch);
		  }
	
		return $nvpResArray;
	}

	/** This function will take NVPString and convert it to an Associative Array 
	  * and it will decode the response.
	  * It is usefull to search for a particular key and displaying arrays.
	  * @nvpstr is NVPString.
	  * @nvpArray is Associative Array.
	  */
	private function deformatNVP($nvpstr)
	{
		$intial = 0;
		 $nvpArray = array();
		
		while(strlen($nvpstr)){
			//postion of Key
			$keypos = strpos($nvpstr, '=');
			//position of value
			$valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&') : strlen($nvpstr);
		
			/*getting the Key and Value values and storing in a Associative Array*/
			$keyval = substr($nvpstr, $intial, $keypos);
			$valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);
			//decoding the respose
			$nvpArray[urldecode($keyval)] = urldecode($valval);
			$nvpstr = substr($nvpstr, $valuepos + 1, strlen($nvpstr));
		}
		  
		return $nvpArray;
	}
}

