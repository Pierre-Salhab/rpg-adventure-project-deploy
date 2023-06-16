<?php

namespace App\Controller;

use App\Entity\HeroClass;
use App\Form\HeroClassType;
use App\Repository\HeroClassRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/hero-class")
 */
class HeroClassController extends AbstractController
{
    /**
     * @Route("/", name="app_hero_class_index", methods={"GET"})
     */
    public function index(HeroClassRepository $heroClassRepository): Response
    {
        return $this->render('hero_class/index.html.twig', [
            'hero_classes' => $heroClassRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_hero_class_new", methods={"GET", "POST"})
     */
    public function new(Request $request, HeroClassRepository $heroClassRepository): Response
    {
        $heroClass = new HeroClass();
        $form = $this->createForm(HeroClassType::class, $heroClass);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $heroClassRepository->add($heroClass, true);

            return $this->redirectToRoute('app_hero_class_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('hero_class/new.html.twig', [
            'hero_class' => $heroClass,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_hero_class_show", methods={"GET"})
     */
    public function show(HeroClass $heroClass): Response
    {
        return $this->render('hero_class/show.html.twig', [
            'hero_class' => $heroClass,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_hero_class_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, HeroClass $heroClass, HeroClassRepository $heroClassRepository): Response
    {
        $form = $this->createForm(HeroClassType::class, $heroClass);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $heroClassRepository->add($heroClass, true);

            return $this->redirectToRoute('app_hero_class_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('hero_class/edit.html.twig', [
            'hero_class' => $heroClass,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_hero_class_delete", methods={"POST"})
     */
    public function delete(Request $request, HeroClass $heroClass, HeroClassRepository $heroClassRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$heroClass->getId(), $request->request->get('_token'))) {
            $heroClassRepository->remove($heroClass, true);
        }

        return $this->redirectToRoute('app_hero_class_index', [], Response::HTTP_SEE_OTHER);
    }
}
