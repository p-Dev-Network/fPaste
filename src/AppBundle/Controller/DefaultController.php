<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Paste;
use AppBundle\Entity\Support;
use AppBundle\Entity\User;
use AppBundle\Entity\Visit;
use AppBundle\Form\PasteType;
use AppBundle\Form\SupportType;
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

            if($user){
                if(isset($_POST['isAnonymous'])){
                    if($_POST['isAnonymous'] == 'anonymous'){
                        $paste->setIsAnonymous(true);
                    }
                }else{
                    $paste->setIsAnonymous(false);
                }

                $paste->setUser($user);
            }else{
                $paste->setIsAnonymous(true);
            }

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
     * @Route("/faq", name="faq")
     */
    public function faqAction()
    {
        $user = $this->getUser();

        return $this->render('default/Info/faq.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/support", name="support")
     */
    public function supportAction(Request $request){
        $user = $this->getUser();
        $error = 1;

        $support = new Support();
        $form = $this->createForm(SupportType::class, $support);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $support->setDate(new \DateTime("now"));
            $support->setIsReaded(false);
            $support->setIP($this->container->get('request_stack')->getCurrentRequest()->getClientIp());

            if($user){
                $support->setUser($user);
            }else{
                $support->setUser(null);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($support);
            $em->flush();

            $error = 0;
        }

        return $this->render('default/support.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'error' => $error
        ]);
    }

    /**
     * @Route("/last", name="lastPastes")
     */
    public function lastPastesAction()
    {
        $user = $this->getUser();

        $last = $this->getDoctrine()->getRepository('AppBundle:Paste')->findBy([
            'privacy' => 'public',
            'isActive' => true,
            'isDeletedByAdmin' => false,
            'isDeletedByUser' => false,
        ],[
            'date' => 'DESC'
        ], 10);

        return $this->render('default/Paste/last.html.twig', [
            'pastes' => $last,
            'user' => $user
        ]);
    }

    /**
     * @Route("/signup", name="signUp")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
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
     * @param AuthenticationUtils $authenticationUtils
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

            $visit = new Visit();

            if(!$paste){
                $error = 404;
            }else{
                if($paste->isDeletedByUser()){
                    $error = 1;
                }

                if($paste->isDeletedByAdmin()){
                    $error = 2;
                }

                if($user){
                    $visit->setUser($user);
                }else{
                    $visit->setUser(null);
                }

                $visit->setIP($this->container->get('request_stack')->getCurrentRequest()->getClientIp());
                $visit->setDate(new \DateTime("now"));
                $visit->setPaste($paste);

                $em->persist($visit);
                $em->flush();
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
