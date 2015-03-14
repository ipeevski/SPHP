<?php
class W3cCss extends Service
{
	public function __construct()
	{
		$this->name = 'W3C CSS Validation';
	}
	
	public function parse()
	{
		$data = $this->data;
		$this->block('Result: '.$data['result'], 'Result');
		$this->block($data['errors'], 'Errors');
	}
	
	protected function _process($url)
	{
		$page = file_get_contents('http://jigsaw.w3.org/css-validator/validator?uri=http://'.$url);
		
		$data['errors'] = '';
		if (preg_match('|<div id=.congrats.>|', $page)) {
			$data['result'] = 'Valid';
		} else {
			$data['result'] = 'Invalid';
			
			$matches = array();
			if (preg_match('|<div class=.error-section.>(.*?</table>)\n *</div>|s', $page, $matches)) {
				$data['errors'] = $matches[1];
			}
		}
		
		return $data;
	}
}
?>