<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/participants", name="admin_liste_des_participants")
     */
    public function listerParticipants(Request $request, ParticipantRepository $participantRepository){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $participantsQuery = $participantRepository->createQueryBuilder('p');
        if ($request->query->get('recherche_terme')!=null){
            $recherche = $request->query->get('recherche_terme');
            $participantsQuery->andWhere('p.nom LIKE :recherche')
                ->setParameter("recherche",'%'.$recherche.'%')
                ->orWhere("p.username LIKE :recherche")
                ->setParameter("recherche",'%'.$recherche.'%')
                ->orWhere("p.prenom LIKE :recherche")
                ->setParameter("recherche",'%'.$recherche.'%');
        };
        $participantsQuery->orderBy('p.id')
            ->getQuery();

 /*       $participants = $paginator->paginate(
            $participantsQuery,
            $request->query->getInt('page', 1),
            10);
*/
        $participants = $participantsQuery;

        return $this->render("admin/listeParticipant.html.twig",[
            "participants" => $participants
        ]);
    }
    /**
     * @Route("/admin/inscrire", name="admin_inscrire")
     */
    public function inscrire(EntityManagerInterface $em, Request $request, UserPasswordEncoderInterface $encoder){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $participant = new Participant();
        $participant->setAdministrateur(false);
        $participant->setActif(true);
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);
        if ($form->isSubmitted()){
            $hash=$encoder->encodePassword($participant, 'password');
            $participant->setPassword($hash);
            $em->persist($participant);
            $em->flush();
            $this->addFlash('success', 'Un nouveau participant a été créé');
            return $this->redirectToRoute('admin_liste_des_participants');
        }
        return $this->render('admin/inscrire.html.twig', [
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route("/admin/{id}", name="admin_participant_detail", requirements={"id"="\d+"}, methods={"GET|POST"})
     */
    public function detailParticipant($id){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $participantRepo =$this->getDoctrine()->getRepository(Participant::class);
        $participant = $participantRepo->find($id);
        if ($participant == null){
            throw $this->createNotFoundException('Ce participant n\'existe pas !');
        }
        return $this->render('admin/detailParticipant.html.twig', [
            'participant' => $participant
        ]);
    }
    /**
     * @Route("/admin/activer", name="admin_activer")
     */
    public function activer(EntityManagerInterface $em, Request $request){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $participantid = $request->query->get('participantid');
        $participant = $em->getRepository(Participant::class)->find($participantid);
        $participant->setActif(true);
        $em->persist($participant);
        $em->flush();
        $this->addFlash('success', 'Le participant a été activé');
        return $this->redirectToRoute('admin_liste_des_participants');
    }
    /**
     * @Route("/admin/desactiver", name="admin_desactiver")
     */
    public function desactiver(EntityManagerInterface $em, Request $request){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $participantid = $request->query->get('participantid');
        $participant = $em->getRepository(Participant::class)->find($participantid);
        $participant->setActif(false);
        $em->persist($participant);
        $em->flush();
        $this->addFlash('success', 'Le participant a été désactivé');
        return $this->redirectToRoute('admin_liste_des_participants');
    }
    /**
     * @Route("/admin/supprimer", name="admin_supprimer")
     */
    public function supprimer(EntityManagerInterface $em, Request $request){
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $participantid = $request->query->get('participantid');
        $participant = $em->getRepository(Participant::class)->find($participantid);
        if ($participant == null){
            throw $this->createNotFoundException('Ce participant n\'existe pas !');
        }
        foreach ($participant->getSorties() as $sorties){
            $participant->removeSortie($sorties);
            $sorties->removeParticipant($participant);
            $em->persist($sorties);
        }
        $em->remove($participant);
        $em->flush();
        $this->addFlash('success', 'Le participant a bien été supprimé');
        return $this->redirectToRoute("admin_liste_des_participants");
    }

    /**
     * @Route("/admin/sortie/annuler", name="admin_annuler_sortie")
     */
    public function annulerSortie(EntityManagerInterface $em, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $sortieid = $request->query->get('sortieid');
        $sortie = $em->getRepository(Sortie::class)->find($sortieid);
        if (($sortie->getEtat()->getId() == 1) || ($sortie->getEtat()->getId() == 2) || ($sortie->getEtat()->getId() == 3)) {
            if ($sortie == null) {
                throw $this->createNotFoundException('Cette sortie n\'existe pas !');
            }
            $etat = $em->getRepository(Etat::class)->find(6);
            if ($etat == null) {
                throw $this->createNotFoundException('Cet état n\'existe pas !');
            }
            $sortie->setEtat($etat);
            $em->persist($sortie);
            $em->flush();
            $this->addFlash('success', 'La sortie a bien été annulée');
            return $this->redirectToRoute("sortie_liste");
        }
        $this->addFlash('danger', 'Le statut de la sortie ne permet pas de l\'annuler');
        return $this->redirectToRoute("sortie_liste");
    }
}
