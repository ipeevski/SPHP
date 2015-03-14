<?php

$dir = isset($_REQUEST['dir']) ? $_REQUEST['dir'] : './' ;

if ($action == 'open') {
} elseif ($action == 'delete') {
	$file = $_REQUEST['file'];
	if (is_file($file)) {
		unlink($file);
	} elseif (is_dir($file)) {
		rmdir($file);
	}
} elseif ($action == 'upload') {
	$file = $_FILES['file']['tmp_name'];
	move_uploaded_file($file, $dir.$_FILES['file']['name']);
} elseif ($action == 'mkdir') {
	mkdir($dir . $_REQUEST['new_dir']);
} elseif ($action == 'rename') {
	rename($dir.$_REQUEST['file'], $dir.$_REQUEST['new_name']);
}

$files = glob($dir . '*');
if ($dir != './') {
	array_unshift($files, dirname($dir));
}
?>

<script type="text/javascript">
$(document).ready(function() {
	$('.rename').hide();
});
</script>


<form action="?go=filebrowser" method="post">
	<input name="new_dir" />
	<input type="hidden" name="action" value="mkdir" />
	<input type="hidden" name="dir" value="<?php echo $dir ?>" />
	<input type="submit" value="Make Directory" />
</form>

<form action="?go=filebrowser" method="post" enctype="multipart/form-data">
	<input type="file" name="file" />
	<input type="hidden" name="action" value="upload" />
	<input type="hidden" name="dir" value="<?php echo $dir ?>" />
	<input type="submit" value="Upload" />
</form>

<table>
<tr>
	<th>Date</th>
	<th>Size</th>
	<th>Date</th>
	<th>Actions</th>
</tr>

<?php foreach ($files as $file): ?>
<tr>
	<td><?php echo $file ?></td>
	<td><?php echo filesize($file) ?></td>
	<td><?php echo filemtime($file) ?></td>
	<td>
		<?php if (is_file($file)): ?>
		<a href="<?php echo $file ?>">Download</a>
		<?php elseif (is_dir($file)): ?>
		<a href="?go=filebrowser&dir=<?php echo $file ?>/">Open</a>
		<?php endif ?>
		<a href="?go=filebrowser&action=delete&file=<?php echo $file ?>">Delete</a>
		<a href="?go=filebrowser&action=rename&file=<?php echo $file ?>" onclick="$(this).parent().parent().next().show(); return false;">Rename</a>
	</td>
</tr>
<tr class="rename">
	<td colspan="4">
		<form action="?go=filebrowser" method="post">
			<input name="new_name" value="<?php echo $file ?>" />
			<input type="hidden" name="file" value="<?php echo $file ?>" />
			<input type="hidden" name="action" value="rename" />
			<input type="hidden" name="dir" value="<?php echo $dir ?>" />
			<input type="submit" value="Rename" />
		</form>
	</td>
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