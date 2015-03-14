<?php
class Domain extends Service
{
	public function __construct()
	{
		$this->name = 'Domain Information';
	}
	
	public function parse()
	{
		$data = $this->data;
		$this->block($this->domain_info($data), 'Domain Info');
	}
	
	protected function _process($url)
	{
		$result = shell_exec('whois ' . $url);
		
		if (preg_match('|creat.*: (.+)|i', $result, $matches)) {
			$data['created'] = strtotime($matches[1]);
			$data['age'] = (time() - $data['created']) / 60 / 60 / 24; // in days
		}
		
		if (preg_match('|expir.*: (.+)|i', $result, $matches)) {
			$data['expires'] = strtotime($matches[1]);
		}
		
		if (preg_match('|update.*: (.+)|i', $result, $matches)) {
			$data['updated'] = strtotime($matches[1]);
		}
		
		if (preg_match('|regist.*: (.+)|i', $result, $matches)) {
			$data['registrar'] = strtotime($matches[1]);
		}
		
		if (substr($url, 0, 4) == 'www.') {
			$url = substr($url, 4);
		}
		
		$ch = curl_init('http://'.$url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_exec($ch);
		$info = curl_getinfo($ch);
		$code = $info['http_code'];
		
		$ch = curl_init('http://www.'.$url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		curl_exec($ch);
		$info = curl_getinfo($ch);
		$www_code = $info['http_code'];
		
		$data['www_redirect'] = ($code == 301 or $www_code == 301);
		
		return $data;
	}

	private function domain_info($data)
	{
		$ret = '';
		foreach ($data as $header => $value) {
			if (is_numeric($value)) {
				$ret .= '<label>'.$header.':</label> '.$this->duration($value).'<br />';
			} else {
				$ret .= '<label>'.$header.':</label> '.$value.'<br />';
			}
		}
		
		return $ret;
	}
	
	public function duration($start, $end = null)
	{
		if ($end == null) {
			$end = time();
		}
		$seconds = $end - $start;  
   
		$years = floor($seconds/60/60/24/365);
		$seconds -= $years * 60*60*24*365;
		$months = floor($seconds/60/60/24/30);
		$seconds -= $months * 60*60*24*30;  
		$days = floor($seconds/60/60/24);  
   
		$duration='';  
		if ($years > 0) {
			$duration .= "$years years ";
		}
		if ($months > 0) {
			$duration .= "$months months ";
		}
		if ($days > 0) {
			$duration .= "$days days ";
		}
   
		$duration = trim($duration);  
   
		return $duration;
	}
}