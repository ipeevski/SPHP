<?php
class Seo extends Service
{
	public function __construct()
	{
		$this->name = 'SEO';
	}
	
	public function parse()
	{
		$data = $this->data;
		$this->block($this->html_size($data), 'Size');
//		$this->block($this->html_search($data), 'Meta Data');
		$this->block($this->meta_report($data), 'Keywords');
		$this->block($this->page_report($data['page']), 'Page Report');
		$this->block($this->readability($data['page']), 'Readability');
		$this->block($this->images($data), 'Images');
		$this->block($this->search_engines($data), 'Search Engines');
		$this->block($this->google($data['google']), 'Google Stats');
		$this->block($this->blogs($data), 'Blog Statistics');
		$this->block($this->domain($data['domain']), 'Domain Stats');
		$this->block($this->html_list($data['meta']), 'Meta Tags');
		$this->block($this->html_image_list($data), 'Images');
		$this->block($this->html_standards($data['page']), 'Standards Compliance');
		$this->block($this->html_tags($data['page'], array('h1', 'h2', 'h3', 'h4', 'h5', 'h6')), 'Use of Headings');
		$this->block($this->html_tags($data['page'], array('font')), 'Use of Font Tags');
		$this->block($this->html_text($data['page'], 'seo'), 'SEO Mode');
	}
	
	protected function _process($url)
	{
		$full_url = 'http://'.$url;
		$this->url = $full_url;
		$page = file_get_contents($full_url);
		
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
		}
		$data['css'] = array();
		if (preg_match_all('|<link[^>]*>|i', $page, $matches)) {
			foreach ($matches[0] as $match) {
				if (preg_match('|href="([^"]*)"|i', $match, $m)) {
					$link = $m[1];
				}
				if (preg_match('|rel="([^"]*)"|i', $match, $m)) {
					$rel = $m[1];
					$data['rel'][$link] = $rel;
				}
			}
		}

		if (preg_match_all('|<img [^>]*src="(?<img>[^"]*)"[^>]*>|i', $page, $matches)) {
			$data['images'] = $matches['img'];
			$data['images_alt'] = array();
			foreach ($data['images'] as $k => $image) {
				if (preg_match('|[\s]alt="(.*)"|', $matches[0][$k], $match)) {
					$data['images_alt'][$k] = $match[1];
				}
			}
		}
		$data['size']['html'] = strlen($page);
		
		$domain = new Domain();
		$data['domain'] = $domain->process($url);
		$data['domain']['created'] = $domain->duration($data['domain']['created']);
		$data['domain']['expires'] = $domain->duration(time(), $data['domain']['expires']);
		
		// Google
		$google_cache = file_get_contents('http://google.com/search?strip=1&q=cache:'.$url);
		if (preg_match('|It is a snapshot of the page as it appeared on ([^\.]+)\.|', $google_cache, $matches)) {
			$data['google']['crawl'] = strtotime($matches[1]);
		} else {
			$data['google']['crawl'] = 'unknown';
		}
		$google = new GoogleRank();
		$data['google']['rank'] = $google->rank($url);
		
		$google_page = file_get_contents('http://www.google.com/search?q=link:'.$url);
		if (preg_match('|([0-9,]+) result|', $google_page, $matches)) {
			$data['google']['backlinks'] = $matches[1];
		};
		
		$google_page = file_get_contents('http://www.google.com/#q=site:'.$url);
		if (preg_match('|([0-9,]+) result|', $google_page, $matches)) {
			$data['google']['indexed'] = $matches[1];
		};
		
		$yahoo_page = file_get_contents('http://siteexplorer.search.yahoo.com/search?bwm=i&p=http://'.$url);
		if (preg_match('|Inlinks \(([0-9,]+)\)|', $yahoo_page, $matches)) {
			$data['yahoo']['backlinks'] = $matches[1];
		};
		
		$msn_page = file_get_contents('http://www.bing.com/search?q=inbody:'.$url);
		//echo htmlentities($msn_page);
		if (preg_match('|1-10 of ([0-9,]+) results</span>|', $msn_page, $matches)) {
			$data['msn']['backlinks'] = str_replace(',', '', $matches[1]);
		};
		
		// Del.icio.us
		$delicious_page = file_get_contents('http://badges.del.icio.us/feeds/json/url/data?hash='.md5('http://'.$url.'/'));
		$data['delicious'] = json_decode($delicious_page, true);
		
		// Technocrati
		global $config;
		if (!empty($config['technorati'])) {
			$tech_page = file_get_contents('http://api.technorati.com/cosmos?limit=100&key='.$config['technorati']['apikey'].'&url='.$url);
			
			$xml = new SimpleXMLElement($tech_page);
			$data['technorati']['backlinks'] = (string) $xml->document->result->inboundlinks;
			if ($xml->document->result->rank) {
				$data['technorati']['rank'] = (string) $xml->document->result->rank;
			}
			if ($xml->document->result->inboundblogs) {
				$data['technorati']['blogs'] = (string) $xml->document->result->inboundblogs;
			}
			foreach ($xml->document->item as $blog) {
				$data['technorati']['blog'][(string) $blog->weblog->name] = (string) $blog->nearestpermalink;
			}
		}

		$alltheweb_page = file_get_contents('http://www.alltheweb.com/search?q=link.all:'.$url.'+-site:'.$url);
		if (preg_match('|<span class="ofSoMany">([0-9]+)</span>|', $alltheweb_page, $matches)) {
			$data['alltheweb']['backlinks'] = str_replace(',', '', $matches[1]);
		};

		$dmoz_page = file_get_contents('http://search.dmoz.org/cgi-bin/search?search='.$url);
		if (preg_match('|<b>Open Directory Sites</b></font> \(1-[0-9]+ of ([0-9]+)\)|', $dmoz_page, $matches)) {
			$data['dmoz']['listed'] = str_replace(',', '', $matches[1]);
		};
		
		return $data;
	}
	
	private function html_size($data)
	{
		$ret = '';
		$ret .= 'Size: ' . $data['size']['html'] . '<br />';
		
		return $ret;
	}

	private function google($data)
	{
		$ret = 'Last Crawled on ' . date('M d, Y', strtotime($data['crawl'])) . '<br />';
		$ret .= 'Page Rank: ' . $data['rank'] . '<br />';
		if (!empty($data['indexed'])) {
			$ret .= 'Indexed Pages: '. $data['indexed'] . '<br />';
			$ret .= 'Back Links: ' . $data['backlinks'] . '<br />';
		}
		
		return $ret;
	}
	
	private function search_engines($data)
	{
		$msgs = array();
		$ret = '<h2>Google</h2>';
		$ret .= 'Google Page Rank: ' . $data['google']['rank'] . '<br />';
		if (empty($data['google']['indexed'])) {
			$msg = 'No pages indexed - google isn\'t crawling the site. <a href="http://www.google.com/webmasters/tools" target="_blank">Submit a sitemap</a>.';
			$ret .= '<div class="warning">'.$msg.'</div>';
			$msgs[] = $msg;
		} else {
			$ret .= 'Indexed Pages: '. $data['google']['indexed'] . '<br />';
			$ret .= 'Back Links: ' . $data['google']['backlinks'] . '<br />';
		}

		if (!empty($data['msn'])) {
			$ret .= '<h2>MSN (Bing)</h2>';
			$ret .= 'Back Links: ' . $data['msn']['backlinks'] . '<br />';
		}

		if (!empty($data['yahoo'])) {
			$ret .= '<h2>Yahoo!</h2>';
			$ret .= 'Back Links: ' . $data['yahoo']['backlinks'] . '<br />';
		}
		
		if (!empty($data['alltheweb'])) {
			$ret .= '<h2>All The Web</h2>';
			$ret .= 'Back Links: ' . $data['alltheweb']['backlinks'] . '<br />';
		}
		
		$ret .= '<h2>DMOZ</h2>';
		if (empty($data['dmoz']['listed'])) {
			$msg = 'No pages listed in the Open Directory DMOZ. <a href="http://www.dmoz.org/add.html" target="_blank">Submit here</a>.';
			$ret .= '<div class="warning">'.$msg.'</div>';
			$msgs[] = $msg;
		} else {
			$ret .= 'Listed: ' . $data['dmoz']['listed'] . '<br />';
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
	
	private function blogs($data)
	{
		$ret = '';
		if (!empty($data['technorati'])) {
			$ret = 'Technorati links: ' . $data['technorati']['backlinks'] . '<br />';
			if (!empty($data['technorati']['rank'])) {
				$ret .= 'Technorati rank: ' . $data['technorati']['rank'] . '<br />';
			}
			if (!empty($data['technorati']['blog'])) {
				$ret .= 'Blogs: <br />';
				foreach ($data['technorati']['blog'] as $blog => $link) {
					$ret .= '<a href="'.$link.'">'.$blog.'</a><br />';
				} 
			}
		}
			
		return $ret;
	}
	
	private function domain($data)
	{
		$msgs = array();
		$ret = 'Domain Created: ' . $data['created'] . '<br />';
		if ($data['age'] < 365) {
			$msg = 'Domain too young (search engines are affected by domain stability). Should be older than an year.';
			$ret .= '<div class="warning">'.$msg.'</div>';
			$msgs[] = $msg;
		}
		$ret .= 'Domain Expires: ' . $data['expires'] . '<br />';
		
		if (!$data['www_redirect']) {
			$msg = 'No Permanent redirect between www. and plain domain.';
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
	
	private function meta_report($data)
	{
		$ret = '';
		$msgs = array();
		if (!empty($data['title'])) {
			$ret .= '<label>Title:</label> '.$data['title'].'<br />';
			if (strlen($data['title']) > 40) {
				$msg = 'Long Title tag - try to limit to 40 characters.';
				$ret .= '<div class="warning">'.$msg.'</div>';
				$msgs[] = $msg;
			}
		} else {
			$msg = 'Missing Meta Title';
			$ret .= '<div class="warning">'.$msg.'</div>';
			$msgs[] = $msg;
		}
		if (!empty($data['description'])) {
			$ret .= '<label>Description:</label> '.$data['description'].'<br />';
			if (strlen($data['description']) > 140) {
				$msg = 'Meta description is too long. It should be 
				no longer than 140 characters. Currently, it is 
				<strong>'.strlen($data['description']).'</strong> characters.';
				$ret .= '<div class="warning">'.$msg.'</div>';
				$msgs[] = $msg;
			}
		} else {
			$msg = 'Missing Meta Description';
			$ret .= '<div class="warning">'.$msg.'</div>';
			$msgs[] = $msg;
		}
		if (!empty($data['keywords'])) {
			$ret .= '<label>Keywords:</label> '.implode(', ', $data['keywords']).'<br />';
			if (count($data['keywords']) > 10) {
				$msg = 'Using a high number of keywords loses the 
				effectiveness of the important ones. Try to keep them at less than 10. 
				Currently there are <strong>'.count($data['keywords']).'</strong> keywords.';
				$ret .= '<div class="warning">'.$msg.'</div>';
				$msgs[] = $msg;
			}
		} else {
			$msg = 'Missing Meta Keywords';
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
	
	private function page_report($data)
	{
		$ret = '';
		
		$tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');
		$tags = implode('|', $tags);
		$matches = array();
		$counts = array();
		$msgs = array();
		if (preg_match_all('#<(?<tag>'.$tags.')([^a-zA-Z>][^>]+)?>(?<content>[^<]+)</#', $data, $matches)) {
			foreach ($matches['tag'] as $k => $tag) {
				$ret .= '['.$tag.'] '.$matches['content'][$k].'<br />';
				if (!isset($counts[$tag])) {
					$counts[$tag] = 0;
				}
				++$counts[$tag];
			}
			
			if (empty($counts['h1'])) {
				$msg = 'There should be an H1 tag. No H1 tag found.';
				$ret .= '<div class="warning">'.$msg.'</div>';
				$msgs[] = $msg;
			} elseif ($counts['h1'] > 1) {
				$msg = 'There should only be one H1 tag. Currently there are <strong>'.$counts['h1'].'</strong>.';
				$ret .= '<div class="warning">'.$msg.'</div>';
				$msgs[] = $msg;
			}
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
	
	private function images($data) {
		$ret = '';
		$msgs = array();
		$ret = count($data['images']) . ' images found.';
		foreach ($data['images'] as $k => $img) {
			if (empty($data['images_alt'][$k])) {
				$msg = 'Image ' . $img . ' missing alt tag.';
				$ret .= '<div class="warning">'.$msg.'</div>';
				$msgs[] = $msg;
			}
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
	
	/**
	 * Printout a list of images (and alt tags)
	 * 
	 * @param $data the page information, gathered in associative array.
	 * @return the result to be displayed in HTML.
	 */ 
	private function html_image_list($data)
	{
		$msgs = array();
		$images = $data['images'];
		$ret = 'Images: ' . count($data);
		if (!empty($images)) {
			$ret = '
			<table>
			<tr>
				<th>thumb</th>
				<th>path</th>
				<th>alt tag</th>
				<td rowspan="'.count($data).'"></td>
			</tr>';
			foreach ($images as $k => $val) {
				$ret .= '<tr>
				<td><img src="'.$this->url($val).'" onclick="'."
				if ($('#html_image').attr('src') != $(this).attr('src')) {
					$('#html_image').hide();
					$('#html_image').attr('src', $(this).attr('src'));
				}
				$('#html_image').css('top', $(this).offset().top);
				$('#html_image').toggle();
				".'" height="20" /></td>
				<td><a href="'.$this->url.'" target="_blank">'.$val.'</a></td>
				<td>'.$data['images_alt'][$k].'</td>
				</tr>';
				if (empty($data['images_alt'][$k])) {
					$msg = 'Image '. $v . ' doesn\'t have an alt tag.';
					$msgs[] = $msg;
				}
			}
			$ret .= '</table>';
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
	
	private function html_standards($data)
	{
		$matches = array();
		if (preg_match('|<!DOCTYPE [Hh][Tt][Mm][Ll] PUBLIC "-//W3C//DTD (?<standard>X?HTML) (?<ver>[0-9\.]+)(?<mod> [a-zA-Z]+)?//EN"[\n\r\t ]+"(?<dtd>[^"]+)">|', $data, $matches)) {
			$ret = 'Standard: ' . $matches['standard'] .' (version: '.$matches['ver']. (!empty($matches['mod']) ? '; Modification: ' . $matches['mod'] : '').')<br />';
		} else {
			$ret = 'No Doctype found!';
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
		if (substr($path, 0, 7) != 'http://') {
			$url = 'http://'.$this->domain;
			if ($path[0] != '/') {
				$url .= '/';
			}
		}
		
		return $url . $path;
	}
}