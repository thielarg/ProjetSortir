<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Form\AnnulationType;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SortieController extends AbstractController
{
    /**
     * @Route("/sortie/liste", name="sortie_liste")
     */
    public function liste(Request $request, EntityManagerInterface $entityManager)
    {
        //verrouille la page aux seuls utilisateurs ayant le role ROLE_USER
        $this->denyAccessUnlessGranted('ROLE_USER');

        //recupere les sorties en fonction des filtres renseignés
        $sorties = $entityManager->getRepository(Sortie::class)->rechercheDetaillee(
            ($request->query->get('recherche_terme') != null ? $request->query->get('recherche_terme') : null),
            ($request->query->get('recherche_site') != null ? $request->query->get('recherche_site') : null),
            ($request->query->get('recherche_etat') != null ? $request->query->get('recherche_etat') : null),
            ($request->query->get('date_debut') != null ? $request->query->get('date_debut') : null),
            ($request->query->get('date_fin') != null ? $request->query->get('date_fin') : null),
            ($request->query->get('cb_organisateur') != null ? $request->query->get('cb_organisateur') : null),
            ($request->query->get('cb_inscrit') != null ? $request->query->get('cb_inscrit') : null),
            ($request->query->get('cb_non_inscrit') != null ? $request->query->get('cb_non_inscrit') : null),
            ($request->query->get('cb_passee') != null ? $request->query->get('cb_passee') : null)
        );

        //recupere tous les sites afin de charger la liste deroulante dans la vue
        $sites = $entityManager->getRepository(Site::class)->findAll();

        //recupere tous les etats afin de charger la liste deroulante dans la vue
        $etats = $entityManager->getRepository(Etat::class)->findAll();

        //envoie à la vue
        return $this->render("sortie/liste.html.twig", [
            'sorties' => $sorties,
            'sites' => $sites,
            'etats' => $etats
        ]);
    }

    /**
     * @Route("/sortie/detail/{id}", name="sortie_detail", requirements={"id"="\d+"}, methods={"GET|POST"})
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function detail($id, SortieRepository $sortieRepository){
        //verrouille la page aux seuls utilisateurs ayant le role ROLE_USER
        $this->denyAccessUnlessGranted('ROLE_USER');

        //retourne la sortie dont l'identifiant est passé en parametre
        $sortie = $sortieRepository->find($id);

        //si non trouvé levée d'exception
        if ($sortie == null){
            throw $this->createNotFoundException('Cette sortie n\'existe pas !');
            //throw new NotFoundHttpException('Cette sortie n\'existe pas !');
        }

        //envoie à la vue
        return $this->render('sortie/detail.html.twig', [
            'sortie' => $sortie
        ]);
    }

    //A REVOIR
    /**
     * @Route("/sortie/ajouter", name="sortie_ajouter")
     */
    public function ajouter(EntityManagerInterface $entityManager, Request $request){
        //verrouille la page aux seuls utilisateurs ayant le role ROLE_USER
        $this->denyAccessUnlessGranted('ROLE_USER');

        //crée l'instance vide de Sortie
        $sortie = new Sortie();

        //hydrate les propriétés qui ne se trouvent pas dans le formulaire
        $etat =$entityManager->getRepository(Etat::class)->find(1);
        $sortie->setEtat($etat);

        $sortie->setOrganisateur($this->getUser());
        $sortie->setEstPublie(false);
        $sortie->setSite($this->getUser()->getSite());

        //crée une instance de SortieType et passe l'instance de Sortie
        //en 2eme parametre de la methode
        $sortieForm = $this ->createForm(SortieType::class, $sortie);

        //prend les données et injecte les données dans le formulaire
        $sortieForm->handleRequest($request);

        //si le formulaire est soumis et que la validation passe alors
        //sauvegarde la sortie dans la BDD
        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){
            //demande à doctrine de sauvegarder l'instance. Attention, cela
            //n'execute pas tout de suite la requete, mais juste gardée en memoire
            $entityManager->persist($sortie);

            //execute ici vraiment la requete sql
            $entityManager->flush();

            //ajoute un message en session qui sera affiché à la prochaine page
            //1ere argument : success, error, warning, info
            $this->addFlash("success", "La sortie a bien été créée");

            //redirige l'utilisateur soit sur une autre page, soit sur la meme page
            //pour vider le formulaire et empecher sa resoumission involontaire
            return $this->redirectToRoute('sortie_liste');
        }

        //envoie à la vue
        return $this->render("sortie/ajout.html.twig", [
            "form" => $sortieForm->createView()
        ]);
    }

    /**
     * @Route("/sortie/inscrire", name="sortie_inscrire")
     */
    public function inscrire(EntityManagerInterface $entityManager, Request $request, SortieRepository $sortieRepository){
        //verrouille la page aux seuls utilisateurs ayant le role ROLE_USER
        $this->denyAccessUnlessGranted('ROLE_USER');

        //recupere l'identifiant de la sortie
        $sortieid = $request->query->get('sortieid');

        //recupere l'utilisateur en session
        $user = $this->getUser();

        //recupere la sortie en BDD
        $sortie = $sortieRepository->find($sortieid);

        //si le nombre de participant pour la sortie est strictement inferieur au nombre d'inscription max pour la sortie
        if($sortie->getParticipants()->count() < $sortie->getNbInscriptionsMax()){
            //si l'etat de la sortie est OUVERTE
            if ($sortie->getEtat()->getId() == 2){
                if ($sortie->getParticipants()->contains($user)){
                    $this->addFlash('danger', 'Vous avez déjà été inscrit à cette sortie');
                    return $this->redirectToRoute("sortie_liste");
                }else{
                    $sortie->addParticipant($user);
                    $user->addSortie($sortie);
                    $entityManager->persist($user);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    $this->addFlash('success', $this->getUser()->getUsername().' ,vous avez bien été inscrit à cette sortie oganisée par ' . $sortie->getOrganisateur()->getUsername() );
                    return $this->redirectToRoute("sortie_liste");
                }
            }else{
                $this->addFlash('danger', 'Désolé il n\'est pas ou plus possible de s\'inscrire à cette sortie');
                return $this->redirectToRoute("sortie_liste");
            }
        }else{
            //sinon plus de place disponible
            $this->addFlash('danger', "Désolé, la sortie n'a plus de places disponibles.");
            return $this->redirectToRoute("sortie_liste");
        }
    }

    /**
     * @Route("/sortie/desister", name="sortie_desister")
     */
    public function desister(EntityManagerInterface $entityManager, Request $request){
        $this->denyAccessUnlessGranted('ROLE_USER');
        $sortieid = $request->query->get('sortieid');
        $participant = $this->getUser();
        $sortie = $entityManager->getRepository(Sortie::class)->find($sortieid);

        $participant->removeSortie($sortie);
        $sortie->removeParticipant($participant);

        $entityManager->persist($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();
        $this->addFlash("success", "Vous n'êtes plus inscrit pour cette sortie");
        return $this->redirectToRoute("sortie_liste");
    }

    /**
     * @Route("/sortie/supprimer", name="sortie_supprimer")
     */
    public function supprimer(EntityManagerInterface $entityManager, Request $request){
        $this->denyAccessUnlessGranted('ROLE_USER');
        $sortieid = $request->query->get('sortieid');
        $sortie = $entityManager->getRepository(Sortie::class)->find($sortieid);
        if ($sortie == null){
            throw $this->createNotFoundException('Cette sortie n\'existe pas !');
        }
        if ($sortie->getOrganisateur()->getId() == $this->getUser()->getId()){
            $sortie->getOrganisateur()->removeSortieOrganisee($sortie);
            foreach ($sortie->getParticipants() as $participant){
                $participant->removeSortie($sortie);
                $sortie->removeParticipant($participant);
                $entityManager->persist($participant);
            }
            $entityManager->remove($sortie);
            $entityManager->flush();
            $this->addFlash("success", "La sortie a bien été supprimée");
        }
        return $this->redirectToRoute("sortie_liste");
    }

    /**
     * @Route("/sortie/modifier", name="sortie_modifier")
     */
    public function modifier(EntityManagerInterface $em, Request $request){
        $this->denyAccessUnlessGranted('ROLE_USER');
        $sortieid = $request->query->get('sortieid');
        $sortie = $em->getRepository(Sortie::class)->find($sortieid);
        $sortie->setIsPublished(false);
        $sortie->setMotifAnnulation('aucun');
        if ($sortie == null){
            throw $this->createNotFoundException('Cette sortie n\'existe pas !');
        }
        $form = $this ->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $sortie->setDateHeureDebut(new \DateTime());
            $em->persist($sortie);
            $em->flush();
            $this->addFlash("success", "La sortie a bien été modifiée");
            return $this->redirectToRoute("sortie_detail", ['id'=>$sortie->getId()]);
        }
        return $this->render("sortie/modification.html.twig", [
            "sortie" => $sortie,
            "form" => $form->createView()
        ]);
    }
    /**
     * @Route("/sortie/publier", name="sortie_publier")
     */
    public function publier(EntityManagerInterface $em, Request $request){
        $this->denyAccessUnlessGranted('ROLE_USER');
        $sortieid = $request->query->get('sortieid');
        $sortie = $em->getRepository(Sortie::class)->find($sortieid);
        $etat = $em->getRepository(Etat::class)->find(2);
        $sortie->setEtat($etat);
        $em->persist($sortie);
        $em->flush();
        $this->addFlash("success", "La sortie a bien été publiée");
        return $this->redirectToRoute("sortie_liste");
    }
    /**
     * @Route("/sortie/annuler", name="sortie_annuler")
     */
    public function annuler(EntityManagerInterface $em, Request $request){
        $this->denyAccessUnlessGranted('ROLE_USER');
        $sortieid = $request->query->get('sortieid');
        $sortie = $em->getRepository(Sortie::class)->find($sortieid);
        if ($sortie == null){
            throw $this->createNotFoundException('Cette sortie n\'existe pas !');
        }
        $form = $this->createForm(AnnulationType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()){
            if ($sortie->getOrganisateur()->getId() == $this->getUser()->getId()){
                $etat = $em->getRepository(Etat::class)->find(6);
                $sortie->setEtat($etat);
                $em->persist($sortie);
                $em->flush();
                $this->addFlash("success", "La sortie a bien été annulée");
                return $this->redirectToRoute("sortie_liste");
            }else{
                $this->addFlash("danger", "Vous ne disposez pas des droits afin d'annuler cette sortie");
                return $this->redirectToRoute("sortie_annuler");
            }
        }
        return $this->render("sortie/annulation.html.twig", [
            "sortie" => $sortie,
            "form" => $form->createView()
        ]);
    }

}
