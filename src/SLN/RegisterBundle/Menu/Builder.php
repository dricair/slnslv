<?php

namespace SLN\RegisterBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware {
    // Main menu
    public function mainMenu(FactoryInterface $factory, array $options) {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $securityContext = $this->container->get('security.context');
        $user = $securityContext->getToken()->getUser();

        $menu->addChild('User', array('label' => 'Mes licenciés'))
			->setAttribute('dropdown', true)
            ->setAttribute('icon', 'list');

        $menu['User']->addChild('Liste des licenciés', array('route' => 'SLNRegisterBundle_homepage'))
            ->setAttribute('icon', 'th-list');

        $menu['User']->addChild('Ajouter un licencié', array('route' => 'SLNRegisterBundle_licensee_create',
                                                             'routeParameters' => array('user_id' => $user->getId())))
            ->setAttribute('icon', 'plus')
            ->setAttribute('divider_append', true);

        $menu['User']->addChild('Imprimer les feuilles d\'inscription', array('route' => 'SLNRegisterBundle_member_inscriptions',
                                                                              'routeParameters' => array('user_id' => $user->getId()),
                                                                              'attr' => array('target' => '_blank')))
            ->setAttribute('icon', 'download-alt');

        if ($user->hasRole('ROLE_ADMIN')) {
            $menu->addChild('Admin', array('label' => 'Administration'))
    			->setAttribute('dropdown', true)
                ->setAttribute('icon', 'wrench');

            $menu['Admin']->addChild('Liste des membres', array('route' => 'SLNRegisterBundle_member_list'))
                ->setAttribute('icon', 'th-list');
            $menu['Admin']->addChild('Ajouter un membre', array('route' => 'SLNRegisterBundle_member_create'))
                ->setAttribute('icon', 'user')
    			->setAttribute('divider_append', true);

            $menu['Admin']->addChild('Liste des licenciés', array('route' => 'SLNRegisterBundle_admin_licensee_list'))
                ->setAttribute('icon', 'th-list')
    			->setAttribute('divider_append', true);

            $menu['Admin']->addChild('Liste des groupes', array('route' => 'SLNRegisterBundle_groupe_list'))
                ->setAttribute('icon', 'th-list');
        }

        // access service from the container
        $em = $this->container->get('doctrine')->getManager();

        return $menu;
    }

    // User/Profile menu
    public function userMenu(FactoryInterface $factory, array $options) {
        $securityContext = $this->container->get('security.context');
        $user = $securityContext->getToken()->getUser();

        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav pull-right');

        $menu->addChild('User', array('label' => "Bonjour {$user->getPrenom()} {$user->getNom()}"))
			->setAttribute('dropdown', true)
			->setAttribute('icon', 'user');

		$menu['User']->addChild("Contactez nous", array('route' => 'SLNRegisterBundle_contact'))
			->setAttribute('icon', 'envelope')
			->setAttribute('divider_append', true);
 
		$menu['User']->addChild("Mettre à jour mon profil", array('route' => 'fos_user_profile_edit'))
			->setAttribute('icon', 'edit');

		$menu['User']->addChild("Changer mon mot de passe", array('route' => 'fos_user_change_password'))
			->setAttribute('icon', 'cog')
			->setAttribute('divider_append', true);

		$menu['User']->addChild("Déconnection", array('route' => 'fos_user_security_logout'))
			->setAttribute('icon', 'log-out');

        //$menu->addChild('Profile', array('label' => 'Mon profil', 'route' => 'fos_user_profile_edit'));
        //     ->setAttribute('dropdown', true);
        //$menu['Profile']->addChild('Editer le profil', array('route' => 'fos_user_security_logout'));
        //$menu['Profile']->addChild('Déconnection', array('route' => 'fos_user_profile_edit'));

        return $menu;
    }
}


