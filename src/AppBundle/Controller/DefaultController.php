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
        $em = $this->getDoctrine()->getManager();
        $uniqueURL = false;

        $paste = new Paste();
        $form = $this->createForm(PasteType::class, $paste);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if(!$paste->getTitle()){
                $paste->setTitle('Untitled');
            }

            $paste->setDate(new \DateTime("now"));
            $paste->setDeleteDate(null);
            $paste->setIP($this->container->get('request_stack')->getCurrentRequest()->getClientIp());


            do{
                $url = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
                $check = $em->getRepository('AppBundle:Paste')->findOneBy([
                    'url' => $url
                ]);

                if(!$check){
                    $paste->setUrl($url);
                    $uniqueURL = true;
                }
            }while($uniqueURL == false);

            $em->persist($paste);
            $em->flush();

            return $this->redirectToRoute('viewPaste', ['url' => $url]);
        }

        return $this->render('default/index.html.twig', [
           'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{url}", name="viewPaste")
     */
    public function viewPasteAction($url, Request $request)
    {
        $error = 0;
        $em = $this->getDoctrine()->getManager();

        $paste = $em->getRepository('AppBundle:Paste')->findOneBy([
            'url' => $url
        ]);

        if(!$paste){
            $error = 404;
        }else{
            if($paste->isDeletedByUser()){
                $error = 1;
            }

            if($paste->isDeletedByAdmin()){
                $error = 2;
            }
        }

        if($error == 0){
            return $this->render('default/Paste/paste.html.twig', [
                'error' => $error,
                'paste' => $paste
            ]);
        }else{
            return $this->render('default/Paste/paste.html.twig', [
                'error' => $error
            ]);
        }

    }
}
