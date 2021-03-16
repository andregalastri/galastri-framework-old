<!DOCTYPE html>
<html>
	<head>
		<?php
		$render->template["head"] ? include_once($render->template["head"]) : "";
		?>
	</head>

	<body>
        <?php
        $render->template["nav"] ? include_once($render->template["nav"]) : "";            
        $render->view ? include_once($render->view) : "";
        $render->template["footer"] ? include_once($render->template["footer"]) : "";
        ?>
	</body>
</html>

