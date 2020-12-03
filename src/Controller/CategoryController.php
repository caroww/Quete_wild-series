<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Category;
use App\Entity\Program;

/**
 * @Route("/categories", name="category_")
 */
class CategoryController extends AbstractController
{
    /**
     * Show all rows from Category’s entity
     *
     * @Route("/", name="index")
     * @return Response A response instance
     */
    public function index(): Response
    {
      $categories = $this->getDoctrine()
      ->getRepository(Category::class)
      ->findAll();

      return $this->render(
        'category/index.html.twig',
        ['categories' => $categories]);
    }

     /**
     * Getting a program by category
     *
     * @Route("/{categoryName}", name="show")
     * @return Response
     */
    public function show(string $categoryName):Response
    {
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findBy(['name' => $categoryName]);
            
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(['category' => $category],
                    ['id' => 'desc'],
                    3
                    );

        if (!$category) {
            throw $this->createNotFoundException(
                'No program with id : '.$categoryName.' found in program\'s table.'
            );
        }

        return $this->render('category/show.html.twig', [
            'programs' => $programs, 'category' => $category
        ]);
    }

}

