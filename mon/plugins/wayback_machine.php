<?php
class WaybackMachine extends Service
{
	public function __construct()
	{
		$this->name = 'The Wayback Machine';
	}
	
	public function parse()
	{
		$this->block($this->data, 'History');
	}
	
	protected function _process($url)
	{
		$url = 'http://web.archive.org/web/*/'.$url;
		@$page = file_get_contents($url);
		if (!empty($page)) {
			return '';
		}
		
		$start_pos = strpos($page, '<!-- SEARCH RESULTS -->');
		$end_pos = strpos($page, '<!-- /SEARCH RESULTS -->');
		$table = substr($page, $start_pos, $end_pos - $start_pos);
		
		return $table;
	}
}
?>