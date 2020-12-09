<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Program;
use App\Entity\Actor;


/**
 * @Route("/actors", name="actor_")
 */
class ActorController extends AbstractController
{
    /**
     * Show all rows from Actor’s entity
     *
     * @Route("/", name="index")
     * @return Response A response instance
     */
    public function index(): Response
    {
      $actors = $this->getDoctrine()
      ->getRepository(Actor::class)
      ->findAll();
    
      return $this->render(
        'actor/index.html.twig',
        ['actors' => $actors]);
    }

     /**
     * Getting an actor by id
     *
     * @Route("/show/{actorId<^[0-9]+$>}", name="show")
     * @ParamConverter("actors", class="App\Entity\Actor", options={"mapping": {"actorId": "id"}})

     * @return Response
     */
    public function show(Actor $actors):Response
    {      
        $programs = $actors->getPrograms();          

        if (!$actors) {
            throw $this->createNotFoundException(
                'No program with id : '.($actors).' found in program\'s table.'
            );
        }
        
        return $this->render('actor/show.html.twig', [
            'programs' => $programs, 
            'actors' => $actors,
        ]);
    }

}