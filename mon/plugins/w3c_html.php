<?php
class W3cHtml extends Service
{
	public function __construct()
	{
		$this->name = 'W3C HTML Validation';
	}
	
	public function parse()
	{
		$data = $this->data;
		$this->block('Result: '.$data['result'], 'Result');
		$this->block('Status: '.$data['status'], 'Status');
		if (!empty($data['outline'])) {
			$this->block('<pre>'.$data['outline'].'</pre>', 'Outline');
		}
		$this->block($data['errors'], 'Errors');
	}
	
	protected function _process($url)
	{
		$page = file_get_contents('http://validator.w3.org/check?group=1&outline=1&uri=http://'.$url);
		
		$matches = array();
		preg_match('|<title>[\r\n\t ]*\[(?<result>[A-Z][a-z]+)\]|', $page, $matches);
		$data['result'] = $matches['result'];
		
		//echo htmlentities($page);
		preg_match('|<th>Result:<\/th>[\n ]*<td colspan="2" class="(?<result>[a-z]+)">(?<status>[^<]+)<|', $page, $matches);
		$data['status'] = trim($matches['status']);
		
		if (preg_match('|<pre class="outline">([^<]+)</pre>|', $page, $matches)) {
			if (strlen(trim($matches[1])) == 0) {
				$data['outline'] = '';
			} else {
				$data['outline'] = $matches[1];
			}
		} else {
			$data['outline'] = '';
		}
		
		if (preg_match('|<div id="result">(.*)</div>[\n ]*<div id="|s', $page, $matches)) {
			$data['errors'] = $matches[1];
		} else {
			$data['errors'] = '';
		}
		
		return $data;
	}
}
?>