<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('location')
            ->add('slots')
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $eventData = $event->getData();
                $oldSlots = $event->getForm()->getData()->getSlots();
                $newSlots = $eventData->getSlots();
                $usersSuscibed = $event->getForm()->getData()->getUsers()->count();

                if ($newSlots < $usersSuscibed) {
                    $event->getForm()->get('slots')->addError(new FormError('New slots value cannot be less than the current suscribed users. (currently '.$usersSuscibed.')'));
                }
            })
            ->add('endDate')
            // Add more fields as needed
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}