<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Post;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        $page = $request->query->getInt('page') === 0 ? 1 : $request->query->getInt('page');
        $postRepo = $this->getDoctrine()->getRepository(Post::class);
        $posts = $paginator->paginate(
            $postRepo->findAllOrderByDateQuery(),
            $page,
            5
        );
    
        return $this->render('home/index.html.twig', [
            'posts' => $posts
        ]);
    }
}
