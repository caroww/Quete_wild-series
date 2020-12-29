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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

        /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Comment $comment): Response
    {   
        $user=$this->getUser();
        $isadmin=false;
        $listeDroits=$user->getRoles();      
 
        foreach ($listeDroits as $value) {
            if ($value=='ROLE_ADMIN')
            {
                $isadmin=true;
            }
        }
        if(!$isadmin)
        {
            if (($this->getUser() != $comment->getAuthor()))
            {
                throw new AccessDeniedException('Only the owner can edit the comment !');
            }

        }
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('comment_index');
        }

        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="delete", methods={"DELETE"})
     */
    public function delete(Request $request, Comment $comment): Response
    {
         if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('comment_index'); 
    }

}