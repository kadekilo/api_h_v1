<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Unirest;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $session = $request->getSession();
        if (empty($session) || $session->get("name") != "SES_HO_1") {
            return $this->redirect('/login?code=302');
        }
        // redirect the user to a page
        $datos_usuario = $session->get("datos_usuario");
        if (in_array('ADMIN', $datos_usuario->roles) == true || in_array('PAGE_1',
                $datos_usuario->roles) == true) {
            return $this->redirect('/page/1');
        }
        if (in_array('PAGE_2', $datos_usuario->roles) == true) {
            return $this->redirect('/page/2');
        }
    }

    /**
     * @Route("/errorpage/", name="errorpage")
     */
    public function errorpageAction(Request $request)
    {
        if (!empty($request->query->get("code"))) {
            $code_error = $request->query->get("code");
            $texto1     = "404 Not Found";
            $response   = new Response();
            if ($code_error == 403) {
                $texto1 = "403 Forbidden";
                $texto2 = "You don't have permission to access to this page!";
                $response->setStatusCode(Response::HTTP_FORBIDDEN);
            } else {
                $response->setStatusCode(Response::HTTP_NOT_FOUND);
            }
            return $this->render('default/errors.html.twig',
                    [
                        'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
                        'error_mostrado' => $texto1,
                        'texto_descriptivo_error' => $texto2,
                    ], $response);
        }
    }

    /**
     * @Route("/page/1", name="page1")
     */
    public function page1Action(Request $request)
    {
        $session = $request->getSession();
        if (empty($session) || $session->get("name") != "SES_HO_1") {
            return $this->redirect('/login?code=302');
        }
        // replace this example code with whatever you need
        $datos_usuario = $session->get("datos_usuario");
        if (in_array('ADMIN', $datos_usuario->roles) == true || in_array('PAGE_1',
                $datos_usuario->roles) == true) {
            return $this->render('default/page_1.html.twig',
                    [
                        'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
                        'nameee' => $datos_usuario->name
            ]);
        } else {
            return $this->redirect('/errorpage?code=403');
        }
    }

    /**
     * @Route("/page/2", name="page2")
     */
    public function page2Action(Request $request)
    {
        $session = $request->getSession();
        if (empty($session) || $session->get("name") != "SES_HO_1") {
            return $this->redirect('/login?code=302');
        }
        // replace this example code with whatever you need
        $datos_usuario = $session->get("datos_usuario");
        if (in_array('ADMIN', $datos_usuario->roles) == true || in_array('PAGE_2',
                $datos_usuario->roles) == true) {
            return $this->render('default/page_2.html.twig',
                    [
                        'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
                        'nameee' => $datos_usuario->name
            ]);
        } else {
            return $this->redirect('/errorpage?code=403');
        }
    }

    /**
     * @Route("/login/", name="login")
     */
    public function loginAction(Request $request)
    {
        if (!empty($request) && !empty($request->get('h_username')) && !empty($request->get('h_password'))) {
            $session = $request->getSession();
            if (!empty($session) && $session->get('name') == "SES_HO_1") {
                return $this->redirect('/');
            }
            // No session, requesq username and pass
            $h_username = $request->get('h_username');
            $h_password = $request->get('h_password');
            // Connect to API - basic auth
            $url        = "http://demohola.com/api/v1/user";
            Unirest\Request::Auth($h_username, $h_password, CURLAUTH_BASIC);
            $response   = Unirest\Request::get($url);
            if ($response->code == '404' || $response->code == "403") {
                return $this->redirect('/login?code=403');
            } else if ($response->code == "200") {
                // Create Session and redirect
                $datos_usuario_ses = $response->body->datos_usuario;
                $session           = new Session();
                $session->set('name', 'SES_HO_1');
                $session->set('datos_usuario', $datos_usuario_ses);
                // Redirect Index page to consider the roles :)
                return $this->redirect('/');
            }
        } else {
            $response     = new Response();
            $texxtoerrors = "";
            if (!empty($request->query->get("code"))) {
                $code = $request->query->get("code");
                if ($code == 403) {
                    $texxtoerrors = "Bad credentials!";
                } else if ($code == 302) {
                    $texxtoerrors = "Please, login first!";
                    $response->setStatusCode(Response::HTTP_FOUND);
                }
            } else {
                $response->setStatusCode(Response::HTTP_OK);
            }
            return $this->render('default/login.html.twig',
                    [
                        'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
                        'texto_errores' => $texxtoerrors,
                    ], $response);
        }
    }

    /**
     * @Route("/logout/", name="logout")
     */
    public function logoutAction()
    {
        $session = new Session();
        $session->set('name', '');
        $session->set('datos_usuario', false);
        return $this->redirect('/login');
    }
}