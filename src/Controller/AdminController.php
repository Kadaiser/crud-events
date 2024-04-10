<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

use App\Repository\EventRepository;

class AdminController extends AbstractController
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/admin', name: 'admin')]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $this->doctrine->getRepository(Event::class)->findAll();

        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/event/create', name: 'admin_event_create')]
    public function create(Request $request): Response
    {
        $event = new Event();

        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        return $this->render('admin/event/create.html.twig', [
            'eventCreateform' => $form->createView(),
        ]);

        return $this->render('admin/create.html.twig');
    }

    /**
     * 
     * edit the event by id.
     * 
     * @Route("/event/edit/{id}", name="admin_event_edit")
     * @param Event $event
     * @return Response
     */
    #[Route('/admin/event/edit/{id}', name: 'admin_event_edit')]
    public function edit(Event $event, Request $request): Response
    {
        // TODO: Add event editing logic here

        return $this->render('admin/event/edit.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * 
     * delete the event by id.
     * 
     * @Route("/event/delete/{id}", name="app_event_delete")
     * @param Event $event
     * @return Response
     */
    #[Route('admin/event/delete/{id}', name: 'admin_event_delete')]
    public function delete(Event $event): Response
    {
        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($event);
        $entityManager->flush();

        return $this->redirectToRoute('app_event');
    }

}