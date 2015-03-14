<?php
// Subscribe at http://aws.amazon.com/awis/ to get keyid and secret access key

class Alexa extends Service
{
	private $service_url = 'http://awis.amazonaws.com?';
	private $access_key_id;
	private $secret_access_key;
	public function __construct()
	{
		$this->name = 'Alexa';
	}
	
	public function login($access_key_id, $secret_access_key)
	{
		$this->access_key_id = $access_key_id;
		$this->secret_access_key = $secret_access_key;
	}
	
	public function parse()
	{
		$data = $this->data;
		$this->block($this->alexa_stats($data), 'Alexa Statistics');
		$this->block($this->alexa_graph($data), 'Alexa Graph');
		if (!empty($data['site'])) {
			$this->block($this->alexa_info($data['site']), 'Alexa Site Info');
		}
		if (!empty($data['history'])) {
			$this->block($this->alexa_history($data['history']), 'Alexa History');
		}
		if (!empty($data['contact'])) {
			$this->block($this->alexa_contact($data['contact']), 'Alexa Contact Info');
		}
		if (!empty($data['rank by country'])) {
			$this->block($this->alexa_rank_country($data['rank by country']), 'Alexa Ranking by Country');
		}
		if (!empty($data['related'])) {
			$this->block($this->alexa_domains($data['related']['domains']), 'Alexa Alternative Domains');
			$this->block($this->alexa_related($data['related']['links']), 'Alexa Related Sites');
		}
		if (!empty($data['links'])) {
			$this->block($this->alexa_inbound($data['links']), 'Alexa Incoming Links');
		}
	}
	
	protected function _process($url)
	{
		$action = 'UrlInfo';
		
		$site_url = $url;
		
		$awis_url = $this->generate_url($site_url, $action);

		$data['graph'] = ' http://traffic.alexa.com/graph?c=1&u='.$url.'&r=6m&y=r&z=3&h=150&w=300&b=FFFFFF';
		
		// Make request
		$result = $this->curl_http($awis_url);
		
		// Parse XML and display results
		$page = str_replace('aws:', '', $result);
		$xml = new SimpleXMLElement($page);
		if (isset($xml->Errors)) {
			$data['error'] = $xml->Errors[0]->Error->Message;
			return $data;
		}
		
		$alexa = $xml->Response->UrlInfoResult->Alexa;
		
		$xml_branch = $alexa->ContactInfo;
		$data['contact']['phone'] = array();
		foreach ($xml_branch->PhoneNumbers->PhoneNumber as $phone) {
			$data['contact']['phone'][] = (string) $phone;
		}
		$data['contact']['email'] = (string) $xml_branch->Email;
		$data['contact']['address'] =
			$xml_branch->PhysicalAddress->Streets->Street[0].', '.
			$xml_branch->PhysicalAddress->City.', '.
			$xml_branch->PhysicalAddress->State.' '.
			$xml_branch->PhysicalAddress->PostalCode.', '.
			$xml_branch->PhysicalAddress->Country;
		$data['contact']['stock'] = (string) $xml_branch->CompanyStockTicker;
		
		$xml_branch = $alexa->ContentData;
		$data['site']['title'] = (string) $xml_branch->SiteData->Title;
		$data['site']['description'] = (string) $xml_branch->SiteData->Description;
		$data['site']['since'] = (string) $xml_branch->SiteData->OnlineSince;
		$data['site']['keywords'] = array();
		foreach ($xml_branch->Keywords->Keyword as $keyword) {
			$data['site']['keywords'][] = (string) $keyword;
		}
		
		$data['speed']['avg'] = (string) $xml_branch->Speed->MedianLoadTime;
		$data['speed']['compare'] = (string) $xml_branch->Speed->Percentile;
		
		$data['site']['adult'] = (string) $xml_branch->AdultContent;
		// $xml_branch->Language->Locale
		
		$data['stats']['inLinks'] = (string) $xml_branch->LinksInCount;
		
		$data['related']['domains'] = array();
		foreach ($xml_branch->OwnedDomains->OwnedDomain as $domain) {
			$data['related']['domains'][(string) $domain->Domain] = (string) $domain->Title;
		}
		
		$xml_branch = $alexa->Related;
		$data['related']['links'] = array();
		foreach ($xml_branch->RelatedLinks->RelatedLink as $link) {
			$data['related']['links'][(string) $link->NavigableUrl] = (string) $link->Title;
		}
		$data['relaged']['categories'] = array();
		if ($xml_branch->Categories->CategoryData) {
			foreach ($xml_branch->Categories->CategoryData as $category) {
				$data['relaged']['categories'][(string) $category->AbsolutePath] = (string) $category->Title;
			}
		}
		
		$xml_branch = $alexa->TrafficData;
		$data['rank'] = (string) $xml_branch->Rank;
		
		$xml_branch = $alexa->TrafficData->RankByCountry->Country;
		foreach ($xml_branch as $country) {
			$data['rank by country'][(string) $country->attributes()->Code] = array('users' => (string) $country->Users, 'rank' => (string) $country->Rank);
		}
		
		$xml_branch = $alexa->ContributingSubdomains->ContributingSubdomain;
		if ($xml_branch) {
			foreach ($xml_branch as $subdomain) {
				$data['subdomains'][(string) $subdomain->DataUrl] = (string) $subdomain->Percentage;
			}
		}
		
		$xml_branch = $alexa->TrafficData->UsageStatistics->UsageStatistic;
		foreach ($xml_branch as $stat) {
			if ($stat->TimeRange->Months) {
				$dur = $stat->TimeRange->Months . ' months';
			} elseif ($stat->TimeRange->Days) {
				$dur = $stat->TimeRange->Days .' days';
			}
			$data['history'][$dur]['rank'] = (string) $stat->Rank->Value;
			$data['history'][$dur]['rank delta'] = (string) $stat->Rank->Delta;
			$data['history'][$dur]['reach'] = (string) $stat->Reach->Rank->Value;
			$data['history'][$dur]['reach delta'] = (string) $stat->Reach->Rank->Delta;
			$data['history'][$dur]['permil'] = (string) $stat->Reach->PerMillion->Value;
			$data['history'][$dur]['permil delta'] = (string) $stat->Reach->PerMillion->Delta;
			$data['history'][$dur]['page views'] = (string) $stat->PageViews->Rank->Value;
			$data['history'][$dur]['page views delta'] = (string) $stat->PageViews->Rank->Delta;
		}
		
		$data['links'] = $this->alexa_links($url);
		
		return $data;
	}
	
	
	private function alexa_links($url)
	{
		$action = 'SitesLinkingIn';
		
		$awis_url = $this->generate_url($url, $action);
		$result = $this->curl_http($awis_url);
		
		// Parse XML and display results
		$page = str_replace('aws:', '', $result);
		$xml = new SimpleXMLElement($page);
		
		$alexa = $xml->Response->SitesLinkingInResult->Alexa;
		
		$xml_branch = $alexa->SitesLinkingIn->Site;
		if ($xml_branch) {
			foreach ($xml_branch as $site) {
				$data[(string) $site->Url] = (string) $site->Title;
			}
		}
		
		return $data;
	}
	
	private function alexa_stats($data) {
		$ret = '<label>Rank:</label> ' . $data['rank'].'<br />';
		if (!empty($data['speed']['avg'])) {
			$ret .= '<label>Average Download speed:</label> ' . $data['speed']['avg'].'ms<br />';
			$ret .= '<label>Faster than:</label> ' . $data['speed']['compare'].'% of sites<br />';
		}
		
		$ret .= '<label>Incoming Links:</label> '.$data['stats']['inLinks'].'<br />';
		
		return $ret;
	}
	
	private function alexa_graph($data) {
		$ret = '<img src="'.$data['graph'].'" />';
		
		return $ret;
	}
	
	private function alexa_info($data) {
		$ret = '<label>Title:</label> '.$data['title'].'<br />';
		if (!empty($data['description'])) {
			$ret .= '<label>Description:</label> ' . $data['description'].'<br />';
		}
		if (!empty($data['since'])) {
			$ret .= '<label>Site running since:</label> ' . $data['since'].'<br />';
		}
		$ret .= '<label>Keywords:</label> ' . implode(', ', $data['keywords']).'<br />';
		$ret .= '<label>Adult site?</label> '.$data['adult'].'<br />';
	
		return $ret;
	}
	
	private function alexa_history($data) {
		$ret = '<table>';
		$ret .= '<tr><th></th><th>rank</th><th>reach (% of total web users)</th><th>per million</th><th>page views (avg per user</th></tr>';
		foreach ($data as $period => $row) {
			$ret .= '<tr><th nowrap="nowrap">'.$period.'</th>
							<td nowrap="nowrap">'.$row['rank'] . ' ('.$row['rank delta'].')</td>
							<td nowrap="nowrap">'.$row['reach'] . ' ('.$row['reach delta'].')</td>
							<td nowrap="nowrap">'.$row['permil'] . ' ('.$row['permil delta'].')</td>
							<td nowrap="nowrap">'.$row['page views'] . ' ('.$row['page views delta'].')</td>
							</tr>';
		}
		$ret .= '</table>';
		
		return $ret;
	}
	
	private function alexa_rank_country($data) {
		$ret = '';
		if (!empty($data)) {
			foreach ($data as $country => $row) {
				$ret .= $country.': '.$row['rank'] . (!empty($row['users']) ? ' ('.$row['users'].')' : '') . '<br />';
			}
		}
		
		return $ret;
	}
	
	private function alexa_domains($data) {
		$ret = '';
		foreach ($data as $link => $title) {
			$ret .= '<a href="'.$link.'">'.$title.'</a><br />';
		}
		
		return $ret;
	}
	
	private function alexa_related($data) {
		$ret = '';
		foreach ($data as $link => $title) {
			$ret .= '<a href="'.$link.'">'.$title.'</a><br />';
		}
		
		return $ret;
	}
	
	private function alexa_contact($data) {
		$ret = '';
		foreach ($data['phone'] as $phone) {
			if (!empty($phone)) {
				$ret .= $phone . '<br />';
			}
		}
		if (!empty($ret)) {
			$ret = '<label>Phone:</label> ' . $ret;
		}
		
		if (!empty($data['email'])) {
			$ret .= '<label>Email:</label> ' . $data['email'] . '<br />';
		}
		if (!empty($data['address'])) {
			$ret .= '<label>Address:</label> ' . $data['address'] . '<br />';
		}
		if (!empty($data['stock'])) {
			$ret .= '<label>Company Stock Code:</label> ' . $data['stock'] . '<br />';
		}
		
		return $ret;
	}
	
	private function alexa_inbound($data) {
		$ret = '';
		if (!empty($data)) {
			foreach ($data as $link => $title) {
				$ret .= '<a href="'.$link.'">'.$title.'</a><br />';
			}
		}
		
		return $ret;
	}
	
	// Returns the AWS url to get AWIS information for the given site
	private function generate_url($site_url, $action)
	{
		$timestamp = gmdate("Y-m-d\\TH:i:s.\\0\\0\\0\\Z", time());
		$site_enc = urlencode($site_url);
		$timestamp_enc = urlencode($timestamp);
		$signature_enc = urlencode($this->calculate_RFC2104HMAC($action . $timestamp, $this->secret_access_key));
	
		if ($action == 'UrlInfo') {
			$groups = 'RelatedLinks,Categories,Rank,RankByCountry,RankByCity,UsageStats,ContactInfo,AdultContent,Speed,Language,Keywords,OwnedDomains,LinksInCount,SiteData';
		} else {
			$groups = $action;
		}
	
		return $this->service_url
			. "AWSAccessKeyId=".$this->access_key_id
			. "&Action=".$action
			. "&ResponseGroup=".$groups
			. "&Count=20"
			. "&Timestamp=$timestamp_enc"
			. "&Signature=$signature_enc"
			. "&Url=$site_enc";
	}
	
	
	// Calculate signature using HMAC: http://www.faqs.org/rfcs/rfc2104.html
	private function calculate_RFC2104HMAC($data, $key)
	{
		return base64_encode (
			pack("H*", sha1((str_pad($key, 64, chr(0x00))
			^(str_repeat(chr(0x5c), 64))) .
			pack("H*", sha1((str_pad($key, 64, chr(0x00))
			^(str_repeat(chr(0x36), 64))) . $data))))
		);
	}
}
?>