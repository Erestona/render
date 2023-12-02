<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

	function optionsCatalogue (Request $request, Response $response, $args) {
	    
	    // Evite que le front demande une confirmation à chaque modification
	    $response = $response->withHeader("Access-Control-Max-Age", 600);
	    
	    return addHeaders ($response);
	}

	function  getSearchCalatogue (Request $request, Response $response, $args) {
	    $filtre = $args['filtre'];
	    $flux = '[{ "id": 1, "name": "Produit 1", "category": "Catégorie 1", "price": 10 },
		{ "id": 2, "name": "Produit 2", "category": "Catégorie 1", "price": 11 },
		{ "id": 3, "name": "Produit 3", "category": "Catégorie 1", "price": 12 },
		{ "id": 4, "name": "Produit 4", "category": "Catégorie 1", "price": 13 },
		{ "id": 5, "name": "Produit 5", "category": "Catégorie 1", "price": 14 },
	
		{ "id": 6, "name": "Produit 6", "category": "Catégorie 2", "price": 10 },
		{ "id": 7, "name": "Produit 7", "category": "Catégorie 2", "price": 12 },
		{ "id": 8, "name": "Produit 8", "category": "Catégorie 2", "price": 14 },
		{ "id": 9, "name": "Produit 9", "category": "Catégorie 2", "price": 16 },
		{ "id": 10, "name": "Produit 10", "category": "Catégorie 2", "price": 18 },
		
		{ "id": 11, "name": "Produit 11", "category": "Catégorie 3", "price": 13 },
		{ "id": 12, "name": "Produit 12", "category": "Catégorie 3", "price": 16 },
		{ "id": 13, "name": "Produit 13", "category": "Catégorie 3", "price": 19 },
		{ "id": 14, "name": "Produit 14", "category": "Catégorie 3", "price": 21 },
		{ "id": 15, "name": "Produit 15", "category": "Catégorie 3", "price": 24 }]';
	   
	    if ($filtre) {
	      $data = json_decode($flux, true); 
	    	
		$res = array_filter($data, function($obj) use ($filtre)
		{ 
		    return strpos($obj["titre"], $filtre) !== false;
		});
		$response->getBody()->write(json_encode(array_values($res)));
	    } else {
		 $response->getBody()->write($flux);
	    }

	    return $response;
	}

	// API Nécessitant un Jwt valide
	function getCatalogue (Request $request, Response $response, $args) {
	    $flux = '[{"titre":"linux","ref":"001","prix":"20"},{"titre":"java","ref":"002","prix":"21"},{"titre":"windows","ref":"003","prix":"22"},{"titre":"angular","ref":"004","prix":"23"},{"titre":"unix","ref":"005","prix":"25"},{"titre":"javascript","ref":"006","prix":"19"},{"titre":"html","ref":"007","prix":"15"},{"titre":"css","ref":"008","prix":"10"}]';
	    $data = json_decode($flux, true); 
	    
	    $response->getBody()->write(json_encode($data));
	    
	    return $response;
	}

	function optionsUtilisateur (Request $request, Response $response, $args) {
	    
	    // Evite que le front demande une confirmation à chaque modification
	    $response = $response->withHeader("Access-Control-Max-Age", 600);
	    
	    return addHeaders ($response);
	}

	// API Nécessitant un Jwt valide
	function getUtilisateur (Request $request, Response $response, $args) {
	    global $entityManager;
	    
	    $payload = getJWTToken($request);
	    $login  = $payload->userid;
	    
	    $utilisateurRepository = $entityManager->getRepository('Utilisateurs');
	    $utilisateur = $utilisateurRepository->findOneBy(array('login' => $login));
	    if ($utilisateur) {
		$data = array('nom' => $utilisateur->getNom(), 'prenom' => $utilisateur->getPrenom());
		$response = addHeaders ($response);
		$response = createJwT ($response);
		$response->getBody()->write(json_encode($data));
	    } else {
		$response = $response->withStatus(404);
	    }

	    return $response;
	}

	// APi d'authentification générant un JWT
	function postLogin (Request $request, Response $response, $args) {   
	    global $entityManager;
	    $err=false;
	    $body = $request->getParsedBody();
	    $login = $body ['login'] ?? "";
	    $pass = $body ['password'] ?? "";

	    if (!preg_match("/[a-zA-Z0-9]{1,20}/",$login))   {
		$err = true;
	    }
	    if (!preg_match("/[a-zA-Z0-9]{1,20}/",$pass))  {
		$err=true;
	    }
	    if (!$err) {
		$utilisateurRepository = $entityManager->getRepository('Utilisateurs');
		$utilisateur = $utilisateurRepository->findOneBy(array('login' => $login, 'password' => $pass));
		if ($utilisateur and $login == $utilisateur->getLogin() and $pass == $utilisateur->getPassword()) {
		    $response = addHeaders ($response);
		    $response = createJwT ($response);
		    $data = array('nom' => $utilisateur->getNom(), 'prenom' => $utilisateur->getPrenom());
		    $response->getBody()->write(json_encode($data));
		} else {          
		    $response = $response->withStatus(403);
		}
	    } else {
		$response = $response->withStatus(500);
	    }

	    return $response;
	}

