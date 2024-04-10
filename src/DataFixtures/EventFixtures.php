<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Event;

class EventFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $event = new Event();
        $event->setName('Event Past Due');
        $event->setDescription('This is finished already');
        $event->setLocation('Not animore');
        $event->setSlots(2);
        $event->setEndDate(new \DateTime('-30 days'));

        $manager->persist($event);

        for ($i = 0; $i < 10; $i++) {
            $event = new Event();
            $event->setName('Event ' . $i);
            $event->setDescription('Description for Event ' . $i);
            $event->setLocation('Location ' . $i);
            $event->setSlots(rand(1, 10));
            $event->setEndDate(new \DateTime('+' . rand(1, 30) . ' days'));

            $manager->persist($event);
        }

        $manager->flush();
    }
}