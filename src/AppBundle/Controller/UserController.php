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

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="myAccount")
     */
    public function myAccountAction()
    {
        $user = $this->getUser();

        if(!$user){
            return $this->redirectToRoute('login');
        }else{


            return $this->render('default/User/myAccount.html.twig', [
                'user' => $user
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