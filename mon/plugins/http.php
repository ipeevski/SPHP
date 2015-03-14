<?php
class Http extends Service
{
	public function __construct()
	{
		$this->name = 'HTTP';
	}
	
	public function parse()
	{
		$data = $this->data;
		$this->block($this->http_server($data), 'Server');
		$this->block($this->http_performance($data), 'Performance');
		$this->block($this->http_content($data), 'Content');
		$this->block($this->http_headers($data['headers']), 'Headers');
		if (!empty($data['cookies'])) {
			$this->block($this->http_cookies($data['cookies']), 'Cookies');
		}
	}
	
	protected function _process($url)
	{
		$url = 'http://'.$url;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FILETIME, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		//curl_setopt($ch, CURLOPT_NOBODY, 1);
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:...'));
		$result = curl_exec($ch);
		
		
		$info = curl_getinfo($ch);
		
		$data['modified'] = $info['filetime'];
		$type = $info['content_type'];
		$matches = array();
		if (preg_match('|([a-z]+/[a-z]+)|i', $type, $matches)) {
			$data['mime'] = $matches[1];
		}
		if (preg_match('|charset=([a-z0-9-]+)|i', $type, $matches)) {
			$data['charset'] = $matches[1];
		}
		$data['code'] = $info['http_code'];
		
		$data['time']['total'] = sprintf('%.3f sec', $info['total_time']);
		$data['time']['name resolution'] = sprintf('%.3f ms', $info['namelookup_time'] * 1000);
		$data['time']['connect'] = sprintf('%.3f ms', $info['connect_time'] * 1000);
		$data['time']['load'] = sprintf('%.3f ms', ($info['pretransfer_time'] - $info['connect_time']) * 1000);
		$data['time']['download'] = sprintf('%.3f sec', $info['total_time'] - $info['pretransfer_time']);
		
		$data['speed'] = sprintf('%.2f Kb/s', $info['speed_download'] / 1024);
		$data['size']['download'] = sprintf('%.2f  Kb', $info['size_download'] / 1024);
		curl_close($ch);
		
		$data['header'] = substr($result, 0, strpos($result, "\r\n\r\n"));
		$headers = explode("\r\n", $data['header']);
		foreach ($headers as $header) {
			if (strpos($header, ':')) {
				list($key, $val) = explode(': ', $header);
				if (isset($data['headers'][$key])) {
					if (!is_array($data['headers'][$key])) {
						$data['headers'][$key] = array($data['headers'][$key]);
					}
					$data['headers'][$key][] = $val;
				} else {
					$data['headers'][$key] = $val;
				}
			} else {
				$data['headers'][$header] = '';
			}
		}
		
		$data['size']['header'] = strlen($data['header']);
		if (!empty($data['headers']['Content-Length'])) {
			$data['size']['content'] = $data['headers']['Content-Length'];
		} else {
			$data['size']['content'] = 0;
		}
		$content_size = strlen($result) - $data['size']['header'] - 4;
		if (!empty($data['headers']['Content-Length']) and 
			intval($data['headers']['Content-Length']) != $content_size) {
			$data['size']['difference'] = $data['headers']['Content-Length'] - $content_size;
		}
		if (!empty($data['size']['content'])) {
			$data['size']['content'] = $content_size;
		}
		
		$data['size']['total'] = $data['size']['header'] + $data['size']['content'];
		
		$data['server'] = $data['headers']['Server'];
		if (preg_match('| \(([^\)]+)\)|', $data['server'], $matches)) {
			$data['os'] = $matches[1];
			$data['server'] = preg_replace('| \([^\)]+\)|', '', $data['server']);
		}
		if (strpos($data['server'], 'Microsoft') !== false) {
			$data['os'] = 'Windows';
		}
		if (strpos($data['server'], '/') > 0) {
			list($data['server name'], $data['server version']) = explode('/', $data['server']);
		} else {
			$data['server name'] = $data['server'];
			$data['server version'] = '';
		}
		if (!empty($data['headers']['X-Powered-By'])) {
			$data['lang'] = $data['headers']['X-Powered-By'];
		}
		if (!empty($data['headers']['X-AspNet-Version'])) {
			$data['lang'] .= ' '.$data['headers']['X-AspNet-Version'];
		}
		if (isset($data['headers']['Set-Cookie'])) {
			$data['cookies'] = $data['headers']['Set-Cookie'];
			if (!is_array($data['cookies'])) {
				$data['cookies'] = array($data['cookies']);
			}
		}
		if (!empty($data['headers']['Location'])) {
			$data['redirect'] = $data['headers']['Location'];
		}
		
		return $data;
	}

	private function http_headers($data)
	{
		$ret = '';
		foreach ($data as $header => $value) {
			if (is_array($value)) {
				foreach ($value as $val) {
					$ret .= '<label>'.$header.':</label> '.$val.'<br />';
				}
			} else {
				$ret .= '<label>'.$header.':</label> '.$value.'<br />';
			}
		}
		
		return $ret;
	}
	
	private function http_content($data)
	{
		$ret = '';
		$ret .= '<label>Response Code:</label> '.$data['code'].'<br />';
		$ret .= '<label>Mime Type:</label> '.$data['mime'].'<br />';
		if (!empty($data['cookies'])) {
			$ret .= '<label>Cookies:</label> '.count($data['cookies']).'<br />';
		}
		if (!empty($data['charset'])) {
			$ret .= '<label>Charset:</label> '.$data['charset'].'<br />';
		}
		$ret .= 'Sizes:<br />';
		foreach ($data['size'] as $header => $value) {
			$ret .= '<label>'.$header.':</label> '.$value.'<br />';
		}
		
		return $ret;
	}
	
	private function http_performance($data)
	{
		$ret = '';
		$ret .= '<label>Speed:</label> '.$data['speed'].'<br />';
		$ret .= '<label>Total Size:</label> '.$data['size']['download'].'<br />';
		$ret .= 'Times:<br />';
		foreach ($data['time'] as $header => $value) {
			$ret .= '<label>'.$header.':</label> '.$value.'<br />';
		}
		
		return $ret;
	}
	
	private function http_server($data)
	{
		$ret = '';
		if (!empty($data['os'])) {
			$ret .= '<label>OS:</label> '.$data['os'].'<br />';
		}
		if (!empty($data['server'])) {
			$ret .= '<label>Server:</label> ' . $data['server name'];
			if (!empty($data['server version'])) {
				$ret .= ' ('.$data['server version'].')';
			}
			$ret .= '<br />';
		}
		if (!empty($data['lang'])) {
			$ret .= '<label>Language:</label> '.$data['lang'].'<br />';
		}
		foreach ($data['headers'] as $header => $value) {
			if (substr($header, 0, 2) == 'X-') {
				$ret .= '<label>'.$header.':</label> '.$value.'<br />';
			}
		}
		
		return $ret;
	}
	
	public function http_cookies($data)
	{
		$ret = '';
		if (!empty($data)) {
			foreach ($data as $cookie_data) {
				$parts = explode('; ', $cookie_data);
				$cookie = array();
				$cookie_name = '';
				foreach ($parts as $part) {
					list($name, $value) = explode('=', $part);
					if (empty($cookie_name)) {
						$cookie_name = $name;
					}
					$cookie[$name] = $value;
				}
				if (!empty($cookie['expires'])) {
					$cookie['expires'] = date('m/d/Y', strtotime($cookie['expires']));
				}
				
				$setup = array();
				foreach ($cookie as $info => $val) {
					if ($info != $cookie_name) {
						$setup[] = $info.': '.$val;
					}
				}
				$setup = implode(', ', $setup);
				
				$ret .= '<label>'.$cookie_name.':</label> '.$cookie[$cookie_name].' ('.$setup.')<br />';
			}
		}
		
		return $ret;
	}
}
?>