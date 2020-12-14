<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProgramType;
use App\Entity\Program;
use App\Entity\Season;
use App\Entity\Episode;
use App\Service\Slugify;


/**
 * @Route("/programs", name="program_")
 */
class ProgramController extends AbstractController
{
    /**
     * Show all rows from Program’s entity
     *
     * @Route("/", name="index")
     * @return Response A response instance
     */
    public function index(): Response
    {
      $programs = $this->getDoctrine()
      ->getRepository(Program::class)
      ->findAll();
    
      return $this->render(
        'program/index.html.twig',
        ['programs' => $programs]);
    }

     /**
     * The controller for the program add form
     * @param Slugify $slugify
     * @Route("/new", name="new")
     */
    public function new(Request $request, Slugify $slugify) : Response
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();

                $slug = $slugify->generate($program->getTitle());
                $program->setSlug($slug);

                $entityManager->persist($program);
                $entityManager->flush();
                return $this->redirectToRoute('program_index');
                }    
                     
        return $this->render('program/new.html.twig', [
            "form" => $form->createView(),
        ]);
    }



     /**
     * Getting a program by slug
     *
     * @Route("/{slug}", name="show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"slug": "slug"}})
     * @return Response
     */
    public function show(Program $program):Response
    {      
        $seasons = $program->getSeasons();          

        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : '.($program).' found in program\'s table.'
            );
        }
        
        return $this->render('program/show.html.twig', [
            'program' => $program, 
            'seasons' => $seasons,
        ]);
    }

    /**
     * Getting a season 
     *
     * @Route("/{slug}/seasons/{seasonId}", name="season_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"slug": "slug"}})
     * @ParamConverter("seasons", class="App\Entity\Season", options={"mapping": {"seasonId": "id"}})
     * @return Response
     */
    public function showSeason(Program $program, Season $seasons) :Response
    {       
        $episodes = $seasons->getEpisodes(); 

        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'seasons' => $seasons,
            'episodes' => $episodes
        ]);

    }

        /**
     * Getting an episode
     *
     * @Route("/{slug}/seasons/{seasonId}/episodes/{episodeId}", name="episode_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"slug": "slug"}})
     * @ParamConverter("seasons", class="App\Entity\Season", options={"mapping": {"seasonId": "id"}})
     * @ParamConverter("episodes", class="App\Entity\Episode", options={"mapping": {"episodeId": "id"}})

     * @return Response
     */
    public function showEpisode(Program $program, Season $seasons, Episode $episodes) :Response
    {     
        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'seasons' => $seasons,
            'episodes' => $episodes
        ]);
    }

}