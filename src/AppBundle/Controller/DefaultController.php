<?php

namespace AppBundle\Controller;

use AppBundle\Authorization\OAuth2;
use AppBundle\Entity\AccessToken;
use AppBundle\Entity\DiscordUser;
use AppBundle\Repository\DiscordUserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{

    const REDIRECT_ROUTE = '/oauth';
    const USER_ID = 'userid';

    /**
     * @var OAuth2
     */
    private $OAuth2;

    /**
     * @var AccessToken
     */
    private $accessToken;

    public function __construct(OAuth2 $OAuth2)
    {
        $this->OAuth2 = $OAuth2;
    }

    /**
     * @Route("/", name="home")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function home(Request $request)
    {
        $loggedInUser = null;
        $token        = $request->cookies->get(self::USER_ID);
        if ($token) {
            /** @var DiscordUserRepository $userRepository */
            $userRepository           = $this->getDoctrine()->getRepository(DiscordUser::class);
            $loggedInUser = $userRepository->getUserByToken($token);
        }

        if (!$loggedInUser) {
            $vars = [
                'login_logout_link' => $this->OAuth2->getAuthenticationUrl($request->getSchemeAndHttpHost() . self::REDIRECT_ROUTE, 'identify guilds'),
                'login_logout_text' => 'Log in',
                'username'          => false,
            ];
        } else {
            /** @var DiscordUser $loggedInUser */
            $vars = [
                'username'          => $loggedInUser->getUsername(),
                'avatar'            => $loggedInUser->getAvatarAddress(),
                'login_logout_link' => $this->generateUrl('logout'),
                'login_logout_text' => 'Log out',
            ];
        }

        return $this->render('default/home.html.twig', $vars);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        $response = $this->redirectToRoute('home');
        $response->headers->clearCookie(self::USER_ID);

        return $response;
    }

    /**
     * @Route("/oauth", name="callback")
     * @param Request $request
     *
     * @return Response
     */
    public function discordAction(Request $request)
    {
        $code              = $request->get('code');
        $this->accessToken = $this->OAuth2->exchangeToken($request->getSchemeAndHttpHost() . self::REDIRECT_ROUTE, $code);
        $user              = $this->OAuth2->getUserInfo($this->accessToken);

        $em = $this->getDoctrine()->getManager();
        $existingUser = $em->getRepository(DiscordUser::class)->findOneBy(['userid' => $user->getUserid()]);
        $em->persist($this->accessToken);
        if ($existingUser) {
            $user = $existingUser;
        } else {
            $user->setToken($this->accessToken);
            $em->persist($user);
        }

        $em->flush();

        $response = $this->redirectToRoute('home');
        $response->headers->setCookie(new Cookie(self::USER_ID, $this->accessToken->getToken()));

        return $response;
    }
}
