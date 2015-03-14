<?php

if ($action == 'backup') {
	$zip = new Zipper;
	$filename = date('Ymd') .'.zip';

	if ($zip->open($filename, ZipArchive::OVERWRITE) === true) {
		$zip->add('/var/www/html/pet/webbackup/');
		$zip->close();
	} else {
		echo 'failed';
	}
} elseif ($action == 'delete') {
	unlink($_REQUEST['file']);
} elseif ($action == 'revert') {
	$file = $_REQUEST['file'];
	$zip = new ZipArchive;
	$zip->open($file);
	$zip->extractTo('.');
	$zip->close();
} elseif ($action == 'upload') {
	$file = $_FILES['file']['tmp_name'];
	
	$zip = new ZipArchive;
	$zip->open($file);
	$zip->extractTo('.');
	$zip->close();
}

$backups = glob('*.zip');
?>

<a href="?go=filebackup&action=backup">Backup</a>

<form action="?go=filebackup" method="post" enctype="multipart/form-data">
	<input type="file" name="file" />
	<input type="hidden" name="action" value="upload" />
	<input type="submit" value="Upload" />
</form>

<table>
<tr>
	<th>Date</th>
	<th>Size</th>
	<th>Actions</th>
</tr>

<?php foreach ($backups as $backup): ?>
<tr>
	<td><?php echo $backup ?></td>
	<td><?php echo filesize($backup) ?></td>
	<td>
		<a href="<?php echo $backup ?>">Download</a>
		<a href="?go=filebackup&action=delete&file=<?php echo $backup ?>">Delete</a>
		<a href="?go=filebackup&action=revert&file=<?php echo $backup ?>">Revert</a>
</tr>
<?php endforeach ?>
</table>

<?php

class Zipper extends ZipArchive {
	protected $filename;
	
	protected $filesCounter = 0;
	protected $estimatedCounter = 0;
	
	protected $sizeAdded = 0;
	protected $sizeEstimated = 0;
	
	public function open($filename, $flags = self::CREATE) {
		$this->filename = $filename;
		$this->filesCounter = 0;
		return parent::open($filename, $flags);
	}
	
	public function reopen() {
		if (!$this->close() ) {
			return false;
		}
		return $this->open($this->filename, self::CREATE);
	}
	
	public function estimate($path) {
		$this->sizeEstimated = $this->estimatePath($path);
		return $this->sizeEstimated;
	}
	
	public function progress() {
		return ($this->sizeAdded / $this->sizeEstimated);
	}
	
	public function estimatePath($path) {
		if (is_file($path)) {
			$this->estimatedCounter++;
			return filesize($path);
		} elseif (is_dir($path)) {
			$nodes = glob($path . '/*');
			$size = 0;
			foreach ($nodes as $node) {
				$size += $this->estimate($node, $structure.'/'.basename($node));
			}
			
			return $size;
		}
	}
	
	public function add($path, $structure = '') { 
		if (is_file($path)) {
			if ($this->filesCounter > 0 and $this->filesCounter % 100 == 0) {
				$this->reopen();
			}
			$this->addFile($path, $structure);
			$this->sizeAdded += filesize($path);
		} elseif (is_dir($path)) {
			$this->addEmptyDir($structure.'/'.basename($path));
			$nodes = glob($path . '/*');
			foreach ($nodes as $node) {
				$this->add($node, $structure.'/'.basename($node));
			}
		}
	}
} // class Zipper