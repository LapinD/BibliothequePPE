<html>
	<head>
		<title>Bibliothèque Lipton</title>
		<meta charset='utf-8'/>
		<link rel="stylesheet" href="StyleIndex.css"/>
		<script type="text/javascript" src="Js/jquery.js"></script>
		<script type="text/javascript" src="Js/Animation.js"></script>
		<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
		<?php include "Banniere.php"; ?>
	</head>
	<body>
		<div ID="BGImage">
			<p id="titleFixedBG"> BIBLIOTHEQUE <br> LIPTON </p>
			<img id="fixedBG" src="Images/library.jpg" width="100%">
		</div>
		<div ID="BlogReco">
			<?php
				$Accueil = new Client();
				$Accueil->Blog();
			?>
		</div>
	</body>
	<footer>
	</footer>
</html>
