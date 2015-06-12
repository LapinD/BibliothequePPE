DROP DATABASE IF EXISTS BDDBibliotheque;
CREATE DATABASE IF NOT EXISTS BDDBibliotheque;
USE BDDBibliotheque;

CREATE TABLE Pays
(IdPays Varchar(30) not null,
PRIMARY KEY(IdPays));


CREATE TABLE Auteurs
(IdAuteur int(6) not null auto_increment,
NomAuteur varchar(30) not null,
PrenomAuteur varchar(30) not null,
DateNaissA DATE,
DateDeces DATE,
IdPays VarChar(30) not null,
Biographie VarChar(1000),
AuteurPhoto VarChar(70),
PRIMARY KEY(IdAuteur),
FOREIGN KEY(IdPays)REFERENCES Pays(IdPays));



CREATE TABLE Livre
(IdLivre VarChar(10) not null,
TitreLivre varchar(50) not null,
AnneeEcriture DATE,
ResumeLivre varchar(5000),
PRIMARY KEY(IdLivre));



CREATE TABLE Ecrire
(IdLivre VarChar(10) not null,
IdAuteur int(6) not null,
PRIMARY KEY(IdAuteur,IdLivre),
FOREIGN KEY(IdAuteur) REFERENCES Auteurs(IdAuteur) ON UPDATE CASCADE ON DELETE CASCADE,
FOREIGN KEY(IdLivre) REFERENCES Livre(IdLivre) ON UPDATE CASCADE ON DELETE CASCADE);



CREATE TABLE Genre
(IdGenre VarChar(20) not null,
PRIMARY KEY(IdGenre));



CREATE TABLE GenreLivre
(IdGenre VarChar(20) not null,
IdLivre VarChar(10) not null,
PRIMARY KEY(IdGenre,IdLivre),
FOREIGN KEY(IdGenre) REFERENCES Genre(IdGenre),
FOREIGN KEY(IdLivre) REFERENCES livre(IdLivre));



CREATE TABLE Edition
(NomMaisonEdition varchar(20) not null,
PRIMARY KEY(NomMaisonEdition));



CREATE TABLE Exemplaire
(IdLivre VarChar(10) not null,
IdExemplaire int(3) not null,
DateAchat DATE,
DateEdition DATE,
nbPages int(4),
LangueExemplaire varchar(15) not null,
NomMaisonEdition varchar(20) not null,
CouvertureLivre varchar(70),
PRIMARY KEY(IDLivre, IDExemplaire),
FOREIGN KEY(NomMaisonEdition) REFERENCES Edition(NomMaisonEdition));

CREATE TABLE EBook
(IdLivre Varchar(10) not null,
IdExemplaire int(3) not null,
DateEdition int(3),
nbPages int(4),
LangueExemplaire varchar(15) not null,
NomMaisonEdition varchar(20) not null,
CouvertureLivre varchar(70),
CheminEBook varchar(70) not null,
PRIMARY KEY(IDLivre, IDExemplaire),
FOREIGN KEY(NomMaisonEdition) REFERENCES Edition(NomMaisonEdition));

CREATE TABLE Adherent
(IdAdherent int(5) not null auto_increment,
Nom varchar(20) not null,
Prenom varchar(20) not null,
DateNaissance DATE,
Ville varchar(30) not null,
Rue VARCHAR(40) not null,
CP char(5) not null,
Mail varchar(50) not null UNIQUE,
Tel char(10) not null,
Login varchar(30) not null UNIQUE,
MDP varchar(50) not null,
Litigieux Enum('Oui','Non') not null,
DateLitige date,
EstAdmin Enum('Oui', 'Non') not null,
PRIMARY KEY(IdAdherent));


CREATE TABLE RetourEmprunt
(IdLivre VarChar(10) not null,
IdExemplaire int(3) not null,
IdAdherent int(5) not null,
IdEmprunt int not null auto_increment,
DateEmprunt DATE,
DateRetourPrevu DATE,
DateRetour DATE,
EtePris Enum('Oui', 'Non'),
PRIMARY KEY(IdEmprunt),
FOREIGN KEY(IdLivre, IdExemplaire) REFERENCES Exemplaire(IdLivre, IdExemplaire),
FOREIGN KEY(IdAdherent) REFERENCES Adherent(IdAdherent));


CREATE TABLE BlogArticle
(IdArticle int(8) not null auto_increment,
DateArticle date,
TitreArticle Varchar(50),
ContenuArticle VarChar(8000),
IdAdherent int(5) not null,
IdLivre VarChar(10),
PRIMARY KEY(IdArticle),
FOREIGN KEY(IdAdherent) REFERENCES Adherent(IdAdherent) ON UPDATE CASCADE ON DELETE CASCADE,
FOREIGN KEY(IdLivre) REFERENCES Livre(IdLivre)) ;

CREATE TABLE AdherentExclut
AS SELECT *, CurDate() DateSup, User() AS Utilisateur from Adherent where 2=0; 

CREATE TABLE ARCHIVE_EMPRUNTS
AS SELECT *, Curdate() DateSup , User() AS Utilisateur FROM RetourEmprunt WHERE 2=0;

CREATE TABLE ExemplairePerdu
AS SELECT *, CurDate() DateSup, User() AS Utilisateur FROM Exemplaire Where 2=0;

