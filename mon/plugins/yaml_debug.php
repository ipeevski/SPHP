<?php
class YamlDebug extends Service
{
	public function __construct()
	{
		$this->name = 'YAML Debug';
	}
	
	public function parse()
	{
		$data = $this->data;
		echo $this->pop_window($data);
	}
	
	protected function _process($url)
	{
		$url = 'http://'.$url;
		
		return $url;
	}

	private function pop_window($url)
	{
		$page = file_get_contents($url);
		
		
		$page = str_replace('src="'.$url, 'src="', $page);
		$page = str_replace('href="'.$url, 'href="', $page);
		
		// Ignore external links
		$page = str_replace('src="http', 'src ="http', $page);
		$page = str_replace('href="http', 'href ="http', $page);
		
		$page = str_replace('src="', 'src="'.$url.'/', $page);
		$page = str_replace('href="', 'href="'.$url.'/', $page);
		$page = str_replace('"', '\\"', $page);
		$page = str_replace('/', '\\/', $page);
		$page = str_replace("\r", "", $page);
		$page = str_replace("\n", "\\\n", $page);
		
		$ret = '
		<button onclick="return yaml_pop();">Test</button>
		<script type="text/javascript">
		
		var win;
		function yaml_pop()
		{			
			win = window.open("about:blank", "Testing", "menubar=no,width=1024,height=768,toolbar=no");
			
			win.document.write("'.$page.'");
			win.document.write(\'<script type="text/javascript" class="ydebug" src="http://debug.yaml.de/debugger.js"><\/script>\');
		}
		</script>
		';
		
		return $ret;
	}
}
?>