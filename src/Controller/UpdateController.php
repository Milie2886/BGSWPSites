<?php

namespace App\Controller;

use App\Repository\SiteRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UpdateController extends AbstractController
{
    /**
     * @Route("/update", name="update", methods={"GET", "PUT"})
     */
    //Définition de la route "update" appelée par un bouton qui effectue la mise à jour pour tous les sites
    public function updateSites(HttpClientInterface $httpClient, SiteRepository $siteRepository ): Response 
    {
        //Récupération des sites
        $sites = $siteRepository->findBy([], ['name' => 'ASC']);
        // Pour chaque site présent dans la base, on veut récupérer le champs "url" et le mettre dans une variable
        foreach($sites as $site) {
            $url = $site->getUrl();
            //On essaye ensuite de mettre à jour notre base de données en récupérant les infos de l'API
            try {
                $em = $this->getDoctrine()->getManager();
                $response = $httpClient->request("GET", $url."/wp-json/listplug/v1/plugins");
                $arrayResp = $response->toArray();
                $site->setWpVersion($arrayResp [0]['wordpress_version']);
                $site->setPhpVersion($arrayResp [0]['php_version']);
                $site->setMySqlVersion($arrayResp [0]['mySql_version']);
                //$site->setDateUpdate()
                $em->persist($site);
                $em->flush();

                } catch(\Exception $ex) {
                    //Si l'url n'existe pas dans notre API, on met les champs à "KO"    
                    if ($em) {
                        $site->setWpVersion("KO");
                        $site->setPhpVersion("KO");
                        $site->setMySqlVersion("KO");
                        $em->persist($site);
                        $em->flush();
                    }
                }
            }
            //Mise en place d'un message flash
            $this->addFlash('success', 'Sites mis à jour avec succès');
        //Redirection vers la page Home (la page update n'est alors jamais affichée)
        return $this->redirectToRoute('app_home');
    }
}