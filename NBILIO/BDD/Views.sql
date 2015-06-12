CREATE View AuteurLivreGenre
AS SELECT A.IdAuteur, PrenomAuteur, NomAuteur, TitreLivre, IdPays, IdGenre, L.IdLivre, Ex.CouvertureLivre
FROM Auteurs A, Ecrire E, Livre L, GenreLivre G, Exemplaire Ex
WHERE A.IdAuteur=E.IdAuteur
AND E.IdLivre=L.IdLivre
AND L.IdLivre=G.IdLivre
AND Ex.IdLivre=L.IdLivre 
GROUP BY IdLivre;

CREATE View AuteurEBookGenre
AS SELECT A.IdAuteur, PrenomAuteur, NomAuteur, TitreLivre, IdPays, IdGenre, L.IdLivre, Ex.CouvertureLivre, Ex.CheminEBook
FROM Auteurs A, Ecrire E, Livre L, GenreLivre G, EBook Ex
WHERE A.IdAuteur=E.IdAuteur
AND E.IdLivre=L.IdLivre
AND L.IdLivre=G.IdLivre
AND Ex.IdLivre=L.IdLivre 
GROUP BY IdLivre;

CREATE view LivreEtGenre
AS SELECT IdGenre, L.IdLivre, TitreLivre 
FROM GenreLivre G, Livre L 
WHERE L.IdLivre=G.IdLivre
ORDER BY IdGenre ;

DROP VIEW IF EXISTS LivreExemplaire;

Create view LivreExemplaire
AS SELECT A.IdAuteur, NomAuteur, PrenomAuteur, E.IdLivre, CouvertureLivre, 
	nbPages, TitreLivre, AnneeEcriture, ResumeLivre
FROM Exemplaire E, Livre L, Ecrire Ec, Auteurs A
WHERE E.IdLivre=L.IdLivre
AND Ec.IdLivre=L.IdLivre
AND A.IdAuteur=Ec.IdAuteur 
GROUP BY IdLivre;

Create view EBookExemplaire
AS SELECT A.IdAuteur, NomAuteur, PrenomAuteur, E.IdLivre, CouvertureLivre, 
	nbPages, TitreLivre, AnneeEcriture, ResumeLivre, E.CheminEBook
FROM EBook E, Livre L, Ecrire Ec, Auteurs A
WHERE E.IdLivre=L.IdLivre
AND Ec.IdLivre=L.IdLivre
AND A.IdAuteur=Ec.IdAuteur 
GROUP BY IdLivre;

DROP VIEW IF EXISTS AdherentEmprunt;

CREATE VIEW AdherentEmprunt
AS SELECT R.IdLivre, R.IdExemplaire, R.IdAdherent, R.IdEmprunt, DateEmprunt, DateRetour, DateRetourPrevu, A.Login, L.TitreLivre
FROM RetourEmprunt R, Adherent A, Exemplaire E, Livre L 
where R.IdAdherent=A.IDAdherent
AND R.IdLivre=E.IdLivre
AND L.IdLivre=E.IdLivre
GROUP BY IdEmprunt ;

DROP VIEW IF EXISTS ExemplaireDispo ;


CREATE VIEW ExemplaireDispo AS 
SELECT IdLivre, group_concat(DISTINCT IdExemplaire SEPARATOR '/') as Dispo, count(IdExemplaire) as NbDispo, MIN(IdExemplaire) as Reserver
FROM Exemplaire
WHERE concat(IdLivre, ':', IdExemplaire) NOT IN 
(SELECT concat(IdLivre, ':', IdExemplaire) as NoDisp
FROM RetourEmprunt
WHERE DateRetour IS NULL)
AND IdExemplaire!=1
GROUP BY IdLivre;


Create view BlogCover
AS SELECT B.TitreArticle, B.ContenuArticle, B.DateArticle, E.CouvertureLivre, A.PrenomAuteur, A.NomAuteur, A.IdAuteur, L.IdLivre, L.TitreLivre, B.IdArticle
FROM BlogArticle B, Livre L, Exemplaire E, Auteurs A, Ecrire Ec
WHERE B.IdLivre=L.IdLivre
AND L.IdLivre=E.IdLivre
AND A.IdAuteur=Ec.IdAuteur
AND Ec.IdLivre=L.IdLivre
Group By IdArticle;


DROP PROCEDURE IF EXISTS AdherentLitigieux ;

DELIMITER //

CREATE PROCEDURE AdherentLitigieux()
BEGIN
DECLARE FINI int default 0;
DECLARE IdA int(5) ;
DECLARE IdE int(5) ;
DECLARE DateE date ;
DECLARE CurEmp CURSOR for
Select R.IdAdherent, R.IdEmprunt, R.DateRetourPrevu 
FROM RetourEmprunt R
WHERE R.DateRetourPrevu < Curdate()
AND R.DateRetour IS NULL
AND EtePris!='Non';
DECLARE Continue HANDLER FOR NOT FOUND SET fini=1 ;
Open CurEmp ; 
FETCH CurEmp into
IdA, IdE, DateE ; 
While fini !=1
DO 
	UPDATE Adherent
	SET Litigieux='Oui', DateLitige=AddDate(DateE, 30)
	where IdAdherent = IdA ;
	FETCH CurEmp into IdA, IdE, DateE ;
END WHILE ;
Close CurEmp ;
END //
DELIMITER ;


DROP PROCEDURE IF EXISTS DeleteAdherent ;

DELIMITER //

CREATE PROCEDURE DeleteAdherent()
BEGIN
DECLARE FINI int default 0;
DECLARE IdA int(5) ;
DECLARE CurEmp CURSOR for
Select IdAdherent
FROM Adherent
WHERE Datediff(CURDATE(), DateLitige) > 30
AND Litigieux='Oui';
DECLARE Continue HANDLER FOR NOT FOUND SET fini=1 ;
Open CurEmp ; 
FETCH CurEmp into
IdA ;
While fini !=1
DO 
	DELETE FROM Adherent
	where IdAdherent = IdA ;
	FETCH CurEmp into IdA ;
END WHILE ;
Close CurEmp ;
END //
DELIMITER ;

/*DROP TRIGGER IF EXISTS EXCLURE_ADHERENT ;

DELIMITER //
CREATE TRIGGER EXCLURE_ADHERENT
BEFORE DELETE ON ADHERENT
FOR EACH ROW
BEGIN
INSERT INTO ADHERENTEXCLUT SELECT * , CURDATE() , USER() FROM ADHERENT WHERE IDADHERENT=OLD.IDADHERENT;
DELETE FROM RetourEmprunt WHERE IdAdherent=OLD.IdAdherent ;
INSERT INTO ARCHIVE_EMPRUNTS SELECT * FROM RetourEmprunt WHERE IdAdherent=OLD.IdAdherent ;
END //
DELIMITER ; */

 
DROP TRIGGER IF EXISTS EMPRUNTS_ADHERENT_EXCLU ; 

DELIMITER //
CREATE TRIGGER EMPRUNTS_ADHERENT_EXCLU
BEFORE DELETE ON ADHERENT
FOR EACH ROW
BEGIN
INSERT INTO ADHERENTEXCLUT SELECT * , CURDATE() , USER() FROM ADHERENT WHERE IDADHERENT=OLD.IDADHERENT;
INSERT INTO ARCHIVE_EMPRUNTS SELECT * , CURDATE() , USER() FROM RetourEmprunt WHERE IdAdherent=OLD.IdAdherent AND EtePris!='Non' ;
INSERT INTO ExemplairePerdu SELECT *, CURDATE(), USER() from Exemplaire 
	where concat(IdLivre, ':', IdExemplaire)=
		(Select concat(IdLivre, ':', IdExemplaire) 
		FROM RetourEmprunt
		WHERE DateRetour is null 
		AND IDAdherent=OLD.IdAdherent
		AND EtePris!='Non') ;
DELETE FROM RetourEmprunt WHERE IdAdherent=OLD.IdAdherent ;
DELETE FROM Exemplaire WHERE concat(IdLivre, ':', IdExemplaire) IN (SELECT CONCAT(IdLivre, ':', IdExemplaire) FROM ExemplairePerdu) ;
END //
DELIMITER ;

ALTER DATABASE BDDBibliotheque charset=utf8;
insert into Pays Values
	("France"),
	("Royaume-Uni"),
	("Italie"),
	("Espagne"),
	("Allemagne"),
	("Etats-Unis") ;

insert into Auteurs Values
	(1, "Bradbury", "Ray", null, null, "Etats-Unis", 
		"Raymond Douglas « Ray » Bradbury, né le 22 août 1920 à Waukegan dans l'Illinois et mort le 5 juin 2012 (à 91 ans) à Los Angeles en Californie, est un écrivain américain, référence du genre de l'anticipation. Il est particulièrement connu pour ses Chroniques martiennes, écrites en 1950, L'Homme illustré, recueil de nouvelles publié en 1951, et surtout Fahrenheit 451, roman dystopique publié en 1953.", "Auteurs/RayBradbury.jpg"),

	(2, "Easton", "Bret", null, null, "Etats-Unis", 
		"Bret Easton Ellis, né le 7 mars 1964 à Los Angeles, est un écrivain américain. C'est l'un des auteurs principaux du mouvement Génération X et on le classe parfois parmi les romanciers d'anticipation sociale. Il se considère comme un moraliste, bien que certains voient en lui un nihiliste. Ses personnages sont souvent jeunes, dépravés et vains, mais ils en sont conscients et l'assument. Ellis situe ses romans dans les années 1980, faisant du mercantilisme et de l'industrie du divertissement de cette décennie un symbole. Ses Livres, des contre-utopies (autrement dit des dystopies) qui se déroulent souvent dans des métropoles américaines (comme Los Angeles et New York), sont peuplés de personnages récurrents.", "Auteurs/BretEaston.jpg"),
	(3, "Zola", "Emile", null, null, "France", 
		"Émile Zola (à l'état civil Émile Édouard Charles Antoine Zola) est un écrivain et journaliste français, né à Paris le 2 avril 1840 et mort dans la même ville le 29 septembre 1902. Considéré comme le chef de file du naturalisme, c'est l'un des romanciers français les plus populaires2, les plus publiés, traduits et commentés au monde. Ses romans ont connu de très nombreuses adaptations au cinéma et à la télévisionN 1.<br/>
		Sa vie et son œuvre ont fait l'objet de nombreuses études historiques. Sur le plan littéraire, il est principalement connu pour Les Rougon-Macquart, fresque romanesque en vingt volumes dépeignant la société française sous le Second Empire et qui met en scène la trajectoire de la famille des Rougon-Macquart, à travers ses différentes générations et dont chacun des représentants d'une époque et d'une génération particulière fait l'objet d'un roman.<br/>
		Les dernières années de sa vie sont marquées par son engagement dans l'affaire Dreyfus avec la publication en janvier 1898, dans le quotidien L'Aurore, de l'article intitulé « J'accuse » qui lui a valu un procès pour diffamation et un exil à Londres dans la même année.", "Auteurs/EmileZola.jpg"),
	(4, "King", "Stephen", null, null, "Etats-Unis", 
		"Stephen Edwin King, plus connu sous le nom de Stephen King, est un écrivain américain né le 21 septembre 1947 à Portland, dans le Maine (États-Unis).<br/>
		Il a publié son premier roman en 1974 et est rapidement devenu célèbre pour ses contributions dans le domaine de l'horreur mais a également écrit des Livres relevant d'autres genres comme le fantastique, la fantasy, la science-fiction et le roman policier. Tout au long de sa carrière, il a écrit et publié plus de cinquante romans, dont sept sous le pseudonyme de Richard Bachman, et environ deux cents nouvelles, dont plus de la moitié sont réunies dans neuf recueils de nouvelles. Depuis son grave accident survenu en 1999, il a ralenti son rythme d'écriture. Ses Livres ont été vendus à plus de 350 millions d'exemplaires à travers le monde1 et il a établi de nouveaux records de ventes dans le domaine de l'édition durant les années 1980, décennie où sa popularité a atteint des sommets.<br/>", "Auteurs/StephenKing.jpg"),
	(5, "Lewis", "Carol", null, null, "Royaume-Uni", null, "Auteurs/CarolLewis.jpg");


Insert into Livre values 
("LBSK", "La brume", null, "Un Livre qui fait peur");

insert into Livre values 
	("APBE", "American Psycho", null, "Resume..."),
	("SDoSK", "Sac d'os", null, "Resume"),
	("LaEZ", "L'assommoir", null, "Resume"),
	("LhIRB", "L'homme Illustré", null, "Resume"),
	("AAPDMCL", "Alice au pays des merveilles", null, "Resume");

insert into Ecrire values 
("LBSK", 4) ;

insert into Ecrire Values
	("APBE", 2),
	("SDoSK", 4),
	("LaEZ", 3),
	("LhIRB", 1),
	("AAPDMCL", 5);

insert into Livre values 
	("TShiSK", "The Shining", null, "Resume..."),
	("TStaSK", "The Stand", null, "Resume..."),
	("CujoSK", "Cujo", null, "Resume..."),
	("TDZSK", "The Dead Zone", null, "Resume...") ;

insert into Ecrire values
	("TShiSK", 4),
	("TStaSK", 4),
	("CujoSK", 4),
	("TDZSK", 4) ;

insert into Genre Values
	("Horreur"),
	("Fantastique"),
	("Policier"),
	("Drame"),
	("Enfant");

insert into GenreLivre values
	("Horreur", "TShiSK"),
	("Horreur", "TStaSK"),
	("Horreur", "CujoSK"),
	("Horreur", "TDZSK"),
	("Enfant", "AAPDMCL");

insert into GenreLivre values
	("Horreur" , "LBSK") ;

Insert into GenreLivre values
	("Horreur", "SDoSK"),
	("Fantastique", "LhIRB"),
	("Drame", "APBE"),
	("Drame" ,"LaEZ");

insert into Edition Values	
	("Gallimard"),
	("Hachette"),
	("Simon & Schuster/us")	;

insert into Exemplaire values
	("LBSK", 1, null, null, 150, "Français", "Gallimard", "Cover/LBSK.jpg"),
	("LBSK", 2, null, null, 150, "Français", "Gallimard", "Cover/LBSK.jpg"),
	("SsLSK", 1, null, null, 460, "Anglais", "Simon & Schuster/us", "Cover/SsLSK.jpg"),
	("SsLSK", 2, null, null, 460, "Anglais", "Simon & Schuster/us", "Cover/SsLSK.jpg"),
	("SsLSK", 3, null, null, 460, "Anglais", "Simon & Schuster/us", "Cover/SsLSK.jpg");

insert into Exemplaire values
	("TShiSK", 1, null, null, 300, "Français", "Gallimard", "Cover/TShiSK.jpg"),
	("TShiSK", 2, null, null, 300, "Français", "Gallimard", "Cover/TShiSK.jpg"),
	("TStaSK", 1, null, null, 400, "Français", "Gallimard", "Cover/TStaSK.jpg"),
	("TStaSK", 2, null, null, 400, "Français", "Gallimard", "Cover/TStaSK.jpg"),
	("CujoSK", 1, null, null, 250, "Français", "Gallimard", "Cover/CujoSK.jpg"),
	("CujoSK", 2, null, null, 250, "Français", "Gallimard", "Cover/CujoSK.jpg"),
	("TDZSK", 1, null, null, 500, "Français", "Gallimard", "Cover/TDZSK.jpg"),
	("TDZSK", 2, null, null, 500, "Français", "Gallimard", "Cover/TDZSK.jpg") ; 
 
insert into Exemplaire values 
	("SDoSK", 1, null, null, 300, "Francais", "Gallimard", null),
	("SDoSK", 2, null, null, 300, "Francais", "Gallimard", null),
	("SDoSK", 3, null, null, 300, "Francais", "Gallimard", null),
	("SDoSK", 4, null, null, 300, "Francais", "Gallimard", null),
	
	("LhIRB", 1, null, null, 120, "Francais", "Hachette", null),
	("LhIRB", 2, null, null, 120, "Francais", "Hachette", null),
	("LhIRB", 3, null, null, 120, "Francais", "Hachette", null),

	("APBE", 1, null, null, 500, "Anglais", "Gallimard", null),
	("APBE", 2, null, null, 500, "Anglais", "Gallimard", null),
	("APBE", 3, null, null, 500, "Anglais", "Gallimard", null),

	("LaEZ", 1, null, null, 700, "Francais", "Hachette", null),
	("LaEZ", 2, null, null, 700, "Francais", "Hachette", null),
	("LaEZ", 3, null, null, 700, "Francais", "Hachette", null),
	("LaEZ", 4, null, null, 700, "Francais", "Hachette", null);

insert into EBook values 
	("AAPDMCL", "1", curdate(), "300", "Français", "Hachette", "Cover/AAPDMCL.jpg", "EBook/AAPDMCL.pdf");


UPDATE Exemplaire 
SET CouvertureLivre="Cover/LhIRB.jpg"
where IdLivre="LhIRB" ;

UPDATE Exemplaire 
SET CouvertureLivre="Cover/SDoSK.jpg"
where IdLivre="SDoSK" ;

UPDATE Exemplaire
SET CouvertureLivre="Cover/APBE.jpg"
where IdLivre="APBE";

UPDATE Exemplaire
SET CouvertureLivre="Cover/LaEZ.jpg"
where IdLivre="LaEZ" ;

insert into Adherent values 
	(1, "Rottenberg", "Nicolas", null, "Paris", "Rue rue", "75000", "Rttb@hotmail.fr", "0300000000", "NicolasR", "Motdepasse", 'Non', null, 'Non'),
	(2, "Lucien", "Pierre", null, "Paris", "21 avenue Victor Hugo", "75116", "Lucien.pierred@gmail.com", "0629327492", "LucienP", "Motdepasse", 'Non', null, 'Oui') ;
insert into Adherent values 
	(3, "aaaaa", "aaaaa", null, "Paris", "Rue rue", "75000", "R@hotmail.fr", "0300000000", "TEST", "Motdepasse", 'Non', null, 'Non') ;


insert into RetourEmprunt values
	("LhIRB", 2, 2, 1,"2014-10-27","2014-10-27", "2014-10-27", 'Oui'),
	("SDoSK", 2, 2, 2,"2014-10-27","2014-10-27", "2014-10-27", 'Oui'),
	("SDoSK", 2, 2, 3,"2014-10-27","2014-10-27", "2015-01-22", 'Oui');

insert into RetourEmprunt values 
	('CujoSK', 2, 3, 4,"2014-10-27","2014-10-27", null, 'Oui'),
	('LhIRB', 2, 2, 5, "2014-10-27", "2014-10-27", "2014-10-27", 'Non') ;

insert into BlogArticle values
(1, "2015-01-01", "Avez vous lu Cujo ?", "Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna.", 1, "CujoSK"),
(2, "2015-01-02", "Avous vous lu Sac-d'os ?", "Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna.", 2, "SDoSK") ;


update Livre
	set resumeLivre="L'auteur nous raconte l'histoire de Patrick Bateman, 27 ans, un flamboyant golden boy de Wall Street. Patrick est beau, riche et intelligent, comme tous ses amis.Il fréquente les restaurants les plus chics, où il est impossible d'obtenir une réservation si l'on n'est pas quelqu'un, va dans les boîtes branchées et sniffe de temps en temps une ligne de coke, comme tout bon yuppie.
	<br>Mais Patrick a une petite particularité : c'est un psychopathe ou un schizophrène qui s'imagine psychopathe. À l'abri dans son appartement hors de prix, au milieu de ses gadgets dernier cri et de ses meubles en matériaux précieux, il tue, décapite, égorge, viole. Sa haine des animaux, des pauvres, des étrangers, des homosexuels et des femmes est illimitée et son humour froid est la seule trace d'humanité que l'on puisse lui trouver.
	<br>Le roman décrit de manière très progressive la véritable nature de Patrick Bateman, sans jamais vraiment s'attarder sur les événements à l'origine de ses pulsions destructrices. On sent la folie du personnage augmenter de manière exponentielle au fil des pages : le récit raconté à la première personne accumule de plus en plus d'hallucinations, d'incohérences, de passages de délire pur et l'on peut sérieusement se demander, à la fin du Livre, si Patrick Bateman ne vit pas uniquement ses meurtres dans sa tête."
	where IdLivre='APBE';
	
	update Livre
	set resumeLivre="Joe Camber, le seul garagiste de Castle Rock, est un homme assez bourru qui vit avec sa femme Charity, enfermée dans les hauts mais surtout les bas de la vie avec son mari, et leur fils Brett, un garçon de dix ans qui a pour meilleur ami un Saint-Bernard d'environ 120 kg : Cujo. L'animal est doux et affectueux jusqu'au jour où, chassant un lapin, il tombe dans une caverne où une chauve-souris lui inocule le virus de la rage. Charity part avec Brett rendre visite à sa sœur, laissant son mari seul avec Cujo. Peu après, le chien tue Gary Pervier, le voisin des Camber, ainsi que Joe Camber, quand celui-ci découvre le corps de son voisin.
	<br>Vic et Donna Trenton forment de leur côté un couple en crise car Vic a découvert que sa femme avait une liaison, et Tad, leur garçon de cinq ans, souffre de terreurs nocturnes (le monstre du placard). Alors que Vic s'est absenté pour son travail, Donna part avec Tad faire réparer sa vieille Ford Pinto mais le véhicule tombe en panne alors qu'ils atteignent la ferme des Camber. Cujo commence alors à faire patiemment le siège de la voiture, et Donna est mordue à deux reprises alors qu'elle tente de s'enfuir, réussissant néanmoins à regagner sa voiture. Donna et Tad restent enfermés dans la Pinto pendant trois jours, souffrant de la faim et de la soif mais n'osant essayer de s'échapper.
	<br>Vic alerte la police car il n'arrive pas à joindre son épouse et le shérif Bannerman finit par se rendre à la ferme de Camber, où il est à son tour tué par Cujo. Alors que Tad est de plus en plus faible, Donna décide finalement de sortir du véhicule et attaque le Saint-Bernard avec une batte de baseball, réussissant à le tuer. Vic arrive à son tour chez les Camber et découvre sa femme s'acharnant sur le corps du chien ainsi que son fils mort de déshydratation sur le siège arrière. Charity Camber apprend avec un certain plaisir la mort de son mari abusif et achète un nouveau chien à Brett alors que Vic et Donna tentent de surmonter la perte de leur fils."
	where IdLivre='CujoSK';
	
	update Livre
	set resumeLivre="Gervaise, la fille d'Antoine Macquart, a, à vingt-deux ans, fui Plassans avec son amant, Auguste Lantier, un ouvrier chapelier, et leurs deux enfants, Claude, le futur peintre de L’oeuvre, et Étienne le futur héros de Germinal. À Paris, ils habitent un hôtel meublé misérable dans le quartier populaire de la Goutte-d’Or. Lantier abandonne vite la jeune femme, emportant tout ce qui reste de leurs maigres économies.<br>
    Jolie, courageuse, dure à la peine, elle travaille comme blanchisseuse. Elle rencontre puis épouse l’ouvrier zingueur Coupeau. À force de travail, le couple atteint une certaine aisance et se dispose à louer une petite boutique. Leur bonheur et leur prospérité sont concrétisés par la naissance de leur fille, Anna, dite Nana. Elle célèbre son succès en organisant une grande fête (évoquée dans le chapitre central) à laquelle participe tout le quartier.<br>
    Mais le bonheur est de courte durée. Coupeau, en voulant regarder son enfant du toit sur lequel il travaille, fait une chute et se casse la jambe. Pour lui éviter l'hôpital, Gervaise le soigne chez elle, dépense les économies du ménage. Il prend son métier en aversion et, pour tromper l’ennui de sa convalescence, il se met à fréquenter L’assommoir, cabaret où trône l’alambic. Gervaise, cependant, grâce à son voisin, le forgeron Goujet qui l’aime d'un amour chaste, peut réaliser son rêve : acheter une blanchisserie, qui est très vite prospère grâce à son activité et à son esprit avisé. Mais Coupeau a peur désormais de monter sur les toits et ne travaille plus régulièrement. Il  consomme au cabaret tout ce qu’il gagne, boit de plus en plus et sombre inéluctablement dans l’ivrognerie et la brutalité.<br>
    Lantier revient et finit s'installer chez le couple. Les deux hommes vivent du travail de la jeune femme qui se laisse aller à la gourmandise et à la paresse. Sa déchéance morale s'accompagne d'une terrible déchéance physique. Un jour, Gervaise, qui a attendu Coupeau en vain, va le chercher à L’assommoir où il boit sa paie avec d’autres ivrognes. Elle-même prend une anisette puis un verre du vitriol que secrète l’alambic. Gervaise commence alors à se porter vers l'alcool, adopte des habitudes de paresse et d’inconduite, néglige son travail.
    Le couple est lentement entraîné vers la chute, sans la moindre compassion du voisinage. Ils sont obligés de céder leur boutique et d’emménager dans un taudis. Coupeau, qui perd progressivement la raison, est enfermé à Sainte-Anne dans une cellule capitonnée. Gervaise doit abandonner sa belle boutique pour aller habiter parmi les pauvres d'une grande maison ouvrière. Devant elle, Coupeau est pris d’une terrible crise de delirium tremens, et meurt dans d’atroces souffrances. Réduite à la mendicité, Gervaise succède au père Bru, qui vivait dans une niche sous l’escalier. Elle connaît la déchéance finale en se prostituant dans la rue, où elle est trouvée morte de faim et de misère."
	where IdLivre='LaEZ';
	
	update Livre
	set resumeLivre=" Il retira sa chemise et la roula en boule.
    De l'anneau bleu tatoué autour de son cou jusqu'à la taille, il était couvert d'illustrations. ‘Et c'est comme ça jusqu'en bas’, précisa-t-il, devinant ma pensée. ‘Je suis entièrement illustré. Regardez !’ Il ouvrit la main. Sur sa paume, une rose. Elle venait d'être coupée ; des gouttelettes cristallines émaillaient ses pétales délicats. J'étendis ma main pour la toucher, mais ce n'était qu'une image. ‘Mais elles sont magnifiques ! m'écriai-je.
    - Oh oui, dit l'Homme Illustré. Je suis si fier de mes Illustrations que j'aimerais les effacer en les brûlant. J'ai essayé le papier de verre, l'acide, le couteau. Car, voyez-vous, ces Illustrations prédisent l'avenir. ‘ 
    Dix-huit Illustrations, dix-huit histoires à fleur de peau par l'un des plus grands poètes du fantastique et de la science-fiction."
	where IdLivre='LhIRB';
	
	update Livre
	set resumeLivre="Mike Noonan, écrivain à succès originaire de la ville de Derry, souffre du blocage de l'écrivain à la suite de la mort de sa femme Johanna d'une rupture d'anévrisme, quatre ans a
	uparavant. Il fait également des cauchemars qui concernent sa résidence secondaire, Sara Laughs, nommée ainsi d'après Sara Tidwell, une chanteuse de blues afro-américaine du début du XXe siècle, et décide de s'y rendre pour l
	'été alors qu'il n'y est plus retourné depuis la mort de sa femme. À peine installé, il découvre que Johanna avait fait à son insu plusieurs visites à Sara Laughs. Il fait aussi la connaissance de Mattie, une jeune femme veuv
	e depuis peu, et de sa fille de trois ans, Kyra. Mattie est la belle-fille de Max Devory, l'homme le plus riche et le plus influent de la région, et elle se bat pour conserver la garde de sa fille, que Devory voudrait récupér
	er.

	Agacé par l'agressivité de Max Devory à son encontre, Mike décide de venir en aide à la jeune femme et engage John Storrow, un jeune et brillant avocat de New York, pour s'occuper de l'affaire. Il est aussi très vite confront
	é à des phénomènes surnaturels : des murmures et des pleurs dans la maison et les lettres aimantées de son frigo qui lui laissent des messages. Il comprend que l'esprit de Johanna hante la maison, et qu'elle tente de communiq
	uer avec lui, mais qu'elle n'est pas la seule, Sara Tidwell aussi hantant les lieux. Mike devient également de plus en plus proche de Mattie et de Kyra. Un soir, il rencontre Max Devory et son assistante, Rogette Whitmore, et
	 tous deux manquent le tuer. Mike apprend le lendemain que Devory s'est suicidé un peu plus tard dans la nuit. Il découvre aussi que Johanna menait des recherches sur Sara Tidwell et son groupe, qui avaient brusquement quitté
	 la région sans laisser de traces, et met le doigt sur un secret caché depuis plusieurs décennies par différentes familles de la région concernant la mort violente de plusieurs enfants.

	Puisque la mort de Devory clot la bataille juridique pour la garde de Kyra, Mike, Mattie et Storrow, ainsi qu'un autre avocat et un détective privé qui étaient également sur l'affaire, décident de fêter l'évènement. Mais, le
	barbecue qu'ils organisent se termine tragiquement quand un homme payé par Devory tire sur le groupe, tuant Mattie avant d'être arrêté. Alors qu'une violente tempête se lève, Mike emmène Kyra à Sara Laughs. Sous l'influence d
	e l'esprit de Sara Tidwell, il commence à faire des préparatifs pour noyer la petite fille, mais est tiré de son état second par l'esprit de Johanna.

	Mike découvre alors que Sara Tidwell a été violée et assassinée par des habitants de la région (dont un ancêtre de Devory et un des siens) qui ont également tué son fils. Depuis lors, l'esprit de Sara a poussé des descendants
	 de ses meurtriers à assassiner certains de leurs enfants. Pour que l'esprit de Sara repose enfin en paix, il doit détruire ses ossements enterrés non loin, et il parvient finalement à accomplir cette tâche grâce à l'interven
	tion de l'esprit de Johanna. Mais, pendant ce temps, Rogette Whitmore, qui se révèle être en fait la fille de Max Devory, a enlevé Kyra, et Mike se lance à sa poursuite. Il finit par la rattraper et, à l'issue d'un combat au
	milieu des éléments déchaînés, Rogette disparaît dans le lac. Six mois plus tard, l'épilogue montre que Mike n'arrive toujours pas à écrire mais que sa bataille juridique pour adopter Kyra semble en bonne voie malgré la lente."
	where IdLivre='SDoSK';
	
	update Livre
	set resumeLivre="Jerusalem's Lot (Salem), est une petite bourgade du Maine sans rien de particulier si ce n'est, sur la colline, Marsten House, une grande demeure inhabitée depuis le suicide de s
	on occupant, un homme étrange et réputé tueur, Hubert Marsten. L'écrivain Ben Mears, témoin enfant du suicide d'Hubert Marsten, revient sur les lieux de son enfance dans le but d'écrire un Livre sur la maison. Il veut alors l
	'acquérir, mais découvre qu'elle a été récemment rachetée par deux antiquaires, Straker et Barlow, dont la boutique vient d'ouvrir en ville (seul Straker a été vu jusqu'à présent). Ben commence à nouer une relation sentimenta
	le avec la jeune Susan Norton dont la mère a entendu les ragots circulant sur l'écrivain. Il se lie également d'amitié avec Matt Burke, un professeur de lycée. C'est alors que le jeune Ralphie Glick, parti un soir avec son fr
	ère, disparaît. Malgré les battues effectuées sans relâche par les habitants, il ne peut être retrouvé et bientôt son frère aîné, Danny, tombe gravement malade, et meurt sans que sa maladie ne puisse être diagnostiquée.

	Peu après l'enterrement de Danny, Matt Burke recueille Mike Ryerson, un ancien élève qui travaillait comme fossoyeur lors de la cérémonie. Très mal en point, Mike semble également gravement choqué, si bien que Matt lui propos
	e de dormir chez lui. Pendant la nuit, il entend pourtant Mike inviter une personne à entrer, et au matin, le retrouve mort à son tour. La nuit suivante, Matt est attaqué par Ryerson et, bien qu'arrivant à le repousser, fait
	une attaque cardiaque. Il est emmené à l'hôpital, tandis que Ben est agressé par l'ancien ami de Susan, Floyd Tibbits, dissimulé sous de lourds vêtements d'hiver en dépit de la chaleur. Les deux amis se persuadent alors que d
	es vampires sont la cause des morts étranges qui frappent la ville, et que Straker et Barlow pourraient en être l'origine. Mark Petrie, un jeune garçon de 12 ans, a de son côté l'horrible surprise de voir Danny Glick frapper
	une nuit à sa fenêtre, et lui demander de le faire entrer. Saisissant une petite croix, il trouve le courage de l'appliquer sur la joue du vampire ce qui fait reculer celui-ci, et sauve Mark du même coup.

	Alors que de plus en plus d'habitants de la ville sont infectés, Mark décide de se rendre à Marsten House et rencontre Susan, qui a eu la même idée. Mais ils sont tous les deux capturés par Straker, qui ligote Mark et offre S
	usan à son maître. Mark parvient cependant à assommer Straker et à s'enfuir sans pouvoir empêcher la transformation de Susan en vampire. Il prévient Ben Mears de ce qui est arrivé, et fait appel au père Callahan pour qu'il le
	ur apporte l'aide de l'Église. Ben et son groupe se rendent alors à Marsten House dans le but de tuer Barlow. Ils arrivent trop tard pour lui, mais Ben se voit à la place contraint de planter un pieu dans le cour de Susan. Fu
	rieux de la mort de son serviteur Straker, Barlow se venge le soir même en tuant les parents de Mark devant ses yeux, et remporte la lutte de pouvoir qui l'oppose au père Callahan. Il fait alors boire son sang au prêtre, qui
	souillé, ne peut plus pénétrer dans son église, et fuit la ville.

	Le petit groupe de résistants recherche alors la nouvelle cachette diurne de Barlow, mais Jim Cody, le médecin témoin de la mort et de la résurrection de plusieurs cadavres, tombe dans un piège élaboré par le vampire et meurt
	 empalé sur des couteaux. Presque au même moment, à l'hôpital, Matt succombe à une nouvelle attaque cardiaque. Ben et Mark, désormais seuls survivants du groupe, parviennent à dénicher le repaire de Barlow sous la pension de
	famille où résidait Ben, et à le tuer in extremis avant le coucher du soleil. Désormais infestée par les vampires, la ville de Salem est perdue, et les deux survivants n'ont d'autres choix que de partir pour le Mexique. Un an
	 passe avant qu'ils ne retournent à Salem, devenue entre-temps une ville fantôme. Ils incendient alors la ville et privent du même coup les vampires de leurs refuges."
		where IdLivre='SsLSK';
	
	update Livre
	set resumeLivre="En 1970, John Smith, professeur ordinaire, accompagne Sarah, son amoureuse, à une fête foraine. Jouant à la loterie, il gagne plusieurs fois d'affilée, profitant d'une série d'in
	tuitions qui lui arrivent quelquefois depuis qu'il est tombé sur la tête quand il était enfant. Le même soir, après avoir raccompagné Sarah, il est victime d'un accident de la route qui va le faire sombrer dans le coma, dont
	il ne se réveille que presque cinq ans plus tard.

	À son réveil, Johnny découvre que son don s'est considérablement accru et qu'il peut désormais voir l'avenir ou le passé d'une personne en la touchant ou en touchant un objet lié à elle. Son médecin, le Dr Weizak, pense qu'un
	e zone du cerveau dont personne ne se sert, une zone morte, s'est activée chez lui. Johnny apprend également que Sarah s'est mariée entretemps et a eu un enfant mais, en la voyant, il comprend qu'elle ne l'a jamais vraiment o
	ublié. Suite à une vision de Johnny qui permet de sauver un enfant d'un incendie, la rumeur à propos de son pouvoir commence à se répandre et il fait l'attention de la presse. Peu après survient la mort de sa mère, femme très
	 religieuse voire fanatique. Refusant d'exploiter son don de façon mercantile, Johnny est traité de charlatan par un journal à scandales dont il a décliné l'offre. Il est cependant contacté par le shérif George Bannermann qui
	 lui demande de l'aide pour arrêter « l'étrangleur de Castle Rock », un tueur qui viole et égorge ses victimes, des femmes de neuf à 77 ans. John découvre vite que le meurtrier est un policier de la ville nommé Frank Dodd mai
	s celui-ci se suicide avant d'être arrêté.

	Parallèlement à l'histoire de Johnny Smith, on suit également l'ascension sociale de Greg Stillson, un homme au caractère violent, représentant de commerce puis agent d'assurances, qui se lance dans la politique et n'hésite p
	as à recourir à des méthodes illégales pour arriver à ses fins.

	Ne pouvant reprendre son travail d'enseignant à cause de son don et de l'hostilité que celui-ci déclenche parmi ses anciens collègues, Johnny devient le tuteur privé de Chuck Chasworth, le fils d'un millionnaire. Lors d'un me
	eting électoral, il rencontre Greg Stillson, désormais candidat à la Chambre des représentants et, en lui serrant la main, aperçoit alors une vision apocalyptique de l'avenir lorsque celui-ci sera devenu président. Johnny Smi
	th se débat alors avec un dilemme moral : que peut-il faire pour empêcher cela ? Peut-il aller jusqu'à tuer cet homme ? Il devient alors obsédé par Stillson.

	Johnny a ensuite la vision d'un incendie se déclarant dans un bar où doit se rendre Chuck et parvient à le dissuader de ne pas y aller mais ne parvient pas à faire fermer le bar. Dans la soirée, l'incendie se déclare et 81 pe
	rsonnes trouvent la mort. Johnny, touché moralement et dont la santé décline, part pour Phoenix afin de se faire oublier. Il apprend plus tard qu'il a une tumeur du cerveau qui s'est développée dans la « zone morte » et qu'il
	 ne lui reste plus longtemps à vivre. Cela le décide à tuer Stillson et, armé d'un fusil, il s'introduit de nuit dans une salle où Stillson fait un discours le lendemain. Lors du meeting, Johnny tire sur Stillson mais le manq
	ue. Lors de l'échange de coups de feu avec les gardes du corps de Stillson, celui-ci se sert d'un enfant comme bouclier humain et Johnny est mortellement blessé. Avant de mourir, il touche Stillson et voit que celui-ci n'a pl
	us aucun avenir. En effet, un photographe a pris un cliché de Stillson se servant de l'enfant pour se protéger, et on devine que cette image va complètement décrédibiliser Stillson et l'empêcher de parvenir à ses fins. Johnny
	 meurt en ayant le sentiment d'avoir accompli son destin."
		where IdLivre='TDZSK';
	
	update Livre
	set resumeLivre="Jack Torrance est un homme instruit mais au tempérament colérique. Il tente de reconstruire sa vie et celle de sa famille après que son alcoolisme lui a fait perdre son emploi d'
	enseignant. Ayant arrêté de boire, il accepte un emploi de gardien dans un grand hôtel isolé dans les montagnes, et fermé en hiver. Il emménage dans l'hôtel Overlook (dans les montagnes du Colorado) avec sa femme Wendy et son
	 fils Danny. Ce dernier possède des dons de médium (le shining du titre) et est sensible aux forces surnaturelles. Le jour de son arrivée à l'hôtel, Danny fait la connaissance de Dick Hallorann, le cuisinier de l'hôtel, qui p
	ossède lui aussi le shining mais à un degré bien moindre que le jeune garçon. Hallorann met en garde Danny contre les dangers de l'hôtel qui serait doté d'une conscience, et possédé par des esprits.

	Danny, ayant des prémonitions du danger que représente l'endroit pour sa famille, commence à voir des fantômes et des visions terrifiantes du passé de l'hôtel. Il préfère néanmoins se taire, sachant quelle importance revêt ce
	 travail pour son père. Mais l'hôtel commence à posséder Jack, le rendant de plus en plus instable et agressif afin de mieux le manipuler. Peu à peu, et après plusieurs incidents de plus en plus inquiétants, Jack cède au char
	me vénéneux de l'hôtel et voit son fils et sa femme comme des ennemis dont il faut se débarrasser.

	En plein cour de l'hiver et alors que l'hôtel est quasiment inaccessible en raison de la neige qui l'isole, Jack finit par craquer et tente de tuer son fils dont l'hôtel veut s'approprier les pouvoirs. Pendant ce temps, Hallo
	rann a eu conscience du danger que courent Wendy et Danny et fait l'impossible pour rallier l'hôtel à temps malgré les conditions météo. Wendy, qui a vu avec inquiétude la santé mentale de son mari se dégrader, essaie de prot
	éger son fils de Jack et, au cours d'une violente bagarre avec son mari, tous les deux sont gravement blessés. Jack, animé par l'énergie surnaturelle que lui confère l'hôtel, assomme néanmoins Hallorann, qui vient d'arriver e
	t croit enfin tenir son fils à sa merci. Mais Jack (et l'Overlook) ont oublié un détail crucial : la vieille chaudière de l'hôtel qui menace d'exploser si elle n'est pas réglée quotidiennement. Jack se rue alors au sous-sol p
	our empêcher l'explosion alors que Danny, Wendy et Hallorann se traînent à l'extérieur. Mais il arrive trop tard et la chaudière explose, détruisant l'Overlook et tuant Jack.

	Le roman se termine avec Danny et Wendy dans une station estivale dans le Maine où Hallorann travaille comme cuisinier. Tous trois vivent heureux malgré leur peine et le traumatisme qu'ils ont vécu."
		where IdLivre='TShiSK';
	
	update Livre
	set resumeLivre="Malgré toutes les précautions, un virus s'échappe d'une base de recherches de l'armée américaine. Un soldat parvient à quitter la base avant sa fermeture automatique et, avant de
	 mourir, transmet le virus à tous les gens qu'il croise sur sa route. Une épidémie de « super-grippe » ayant un taux de contamination de 99,4 % se répand alors, d'abord aux États-Unis, puis dans le monde entier et, en quelque
	s semaines, la civilisation s'effondre, totalement ravagée. Seule une poignée de rescapés naturellement immunisés contre le virus parviennent à survivre.

	Parallèlement à l'évolution de ce fléau, nous suivons les destinées de certaines personnes qui semblent être immunisées. Ainsi, Stu Redman, l'un des premiers exposés à la « super-grippe » est d'abord transféré au CDC d'Atlant
	a puis dans un centre spécialisé à Stovington (Vermont), d'où il parvient à s'échapper après la mort de tout le personnel. Il croise la route de Glen Bateman, puis de Frannie Goldsmith et d'Harold Lauder, deux jeunes gens du
	Maine qui sont allés à Stovington dans l'espoir d'y trouver de l'aide. Larry Underwood, un chanteur qui commençait à se faire un nom au début de l'épidémie, suit les traces d'Harold et Frannie accompagné de Nadine Cross, une
	mystérieuse jeune femme qui repousse ses avances. Frannie et Stu sont attirés l'un par l'autre, au grand dam d'Harold, qui est amoureux de la jeune femme. Nick Andros, un sourd-muet, est le premier à avoir des rêves qui le mè
	nent, avec ses amis Ralph Brentner et Tom Cullen, au Nebraska en direction d'une vielle femme nommée Mère Abigaël, qui semble guidée par des desseins divins. D'autres survivants, tels Lloyd Henreid et « La Poubelle », se rang
	ent quant à eux du côté de Randall Flagg, un être inquiétant doté de pouvoirs surnaturels qui rassemble ses propres disciples.

	Autour de ces deux figures, Mère Abigaël et Flagg, se constituent deux communautés. Mère Abigaël mène le groupe de Nick Andros jusqu'à Boulder, dans le Colorado, où vont les rejoindre le groupe de Stu et Fran, celui de Larry,
	 ainsi que d'autres survivants. Flagg rassemble quant à lui ses troupes dans la ville de Las Vegas, où il fait régner l'ordre et la discipline en utilisant les moyens les plus extrêmes comme punition pour les contrevenants, e
	t les prépare à la lutte contre l'autre communauté, répétant ainsi la perpétuelle lutte entre le Bien et le Mal. À Boulder, une nouvelle société s'organise sous l'égide de Mère Abigaël et du Conseil élu de la ville, dont font
	 partie, entre autres, Stu, Frannie, Glen, Nick, Ralph et Larry.

	Mère Abigaël reçoit une vision qui lui indique qu'elle a pêché par orgueil et part dans le désert. Nadine Cross est quant à elle visitée par des rêves qui la destine à Flagg et, pour chercher à s'en défaire, tente de séduire
	Larry. Mais celui-ci, qui a trouvé le réconfort auprès de Lucy Swann, la repousse et Nadine va trouver Harold, amer d'avoir été tenu à l'écart du Conseil, pour conspirer contre les dirigeants de Boulder. Tous deux font explos
	er une bombe lors d'une réunion du Conseil, tuant plusieurs personnes dont Nick Andros. Mère Abigaël est retrouvée dans le même temps et, avant de mourir d'épuisement, fait part aux dirigeants de Boulder de sa dernière vision
	 : ils doivent se rendre à Las Vegas pour se confronter au mal. Stu, Larry, Glen et Ralph se mettent donc en route mais Stu se casse une jambe en chemin et ses compagnons, la mort dans l'âme et sur l'insistance de Stu, doiven
	t l'abandonner. Flagg se débarrasse d'Harold et tente de faire de Nadine sa reine, mais celle-ci, dans un ultime sursaut, se révolte contre la véritable nature de Flagg et se suicide.

	Larry, Glen et Ralph arrivent à Las Vegas et sont aussitôt capturés. Glen, qui refuse de se rallier à Flagg, est tué par Lloyd, et Larry et Ralph se préparent à être exécutés. Mais « La Poubelle », qui s'est senti rejeté par
	ses pairs, revient à Las Vegas avec une arme nucléaire qu'il a trouvé dans le désert et la bombe explose à son arrivée, tuant tout le monde. Stu est retrouvé par Tom Cullen, qui le ramène à Boulder, et tous deux arrivent peu
	après la naissance du bébé de Frannie. Plus tard, Stu et Fran, inquiets de voir les mêmes erreurs déjà commises par la civilisation commencer à se reproduire, décident de quitter Boulder. L'épilogue du roman nous fait retrouv
	er Flagg, quelque part dans l'hémisphère sud, qui a survécu à l'explosion et commence à retrouver la mémoire et ses pouvoirs, rassemblant de nouveaux adeptes."
		where IdLivre='TStaSK';
	