<?php

namespace EMM\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;
use EMM\UserBundle\Entity\User;
use EMM\UserBundle\Form\UserType;

class UserController extends Controller
{
    public function indexAction(Request $request )
    {
        $em= $this->getDoctrine()->getManager();
       // $users= $em->getRepository('EMMUserBundle:User')->findAll();

       /* $res='Lista de usarios: <br />';
        
        foreach($users as $user)
        {
            $res .= 'Usuario:'.$user->getUsername(). ' -Email:'. $user->getEmail(). '<br/>';
        }

        return new Response($res);*/

        $dql= "SELECT u FROM EMMUserBundle:User u ORDER BY  u.id DESC";
        $users= $em->createQuery($dql);

        $paginator= $this->get('knp_paginator');
        $pagination= $paginator->paginate($users,$request->query->getInt('page',1),3        
        ); 

        $deleteFormAjax= $this->createCustomForm(':USER_ID', 'DELETE', 'emm_user_delete');
        return $this->render('EMMUserBundle:User:index.html.twig', array('pagination'=>$pagination,
                    'delete_form_ajax'=>$deleteFormAjax->createView()));
    }

    public function editAction($id)
    {       //llamo a los metodos de conexion a la base de datos 
            $em= $this->getDoctrine()->getManager();
            //obtengo el registro con la cual deseo realizar la operacion utilizando  el metodo getRepository('asigno bundle y entidad')
            //y con el metodo find() obtengo el registro de la tabla asociado al id
            $user= $em->getRepository('EMMUserBundle:User')->find($id);

            if(!$user){

                $menssageExeption= $this->get('translator')->trans('User not found');
                throw $this->createNotFoundException($menssageExeption);

            }
            $form= $this->createEditForm($user);

            return $this->render('EMMUserBundle:User:edit.html.twig', array('user'=>$user,'form'=>$form->createView()));
    }

    private function createEditForm(User $entity)
    {   // se cra el formulario utlizando el metodo createForm('se crea un objeto de la clase que contiene el formulario')
        //y se le pasa el registro asociado , lal ruta de redireccinamiento y en este caso el id del registro
        $form= $this->createForm(new UserType(),$entity,array('action'=>$this->generateUrl('emm_user_update',
        array('id'=>$entity->getId())),'method'=>'PUT'));

        return $form;
    }

    public function updateAction($id, Request $request)
    {
        
        $em= $this->getDoctrine()->getManager();
        $user= $em->getRepository('EMMUserBundle:User')->find($id);

        if(!$user){
            $messageExeption= $this->get('translator')->trans('User not found');
            throw $this->createNotFoundException($menssageExeption);
        }

        $form= $this->createEditForm($user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            //para poder modificar el password y encriptarlo debemos 
            $password= $form->get('password')->getData();
            //print_r($password);
            //exit;
            //ferificamos si el campo contiene datos 
            if(!empty($password)){
                //codifiacamos el nuevo password
                $encoder= $this->container->get('security.password_encoder');
                $encoded= $encoder->encodePassword($user,$password);
                $user->setpassword($encoded);
            }else{
                $recoverPass= $this->recoverPass($id);
                //lo que devuelve el metodo es un arreglo por lo tanto para setearlo hay pasar la 
                //la posicion del arreglo y su clave
                $user->setPassword($recoverPass[0]['password']);
               //print_r($recoverPass);
               //exit;
            }

            if($form->get('role')->getData()=='ROLE_ADMIN'){
                $user->setIsActive(1);
            }

            $em->flush();

            $successmessage= $this->get('translator')->trans('The user has been modified.');
            $this->addFlash('mensaje', $successmessage);

            return $this->redirectToRoute('emm_user_edit',array('id'=>$user->getId()));

        }

        return $this->render('EMMUserBundle:User:edit.html.twig',
                     array('user'=>$user,'form'=>$form->createView()));
    }

    private function recoverPass($id){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT u.password
             FROM EMMUserBundle:User u
             WHERE u.id = :id' 
             )->setParameter('id', $id);
                //con el metodo getResult() obtengo el resultado de la consulta
             $currentPass= $query->getResult();
             return $currentPass;
    }
    public function viewAction($id)
    {
        $repository= $this->getDoctrine()->getRepository('EMMUserBundle:User');
        $user= $repository->find($id);

        if(!$user){
            $messageExeption= $this->get('translator')->trans('User not found');
            throw $this->createNotFoundException($menssageExeption);
        }

        $deleteForm= $this->createDeleteForm($user);

        return $this->render('EMMUserBundle:User:view.html.twig', array('user'=>$user, 
                             'delete_form'=>$deleteForm->createView()));
    }

    private function createDeleteForm($user)
    {
        return $this->createFormBuilder()
                    ->setAction($this->generateUrl('emm_user_delete', array('id'=>$user->getId())))
                    ->setMethod('DELETE')
                    ->getForm();
    }

    public function deleteAction(Request $request, $id)
    {
        $em= $this->getDoctrine()->getManager();

        $user= $em->getRepository('EMMUserBundle:User')->find($id);

        if (!$user){
            $exceptionMessage= $this->get('translator')->trans('User not found');
            throw $this->createNotFoundException($exceptionMessage);
        }

        $form= $this->createDeleteForm($user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $em->remove($user);
            $em->flush();

            $successMessage= $this->get('translator')->trans('The user has been delete');
            $this->addflash('mensaje',$successMessage);
            return $this->redirectToRoute('emm_user_index');

        }
    }

    private function createCustomForm($id, $method, $route)
    {
        return $this->createFormBuilder()
                    ->setAction($this->generateURL($route, array('id'=>$id)))
                    ->setMethod($method)
                    ->getForm();
    }

    public function addAction()
    {
        $user= new User();
        $form= $this->createCreateForm($user);

        return $this->render('EMMUserBundle:User:add.html.twig', array('form'=>$form->createView()));
        
    }

    private function createCreateForm(User $entity)
    {
        $form= $this->createForm(new userType(),$entity,array(
            'action'=>$this->generateURL('emm_user_create'),
            'method'=>'POST'
        ));

        return $form;
    }

    public function createAction(Request $request)
    {
        $user= new User();
        $form=$this->createCreateForm($user);
        $form->handleRequest($request);
        
        if($form->isValid())
        {
            $password= $form->get('password')->getData();
            //para verificar que el campo password no se encuentre vacio ya que la velidacion 
            //en la entidad fue eliminada
            $passwordConstraint= new Assert\NotBlank();
            $successmessage= $this->get('translator')->trans('please define a password.');
            $passwordConstraint->message=$successmessage;
            $errorList= $this->get('validator')->validate($password,$passwordConstraint);

            //evaluamos que no exista ningun error para realizar la persistencia de los datos 
            if(count($errorList)==0){

                $encoder= $this->container->get('security.password_encoder');
                $encoded= $encoder->encodePassword($user,$password);

                $user->setPassword($encoded);

                $em= $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $successMessage= $this->get('translator')->trans('The user has been created.');
                $this->addFlash('mensaje', $successMessage);
                
                return $this->redirectToRoute('emm_user_index');
            }else{
                $errorMesagge= new formError($errorList[0]->getMessage());
                $form->get('password')->addError($errorMesagge);
            }
            
        }

        return $this->render('EMMUserBundle:User:add.html.twig', array('form'=>$form->createView()));

    }
}


