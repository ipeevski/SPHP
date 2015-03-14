<?php
class Service
{
	protected $name;
	protected $data = array();
	protected static $cache = array();
	protected $domain;
	public function process($url)
	{
		//if (!isset(self::$cache[$url])) {
			self::$cache[$url] = $this->_process($url);
		//}
		$this->data = self::$cache[$url];
		
		return $this->data;
	}
	
	protected function _process($url)
	{
		return array();
	}
	
	public function parse()
	{
		$this->block('test', 'Test');
	}
	
	public function display($url)
	{
		$this->domain = $url;
		$this->process($url);
		
		echo '<div class="service">';
			echo '<div class="title" onclick="$(this).next().slideToggle()">'.$this->name.'</div>';
			echo '<div class="block_body">';
				$this->parse($url);
			echo '</div>';
		echo '</div>';
	}
	
	public function email($url)
	{
		$this->domain = $url;
		$this->process($url);
		
		ob_start();
		$this->parse($url);
		$ret = ob_get_clean();
		
		$ret = 
		'<table width="98%" align="center" style="border: 1px solid #ccc; padding: 2px; margin-bottom: 10px">
			<tr><td style="background: #ccc; font-family: Tahoma, Verdana, Ariel, sans-serif; font-size: 18px; font-weight: bold; text-align: center">'.$this->name.' Report</td></tr>
			<tr><td>
				' . $ret . '
			</td></tr>
		</table>';
		
		return $ret;
	}
	
	public function block($data, $title = '') {
		if (!empty($data)) {
			echo '<div class="block">';
			if (!empty($title)) {
				echo '<h2 class="title" onclick="$(this).next().slideToggle()">'.$title.'</h2>';
			}
			echo '<div class="block_body">'.$data.'</div>';
			echo '</div>';
		}
	}
	
	public function block_table($data, $title = '', $headers = array('name', 'value'))
	{
		$ret = '<table><tr><th>'.$headers[0].'</th><th>'.$headers[1].'</th></tr>';
		foreach ($data as $name => $val) {
			$ret .= '<tr><th align="left">'.$name.'</th><td>' . $val . '</td></tr>';
		}
		$ret .= '</table>';
		
		return $this->block($ret, $title);
	}
	
	// Make an http request to the specified URL and return the result
	function curl_http($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		curl_close($ch);
		
		return $result;
	}
}
?>