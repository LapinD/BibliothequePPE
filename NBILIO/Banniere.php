<?php include 'BiblioClass.php' ; ?>
<?php session_start(); ?>
<?php $Tri = new Client(); $Tri->SessionStart(); $Tri->SessionKill() ;?>
<div ID="DivNavigation">
	<ul ID="ListeNavigation">
		<li ID="LiLogo"> <img ID="LogoBiblio" src="Images/Logo.png" ></li>
		<li ID="MainAccueil" class="NavBar"><a href="index.php"> Accueil</a> </li>
		<!-- <li ID="MainReco" class="NavBar"> Recommandations 
			<ul class="SousListe" ID="SousListeReco">
				<li class="LiSReco"> Par genre </li>
				<li class="LiSReco"> Les dernières sorties </li>
				<li class="LiSReco"> Les plus populaires </li>
			</ul>
		</li>-->
		<li ID="MainLivre" class="NavBar"> Livres
			<ul class="SousListe" ID="SousListeLivre">
				<li class="LiSLivre"><a class="LiSLivre" href="PageLivre.php?page=Genre"> Trier par genre </a></li>
				<li class="LiSLivre"><a class="LiSLivre" href="PageLivre.php?page=Auteur"> Trier par auteur </a></li>
				<li class="LiSLivre"><a class="LiSLivre" href="PageLivre.php?page=Pays"> Trier par pays </a></li> 
				<!-- <li class="LiSLivre"><a class="LiSLivre" href="PageLivre.php?page=Langue"> Trier par langue </a></li> -->
			</ul>
		</li>
		<li ID="MainEbook" class="NavBar"> EBooks
			<ul class="SousListe" ID="SousListeEbook">
				<li class="LiSEBook"><a class="LiSEBook" href="PageEBook.php?page=Genre"> Trier par genre </a></li>
				<li class="LiSEBook"><a class="LiSEBook" href="PageEBook.php?page=Auteur"> Trier par auteur </a></li>
				<li class="LiSEBook"><a class="LiSEBook" href="PageEBook.php?page=Pays"> Trier par pays </a></li> 
				<!-- <li class="LiSEBook"> Trier par langue </li> -->
			</ul>
		</li>
		<li ID="SearchLivre" class="NavBar">
			<form method='POST' action='PageLivre.php?page=Search'>
				<input type="text" name="SearchLivre" placeholder="">
				<input ID="SearchLivreButton" type="submit" name="SearchLivreButton" value="Go!">
				<SELECT NAME="SearchBy">
					<OPTION SELECTED> Nom
					<OPTION> Livre
				</SELECT>
			</form>
		</li>
		<li ID="MainPerso" class="NavBar"> <?php $Tri->SessionRun();?>
			<ul class="SousListe" ID="SousListePerso">
				<li class="LiSPerso"> <a class="LiSPerso" href="Inscription.php">Inscription</a> </li>
				<li class="LiSPerso" id="ConnexionOnClick"> Connexion </li>
				<li class="LiSPerso"><a class="LiSPerso" href="Compte.php"> Mon compte </a></li> <!-- Accès aux informations (Date pour rendre les livres, lesquels ont été empruntés) -->
				<form method="POST" action="">
					<li class="LiSPerso"> <input id="DeconnexionButton" type="submit" name="Deco" value="Se deconnecter"></li>
				</form>
			</ul>
		</li>
	</ul>
</div>
<div id="ConnexionPopUp">
	<table id="ConnexionPopUpForm">
			<form method="POST" action="">
				<tr><input class="ConnexionPopUpFormLi" type="text" name="Login" placeholder="Login"/></tr>
				<tr><input class="ConnexionPopUpFormLi" type="password" name="Password" placeholder="Mot de passe"/></tr>
				<tr><input class="ConnexionPopUpFormLi" type="submit" name="ConnexionClient" value="Se connecter">
				<input id="ConnexionPopFormQuit" class="ConnexionPopUpFormLi" type="submit" name="Quit" value="Quitter"></tr>
			</form>
	</table>
</div>