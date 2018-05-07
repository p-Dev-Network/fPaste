<?php

namespace AppBundle\Controller;

use AppBundle\Entity\MailVerification;
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
    public function myAccountAction(Request $request, \Swift_Mailer $mailer)
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

                    $validations = $this->getDoctrine()->getRepository('AppBundle:MailVerification')->findBy([
                        'user' => $user,
                        'isUsed' => false,
                        'isValid' => true
                    ]);

                    foreach ($validations as $v){
                        $v->setIsValid(false);

                        $em->persist($v);
                        $em->flush();
                    }

                    $code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);

                    $verify = new MailVerification();
                    $verify->setIsUsed(false);
                    $verify->setDate(new \DateTime("now"));
                    $verify->setExpiredDate(new \DateTime("now + 24 hours"));
                    $verify->setEmail($user->getEmail());
                    $verify->setUser($user);
                    $verify->setCode($code);

                    $em->persist($verify);
                    $em->flush();

                    $user->setIsActive(false);

                    $em->persist($user);
                    $em->flush();

                    $message = (new \Swift_Message('[fPaste.me] Verify your Account'))
                        ->setFrom('support@fpaste.me')
                        ->setTo($verify->getEmail())
                        ->setBody(
                            $this->renderView(
                                'default/Mails/verifyEmail.html.twig', [
                                    'code' => $code,
                                    'email' => $verify->getEmail()
                                ]
                            ),
                            'text/html'
                        );

                    $mailer->send($message);

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