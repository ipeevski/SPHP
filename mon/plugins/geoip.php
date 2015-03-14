<?php
class Geoip extends Service
{
	public function __construct()
	{
		$this->name = 'GeoIP';
	}
	
	public function parse()
	{
		// var_dump($this->data);
		$geody = $this->data['geody'];
		if (!empty($geody)) {
			echo '<h2>Geody</h2>';
			echo $geody['city'], ', ', $geody['state'] . '<br />';
			echo $geody['country'] . '<br />';
			echo 'ISP: ' . $geody['isp'];
		}
	}
	
	protected function _process($domain)
	{
		$ip = gethostbyname($domain);
		
		$content = file_get_contents("http://ws.arin.net/whois?queryinput=$ip");
		
		$pos = strpos($content, "<pre") + 4;
		$content = substr($content,$pos);
		
		$matches = array();
		if (strpos($content, 'Updated:') === false) {
			preg_match_all('| \(<a href="([^"]+)">[^<]+</a>\)|', $content, $matches);
			$url = array_pop($matches[1]);
			$content = file_get_contents('http://ws.arin.net'.$url);
		
			$pos = strpos($content, "<pre") + 4;
			$content = substr($content,$pos);
		}
		
		
		$fields = array(
			'customer' => 'CustName',
			'isp' => 'OrgName',
			'address' => 'Address', // multiples
			'city' => 'City',
			'state' => 'StateProv',
			'postcode' => 'PostalCode',
			'country' => 'Country',
			'dns' => 'NameServer', // multiples
			'updated' => 'Updated',
			'tech name' => 'RTechName',
			'tech phone' => 'RTechPhone',
			'tech email' => 'RTechEmail',
			'abuse name' => 'OrgAbuseName',
			'abuse phone' => 'OrgAbusePhone',
			'abuse email' => 'OrgAbuseEmail',
			'netops name' => 'OrgNOCName',
			'netops phone' => 'OrgNOCPhone',
			'netops email' => 'OrgNOCEmail',
			'orgtech name' => 'OrgTechName',
			'orgtech phone' => 'OrgTechPhone',
			'orgtech email' => 'OrgTechEmail',
		);
		
		foreach ($fields as $name => $field) {
			if (preg_match("/$field:(.+)\n/", $content, $matches)) {
				$data['arin'][$name] = trim($matches[1]);
			}
		}
		
		$page = file_get_contents('http://www.geody.com/geoip.php?ip='.$ip);
		if (preg_match('|Location: <br><b><a[^>]+>([^<]+)</a>, <a[^>]+>([^<]+)</a>, <a[^>]+>([^<]+) <img[^>]+> </a>([^<]+)<|', $page, $matches)) {
			$data['geody']['city'] = $matches[1];
			$data['geody']['state'] = $matches[2];
			$data['geody']['country'] = $matches[3];
			$data['geody']['isp'] = $matches[4];
		}
		
		
		$fields = array(
			'city' => 'CITY',
			'state' => 'STATE',
			'country' => 'COUNTRY',
			'lat' => 'LAT',
			'long' => 'LONG',
			'updated' => 'LAST_UPDATED',
			'nic' => 'NIC',
			'domain' => 'DOMAIN_GUESS',
			'rating' => 'RATING',
		);
		$page = file_get_contents('http://netgeo.caida.org/perl/netgeo.cgi?target='.$ip);
		foreach ($fields as $name => $field) {
			if (preg_match("/$field: (.+)<br>/", $page, $matches)) {
				$match = trim($matches[1]);
				if (!empty($match)) {
					$data['netgeo'][$name] = ucwords($match);
				}
			}
		}
		
		return $data;
	}
}
?>