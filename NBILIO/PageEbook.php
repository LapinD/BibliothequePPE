<html>
	<head>
		<title>Bibliothèque Lipton</title>
		<meta charset='utf-8'/>
		<link rel="stylesheet" href="StyleIndex.css"/>
		<script type="text/javascript" src="Js/jquery.js"></script>
		<script type="text/javascript" src="Js/Animation.js"></script>
		<script type="text/javascript" src="Js/color-thief.js"></script>
		<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
		<?php include "Banniere.php"; ?>
	</head>
	<body>
		<div ID="Tri">
			<?php
				$Tri = new Client();
				$Tri ->	Ebook();
				include "Js/Var.js" ; 
			?>
		</div>
	</body>
	<footer>
	</footer>
</html>
