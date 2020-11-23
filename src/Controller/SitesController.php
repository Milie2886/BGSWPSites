<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use App\Entity\History;
use Psr\Log\LoggerInterface;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SitesController extends AbstractController
{
    /*Je stock mes logs dans des fichiers distincts, pour ce controller, les logs seront stockés dans dev.site-AAAA-MM-JJ.log 
    J'instancie mon logger dans le constructeur afin de pouvoir y faire appel dans tout le controller
    La configuration des logs se trouve dans config/packages/dev/monolog.yaml et config/services.yaml*/
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * @Route("/", name="app_home", methods="GET")
     */
    public function index(SiteRepository $siteRepository): Response
    {
        
        // je prend les sites en base et je les affiche
        $this->logger->info('Récupération des sites, affichage A-Z');
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
            $this->logger->info('création du site : '. $site->getName());

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

    public function show(EntityManagerInterface $em, HttpClientInterface $httpClient, Site $site): Response
    {
        //Je récupère le contenu du champs url de la table Site
        $url = $site->getUrl();
        //Je tente d'intérroger la route que j'ai créé via le plugin "Etat Actuel du site"
        $this->logger->info($url.' -> Connexion à l\'URI /wp-json/listplug/v1/plugins');
        try {
            $response = $httpClient->request("GET", $url."/wp-json/listplug/v1/plugins");
            $arrayResp = $response->toArray();
            /*J'instancie une varialbe histo afin d'avoir un backup dans la BDD des infos présentes dans l'API
            à chaque raffraichissement, une copie est envoyée dans la table History. */
            $histo = new History();
            $histo->setSite($site);
            $histo->setResponseJson($arrayResp);
            $em->persist($histo);
            $em->flush();
            $this->logger->info('mise à jour de la table History');
        //Si l'API ne répond pas,je peux toujours afficher les dernières données enregistrées    
        } catch(\Exception $ex) {
            //j'instancie une variable error pour récupérer le contenu du message
            $error = $ex->getMessage();
            $this->logger->info($error);
            $last = $site->getHistories()->last();
            if ($last) {
                $arrayResp = $last->getResponseJson();
            }
        }
        //J'instancie une variable plugins qui récupère le tableau des plugins présent dans l'API
        $plugins = $arrayResp[0]["plugins"];
        //J'instancie une variable component pour récupérer seulement les infos du CORE
        $component = $arrayResp[0];
        //Je liste les plugins grâce à la boucle foreach
        foreach($plugins as $plugin){
            //Je renomme mes variable afin de pouvoir les utiliser dans mon template
            return $this->render('sites/show.html.twig', [
                'plugins' => $plugins,
                'site' => $site,
                'apiComponent' => $component
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
            $this->logger->info($site->getName().' modifié');

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
        //Je vérifie que le site à supprimer est bien valide grace au token CSRF. Si oui, je supprime le site
        if ($this->isCsrfTokenValid('site_deletion_' . $site->getId(), $request->request->get('csrf_token'))) {
            $em->remove($site);
            $em->flush();
            $this->logger->info($site->getName() .' supprimé!!');
            $this->addFlash('info', 'Site supprimé avec succès!!');
        }
        return $this->redirectToRoute('app_home');
    }   
}
