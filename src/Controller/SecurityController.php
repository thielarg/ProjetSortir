<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // obtient une erreur sur le login s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        //si user connecté et user actif, on dirige vers la page d'acceuil cad la liste des sorties
        //sinon si user connecté et user non actif, on recupere le username entré par l'utilisateur,
        //on charge en session un message flash "vous n'êtes plus actif" et on redirige vers la page
        //de connexion
        //sinon , on recupere le username entré par l'utilisateur, on redirige vers la page de connexion
        if ($this->getUser() != null && $this->getUser()->getActif()){
            return $this->redirectToRoute('sortie_liste');
        } elseif ($this->getUser() != null && !$this->getUser()->getActif()){
            $lastUserName = $authenticationUtils->getLastUsername();
            $this->addFlash('danger', 'Vous n\'êtes plus actif!');
            return $this->render("security/login.html.twig", [
                'error' => $error,
                'lastUserName' => $lastUserName
            ]);
        }else{
            $lastUserName = $authenticationUtils->getLastUsername();
            return $this->render("security/login.html.twig", [
                'error' => $error,
                'lastUserName' => $lastUserName
            ]);
        }
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        //throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/logout-succesfull", name="logout_temp")
     */
    public function logoutSuccess()
    {
        //return $this->render('security/logout.html.twig');
        $this->addFlash('success','vous êtes bien deconnecté');
        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("/mdp_forgot", name="mdp_forgot")
     */
    public function mdpForgot()
    {
        return $this->render('security/mdp_forgot.html.twig', []);
    }
}
