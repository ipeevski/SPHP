<?php
class Html extends Service
{
	public function __construct()
	{
		$this->name = 'HTML';
	}
	
	public function parse()
	{
		$data = $this->data;
		$this->block($this->html_size($data), 'Size');
		$this->block($this->html_search($data), 'Search');
		if (!empty($data['keywords'])) {
			$this->block($this->html_list($data['keywords']), 'Keywords');
		}
		$this->block($this->html_list($data['meta']), 'Meta Tags');
		if (!empty($data['scripts'])) {
			$this->block($this->html_links_list($data['scripts']), 'Scripts');
		}
		if (!empty($data['rel'])) {
			$this->block($this->html_rel($data['rel']), 'Additional Connections');
		}
		if (!empty($data['links'])) {
			$this->block($this->html_links_list($data['links']), 'Links');
		}
		if (!empty($data['favicon'])) {
			$this->block('<img src="'.$data['favicon'].'" />', 'Favicon');
		}
		// Create preview image for image lists
		echo '<img id="html_image" onclick="'."if ($(this).css('background').substring(0, 5) == 'white') $(this).css('background', 'black'); else $(this).css('background', 'white');".' " style="display: none; position: absolute; background: white; border: 1px solid gray; padding: 2px; margin: 24px 0px 0px 10px" />';
		if (!empty($data['images'])) {
			$this->block($this->html_image_list($data['images']), 'Images');
		}
		if (!empty($data['external_images'])) {
			$this->block($this->html_image_list($data['external images']), 'External Images');
		}
		$this->block($this->html_standards($data['page']), 'Standards Compliance');
		$this->block($this->html_tags($data['page'], array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'cite', 'strong', 'form', 'label', 'sup', 'sub', 'ol', 'ul')), 'Semantic Structure');
		$this->block($this->html_tags($data['page'], array('div')), 'Layout Structure');
		$this->block($this->html_tags($data['page'], array('applet', 'center', 'dir', 'font', 'menu', 's', 'strike', 'u')), 'Deprecated Tags');
		$this->block($this->readability($data['page']), 'Readability Level');
		$this->block($this->html_text($data['page']), 'Text Mode');
		$this->block($this->html_text($data['page'], 'seo'), 'SEO Mode');
	}
	
	protected function _process($url)
	{
		$url = 'http://'.$url;
		$this->url = $url;
		$page = file_get_contents($url);
		
		$matches = array();
		$m = array();
		
		$data['page'] = $page;
		if (preg_match('|<title>(.*)</title>|i', $page, $matches)) {
			$data['title'] = $matches[1];
		}
		if (preg_match('|<meta name="description" content="([^"]*)"|i', $page, $matches)) {
			$data['description'] = $matches[1];
		}
		if (preg_match('|<meta name="keywords" content="([^"]*)"|i', $page, $matches)) {
			$keywords = explode(',', $matches[1]);
			foreach ($keywords as $k => $word) {
				$keywords[$k] = trim($word);
			}
			$data['keywords'] = $keywords;
		}
		if (preg_match_all('|<meta[^>]*>|i', $page, $matches)) {
			foreach ($matches[0] as $match) {
				preg_match('/(?:name|http-equiv)="([^"]*)"/i', $match, $m);
				$name = $m[1];
				preg_match('|content="([^"]*)"|i', $match, $m);
				$content = $m[1];
				$data['meta'][$name] = $content;
			}
		}
		$data['size']['scripts'] = 0;
		if (preg_match_all('|<script .*src="([^"]*)"|i', $page, $matches)) {
			$data['scripts'] = $matches[1];
			foreach ($matches[1] as $script_url) {
				if (substr($script_url, 0, 7) != 'http://') {
					$script_url = $url.'/'.$script_url;
				}
				$data['size']['scripts'] += strlen(file_get_contents($script_url));
			}
		}
		$data['css'] = array();
		$data['size']['css'] = 0;
		$data['size']['images'] = 0;
		if (preg_match_all('|<link[^>]*>|i', $page, $matches)) {
			foreach ($matches[0] as $match) {
				if (preg_match('|href="([^"]*)"|i', $match, $m)) {
					$link = $m[1];
				}
				if (preg_match('|rel="([^"]*)"|i', $match, $m)) {
					$rel = $m[1];
					$data['rel'][$link] = $rel;
					if ($rel == 'stylesheet') {
						$data['css'][] = $link;
						$link_url = $link;
						if (substr($link, 0, 7) != 'http://') {
							$link_url = $url.'/'.$link;
						}
						$data['size']['css'] += strlen(file_get_contents($link_url));
					}
					if ($rel == 'icon') {
						$data['favicon'] = $link;
					}
				}
			}
		}
		if (preg_match_all('|<a .*href="([^"]*)"|i', $page, $matches)) {
			$data['links'] = $matches[1];
		}
		if (preg_match_all('|<img [^>]*src="(?<img>[^"]*)"[^>]*>|i', $page, $matches)) {
			$data['images'] = $matches['img'];
			$data['external images'] = array();
			$data['images_alt'] = array();
			foreach ($data['images'] as $k => $image) {
				if (substr($image, 0, 7) == 'http://' and
					substr($image, 0, strlen($url)) != $url) {
					$data['external images'][] = $image;
				}
				$image_url = $image;
				if (substr($image, 0, 7) != 'http://') {
					$image_url = $url.'/'.$image;
				}
				$data['size']['images'] += strlen(file_get_contents($image_url));
				
				if (preg_match('|[\s]alt="([^"]*)"|', $matches[0][$k], $match)) {
					$data['images_alt'][$k] = $match[1];
				}
			}
		}
		$data['size']['html'] = strlen($page);
		if (empty($data['favicon'])) {
			$url .= '/favicon.ico';
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_NOBODY, 1);
			curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			
			if ($info['http_code'] == 200) {
				$data['favicon'] = $url;
			}
		}
		
		return $data;
	}
	
	private function html_size($data)
	{
		$ret = '';
		$ret .= 'Size: ' . $data['size']['html'] . '<br />';
		if (!empty($data['images'])) {
			$ret .= 'Images: ' . count($data['images']) . ' ('.$data['size']['images'].')<br />';
		}
		$ret .= 'Links: ' . count($data['css']) . ' ('.$data['size']['css'].')<br />';
		if (!empty($data['scripts'])) {
			$ret .= 'Scripts: ' . count($data['scripts']) . ' ('.$data['size']['scripts'].')<br />';
		}
		
		return $ret;
	}

	private function html_search($data)
	{
		$ret = '';
		if (!empty($data['title'])) {
			$ret .= '<label>Title:</label> '.$data['title'].'<br />';
		}
		if (!empty($data['description'])) {
			$ret .= '<label>Description:</label> '.$data['description'].'<br />';
		}
		if (!empty($data['keywords'])) {
			$ret .= '<label>keywords:</label> '.implode(', ', $data['keywords']).'<br />';
		}
		
		return $ret;
	}
	
	private function html_list($data)
	{
		$ret = '';
		if (!empty($data)) {
			foreach ($data as $tag => $val) {
				if (!is_numeric($tag)) {
					$ret .= '<label>'.$tag.':</label> '.$val.'<br />';
				} else {
					$ret .= $val.'<br />';
				}
			}
		}
		
		return $ret;
	}
	
	private function html_links_list($data)
	{
		$ret = '';
		if (!empty($data)) {
			foreach ($data as $tag => $val) {
				$val = '<a href="'.$this->url($val).'" target="_blank">'.$val.'</a>';
				if (!is_numeric($tag)) {
					$ret .= '<label>'.$tag.':</label> '.$val.'<br />';
				} else {
					$ret .= $val.'<br />';
				}
			}
		}
		
		return $ret;
	}
	
	private function html_image_list($data)
	{
		$ret = 'Images: ' . count($data);
		if (!empty($data)) {
			$ret = '
			<table>
			<tr>
				<th>thumb</th>
				<th>path</th>
				<td rowspan="'.count($data).'"></td>
			</tr>';
			foreach ($data as $val) {
				$ret .= '<tr>
				<td><img src="'.$this->url($val).'" onclick="'."
				if ($('#html_image').attr('src') != $(this).attr('src')) {
					$('#html_image').hide();
					$('#html_image').attr('src', $(this).attr('src'));
				}
				$('#html_image').css('top', $(this).offset().top);
				$('#html_image').toggle();
				".'" height="20" /></td>
				<td><a href="'.$this->url.'" target="_blank">'.$val.'</a></td></tr>';
			}
			$ret .= '</table>';
		}
		
		return $ret;
	}
	
	private function html_rel($data)
	{
		$ret = '';
		
		if (!empty($data)) {
			$ret .= 'Stylesheets:<br />';
			foreach ($data as $link => $type) {
				if (strtolower($type) == 'stylesheet') {
					$ret .= '<a href="'.$this->url($link).'" target="_blank">'.$link.'</a><br />';
					unset($data[$link]);
				}
			}
			
			$ret .= 'Other<br />';
			foreach ($data as $link => $type) {
				$ret .= '<label>'.$type.':</label> <a href="'.$this->url($link).'" target="_blank">'.$link.'</a><br />';
			}
		}
		
		return $ret;
	}
	
	private function html_standards($data)
	{
		$ret = '';
		$matches = array();
		if (preg_match('|<!DOCTYPE [Hh][Tt][Mm][Ll] PUBLIC "-//W3C//DTD (?<standard>X?HTML) (?<ver>[0-9\.]+)(?<mod> [a-zA-Z]+)?//EN"[\n\r\t ]+"(?<dtd>[^"]+)">|', $data, $matches)) {
			$ret .= 'Doctype declaration:<br />' . htmlentities($matches[0]).'<br />';
			$ret .= 'Standard: ' . $matches['standard'] .' (version: '.$matches['ver']. (!empty($matches['mod']) ? '; Modification: ' . $matches['mod'] : '').')<br />';
			$ret .= 'DTD: ' . $matches['dtd'] .'<br />';
		} else {
			$ret = 'No Doctype found!';
		}
		
		return $ret;
	}
	

	private function readability($data)
	{
		$ret = '';
		$msgs = array();
		$stats = new TextStatistics();
		$ret .= 'Flesh Kincaid Reading Ease: ' . $stats->flesch_kincaid_reading_ease($data) . '<br />';
		if ($stats->flesch_kincaid_reading_ease($data) < 60) {
			$msg = 'Readability level is too advanced (under 60) and it might make the text harder to understand for the average user.';
			$ret .= '<div class="warning">'.$msg.'</div>';
			$msgs[] = $msg;
		}
		$ret .= 'Flesh Kincaid Grade Level: ' . $stats->flesch_kincaid_grade_level($data) . '<br />';
		if ($stats->flesch_kincaid_grade_level($data) > 8) {
			$msg = 'Educational level is too high (over 8) and it might make the text harder to understand for the average user.';
			$ret .= '<div class="warning">'.$msg.'</div>';
			$msgs[] = $msg;
		}
		$ret .= 'Gunning Fog Level: ' . $stats->gunning_fog_score($data) . '<br />';
		if ($stats->gunning_fog_score($data) > 8) {
			$msg = 'Educational level is too high (over 8) and it might make the text harder to understand for the average user.';
			$ret .= '<div class="warning">'.$msg.'</div>';
			$msgs[] = $msg;
		}
		$ret .= 'Coleman Liau Level: ' . $stats->coleman_liau_index($data) . '<br />';
		if ($stats->coleman_liau_index($data) > 8) {
			$msg = 'Educational level is too high (over 8) and it might make the text harder to understand for the average user.';
			$ret .= '<div class="warning">'.$msg.'</div>';
			$msgs[] = $msg;
		}
		$ret .= 'SMOG Index: ' . $stats->smog_index($data) . '<br />';
		if ($stats->smog_index($data) > 8) {
			$msg = 'Educational level is too high (over 8) and it might make the text harder to understand for the average user.';
			$ret .= '<div class="warning">'.$msg.'</div>';
			$msgs[] = $msg;
		}
		$ret .= 'Automated Readibility Index: ' . $stats->automated_readability_index($data) . '<br />';
		if ($stats->automated_readability_index($data) > 8) {
			$msg = 'Educational level is too high (over 8) and it might make the text harder to understand for the average user.';
			$ret .= '<div class="warning">'.$msg.'</div>';
			$msgs[] = $msg;
		}
		
		if (!empty($msgs)) {
			$ret .= '<br /><h2>Advice:</h2><ul>';
			foreach ($msgs as $msg) {
				$ret .= '<li>'.$msg.'</li>';
			}
			$ret .= '</ul>';
		}
		
		return $ret;
	}
	
	private function html_text($data, $mode = 'text')
	{
		$ret = $data;
		$head = '';
		$count = 0;
		
		$ret = str_replace(array("\n", "\t"), ' ', $ret);
		$ret = preg_replace('|<script[^>]*>[^<]*</script>|i', '', $ret, -1, $count);
		$head .= 'Stripped &lt;script&gt; tags: ' . $count . '<br />';
		$ret = preg_replace('|<style[^>]*>[^<]*</style>|i', '', $ret, -1, $count);
		$head .= 'Stripped &lt;style&gt; tags: ' . $count . '<br />';
		$ret = preg_replace('|<object .+</object>|i', '', $ret, -1, $count);
		$head .= 'Stripped &lt;object&gt; tags: ' . $count . '<br />';
		$ret = preg_replace('|<embed .+</embed>|i', '', $ret, -1, $count);
		$head .= 'Stripped &lt;embed&gt; tags: ' . $count . '<br />';
		$ret = preg_replace('|<[^>]+ />|i', '', $ret, -1, $count);
		$head .= 'Stripped self-closing tags: ' . $count . '<br />';
		$ret = preg_replace('| style="[^"]+"|i', '', $ret, -1, $count);
		$head .= 'Stripped styles: ' . $count . '<br />';
		if ($mode == 'text') {
			//$ret = preg_replace('|<[^>]+>|i', '', $ret, -1, $count);
			//$head .= 'Stripped all html tags: ' . $count . '<br />';
			$ret = strip_tags($ret);
		} elseif ($mode == 'seo') {
			$ret = preg_replace('|<h([1-3])>(.+)</h[1-3]>|i', '<strong>$1: $2</strong>', $ret, -1, $count);
			$head .= 'Stripped headers: ' . $count . '<br />';
			$ret = preg_replace('|<title>(.+)</title>|i', '<h1>$1</h1>', $ret, -1, $count);
			$head .= 'Processed title: ' . $count . '<br />';
		}
		$ret = str_replace('  ', ' ', $ret);
		
		$ret = $head.$ret;
		
		return $ret;
	}
	
	private function html_tags($data, $tags)
	{
		$ret = '';
		
		$tags = implode('|', $tags);
		$matches = array();
		if (preg_match_all('#<(?<tag>'.$tags.')([^a-zA-Z>][^>]+)?>(?<content>[^<]+)</#', $data, $matches)) {
			foreach ($matches['tag'] as $k => $tag) {
				$ret .= '['.$tag.'] '.$matches['content'][$k].'<br />';
			}
		}
		
		return $ret;
	}
	
	private function url($path) {
		$url = '';
		if (substr($path, 0, 7) == 'mailto:') {
			// do nothing
		} elseif (substr($path, 0, 7) != 'http://') {
			$url = 'http://'.$this->domain;
			if (empty($path) or $path[0] != '/') {
				$url .= '/';
			}
		}
		
		return $url . $path;
	}
}
?>