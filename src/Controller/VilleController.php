<?php

namespace App\Controller;

use App\Entity\Ville;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VilleController extends AbstractController
{
    /**
     * @Route("/ville", name="ville")
     */
    public function index()
    {
        return $this->render('ville/index.html.twig', [
            'controller_name' => 'VilleController',
        ]);
    }

    /**
     * @Route("/ville/recherche", name="ville_rechercher")
     */
    public function rechercher(Request $request, EntityManagerInterface $entityManager){
        $recherche = $request->request->get('recherche');
        $villes = $entityManager->getRepository(Ville::class)->findAjaxRecherche($recherche);
        if ($request->isXmlHttpRequest()) {
            $jsonData = array();
            $idx = 0;
            foreach($villes as $ville) {
                $temp = array(
                    'id' => $ville->getId(),
                    'nom' => $ville->getNom(),
                    'code_postal' => $ville->getCodePostal(),
                );
                $jsonData[$idx++] = $temp;
            }
            return new JsonResponse($jsonData);
        } else {
            return $this->redirectToRoute('sortie_liste');
        }
    }

    /**
     * @Route("/ville/modifier", name="ville_modifier")
     */
    public function modifier(Request $request, EntityManagerInterface $entityManager){
        $id = $request->request->get('id');
        $ville = $entityManager->getRepository(Ville::class)->find($id);
        if ($request->isXmlHttpRequest()) {
            $ville->setNom($request->request->get('nom_ville'));
            $ville->setCodePostal($request->request->get('code_postal_ville'));
            $entityManager->persist($ville);
            $entityManager->flush();
            return new JsonResponse('Ville modifiée.');
        } else {
            return $this->redirectToRoute('sortie_liste');
        }
    }

    /**
     * @Route("/ville/supprimer", name="ville_supprimer")
     */
    public function supprimer(Request $request, EntityManagerInterface $entityManager){
        $id = $request->request->get('id');
        $ville = $entityManager->getRepository(Ville::class)->find($id);
        if ($request->isXmlHttpRequest()) {
            $entityManager->remove($ville);
            $entityManager->flush();
            return new JsonResponse('Ville supprimée.');
        } else {
            return $this->redirectToRoute('sortie_liste');
        }
    }

    /**
     * @Route("/ville/ajouter", name="ville_ajouter")
     */
    public function ajouter(Request $request, EntityManagerInterface $entityManager){
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $ville = new Ville();
        if ($request->isXmlHttpRequest()) {
            if($entityManager->getRepository(Ville::class)->findBy(['nom' => $request->request->get('nom_ville')]) == null){
                $ville->setNom($request->request->get('nom_ville'));
                $ville->setCodePostal($request->request->get('cp_ville'));
                $entityManager->persist($ville);
                $entityManager->flush();
                return new JsonResponse('Ville ajoutée avec succès.');
            }else{
                return new JsonResponse(array('message' => 'Ville déjà existante.'), 419);
            }
        } else {
            return $this->redirectToRoute('sortie_liste');
        }
    }
}
