<?php

namespace App\Security;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $logger;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->logger = $logger;
    }
    // methode booléenne qui permet de vérifier si l'authenticator support la requête (POST), si false, on skip, si true, on passe à la méthode getCredential
    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }
    // méthode qui permet de récupérer les credentials (identifiants de connexion) depuis la requête et de les retourner sous forme de tableau associatif
    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        //Ici on sauvegarde l'email de l'utilisateur s'il s'est trompé de mdp, celà lui évite de le retaper.
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
        // les éléments retournés par getCredentials sont passés à getUser
    }
    //Cette méthode va chercher l'utilisateur correspondant au credentials récupérés
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        //On valide tout d'abord que le token est valide
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        //S'il n'est pas valide on lève une exception
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }
        //Sinon on va chercher dans la table User, on cherche l'email correspondant au mail récupéré dans les crédential
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);
        //Si l'email trouvées ne correspond pas à ce qui est présent dans la table alors on lève une exception
        if (!$user) {
            //J'ai mis un message vague pour éviter d'indiquer à un hacker que l'email existe mais mauvais mdp...
            throw new CustomUserMessageAuthenticationException('Email ou mot de passe invalide');
        }
        return $user;
    }
    //Cette méthode vérifie le mot de passe entré par l'utilisateur ($credential) et ce qui est retourné par getUser
    public function checkCredentials($credentials, UserInterface $user)
    {
        //Si en décodant le mot passe, on voit qu'il n'est pas valide alors on lève une exception (algorithm défini dans security.yaml)
        if (!$this->passwordEncoder->isPasswordValid($user, $credentials['password'])) {
            throw new CustomUserMessageAuthenticationException('Erreur de saisie, veuillez recommencer!');
        };
        return true;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }
    //Si l'authentification est un succès, on appelle la méthode suivante
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)//providerKey = firewall défini dans security.yaml
    {
        $this->logger->info($request->request->get('email').' est connecté!');
        //
        $request->getSession()->getFlashBag()->add('success', 'Connexion réussie! Bienvenue '. $token->getUser()->getFirstName().'!');
        //si le chemin demandé est utilisé par un utilisateur non connecté, alors il est redirigé vers la page de connexion, se connecte et accède au chemin demandé
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }
        
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
