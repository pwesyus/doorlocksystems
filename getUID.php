<?php
	$UIDresult=$_POST["UIDresult"];
	$Write="<?php $" . "UIDresult='" . $UIDresult . "'; " . "echo $" . "UIDresult;" . " ?>";
	file_put_contents('UIDContainer.php',$Write);

	$Timestamp=$_POST["Timestamp"];
	$Write="<?php $" . "Timestamp='" . $Timestamp . "'; " . "echo $" . "Timestamp;" . " ?>";
	file_put_contents('timestamp.php',$Write);

?>