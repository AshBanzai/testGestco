<?php
include_once('../class/autoload.php');
$pdo = new mypdo();
$r = array();//Array renvoyé à la fin
$action = $_POST['action'];//Récupération de la source de l'AJAX

switch ($action)
{ 
   /* case 'deleteClient':
        $idC = $_POST['idClient'];
        $r['idClient'] = $idC; 
        $r['result'] = $pdo->deleteContactClient($idC);
        break;*///annuler car il faut aussi supprimer les ventes
   
    case 'modifierClient':
        $i=$_POST['idSociete'];
        $n=$_POST['nomSociete'];
        $sw=$_POST['siteWebSociete'];
        $t=$_POST['telSociete'];
        $f=$_POST['faxSociete'];
        $a=$_POST['adresseSociete'];
        $ra=$_POST['raisonSociete'];
        $m=$_POST['mailSociete'];
        $r['result']=$pdo->TupdateClient('nom',$n,'idSociete',$i);
        $r['result']=$pdo->TupdateClient('siteWeb',$sw,'idSociete',$i);
        $r['result']=$pdo->TupdateClient('telephone',$t,'idSociete',$i);
        $r['result']=$pdo->TupdateClient('fax',$f,'idSociete',$i);
        $r['result']=$pdo->TupdateClient('adresse',$a,'idSociete',$i);
        $r['result']=$pdo->TupdateClient('raison',$ra,'idSociete',$i);
        $r['result']=$pdo->TupdateClient('mail',$m,'idSociete',$i);
       // $r['result'] = $pdo->updateClient($n,$a,$t,$f,$sw,$ra,$m,$i);//méthode différente.
        break;
        
        
    case 'modifierContactClient':
        $i=$_POST['idClient'];
        $n=$_POST['nom'];
        $p=$_POST['prenom'];
        $t=$_POST['telephone'];
        $m=$_POST['mail'];
        $s=$_POST['societe'];
        //$i=$_POST['idClient'];
        $r['result']=$pdo->updateContactClient('nom',$n,'idClient',$i);
        $r['result']=$pdo->updateContactClient('prenom',$p,'idClient',$i);
        $r['result']=$pdo->updateContactClient('telephone',$t,'idClient',$i);
        $r['result']=$pdo->updateContactClient('mail',$m,'idClient',$i);
        $r['result']=$pdo->updateContactClient('idSociete',$s,'idClient',$i);
        break;
        
    case 'ajouterContactClient':
        $i=$_POST['idClient'];
        $s=$_POST['societe'];
        $n=$_POST['nom'];
        $p=$_POST['prenom'];
        $t=$_POST['telephone'];
        $m=$_POST['mail'];
        $r['result']=$pdo->insertContactClient($i,$s, $n, $p, $m, $t);
        break;
}

die( json_encode($r) );

/*$lcc = $pdo->listeContactClientsParId($s);
 while($ligneIdContact = $lcc->fetch(PDO::FETCH_OBJ))
 {
 $r['result']="";
 $r['result'] = $r['result'].'
 <div class="bloc-liste">
 <row>
 <p>Code du contact : <input type="text" id="idClient" readonly value='.$ligneIdContact->idClient.'></p>
 <p>Nom du contact : <input type="text" id="nomClient" value='.$ligneIdContact->nom.'></p>
 <p>Prenom du contact : <input type="text" id="prenomClient"  value='.$ligneIdContact->prenom.'></p>
 </row>
 <row>
 <p>Téléphone du contact : <input type="text" id="telClient"  value='.$ligneIdContact->telephone.'></p>
 <p>Mail du contact : <input type="text" id="mailClient"  value='.$ligneIdContact->mail.'></p>
 <p>Id société du contact : <input type="text" id="societeClient"  value='.$ligneIdContact->idSociete.'></p>
 </row>
 <row>
 <a onclick="modificationcontactclient()" class="bou-classique">Modifier le contact</a>
 
 </row>
 </div>'; }*/
?>
    

      
            
       
      
