<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Response;

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
     * @Route("/usuario/novo",methods="POST",name="user_new")
     */
    public function formulario()
    {
        $form = $this->createFormBuilder(new User())
            ->add('nome')
            ->add('email')
            ->add('password')
            ->add('telefone')
            ->add('endereco')
            ->add('perfil', ChoiceType::class, [
                "multiple" => true,
                "choices" => [
                    "Administrador" => "ROLE_ADMIN", "Usuario" => "ROLE_USER"
                ]
            ])
            ->setAction('/usuario/novo')
            ->getForm();

        return $this->render("new_user/newUser.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * @Route("/usuario/novo",methods="POST",name="user_new")
     */
    public function cria(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $em = $this->getDoctrine()->getManager();

        $nome = $request->request->get('nome');
        $email = $request->request->get('email');
        $senha = $request->request->get('senha');
        $telefone = $request->request->get('telefone');
        $endereco = $request->request->get('endereco');
        $perfil = $request->request->get('perfil');
        $roleAdmin = $request->request->get('Administrador');
        $roleUser = $request->request->get('Usuario');

        if (is_null($email) || is_null($senha)){
            return $this->setResponse('Credenciais Inválidas', 400);
        }

        try {
            $user = new User($email);
            $user->setNome($nome);
            $user->setEmail($email);
            $user->setPassword($encoder->encodePassword($user, $senha));
            $user->setTelefone($telefone);
            $user->setEndereco($endereco);

            if(empty($perfil)){
               $user->setPerfil('ROLE_USER');
            }else{
                if ($roleAdmin == true){
                    $user->setPerfil('ROLE_ADMIN');
                }
                if ($roleUser == true){
                    $user->setPerfil('ROLE_USER');
                }
            }
            $em->persist($user);
            $em->flush();
        }catch (UniqueConstraintViolationException $exception){
            return $this->setResponse("Usuario ja cadastrado. Tente outro", 409);
        }catch (\Throwable $exception){
            return $this->setResponse($exception->getMessage() . $exception->getTraceAsString(), 400);
        }
        return $this->setResponse("Usuario {$nome} criado com sucesso.", 201, false);
    }

    /**
     * @Route("/usuario/lista",methods="GET",name="admin_user_lista")
     */
    public function lista()
    {
        $repository = $this->getDoctrine()->getManager()->getRepository(User::class);

        return $this->render("new_user/index.html.twig" . ['user' => $repository->findAll()]);
    }

    /**
     * @Route("/usuario/edita/{id}",methods="GET")
     */
    public function mostra(User $user)
    {
        $form = $this->createFormBuilder($user)
            ->add('nome')
            ->add('email')
            ->add('password')
            ->add('telefone')
            ->add('endereco')
            ->setAction("/usuario/edita" . $user->getId())
            ->getForm();

        return $this->render("new_user/index.html.twig", ["form" => $form->createView()]);
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

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $em = $this->getDoctrine()->getManager();

            $em->merge($user);
            $em->flush();

            return $this->redirect("/usuario/edita" . $user->getId());
        }

        return $this->render("new_user/index.html.twig", ["user" => $user]);

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

    private function setResponse(string $menssagem, int $httpCode, bool $erro = true)
    {
        $response = [
            $erro ? 'erro' : 'sucess' =>$menssagem,
        ];

        return new Response($menssagem, $httpCode);
    }
}
