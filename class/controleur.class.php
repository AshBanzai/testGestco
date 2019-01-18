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
            <div id="conteneur">
                <div id="details-vente">
                    <row>
                        <p>Responsable Devis : <input type="text" value="'.$e->idEmploye.' - '.$e->prenom.' '.$e->nom.'" readonly></p>
                    </row>                    
                    <row>
                        <p>N° Vente : <input type="text" value="'.$v->idVente.'" readonly></p>
                        <p>N° Client : <input type="text" value="'.$c->idClient.' - '.$c->prenom.' '.$c->nom.'" readonly></p>
                        <p>Date : <input type="text" value="'.substr($v->dateDevis,0 ).'" readonly></p>
                    </row>
                    <row>
                        <p>Entreprise : <input type="text" value="'.$s->idSociete.' - '.$s->nom.'" readonly></p>
                        <p>Adresse : <input type="text" value="'.$s->adresse.'" readonly></p>
                        <p>Coordonnées : <input type="text" value="'.$s->telephone.'" readonly></p>

                    </row>
                </div>

                <div id="details-article">
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
                                <td>'.$a->dernierCMUP.'</td>
                                <td>'.$d->qteDemandee.'</td>
                                <td>'.$d->txRemise.'</td>
                                <td>'.$d->remise.'</td>
                                <td>'.$ht.'</td>
                                <td>'.$a->txTVA.'</td>
                                <td>'.$ht*(1+$a->txTVA).'</td>
                                <td>'.$d->observation.'</td>
                        </tr>';	       
	    }    
	    
	  $retour = $retour.'
                    </table>
                </div>
            </div>';
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
	        session_start();
	        // on enregistre les parametres du visiteur comme variables de session
	        $_SESSION['id'] = $result->identifiant;    // son identifiant
	        $_SESSION['type'] = $result->idType;   //le poste de la personne dans l'entreprise
	        
	        //Le visiteur pourra seulement rejoindre les pages ou son type (son rôle dans l'entreprise) le lui permet
	        header ('location: Accueil'); 
	        // l'envoi sur la page accueil afin qu'il puisse se diriger vers la page qu'il souhaite
	    }
	    else {
	        // L'identifiant et/ou le mots de passe, est incorrect, on laisse un message au visiteur
	        echo '<body onLoad="alert(\'Identifiant ou mots de passe incorrect ! \')">';
	        // puis on le redirige vers la page d'accueil
	        echo '<meta http-equiv="refresh" content="0;URL=Accueil">';
	    }
	}
	
    public function estConnecte()
    {
        if(isset($_SESSION['id']) && isset($_SESSION['type']))
        return $_SESSION['type'];
        else return false;
    }
	
	public function listeDevis()
	{
	    
	$return='<p><fieldset> 
    Voici l outil de gestion des devis. Ci-dessous la liste des devis existants.
    Vous pouvez accéder au detail de chaque devis en cliquant sur "Voir Détail".
    Si vous souhaitez ajouter un devis cliquer sur le bouton "Ajouter un devis" tout en bas de la page.
	</fieldset></p>';
	
	$l = $this->vpdo->listeVentes();
	while($ligneIdVente = $l->fetch(PDO::FETCH_OBJ))
    {    
    $e=$this->vpdo->employeParIdVente($ligneIdVente->idVente)->fetch(PDO::FETCH_OBJ);
    $v=$this->vpdo->venteParSonId($ligneIdVente->idVente);
    $s=$this->vpdo->entrepriseParIdVente($ligneIdVente->idVente)->fetch(PDO::FETCH_OBJ);
    $c=$this->vpdo->entrepriseParIdVente($ligneIdVente->idVente)->fetch(PDO::FETCH_OBJ);
    $p=$this->vpdo->prixTotalParIdVente($ligneIdVente->idVente)->fetch(PDO::FETCH_OBJ);
    
    $return = $return.'<p><fieldset>
   
    Code de la vente : &emsp;&emsp;<input type="text" readonly value='.$ligneIdVente->idVente.'> &emsp; 
    Responsbale devis :&emsp;<input type="text" readonly value='.$e->idEmploye.'-'.$e->nom.'-'.$e->prenom.'> &emsp;
    Date devis :&emsp;<input type="text" readonly value='.$v->dateDevis.'> &emsp;

    <br /> <br /> 

    Nom de l Entreprise :&emsp;<input type="text" readonly value='.$s->nom.'>&emsp;
    Code du client :&emsp;&emsp;&emsp;<input type="text" readonly value='.$c->idClient.'>&emsp; 
    Prix Total :&emsp;<input type="text" readonly value='.$p->prixTotal.'>&emsp;

     <br /><br /><br />

    <a href="devis/'.$ligneIdVente->idVente.'"><input type="button" id="btn-voirDetail" value=" Voir Detail"></a>
    
    </fieldset></p>';
	}
	
    $return = $return.'<p><br/><br/><fieldset>
    <a href="devis/ajouter"><input type="button" id="btn-ajouterUnDevis" value=" Ajouter un Devis"></a>
    </fieldset></p>';
    
    return $return;
	}
	
	
	
	public function ajouter()
	{
	    
	    $vretour='
        </br><fieldset>Voici l outil d ajout des devis. Ci-dessous les informations demandé pour créer un nouveau devis.</fieldset>
        <div id="conteneur">
        <div id="details-Ajouter">
      
                    <row>
                        <p>Responsable Devis : <input type="text" value="">
                        N° Vente : <input type="text" value="">
                        N° Client : <input type="text" value="">
                        Date : <input type="text" value=""> </p>
               
                        <p>Entreprise : <input type="text" value="">
                        Adresse : <input type="text" value=""> 
                        Coordonnées : <input type="text" value=""> 

                    </row>
                </div>
	        
  <div id="details-article">
                <table>
            
                        <tr>    <th>Code article</th>   <th>Nom</th>   <th>Prix unitaire</th>   <th>Quantité</th>   <th>Remise %</th>   <th>Remise €</th>   <th>Total HT</th>   <th>TVA</th>   <th>Total TTC</th>   <th>Oservation</th>   </tr>';       

	                            $vretour = $vretour.'<tr>
                                <td><select name="codeArticle">';
                                $l = $this->vpdo->listeArticle();
	                            while($a= $l->fetch(PDO::FETCH_OBJ))
                                {    
                                $vretour = $vretour.'<option value="'.$a->idArticle.'">'.$a->idArticle.'</option>'; 
                                }
                                $vretour=$vretour.'</td>
                                <td><input type="text" value=""></td>
                                <td><input type="text" value=""></td>
                                <td><input type="text" value=""></td>
                                <td><input type="text" value=""></td>
                                <td><input type="text" value=""></td>
                                <td><input type="text" value=""></td>
                                <td><input type="text" value=""></td>
                                <td><input type="text" value=""></td>
                                <td><input type="text" value=""></td>
                                </tr>';  
	
	    
        $vretour=$vretour.' </table>
                </div>
            </div>';
	    return $vretour;
	}
	

	   
	public function genererMDP ($longueur = 8){
		// initialiser la variable $mdp
		$mdp = "";
	
		// Définir tout les caractères possibles dans le mot de passe,
		// Il est possible de rajouter des voyelles ou bien des caractères spéciaux
		$possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ&#@$*!";
	
		// obtenir le nombre de caractères dans la chaîne précédente
		// cette valeur sera utilisé plus tard
		$longueurMax = strlen($possible);
	
		if ($longueur > $longueurMax) {
			$longueur = $longueurMax;
		}
	
		// initialiser le compteur
		$i = 0;
	
		// ajouter un caractère aléatoire à $mdp jusqu'à ce que $longueur soit atteint
		while ($i < $longueur) {
			// prendre un caractère aléatoire
			$caractere = substr($possible, mt_rand(0, $longueurMax-1), 1);
	
			// vérifier si le caractère est déjà utilisé dans $mdp
			if (!strstr($mdp, $caractere)) {
				// Si non, ajouter le caractère à $mdp et augmenter le compteur
				$mdp .= $caractere;
				$i++;
			}
		}
	
		// retourner le résultat final
		return $mdp;
	}
	
	
}

?>
