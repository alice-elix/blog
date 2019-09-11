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

use App\Entity\Articles;


class ArticleController extends AbstractController
{
    /**
     * @Route("/article", name="article")
     */
    
    //Déclarations des variables qui sont dans plusieurs fonction. A appeler sous la forme de $this->nomdelavariable (sans le$ devant la variable)
    public $maxSizeFile = 3 * 1000 * 1000; 
    public $allowMimes = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif']; 
    
    

    public function addArticle()
    {
	    if(!empty($_POST)){

            $safe = array_map('trim', array_map('strip_tags', $_POST));
            $errorsImg = [];
            if(!empty($_FILES) && $_FILES['img']['error'] != UPLOAD_ERR_NO_FILE){
                //var_dump($_FILES);
                
                if($_FILES['img']['error'] == UPLOAD_ERR_OK){ // L'utilisateur a envoyé une image
                    $img = Image::make($_FILES['img']['tmp_name'])->resize(50, 50); // Instancie l'image
                    if($img->filesize() > $this->maxSizeFile){
                        $errorsImg[] = 'Votre image ne doit pas excedér 3 Mo';
                    }
                    elseif(!v::in($this->allowMimes)->validate($img->mime())){
                        $errorsImg[] = 'Votre fichier n\'est pas une image valide';
                    }
               
                //////////////////// Upload de mon image 
                    // récupération de l'extension
                    $extension = substr($_FILES['img']['name'], strrpos($_FILES['img']['name'], '.'));
                    // un nom en aléatoire sur 32 caractères
                    $newFilename = md5(uniqid(rand(), true)).$extension;
                    // Déplace le fichier temporaire vers son emplacement final
                    $img->save($_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR . 'uploads'. DIRECTORY_SEPARATOR . $newFilename);      
                }
            }
            else{
                $errorsImg[] = 'Vous devez obligatoirement choisir une image';
            }
			
            ///////////////////////////////////////// tableau d'erreur

			$errors = [                                                      
    			(!v::notEmpty()->stringType()->length(2, 255)->validate($safe['auteur'])) ? 'Le nom de l\'auteur ou de l\'autrice doit contenir entre 2 et 255 caractères' : null,
    			(!v::notEmpty()->stringType()->length(2, 255)->validate($safe['titre'])) ? 'Le titre doit contenir entre 2 et 255 caractères' : null,
    			(!v::notEmpty()->stringType()->length(2, 255)->validate($safe['categorie'])) ? 'La categorie doit contenir entre 2 et 255 caractères' : null,
    			(!v::notEmpty()->stringType()->length(2)->validate($safe['contenu'])) ? 'Le contenu doit contenir entre 2 et 255 caractères' : null,
       
			];

			$errors = array_filter($errors);

			if(count($errors) == 0 && count($errorsImg) ==0){
				$success = true;
                /////////////////////////////////////////// ajout bdd /////////////////////////////////////
                $entityManager = $this->getDoctrine()->getManager();

                $article = new Articles();
                $article->setAuteur($safe['auteur'])
                        ->setTitre($safe['titre'])
                        ->setCategorie($safe['categorie'])
                        ->setContenu($safe['contenu'])
                        ->setDatePublication(new \DateTime('now'));
                if(isset($newFilename)){
                    $article->setImg($newFilename);

                }

                // tell Doctrine you want to (eventually) save the Product (no queries yet)
                $entityManager->persist($article);

                // actually executes the queries (i.e. the INSERT query)
                $entityManager->flush();
			}
			else {
				$errorsForm = implode('<br>', $errors).'<br>'.implode('<br>', $errorsImg);
                //$errorsFormImg = ;
			}
		}

        return $this->render('article/addArticle.html.twig', [
            'controller_name' 	=> $safe['auteur'] ?? 'You',
            'avatar'            => '✅',
            'success'	 		=> $success ?? false,
            'errors' 			=> $errorsForm ?? [],
            //'errorsImg'         => $errorsFormImg ?? [],
            'donnees_saisies'   => $safe ?? [],
            'maxSizeFile'       => $this->maxSizeFile,

            
        ]);
    }

    public function editArticle($id)
    {

        // Récupération de l'article
        $entityManager = $this->getDoctrine()->getManager();
        // Permet de chercher l'article donnée en id via le repository
        $articleFound = $entityManager->getRepository(Articles::class)->find($id);
            if (!$articleFound) {
                throw $this->createNotFoundException(
                'Aucun article disponible à la page '.$id
                    );
            }

                
        if(!empty($_POST)){
            $errorsImg = [];
            $safe = array_map('trim', array_map('strip_tags', $_POST));

            //////////////////// Upload de mon image 
            if(!empty($_FILES) && $_FILES['img']['error'] != UPLOAD_ERR_NO_FILE){ //verification qu'il y a une nouvelle image proposée
                // var_dump($_FILES);
                
                if($_FILES['img']['error'] == UPLOAD_ERR_OK){ // L'utilisateur a envoyé une image
                   
                   $img = Image::make($_FILES['img']['tmp_name'])->resize(50, 50); // Instancie l'image
                    if($img->filesize() > $this->maxSizeFile){
                        $errorsImg[] = 'Votre image ne doit pas excedér 3 Mo';
                    }
                    elseif(!v::in($this->allowMimes)->validate($img->mime())){
                        $errorsImg[] = 'Votre fichier n\'est pas une image valide';
                    }

                    // récupération de l'extension
                    $extension = substr($_FILES['img']['name'], strrpos($_FILES['img']['name'], '.'));
                    // un nom en aléatoire sur 32 caractères
                    $newFilename = md5(uniqid(rand(), true)).$extension;
                    // Déplace le fichier temporaire vers son emplacement final
                    $img->save($_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR . 'uploads'. DIRECTORY_SEPARATOR . $newFilename);  
                }
            }
            
            ///////////////////////////////////////// tableau d'erreur

            $errors = [                                                      
                (!v::notEmpty()->stringType()->length(2, 255)->validate($safe['auteur'])) ? 'Le nom de l\'auteur ou de l\'autrice doit contenir entre 2 et 255 caractères' : null,
                (!v::notEmpty()->stringType()->length(2, 255)->validate($safe['titre'])) ? 'Le titre doit contenir entre 2 et 255 caractères' : null,
                (!v::notEmpty()->stringType()->length(2, 255)->validate($safe['categorie'])) ? 'La categorie doit contenir entre 2 et 255 caractères' : null,
                (!v::notEmpty()->stringType()->length(2)->validate($safe['contenu'])) ? 'Le contenu doit contenir entre 2 et 255 caractères' : null,
       
            ];

            $errors = array_filter($errors);
            if(count($errors) == 0 && count($errorsImg) ==0){
            /////////////////////////////////////////// upload de l'article dans la bdd /////////////////////////////////////

                
                $articleFound->setAuteur($safe['auteur'])
                                ->setTitre($safe['titre'])
                                
                                ->setCategorie($safe['categorie'])
                                ->setContenu($safe['contenu'])
                                ->setDateModification(new \DateTime('now'));

                if(isset($newFilename)){
                    $articleFound->setImg($newFilename);
                } 
                else {
                    $articleFound->setImg($articleFound->getImg());
                }

                // tell Doctrine you want to (eventually) save the Product (no queries yet)
                //$entityManager->persist($articleFound); pas besoin de persiste pour une mise à jour

                // actually executes the queries (i.e. the INSERT query)
                $entityManager->flush();

                $success = true;
            }
            else {
                $errorsForm = implode('<br>', $errors).'<br>'.implode('<br>', $errorsImg);
            }
        }


    	return $this->render('article/editArticle.html.twig', [
            'mon_prenom' 	    => 'Elix',
            'avatar'            => '✅',
            'success'           => $success ?? false,
            'errors'            => $errorsForm ?? [],
            'maxSizeFile'       => $this->maxSizeFile,
            'articleTrouve'     => $articleFound,
            'donnees_saisies'   => $safe ?? [],
            'dateModif'         => $articleFound->getDateModification(),
            
        ]);
    }

    public function listArticle()
    {
        // Récupération de l'article
            $entityManager = $this->getDoctrine()->getManager();
            // Permet de chercher l'article donnée en id via le repository
            $articleFound = $entityManager->getRepository(Articles::class)->findAll();
                

        //ce qui va s'afficher sur la page view Article
        return $this->render('article/list.html.twig', [
            'mon_prenom'    => 'You',
            'avatar'        => '✅',
            'articlesTrouves' => $articleFound, 
            //'id'            => $articleFound->getId($id) 
        ]);
    }

    public function viewArticle($id)
    {
        // Récupération de l'article
        $entityManager = $this->getDoctrine()->getManager();
        // Permet de chercher l'article donnée en id via le repository
        $articleFound = $entityManager->getRepository(Articles::class)->find($id);
            if (!$articleFound) {
                throw $this->createNotFoundException(
                'Aucun article disponible à la page '.$id
                    );
            }
        //ce qui va s'afficher sur la page view Article
    	return $this->render('article/view.html.twig', [
            'mon_prenom' 	=> 'Toi',
            'articleTrouve' => $articleFound,
            'dateModif'     => $articleFound->getDateModification(),

        ]);
    }


    public function deleteArticle($id)
    {
    	return $this->render('article/deleteArticle.html.twig', [
            'mon_prenom' 	  => 'Alice',   
        ]);
    }
}
