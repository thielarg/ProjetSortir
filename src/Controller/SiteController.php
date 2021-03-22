<?php

namespace App\Controller;

use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SiteController extends AbstractController
{
    /**
     * @Route("/site", name="site")
     */
    public function index()
    {
        return $this->render('site/index.html.twig');
    }

    /**
     * @Route("/site/recherche", name="site_rechercher")
     */
    public function rechercher(Request $request, EntityManagerInterface $entityManager){
        $this->denyAccessUnlessGranted('ROLE_USER');
        $recherche = $request->request->get('recherche');
        $sites = $entityManager->getRepository(Site::class)->findAjaxRecherche($recherche);
        if ($request->isXmlHttpRequest()) {
            $jsonData = array();
            $idx = 0;
            foreach($sites as $site) {
                $temp = array(
                    'id' => $site->getId(),
                    'nom' => $site->getNom(),
                );
                $jsonData[$idx++] = $temp;
            }
            return new JsonResponse($jsonData);
        } else {
            return $this->redirectToRoute('sortie_liste');
        }
    }

    /**
     * @Route("/site/modifier", name="site_modifier")
     */
    public function modifier(Request $request, EntityManagerInterface $entityManager){
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $id = $request->request->get('id');
        $site = $entityManager->getRepository(Site::class)->find($id);
        if ($request->isXmlHttpRequest()) {
            $site->setNom($request->request->get('nom_site'));
            $entityManager->persist($site);
            $entityManager->flush();
            return new JsonResponse('site modifiée.');
        } else {
            return $this->redirectToRoute('sortie_liste');
        }
    }

    /**
     * @Route("/site/supprimer", name="site_supprimer")
     */
    public function supprimer(Request $request, EntityManagerInterface $entityManager){
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $id = $request->request->get('id');
        $site = $entityManager->getRepository(Site::class)->find($id);
        if ($request->isXmlHttpRequest()) {
            $entityManager->remove($site);
            $entityManager->flush();
            return new JsonResponse('site supprimée.');
        } else {
            return $this->redirectToRoute('sortie_liste');
        }
    }

    /**
     * @Route("/site/ajouter", name="site_ajouter")
     */
    public function ajouter(Request $request, EntityManagerInterface $entityManager){
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');
        $site = new Site();
        if ($request->isXmlHttpRequest()) {
            if($entityManager->getRepository(Site::class)->findBy(['nom' => $request->request->get('nom_site')]) == null){
                $site->setNom($request->request->get('nom_site'));
                $entityManager->persist($site);
                $entityManager->flush();
                return new JsonResponse('site ajoutée avec succès.');
            }else{
                return new JsonResponse(array('message' => 'site déjà existante.'), 419);
            }
        } else {
            return $this->redirectToRoute('sortie_liste');
        }
    }
}
