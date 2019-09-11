<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Respect\Validation\Validator as v;
use Intervention\Image\ImageManagerStatic as Image;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Users;

class UsersController extends AbstractController
{
    /**
     * @Route("/users", name="users")
     */

    //public $maxSizeFile = 3 * 1000 * 1000;
    //public $allowMimes = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif']; 


    public function addUser()
    {
       if(!empty($_POST)){

           	$safe = array_map('trim', array_map('strip_tags', $_POST));

	       	$entityManager = $this->getDoctrine()->getManager();
	       	$userFound = $entityManager->getRepository(Users::class)->findByEmail($safe['email']);
	       	//var_dump($userFound);


           // $errorsImg = [];
           // if(!empty($_FILES) && $_FILES['img']['error'] != UPLOAD_ERR_NO_FILE){
           //     //var_dump($_FILES);
               
           //     if($_FILES['img']['error'] == UPLOAD_ERR_OK){ // L'utilisateur a envoyé une image
           //         $img = Image::make($_FILES['img']['tmp_name'])->resize(50, 50); // Instancie l'image
           //         if($img->filesize() > $this->maxSizeFile){
           //             $errorsImg[] = 'Votre image ne doit pas excedér 3 Mo';
           //         }
           //         elseif(!v::in($this->allowMimes)->validate($img->mime())){
           //             $errorsImg[] = 'Votre fichier n\'est pas une image valide';
           //         }
              
           //     //////////////////// Upload de mon image 
           //         // récupération de l'extension
           //         $extension = substr($_FILES['img']['name'], strrpos($_FILES['img']['name'], '.'));
           //         // un nom en aléatoire sur 32 caractères
           //         $newFilename = md5(uniqid(rand(), true)).$extension;
           //         // Déplace le fichier temporaire vers son emplacement final
           //         $img->save($_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR . 'uploads'. DIRECTORY_SEPARATOR . $newFilename);      
           //     }
           // }
           // else{
           //     $errorsImg[] = 'Vous devez obligatoirement choisir une image';
           // }
			
           ///////////////////////////////////////// tableau d'erreur

			$errors = [                                                      
   			(!v::notEmpty()->length(2, 180)->email()->validate($safe['email'])) ? 'Votre email doit doit contenir de 2 à 180 caractères et être sous la forme "email@email.email"' : null,
   			(v::notEmpty()->validate($userFound)) ? 'Cet email est déjà utilisé pour un compte' : null,      
   			(!v::notEmpty()->length(6)->validate($safe['pwd'])) ? 'Votre mot de passe doit faire au moins 6 caractères' : null,
   			(!v::notEmpty()->identical($safe['pwd'])->validate($safe['conf-pwd'])) ? 'Le mot de passe et la confirmation doivent être identique' : null,  
   			//(v::exists()->validate($safe['email'])) ? 'Cet email est déjà utilisé pour un compte' : null,
   			(!v::notEmpty()->stringType()->length(2, 180)->validate($safe['role'])) ? 'Le rôle doit contenir de 2 à 180 caractères' : null,
			];

			$errors = array_filter($errors);

			if(count($errors) == 0 ){
			//&& count($errorsImg) ==0	// a rajouter si on met une image
				$success = true;
	               /////////////////////////////////////////// ajout bdd /////////////////////////////////////
				//$entityManager = $this->getDoctrine()->getManager();//
				///////////////////////////////////////////////////////

	           	$user = new Users();
	           	$user  	->setEmail($safe['email'])
	                   	->setPassword(password_hash($safe['pwd'], PASSWORD_DEFAULT))
	                   	->setUsername($safe['name'])
	                   	->setRoles($safe['role']);
	           	//         ->setDatePublication(new \DateTime('now'));
	           	// if(isset($newFilename)){
	           	//     $article->setImg($newFilename);

	           	// tell Doctrine you want to (eventually) save the Product (no queries yet)
	          	$entityManager->persist($user);

	           	// actually executes the queries (i.e. the INSERT query)
	           	$entityManager->flush();
				}
			else {
				$errorsForm = implode('<br>', $errors);
	           //$errorsFormImg = .'<br>'.implode('<br>', $errorsImg); // a remettre sur la ligne du haut si on ajoute des images
				}

			}

       return $this->render('users/addUser.html.twig', [
           'controller_name' 	=> $safe['auteur'] ?? 'You',
           'avatar'           => '✅',
           'success'	 		    => $success ?? false,
           'errors' 			    => $errorsForm ?? [],
           'donnees_saisies'  => $safe ?? [],
           //'maxSizeFile'       	=> $this->maxSizeFile,

                   
        ]);     
	}

	public function profil($id)
    {
        // Récupération de l'article
        $entityManager = $this->getDoctrine()->getManager();
        // Permet de chercher l'article donnée en id via le repository
        $userFound = $entityManager->getRepository(Users::class)->find($id);
            if (!$userFound) {
                throw $this->createNotFoundException(
                'Aucun profil disponible '
                    );
            }
        //ce qui va s'afficher sur la page view Article
    	return $this->render('users/profil.html.twig', [
            'mon_prenom' 	=> 'Toi',
            'profilTrouve' => $userFound,
            //'dateModif'     => $articleFound->getDateModification(),

        ]);
    }


        public function editUser($id)
        {
    	    $entityManager = $this->getDoctrine()->getManager();
    	    $userSelect = $entityManager->getRepository(Articles::class)->find($id);
            if (!$userSelect) {
                throw $this->createNotFoundException(
                'Aucun article disponible à la page '.$id
                    );
            }


           	if(!empty($_POST)){

               	$safe = array_map('trim', array_map('strip_tags', $_POST));

    	       	$userFound = $entityManager->getRepository(Users::class)->findByEmail($safe['email']);
    	       	//var_dump($userFound);


               // $errorsImg = [];
               // if(!empty($_FILES) && $_FILES['img']['error'] != UPLOAD_ERR_NO_FILE){
               //     //var_dump($_FILES);
                   
               //     if($_FILES['img']['error'] == UPLOAD_ERR_OK){ // L'utilisateur a envoyé une image
               //         $img = Image::make($_FILES['img']['tmp_name'])->resize(50, 50); // Instancie l'image
               //         if($img->filesize() > $this->maxSizeFile){
               //             $errorsImg[] = 'Votre image ne doit pas excedér 3 Mo';
               //         }
               //         elseif(!v::in($this->allowMimes)->validate($img->mime())){
               //             $errorsImg[] = 'Votre fichier n\'est pas une image valide';
               //         }
                  
               //     //////////////////// Upload de mon image 
               //         // récupération de l'extension
               //         $extension = substr($_FILES['img']['name'], strrpos($_FILES['img']['name'], '.'));
               //         // un nom en aléatoire sur 32 caractères
               //         $newFilename = md5(uniqid(rand(), true)).$extension;
               //         // Déplace le fichier temporaire vers son emplacement final
               //         $img->save($_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR . 'uploads'. DIRECTORY_SEPARATOR . $newFilename);      
               //     }
               // }
               // else{
               //     $errorsImg[] = 'Vous devez obligatoirement choisir une image';
               // }
    			
               ///////////////////////////////////////// tableau d'erreur

    			$errors = [                                                      
       			(!v::notEmpty()->length(2, 180)->email()->validate($safe['email'])) ? 'Votre email doit doit contenir de 2 à 180 caractères et être sous la forme "email@email.email"' : null,
       			(v::notEmpty()->validate($userFound)) ? 'Cet email est déjà utilisé pour un compte' : null,      
       			(!v::notEmpty()->length(6)->validate($safe['pwd'])) ? 'Votre mot de passe doit faire au moins 6 caractères' : null,
       			(!v::notEmpty()->identical($safe['pwd'])->validate($safe['conf-pwd'])) ? 'Le mot de passe et la confirmation doivent être identique' : null,  
       			//(v::exists()->validate($safe['email'])) ? 'Cet email est déjà utilisé pour un compte' : null,
       			(!v::notEmpty()->stringType()->length(2, 180)->validate($safe['role'])) ? 'Le rôle doit contenir de 2 à 180 caractères' : null,
    			];

    			$errors = array_filter($errors);

    			if(count($errors) == 0 ){
    			//&& count($errorsImg) ==0	// a rajouter si on met une image
    				$success = true;
    	               /////////////////////////////////////////// ajout bdd /////////////////////////////////////
    				//$entityManager = $this->getDoctrine()->getManager();//
    				///////////////////////////////////////////////////////

    	           	
    	           	$userFound	->setEmail($safe['email'])
    	                   		->setPassword(password_hash($safe['pwd'], PASSWORD_DEFAULT))
    	                   		->setUsername($safe['name'])
    	                   		->setRoles($safe['role']);
    	           	//         ->setDatePublication(new \DateTime('now'));
    	           	// if(isset($newFilename)){
    	           	//     $article->setImg($newFilename);

    	           	// tell Doctrine you want to (eventually) save the Product (no queries yet)
    	          	$entityManager->persist($user);

    	           	// actually executes the queries (i.e. the INSERT query)
    	           	$entityManager->flush();
    				}
    			else {
    				$errorsForm = implode('<br>', $errors);
    	           //$errorsFormImg = .'<br>'.implode('<br>', $errorsImg); // a remettre sur la ligne du haut si on ajoute des images
    				}

    			}

           return $this->render('users/editUser.html.twig', [
               'controller_name' 	=> $safe['auteur'] ?? 'You',
               'avatar'            	=> '✅',
               'success'	 		=> $success ?? false,
               'errors' 			=> $errorsForm ?? [],
               'donnees_saisies'   	=> $safe ?? [],
               'profilSelectionne'		=> $userSelect,
               //'maxSizeFile'       	=> $this->maxSizeFile,

                       
            ]);     
    	}
}
