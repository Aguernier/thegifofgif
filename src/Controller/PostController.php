<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CommentType;
use App\Entity\Comment;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostController extends AbstractController
{
    /**
     * @Route("/profile/post/list", name="post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository): Response
    {
        $user = $this->getUser();

        return $this->render('post/index.html.twig', [
            'posts' => $user->getPosts(),
        ]);
    }

    /**
     * @Route("/profile/post/new", name="post_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $post->setUser($this->getUser());
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('post_index');
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/post/{id}", name="post_show", methods={"GET","POST"})
     */
    public function show(Post $post, Request $request): Response
    {
        $comments = $post->getComments();
        $commentForm = null; 
        if ($this->getUser()) {
            $comment = new Comment();
            $commentForm = $this->createForm(CommentType::class, $comment);
            $commentForm->handleRequest($request);
    
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $comment->setUser($this->getUser());
                $comment->setPost($post);
                $em = $this->getDoctrine()->getManager();
                $em->persist($comment);
                $em->flush();
                return $this->redirectToRoute('post_show', [
                    'id' => $post->getId(),
                ]);
            }
        }

        if ($commentForm !== null) {
            $commentForm = $commentForm->createView();
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'commentForm' => $commentForm,
            'comments' => $comments
        ]);
    }

    /**
     * @Route("/profile/post/{id}/edit", name="post_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Post $post): Response
    {
        if (!$this->isMinePost($post)) {
            return $this->redirectToRoute('home');
        }
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('post_index', [
                'id' => $post->getId(),
            ]);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile/post/{id}", name="post_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Post $post): Response
    {
        if (!$this->isMinePost($post)) {
            return $this->redirectToRoute('home');
        }
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('post_index');
    }

    private function isMinePost(Post $post)
    {
        return $this->getUser() === $post->getUser();
    }
}
