<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/users", name="adminUsers")
     */
    public function adminUsersAction()
    {
        $user = $this->getUser();

        if($user){
            if($user->isAdmin()){
                $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();

                $suspendedUsers = $this->getDoctrine()->getRepository('AppBundle:User')->findBy([
                    'isSuspended' => true
                ],[
                    'signUpDate' => 'DESC'
                ]);

                return $this->render('default/Admin/users.html.twig', [
                    'user' => $user,
                    'users' => $users,
                    'suspendedUsers' => $suspendedUsers
                ]);
            }else{
                return $this->redirectToRoute('homepage');
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/users/{id}", name="adminUserProfile")
     */
    public function adminUserProfile($id)
    {
        $user = $this->getUser();

        if($user){
            if($user->isAdmin()){
                $u = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

                if($u){
                    $error = 0;
                }else{
                    $error = 1;
                }

                return $this->render('default/Admin/user.html.twig', [
                    'user' => $user,
                    'u' => $u,
                    'error' => $error
                ]);
            }else{
                return $this->redirectToRoute('homepage');
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }

}