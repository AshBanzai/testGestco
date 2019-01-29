<?php
class controleur {

	private $vpdo;
	private $db;
	public function __construct() {
		$this->vpdo = new mypdo ();
		$this->db = $this->vpdo->connexion;
	}
	public function __get($propriete) {
		switch ($propriete) {
			case 'vpdo' :
				{
					return $this->vpdo;
					break;
				}
			case 'db' :
				{
					
					return $this->db;
					break;
				}
		}
    }
	
public function detailsDevis($idVente)
{
    $v = $this->vpdo->venteParSonId($idVente);
    $c = $this->vpdo->clientParSonId($v->idClient);
    $s = $this->vpdo->societeParSonId($c->idSociete);
    $lesDetails = $this->vpdo->listeDetailsDevisParIdVente($v->idVente);
    $e = $this->vpdo->employeParSonId($lesDetails->fetch(PDO::FETCH_OBJ)->idEmploye);
    $retour = '
        <div class="conteneur">
            <div id="details-vente">
                <row>
                    <p>Responsable Devis : <input type="text" data-employe="'.$e->idEmploye.'" value="'.$e->idEmploye.' - '.$e->prenom.' '.$e->nom.'" readonly></p>
                </row>                    
                <row>
                    <p>N° Vente : <input id="idVente" type="text" value="'.$v->idVente.'" readonly></p>
                    <p>N° Client : <input type="text" value="'.$c->idClient.' - '.$c->prenom.' '.$c->nom.'" readonly required></p>
                    <p>Date : <input type="date" value="'.substr($v->dateDevis, 0,10).'" readonly required></p>
                </row>
                <row>
                    <p>Entreprise : <input type="text" value="'.$s->idSociete.' - '.$s->nom.'" readonly></p>
                    <p>Adresse : <input type="text" value="'.$s->adresse.'" readonly></p>
                    <p>Coordonnées : <input type="text" value="'.$s->telephone.'" readonly></p>
                </row>
            </div>

            <div id="details-articles-devis">
                <table>
                    <tr>    <th>Code article</th>   <th>Nom</th>   <th>Prix unitaire</th>   <th>Quantité</th>   <th>Remise %</th>   <th>Remise €</th>   <th>Total HT</th>   <th>TVA</th>   <th>Total TTC</th>   <th>Oservation</th>   </tr>';
    
    $lesDetails = $this->vpdo->listeDetailsDevisParIdVente($v->idVente);	    
    while($d = $lesDetails->fetch(PDO::FETCH_OBJ))
    {	       
        $a = $this->vpdo->articleParSonId($d->idArticle);
        $ht = ($d->prixVente*$d->qteDemandee*(1-$d->txRemise));
        $retour = $retour.'
                    <tr>
                            <td>'.$d->idArticle.'</td>
                            <td>'.$a->libelle.'</td>
                            <td>'.$d->prixVente.'</td>
                            <td>'.$d->qteDemandee.'</td>
                            <td>'.$d->txRemise.'</td>
                            <td>'.$d->remise.'</td>
                            <td>'.$ht.'</td>
                            <td>'.$a->txTVA.'</td>
                            <td>'.$d->prixVente * $d->qteDemandee.'</td>
                            <td>'.$d->observation.'</td>
                    </tr>';	       
    }    
    
  $retour = $retour.'
                </table>
            </div>
        </div>';
  if($v->dateCommande != null)//Si le devis a déjà été confirmé en commande, on ne fait pas de bouton JS.
      $retour = $retour.'<a class="bou-classique">Devis confirmé</a>';
    else
        $retour=$retour.'<a id="confirmer" class="bou-classique">Confirmer Devis</a>';
    return $retour;
}	
		
public function formulaireLogin()
{
	    
	    return '    <form action="confirmation" method="post">
    Votre login : <input type="text" name="login">
    <br />
    Votre mot de passe : <input type="password" name="pwd"><br />
    <input type="submit" value="Connexion">
    </form>';
}	
	
public function confirmationLogin($login,$mdp)
{  // verifie si l'identifiant et le mots de passe est valide
	    $mdp=md5($mdp);
	    $result = $this->vpdo->listeComptes($login,$mdp)->fetch(PDO::FETCH_OBJ);
	    if($result != null)
	    {
	        echo 'connecte';
	        session_start ();
	        // on enregistre les parametres du visiteur comme variables de session
	        $_SESSION['id'] = $result->identifiant;// son identifiant
	        $_SESSION['idEmploye'] = $result->idEmploye; //Son id de base de données 
	        $_SESSION['type'] = $result->idType;   //le poste de la personne dans l'entreprise
	        // on redirige notre visiteur vers une page de notre section membre
	        
	        //Le visiteur pourra seulement rejoindre les pages ou son type (son rôle dans l'entreprise) le lui permet
	        // l'envoi sur la page accueil afin qu'il puisse se diriger vers la page qu'il souhaite
	    }
	    else {
	        // L'identifiant et/ou le mots de passe, est incorrect, on laisse un message au visiteur
	        echo '<body onLoad="alert(\'Identifiant ou mots de passe incorrect ! \')">';
	    }
	    // puis on le redirige vers la page d'accueil	    
	    header ('location: Accueil');	    
}
	
public function estConnecte()
{
        if(isset($_SESSION['id']) && isset($_SESSION['type'])) // on verfie que l'utilisateur a bien un id et un type
            return $_SESSION['type'];// Si oui on retourne le type qui lui servira à ce connecter aux pages dont il a accès,
            else return false;// Sinon retourne false et donc a seulement accès au page sans sécurité.
}
	
public function listeDevis()
{
	    
	$return='
            <div class="conteneur div-liste-devis">
                <p style="margin-left: 1em">
                    Voici l\'outil de gestion des devis. Ci-dessous la liste des devis existants.<br>
                    Vous pouvez accéder au detail de chaque devis en cliquant sur "Voir Détail".<br>
                    Si vous souhaitez ajouter un devis, cliquez sur le bouton "Ajouter un devis" en bas de la page.
                </p>';//On affiche un message pour que l'utilisateur trouve plus facilement ses marques.
	
	$l = $this->vpdo->listeVentes();
	while($ligneIdVente = $l->fetch(PDO::FETCH_OBJ))//boucle tant que..des données sont présentes dans la requête liste. 
    {    
    $e=$this->vpdo->employeParIdVente($ligneIdVente->idVente)->fetch(PDO::FETCH_OBJ);
    $v=$this->vpdo->venteParSonId($ligneIdVente->idVente);
    $s=$this->vpdo->entrepriseParIdVente($ligneIdVente->idVente)->fetch(PDO::FETCH_OBJ);
    $c=$v->idClient;
    $p=$this->vpdo->prixTotalParIdVente($ligneIdVente->idVente)->fetch(PDO::FETCH_OBJ);
    
    //on prévoit des variables pour nos appels
    // on crée un bloque avec les informations qui seront multipliées pour chaque nouvelle ligne de la requête. 
    //On met aussi le bouton "Voir Detail", avec un lien dynamique pour envoyé l'utilisateur sur un lien différent en fonction du bouton sur lequel il clique
    $return = $return.'
                <bloc>
                    <row>
                        <p>Code vente :<input type="text" readonly value='.$ligneIdVente->idVente.'></p>
                        <p>Responsbale devis :<input type="text" readonly value='.$e->idEmploye.' - '.$e->prenom.' '.$e->nom.'></p>
                        <p>Date devis :<input type="text" readonly value='.$v->dateDevis.'></p>
                    </row>
                    <row>
                        <p>Entreprise :<input type="text" readonly value='.$s->idSociete.' - '.$s->nom.'></p>
                        <p>Code client :<input type="text" readonly value='.$c.'></p>
                        <p>Prix Total :<input type="text" readonly value='.$p->prixTotal.'></p>
                    </row>
                    <row>
                       <a href="Devis/'.$ligneIdVente->idVente.'" id="btn-voirDetail" class="bou-classique">Voir Details</a>
                    </row>
                </bloc>
    ';
	}
    // on rajoute le bouton pour ajouter un Devis
    $return = $return.'</div>
    <a href="Devis/Ajouter" id="btn-ajouter" class="bou-classique">Ajouter un Devis</a>';
    return $return;   // on retourne la totalité du texte
	}
	
		
	public function afficheAjoutDevis()
	{
	    $idVente = $this->vpdo->idDerniereVente()->idVente+1;
	    $emp = $this->vpdo->employeParSonId($_SESSION['idEmploye']);	  
	    $lesClients = $this->vpdo->listeClients();
	    $lesArticles = $this->vpdo->listeArticles();
	    $return = '
            <div class="conteneur">
                <div id="details-vente">
                    <row>
                        <p>Responsable Devis : <input id="respDevis" type="text" value="'.$emp->idEmploye.' - '.$emp->prenom.' '.$emp->nom.'" readonly></input></p>
                    </row>
                    <row>
                        <p>N° Vente : <input id="idVente" type="text" value="'.$idVente.'" readonly></p>
                        <p>N° Client :<span class="tooltip" id="ttIdClient" title="Vous n\'avez pas choisi de client !"><select id="idClient">
                                        <option selected hidden disabled></option>';//Ajoute une option vide pour forcer l'utilisateur à choisir.
        
        while($e = $lesClients->fetch(PDO::FETCH_OBJ))
        {
            $return = $return.'<option value="'.$e->idClient.'">'.$e->idClient.' - '.$e->nom.' '.$e->prenom.'</option>';
        }
        $return = $return.'</select></span></p>
                        <p>Date : <input id="dateDevis" type="date" required></p>
                    </row>
                    <row>
                        <p>Société : <input id="idSociete" type="text" readonly></p>
                        <p>Adresse : <input id="adrSociete" type="text" readonly></p>
                        <p>Coordonnées : <input id="coordSociete" type="text" readonly></p>
                    </row>
                </div>
	        
                <div id="details-articles-devis">
                    <table id="table-articles">
                        <tr>    <th>Code article</th>   <th>Nom</th>   <th>Prix unitaire</th>   <th>Quantité</th>   <th>Remise % TTC</th>   <th>Remise € TTC</th>   <th>Total HT</th>   <th>TVA %</th>   <th>Total TTC</th>   <th>Oservation</th>   </tr>
                        <tr>
                            <td><span class="tooltip" id="ttIdArticle" title="Sélectionnez au moins un article !"><select id="idArticle1">
                                <option value></option>';
        while($e = $lesArticles->fetch(PDO::FETCH_OBJ))
        {
            $return = $return.'<option value="'.$e->idArticle.'">'.$e->idArticle.'</option>';
        }        
        $return = $return.'</select></span></td>
                            <td><input id="nomArticle1" type="text" readonly></td>
                            <td><input id="CMUPArticle1" type="number" readonly></td>
                            <td><input id="qteArticle1" type="number" min=1 value=1></td>
                            <td><input id="txArticle1" type="number" min=0 max=100 step=0.5 value=0></td>
                            <td><input id="remise1" type="number" value=0 readonly></td>
                            <td><input id="ht1" type="number" value=0 readonly></td>
                            <td><input id="tva1" type="number" value=0 readonly></td>
                            <td><input id="ttc1" type="number" value=0 readonly></td>
                            <td><input id="obsArticle1" type="text"></td>
                      </tr>
                    </table>
                    <a id="ajouteLigne" class="bou-classique bou-plusLigne">+</a>
                </div>
            </div>
	    <a id="enregistrer" class="bou-classique">Enregistrer le devis</a>';
	    return $return;
	}
	
/*______________________________________________________________________________________________________________________________________*/
/* ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */
/* ************************************************************************************************************************************* */	
/* **------------------------------------------DEBUT-GESTION-LISTE-CLIENT-ET-FOURNISSEUR----------------------------------------------** */	
/* ************************************************************************************************************************************* */	
/* ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */
	
public function listeSociete($types,$type,$idType)
{    
    $return='<div class="conteneur div-liste-entreprises"><p style="margin-left: 1em">
                    <center><u><h4>Voici l\'outil de gestion des  '.$types.' </h4></u></center><br>
                    Vous pouvez accéder au contact pour chaque societe en cliquant sur <b>"Voir détails"</b>.<br>                  
                    Si vous souhaitez ajouter un nouveau contact, cliquez sur <b>"Voir détails"</b>.<br>
                    Si vous souhaitez modifier les informations d\'une societe ou d\'un contact, cliquez sur <b>"Voir détails"</b>.<br>
                    Si vous souhaitez ajouter une nouvelle societe, cliquez sur le bouton <b>"Ajouter une Societe"</b>.<br>
                    <a href="'.$types.'/ajoutersociete" id="btn-confirmerModifEntreprise" class="bou-classique">Ajouter une société</a>';                    
if ($types=="clients"){
    $return = $return.'<p><b>Liste des '.$types.'</b></p><div style="display:block"  id="block-'.$type.'">';
    $lscof = $this->vpdo->listeSocieteClients();
}
else{
    $return = $return.'<p><b>Liste des '.$types.'</b></p><div style="display:block"  id="block-'.$type.'">';
    $lscof = $this->vpdo->listeSocieteFournisseurs();
}
while($ls = $lscof->fetch(PDO::FETCH_OBJ))
    {
    $return = $return.'
                <bloc>
                   	<row>    
                        <p>Dénomination : <input type="text" readonly maxlength="24" value='.$ls->nom.'> </p>
                        <p>Code : <input type="text" readonly  value='.$ls->idSociete.'></p>
                        <p>Site web :<input type="text" readonly maxlength="48" value='.$ls->siteWeb.'> </p>  
                    </row>
                    <row>
                        <p>Téléphone :<input type="text" readonly maxlength="12" value='.$ls->telephone.'></p>
                        <p>Adresse :<input type="text" readonly maxlength="64" value="'.$ls->adresse.'"> </p>
                        <p>Raison sociale :<input type="text" readonly maxlength="12" value='.$ls->raison.'></p>
                    </row>
                    <row>
                        <p>Mail :<input type="text" readonly value='.$ls->mail.'></p>
                        <a href="'.$types.'/'.$ls->idSociete.'" id="btn-voirDetail" class="bou-classique">Voir détails</a>   
                    </row>
                </bloc>';
    }    
    $return=$return.'</div></div>';
    return $return;
}

/* ************************************************************************************************************************************* */
/* *****************************************************AUTRE*METHODE*MEME*BUT********************************************************** */
/* ************************************************************************************************************************************* */


public function listeContact($idSociete, $types,$type, $idType)
{
    $s = $this->vpdo->societeParSonId($idSociete);
    if ($types=="clients")
    {
        $lccof = $this->vpdo->listeContactClientsParId($idSociete);
    }
    else
    {
        $lccof = $this->vpdo->listeContactFournisseursParId($idSociete);
    }
    $return='   <div class="conteneur  div-liste-entreprises">
                    <p style="margin-left: 1em">
                        Voici l\'outil de gestion des contacts. Ci-dessous la liste des contacts existant pour le '.$type.' : <b>'.$s->nom.'</b>.<br>
                        Si vous souhaitez modifier les informations d\'un '.$type.', cliquez sur <b>"Modifier les informations"</b>.<br>                        
                        Si vous souhaitez modifier les informations d\'un contact, cliquez sur <b>"Modifier le contact"</b>.<br>
                        Si vous souhaitez ajouter un nouveau contact, cliquez sur <b>"Ajouter un contact"</b> en bas de la page.
                    </p> ';
   $return=$return.' <p><b>INFORMATION SUR L\'ENTREPRISE</b></p> ';  
   $return = $return.'<div id="details-entreprise">
                        <row>    
                        <p> Code de l\'entreprise : <input type="text" id="idSociete" readonly value='.$s->idSociete.'></p>
                        <p>  Nom de l\'entreprise : <input type="text" maxlength="24" id="nomSociete"value='.$s->nom.'> </p>
                        <p>  Raison sociale : <input type="text" id="raisonSociete" maxlength="12" value='.$s->raison.'> </p>
                     </row>
                     <row>
                        <p>  Site web de l\'entreprise : <input type="text" id="siteWebSociete" maxlength="48" value='.$s->siteWeb.'> </p>  
                        <p>  Téléphone de l\'entreprise : <input type="text"  id="telSociete" maxlength="12" value='.$s->telephone.'> </p>
                        <p> Adresse de l\'entreprise : <input type="text" id="adresseSociete" maxlength="64" value="'.$s->adresse.'"> </p>
                      </row>
                      <row>
                        <p>  fax de l\'entreprise : <input type="text" id="faxSociete" required value='.$s->fax.'> </p>          
                        <p>  Mail de l\'entreprise : <input type="text" id="mailSociete" required value='.$s->mail.'></p>
                        <a onclick="modification'.$type.'()" class="bou-classique">Modifier les informations</a>
                      </row> 
                </div> 
            <p></p>';

    while($ligneIdContact = $lccof->fetch(PDO::FETCH_OBJ))
    { 
     $return = $return.'<div id="conteneur  div-liste-entreprises">
    <row> 
        <p>Code du contact : <input type="text" id="id'.$ligneIdContact->$idType.'" required readonly value='.$ligneIdContact->$idType.'></p>
        <p>Nom du contact : <input type="text" id="nom'.$ligneIdContact->$idType.'" maxlength="16" required value='.$ligneIdContact->nom.'></p>
        <p>Prenom du contact : <input type="text" id="prenom'.$ligneIdContact->$idType.'" maxlength="16"  value='.$ligneIdContact->prenom.'></p> 
    </row>
    <row>
        <p>Téléphone du contact : <input type="text" id="tel'.$ligneIdContact->$idType.'" maxlength="10" required value='.$ligneIdContact->telephone.'></p>
        <p>Mail du contact : <input type="text" id="mail'.$ligneIdContact->$idType.'" required maxlength="40" value='.$ligneIdContact->mail.'></p>
        <p>Id société du contact : <input type="text" id="societe'.$ligneIdContact->$idType.'" required value='.$ligneIdContact->idSociete.'></p>
     </row>   
     <row>
        <a onclick=\'modificationContact("'.$ligneIdContact->$idType.'","'.$type.'")\' class="bou-classique">Modifier le contact</a>
        

    </row>
</div>';
    }
    $return = $return.'</div><a href="ajoutercontact'.$type.'/'.$idSociete.'" class="bou-classique">Ajouter un contact</a><a href="javascript:history.go(-1)" class="bou-classique">Retour</a>   ';
    return $return;
}


/* ************************************************************************************************************************************* */
/* *****************************************************AUTRE*METHODE*MEME*BUT********************************************************** */
/* ************************************************************************************************************************************* */


public function ajouterContact($idSociete)//à revoir pour fournisseur
{   
    $idContactClient = $this->vpdo->idDernierContactClient()->idClient+1;
    $infoSociete=$this-> vpdo ->societeParSonId($idSociete)->nom;
    $return='<div class="conteneur div-liste-entreprises">
                    <p style="margin-left: 1em">
                        Voici l\'outil d\'ajout des contacts pour <b>'.$infoSociete.'</b><br>
                        Il vous suffit de remplir les cases ci-dessous et cliquez sur <b>"Valider le contact"</b>.<br>
                        Les cases <b>"Code du contact"</b> et  <b>"id société '.$infoSociete.'"</b> ne peuvent pas être changées.<br>
                        Si vous souhaitez ajouter un contact pour une entreprise déjà existante, veuillez vous diriger vers la liste des entreprises.  
                    </p> ';
    $return = $return.'
<div id="bloc-liste" class="conteneur div-liste-entreprises">
    <row>
        <p>Code du contact : <input type="text" id="idClient" readonly value="'.$idContactClient.'"></p>
        <p>Nom du contact(*) : <input type="text" id="nomClient" maxlength="16" required value=""></p>
        <p>Prenom du contact(*) : <input type="text" id="prenomClient" maxlength="16" required  value=""></p>
    </row>
    <row>
        <p>Téléphone du contact(*) : <input type="text" id="telClient" maxlength="10" required  value=""></p>
        <p>Mail du contact(*) : <input type="text" id="mailClient" maxlength="40" required value=""></p>
        <p>id société '.$infoSociete.' : <input type="text" readonly id="societeClient" value="'.$idSociete.'"></p>
     </row>
     <row>
        <a onclick=\'ajoutercontactclient()\' class="bou-classique">Valider le contact</a>
        <a href="javascript:history.go(-1)" class="bou-classique">Retour</a>   
    </row>
</div>';
	
	return $return;
}


/* ************************************************************************************************************************************* */
/* *****************************************************AUTRE*METHODE*MEME*BUT********************************************************** */
/* ************************************************************************************************************************************* */


public function ajouterSociete()
    {
        $idSociete = $this->vpdo->idDernierSociete()->idSociete+1;
        $return='<div class="conteneur  div-liste-entreprises">
                    <p style="margin-left: 1em">
                        Voici l\'outil d\'ajout de nouvelle entreprise.</b><br>
                        Il vous suffit de remplir les cases ci-dessous et cliquez sur <b>"Valider la société"</b>.<br>
                        La case <b>"id de l\'entreprise"</b> ne peut pas être changé.
                    </p> ';
        $return = $return.'
<div id="bloc-liste" class="conteneur  div-liste-entreprises">
    <row>
        <p> Code de l\'entreprise : <input type="text" id="idSociete" readonly value="'.$idSociete.'"></p>
        <p>  Nom de l\'entreprise : <input type="text" maxlength="24" id="nomSociete"value=""></p>
        <p>  Raison sociale : <input type="text" id="raisonSociete" maxlength="12" value=""> </p>
   </row>
    <row>
         <p>  Site web de l\'entreprise : <input type="text" id="siteWebSociete" maxlength="48" value=""> </p>  
         <p>  Téléphone de l\'entreprise : <input type="text"  id="telSociete" maxlength="12" value=""> </p>
         <p> Adresse de l\'entreprise : <input type="text" id="adresseSociete" maxlength="64" value=""> </p>
    </row>
     <row>
         <p>  fax de l\'entreprise : <input type="text" id="faxSociete" required maxlength="48"  value=""> </p>          
         <p>  Mail de l\'entreprise : <input type="text" id="mailSociete" required maxlength="40"  value=""></p>
    </row>
         <a onclick=\'ajoutersociete()\' class="bou-classique">Confirmer l\'entreprise</a>
         <a href="javascript:history.go(-1)" class="bou-classique">Retour</a>
    </row>
</div>';
        
        return $return;
    }

/* ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */
/* ************************************************************************************************************************************* */
/* ****************************************FIN*GESTION*LISTE*CLIENT*ET*FOURNISSEUR****************************************************** */
/* ************************************************************************************************************************************* */
/* ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// */
    
public function afficheListeArticles()
    {
        $retour = '
                <div class="conteneur div-articles">';
        
        $retour = $retour.'
                    <p>Voici la liste des articles enregistrés dans la base de données.</p>
        ';
        $lesArticles = $this->vpdo->listeArticles();
        $i = 1;
        while($a = $lesArticles->fetch(PDO::FETCH_OBJ))
        {
            $f = $this->vpdo->familleParSonId($a->idFam);
            $retour = $retour.'
                    <row>
                        <p>ID Article : <input id="idArticle" value="'.$a->idArticle.'"></p>
                        <p>Code-barre : <input id="barreArticle" value="'.$a->codeBarre.'"></p>
                        <p>Dénomination : <input id="libArticle" value="'.$a->libelle.'"></p>
                        <p>Famille : <input id="famArticle" value="'.$f->libelle.'"></p>
                        <a class="bou-classique" href="Articles/'.$a->idArticle.'">Détails article</a>
                    </row>
                ';
          $i++;  
        }
        $retour = $retour.'
                </div>';
        return $retour;
    }
    
    public function afficheDetailsArticle($idArticle)
    {
        $a = $this->vpdo->articleParSonId($idArticle);
        $em = $this->vpdo->emplacementParSonId($a->idEmp);
        $f = $this->vpdo->familleParSonId($a->idFam);
        $lesMouvements = $this->vpdo->listeMouvementsParArticle($idArticle);
        $retour = '
    <div class="conteneur">
        <div id="details-infos-article">
            <row>
                <p>Nom Article : <input type="text" value="'.$a->libelle.'"></p>
                <p>ID Article : <input type="text" value="'.$a->idArticle.'" readonly></p>
            </row>
            <row>
                <p>Famille Article : <select>';
            $lesFamilles = $this->vpdo->listeFamilles();
            while($lesF = $lesFamilles->fetch(PDO::FETCH_OBJ))
            {
                $retour = $retour.'
                    <option value="'.$lesF->idFam.'"';
                
                //Si l'id de la famille l'article en détail = l'id de la famille actuellement ajoutée au select
                if($lesF->idFam = $a->idFam)
                    $retour = $retour." selected"; //On la choisit par défaut.
                
                $retour = $retour.'>'.$lesF->idFam.' - '.$lesF->libelle.'</option>';
            }                                          
            $retour = $retour.'</select></p>
                <p>Code à barres : <input type="" value="'.$a->codeBarre.'"></p>
                <p>Localisation : <input type="text" value="Dépôt '.$em->depot.'; '.$em->libelle.'"></p>
            </row>
        </div>
        <div id="onglets-details-article">
            <div id="on-cmup" active=true>Calcul CMUP</div>
            <div id="on-mouv">Entrées/Sorties de stock</div>
        </div>
        <div id="div-onglets-details-article">
            <div id="div-on-cmup">
                <row>
                    <p>Marge % minimale : <input type="number" value="'.(100*$a->txMarge).'"></p>
                    <p>TVA % : <input type="number" value="'.(100*$a->txTVA).'"></p>
                    <p>Coût Unitaire Moyen Pondéré (CMUP) actuel : <input type="number" value="'.$a->dernierCMUP.'" readonly></p>
                </row>
                <row>
                    <p>Nouveau CMUP calculé : <input id="nouveauCMUP" type="number"></p>
                </row>
            </div>

            <div id="div-on-mouv">
                <row>
                    <table>
                        <tr><th>Id Mouvement</th>   <th>Type</th>   <th>Fournisseur</th>   <th>Date</th>   <th>Prix unité</th>   <th>Quantité</th>  <th>Observations</th></tr>';
                        
                        
            while($m = $lesMouvements->fetch(PDO::FETCH_OBJ))
            {
                $retour = $retour.'
                            <tr>
                                <td>'.$m->idMouv.'</td>
                                <td>'.$m->idType.'</td>
                                <td>'.$m->idFour.'</td>
                                <td>'.$m->date.'</td>
                                <td>'.$m->prix.'</td>
                                <td>'.$m->qte.'</td>
                                <td>'.$m->commentaire.'</td>
                            </tr>
            ';   
            }
            
            $retour = $retour.'
                        </table>
                    </row>
                </div>
            </div>
';
                
        $retour = $retour.'
                </div>';
        return $retour;
    }
    
    
}
?>
