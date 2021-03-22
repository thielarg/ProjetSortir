<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ParticipantController extends AbstractController
{
    /**
     * @Route("/participant/modifier", name="participant_modifier")
     */
    public function modifier(EntityManagerInterface $em, Request $request, UserPasswordEncoderInterface $encoder){
        $this->denyAccessUnlessGranted('ROLE_USER');
        $participant = $this->getUser();
        $modifForm = $this->createForm(ParticipantType::class, $participant);
        $modifForm->handleRequest($request);
        if ($modifForm->isSubmitted() && $modifForm->isValid()){
            if($modifForm->get('photo')->getData() != null){
                $file = $modifForm->get('photo')->getData();
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $file->move($this->getParameter('users_photos_directory'), $fileName);
                $participant->setPhoto($fileName);
            }
            $hash=$encoder->encodePassword($participant, $participant->getPassword());
            $participant->setPassword($hash);
            $em->persist($participant);
            $em->flush();
            $this->addFlash('success', 'Votre profil a été modifié');
            $this->redirectToRoute("participant_modifier");
        }

        return $this->render('participant/add.html.twig', [
            'form' => $modifForm->createView()
        ]);
    }

    /**
     * @Route("/participant/detail/{id}", name="participant_detail",
     *     requirements={"id"="\d+"}, methods={"GET|POST"})
     */
    public function details(EntityManagerInterface $entityManager, $id) {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $participant = $entityManager->getRepository(Participant::class)->find($id);
        return $this->render( 'participant/detail.html.twig', [
            'participant' => $participant
        ]);
    }
}
