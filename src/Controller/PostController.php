<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PostRepository $postRepo): Response
    {
        $calcul = 2 * 10;

        $string = strtolower('Y A PERSONNE ICI, ON EST QUE DEUX');

        $arrayPosts = $postRepo->findAll();

        // dd($arrayPosts);

        return $this->render('post/index.html.twig', ['resultat' => $calcul, 'phrase' => $string, 'posts' => $arrayPosts]);
    }

    #[Route('/create', name: 'app_post_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $post = new Post();
        $formulaire = $this->createForm(PostType::class, $post);
        $formulaire->handleRequest($request);
        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->renderForm('post/create.html.twig', ['form' => $formulaire]);
    }

    #[Route('/post/{id}', name: 'app_post_details')]
    public function details(Post $post): Response
    {
        dd($post);

        return $this->render('post/index.html.twig', []);
    }
}