<?php

namespace App\Controller;

use DateTime;
use App\Entity\Post;
use App\Form\PostType;
use App\Entity\Comment;
use App\Form\CommentType;
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
        
        if (!$this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $post = new Post(); // represent table post in db so i take instance of it
        $form = $this->createForm(PostType::class,$post);// data type, data
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setCreatedAt(new DateTime());
            $entityManager = $doctrine->getManager();           
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash(
            'success',
            'Your message is added'
            );

           return  $this->redirectToRoute('post');
        }

        return $this->render('post/new.html.twig',[
            'form'=> $form->createView()

        ]);
    }



    #[Route('/post/{id}', name: 'post_show')]
    public function show(Request $request, PostRepository $postRepository,ManagerRegistry $doctrine): Response
    {
        $postId = $request->attributes->get('id');
       
        $post = $postRepository->find($postId);

        // for comments
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class,$comment);//it take the from and entity
        $commentForm->handleRequest($request);
        
        $this->addComment($commentForm, $comment, $post, $doctrine);
    

        return $this->render('post/show.html.twig',[
            'post' => $post,
            'commentForm'=>$commentForm->createView()
        ]);
    }

    // modify new post (edit)
    #[Route('/post/{id}/edit', name: 'post_edit')]

    public function edit (Post $post, Request $request,ManagerRegistry $doctrine) { // it take two param the text and request
        
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();           
            $entityManager->persist($post);
            $entityManager->flush();
            
            $this->addFlash(
            'success',
            'Your message is edited'
            );
            return $this->redirectToRoute('post_show',['id'=>$post->getId()]);
        }
        return $this->render('post/edit.html.twig',[
            'post'=>$post,
            'editForm'=>$form->createView()
    
        ]);
    }

    // add comment
    private function addComment($commentForm, $comment, $post,ManagerRegistry $doctrine) {
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            
            $comment->setCreatedAt(new DateTime());
            $comment-> setPost($post);
            $entityManager = $doctrine->getManager();           
            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash(
            'success',
            'Your comment is added'
            );
            return $this->redirectToRoute('post_show',['id'=>$post->getId()]);
        }

    }
}
