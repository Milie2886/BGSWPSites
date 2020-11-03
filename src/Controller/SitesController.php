<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SitesController extends AbstractController
{
    /**
     * @Route("/", name="app_home", methods="GET")
     */
    public function index(SiteRepository $siteRepository): Response
    {
        // je prend les sites en base et je les affiche
        $sites = $siteRepository->findBy([], ['name' => 'ASC']);
        
        return $this->render('sites/index.html.twig', 
        [
            'sites' => $sites
        ]);
    }
    //----------------Route pour ajouter un nouveau site-----------------------
    /**
     * @Route("/sites/create", name="app_sites_create", methods={"GET", "POST"})
     */
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        //On créé une variable qui contient le schema de notre entité Site
        $site = new Site;
        //on construit notre formulaire grace a form builder, le formulaire ayant été créé avec make:form, il récupère la liste des champs de la table (présent dans Form/SiteType.php)
        $form = $this->createForm(SiteType::class, $site);
        //On demande à Request de gérer notre formulaire (il va récupérer les données saisies)
        $form->handleRequest($request);

        //On emet une condition, si notre formulaire est soumis et est valide alors on créé notre site
        if ($form->isSubmitted() && $form->isValid()) {
            $site->setUser($this->getUser());
            $em->persist($site);
            $em->flush();

            //Pour ajouter un message flash à la création : 
            $this->addFlash('success', 'Site ajouté avec succès!!');
            //à la validation, on est redirigé vers la home
            return $this->redirectToRoute('app_home');
        }

        //on passe en parametre de notre twig le form qu'on vient de créer en n'oubliant pas createView car symfony attend un objet de type form view
        return $this->render('sites/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
    //----------------Route pour voir le détail de chaque site----------------
    //On indique que l'id est obligatoirement un nombre
    /**
     * @Route("/sites/{id<[0-9]+>}", name="app_sites_show", methods="GET")
     */

    public function show(HttpClientInterface $httpClient, Site $site, SiteRepository $siteRepository): Response
    {
            $url = $site->getUrl();
            $response = $httpClient->request("GET", $url."/wp-json/listplug/v1/plugins");
            $arrayResp = $response->toArray();
            $plugins = $arrayResp[0]["plugins"];
            foreach($plugins as $plugin){
        
                return $this->render('sites/show.html.twig', [
                    'plugins' => $plugins,
                    'site' => $site
                ]);
            }
    }
    //------------------Route pour éditer un site------------------------------
    //On utilise le verbe HTTP PUT pour la modification des datas
    /**
     * @Route("/sites/{id<[0-9]+>}/edit", name="app_sites_edit", methods={"GET", "PUT"})
     */
    
    public function edit(Site $site, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SiteType::class, $site, [
            'method' => 'PUT'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

                $em->flush();

                $this->addFlash('success', 'Site modifié avec succès!!');

                //à la validation, on est redirigé vers la home
                return $this->redirectToRoute('app_home');
        }

        return $this->render('sites/edit.html.twig', [
            'site' => $site,
            'form' => $form->createView()
        ]);

    }
    //---------Route pour supprimer un site de la table (meme route que show mais méthode différente)------------------------------------------------
    /**
     * @Route("/sites/{id<[0-9]+>}", name="app_sites_delete", methods={"DELETE"})
     */
    
    public function delete(Request $request, Site $site, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('site_deletion_'. $site->getId(), $request->request->get('csrf_token'))) {
            $em->remove($site);
            $em->flush();

            $this->addFlash('info', 'Site supprimé avec succès!!');
        }
        return $this->redirectToRoute('app_home');
    }   
}
