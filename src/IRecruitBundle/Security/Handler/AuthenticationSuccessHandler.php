<?php

namespace IRecruitBundle\Security\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    protected
        $router,
        $security;

    public function __construct(Router $router, SecurityContext $security)
    {
        $this->router = $router;
        $this->security = $security;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        // URL for redirect the user to where they were before the login process begun if you want.
        // $referer_url = $request->headers->get('referer');

        // Default target for unknown roles. Everyone else go there.
        if($this->security->isGranted('ROLE_ADMIN')) {
            $url = 'sonata_admin_dashboard';
        } elseif ($this->security->isGranted('ROLE_COMPANY')) {
            $url = 'company_dashboard';
        } else {
            $url = 'user_dashboard';
        }
        
        $response = new RedirectResponse($this->router->generate($url));

        return $response;
    }
}