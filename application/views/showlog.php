<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<div class='marklog'>
	<?php if ($logname != '') echo "<h1>$logname for $username</h1>"; ?>
<p>
<?php echo $log; ?>
</p>
</div>
<?php include("footer.php"); ?>
