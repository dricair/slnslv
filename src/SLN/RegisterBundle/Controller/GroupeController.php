<?php

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Form\GroupeType;

/**
 * Groupe controller.
 */
class GroupeController extends Controller {
    
    /**
     * Create a groupe
     */
    public function editAction($id=0) {
        if ($id == 0) {
            $groupe = new Groupe();
        } else {
          $em = $this->getDoctrine()->getEntityManager();
          $groupe = $em->getRepository('SLNRegisterBundle:Groupe')->find($id);

          if (!is_object($groupe)) {
              throw $this->createNotFoundException('Ce groupe n\'existe pas dans la base de données.');
          }
        }

        $request = $this->getRequest();
        $form    = $this->createForm(new GroupeType(), $groupe);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()
                   ->getEntityManager();
            $em->persist($groupe);
            $em->flush();

            return $this->redirect($this->generateUrl('SLNRegisterBundle_groupe_show', array(
                'id' => $groupe->getId()
            )));
        }

        return $this->render('SLNRegisterBundle:Groupe:edit.html.twig', array(
            'groupe'  => $groupe,
            'form'    => $form->createView(),
            'title'   => $id == 0 ? "Ajouter un groupe" : "Editer ce groupe",
            'id'      => $id
        ));
    }

    /**
     * Show and modify a groupe
     */
    public function showAction($id, $licensees=False) {
        $em = $this->getDoctrine()->getEntityManager();
        $groupe = $em->getRepository('SLNRegisterBundle:Groupe')->find($id);

        if (!$groupe) {
            throw $this->createNotFoundException('Ce groupe n\'existe pas dans la base de données.');
        }

        return $this->render('SLNRegisterBundle:Groupe:show.html.twig', array(
          'groupe' => $groupe));
    }


    public function deleteAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $groupe = $em->getRepository('SLNRegisterBundle:Groupe')->find($id);

        if (!$groupe) {
            throw $this->createNotFoundException('Ce groupe n\'existe pas dans la base de données.');
        }

        $em->remove($groupe);
        $em->flush();

        return $this->redirect($this->generateUrl('SLNRegisterBundle_groupe_list'));
    }


    /**
     * List of the groups
     */
    public function listAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $groupes = $em->getRepository('SLNRegisterBundle:Groupe')->findAll();

        $groupes_by_categories = [];
        foreach ($groupes as $groupe) {
            if (!array_key_exists($groupe->getCategorieName(), $groupes_by_categories))
                $groupes_by_categories[$groupe->getCategorieName()] = [];
            $groupes_by_categories[$groupe->getCategorieName()][] = $groupe;
        }

        return $this->render('SLNRegisterBundle:Groupe:list.html.twig', array(
          'groupes_by_categories' => $groupes_by_categories));
    }




}

