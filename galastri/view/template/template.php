<!DOCTYPE html>
<html>
	<head>
		<?php
		$render->template["head"] ? include_once(path($render->template["head"])) : "";
		?>
	</head>

	<body>
        <?php
        $render->template["nav"] ? include_once(path($render->template["nav"])) : "";            
        $render->view ? include_once(path($render->view)) : "";
        $render->template["footer"] ? include_once(path($render->template["footer"])) : "";
        ?>
	</body>
</html>

