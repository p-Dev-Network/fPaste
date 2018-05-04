<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Paste;
use AppBundle\Entity\Recover;
use AppBundle\Entity\Report;
use AppBundle\Entity\Search;
use AppBundle\Entity\Support;
use AppBundle\Entity\User;
use AppBundle\Entity\Visit;
use AppBundle\Form\ChangePasswordType;
use AppBundle\Form\PasteType;
use AppBundle\Form\ReportType;
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

                if(isset($_POST['asAdmin'])){
                    if($_POST['asAdmin'] == 'admin'){
                        $paste->setSendAsAdmin(true);
                    }
                }else{
                    $paste->setSendAsAdmin(false);
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
     * @Route("/admin", name="adminIndex")
     */
    public function adminAction()
    {
        $user = $this->getUser();

        if($user){
            if($user->isAdmin()){
                $pastes = $this->getDoctrine()->getRepository('AppBundle:Paste')->findAll();

                $activePastes = $this->getDoctrine()->getRepository('AppBundle:Paste')->findBy([
                    'isDeletedByUser' => false,
                    'isDeletedByAdmin' => false,
                    'isActive' => true
                ]);

                $deletedPastes = $this->getDoctrine()->getRepository('AppBundle:Paste')->findBy([
                    'isDeletedByUser' => true
                ]);

                $suspendedPastes = $this->getDoctrine()->getRepository('AppBundle:Paste')->findBy([
                    'isDeletedByAdmin' => true
                ]);

                $publicPastes = $this->getDoctrine()->getRepository('AppBundle:Paste')->findBy([
                    'privacy' => 'public',
                    'isDeletedByAdmin' => false,
                    'isDeletedByUser' => false,
                    'isActive' => true
                ]);

                $privatePastes = $this->getDoctrine()->getRepository('AppBundle:Paste')->findBy([
                    'privacy' => 'private',
                    'isDeletedByAdmin' => false,
                    'isDeletedByUser' => false,
                    'isActive' => true
                ]);

                $anonymousPastes = $this->getDoctrine()->getRepository('AppBundle:Paste')->findBy([
                    'isActive' => true,
                    'isDeletedByUser' => false,
                    'isDeletedByAdmin' => false,
                    'user' => null
                ]);

                $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();

                $activeUsers = $this->getDoctrine()->getRepository('AppBundle:User')->findBy([
                    'isActive' => true,
                    'isSuspended' => false
                ]);

                $inactiveUsers = $this->getDoctrine()->getRepository('AppBundle:User')->findBy([
                    'isActive' => false,
                    'isSuspended' => false
                ]);

                $suspendedUsers = $this->getDoctrine()->getRepository('AppBundle:User')->findBy([
                    'isSuspended' => true
                ]);

                return $this->render('default/Admin/index.html.twig', [
                    'user' => $user,
                    'pastes' => $pastes,
                    'activePastes' => $activePastes,
                    'deletedPastes' => $deletedPastes,
                    'suspendedPastes' => $suspendedPastes,
                    'publicPastes' => $publicPastes,
                    'privatePastes' => $privatePastes,
                    'anonymousePastes' => $anonymousPastes,
                    'users' => $users,
                    'activeUsers' => $activeUsers,
                    'inactiveUsers' => $inactiveUsers,
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
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/recover", name="recoverPassword")
     */
    public function recoverPasswordAction(\Swift_Mailer $mailer)
    {
        $user = $this->getUser();

        if(!$user){
            if(isset($_POST['email']) && strlen($_POST['email']) > 3){
                $email = $_POST['email'];
                $error = 1;

                $account = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy([
                    'email' => $email
                ]);

                if($account){
                    $recover = new Recover();
                    $recover->setDate(new \DateTime("now"));
                    $recover->setEmail($email);
                    $recover->setExpiredDate(new \DateTime("now + 24 hours"));
                    $recover->setIsUsed(false);
                    $code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
                    $recover->setCode($code);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($recover);
                    $em->flush();

                    $message = (new \Swift_Message('[fPaste.me] Change Password'))
                        ->setFrom('support@fpaste.me')
                        ->setTo($email)
                        ->setBody(
                            $this->renderView(
                                'default/Mails/recoverPassword.html.twig', [
                                    'email' => $email,
                                    'code' => $code
                                ]
                            ),
                            'text/html'
                        );

                    $mailer->send($message);
                }
            }else{
                $error = 0;
            }

            return $this->render('default/recover.html.twig', [
                'user' => $user,
                'error' => $error
            ]);
        }else{
            return $this->redirectToRoute('myAccount');
        }
    }

    /**
     * @Route("/changePassword", name="changePassword")
     */
    public function changePasswordAction(Request $request)
    {
        $user = $this->getUser();

        if(!$user){
            $email = $_GET['email'];
            $code = $_GET['code'];
            $error = 0;

            $check = $this->getDoctrine()->getRepository('AppBundle:Recover')->findOneBy([
                'email' => $email,
                'code' => $code
            ]);

            if($check){
                if($check->isUsed()){
                    $error = 2;
                }else{
                    $actualDate = new \DateTime("now");

                    if($actualDate < $check->getExpiredDate()){
                        $userPassword = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy([
                            'email' => $email
                        ]);

                        if($userPassword){
                            $form = $this->createForm(ChangePasswordType::class, $userPassword);
                            $form->handleRequest($request);

                            if($form->isSubmitted() && $form->isValid()){
                                $password = $this->get('security.password_encoder');
                                $userPassword->setPassword($password->encodePassword($userPassword, $form->get('password')->get('first')->getData()));

                                $em = $this->getDoctrine()->getManager();
                                $em->persist($userPassword);
                                $em->flush();

                                $check->setIsUsed(true);
                                $check->setChangeDate(new \DateTime("now"));
                                $check->setIP($this->container->get('request_stack')->getCurrentRequest()->getClientIp());
                                $check->setUser($userPassword);

                                $em->persist($check);
                                $em->flush();

                                $error = 5;
                            }else{
                                $error = 6;
                            }

                            return $this->render('default/changePassword.html.twig', [
                                'user' => $user,
                                'form' => $form->createView(),
                                'error' => $error
                            ]);
                        }else{
                            $error = 4;
                        }
                    }else{
                        $error = 3;
                    }
                }
            }else{
                $error = 1;
            }

            return $this->render('default/changePassword.html.twig', [
                'user' => $user,
                'error' => $error
            ]);
        }else{
            return $this->redirectToRoute('myAccount');
        }
    }

    /**
     * @Route("/privacy", name="privacy")
     */
    public function privacyAction()
    {
        $user = $this->getUser();

        return $this->render('default/Info/privacy.html.twig', [
            'user' => $user
        ]);

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
    public function supportAction(Request $request, \Swift_Mailer $mailer){
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

            $message = (new \Swift_Message('[fPaste.me] New Support Ticket'))
                ->setFrom('support@fpaste.me')
                ->setTo('support@fpaste.me')
                ->setBody(
                    $this->renderView(
                        'default/Mails/ticket.html.twig', [
                            'ticket' => $support
                        ]
                    ),
                    'text/html'
                );

            $mailer->send($message);

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
     * @Route("/top50", name="top50")
     */
    public function top50Action()
    {
        $user = $this->getUser();

        $pastes = $this->getDoctrine()->getManager()->createQuery("SELECT p.title as title, p.url as url, p.date as date, count(v) as counter FROM AppBundle:Visit v JOIN AppBundle:Paste p WITH v.paste = p.id WHERE p.privacy = 'public' AND p.isDeletedByUser = FALSE AND p.isDeletedByAdmin = FALSE AND p.isActive = TRUE GROUP BY v.paste ORDER BY counter DESC")->getResult();

        return $this->render('default/Paste/top50.html.twig', [
            'user' => $user,
            'pastes' => $pastes
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
            $newUser = new User();
            $form = $this->createForm(UserType::class, $newUser);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $newUser->setSignUpDate(new \DateTime("now"));
                $password = $this->get('security.password_encoder');
                $newUser->setPassword($password->encodePassword($newUser, $form->get('password')->get('first')->getData()));

                $em = $this->getDoctrine()->getManager();
                $em->persist($newUser);
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
     * @Route("/search", name="searchPastes")
     */
    public function searchPastesAction(Request $request)
    {
        $user = $this->getUser();

        if(isset($_POST['inputSearch'])){
            $input = $_POST['inputSearch'];

            if(strlen($input) == 0){
                return $this->redirect($request->headers->get('referer'));
            }elseif(strlen($input) == 1 && $input[0] == ' '){
                return $this->redirect($request->headers->get('referer'));
            }

            $em = $this->getDoctrine()->getManager();

            $search = new Search();

            if($user){
                $search->setUser($user);
            }else{
                $search->setUser(null);
            }

            $search->setDate(new \DateTime("now"));
            $search->setValue($input);
            $search->setIP($this->container->get('request_stack')->getCurrentRequest()->getClientIp());

            $em->persist($search);
            $em->flush();

            $result = $em->getRepository("AppBundle:Paste")->createQueryBuilder('p')
                ->where('p.content LIKE :content')
                ->andWhere("p.privacy = 'public'")
                ->andWhere('p.isDeletedByUser = FALSE')
                ->andWhere('p.isDeletedByAdmin = FALSE')
                ->andWhere('p.isActive = TRUE')
                ->setParameter('content', '%'.$input.'%')
                ->getQuery()
                ->getResult();

            return $this->render('default/search.html.twig', [
                'user' => $user,
                'search' => $result,
                'searchBy' => $input
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

    /**
     * @Route("/{url}/changePrivacy", name="changePrivacyPaste")
     */
    public function changePrivacyPasteAction($url, Request $request)
    {
        $user = $this->getUser();

        if($user){
            $paste = $this->getDoctrine()->getRepository('AppBundle:Paste')->findOneBy([
                'url' => $url
            ]);

            if($paste){
                if($paste->getUser() == $user){
                    if($paste->getPrivacy() == 'public'){
                        $paste->setPrivacy('private');
                    }else{
                        $paste->setPrivacy('public');
                    }

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($paste);
                    $em->flush();

                    return $this->redirect($request->headers->get('referer'));
                }
            }

            return $this->redirectToRoute('viewPaste', [
                'url' => $url
            ]);
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/{url}/edit", name="editPaste")
     */
    public function editPasteAction($url, Request $request)
    {
        $user = $this->getUser();

        if($user){
            $paste = $this->getDoctrine()->getRepository('AppBundle:Paste')->findOneBy([
                'url' => $url
            ]);

            if($paste){
                if($paste->getUser() == $user){
                    $form = $this->createForm(PasteType::class, $paste);
                    $form->handleRequest($request);

                    if($form->isSubmitted() && $form->isValid()){
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($paste);
                        $em->flush();

                        return $this->redirectToRoute('viewPaste', ['url' => $url]);
                    }

                    return $this->render('default/Paste/editPaste.html.twig', [
                        'user' => $user,
                        'form' => $form->createView()
                    ]);
                }
            }

            return $this->redirectToRoute('viewPaste', ['url' => $url]);
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/{url}/confirmDelete", name="confirmDelete")
     */
    public function confirmDeleteAction($url)
    {
        $user = $this->getUser();

        if($user){
            $paste = $this->getDoctrine()->getRepository('AppBundle:Paste')->findOneBy([
                'url' => $url
            ]);

            if($paste){
                if($paste->getUser() == $user){
                    return $this->render('default/Paste/confirmDeletePaste.html.twig', [
                        'user' => $user,
                        'paste' => $paste
                    ]);
                }else{
                    return $this->redirectToRoute('viewPaste', ['url' => $url]);
                }
            }else{
                return $this->redirectToRoute('myPastes');
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/{url}/delete", name="deletePaste")
     * @param $url
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deletePasteAction($url)
    {
        $user = $this->getUser();

        if($user){
            $paste = $this->getDoctrine()->getRepository('AppBundle:Paste')->findOneBy([
                'url' => $url
            ]);

            if($paste){
                if($paste->getUser() == $user){
                    $paste->setDeleteDate(new \DateTime("now"));
                    $paste->setIsActive(false);
                    $paste->setIsDeletedByUser(true);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($paste);
                    $em->flush();
                }

                return $this->redirectToRoute('viewPaste', ['url' => $url]);
            }else{
                return $this->redirectToRoute('myPastes');
            }
        }else{
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/{url}/report", name="reportPaste")
     */
    public function reportPasteAction($url, Request $request, \Swift_Mailer $mailer)
    {
        $user = $this->getUser();
        $error = 1;

        $paste = $this->getDoctrine()->getRepository('AppBundle:Paste')->findOneBy([
            'url' => $url,
            'isDeletedByAdmin' => false,
            'isDeletedByUser' => false,
            'isActive' => true
        ]);

        if($paste){
            $report = new Report();
            $form = $this->createForm(ReportType::class, $report);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $report->setIP($this->container->get('request_stack')->getCurrentRequest()->getClientIp());
                $report->setDate(new \DateTime("now"));

                if($user){
                    $report->setUser($user);
                }else{
                    $report->setUser(null);
                }

                $report->setIsActive(true);
                $report->setPaste($paste);

                $em = $this->getDoctrine()->getManager();
                $em->persist($report);
                $em->flush();

                $check = $this->getDoctrine()->getRepository('AppBundle:Report')->findBy([
                    'isActive' => true,
                    'paste' => $paste
                ]);

                if(count($check) > 3){
                    $message = (new \Swift_Message('[fPaste.me] New Repport'))
                        ->setFrom('support@fpaste.me')
                        ->setTo('support@fpaste.me')
                        ->setBody(
                            $this->renderView(
                                'default/Mails/report.html.twig', [
                                    'report' => $report
                                ]
                            ),
                            'text/html'
                        );

                    $mailer->send($message);
                }

                $error = 0;
            }

            return $this->render('default/Paste/report.html.twig',[
                'user' => $user,
                'form' => $form->createView(),
                'error' => $error
            ]);
        }else{
            return $this->redirectToRoute('viewPaste', ['url' => $url]);
        }
    }
}
