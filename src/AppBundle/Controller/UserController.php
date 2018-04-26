<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Paste;
use AppBundle\Entity\User;
use AppBundle\Form\ChangeMailType;
use AppBundle\Form\ChangePasswordType;
use AppBundle\Form\PasteType;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="myAccount")
     */
    public function myAccountAction(Request $request)
    {
        $user = $this->getUser();
        $error = 0;

        if(!$user){
            return $this->redirectToRoute('login');
        }else{
            $em = $this->getDoctrine()->getManager();
            $form = $this->createForm(ChangeMailType::class, $user);
            $form->handleRequest($request);

            $formPassword = $this->createForm(ChangePasswordType::class, $user);
            $formPassword->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $check = $em->getRepository('AppBundle:User')->findOneBy([
                    'email' => $user->getEmail()
                ]);

                if($check){
                    $error = 1;
                }else{
                    $em->persist($user);
                    $em->flush();

                    $error = 99;
                }
            }

            if($formPassword->isSubmitted() && $formPassword->isValid()){
                $password = $this->get('security.password_encoder');
                $user->setPassword($password->encodePassword($user, $formPassword->get('password')->get('first')->getData()));

                $em->persist($user);
                $em->flush();

                $error = 98;
            }

            return $this->render('default/User/myAccount.html.twig', [
                'user' => $user,
                'form' => $form->createView(),
                'formPassword' => $formPassword->createView(),
                'error' => $error
            ]);
        }
    }

    /**
     * @Route("/myPastes", name="myPastes")
     */
    public function myPastesAction()
    {
        $user = $this->getUser();

        if(!$user){
            return $this->redirectToRoute('login');
        }else{
            $pastes = $this->getDoctrine()->getRepository('AppBundle:Paste')->findBy([
                'user' => $user,
                'isDeletedByUser' => false,
                'isDeletedByAdmin' => false
            ],[
                'date' => 'DESC'
            ]);

            return $this->render('default/User/myPastes.html.twig', [
                'user' => $user,
                'pastes' => $pastes
            ]);
        }
    }

}