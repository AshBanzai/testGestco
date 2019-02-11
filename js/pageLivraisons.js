/*$('document').ready(function(){// NC : début gestion reliquat
	let idVente = document.getElementById("idVente");
	let listeIdArticle;
	$.ajax({ 
        type: "POST",
        dataType: "json",
        data:
    	{
        	'action':'boucleArticle,
        	'idV':idVente.value,
    	},
    	url: "../../ajax/detailsLivraisonAjax.php",
        success: function(r) {
        	$(listeidArticle).val(r['result']);
        },
        error: function (xhr, ajaxOptions, thrownError)
        {
            console.log(xhr.status);
            console.log(thrownError);
            console.log(ajaxOptions);
        }
	});
});*/

function aide(){
	let elmt = document.getElementById("afficherAide");
	let elmt1 = document.getElementById("masquerAide");
	let elmt2 = document.getElementById("aide");
	toggleDisplay(elmt);
	toggleDisplay(elmt1);
	toggleDisplay(elmt2);
}
function toggleDisplay(elmt){		
	   if(typeof elmt == "string")		
	      elmt = document.getElementById(elmt);		
	   if(elmt.style.display == "none")		
	      elmt.style.display = "block";		
	   else		
	      elmt.style.display = "none";		
}


function gestionHistorique(){
	var elmt = document.getElementById("historique");
	var elmt1 = document.getElementById("encours");
	var elmt2 = document.getElementById("btn-historique");
	var elmt3 = document.getElementById("btn-encours");
	toggleDisplay(elmt);
	toggleDisplay(elmt1);
	toggleDisplay(elmt2);
	toggleDisplay(elmt3);
}


function ajouterlivraison() {
	if (confirm("Pour confirmer la livraison, cliqué sur 'ok', sinon cliquer sur 'annuler'."))
	{
		let idEmploye= document.getElementById("idEmploye");
		let idVente = document.getElementById("idVente");
		var redirection =("http://localhost/GESTCO/Livraisons/");
		
		/*var qteFournie ={}; // NC: début gestion reliquat
		while (isset listeIdArticle)
		boucle des articles de la vente
		if qteFournie == qteDemandee
		ajax
		else
		rien*/
		
		
		$.ajax({ 
	        type: "POST",
	        dataType: "json",
	        data:
	    	{
	        	'action':'ajoutLivraison',
	        	'idV':idVente.value,
	        	'idEmploye':idEmploye.value,
	    	},
	        url: "../../ajax/detailsLivraisonAjax.php",
	        success: function(r) {
	        	alert("Livraison confirmer avec succès !");
	        	location.href =(redirection);
	        },
	        error: function (xhr, ajaxOptions, thrownError)
	        {
	            console.log(xhr.status);
	            console.log(thrownError);
	            console.log(ajaxOptions);
	        }
		});
		
	}
		
	}
