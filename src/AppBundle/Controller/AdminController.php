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
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
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
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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

    /**
     * @Route("/reports/{id}", name="viewReport")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewReportAction($id)
    {
        $user = $this->getUser();
        $error = 0;

        if($user){
            if($user->isAdmin()){
                $report = $this->getDoctrine()->getRepository('AppBundle:Report')->find($id);

                if(!$report){
                    $error = 1;

                    return $this->render('default/Admin/report.html.twig', [
                        'user' => $user,
                        'error' => $error
                    ]);
                }else{
                    if(!$report->isReaded()){
                        $report->setIsReaded(true);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($report);
                        $em->flush();
                    }

                    return $this->render('default/Admin/report.html.twig', [
                        'user' => $user,
                        'error' => $error,
                        'report' => $report
                    ]);
                }
            }else{
                return $this->redirectToRoute('homepage');
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/reports/{id}/close", name="closeReport")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function closeReportAction($id)
    {
        $user = $this->getUser();

        if($user){
            if($user->isAdmin()){
                $report = $this->getDoctrine()->getRepository('AppBundle:Report')->find($id);

                if($report){
                    $report->setIsActive(false);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($report);
                    $em->flush();
                }

                return $this->redirectToRoute('viewReport', ['id' => $report->getId()]);
            }else{
                return $this->redirectToRoute('homepage');
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/pastes", name="adminPastes")
     */
    public function adminPastesAction()
    {
        $user = $this->getUser();

        if($user){
            if($user->isAdmin()){
                $public = $this->getDoctrine()->getRepository('AppBundle:Paste')->findBy([
                    'privacy' => 'public',
                    'isDeletedByUser' => false,
                    'isDeletedByAdmin' => false
                ],[
                    'date' => 'DESC'
                ]);

                $private = $this->getDoctrine()->getRepository('AppBundle:Paste')->findBy([
                    'privacy' => 'private',
                    'isDeletedByAdmin' => false,
                    'isDeletedByUser' => false
                ],[
                    'date' => 'DESC'
                ]);

                $deleted = $this->getDoctrine()->getRepository('AppBundle:Paste')->findBy([
                    'isActive' => false
                ],[
                    'date' => 'DESC'
                ]);

                return $this->render('default/Admin/pastes.html.twig', [
                    'user' => $user,
                    'public' => $public,
                    'private' => $private,
                    'deleted' => $deleted
                ]);
            }else{
                return $this->redirectToRoute('homepage');
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/pastes/{url}", name="adminViewPaste")
     * @param $url
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function adminViewPasteAction($url)
    {
        $user = $this->getUser();
        $error = 0;

        if($user){
            if($user->isAdmin()){
                $paste = $this->getDoctrine()->getRepository('AppBundle:Paste')->findOneBy([
                    'url' => $url
                ]);

                if($paste){
                    return $this->render('default/Admin/paste.html.twig', [
                        'user' => $user,
                        'error' => $error,
                        'paste' => $paste
                    ]);
                }else{
                    $error = 1;

                    return $this->render('default/Admin/paste.html.twig', [
                        'user' => $user,
                        'error' => $error
                    ]);
                }
            }else{
                return $this->redirectToRoute('homepage');
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/pastes/{url}/delete", name="adminDeletePaste")
     * @param $url
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function adminDeletePasteAction($url)
    {
        $user = $this->getUser();

        if($user){
            if($user->isAdmin()){
                $paste = $this->getDoctrine()->getRepository('AppBundle:Paste')->findOneBy([
                    'url' => $url
                ]);

                if($paste){
                    $paste->setIsActive(false);
                    $paste->setIsDeletedByAdmin(true);
                    $paste->setIsDeletedByUser(false);
                    $paste->setDeleteDate(new \DateTime("now"));

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($paste);
                    $em->flush();
                }

                return $this->redirectToRoute('adminViewPaste', ['url' => $url]);
            }else{
                return $this->redirectToRoute('homepage');
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/support", name="supportIndex")
     */
    public function supportIndexAction()
    {
        $user = $this->getUser();

        if($user){
            if($user->isAdmin()){
                $abuse = $this->getDoctrine()->getRepository('AppBundle:Support')->findBy([
                    'isClosed' => false,
                    'category' => 'abuse'
                ],[
                    'date' => 'DESC'
                ]);

                $tech = $this->getDoctrine()->getRepository('AppBundle:Support')->findBy([
                    'isClosed' => false,
                    'category' => 'technicalProblems'
                ], [
                    'date' => 'DESC'
                ]);

                $other = $this->getDoctrine()->getRepository('AppBundle:Support')->findBy([
                    'isClosed' => false,
                    'category' => 'other'
                ], [
                    'date' => 'DESC'
                ]);

                $closed = $this->getDoctrine()->getRepository('AppBundle:Support')->findBy([
                    'isClosed' => true
                ], [
                    'date' => 'DESC'
                ]);

                return $this->render('default/Admin/support.html.twig', [
                    'user' => $user,
                    'abuse' => $abuse,
                    'tech' => $tech,
                    'other' => $other,
                    'closed' => $closed
                ]);
            }else{
                return $this->redirectToRoute('homepage');
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/support/{id}", name="adminViewTicket")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function adminViewReportTicket($id)
    {
        $user = $this->getUser();
        $error = 0;

        if($user){
            if($user->isAdmin()){
                $ticket = $this->getDoctrine()->getRepository('AppBundle:Support')->find($id);

                if($ticket){
                    if(!$ticket->isReaded()){
                        $ticket->setIsReaded(true);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($ticket);
                        $em->flush();
                    }

                    return $this->render('default/Admin/ticket.html.twig', [
                        'user' => $user,
                        'error' => $error,
                        'ticket' => $ticket
                    ]);
                }else{
                    $error = 1;

                    return $this->render('default/Admin/ticket.html.twig', [
                        'user' => $user,
                        'error' => $error
                    ]);
                }
            }else{
                return $this->redirectToRoute('homepage');
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/support/{id}/close", name="closeTicket")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function closeTicketAction($id)
    {
        $user = $this->getUser();

        if($user){
            if($user->isAdmin()){
                $ticket = $this->getDoctrine()->getRepository('AppBundle:Support')->find($id);

                if($ticket){
                    $ticket->setIsClosed(true);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($ticket);
                    $em->flush();
                }

                return $this->redirectToRoute('adminViewTicket', ['id' => $id]);
            }else{
                return $this->redirectToRoute('homepage');
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }
}