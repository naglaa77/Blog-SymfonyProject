<?php

namespace App\Controller;

use DateTime;
use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PostController extends AbstractController
{
    #[Route('/', name: 'post')]
    public function index(PostRepository $postRepository, PaginatorInterface $paginator,Request $request ): Response
    {
    
        $posts = $paginator->paginate(
    
            $postRepository->findAll(), /* query NOT result */
    
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
        //dd($posts);
        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }
    

    #[Route('/post/new', name: 'post_new')]

    public function create(Request $request, ManagerRegistry $doctrine) {
        
        $post = new Post(); // represent table post in db so i take instance of it
        $form = $this->createForm(PostType::class,$post);// data type, data
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setCreatedAt(new DateTime());
            $entityManager = $doctrine->getManager();           
            $entityManager->persist($post);
            $entityManager->flush();

           return  $this->redirectToRoute('post');
        }

        return $this->render('post/new.html.twig',[
            'form'=> $form->createView()

        ]);
    }

    #[Route('/post/{id}', name: 'post_show')]
    public function show(Request $request, PostRepository $postRepository): Response
    {
        $postId = $request->attributes->get('id');
       
        $post = $postRepository->find($postId);
        
        return $this->render('post/show.html.twig',[
            'post' => $post,
        ]);
    }


}
