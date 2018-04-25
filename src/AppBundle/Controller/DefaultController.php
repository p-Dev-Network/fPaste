<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Paste;
use AppBundle\Form\PasteType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $paste = new Paste();
        $form = $this->createForm(PasteType::class, $paste);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $paste->setDate(new \DateTime("now"));
            $paste->setDeleteDate(null);
            $paste->setIP($this->container->get('request_stack')->getCurrentRequest()->getClientIp());

            $em = $this->getDoctrine()->getManager();
            $em->persist($paste);
            $em->flush();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('default/index.html.twig', [
           'form' => $form->createView()
        ]);
    }
}
