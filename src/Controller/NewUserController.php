<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NewUserController extends AbstractController
{
    /**
     * @Route("/usuario/mostra",methods="GET")
     */
    public function mostraAction(User $user)
    {
        return $this->render("new_user/index.html.twig", ["user" => new User()]);

    }

    /**
     * @Route("/usuario/novo",methods="GET")
     */
    public function formulario()
    {
        $form = $this->createFormBuilder(new User())
            ->add('email')
            ->add('senha')
            ->setAction('/usuario/novo')
            ->getForm();

        return $this->render("new_user/index.html.twig",["form" => $form->createView()]);
    }

    /**
     * @Route("/usuario/novo",methods="POST",name="user_new")
     */
    public function cria(Request $request)
    {
        $user = new User();

        $form = $this->createFormBuilder($user)
            ->add('nome')
            ->add('email')
            ->add('senha')
            ->add('telefone')
            ->add('endereco')
            ->getForm();

        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirect("/usuario/lista");
    }

    /**
     * @Route("/usuario/lista",methods="GET",name="admin_user_lista")
     */
    public function lista()
    {
        $repository = $this->getDoctrine()->getManager()->getRepository(User::class);

        return $this->render("new_user/index.html.twig".["user" => $repository->findAll()]);
    }

    /**
     * @Route("/usuario/edita/{id}",methods="GET")
     */
    public function mostra(User $user)
    {
        $form = $this->createFormBuilder($user)
            ->add('nome')
            ->add('email')
            ->add('senha')
            ->add('telefone')
            ->add('endereco')
            ->setAction("/usuario/edita".$user->getId())
            ->getForm();

        return $this->render("new_user/index.html.twig",["form" => $form->createView()]);
    }

    /**
     * @Route("/usuario/edita/{id}",methods="GET")
     */
    public function edita(User $user, Request $request)
    {
        $form = $this->createFormBuilder($user)
            ->add('nome')
            ->add('email')
            ->add('senha')
            ->add('telefone')
            ->add('endereco')
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $user = $form->getData();

            $em = $this->getDoctrine()->getManager();

            $em->merge($user);
            $em->flush();

            return $this->redirect("/usuario/edita".$user->getId());
        }

        return $this->render("new_user/index.html.twig",["user" => $user]);

    }

    /**
     * @Route("/usuario/remove/{id}",methods="GET")
     */
    public function delete(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return $this->redirect("/usuario/lista");
    }
}
