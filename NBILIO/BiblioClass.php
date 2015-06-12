<?php

	Class Client
	{
		private $Nom, $Prenom, $DN, $Ville, $Rue, $CP, $Mail, $Tel, $Login, $MDP ;
		private $ConnectionString, $ConnectionMysqli;
		private $PageTri, $SearchLivre ;
		private $CompteurSlideShow ;
		private $Session, $SessionLogin, $SessionPassword, $SessionID ;

		public function __construct()
		{
			$this->Prenom="";
			$this->Nom="";
			$this->DN="";
			$this->Ville="";
			$this->Rue="";
			$this->CP="";
			$this->Mail="";
			$this->Tel="";
			$this->Login="";
			$this->MDP="";
			$this->SessionLogin="";
			$this->SessionPassword="";
			$this->Session="";
			$this->SessionID="";
			$this->ConnectionMysqli=mysqli_connect("localhost","root","","BDDBibliotheque");
			$this->ConnectionMysqli->query("SET NAMES utf8");
			$this->ConnectionString="";
		}

		public function Connection()
		{
			$DNS= "mysql:host=localhost;dbname=BDDBibliotheque";
			try
			{
				$User="root";
				$MDP = "";
				$this->ConnectionString = new PDO($DNS, $User, $MDP) ;
				$this->ConnectionString-> exec('SET NAMES utf8');
			}
			catch(Exception $e)
			{
				echo "Erreur de connection au serveur : " .$DNS; 
			}
		
		}

		public function SessionStart()
		{
			$error=''; // Variable To Store Error Message
			if (isset($_POST['ConnexionClient'])) 
			{
				if (empty($_POST['Login']) || empty($_POST['Password'])) 
				{
					echo "<script> alert('Login ou mot de passe invalide')</script>" ;
				}
				else
				{
					// Define $username and $password
					$username=$_POST['Login'];
					$password=$_POST['Password'];
					// To protect MySQL injection for Security purpose
					$username = stripslashes($username);
					$password = stripslashes($password);
					$username = mysqli_real_escape_string($this->ConnectionMysqli, $username);
					$password = mysqli_real_escape_string($this->ConnectionMysqli, $password);
					// Establishing Connection with Server by passing server_name, user_id and password as a parameter
					// Selecting Database
					// SQL query to fetch information of registerd users and finds user match.
					$query = mysqli_query($this->ConnectionMysqli, "select * from Adherent where MDP='".$password."' AND Login='".$username."'");
					if($query->num_rows==1)
					{
						$this->Connection();
						$_SESSION['login_user']=$username;
						$this->SessionLogin=$username;
						$IDQuery=$this->ConnectionString->query("select IdAdherent from Adherent where Login='".$username."' ;") ;
						while($Afficher=$IDQuery->fetch())
						{
							$this->SessionID=$Afficher['IdAdherent'] ;
						}
						$_SESSION['id']=$this->SessionID ;
						echo "<script> alert('Connection etablie')</script>" ; // Initializing Session
					} 
					else 
					{
						$error = "Username or Password is invalid";
					}
				}
			}
		}

		public function SessionRun()
		{
			if(isset($_SESSION['login_user']))
			{
				// echo "<script> alert('Hola !') </script>" ;
				echo $_SESSION['login_user'] ;
			}
			else
			{
				echo 'Invité' ;
			}
		}

		public function SessionKill()
		{
			if(isset($_POST['Deco']))
			{
				session_destroy();
			}
		}

		public function RenseignerInscription($Nom, $Prenom, $DN, $Ville, $Rue, $CP, $Mail, $Tel, $Login, $MDP)
		{
			$this->Nom=$Nom;
			$this->Prenom=$Prenom;
			$this->DN=$DN;
			$this->Ville=$Ville;
			$this->Rue=$Rue;
			$this->CP=$CP;
			$this->Mail=$Mail;
			$this->Tel=$Tel;
			$this->Login=$Login;
			$this->MDP=$MDP;
		}

		public function PageTri() //Fonction qui sert à trier les livres.
		{
			if(isset($_GET['page']))
			{
				$this->PageTri=$_GET['page'];
			}
			else
			{
				$this->PageTri=0;
			}

			switch($this->PageTri) 
			{
				case 'Genre' : 
					$this->Connection();
					$TriGenre=$this->ConnectionString->query("Select * from AuteurLivreGenre GROUP BY IdGenre ; ") ;
					$i=0;
					$j=0;
					while($Afficher=$TriGenre->fetch())
					{
						echo "<div class='MasterListGenre' id='MasterListeGenre".$i."' style='''background-image:url(".$Afficher['IdGenre'].".jpg)'>" .strtoupper($Afficher['IdGenre']);
						$TriLivre=$this->ConnectionString->query("Select * from AuteurLivreGenre where
								IdGenre='".$Afficher['IdGenre']."'; ") ;
						echo "</div><div class='SMasterListGenre' id='SMasterListGenre".$i."'>" ;
						while($AfficherSousListe=$TriLivre->fetch())
						{
							echo "<div id='DivGenreListLivre".$i."&".$j."' class='CDivGenreListLivre'><p class='GenreListLivre'><a href='PageLivre.php?page=Exemp?".$AfficherSousListe['IdLivre']."'>" .mb_strtoupper($AfficherSousListe['TitreLivre'], 'utf8'). 
								"<br/>" .strtoupper($AfficherSousListe['PrenomAuteur']). " " .strtoupper($AfficherSousListe['NomAuteur']). "</a></p><p class='ImgGenreListLivre'><img src='".$AfficherSousListe['CouvertureLivre']."' class='ImgGenreListLivrepng' height='70%'/></p></div>" ;


								echo
								"
								<script type='text/javascript'>
								var img".$i."a".$j."= document.createElement('img') ;
								img".$i."a".$j.".onload = function(){
									var colorThief = new ColorThief();
									var color = colorThief.getColor(img".$i."a".$j.");
									document.getElementById('DivGenreListLivre".$i."&".$j."').style.background = 'rgb(' + color + ')';
								};
									img".$i."a".$j.".src= '".$AfficherSousListe['CouvertureLivre']."' ;
								</script>" ;

								$j++;
						}
						echo "</div>";
						$i++; 
					}break;
				case 'Auteur' : 
					$this->Connection();
					$TriAuteur=$this->ConnectionString->query("Select A.* from Auteurs A, AuteurLivreGenre L where A.IdAuteur=L.IdAuteur group by IdAuteur;");
					$i=0;
					while($Afficher=$TriAuteur->fetch())
					{
						echo "<div class='MasterListAuteur' id='MasterListAuteur".$i."'><p><a href='PageLivre.php?page=Auteur?".$Afficher['PrenomAuteur']. "
									?" .$Afficher['NomAuteur']. "?".$Afficher['IdAuteur']."'>".strtoupper($Afficher['PrenomAuteur']). "
									 " .strtoupper($Afficher['NomAuteur']). "</a><img class='MasterListAuteurImg' src='".$Afficher['AuteurPhoto']."' height='100%'/></p>" ;
						echo "</div>								
								<script type='text/javascript'>
								var img".$i."= document.createElement('img') ;
								img".$i.".onload = function(){
									var colorThief = new ColorThief();
									var color = colorThief.getColor(img".$i.");
									document.getElementById('MasterListAuteur".$i."').style.background = 'rgb(' + color + ')';
								};
									img".$i.".src= '".$Afficher['AuteurPhoto']."' ;
								</script>" ;
								$i++;
					}break;
				case 'Pays' :
					$this->Connection();
					$TriPays=$this->ConnectionString->query("Select * from AuteurLivreGenre group by IdPays ;") ;
					$i=0;
					$j=0;
					while($Afficher=$TriPays->fetch())
					{
						echo "<div class='MasterListGenre' id='MasterListeGenre".$i."'>" .strtoupper($Afficher['IdPays']);
						$TriAuteur=$this->ConnectionString->query("Select * from Auteurs where
								IdPays='".$Afficher['IdPays']."'; ") ;
						echo "</div><div class='SMasterListGenre' id='SMasterListGenre".$i."'>" ;
						while($AfficherSousListe=$TriAuteur->fetch())
						{
							echo "<div id='DivGenreListLivre".$i."&".$j."' class='CDivGenreListLivre'><p class='GenreListLivre'><a href='PageLivre.php?page=Auteur?".$AfficherSousListe['PrenomAuteur']."?".$AfficherSousListe['NomAuteur']."?".$AfficherSousListe['IdAuteur']."'>" .mb_strtoupper($AfficherSousListe['PrenomAuteur'], 'utf8'). 
								"<br/>" .strtoupper($AfficherSousListe['NomAuteur']). "</a></p><p class='ImgGenreListLivre'><img src='".$AfficherSousListe['AuteurPhoto']."' class='ImgGenreListLivrepng' height='70%'/></p></div>" ;


								echo
								"
								<script type='text/javascript'>
								var img".$i."a".$j."= document.createElement('img') ;
								img".$i."a".$j.".onload = function(){
									var colorThief = new ColorThief();
									var color = colorThief.getColor(img".$i."a".$j.");
									document.getElementById('DivGenreListLivre".$i."&".$j."').style.background = 'rgb(' + color + ')';
								};
									img".$i."a".$j.".src= '".$AfficherSousListe['AuteurPhoto']."' ;
								</script>" ;

								$j++;
						}
						echo "</div>";
						$i++;
					}break;
				case $this->PageTri :
					$this->Connection();
					$Seperator=explode("?", $this->PageTri) ;				
					if($Seperator[0]=='Exemp')
					{
						$LivreExemplaire=$this->ConnectionString->query("Select * from LivreExemplaire where IdLivre='".$Seperator[1]."' 
							Group by TitreLivre ; )") ;
						$ExempQuery=mysqli_query($this->ConnectionMysqli, "Select * from ExemplaireDispo where IdLivre='".$Seperator[1]."' ;") ;
						
						$NbDispo='';
						$Reserver='';
						$LivExemp='';

						while($AfficherExemp=$ExempQuery->fetch_assoc())
						{
							$NbDispo=$AfficherExemp['NbDispo'];
							$Reserver=$AfficherExemp['Reserver'];
						}

						if($NbDispo=='')
						{
							$NbDispo='Aucun' ;
							$Reserver='0' ;
						}						
						
						while($Afficher=$LivreExemplaire->fetch())
						{

							echo "<div class='UniqueLivre'> 
									<div class='UniqueLivrePres'>
										<img class='UniqueLivreImg' src='".$Afficher['CouvertureLivre']."' height='50%' width='auto'>
									</div>
									<div class='UniqueLivreDetails'>	
										<h1 class='UniqueLivreTitle'>".$Afficher['TitreLivre']. "</h1>
										<p class='UniqueLivreDesc'> Actuellement ".$NbDispo." exemplaires disponible(s)
											<form method=POST class='UniqueLivreButton'>
												<input type='submit' name='ReserverExemp' value='Réserver'>
											</form>
										</p>
										<p class='UniqueLivreDesc'>" .$Afficher['ResumeLivre']."</p><br>
									</div>
								</div>" ;

							if(isset($_POST['ReserverExemp']) && $Reserver!='0' && $_SESSION!=null)
							{
								$mysqli = new mysqli("localhost","root","","BDDBibliotheque");
								$insert_row = $mysqli->query("INSERT INTO RETOUREMPRUNT 
									(IdLivre, IdExemplaire, IdAdherent, IdEmprunt, DateEmprunt, DateRetourPrevu, DateRetour) 
										VALUES('".$Seperator[1]."', '".$Reserver."', '".$_SESSION['id']."', 
											'', null, null, null) ;");
								if($insert_row)
								{
								    print 'Exemplaire N°'.$Reserver.' réservé.';
								    header("Refresh:0");
								}
								else
								{
								    echo "Limite d'emprunts simultanés déjà atteinte !";
								}
							}
						}
					}
					if($Seperator[0]=='Auteur')
					{
						echo "<script type='text/javascript' src='Js/AnimationAuteur.js'></script>";
						$Seperator=explode("?", $this->PageTri) ;
						$AuteurSlideShow=$this->ConnectionString->query("Select * from LivreExemplaire where IdAuteur='".$Seperator[3]."' group by TitreLivre order by TitreLivre; ") ;
						$AuteurResumeLivre=$this->ConnectionString->query("Select * from LivreExemplaire where IdAuteur='".$Seperator[3]."' group by TitreLivre order by TitreLivre; ") ;
						$AuteurPresentation=$this->ConnectionString->query("Select * from Auteurs where IdAuteur='".$Seperator[3]."' ;") ;
						echo "	<img src='Images/WhiteArrowL.png' class='Left' height='8%'' width='auto'>
								<img src='Images/WhiteArrowR.png' class='Right' height='8%' width='auto'>
								<div class='AuteurSlideShow'>" ;
						$i = 0;
						while ($Afficher=$AuteurSlideShow->fetch()) 
						{
							echo "
							<img class='ImagePresentation' src='".$Afficher['CouvertureLivre']."' height='80%' width=auto ID='SlideShow".$i."'> ";
							$i++;
						}
						$this->CompteurSlideShow=$i ;
						echo "	</div>
								<div id='ToggleResume'><input id='ToggleResumeButton' type='submit' value='Résumé'></div>
								<div id='AuteurResume'>" ;

						$r=0;
						while($Afficher=$AuteurResumeLivre->fetch())
						{
							echo "	
									<p class='ClassResumeLivre' id='ResumeLivre".$r."'>".$Afficher['TitreLivre']."<br><br>".$Afficher['ResumeLivre']. "<br><br><a class='DLUniqueLivreAut' href='PageLivre.php?page=Exemp?".$Afficher['IdLivre']."'> Réserver le livre. </a></p>";

							$r++;
						}

						echo "	</div>
								<div class='AuteurPresentation'>" ;
						while($Afficher=$AuteurPresentation->fetch())
						{
							echo "	<h1 id='AuteurPresentationTitle'>".$Afficher['PrenomAuteur']. " " .$Afficher['NomAuteur']. "</h1> <img id='AuteurPresentationPortrait' src='".$Afficher['AuteurPhoto']."'>
									<p id='AuteurPresentationBio'>" .$Afficher['Biographie']. "</p>
									</div>";
						}

					}break;

			}

			if(isset($_POST['SearchLivreButton']))
			{
				if($_POST['SearchBy']=='Nom' && $_POST['SearchLivre']!='')
				{
					$this->SearchLivre=$_POST['SearchLivre'] ; 
					$StringSearch=explode(" ", $this->SearchLivre) ;
					$this->Connection();
					$SearchLivreQuery=$this->ConnectionString->query("Select * from Auteurs where 
						PrenomAuteur LIKE '%".$StringSearch[0]."%' 
						OR NomAuteur LIKE '%".$StringSearch[0]."%' GROUP BY IdAuteur ORDER BY PrenomAuteur ");
					$i=0;
					while($Afficher=$SearchLivreQuery->fetch())
					{
						echo "<div class='MasterListAuteur' id='MasterListAuteur".$i."'><p><a href='PageLivre.php?page=Auteur?".$Afficher['PrenomAuteur']. "
									?" .$Afficher['NomAuteur']. "?".$Afficher['IdAuteur']."'>".strtoupper($Afficher['PrenomAuteur']). "
									 " .strtoupper($Afficher['NomAuteur']). "</a><img class='MasterListAuteurImg' src='".$Afficher['AuteurPhoto']."' height='100%'/></p>" ;
						echo "</div>								
								<script type='text/javascript'>
								var img".$i."= document.createElement('img') ;
								img".$i.".onload = function(){
									var colorThief = new ColorThief();
									var color = colorThief.getColor(img".$i.");
									document.getElementById('MasterListAuteur".$i."').style.background = 'rgb(' + color + ')';
								};
									img".$i.".src= '".$Afficher['AuteurPhoto']."' ;
								</script>" ;
								$i++;
					}
				}
				elseif($_POST['SearchBy']=='Livre' && $_POST['SearchLivre']!='')
				{
					$this->Connection();
					$this->SearchLivre=$_POST['SearchLivre'] ;
					$StringSearch=explode(" ", $this->SearchLivre) ;
					$SearchLivreQuery=$this->ConnectionString->query("Select * from AuteurLivreGenre where
						TitreLivre Like '%".$StringSearch[0]."%' order by TitreLivre asc;");
					echo "<br><br><br>Resultats : " ;
					$j=0;
					$i=0;
					while($AfficherSousListe=$SearchLivreQuery->fetch())
					{
						echo "<div id='DivGenreListLivre".$i."&".$j."' class='CDivGenreListLivre'><p class='GenreListLivre'><a href='PageLivre.php?page=Exemp?".$AfficherSousListe['IdLivre']."'>" .mb_strtoupper($AfficherSousListe['TitreLivre'], 'utf8'). 
							"<br/>" .strtoupper($AfficherSousListe['PrenomAuteur']). " " .strtoupper($AfficherSousListe['NomAuteur']). "</a></p><p class='ImgGenreListLivre'><img src='".$AfficherSousListe['CouvertureLivre']."' class='ImgGenreListLivrepng' height='70%'/></p></div>" ;

						echo
							"
							<script type='text/javascript'>
							var img".$i."a".$j."= document.createElement('img') ;
							img".$i."a".$j.".onload = function(){
								var colorThief = new ColorThief();
								var color = colorThief.getColor(img".$i."a".$j.");
								document.getElementById('DivGenreListLivre".$i."&".$j."').style.background = 'rgb(' + color + ')';
							};
								img".$i."a".$j.".src= '".$AfficherSousListe['CouvertureLivre']."' ;
							</script>" ;
						$j++;
					}
				}
			}
			else
			{
				// Ajouter Message Erreur.
			}
		}

		public function InsertInscription()
		{
			if (isset($_POST['FormValider']))
			{
				$this->Nom=$_POST['FormNom'];
				$this->Prenom=$_POST['FormPrenom'];
				$this->DN=$_POST['FormDN'];
				$this->Ville=$_POST['FormVille'];
				$this->Rue=$_POST['FormRue'];
				$this->CP=$_POST['FormCP'];
				$this->Mail=$_POST['FormMail'];
				$this->Tel=$_POST['FormTel'];
				$this->Login=$_POST['FormLogin'];
				$this->MDP=$_POST['FormMDP'];
				
				$this->Connection();

				$DonneesInsert=array(
					"FormNom"=>$this->Nom,
					"FormPrenom"=>$this->Prenom,
					"FormDN"=>$this->DN,
					"FormVille"=>$this->Ville,
					"FormRue"=>$this->Rue,
					"FormCP"=>$this->CP,
					"FormMail"=>$this->Mail,
					"FormTel"=>$this->Tel,
					"FormLogin"=>$this->Login,
					"FormMDP"=>$this->MDP);
					
				/*

				if(!preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $this->Mail))
					 {
					  echo "<span style = 'color:red' class='ErrorMailValide'><h2><strong>L'adresse email que vous venez de taper est invalide</strong></h2></span>";return false;
					 }
					
					
					$sql2="select count(IdAdherent) from Adherent where Login='".$this->Login."'";
					$rep2=$this->ConnectionString->query($sql2);
					$NbAdh=$rep2->fetch();
					if ($NbAdh[0]!=0){
						echo "<span style = 'color:red' class='ErrorPseudo'><h2><strong>Le pseudo que vous avez choisi existe déjà.</strong></h2></span>";return false;}
					
					
					$sql3="select count(IdAdherent) from Adherent where Mail='".$this->Mail."'";
					$rep3=$this->ConnectionString->query($sql3);
					$NbMailcommuns=$rep3->fetch();
					if ($NbMailcommuns[0]!=0){
						echo "<span style = 'color:red' class='ErrorChoixMail'><h2><strong>L'adresse email que vous avez choisi est déjà utilisée.</strong></h2></span>";return false;}
					
					*/
				$RequeteInsert = $this->ConnectionString->prepare("INSERT INTO Adherent (Nom, Prenom, DateNaissance, Ville, Rue, CP, Mail, Tel, Login, MDP, Litigieux, DateLitige, EstAdmin) VALUES (:FormNom, :FormPrenom, :FormDN, 
				:FormVille, :FormRue, :FormCP, :FormMail, :FormTel, :FormLogin, :FormMDP, 'non', null, 'non')");
					
				try
				{
					$ResultatInsert=$RequeteInsert->execute($DonneesInsert);
					if($ResultatInsert)
					echo "Vous avez bien été enregistré" ;
				}
				catch(Exception $e)
				{
					echo "Echec de l'enregistrement" ;
				}
			}
		}

		public function InsertLivre()
		{
			$this->Connection();
			$SelectAuteur=$this->ConnectionString->query("Select * from Auteurs ; ") ;

			echo '
			<form method="POST">
				<input type="text" name="LivreNom" placeholder="Nom du livre">
				Auteur : <select name="LivreAuteur">
				' ;

			while($Display=$SelectAuteur->fetch())
			{
				echo '<option>('.$Display['IdAuteur'].')'.$Display['PrenomAuteur'].' '.$Display['NomAuteur'].'</option>' ;
			}

			echo'
				</select>
				<input type="date" name="LivreDate" placeholder="Date écriture">
				<textarea name="LivreResume" placeholder="Résumé"></textarea>
				<input type="submit" name="AddLivre" value="Ajouter Livre">
			</form> 
				' ;

			if(isset($_POST['AddLivre']))
			{
				$LivreNom=$_POST['LivreNom'] ;
				$LivreDate=$_POST['LivreDate'] ;
				$LivreResume=$_POST['LivreResume'] ;
				$LivreAuteur=ltrim($_POST['LivreAuteur'], "0123456789()") ;
				$LivreAuteurID= preg_replace('/[^0-9.]+/', '', $_POST['LivreAuteur']);
				$LivreTempID=explode(" ", $LivreNom) ;
				$LivreTempAuteur=explode(" ", $LivreAuteur) ;
				$LivreID="";
				
				foreach ($LivreTempID as $Value)
				{
					if(strpos($Value,"'") !== false) 
					{
   						$TempID=str_replace("'", "", $Value) ;
   						$TempID=substr($TempID, 0,2) ;
   						$TempID=strtoupper(substr($TempID,0,2)).substr($TempID,2);
   						$TempID=lcfirst($TempID) ;
   						$LivreID=$LivreID.''.$TempID;
					}
					else
					{
						$TempID=substr($Value, 0,1) ;
						$TempID=strtoupper($TempID) ;
						$LivreID=$LivreID.''.$TempID;
					}

				}

				foreach ($LivreTempAuteur as $Value) 
				{
					$TempAuteur=substr($Value, 0,1);
					$LivreID=$LivreID.''.$TempAuteur;
				}

				$DonneesInsert=array(
					"LivreID"=>$LivreID,
					"LivreNom"=>$LivreNom,
					"LivreDate"=>$LivreDate,
					"LivreResume"=>$LivreResume) ;
				$RequeteInsert=$this->ConnectionString->prepare("Insert into Livre (IdLivre, TitreLivre, AnneeEcriture, ResumeLivre)
					VALUES(:LivreID, :LivreNom, :LivreDate, :LivreResume) ;") ;
				try
				{
					$ResultatInsert=$RequeteInsert->execute($DonneesInsert);
					if($ResultatInsert)
					{
						$DonneesInsert=array(
							"LivreID"=>$LivreID,
							"AuteurID"=>$LivreAuteurID) ;

						$RequeteInsert=$this->ConnectionString->prepare("Insert into Ecrire (IdLivre, IdAuteur)
							VALUES (:LivreID, :AuteurID) ;") ;
						try
						{
							$ResultatInsert=$RequeteInsert->execute($DonneesInsert);
							if($ResultatInsert)
							{
								echo "Insertion réussie." ;
							}
						}
						catch(Exception $e)
						{
							echo $e ;
						}
					}

				}
				catch(Exception $e)
				{
					echo $e;
				}

			}
		}


		public function CompteClient()
		{
			$this->Connection();
			if(isset($_SESSION['login_user']))
			{
				$SessionID=$_SESSION['id'] ;
				$Login=$_SESSION['login_user'] ;
				$InfoCompte=mysqli_query($this->ConnectionMysqli, "Select * from AdherentEmprunt where IdAdherent='".$SessionID."' AND DATERETOUR IS NULL;");
				$EstAdmin=mysqli_query($this->ConnectionMysqli, "Select EstAdmin from Adherent where IdAdherent='".$SessionID."' And EstAdmin='Oui';") ;
				if($InfoCompte->num_rows>=1)
				{
					echo "Livre(s) emprunté(s) et non rendus : <br>" ;
					while($Afficher=$InfoCompte->fetch_assoc())
					{
						$Emprunt=date('d-m-Y', strtotime($Afficher['DateEmprunt']));
						$Retour=date('d-m-Y', strtotime($Afficher['DateRetourPrevu']));

						echo 	$Afficher['TitreLivre']."<br>
								Emprunté le : ".$Emprunt."<br>
								à retourner avant le : ".$Retour;
						$tsE= strtotime($Emprunt);
						$tsR = strtotime($Retour);
						$tsCur= strtotime(date('d-m-Y')) ;
						if($tsE>$tsCur)
						{
							echo "Vous pouvez chercher votre livre dès demain. <br>
									Si il vous est impossible d'aller le récuperer, vous pouvez annuler votre réservation.<br>
									<form method=POST>
										<input type='submit' value='Supprimer' name='Supprimer'>
										<input type='hidden' name ='EmpDelete'value='".$Afficher['IdEmprunt']."'>
									</form>";

							if(isset($_POST['Supprimer']))
							{
								$IdEmprunt=$_POST['EmpDelete'] ;
								$DeleteReservation=mysqli_query($this->ConnectionMysqli, "DELETE FROM RETOUREMPRUNT WHERE IdEmprunt='".$IdEmprunt."';") ;
								if($IdEmprunt)
								{
									echo "<script> alert('Suppression effectuée.')</script>";
								}
								else
								{
									echo "<script> alert('Suppression impossible.')</script>";
								}
							} 
						}
						elseif($tsCur>$tsR)
						{
							$seconds_diff = $tsCur - $tsR;
							$seconds_diff=$seconds_diff/86400 ;
							echo "<br>Vous avez ".$seconds_diff." jours de retard.<br><br>" ;
						}
						elseif($tsCur<$tsR)
						{
							$seconds_diff = $tsR - $tsCur;
							$seconds_diff=$seconds_diff/86400 ;
							echo "<br>Il vous reste ".$seconds_diff." jours pour rendre votre livre. <br><br>" ;
						}
					}
				}
				else
				{
					echo "Aucun livre emprunté" ;
				}
				if($EstAdmin->num_rows==1)
					{
						$this->AdminPanel() ;
						$this->AdminAdherent() ;
						$this->AdminAuteurs();
						$this->AdminPays();
						$this->AdminGenre();
						$this->AdminGenreLivre();
						$this->AdminExemplaire();
						$this->AdminEBook();
						$this->InsertLivre();
						$this->AdminME();
						$this->AdminLivre();
						$this->Ecrireblog();
					}		
			}
			else 
			{
				echo 'pas de compte' ;
			}
		}

		public function GetCompteur() 
		{
	    	echo $this->CompteurSlideShow ;
	 	}

	 	// REVOIR LA BDD ET METTRE LES DEUX ARTICLES LES PLUS RECENTS.

	 	public function Blog()
	 	{
	 		$this->Connection();
	 		$Blog=$this->ConnectionString->query('select * from BlogCover order by IdArticle desc limit 2 ;') ;
	 		while($Afficher=$Blog->fetch())
	 		{
	 			echo "
	 				<a href='PageLivre.php?page=Exemp?".$Afficher['IdLivre']."'>
	 				<img class='PNGArticle' src=".$Afficher['CouvertureLivre']." height='50%'></a>
	 				<H1 class='TitreArticle'>".$Afficher['TitreArticle']."</H1><br/>
	 				<p class='ContenuArticle'>".$Afficher['ContenuArticle']."</p><br>
	 				<a class='LienArticle' href='PageLivre.php?page=Auteur?".$Afficher['PrenomAuteur']."?".$Afficher['NomAuteur']."?".$Afficher['IdAuteur']."'> Auteur du livre : " 
							.$Afficher['PrenomAuteur']. " " .$Afficher['NomAuteur']. "</a>";
	 		}
	 	}

//---------------------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------------	 	
	 			// PARTIE ADMIN

	 	public function AdminPanel()
	 	{
	 		$QAdAdherent=$this->ConnectionString->query("select * from Adherent ;") ;
	 		echo "
	 				<table>
	 					<tr>
		 					<th>Identifiant</th>
		 					<th>Nom</th>
							<th>Prenom</th>
	 						<th>Date de naissance </th>
	 						<th>Ville </th>
		 					<th>Rue </th>
		 					<th>Code Postal </th>
		 					<th>Mail </th>
		 					<th>Telephone </th>
		 					<th>Login </th>
		 					<th>Est litigieux </th>
		 					<th>Date litige </th>
		 					<th>Est admin </th>
		 				</tr> " ;
	 		while($Afficher=$QAdAdherent->fetch())
	 		{
	 			echo 	"
		 				<tr>
		 					<form method='post'>
			 					<td><input type='text' Name='AdIdentifiant' value='".$Afficher['IdAdherent']."'></td>
			 					<td><input type='text' Name='AdNom' value='".$Afficher['Nom']."'></td>
			 					<td><input type='text' Name='AdPrenom' value='".$Afficher['Prenom']."'></td>
			 					<td><input type='text' Name='AdDateN' value='".$Afficher['DateNaissance']."'></td>
			 					<td><input type='text' Name='AdVille' value='".$Afficher['Ville']."'></td>
			 					<td><input type='text' Name='AdRue' value='".$Afficher['Rue']."'></td>
			 					<td><input type='text' Name='AdCP' value='".$Afficher['CP']."'></td>
			 					<td><input type='text' Name='AdMail' value='".$Afficher['Mail']."'></td>
			 					<td><input type='text' Name='AdTelephone' value='".$Afficher['Tel']."'></td>
			 					<td><input type='text' Name='AdLogin' value='".$Afficher['Login']."'></td>
			 					<td><input type='text' Name='AdLitigieux' value='".$Afficher['Litigieux']."'></td>
			 					<td><input type='text' Name='AdDateL' value='".$Afficher['DateLitige']."'></td>
			 					<td><input type='text' Name='AdAdmin' value='".$Afficher['EstAdmin']."'></td>
			 					<td>
			 						<input type='submit' name='AdhDelete' value='Supprimer'>
			 						<input type='submit' name='AdhUpdate' value='Mettre à jour'>
			 						<input type='hidden' name='HiddenToGet' value='Supp".$Afficher['IdAdherent']."'>
		 						</td>
		 					</form>
		 				</tr>" ;
	 		}

	 		echo '</table>' ;

	 		$QAuteurs=$this->ConnectionString->query("Select * from Auteurs") ;
	 		echo "
	 			<table>
	 				<tr>
	 					<th>ID</th>
	 					<th>Nom</th>
	 					<th>Prenom</th>
	 					<th>DateNaissance</th>
	 					<th>DateDeces</th>
	 					<th>Pays</th>
	 					<th>Portrait</th>
	 				</tr>
	 			" ;

	 		while($Afficher=$QAuteurs->fetch())
	 		{
	 			echo
	 				"<tr>
	 					<form method='POST'>
	 						<td><input type='text' Name='AutId' value='".$Afficher['IdAuteur']."'></td>
	 						<td><input type='text' Name='AutNom' value='".$Afficher['NomAuteur']."'></td>
	 						<td><input type='text' name='AutPrenom' value='".$Afficher['PrenomAuteur']."'></td>
	 						<td><input type='text' name='AutDN' value='".$Afficher['DateNaissA']."'></td>
	 						<td><input type='text' name='AutDC' value='".$Afficher['DateDeces']."'></td>
	 						<td><input type='text' name='AutPays' value='".$Afficher['IdPays']."'></td>
	 						<td><input type='text' name='AutPhoto' value='".$Afficher['AuteurPhoto']."'></td>
	 						<td>
		 						<input type='submit' name='AutDelete' value='Supprimer'>
		 						<input type='submit' name='AutUpdate' value='Mettre à jour'>
		 						<input type='hidden' name='HiddenToGetAut' value='Supp".$Afficher['IdAuteur']."'>
		 					</td>
		 				</form>
		 			</tr>"; 

		 			//<td><textarea name='AutBio' value='".$Afficher['Biographie']."'></textarea></td>
	 		}

	 		echo
	 			"<tr>
	 				<form method='POST'>
	 					<td><input type='text' Name='AutId' placeholder='ID'></td>
	 					<td><input type='text' Name='AutNom' placeholder='Nom'></td>
	 					<td><input type='text' name='AutPrenom' placeholder='Prenom'></td>
	 					<td><input type='text' name='AutDN' placeholder='Date de Naissance'></td>
	 					<td><input type='text' name='AutDC' placeholder='Date Deces'></td>
	 					<td><input type='text' name='AutPays' placeholder='Pays'></td>
	 					<td><input type='text' name='AutPhoto' placeholder='Chemin Photo'></td>
	 					<td>
		 					<input type='submit' name='AutAdd' value='Ajouter'>
		 				</td>
		 			</form>
		 		</tr>"; 

	 		echo '</table>' ;

	 		$QPays=$this->ConnectionString->query("select * from Pays") ;

	 		echo "
	 			<table>
	 				<tr>
	 					<th>Pays</th>
	 				</tr>" ;

	 		while($Afficher=$QPays->fetch())
	 		{
	 			echo "
	 				<tr>
	 					<form method='POST'>
	 						<td><input type='text' Name='Pays' value='".$Afficher['IdPays']."'></td>
	 						<td>
	 							<input type='submit' Name='PaysDelete' value='Supprimer'>
		 						<input type='hidden' Name='HiddenToGetPays' value='".$Afficher['IdPays']."'>
		 					</td>
	 					</form>
	 				</tr>" ;
	 		}

	 		echo "
	 			<tr>
	 				<form method='POST'>
	 					<td><input type='text' Name='IdPays' placeholder='Nom du pays'></td>
	 					<td>
		 					<input type='submit' Name='PaysAdd' value='Ajouter'>
						</td>
					</form>
				</tr>
				</table>" ;

			// EXEMPLAIRES

			$QExemplaires=$this->ConnectionString->query("Select * from Exemplaire") ;

			echo "
					<table>
						<tr>
							<th>Livre</th>
							<th>N° exemplaire</th>
							<th>Date Achat</th>
							<th>Date Edition</th>
							<th>Nombre de pages</th>
							<th>Langue</th>
							<th>Maison d'édition</th>
							<th>Chemin Couverture</th>
						</tr>" ;
			while($Afficher=$QExemplaires->fetch())
			{
				echo "
					<tr>
						<form method='POST'>
							<td><input type='text' name='IdLivre' value='".$Afficher['IdLivre']."'></td>
							<td><input type='text' name='IdExemplaire' value='".$Afficher['IdExemplaire']."'></td>
							<td><input type='text' name='DateAchat' value='".$Afficher['DateAchat']."'></td>
							<td><input type='text' name='DateEdition' value='".$Afficher['DateEdition']."'></td>
							<td><input type='text' name='nbPages' value='".$Afficher['nbPages']."'></td>
							<td><input type='text' name='LangueExemplaire' value='".$Afficher['LangueExemplaire']."'></td>
							<td><input type='text' name='NomMaisonEdition' value='".$Afficher['NomMaisonEdition']."'></td>
							<td><input type='text' name='CouvertureLivre' value='".$Afficher['CouvertureLivre']."'></td>
							<td>
								<input type='submit' value='Supprimer' name='ExemplaireDelete'>
								<input type='submit' value='Mettre à jour' name='ExemplaireUpdate'>
								<input type='Hidden' name='HiddenToGetEx'value='".$Afficher['IdLivre']."?".$Afficher['IdExemplaire']."'>
							</td>
						</form>
					</tr>" ;
			}

			echo "
				<tr>
					<form method='POST'>
						<td><input type='text' name='IdLivre' placeholder='ID Livre'></td>
						<td><input type='text' name='IdExemplaire' placeholder='ID Exemplaire'></td>
						<td><input type='text' name='DateAchat' placeholder='Date achat'></td>
						<td><input type='text' name='DateEdition' placeholder='Date edition'></td>
						<td><input type='text' name='nbPages' placeholder='Nombre de pages'></td>
						<td><input type='text' name='LangueExemplaire' placeholder='Langue'></td>
						<td><input type='text' name='NomMaisonEdition' placeholder='Maison d edition'></td>
						<td><input type='text' name='CouvertureLivre' placeholder='Couverture'></td>
						<td><input type='submit' value='Ajouter' name='AddExemplaire'></td>
					</form>
				</tr>
			</table>" ;

			// AJOUTER EBook

						$QExemplairesEb=$this->ConnectionString->query("Select * from EBook") ;

			echo "
					<table>
						<tr>
							<th>Livre</th>
							<th>N° exemplaire</th>
							<th>Date Edition</th>
							<th>Nombre de pages</th>
							<th>Langue</th>
							<th>Maison d'édition</th>
							<th>Chemin Couverture</th>
							<th>Chemin DL</th>
						</tr>" ;
			while($Afficher=$QExemplairesEb->fetch())
			{
				echo "
					<tr>
						<form method='POST'>
							<td><input type='text' name='IdLivre' value='".$Afficher['IdLivre']."'></td>
							<td><input type='text' name='IdExemplaire' value='".$Afficher['IdExemplaire']."'></td>
							<td><input type='text' name='DateEdition' value='".$Afficher['DateEdition']."'></td>
							<td><input type='text' name='nbPages' value='".$Afficher['nbPages']."'></td>
							<td><input type='text' name='LangueExemplaire' value='".$Afficher['LangueExemplaire']."'></td>
							<td><input type='text' name='NomMaisonEdition' value='".$Afficher['NomMaisonEdition']."'></td>
							<td><input type='text' name='CouvertureLivre' value='".$Afficher['CouvertureLivre']."'></td>
							<td><input type='text' name='CheminEBook' value='".$Afficher['CheminEBook']."'></td>
							<td>
								<input type='submit' value='Supprimer' name='EBookDelete'>
								<input type='submit' value='Mettre à jour' name='EBookUpdate'>
								<input type='Hidden' name='HiddenToGetExEb'value='".$Afficher['IdLivre']."?".$Afficher['IdExemplaire']."'>
							</td>
						</form>
					</tr>" ;
			}

			echo "
				<tr>
					<form method='POST' enctype='multipart/form-data'>
						<td><input type='text' name='IdLivre' placeholder='ID Livre'></td>
						<td><input type='text' name='IdExemplaire' placeholder='ID Exemplaire'></td>
						<td><input type='text' name='DateEdition' placeholder='Date edition'></td>
						<td><input type='text' name='nbPages' placeholder='Nombre de pages'></td>
						<td><input type='text' name='LangueExemplaire' placeholder='Langue'></td>
						<td><input type='text' name='NomMaisonEdition' placeholder='Maison d edition'></td>
						<td><input type='file' name='CouvertureLivre' value='Couverture'></td>
						<td><input type='file' name='UpEBook' value='UpEBook'></td>
						<td><input type='submit' value='Ajouter' name='AddEBook'></td>
					</form>
				</tr>
			</table>" ;

			// AJOUTER GENRE 

			$QGenre = $this->ConnectionString->query("Select * from Genre") ;

			echo "
					<table>
						<tr>
							<th>Nom du Genre</th>
						</tr>" ;
			while($Afficher=$QGenre->fetch())
			{
				echo "
					<tr>
						<form method='POST'>
							<td><input type='text' name='IdGenre' value='".$Afficher['IdGenre']."'></td>
							<td>
								<input type='submit' value='Supprimer' name='GenreDelete'>
								<input type='submit' value='Mettre à jour' name='GenreUpdate'>
								<input type='Hidden' name='HiddenToGetGenre' value='".$Afficher['IdGenre']."'>
							</td>
						</form>
					</tr>" ;
			}

			echo "
				<tr>
					<form method='POST'>
						<td><input type='text' name='IdGenre' placeholder='ID Genre'></td>
						<td><input type='submit' value='Ajouter' name='AddGenre'></td>
					</form>
				</tr>
			</table>" ;

			$QGenreL = $this->ConnectionString->query("Select * from GenreLivre") ;

			echo "
					<table>
						<tr>
							<th>Nom du Genre</th>
							<th>Id Livre </th>
						</tr>" ;
			while($Afficher=$QGenreL->fetch())
			{
				echo "
					<tr>
						<form method='POST'>
							<td><input type='text' name='IdGenre' value='".$Afficher['IdGenre']."'></td>
							<td><input type='text' name='IdLivre' value='".$Afficher['IdLivre']."'></td>
							<td>
								<input type='submit' value='Supprimer' name='GenreLDelete'>
								<input type='submit' value='Mettre à jour' name='GenreLUpdate'>
								<input type='Hidden' name='HiddenToGetGenreL' value='".$Afficher['IdGenre']."?".$Afficher['IdLivre']."'>
							</td>
						</form>
					</tr>" ;
			}

			echo "
				<tr>
					<form method='POST'>
						<td><input type='text' name='IdGenre' placeholder='ID Genre'></td>
						<td><input type='text' name='IdLivre' placeholder='ID Livre'></td>
						<td><input type='submit' value='Ajouter' name='AddGenreL'></td>
					<form>
				</tr>
			</table>" ;


	 	//Ajouter maison edition

	 		$QMEdition = $this->ConnectionString->query("Select * from Edition") ;

			echo "
					<table>
						<tr>
							<th>Nom de la maison d'édition</th>
						</tr>" ;
			while($Afficher=$QMEdition->fetch())
			{
				echo "
					<tr>
						<form method='POST'>
							<td><input type='text' name='IdME' value='".$Afficher['NomMaisonEdition']."'></td>
							<td>
								<input type='submit' value='Supprimer' name='MEDelete'>
								<input type='submit' value='Mettre à jour' name='MEUpdate'>
								<input type='Hidden' name='HiddenToGetME' value='".$Afficher['NomMaisonEdition']."'>
							</td>
						</form>
					</tr>" ;
			}

			echo "
				<tr>
					<form method='POST'>
						<td><input type='text' name='IdME' placeholder='Nom maison edition'></td>
						<td><input type='submit' value='Ajouter' name='AddME'></td>
					</form>
				</tr>
			</table>" ;

						// LIVRES

			$QLivres=$this->ConnectionString->query("Select * from Livre") ;

			echo "
					<table>
						<tr>
							<th>IdLivre</th>
							<th>TitreLivre</th>
							<th>Annee Ecriture</th>
							<th>ResumeLivre</th>
						</tr>" ;
			while($Afficher=$QLivres->fetch())
			{
				$Titre=$Afficher['TitreLivre'] ;
				$ResumeLivre=$Afficher['ResumeLivre'] ;
				echo '
					<tr>
						<form method="POST">
							<td><input type="text" name="IdLivre" value="'.$Afficher['IdLivre'].'"></td>
							<td><input type="text" name="TitreLivre" value="'.$Titre.'"></td>
							<td><input type="text" name="AnneeEcriture" value="'.$Afficher['AnneeEcriture'].'"></td>
							<td>
								<input type="submit" value="Supprimer" name="LivreDelete">
								<input type="submit" value="Mettre à jour" name="LivreUpdate">
								<input type="Hidden" name="HiddenToGetL" value="'.$Afficher['IdLivre'].'">
							</td>
						</form>
					</tr>' ;
			}
		}


	 	public function AdminAdherent()
	 	{
	 		if(isset($_POST['AdhDelete']))
	 		{
	 			$ToDelete=$_POST['HiddenToGet'] ;
	 			$ToDelete=preg_replace("/[^0-9]/","",$ToDelete);
	 			mysqli_query($this->ConnectionMysqli, "Delete FROM Adherent where IdAdherent=".$ToDelete." ; ");
	 		}
	 		if(isset($_POST['AdhUpdate']))
	 		{
	 			$VIdentifiant=$_POST['AdIdentifiant'] ;
	 			$VNom=$_POST['AdNom'] ;
	 			$VPrenom=$_POST['AdPrenom'] ;
	 			$VDateN=$_POST['AdDateN'] ;
	 			$VVille=$_POST['AdVille'] ;
	 			$VRue=$_POST['AdRue'] ;
	 			$VCP=$_POST['AdCP'] ;
	 			$VMail=$_POST['AdMail'] ;
	 			$VTel=$_POST['AdTelephone'] ;
	 			$VLogin=$_POST['AdLogin'] ;
	 			$VLitigieux=$_POST['AdLitigieux'] ;
	 			$VDateL=$_POST['AdDateL'] ;
	 			$VAmdin=$_POST['AdAdmin'] ;

	 			$ToUpdate=$_POST['HiddenToGet'] ;
	 			$ToUpdate=preg_replace("/[^0-9]/", "", $ToUpdate) ;

	 			mysqli_query($this->ConnectionMysqli, "Update Adherent set 
	 											IdAdherent='".$VIdentifiant."',
	 											Nom='".$VNom."',
	 											Prenom='".$VPrenom."',
	 											DateNaissance='".$VDateN."',
	 											Ville='".$VVille."',
	 											Rue='".$VRue."',
	 											CP='".$VCP."',
	 											Mail='".$VMail."',
	 											Tel='".$VTel."',
	 											Login='".$VLogin."',
	 											Litigieux='".$VLitigieux."',
	 											DateLitige='".$VDateL."',
	 											EstAdmin='".$VAmdin."'
	 											WHERE IdAdherent='".$ToUpdate."';") ;
	 		}

	 	}

	 	public function AdminAuteurs()
	 	{
	 		if(isset($_POST['AutDelete']))
	 		{
	 			$ToDelete=$_POST['HiddenToGetAut'] ;
	 			$ToDelete=preg_replace("/[^0-9]/","",$ToDelete);
	 			mysqli_query($this->ConnectionMysqli, "Delete FROM Auteurs where IdAuteur=".$ToDelete." ; ");

	 		}
	 		if(isset($_POST['AutUpdate']))
	 		{
	 			$AutId=$_POST['AutId'] ;
	 			$AutNom=$_POST['AutNom'] ;
	 			$AutPrenom=$_POST['AutPrenom'] ;
	 			$AutDN=$_POST['AutDN'] ;
	 			$AutDC=$_POST['AutDC'] ;
	 			$AutPays=$_POST['AutPays'] ;
	 			$AutPhoto=$_POST['AutPhoto'] ;

				$ToUpdate=$_POST['HiddenToGetAut'] ;
	 			$ToUpdate=preg_replace("/[^0-9]/","",$ToUpdate); 			

				mysqli_query($this->ConnectionMysqli, "Update Auteurs SET 
												IdAuteur='".$AutId."',
												NomAuteur='".$AutNom."',
												PrenomAuteur='".$AutPrenom."',
												DateNaissA='".$AutDN."',
												DateDeces='".$AutDC."',
												IdPays='".$AutPays."',
												AuteurPhoto='".$AutPhoto."'
												WHERE IdAuteur='".$ToUpdate."' ;") ;
 	 		}
 	 		if(isset($_POST['AutAdd']))
 	 		{
 	 			$this->Connection();
 	 			
 	 			$AutId=$_POST['AutId'] ;
	 			$AutNom=$_POST['AutNom'] ;
	 			$AutPrenom=$_POST['AutPrenom'] ;
	 			$AutDN=$_POST['AutDN'] ;
	 			$AutDC=$_POST['AutDC'] ;
	 			$AutPays=$_POST['AutPays'] ;
	 			$AutPhoto=$_POST['AutPhoto'] ;
	 			$AutBio='null' ;

 	 			$DonneesInsert=array(
					"AutId"=>$AutId,
					"AutNom"=>$AutNom,
					"AutPrenom"=>$AutPrenom,
					"AutDN"=>$AutDN,
					"AutDC"=>$AutDC,
					"AutPays"=>$AutPays,
					"AutBio"=>$AutBio,
					"AutPhoto"=>$AutPhoto) ;

				$RequeteInsert=$this->ConnectionString->prepare("INSERT INTO Auteurs (IdAuteur, NomAuteur, PrenomAuteur, DateNaissA, DateDeces, IdPays, Biographie, AuteurPhoto)
					VALUES(:AutId, :AutNom, :AutPrenom, :AutDN, :AutDC, :AutPays, :AutBio, :AutPhoto) ;") ;

				try
					{
						$ResultatInsert=$RequeteInsert->execute($DonneesInsert);
						if($ResultatInsert)
						{
							echo "<br><br><br><br> Insertion réussie." ;
						}
					}
				catch(Exception $e)
					{
						echo '<br><br><br><br>'.$e ;
					}
 	 		}
	 	}

	 	public function AdminPays()
	 	{
	 		if(isset($_POST['PaysDelete']))
	 		{
	 			$ToDelete=$_POST['HiddenToGetPays'] ;
	 			mysqli_query($this->ConnectionMysqli, "Delete FROM Pays where IdPays='".$ToDelete."' ; ");

	 		}
	 		if(isset($_POST['PaysAdd']) && $_POST['IdPays']!='')
	 		{
	 			$IdPays=$_POST['IdPays'] ;

	 			$DonneesInsert=array(
					"IdPays"=>$IdPays) ;

				$RequeteInsert=$this->ConnectionString->prepare("INSERT INTO Pays (IdPays)
					VALUES(:IdPays) ;") ;

				try
					{
						$ResultatInsert=$RequeteInsert->execute($DonneesInsert);
						if($ResultatInsert)
						{
							echo "<br><br><br><br> Insertion réussie." ;
						}
					}
				catch(Exception $e)
					{
						echo '<br><br><br><br>'.$e ;
					}

 	 		}
	 	}

	 	public function AdminGenre()
	 	{
	 		if(isset($_POST['GenreDelete']))
	 		{
	 			$ToDelete=$_POST['HiddenToGetGenre'] ;
	 			mysqli_query($this->ConnectionMysqli, "Delete FROM GENRE where IdGenre='".$ToDelete."' ; ");

	 		}
	 		if(isset($_POST['GenreUpdate']))
	 		{
	 			$IdGenre=$_POST['IdGenre'] ;

				$HiddenToGetGenre=$_POST['HiddenToGetGenre'] ;

				mysqli_query($this->ConnectionMysqli, "UPDATE GENRE SET 
													IdGenre='".$IdGenre."'
													WHERE IdGenre='".$HiddenToGetGenre."' ;");
			}


	 		if(isset($_POST['AddGenre']) && $_POST['IdGenre']!='')
	 		{
	 			$IdGenre=$_POST['IdGenre'] ;

	 			$DonneesInsert=array(
					"IdGenre"=>$IdGenre) ;

				$RequeteInsert=$this->ConnectionString->prepare("INSERT INTO Genre (IdGenre)
					VALUES(:IdGenre) ;") ;

				try
					{
						$ResultatInsert=$RequeteInsert->execute($DonneesInsert);
						if($ResultatInsert)
						{
							echo "<br><br><br><br> Insertion réussie." ;
						}
					}
				catch(Exception $e)
					{
						echo '<br><br><br><br>'.$e ;
					}

 	 		}
	 	}

	 	public function AdminLivre()
	 	{
	 		if(isset($_POST['LivreDelete']))
	 		{
	 			$ToDelete=$_POST['HiddenToGetL'] ;
	 			mysqli_query($this->ConnectionMysqli, "Delete from Livre where IdLivre='".$ToDelete."'; ");
	 		}

	 		if(isset($_POST['LivreUpdate']))
	 		{
	 			$IdLivre=$_POST['IdLivre'] ;
	 			$TitreLivre=$_POST['TitreLivre'] ;
	 			$AnneeEcriture=$_POST['AnneeEcriture'] ;

	 			$HiddenToGetL=$_POST['HiddenToGetL'] ;

	 			mysqli_query($this->ConnectionMysqli, 'UPDATE LIVRE SET
	 												IdLivre="'.$IdLivre.'",
	 												TitreLivre="'.$TitreLivre.'",
	 												AnneeEcriture="'.$AnneeEcriture.'"
	 												WHERE IdLivre="'.$HiddenToGetL.'" ;') or die(mysqli_error($this->ConnectionMysqli)) ;
	 		}
	 	} 

	 	public function AdminME()
	 	{
	 		if(isset($_POST['MEDelete']))
	 		{	
	 			$ToDelete=$_POST['HiddenToGetME'] ;
	 			mysqli_query($this->ConnectionMysqli, "Delete from Edition where NomMaisonEdition='".$ToDelete."'; ");
	 		}
	 		
	 		if(isset($_POST['MEUpdate']))
	 		{
	 			$IDME=$_POST['IdME'] ;

				$HiddenToGetME=$_POST['HiddenToGetME'] ;

				mysqli_query($this->ConnectionMysqli, "UPDATE EDITION SET 
													NomMaisonEdition='".$IDME."'
													WHERE NomMaisonEdition='".$HiddenToGetME."' ;");
			}


	 		if(isset($_POST['AddME']) && $_POST['IdME']!='')
	 		{
	 			$IdME=$_POST['IdME'] ;
	 			$DonneesInsert=array(
	 				"IdME"=>$IdME);

	 			$RequeteInsert=$this->ConnectionString->prepare("INSERT INTO Edition(NomMaisonEdition)
	 				VALUES (:IdME) ;");

	 			try
					{
						$ResultatInsert=$RequeteInsert->execute($DonneesInsert);
						if($ResultatInsert)
						{
							echo "<br><br><br><br> Insertion réussie." ;
						}
					}
				catch(Exception $e)
					{
						echo '<br><br><br><br>'.$e ;
					}
				}

	 	}

	 	public function AdminGenreLivre()
	 	{
	 		if(isset($_POST['GenreLDelete']))
	 		{
	 			$HiddenToGetGenreL=$_POST['HiddenToGetGenreL'] ;
	 			$ToDelete=explode("?", $HiddenToGetGenreL);
	 			mysqli_query($this->ConnectionMysqli, "Delete FROM GenreLivre where IdGenre='".$ToDelete[0]."' AND IdLivre='".$ToDelete[1]."' ; ");

	 		}

	 		if(isset($_POST['GenreLUpdate']))
	 		{
	 			$IdGenre=$_POST['IdGenre'] ;
	 			$IdLivre=$_POST['IdLivre'] ;

				$HiddenToGetGenreL=$_POST['HiddenToGetGenreL'] ;
				$ToUpdate=explode("?", $HiddenToGetGenreL) ;

				mysqli_query($this->ConnectionMysqli, "UPDATE GENRELIVRE SET 
													IdLivre='".$IdLivre."',
													IdGenre='".$IdGenre."'
													WHERE IdGenre='".$ToUpdate[0]."' AND IdLivre='".$ToUpdate[1]."' ;") ;
			}
	 		if(isset($_POST['AddGenreL']) && $_POST['IdGenre']!='')
	 		{
	 			$IdGenre=$_POST['IdGenre'] ;
	 			$IdLivre=$_POST['IdLivre'] ;

	 			$DonneesInsert=array(
					"IdGenre"=>$IdGenre,
					"IdLivre"=>$IdLivre) ;

				$RequeteInsert=$this->ConnectionString->prepare("INSERT INTO GenreLivre (IdGenre, IdLivre)
					VALUES(:IdGenre, :IdLivre) ;") ;

				try
					{
						$ResultatInsert=$RequeteInsert->execute($DonneesInsert);
						if($ResultatInsert)
						{
							echo "<br><br><br><br> Insertion réussie." ;
						}
					}
				catch(Exception $e)
					{
						echo '<br><br><br><br>'.$e ;
					}

 	 		}
	 	}


	 	public function AdminExemplaire()
	 	{
	 		if(isset($_POST['ExemplaireDelete']))
	 		{
	 			$HiddenToGetEx=$_POST['HiddenToGetEx'] ;
	 			$ToDelete=explode("?", $HiddenToGetEx);
	 			mysqli_query($this->ConnectionMysqli, "Delete FROM Exemplaire where IdLivre='".$ToDelete[0]."' AND IdExemplaire='".$ToDelete[1]."' ; ");
	 		}
	 		if(isset($_POST['ExemplaireUpdate']))
	 		{
	 			$IdLivre=$_POST['IdLivre'] ;
				$IdExemplaire=$_POST['IdExemplaire'] ;
				$DateAchat=$_POST['DateAchat'] ;
				$DateEdition=$_POST['DateEdition'] ;
				$nbPages=$_POST['nbPages'] ;
				$LangueExemplaire=$_POST['LangueExemplaire'] ;
				$NomMaisonEdition=$_POST['NomMaisonEdition'] ;
				$CouvertureLivre=$_POST['CouvertureLivre'] ;

				$HiddenToGetEx=$_POST['HiddenToGetEx'] ;
				$ToUpdate=explode("?", $HiddenToGetEx) ;

				mysqli_query($this->ConnectionMysqli, "UPDATE Exemplaire SET 
													IdLivre='".$IdLivre."',
													IdExemplaire='".$IdExemplaire."',
													DateAchat='".$DateAchat."',
													DateEdition='".$DateEdition."',
													nbPages='".$nbPages."',
													LangueExemplaire='".$LangueExemplaire."',
													NomMaisonEdition='".$NomMaisonEdition."',
													CouvertureLivre='".$CouvertureLivre."'
													WHERE IdLivre='".$ToUpdate[0]."' AND IdExemplaire='".$ToUpdate[1]."' ;") ;
			}
			if(isset($_POST['AddExemplaire']))
			{
				$IdLivre=$_POST['IdLivre'] ;
				$IdExemplaire=$_POST['IdExemplaire'] ;
				$DateA=$_POST['DateAchat'] ;
				$DateE=$_POST['DateEdition'] ;
				$nbPages=$_POST['nbPages'] ;
				$LangueE=$_POST['LangueExemplaire'] ;
				$NomME=$_POST['NomMaisonEdition'] ;
				$Couverture=$_POST['CouvertureLivre'] ;

				$DonneesInsert=array(
					'IdLivre'=>$IdLivre,
					'IdExemplaire'=>$IdExemplaire,
					'DateAchat'=>$DateA,
					'DateEdition'=>$DateE,
					'nbPages'=>$nbPages,
					'LangueExemplaire'=>$LangueE,
					'NomMaisonEdition'=>$NomME,
					'CouvertureLivre'=>$Couverture) ;

				$RequeteInsert=$this->ConnectionString->prepare("INSERT INTO Exemplaire 
																(IdLivre, IdExemplaire, DateAchat, DateEdition, nbPages, LangueExemplaire, NomMaisonEdition, CouvertureLivre) VALUES 
																(:IdLivre, :IdExemplaire, :DateAchat, :DateEdition, :nbPages, :LangueExemplaire, :NomMaisonEdition, :CouvertureLivre) ") ;

				try
					{
						$ResultatInsert=$RequeteInsert->execute($DonneesInsert);
						if($ResultatInsert)
						{
							echo "<br><br><br><br> Insertion réussie." ;
						}
					}
				catch(Exception $e)
					{
						echo '<br><br><br><br>'.$e ;
					}

			}
	 	}

	 	public function AdminEBook()
	 	{
	 		if(isset($_POST['EBookDelete']))
	 		{
	 			$HiddenToGetEx=$_POST['HiddenToGetExEb'] ;
	 			$ToDelete=explode("?", $HiddenToGetEx);
	 			mysqli_query($this->ConnectionMysqli, "Delete FROM EBook where IdLivre='".$ToDelete[0]."' AND IdExemplaire='".$ToDelete[1]."' ; ");
	 			try
	 			{
	 				if(file_exists('EBook/'.$ToDelete[0].'.pdf'))
	 				{
	 					unlink('EBook/'.$ToDelete[0].'.pdf');
	 				}
	 			}
	 			catch(Exception $e)
	 			{

	 			}
	 		}
	 		if(isset($_POST['EBookUpdate']))
	 		{
	 			$IdLivre=$_POST['IdLivre'] ;
				$IdExemplaire=$_POST['IdExemplaire'] ;
				$DateEdition=$_POST['DateEdition'] ;
				$nbPages=$_POST['nbPages'] ;
				$LangueExemplaire=$_POST['LangueExemplaire'] ;
				$NomMaisonEdition=$_POST['NomMaisonEdition'] ;
				$CouvertureLivre=$_POST['CouvertureLivre'] ;
				$CheminEBook=$_POST['CheminEBook'];

				$HiddenToGetEx=$_POST['HiddenToGetExEb'] ;
				$ToUpdate=explode("?", $HiddenToGetEx) ;

				mysqli_query($this->ConnectionMysqli, "UPDATE EBook SET 
													IdLivre='".$IdLivre."',
													IdExemplaire='".$IdExemplaire."',
													DateEdition='".$DateEdition."',
													nbPages='".$nbPages."',
													LangueExemplaire='".$LangueExemplaire."',
													NomMaisonEdition='".$NomMaisonEdition."',
													CouvertureLivre='".$CouvertureLivre."',
													CheminEBook='".$CheminEBook."'
													WHERE IdLivre='".$ToUpdate[0]."' AND IdExemplaire='".$ToUpdate[1]."' ;") ;




			}

			if(isset($_POST['AddEBook']))
			{
				$IdLivre=$_POST['IdLivre'] ;
				$IdExemplaire=$_POST['IdExemplaire'] ;
				$DateE=$_POST['DateEdition'] ;
				$nbPages=$_POST['nbPages'] ;
				$LangueE=$_POST['LangueExemplaire'] ;
				$NomME=$_POST['NomMaisonEdition'] ;

				// EBook

				$NomEBook=$_FILES['UpEBook']['name'] ;
				$NomImg=$_FILES['CouvertureLivre']['name'] ;
				$SizeEBook=$_FILES['UpEBook']['size']/1024 ;
				$SizeImg=$_FILES['CouvertureLivre']['size']/1024 ;
				$TypeEBook=$_FILES['UpEBook']['type'] ;
				$TypeIMG=$_FILES['CouvertureLivre']['type'] ;
				$TmpNameEBook=$_FILES['UpEBook']['tmp_name'] ;
				$TmpNameIMG=$_FILES['CouvertureLivre']['tmp_name'] ;

				if($TypeEBook=="application/pdf" && $TypeIMG=="image/jpeg")
				{
					$NewNomEBook=$IdLivre.".pdf" ; 
					$NewNomIMG=$IdLivre.".jpg" ;
					$uploadPath="EBook/".$NewNomEBook ;
					$uploadPathIMG="Cover/".$NewNomIMG ;

					if(move_uploaded_file($TmpNameEBook, $uploadPath) && move_uploaded_file($TmpNameIMG, $uploadPathIMG))
					{
					
						mysqli_query($this->ConnectionMysqli, "INSERT INTO EBook VALUES ( 
													'".$IdLivre."',
													'".$IdExemplaire."',
													'".$DateE."',
													'".$nbPages."',
													'".$LangueE."',
													'".$NomME."',
													'".$uploadPathIMG."',
													'".$uploadPath."' ); ") or die(mysqli_error($this->ConnectionMysqli)) ;
					}

				else
				{
					echo "<script>alert('bip')</script> ;" ;
				}
			}
		}
	}


	 	public function EBook() //Fonction qui sert à trier les livres.
		{
			if(isset($_GET['page']))
			{
				$this->PageTri=$_GET['page'];
			}
			else
			{
				$this->PageTri=0;
			}

			switch($this->PageTri) 
			{
				case 'Genre' : 
					$this->Connection();
					$TriGenre=$this->ConnectionString->query("Select * from AuteurEBookGenre GROUP BY IdGenre ; ") ;
					$i=0;
					$j=0;
					while($Afficher=$TriGenre->fetch())
					{
						echo "<div class='MasterListGenre' id='MasterListeGenre".$i."' style='''background-image:url(".$Afficher['IdGenre'].".jpg)'>" .strtoupper($Afficher['IdGenre']);
						$TriLivre=$this->ConnectionString->query("Select * from AuteurEBookGenre where
								IdGenre='".$Afficher['IdGenre']."'; ") ;
						echo "</div><div class='SMasterListGenre' id='SMasterListGenre".$i."'>" ;
						while($AfficherSousListe=$TriLivre->fetch())
						{
							echo "<div id='DivGenreListLivre".$i."&".$j."' class='CDivGenreListLivre'><p class='GenreListLivre'><a href='PageEBook.php?page=Exemp?".$AfficherSousListe['IdLivre']."'>" .mb_strtoupper($AfficherSousListe['TitreLivre'], 'utf8'). 
								"<br/>" .strtoupper($AfficherSousListe['PrenomAuteur']). " " .strtoupper($AfficherSousListe['NomAuteur']). "</a></p><p class='ImgGenreListLivre'><img src='".$AfficherSousListe['CouvertureLivre']."' class='ImgGenreListLivrepng' height='70%'/></p></div>" ;


								echo
								"
								<script type='text/javascript'>
								var img".$i."a".$j."= document.createElement('img') ;
								img".$i."a".$j.".onload = function(){
									var colorThief = new ColorThief();
									var color = colorThief.getColor(img".$i."a".$j.");
									document.getElementById('DivGenreListLivre".$i."&".$j."').style.background = 'rgb(' + color + ')';
								};
									img".$i."a".$j.".src= '".$AfficherSousListe['CouvertureLivre']."' ;
								</script>" ;

								$j++;
						}
						echo "</div>";
						$i++; 
					}break;
				case 'Auteur' : 
					$this->Connection();
					$TriAuteur=$this->ConnectionString->query("Select A.* from Auteurs A, AuteurEBookGenre Eb where A.IdAuteur=Eb.IdAuteur group by IdAuteur;");
					$i=0;
					while($Afficher=$TriAuteur->fetch())
					{
						echo "<div class='MasterListAuteur' id='MasterListAuteur".$i."'><p><a href='PageEBook.php?page=Auteur?".$Afficher['PrenomAuteur']. "
									?" .$Afficher['NomAuteur']. "?".$Afficher['IdAuteur']."'>".strtoupper($Afficher['PrenomAuteur']). "
									 " .strtoupper($Afficher['NomAuteur']). "</a><img class='MasterListAuteurImg' src='".$Afficher['AuteurPhoto']."' height='100%'/></p>" ;
						echo "</div>								
								<script type='text/javascript'>
								var img".$i."= document.createElement('img') ;
								img".$i.".onload = function(){
									var colorThief = new ColorThief();
									var color = colorThief.getColor(img".$i.");
									document.getElementById('MasterListAuteur".$i."').style.background = 'rgb(' + color + ')';
								};
									img".$i.".src= '".$Afficher['AuteurPhoto']."' ;
								</script>" ;
								$i++;
					}break;
				case 'Pays' :
					$this->Connection();
					$TriPays=$this->ConnectionString->query("Select * from AuteurEBookGenre group by IdPays ;") ;
					$i=0;
					$j=0;
					while($Afficher=$TriPays->fetch())
					{
						echo "<div class='MasterListGenre' id='MasterListeGenre".$i."'>" .strtoupper($Afficher['IdPays']);
						$TriAuteur=$this->ConnectionString->query("Select * from Auteurs where
								IdPays='".$Afficher['IdPays']."'; ") ;
						echo "</div><div class='SMasterListGenre' id='SMasterListGenre".$i."'>" ;
						while($AfficherSousListe=$TriAuteur->fetch())
						{
							echo "<div id='DivGenreListLivre".$i."&".$j."' class='CDivGenreListLivre'><p class='GenreListLivre'><a href='PageEBook.php?page=Auteur?".$AfficherSousListe['PrenomAuteur']."?".$AfficherSousListe['NomAuteur']."?".$AfficherSousListe['IdAuteur']."'>" .mb_strtoupper($AfficherSousListe['PrenomAuteur'], 'utf8'). 
								"<br/>" .strtoupper($AfficherSousListe['NomAuteur']). "</a></p><p class='ImgGenreListLivre'><img src='".$AfficherSousListe['AuteurPhoto']."' class='ImgGenreListLivrepng' height='70%'/></p></div>" ;


								echo
								"
								<script type='text/javascript'>
								var img".$i."a".$j."= document.createElement('img') ;
								img".$i."a".$j.".onload = function(){
									var colorThief = new ColorThief();
									var color = colorThief.getColor(img".$i."a".$j.");
									document.getElementById('DivGenreListLivre".$i."&".$j."').style.background = 'rgb(' + color + ')';
								};
									img".$i."a".$j.".src= '".$AfficherSousListe['AuteurPhoto']."' ;
								</script>" ;

								$j++;
						}
						echo "</div>";
						$i++;					
					}break;
				case $this->PageTri :
					$this->Connection();
					$Seperator=explode("?", $this->PageTri) ;				
					if($Seperator[0]=='Exemp')
					{
						$LivreExemplaire=$this->ConnectionString->query("Select * from EBookExemplaire where IdLivre='".$Seperator[1]."' 
							Group by TitreLivre ; )") ;						
						
						while($Afficher=$LivreExemplaire->fetch())
						{

							echo "<div class='UniqueLivre'> 
									<div class='UniqueLivrePres'>
										<img class='UniqueLivreImg' src='".$Afficher['CouvertureLivre']."' height='50%' width='auto'>
									</div>
									<div class='UniqueLivreDetails'>	
										<h1 class='UniqueLivreTitle'>".$Afficher['TitreLivre']. "</h1>
										<form method='POST' class='UniqueLivreButton'>
											<input type='submit' name='ReserverExemp' value='Télécharger'>
										</form>";
										if(isset($_POST['ReserverExemp']) && $_SESSION!=null)
										{
											echo "<a class='DLUniqueLivreEb' target='_blank' href='".$Afficher['CheminEBook']."'> Télécharger </a>";
										}
										elseif (isset($_POST['ReserverExemp']) && $_SESSION==null) 
										{
											echo "<p class='UniqueLivreDesc'> Accès non autorisé : <a class='DLUniqueLivreEb' href='Inscription.php'> créer un compte ? </a>";
										}
										echo   "</p>
										<p class='UniqueLivreDesc'>" .$Afficher['ResumeLivre']."</p><br>
									</div>
								</div>" ;
						}
					}
					if($Seperator[0]=='Auteur')
					{
						echo "<script type='text/javascript' src='Js/AnimationAuteur.js'></script>";
						$Seperator=explode("?", $this->PageTri) ;
						$AuteurSlideShow=$this->ConnectionString->query("Select * from EBookExemplaire where IdAuteur='".$Seperator[3]."' group by TitreLivre order by TitreLivre; ") ;
						$AuteurResumeLivre=$this->ConnectionString->query("Select * from EBookExemplaire where IdAuteur='".$Seperator[3]."' group by TitreLivre order by TitreLivre; ") ;
						$AuteurPresentation=$this->ConnectionString->query("Select * from Auteurs where IdAuteur='".$Seperator[3]."' ;") ;
						echo "	<img src='Images/WhiteArrowL.png' class='Left' height='8%'' width='auto'>
								<img src='Images/WhiteArrowR.png' class='Right' height='8%' width='auto'>
								<div class='AuteurSlideShow'>" ;
						$i = 0;
						while ($Afficher=$AuteurSlideShow->fetch()) 
						{
							echo "
							<img class='ImagePresentation' src='".$Afficher['CouvertureLivre']."' height='80%' width=auto ID='SlideShow".$i."'> ";
							$i++;
						}
						$this->CompteurSlideShow=$i ;
						echo "	</div>
								<div id='ToggleResume'><input id='ToggleResumeButton' type='submit' value='Résumé'></div>
								<div id='AuteurResume'>" ;

						$r=0;
						while($Afficher=$AuteurResumeLivre->fetch())
						{
							echo "	
									<p class='ClassResumeLivre' id='ResumeLivre".$r."'>".$Afficher['TitreLivre']."<br><br>".$Afficher['ResumeLivre']. "<br><br><a class='DLUniqueLivreAut' href='PageEBook.php?page=Exemp?".$Afficher['IdLivre']."'> Réserver le livre. </a></p>";

							$r++;
						}

						echo "	</div>
								<div class='AuteurPresentation'>" ;
						while($Afficher=$AuteurPresentation->fetch())
						{
							echo "	<h1 id='AuteurPresentationTitle'>".$Afficher['PrenomAuteur']. " " .$Afficher['NomAuteur']. "</h1> <img id='AuteurPresentationPortrait' src='".$Afficher['AuteurPhoto']."'>
									<p id='AuteurPresentationBio'>" .$Afficher['Biographie']. "</p>
									</div>";
						}

					}break;
			}

			if(isset($_POST['SearchLivreButton']))
			{
				if($_POST['SearchBy']=='Nom' && $_POST['SearchLivre']!='')
				{
					$this->SearchLivre=$_POST['SearchLivre'] ; 
					$StringSearch=explode(" ", $this->SearchLivre) ;
					$this->Connection();
					$SearchLivreQuery=$this->ConnectionString->query("Select * from AuteurLivreGenre where 
						PrenomAuteur LIKE '%".$StringSearch[0]."%' 
						OR NomAuteur LIKE '%".$StringSearch[0]."%' GROUP BY IdAuteur ");
					while($Afficher=$SearchLivreQuery->fetch())
					{
						echo "<a href='PageEBook.php?page=Auteur?".$Afficher['PrenomAuteur']."?".$Afficher['NomAuteur']."?".$Afficher['IdAuteur']."'>" 
							.$Afficher['PrenomAuteur']. " " .$Afficher['NomAuteur']. "</a>" ;
					}
				}
				elseif($_POST['SearchBy']=='Livre' && $_POST['SearchLivre']!='')
				{
					$this->Connection();
					$this->SearchLivre=$_POST['SearchLivre'] ;
					$StringSearch=explode(" ", $this->SearchLivre) ;
					$SearchLivreQuery=$this->ConnectionString->query("Select * from AuteurLivreGenre where
						TitreLivre Like '%".$StringSearch[0]."%' ;");
					echo "Resultats : " ;
					while($Afficher=$SearchLivreQuery->fetch())
					{
						echo "<a href='PageEBook.php?page=Exemp?".$Afficher['IdLivre']."'>" .$Afficher['TitreLivre']. 
									"<br/> Auteur : " .$Afficher['PrenomAuteur']. " " .$Afficher['NomAuteur']. "<br>" ;
					}
				}
			}
			else
			{
				// Ajouter Message Erreur.
			}
		}

		public function Ecrireblog()
		{
			$this->Connection();
			$SelectLivre=$this->ConnectionString->query("Select IdLivre, TitreLivre from Livre") ;


			echo 
				"<br><br><form method='POST'>
					<textarea name='ContenuArticle'> Ecrire ici </textarea>
					<select name='TitreLivre'>" ;

					while($Display=$SelectLivre->fetch())
					{
						echo '<option>'.$Display['TitreLivre'].'/'.$Display['IdLivre'].'</option>' ;
					} 

					echo 
					"
					</select>
					<input type='submit' name='AddArticle'>
				</form>" ;

			if(isset($_POST['AddArticle']))
			{
				$ToExplode=explode('/', $_POST['TitreLivre']) ;
				$TitreArticle='Avez vous déjà lu '.$ToExplode[0].' ?' ;
				$ContenuArticle=nl2br($_POST['ContenuArticle']) ;
				$IdAdherent=$_SESSION['id'] ;

				mysqli_query($this->ConnectionMysqli, 'INSERT INTO BlogArticle 
														(DateArticle, TitreArticle, ContenuArticle, IdAdherent, IdLivre) VALUES (
														"null", "'.$TitreArticle.'", "'.$ContenuArticle.'", "'.$IdAdherent.'", "'.$ToExplode[1].'") ;') or die(mysqli_error($this->ConnectionMysqli)) ;
			}
		}
	}

?>