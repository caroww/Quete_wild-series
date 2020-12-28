<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\User;
use App\Form\CommentType;


/**
 * @Route("/comment", name="comment_")
 */
class CommentController extends AbstractController
{
    /**
     * Show all rows from Comment’s entity
     *
     * @Route("/", name="index")
     * @return Response A response instance
     */
    public function index(): Response
    {
      $comments = $this->getDoctrine()
      ->getRepository(Comment::class)
      ->findAll();
    
      return $this->render(
        'comment/index.html.twig',
        ['comments' => $comments,
       ]);
    }

}