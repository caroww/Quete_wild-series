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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Form\SearchProgramFormType;
use App\Repository\ProgramRepository;

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
    public function index(Request $request, ProgramRepository $programRepository): Response
    { 
      $form = $this->createForm(SearchProgramFormType::class);
      $form->handleRequest($request);
    
      if ($form->isSubmitted() && $form->isValid()) {
        $search = $form->getData()['search'];
        $programs = $programRepository->findLikeName($search);
    } else {
        $programs = $programRepository->findAll();
    }

/*       $programs = $this->getDoctrine()
      ->getRepository(Program::class)
      ->findAll(); */
    
      return $this->render(
        'program/index.html.twig',
        ['programs' => $programs,
        "form" => $form->createView()]);
    }

     /**
     * The controller for the program add form
     * @param Slugify $slugify
     * @Route("/new", name="new")
     */
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer) : Response
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();

                $slug = $slugify->generate($program->getTitle());
                $program->setSlug($slug);

                $program->setOwner($this->getUser());

                $entityManager->persist($program);
                $entityManager->flush();

                $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('your_email@example.com')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('program/newProgramEmail.html.twig', ['program' => $program]));

                $mailer->send($email);

                
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
    public function showEpisode(Program $program, Season $seasons, Episode $episodes, Request $request) :Response
    {   
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        $user=$this->getUser();
        $comment->setEpisode($episodes);
        $comment->setAuthor($user);
 

         if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('comment_index');
        }   

        $comments=$this->getDoctrine()
                       ->getRepository(Comment::class)
                       ->findBy(['episode'=>$episodes]);                      

        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'seasons' => $seasons,
            'episodes' => $episodes,
            'comments'=> $comments,
            'form'=> $form->createView()
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Program $program): Response
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
            if (($this->getUser() != $program->getOwner()))
            {
                throw new AccessDeniedException('Only the owner can edit the program!');
            }

        }

        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('program_index');
        }

        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
    }

}