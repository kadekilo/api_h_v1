<?php

namespace AppBundle\Controller\Api\v1;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class UserController extends Controller
{
    public function is_loged_in($request)
    {
        $usuario = $request->headers->get('php-auth-user');
        $passwd  = $request->headers->get('php-auth-pw');
        if (empty($usuario) || empty($passwd)) {
            return false;
        }
        $passenc = md5("<<399s8f>>".$passwd);
        $bd_user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['username' => $usuario, 'password' => $passenc]);
        if (!empty($bd_user)) {
            $T_roles = $bd_user->getRoles();
            if (!empty($T_roles) && is_array($T_roles)) {
                return [
                    'id' => $bd_user->getId(),
                    'name' => $bd_user->getName(),
                    'username' => $bd_user->getUsername(),
                    'roles' => $T_roles];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @Route("/api/v1/user")
     * @Method("POST")
     * @ApiDoc(
     *  description="Create a new User. You need ADMIN role to do this.",
     *  parameters={
     *      {"name"="s_name", "dataType"="string", "required"=true, "description"="Name of the USER"},
     *      {"name"="s_username", "dataType"="string", "required"=true, "description"="UserName used by USER in login"},
     *      {"name"="s_password", "dataType"="string", "required"=true, "description"="Password of the USER"},
     *      {"name"="s_roles", "dataType"="simple_array", "required"=false, "description"="Roles [ADMIN,PAGE_1,PAGE_2]"},
     *  },
     *  headers={
     *      {"name"="AUTH_USER", "description"="Basic Auth User"},
     *      {"name"="AUTH_PW ", "description"="Basic Auth Password"},
     *  },
     *  statusCodes={
     *      201="Created USER sucessfull",
     *      304="Not Authorized",
     *      400="Bad Request (the username is in use or fields missing)",
     *  },
     * )
     */
    public function newAction(Request $request)
    {
        $user_cache = $this->is_loged_in($request);
        if (in_array('ADMIN', $user_cache['roles']) === false) {
            return new Response('Not Authorized', 304);
        }

        // Get the Query Parameters from the URL
        // We will trust that the input is safe (sanitized)
        $s_name     = $request->query->get('s_name');
        $s_username = $request->query->get('s_username');
        $s_password = $request->query->get('s_password');
        $s_rolesA   = $request->query->get('s_roles');

        // All necesary?
        if (empty($s_name) || empty($s_username) || empty($s_password)) {
            return new Response("Fields mising!", 400);
        }
        if (empty($s_rolesA)) {
            $s_roles = ['PAGE_1', 'PAGE_2'];
        } else {
            $s_roles = explode(",", $s_rolesA);
        }

        // Very Simple encript of pass
        $pass_enc = md5("<<399s8f>>".$s_password);

        // Search for username, in use?
        $bd_user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['username' => $s_username]);
        if (!empty($bd_user) && $bd_user->Getusername() == $s_username) {
            return new Response("The Username passed is in use", 400);
        }
        // Create a new empty object
        $usuario = new User();

        // Use methods from the Quote entity to set the values
        $usuario->setName($s_name);
        $usuario->setUsername($s_username);
        $usuario->setPassword($pass_enc);
        $usuario->setRoles($s_roles);

        // Get the Doctrine service and manager
        $em = $this->getDoctrine()->getManager();

        // Add our quote to Doctrine so that it can be saved
        $em->persist($usuario);

        // Save our quote
        $em->flush();
        $salida = ["estado" => "OK"];
        return new Response(json_encode($salida), 201);
    }

    /**
     * @Route("/api/v1/user")
     * @Method("GET")
     * @ApiDoc(
     *  description="Retrieve a USER with username and password. Used in login.",
     *  headers={
     *      {"name"="AUTH_USER", "description"="Basic Auth User"},
     *      {"name"="AUTH_PW", "description"="Basic Auth Password"},
     *  },
     *  statusCodes={
     *      200="User login sucessfull. And return JSON with USER object.",
     *      403="Forbidden"
     *  },
     * )
     */
    public function getAction(Request $request)
    {
        $user_cache = $this->is_loged_in($request);
        if ($user_cache == false) {
            return new Response('Forbidden', 403);
        }
        $salida = ["estado" => "OK", "datos_usuario" => $user_cache];
        return new Response(json_encode($salida), 200);
    }

    /**
     * @Route("/api/v1/user/{id}")
     * @Method("PUT")
     * @ApiDoc(
     *  description="PUT. Edit a user by the ID passed to the URL.",
     *  parameters={
     *      {"name"="ID", "dataType"="integer", "required"=true, "description"="ID in URL!"},
     *      {"name"="s_name", "dataType"="string", "required"=true, "description"="Name of the USER"},
     *      {"name"="s_username", "dataType"="string", "required"=true, "description"="UserName used by USER in login"},
     *      {"name"="s_password", "dataType"="string", "required"=true, "description"="Password of the USER"},
     *      {"name"="s_roles", "dataType"="simple_array", "required"=false, "description"="Roles [ADMIN,PAGE_1,PAGE_2]"},
     *  },
     *  headers={
     *      {"name"="AUTH_USER", "description"="Basic Auth User"},
     *      {"name"="AUTH_PW ", "description"="Basic Auth Password"},
     *  },
     *  statusCodes={
     *      200="Updated USER sucessfull",
     *      403="Forbidden",
     *      404="User not found",
     *      400="The Username passed is in use",
     *  },
     * )
     */
    public function putAction($id, Request $request)
    {
        $user_cache = $this->is_loged_in();
        if (in_array('ADMIN', $user_cache['roles']) === false) {
            return new Response('Forbidden', 403);
        }
        $bd_user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['id' => $id]);
        if (empty($bd_user)) {
            return new Response("User not found", 404);
        }
        // Get the Query Parameters from the URL
        // We will trust that the input is safe (sanitized)
        $s_name     = $request->query->get('s_name');
        $s_username = $request->query->get('s_username');
        $s_password = $request->query->get('s_password');
        $s_rolesA   = $request->query->get('s_roles');

        if (!empty($s_name) && $s_name != $bd_user->getName()) {
            $bd_user->setName($s_name);
        }
        if (!empty($s_username) && $s_username != $s_username->getName()) {
            $bd_user2 = $this->getDoctrine()
                ->getRepository('AppBundle:User')
                ->findOneBy(['username' => $s_username]);
            if ($bd_user2 != null && $bd_user2->Getusername() == $s_username) {
                return new Response("The Username passed is in use", 400);
            }
            $bd_user->setName($s_name);
        }
        if (!empty($s_password)) {
            // Very Simple encript of pass
            $pass_enc = md5("<<399s8f>>".$s_password);
            $bd_user->setName($pass_enc);
        }
        if (!empty($s_rolesA)) {
            $s_roles = explode(",", $s_rolesA);
            if (is_array($s_roles)) {
                $bd_user->setRoles($s_roles);
            }
        }

        // Get the Doctrine service and manager
        $em = $this->getDoctrine()->getManager();

        // Add our quote to Doctrine so that it can be saved
        $em->persist($bd_user);

        // Save our quote
        $em->flush();
        $salida = ["estado" => "OK"];
        return new Response(json_encode($salida), 200);
    }

    /**
     * @Route("/api/v1/user/{id}")
     * @Method("DELETE")
     * @ApiDoc(
     *  description="DELETE. Remove a userby the ID passed to the URL.!.",
     *  parameters={
     *      {"name"="ID", "dataType"="integer", "required"=true, "description"="ID in URL!"},
     *  },
     *  headers={
     *      {"name"="AUTH_USER", "description"="Basic Auth User"},
     *      {"name"="AUTH_PW", "description"="Basic Auth Password"},
     *  },
     *  statusCodes={
     *      200="Updated USER sucessfull",
     *      403="Forbidden",
     *      404="User not found",
     *      400="The Username passed is in use",
     *  },
     * )
     */
    public function deleteAction($id)
    {
        $user_cache = $this->is_loged_in();
        if (in_array('ADMIN', $user_cache['roles']) === false) {
            return new Response('Forbidden', 403);
        }
        $em      = $this->getDoctrine()->getManager();
        $bd_user = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);
        if (empty($bd_user)) {
            return new Response("User not found", 404);
        } else {
            $em->remove($bd_user);
            $em->flush();
        }
        $salida = ["estado" => "OK"];
        return new Response(json_encode($salida), 200);
    }
}