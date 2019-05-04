<?php
	header('Content-type: application/octet-stream');
	header('Content-Disposition: attachment; filename=output.obj');
	header('Content-Transfer-Encoding: binary');
	readfile('out/output.obj');
?>