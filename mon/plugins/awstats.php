<?php
class Awstats extends Service
{
	private $base_url;
	private $date = null;
	
	public function __construct()
	{
		$this->name = 'AWStats';
	}
	
	public function setDate($date)
	{
		$this->date = $date;
	}
	
	public function setBaseUrl($url)
	{
		$this->base_url = $url;
	}
	
	public function parse()
	{
		$data = $this->data;
		if (!empty($data['hits'])) {
			$this->block($this->aws_stats($data), 'Statistics');
			$this->block($this->aws_table($data['keywords']), 'Keywords');
			$this->block($this->aws_stories($data), 'Top Items');
			$this->block($this->aws_pages($data), 'Top Pages');
			$this->block('<a href="'.$data['url'] . '" target="_blank">AWStats</a>', 'Full Report');
		}
	}
	
	protected function _process($url)
	{
		$url = $this->base_url.'awstats.pl?config='.$url;
		if (!empty($this->date)) {
			$m = date('m', $this->date);
			$y = date('Y', $this->date);
			$url .= '&amp;month='.$m.'&amp;year='.$y;
		}
		$data['url'] = $url;
		$page = file_get_contents($url.'&amp;framename=mainright');
		
		$html = htmlentities($page);
		$html = str_replace("\n", '\\n', $html);
		//echo $html;
		//echo $page;
		
		$matches = array();
		if (preg_match('|Viewed traffic[^<]*</td><td><b>([0-9]*)</b><br />&nbsp;</td><td><b>([0-9]*)</b><br />[^<]*</td><td><b>([0-9]*)</b><br />[^<]*</td><td><b>([0-9]*)</b><br />[^<]*</td><td><b>([^<]*)</b><br />\(([^<]*)\)</td></tr>|', $page, $matches)) {
			$data['unique'] = $matches[1];
			$data['visitors'] = $matches[2];
			$data['views'] = $matches[3];
			$data['hits'] = $matches[4];
			$data['data'] = $matches[5];
			$data['data_per_visitor'] = $matches[6];
		}
		
		$data['pages'] = $this->table($url.'&amp;framename=mainright&amp;output=urldetail');
		$data['keywords'] = $this->table($url.'&amp;framename=mainright&amp;output=keywords');
		
		return $data;
	}
	
	private function table($url)
	{
		$page = file_get_contents($url);
		
		$data = array();
		$matches = array();
		if (preg_match_all('|<tr><td class="aws"><a href="[^>]*" target="url">([^>]*)</a></td><td>([0-9]*)</td>|', $page, $matches)) {
			//var_dump($matches);
			foreach ($matches[1] as $id => $match) {
				$data[$match] = $matches[2][$id];
			}
		}
		
		return $data;
	}
	
	private function aws_stats($data) {
		$ret = '
<label>Unique Visitors:</label> '.$data['unique'].'<br />
<label>Visitors:</label> ' . $data['visitors'].'<br />
<label>Views:</label> '.$data['views'].'<br />
<label>Hits:</label> '.$data['hits'].'<br />';

		return $ret;
	}
	
	private function aws_stories($data) {
		global $site;
		
		$ret = '
<table>
<tr><th>Story</th><th>Views</th></tr>';
		$pages = $data['pages'];
		$i = 0;
		foreach ($pages as $url => $views) {
			$url = substr($url, 1);
			if (is_numeric($url)) {
				$ret .= '<tr><td><a href="http://'.$site.'/'.$url.'">#'.$url.'</a></td><td>'.$views.'</td></tr>';
				if ($i++ >= 10) {
					break;
				}
			}
		}
		$ret .= '</table>';
		
		return $ret;
	}
	
	private function aws_pages($data) {
		global $site;
		
		$ret = '
<table>
<tr><th>Page</th><th>Views</th></tr>';
		$pages = $data['pages'];
		$i = 0;
		foreach ($pages as $url => $views) {
			$ret .= '<tr><td><a href="http://'.$site.$url.'">'.$url.'</a></td><td>'.$views.'</td></tr>';
			if ($i++ >= 50) {
				break;
			}
		}
		$ret .= '</table>';
		
		return $ret;
	}
	
	private function aws_table($data) {
		$ret = '
<table>
<tr><th>Item</th><th>Count</th></tr>';
		$i = 0;
		foreach ($data as $url => $views) {
			$url = substr($url, 1);
			if (is_numeric($url)) {
				$ret .= '<tr><td>'.$url.'</td><td>'.$views.'</td></tr>';
				if ($i++ >= 10) {
					break;
				}
			}
		}
		$ret .= '</table>';
		
		return $ret;
	}
}
?>