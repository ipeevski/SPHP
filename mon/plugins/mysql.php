<?php
class Mysql extends Service
{
	private $user;
	private $passwd;
	
	public function __construct()
	{
		$this->name = 'MySQL';
	}
	
	public function login($user, $passwd)
	{
		$this->user = $user;
		$this->passwd = $passwd;
	}
	
	public function parse()
	{
		$data = $this->data;
		if (!empty($data)) {
			$this->block($this->mysql_server($data), 'Server Info');
			$this->block($this->mysql_status($data['status']), 'Server Status');
			$this->block_table($data['config'], 'Server Configuration');
			$this->block_table($data['detailed_status'], 'Server Detailed Status');
			$this->block($this->mysql_dbs($data['db']), 'Databases');
		}
	}
	
	protected function _process($url)
	{
		if (!@mysql_connect($url, 'root', '')) {
			if (!mysql_connect($url, $this->user, $this->passwd)) {
				return array();
			}
		}
		
		$data['server'] = mysql_get_server_info();
		$data['client'] = mysql_get_client_info();
		$data['protocol'] = mysql_get_proto_info();
		$data['host'] = mysql_get_host_info();
		
		$data['status'] = explode('  ', mysql_stat());
		
		$result = mysql_query('SHOW GLOBAL STATUS');
		while (($row = mysql_fetch_assoc($result))) {
			$data['detailed_status'][$row['Variable_name']] = $row['Value'];
		}
		
		$result = mysql_query('SHOW VARIABLES');
		while (($row = mysql_fetch_assoc($result))) {
			$data['config'][$row['Variable_name']] = $row['Value'];
		}
		
		$db_list = mysql_list_dbs();
		while (($row = mysql_fetch_object($db_list))) {
			$db = $row->Database;
			$tables = mysql_query('SHOW TABLES FROM '.$db);
			while (($table = mysql_fetch_row($tables))) {
				$table = $table[0];
				$q = mysql_query('SELECT count(*) FROM '.$db.'.'.$table);
				$r = mysql_fetch_row($q);
				$count = $r[0];
				$data['db'][$db][$table] = $count;
			}
		}
		
		return $data;
	}
	
	private function mysql_server($data)
	{
		$ret = '';
		$ret .= '<label>Server version:</label> '.$data['server'].'<br />';
		$ret .= '<label>Client version:</label> '.$data['client'].'<br />';
		$ret .= '<label>Protocol version:</label> '.$data['protocol'].'<br />';
		$ret .= '<label>Host:</label> '.$data['host'].'<br />';
		
		return $ret;
	}
	
	private function mysql_status($data)
	{
		$ret = '';
		foreach ($data as $line) {
			$ret .= $line . '<br />';
		}
		
		return $ret;
	}
	
	private function mysql_dbs($data)
	{
		$ret = '';
		foreach ($data as $db_name => $db) {
			$ret .= '<h2>'.$db_name.'</h2>';
			$ret .= '<table><th>Table</th><th>Records</th></tr>';
			foreach ($db as $table_name => $count) {
				$ret .= '<tr><td>'.$table_name.'</td><td>'.$count.'</td></tr>';
			}
			$ret .= '</table>';
		}
		
		return $ret;
	}
}
?>