<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Event;

use App\Entity\User;
use App\Repository\EventRepository;


use App\Form\EventType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EventController extends AbstractController
{

    private $doctrine;

    //constant of threshold percentage
    public const MAX_THRESOLD_PERCENT = 80;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * fetch all events from the database and passing them to the view.
     *
     * @return Response
     */
    #[Route('/event', name: 'app_event')]
    public function index(Request $request, EventRepository $eventRepository): Response
    {
        //$events = $this->doctrine->getRepository(Event::class)->findAll();
        /*
        $form = $this->createForm(EventType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filterData = $form->getData();

            $events = $this->doctrine
                ->getRepository(Event::class)
                ->filterBy($filterData);
        } else {
            $events = $this->doctrine
                ->getRepository(Event::class)
                ->findAll();
        }
        */
        $name = $request->query->get('name');

        if ($name) {
            $events = $eventRepository->findByName($name);
        } else {
            $events = $eventRepository->findAll();
        }

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    /**
     * 
     * fetch the event by id and pass it to the view.
     * 
     * @Route("/event/{id}", name="app_event_show")
     * @param Event $event
     * @return Response
     */
    #[Route('/event/{id}', name: 'app_event_show')]
    public function show(Event $event): Response
    {
        $users = $event->getUsers();
        
        return $this->render('event/show.html.twig', [
            'event' => $event,
            'users' => $users,
        ]);
    }

    /**
     * 
     * add the current user to the event's attendees.
     * first check if the user is already suscribe for the event or if the event is full. 
     * If neither of these conditions is true, add the user to the event's attendees and save the event.
     * 
     * @param User $user
     * @param Event $event
     * @return Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    #[Route('/event/suscribe/{event}/{user}', name: 'user_event_subscribe')]
    public function suscribe(Event $event, User $user): Response
    {
        // Check if the event date is in the past
        $now = new \DateTime();
        if ($event->getEndDate() < $now) {
            $this->addFlash('error', 'This event is off date.');
        }

        if ($event->getUsers()->contains($user)) {
            $this->addFlash('error', 'You are already suscribe for this event.');
        } elseif ($event->getSlots() <= count($event->getUsers())) {
            $this->addFlash('error', 'This event is full.');
        } else {
            //check the capacity of the event, threshold is 80%
            $this->checkCapacity($event, self::MAX_THRESOLD_PERCENT);

            $event->addUser($user);
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'You have successfully suscribe for the event.');
        }

        return $this->redirectToRoute('app_event');
    }

    /* 
    * @param User $user
    * @param Event $event
    * @return Response
    * @throws \Doctrine\ORM\ORMException
    * @throws \Doctrine\ORM\OptimisticLockException
    * @throws \Doctrine\ORM\ORMInvalidArgumentException
    */
    #[Route('/event/unsuscribe/{event}/{user}', name: 'user_event_unsubscribe')]
   public function unsuscribe(Event $event, User $user): Response
   {
         if (!$event->getUsers()->contains($user)) {
              $this->addFlash('error', 'You are not registered for this event.');
         } else {
              $event->removeUser($user);
              $entityManager = $this->doctrine->getManager();
              $entityManager->persist($event);
              $entityManager->flush();
    
              $this->addFlash('success', 'You have successfully unregistered for the event.');
         }
    
         return $this->redirectToRoute('app_event');
    }



    /**
     * Calculate the capacity percentage of the event after a user registers. 
     * If the capacity is at $percent or more, send a notification
     */
    #[Route('/event/register/{id}', name: 'app_event_register')]
    public function checkCapacity(Event $event, int $percent): Bool
    {
        $capacity = $event->getSlots();
        $users = count($event->getUsers());
        $percentage = ($users / $capacity) * 100;

        if ($percentage >= $percent) {
            //$this->sendNotification($event);
            return true;
        }
        return false;
    }

}
