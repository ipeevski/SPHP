<?php
include_once 'Zend/Mail.php';
include_once 'Zend/Mail/Transport/Smtp.php';

class Mail
{
	public function cron()
	{
		global $config;
		
		// Setup mailing
		if (!empty($config['email']['smtp'])) {
			Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_Smtp($config['email']['smtp']));
		}
		
		// Prepare modules
		$mods = array();
		$dh = opendir($config['dir'].'/plugins');
		while (($mod = readdir($dh))) {
			if ($mod[0] != '.') {
				include 'plugins/'.$mod;
				$modname = substr($mod, 0, -4);
				$module = ucwords(str_replace('_', ' ', $modname));
				$classname = str_replace(' ', '', $module);
				$obj = new $classname();
				$mods[$modname] = $obj;
			}
		}
		closedir($dh);
		
		// Initialize modules
		$mods['alexa']->login($config['alexa']['login'], $config['alexa']['secret']);
		$mods['mysql']->login($config['mysql']['user'], $config['mysql']['passwd']);
		// Look up last month
		$this_month = mktime(0, 0, 0, date('n'), 1, date('Y'));
		$last_month = strtotime('-1 months', $this_month);
		$mods['awstats']->setDate($last_month);
		
		foreach ($config['email']['to'] as $email => $settings) {
			// Only send monthly emails on the first of the month
			if ($settings['type'] == 'monthly' and date('j') != 1) {
				continue;
			// Only send weekly emails on Monday
			} elseif ($settings['type'] == 'weekly' and date('N') != 1) {
				continue;
			// Don't send daily emails during the weekend
			} elseif ($settings['type'] == 'daily' and date('N') >= 6) {
				continue;
			}
			
			$msg = '';
			foreach ($settings['sites'] as $site) {
				$msg .= '
				<table style="border: 1px solid #333; padding: 2px">
				<tr><td>
				<h1 style="background: #333; color: #fff; font-family: Tahoma, Verdana, Ariel, sans-serif; font-weight: bold; padding: 10px;">Statistics for '.$site.'</h1>';
				
				if ($site == 'rejournal.com') {
					$mods['awstats']->setBaseUrl('http://72.18.132.174/awstats/cgi-bin/');
				} else {
					$mods['awstats']->setBaseUrl('http://myrejournal.com/awstats/');
				}
				
				foreach ($settings['mods'] as $mod) {
					$msg .= $mods[$mod]->email($site);
				}
				$msg .= '</td></tr></table><br /><br />';
			}
			
			$mail = new Zend_Mail();
			$mail->setFrom($config['email']['from']);
			$mail->addTo($email);
			$mail->setSubject('Website Statistics Summary');
			$mail->setBodyHtml($msg);
			if ($mail->send()) {
				echo 'Report sent to ' . $email."\n";
			}
		}
	}
}