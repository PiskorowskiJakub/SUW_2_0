<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Lecture;
use AppBundle\Form\LectureType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Lecture controller.
 *
 * @Route("lecture")
 */
class LectureController extends Controller
{
    /**
     * Lists all lecture entities.
     *
     * @Route("/", name="lecture_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $lectures = $em->getRepository('AppBundle:Lecture')->findAll();

        return $this->render('lecture/index.html.twig', array(
            'lectures' => $lectures,
        ));
    }

    /**
     * Creates a new lecture entity.
     *
     * @Route("/lecture/new", name="lecture_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $user = $this->getUser();
        $lecture = new Lecture();

        $form = $this->createForm('AppBundle\Form\LectureType', $lecture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $file = $lecture->getSciezka();
            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('brochures_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }
            $file->setSciezka($fileName);


            $em->persist($lecture);
            $em->flush();

            return $this->redirect($this->generateUrl('lecture_show'));
        }

        return $this->render('lecture/new.html.twig', array(
            'profile' => $user,
            'lecture' => $lecture,
            'form' => $form->createView(),
        ));
    }

    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    /**
     * Finds and displays a lecture entity.
     *
     * @Route("/{id}", name="lecture_show")
     * @Method("GET")
     */
    public function showAction(Lecture $lecture)
    {
        $deleteForm = $this->createDeleteForm($lecture);

        return $this->render('lecture/show.html.twig', array(
            'lecture' => $lecture,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing lecture entity.
     *
     * @Route("/{id}/edit", name="lecture_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Lecture $lecture)
    {
        $deleteForm = $this->createDeleteForm($lecture);
        $editForm = $this->createForm('AppBundle\Form\LectureType', $lecture);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('lecture_edit', array('id' => $lecture->getId()));
        }

        return $this->render('lecture/edit.html.twig', array(
            'lecture' => $lecture,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a lecture entity.
     *
     * @Route("/{id}", name="lecture_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Lecture $lecture)
    {
        $form = $this->createDeleteForm($lecture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($lecture);
            $em->flush();
        }

        return $this->redirectToRoute('lecture_index');
    }

    /**
     * Creates a form to delete a lecture entity.
     *
     * @param Lecture $lecture The lecture entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Lecture $lecture)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('lecture_delete', array('id' => $lecture->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}