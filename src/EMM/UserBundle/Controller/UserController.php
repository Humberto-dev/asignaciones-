<?php

namespace EMM\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function indexAction()
    {
        $em= $this->getDoctrine()->getManager();
        $users= $em->getRepository('EMMUserBundle:User')->findAll();

        $res='Lista de usarios: <br />';
        
        foreach($users as $user)
        {
            $res .= 'Usuario:'.$user->getUsername(). ' -Email:'. $user->getEmail(). '<br/>';
        }

        return new Response($res);
    }

    public function viewAction($id)
    {
        $repository= $this->getDoctrine()->getRepository('EMMUserBundle:User');
        $user= $repository->find($id);

        return new Response('Usuario '. $user->getusername(). ' con Email '. $user->getEmail());
    }
}


