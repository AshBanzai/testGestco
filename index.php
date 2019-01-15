<?php
	session_start();

	include_once('class/autoload.php');
	$site = new page_base();
	$controleur=new controleur();
	$request = strtolower($_SERVER['REQUEST_URI']);
	$params = explode('/', trim($request, '/'));
    $params = array_filter($params);
	if (!isset($params[1]))
	{
		$params[1]='accueil';
	}
	switch ($params[1]) {
	    case 'accueil' :
	        $site->titre='Accueil';
	        $site-> right_sidebar=$site->rempli_right_sidebar();
	        $site-> left_sidebar=$controleur->retourne_article($site->titre).'Je suis un texte de remplissage, dans index.php, l.18';
	        $site->affiche();
	        break;
	        
	    case 'ventes' :
	        $site->titre='Ventes';
	        $site-> right_sidebar=$site->rempli_right_sidebar();
	        $site-> left_sidebar=$controleur->retourne_ventes();
	        $site->affiche();
	        
	        break;
	    case 'connexion' :
			$site->titre='Connexion';
			$site->js='jquery.validate.min';
			$site->js='messages_fr';
			$site->js='jquery.tooltipster.min';
			$site->css='tooltipster';
			$site-> right_sidebar=$site->rempli_right_sidebar();
			$site-> left_sidebar=$controleur->retourne_formulaire_login();
			$site->affiche();
			break;
		case 'deconnexion' :
			$_SESSION=array();
			session_destroy();
			echo '<script>document.location.href="Accueil"; </script>';
			break;
		case 'testconnexion' :
		    $_SESSION['id'] = 'admin';
		    $_SESSION['type'] = '666';
		    echo '<script>document.location.href="Accueil"; </script>';		   
		    break;
		default: 
			$site->titre='Accueil';
			$site-> right_sidebar=$site->rempli_right_sidebar();
			$site-> left_sidebar='<img src="image/erreur-404.png" alt="Erreur de liens">';
			$site->affiche();
			break;
	}	
	
?>