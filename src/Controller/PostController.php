<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PostRepository $postRepo): Response
    {
        

        $arrayPosts = $postRepo->findBy([], ['createdAt' => 'DESC']);

        // dd($arrayPosts);

        return $this->render('post/index.html.twig', ['posts' => $arrayPosts]);
    }

    #[Route('/post/{id<\d+>}', name: 'app_post_details',  methods : ['GET|POST'])]

    public function details(Post $post, Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $user = $security->getUser();
            $comment->setUser($user);

            $post->addComment($comment);
            $em->persist($comment);
            $em->flush();
        }

        $comments = $post->getComments();

        return $this->renderForm('post/details.html.twig', ['post' => $post, 'comments' => $comments, 'form' => $form]);
    }

    
    #[Route('/post/create', name: 'app_post_create')]
    #[IsGranted('ROLE_USER', message: 'You need to be logged-in to access this resource')]
    public function create(Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $post = new Post();
        $formulaire = $this->createForm(PostType::class, $post);
        $formulaire->handleRequest($request);
        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $user = $security->getUser(); #créer une var user qui récupére les infos de l'utilisateur
            $post->setUser($user); #on assigne le user connecté comme auteur du post
            $em->persist($post); # mets en cache les opérations d'insert/update d'un objet pour effectuer une transaction
            $em->flush(); #execute toutes les operations mises en cache (transactions sql)

            return $this->redirectToRoute('app_home');
        }

        return $this->renderForm('post/create.html.twig', ['form' => $formulaire, 'action' => 'Créer']);
    }

    #[Route('/post/edit/{id<\d+>}', name: 'app_post_edit')]
    #[IsGranted('ROLE_USER', message: 'You need to be logged-in to access this resource')]
    public function edit(Post $post, Request $request, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        if($user === $post->getUser())
        {
            $formulaire = $this->createForm(PostType::class, $post);
            $formulaire->handleRequest($request);
            if ($formulaire->isSubmitted() && $formulaire->isValid()) 
            {
                $em->flush();
                
                return $this->redirectToRoute('app_home');
            }
            return $this->renderForm('post/create.html.twig', ['form' => $formulaire, 'action' => 'Mettre à jour']);
        }
        
        return $this->redirectToRoute('app_home');
    }

    #[Route('/post/delete/{id<\d+>}', name: 'app_post_delete', methods : ['POST'])]
    #[IsGranted('ROLE_USER', message: 'You need to be logged-in to access this resource')]
    public function delete(Request $request, Post $post, EntityManagerInterface $em, Security $security): Response
    {
        $user = $security->getUser();
        if($user === $post->getUser())
        {
            if($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token')))
            {
                $em->remove($post);
                $em->flush();

                return $this->redirectToRoute('app_home');
            }
            
            return $this->render('post/delete.html.twig');
        }
        return $this->redirectToRoute('app_home');
    }

    #[Route('/like/{id}', name: 'app_post_like')]
    #[IsGranted('ROLE_USER', message: 'You need to be logged-in to access this resource')]
    public function like(Post $post, Security $security, EntityManagerInterface $em): Response
    {
        $user = $security->getUser();
        //? Si mon user a déja liké le post, alors il fait parti de l'array qui contient les post.
        if ($post->getLikes()->contains($user)) {
            //? Il faut l'en enlever
            $post->removeLike($user);
            $em->flush();

            return $this->redirectToRoute('app_post_details', ['id' => $post->getId()]);
        }
        //? Sinon il faut le rajouter
        $post->addLike($user);
        $em->flush();

        return $this->redirectToRoute('app_post_details', ['id' => $post->getId()]);
    }

    #[Route('/like/{id}/home', name: 'app_post_likehome')]
    #[IsGranted('ROLE_USER', message: 'You need to be logged-in to access this resource')]
    public function likeFromHome(Post $post, Security $security, EntityManagerInterface $em): Response
    {
        $user = $security->getUser();
        //? Si mon user a déja liké le post, alors il fait parti de l'array qui contient les post.
        if ($post->getLikes()->contains($user)) {
            //? Il faut l'en enlever
            $post->removeLike($user);
            $em->flush();

            return $this->redirectToRoute('app_home');
        }
        //? Sinon il faut le rajouter
        $post->addLike($user);
        $em->flush();

        return $this->redirectToRoute('app_home');
    }

}