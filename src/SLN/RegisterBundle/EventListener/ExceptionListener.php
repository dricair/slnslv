<?php
namespace SLN\RegisterBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Session\Session;

class ExceptionListener
{
    protected $router;
    protected $session;

    public function __construct(Router $router, Session $session)
    {
        $this->router = $router;
        $this->session = $session;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        //if (!$event->isMasterRequest()) {
        //    // don't do anything if it's not the master request
        //    return;
        //}

        $e = $event->getException();

        if($e instanceof NotFoundHttpException) {
            if(strpos($e->getMessage(), 'The user with confirmation token') !== false && strpos($e->getMessage(), 'does not exist') !== false) {
                $this->session->getFlashBag()->add(
                  'warning',
                  sprintf("Ce lien de confirmation n'existe plus. Nous vous avons redirigé vers la page correcte.")
                );

                $event->setResponse(new RedirectResponse($this->router->generate('fos_user_security_login')));
                $event->stopPropagation();
            }
        }
    }
}
