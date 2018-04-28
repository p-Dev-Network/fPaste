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

    /**
     * @Route("/users/{id}/suspend", name="adminSuspendUser")
     */
    public function adminSuspendUserAction($id)
    {
        $user = $this->getUser();

        if($user){
            if($user->isAdmin()){
                $u = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

                if($u){
                    $u->setIsSuspended(true);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($u);
                    $em->flush();
                }

                return $this->redirectToRoute('adminUserProfile', ['id' => $id]);
            }else{
                return $this->redirectToRoute('homepage');
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/users/{id}/approve", name="adminApproveUser")
     */
    public function adminApproveUser($id)
    {
        $user = $this->getUser();

        if($user){
            if($user->isAdmin()){
                $u = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

                if($u){
                    $u->setIsSuspended(false);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($u);
                    $em->flush();
                }

                return $this->redirectToRoute('adminUserProfile', ['id' => $id]);
            }else{
                return $this->redirectToRoute('homepage');
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/reports", name="adminReports")
     */
    public function adminReportsAction()
    {
        $user = $this->getUser();

        if($user){
            if($user->isAdmin()){
                $reports = $this->getDoctrine()->getRepository('AppBundle:Report')->findAll();

                $activeReports = $this->getDoctrine()->getRepository('AppBundle:Report')->findBy([
                    'isActive' => true
                ],[
                    'date' => 'DESC'
                ]);

                $closedReports = $this->getDoctrine()->getRepository('AppBundle:Report')->findBy([
                    'isActive' => false
                ],[
                    'date' => 'DESC'
                ]);

                $pendingReports = $this->getDoctrine()->getRepository('AppBundle:Report')->findBy([
                    'isReaded' => false
                ],[
                    'date' => 'DESC'
                ]);

                return $this->render('default/Admin/reports.html.twig', [
                    'user' => $user,
                    'reports' => $reports,
                    'activeReports' => $activeReports,
                    'closedReports' => $closedReports,
                    'pendingReports' => $pendingReports
                ]);
            }else{
                return $this->redirectToRoute('homepage');
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }
}