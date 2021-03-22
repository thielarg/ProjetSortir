<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
//use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\User\User;

class SortieFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        //créer 20 Villes
        for($i = 0 ; $i < 5 ; $i++ ){
            $ville = new Ville();
            $ville->setNom($faker->city());
            $ville->setCodePostal($faker->numberBetween(35000, 35999));
            $manager->persist($ville);
        }
        for($i = 0 ; $i < 5 ; $i++ ){
            $ville = new Ville();
            $ville->setNom($faker->city());
            $ville->setCodePostal($faker->numberBetween(44000, 44999));
            $manager->persist($ville);
        }
        for($i = 0 ; $i < 5 ; $i++ ){
            $ville = new Ville();
            $ville->setNom($faker->city());
            $ville->setCodePostal($faker->numberBetween(79000, 79999));
            $manager->persist($ville);
        }
        $manager->flush();

        //créer 50 Lieux
        for($i = 0 ; $i < 30 ; $i++){
            $lieu = new Lieu();
            $lieu->setNom($faker->company());
            $lieu->setLatitude($faker->latitude());
            $lieu->setLongitude($faker->longitude());
            $lieu->setRue($faker->streetAddress());
            $villes = $manager->getRepository(Ville::class)->findAll();
            shuffle($villes);
            $lieu->setVille($villes[0]);
            $manager->persist($lieu);
        }
        $manager->flush();

        //créer les états
        $creee = new Etat();
        $creee->setLibelle('Créée');
        $manager->persist($creee);
        $manager->flush();
        $ouverte = new Etat();
        $ouverte->setLibelle('Ouverte');
        $manager->persist($ouverte);
        $manager->flush();
        $cloturee = new Etat();
        $cloturee->setLibelle('Cloturée');
        $manager->persist($cloturee);
        $manager->flush();
        $en_cours = new Etat();
        $en_cours->setLibelle('Activité en cours');
        $manager->persist($en_cours);
        $manager->flush();
        $passee = new Etat();
        $passee->setLibelle('Passée');
        $manager->persist($passee);
        $manager->flush();
        $annulee = new Etat();
        $annulee->setLibelle('Annulée');
        $manager->persist($annulee);
        $manager->flush();

        //Créer les sites
        $chartres = new Site();
        $chartres->setNom("Chartres de Bretagne");
        $manager->persist($chartres);
        $manager->flush();
        $nantes = new Site();
        $nantes->setNom("Nantes");
        $manager->persist($nantes);
        $manager->flush();
        $niort = new Site();
        $niort->setNom("Niort");
        $manager->persist($niort);
        $manager->flush();

        //Créer 20 participants
        for($i = 0 ; $i < 20 ; $i++){
            $participant = new Participant();
            $participant->setNom($faker->lastName());
            $participant->setPrenom($faker->firstName());
            $telephone = $faker->phoneNumber();
            $telephone = "0".substr($telephone,-6,9);
            $participant->setTelephone(str_replace(' ', '', $telephone));
            $participant->setMail($faker->unique()->email(1));
            $participant->setAdministrateur(0);
            $participant->setActif($faker->numberBetween(0, 1));
            $participant->setUsername($faker->unique()->userName());
            $participant->setPassword($faker->password());
            //site au hasard
            $sites = $manager->getRepository(Site::class)->findAll();
            shuffle($sites);
            $participant->setSite($sites[0]);
            $manager->persist($participant);
        }
        $manager->flush();

        //Créer des utilisateurs tests
        $thierry = new Participant();
        $thierry->setNom('LARGEAU');
        $thierry->setPrenom('Thierry');
        $sites = $manager->getRepository(Site::class)->findAll();
        shuffle($sites);
        $thierry->setSite($sites[2]);
        $thierry->setAdministrateur(1);
        $thierry->setActif(1);
        $thierry->setMail($faker->email());
        $thierry->setTelephone('0707070707');
        $thierry->setUsername('thielarg');
        $thierry->setPassword('$argon2id$v=19$m=65536,t=4,p=1$U3ROdmYvRnBJNExCOE01YQ$zTL26CClhJAWsGFNlJ7ZSvSYNA1BJ6ZTS5mtVeTeTSY');
        $manager->persist($thierry);
        $manager->flush();

        $patrick = new Participant();
        $patrick->setNom('DUPONT');
        $patrick->setPrenom('Patrick');
        $sites = $manager->getRepository(Site::class)->findAll();
        shuffle($sites);
        $patrick->setSite($sites[0]);
        $patrick->setAdministrateur(0);
        $patrick->setActif(1);
        $patrick->setMail($faker->email());
        $patrick->setTelephone('0101010101');
        $patrick->setUsername('Patrick');
        $patrick->setPassword('$argon2id$v=19$m=65536,t=4,p=1$ejc4QVlUVDA0M2RhbUxjRg$hjW63+yny27/N/IkViQJm6lQ5Mz/2VfbqEDQXcuDcMg');
        $manager->persist($patrick);
        $manager->flush();

        $dominique = new Participant();
        $dominique->setNom('DURANT');
        $dominique->setPrenom('Dominique');
        $sites = $manager->getRepository(Site::class)->findAll();
        shuffle($sites);
        $dominique->setSite($sites[0]);
        $dominique->setAdministrateur(0);
        $dominique->setActif(1);
        $dominique->setMail($faker->email());
        $dominique->setTelephone('0202020202');
        $dominique->setUsername('Dominique');
        $dominique->setPassword('$argon2id$v=19$m=65536,t=4,p=1$UldrZmVWZS5uTVYyekFIcg$mJC8YRwUpMaPdo5APht9T/fHO1wOaHzY4fxY0vkqDLs');
        $manager->persist($dominique);
        $manager->flush();

//        //Créer 20 sorties
        for($i = 0 ; $i < 100 ; $i++){
            $sortie = new Sortie();
            $sortie->setNom($faker->text(50));
            $sortie->setDateHeureDebut($faker->dateTimeBetween('-6 months', '+ 6 months'));
            $sortie->setDateLimiteInscription($faker->dateTimeInInterval($sortie->getDateHeureDebut(), '-5 days'));
            $sortie->setNbInscriptionMax($faker->numberBetween(4, 20));
            $sortie->setDuree($faker->numberBetween(0, 2400));
            $description = $faker->text(240);
            $sortie->setInfosSortie($description);
            //tire un organisateur au hasard
            $participants = $manager->getRepository(Participant::class)->findAll();
            shuffle($participants);
            $sortie->setOrganisateur($participants[0]);
            //etat au hasard
            $etats = $manager->getRepository(Etat::class)->findAll();
            shuffle($etats);
            $sortie->setEtat($etats[0]);
            //lieu au hasard
            $lieux = $manager->getRepository(Lieu::class)->findAll();
            shuffle($lieux);
            $sortie->setLieu($lieux[0]);
            //site est celui de l'organisateur
            $sortie->setSite($participants[0]->getSite());
            //publié au sort
            $sortie->setEstPublie($faker->numberBetween(0,1));
            $manager->persist($sortie);
        }
        $manager->flush();

        //créer les inscriptions
/*        $sorties = $manager->getRepository(Sortie::class)->findAll();
        //pour chaques sorties
        for($i = 0 ; $i < count($sorties) ; $i++){
            //pour chaque place jusqu'au rand max
            $nbInscrits = rand(0, $sorties[$i]->getNbInscriptionMax());
            //prend un partipant au hasard et ajoute
            $participants = $manager->getRepository(Participant::class)->findAll();
            shuffle($participants);
            for($j = 0 ; $j < $nbInscrits ; $j++ ){
                //evite d'avoir deux fois le meme participant
                $sorties[$i]->addParticipant($participants[$j]);
                $participants[$j]->addSortie($sorties[$i]);
                $manager->persist($sorties[$i]);
                $manager->persist($participants[$j]);
            }
        }
        $manager->flush();
*/    }
}