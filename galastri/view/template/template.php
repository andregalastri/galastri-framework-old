<!DOCTYPE html>
<html>
	<head>
		<?php
		$render->template["head"] ? include_once($render->template["head"]) : "";
		?>
	</head>

	<body>
        <div class="main">
            <?php
            $render->view ? include_once($render->view) : "";
            $render->template["nav"] ? include_once($render->template["nav"]) : "";
            ?>
        </div>
        <?php
            $render->template["footer"] ? include_once($render->template["footer"]) : "";
        ?>
	</body>
    
    <script>load();</script>
</html>

