<?php

namespace GameBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use GameBundle\Entity\User;
use GameBundle\Repository\UserRepository;
use GameBundle\Exception\MessageException;
use GameBundle\Form\ChangePasswordType;
use GameBundle\Form\RegisterType;

class SecurityController extends BaseController
{
    /**
     * @Route("/login", name="_security_login")
     * @Method("GET")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return array(
            'last_username' => $lastUsername,
            'error' => $error
        );
    }

    /**
     * @Route("/login", name="_security_login_check")
     * @Method("POST")
     * @Template("GameBundle:Security:login.html.twig")
     */
    public function loginCheckAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $username = trim($request->get('_username'));
        $password = trim($request->get('_password'));

        try {
            /* @var $user User  */
            $user = $this->getRepository('user')->findOneByUsername($username);
            if ($user) {
                if ($this->get('user.helper')->checkPassword($user, $password)) {

                    $response = $this->redirect($this->generateUrl('homepage'));
                    $this->_authenticateUser($user, $request, $response);

                    return $response;
                } else {
                    throw new MessageException($this->trans('error.passwordNotCorrect'));
                }
            } else {
                throw new MessageException($this->trans('error.userNotFound'));
            }

        } catch (MessageException $ex) {
            $this->flashMessage($ex->getMessage(), 'error');
        }

        return array(
            'last_username' => $username,
            'error' => $error
        );
    }

    /**
     * @Route("/recover")
     * @Method("GET|POST")
     * @Template()
     */
    public function recoverAction(Request $request)
    {
        $form = $this->createFormBuilder(null, array('intention' => 'recover'))
            ->add('email', 'email')
            ->getForm();

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            try {
                $data = $form->getData();
                if (! $user = $this->getRepository('user')->findOneByEmail($data['email'])) {
                    throw new MessageException($this->trans('error.userNotFoundByEmail'));
                }

                $this->get('mail.helper')->sendRecoverPasswordLink($user);
                $this->flashMessage('email.linkHasBeenSent');

                return $this->redirect($this->generateUrl('_security_login'));

            } catch (MessageException $ex) {
                $this->flashMessage($ex->getMessage(), 'error');
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/changePassword", name="security_change_password")
     * @Method("GET|POST")
     * @Template("MainBundle:Security:changePassword.html.twig")
     */
    public function changeUserPasswordAction(Request $request)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->getRepository('user');
        /* @var $user User  */
        $user = $this->getUser();

        $form = $this->createForm(new ChangePasswordType(), $user, array(
            'action' => $this->generateUrl('security_change_password')
        ));

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $this->get('user.helper')->updatePassword($user);

            $userRepository->save($user);

            $this->flashMessage('user.passwordChanged');

            return $this->redirect($this->generateUrl('homepage'));
        }

        return array(
            'form' => $form->createView(),
            'headerTip' => $this->trans('user.changePasswordHeaderTip')
        );
    }

    /**
     * @Route("/change/{key}")
     * @Method("GET|POST")
     * @Template()
     */
    public function changePasswordAction($key, Request $request)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->getRepository('user');
        /* @var $user User  */
        if (!$user = $userRepository->getUserBySecretKey($key)) {
            throw new MessageException($this->trans('error.invalidUrl'));
        }

        $form = $this->createForm(new ChangePasswordType(), $user, array(
            'action' => $this->generateUrl('game_security_changepassword', ['key' => $key])
        ));

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $this->get('user.helper')->updatePassword($user);
            $userRepository->save($user);

            return $this->redirect($this->generateUrl('_security_login'));
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/register")
     * @Method("GET|POST")
     * @Template()
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(new RegisterType(), $user, array(
            'action' => $this->generateUrl('game_security_register')
        ));

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $this->get('user.helper')->updatePassword($user);
            $this->getRepository('user')->save($user);
            return $this->redirect($this->generateUrl('_security_login'));
        }

        return array(
            'form' => $form->createView(),
        );
    }

    protected function _authenticateUser(User $user, $request, $response)
    {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('helper.user_rememberer')->rememberUser($token, $request, $response);
    }
}