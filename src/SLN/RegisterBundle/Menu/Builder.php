<?php
/**
  * Menus for the application
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Menu builder
 */
class Builder extends ContainerAware {
    /**
     * Main menu for most options. It contains options that are specific to administration.
     *
     * @param FactoryInterface $factory Factory interface
     * @param array            $options List of options
     *
     * @return Knp\Menu\ItemInterface Built menu
     */
    public function mainMenu(FactoryInterface $factory, array $options) {
        // access service from the container
        $em = $this->container->get('doctrine')->getManager();

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
                ->setAttribute('icon', 'user');
            $menu['Admin']->addChild('Suivi des inscriptions', array('route' => 'SLNRegisterBundle_payment_search'))
                ->setAttribute('icon', 'euro')
    			->setAttribute('divider_append', true);

            $open_saison = $em->getRepository('SLNRegisterBundle:Saison')->getOpen();
            $current_saison = $em->getRepository('SLNRegisterBundle:Saison')->getcurrent();
            
            if ($open_saison && $open_saison->getId() != $current_saison->getId()) {
                $menu['Admin']->addChild("Liste des licenciés - " . $current_saison->getNom(), 
                                         array('route' => 'SLNRegisterBundle_admin_licensee_list',
                                               'routeParameters' => array('saison_id' => $current_saison->getId()),))
                               ->setAttribute('icon', 'th-list');
                $menu['Admin']->addChild("Liste des licenciés - " . $open_saison->getNom(), 
                                         array('route' => 'SLNRegisterBundle_admin_licensee_list',
                                               'routeParameters' => array('saison_id' => $open_saison->getId()),))
                               ->setAttribute('icon', 'th-list');
            } else {
                $menu['Admin']->addChild('Liste des licenciés', array('route' => 'SLNRegisterBundle_admin_licensee_list',
                                                                      'routeParameters' => array('saison_id' => 0),))
                    ->setAttribute('icon', 'th-list');
            }

            $old_saisons = $em->getRepository('SLNRegisterBundle:Saison')->getOldSaisons();
            if (count($old_saisons) > 0) {
                foreach($old_saisons as $saison) {
                    $menu['Admin']->addChild("Archive: saison " . $saison->getNom(), 
                                             array('route' => 'SLNRegisterBundle_admin_licensee_list',
                                             'routeParameters' => array('saison_id' => $saison->getId()),))
                                  ->setAttribute('icon', 'floppy-disk');
                }
            }

            $menu['Admin']->addChild('Ajouter un licencié', array('route' => 'SLNRegisterBundle_admin_licensee_create',
                                                                  'routeParameters' => array('saison_id' => 0),))
                ->setAttribute('icon', 'user')
    			->setAttribute('divider_append', true);

            $menu['Admin']->addChild('Liste des groupes', array('route' => 'SLNRegisterBundle_groupe_list'))
                ->setAttribute('icon', 'th-list');
            $menu['Admin']->addChild('Mettre à jour les groupes', array('route' => 'SLNRegisterBundle_licensee_change_group',
                                                                        'routeParameters' => array('saison_id' => $current_saison->getId()),))
                ->setAttribute('icon', 'sort')
    			->setAttribute('divider_append', true);

            $menu['Admin']->addChild('Liste des mails', array('route' => 'SLNRegisterBundle_mail_list',
                                                              'routeParameters' => array('saison_id' => $current_saison->getId(), 'page' => 1, 'admin' => TRUE),))
                ->setAttribute('icon', 'th-list');

            $menu['Admin']->addChild('Envoyer un email', array('route' => 'SLNRegisterBundle_mail_licensee',
                                                               'routeParameters' => array('saison_id' => 0),))
                ->setAttribute('icon', 'envelope');

        }

        return $menu;
    }

    /**
     * Specific user menu for profile edit.
     *
     * @param FactoryInterface $factory Factory interface
     * @param array            $options List of options
     *
     * @return \Knp\Menu\ItemInterface Built menu
     */
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

        return $menu;
    }
}


