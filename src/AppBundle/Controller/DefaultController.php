<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Paste;
use AppBundle\Entity\User;
use AppBundle\Form\PasteType;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();
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
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/last", name="lastPastes")
     */
    public function lastPastesAction()
    {
        $last = $this->getDoctrine()->getRepository('AppBundle:Paste')->findBy([
            'privacy' => 'public',
            'isActive' => true,
            'isDeletedByAdmin' => false,
            'isDeletedByUser' => false,
        ],[
            'date' => 'DESC'
        ], 10);

        return $this->render('default/Paste/last.html.twig', [
            'pastes' => $last
        ]);
    }



    /**
     * @Route("/signup", name="signUp")
     */
    public function signUpAction(Request $request)
    {
        $user = $this->getUser();

        if(!$user){
            $user = new User();
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $user->setSignUpDate(new \DateTime("now"));
                $password = $this->get('security.password_encoder');
                $user->setPassword($password->encodePassword($user, $form->get('password')->get('first')->getData()));

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('login');
            }

            return $this->render('default/signUp.html.twig', [
                'form' => $form->createView(),
                'user' => $user
            ]);
        }else{
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @Route("/{url}", name="viewPaste")
     * @param $url
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewPasteAction($url, Request $request, AuthenticationUtils $authenticationUtils)
    {
        $user = $this->getUser();

        if($url == 'login') {
            $error = $authenticationUtils->getLastAuthenticationError();

            $lastUsername = $authenticationUtils->getLastUsername();

            return $this->render('default/security.html.twig', array(
                'last_username' => $lastUsername,
                'error' => $error,
                'user' => $user
            ));
        }else{
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
                    'paste' => $paste,
                    'user' => $user
                ]);
            }else{
                return $this->render('default/Paste/paste.html.twig', [
                    'error' => $error,
                    'user' => $user
                ]);
            }
        }
    }
}
