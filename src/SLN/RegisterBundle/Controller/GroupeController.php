<?php
/**
  * Group controller class. 
  *
  * Contains controller class to deal with groups. Mostly admin functions.
  *
  * @author Cédric Airaud
  */

namespace SLN\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use SLN\RegisterBundle\Entity\Groupe;
use SLN\RegisterBundle\Entity\Licensee;
use SLN\RegisterBundle\Form\GroupeType;

/**
 * Groupe controller.
 */
class GroupeController extends Controller {
    
    /**
     * Create or edit a groupe. 
     *
     * Can be used for GET and POST requests. 
     *
     * @param int $id Id of the group to edit. If 0 or not specified, create a group
     *
     * @return Response Rendered page
     */
    public function editAction($id=0) {
        if ($id == 0) {
            $groupe = new Groupe();
        } else {
          $em = $this->getDoctrine()->getManager();
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
                   ->getManager();
            $em->persist($groupe);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
              'notice',
              sprintf("Le groupe '%s' a été %s avec succès.", $groupe->getNom(), $id = 0 ? "ajouté" : "modifié")
            );

            return $this->redirect($this->generateUrl('SLNRegisterBundle_groupe_show', array(
                'id' => $groupe->getId()
            )));
        }

        else if ($request->getMethod() == 'POST') {
            $this->get('session')->getFlashBag()->add('error', "Des erreurs sont présentes dans le formulaire");
        }


        return $this->render('SLNRegisterBundle:Groupe:edit.html.twig', array(
            'groupe'  => $groupe,
            'form'    => $form->createView(),
            'title'   => $id == 0 ? "Ajouter un groupe" : "Editer ce groupe",
            'id'      => $id
        ));
    }

    /**
     * Show a groupe.
     *
     * Show the groupe detail as well as the licensees that are in this groupe.
     *
     * @param int  $id     Id of the group to show
     * @param bool $admin  True if the page is accessed with admin rights
     *
     * @return Response Rendered page
     */
    public function showAction($id, $admin=false) {
        $em = $this->getDoctrine()->getManager();
        $groupe = $em->getRepository('SLNRegisterBundle:Groupe')->find($id);

        if (!$groupe) {
            throw $this->createNotFoundException('Ce groupe n\'existe pas dans la base de données.');
        }

        $licensees =  $em->getRepository('SLNRegisterBundle:Licensee')->getAllForGroupe($groupe);
        $groupes = Licensee::sortByGroups($licensees);

        return $this->render('SLNRegisterBundle:Groupe:show.html.twig', array(
          'groupe' => $groupe,
          'licensees' => $groupes[$groupe->getNom()],
          'admin' => $admin));
    }


    /**
     * Delete a groupe.
     *
     * @param int $id Id for the groupe.
     *
     * @return Response Redirect to the groupe list.
     */
    public function deleteAction($id) {
        $em = $this->getDoctrine()->getManager();
        $groupe = $em->getRepository('SLNRegisterBundle:Groupe')->find($id);

        if (!$groupe) {
            throw $this->createNotFoundException('Ce groupe n\'existe pas dans la base de données.');
        }

        $em->remove($groupe);
        $em->flush();

        return $this->redirect($this->generateUrl('SLNRegisterBundle_groupe_list'));
    }


    /**
     * List of the groups.
     *
     * @return Response Rendered page.
     */
    public function listAction() {
        $em = $this->getDoctrine()->getManager();
        $groupes = $em->getRepository('SLNRegisterBundle:Groupe')->findAll();

        $groupes_by_categories = array();
        foreach ($groupes as $groupe) {
            if (!array_key_exists($groupe->getCategorieName(), $groupes_by_categories))
                $groupes_by_categories[$groupe->getCategorieName()] = array();
            $groupes_by_categories[$groupe->getCategorieName()][] = $groupe;
        }

        return $this->render('SLNRegisterBundle:Groupe:list.html.twig', array(
          'groupes_by_categories' => $groupes_by_categories));
    }




}

