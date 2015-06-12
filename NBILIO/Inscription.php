<html>
	<head>
		<title>Bibliothèque Lipton</title>
		<meta charset='utf-8'/>
		<link rel="stylesheet" href="StyleIndex.css"/>
		<script type="text/javascript" src="Js/jquery.js"></script>
		<script type="text/javascript" src="Js/Animation.js"></script>
		<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'></script>
		<?php include "Banniere.php"; ?>
	</head>
	<body>
		<div id='Inscription'>
			<table class="FormulaireInscription">
				<form method="post" action="Inscription.php">
					<tr class="FormNom">
						<td> <input type="text" name="FormNom" placeholder="Nom" required/></td>
					</tr>
					<tr class="FormPrenom">
						<td> <input type="text" name="FormPrenom" placeholder="Prenom" required/></td>
					</tr>
					<tr class="FormDN">
						<td> <input type="date" name="FormDN" placeholder="Date de naissance" required/></td>
					</tr>
					<tr class="FormVille">
						<td> <input type="text" name="FormVille" placeholder="Ville" required/></td>
					</tr>
					<tr class="FormRue">
						<td> <input type="text" name="FormRue" placeholder="Rue" required/></td>
					</tr>
					<tr class="FormCP">
						<td> <input type="text" name="FormCP" placeholder="Code Postal" required/></td>
					</tr>
					<tr class="FormMail">
						<td> <input type="mail" name="FormMail" placeholder="Mail" required/></td>
					</tr>
					<tr class="FormTel">
						<td> <input type="text" name="FormTel" placeholder="Numero de telephone" required/></td>
					</tr>
					<tr class="FormLogin">
						<td> <input type="text" name="FormLogin" placeholder="Identifiant" required/></td>
					</tr>
					<tr class="FormMDP">
						<td> <input type="password" name="FormMDP" placeholder="Mot de passe" required/></td>
					</tr>
					<tr class="FormValider">
						<td> <input type="submit" name="FormValider" value="Enregistrer"/></td>
					</tr>
				</form>
			</table>
		</div>
		<?php
					$InscriptionClient = new Client();
					$InscriptionClient -> InsertInscription();
		?>
	</body>








