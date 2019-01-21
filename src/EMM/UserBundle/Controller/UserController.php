<?php

namespace EMM\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use EMM\UserBundle\Entity\User;
use EMM\UserBundle\Form\UserType;

class UserController extends Controller
{
    public function indexAction()
    {
        $em= $this->getDoctrine()->getManager();
        $users= $em->getRepository('EMMUserBundle:User')->findAll();

       /* $res='Lista de usarios: <br />';
        
        foreach($users as $user)
        {
            $res .= 'Usuario:'.$user->getUsername(). ' -Email:'. $user->getEmail(). '<br/>';
        }

        return new Response($res);*/

        return $this->render('EMMUserBundle:User:index.html.twig', array('users'=>$users));
    }

    public function viewAction($id)
    {
        $repository= $this->getDoctrine()->getRepository('EMMUserBundle:User');
        $user= $repository->find($id);

        return new Response('Usuario '. $user->getusername(). ' con Email '. $user->getEmail());
    }

    public function addAction()
    {
        $user= new User();
        $form= $this->createCreateForm($user);

        return $this->render('EMMUserBundle:User:add.html.twig', array('form'=>$form->createView()));
        
    }

    public function createCreateForm(User $entity)
    {
        $form= $this->createForm(new userType(),$entity,array(
            'action'=>$this->generateURL('emm_user_create'),
            'method'=>'POST'
        ));

        return $form;
    }
}


