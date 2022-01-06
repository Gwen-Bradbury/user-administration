<?php

namespace App\Controller;

use App\Repository\User as Repository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/all-users", name="all-users")
     *
     */
    public function allUsers(Repository $repository): Response
    {
        return $this->render('all-users.html.twig', ['user' => $repository->getAllUsers()]);
    }


    /**
     * @Route("/add-user-form", name="add-user-form")
     *
     */
    public function viewAddUserForm(): Response
    {
        return $this->render(
            'add-user.html.twig', ['user' => ['username' => '', 'email' => '', 'password' => '']]
        );
    }


    /**
     * @Route("/add-user", methods={"POST", "OPTIONS"}, name="add-user")
     *
     */
    public function addUser(Repository $repository, Request $request): Response
    {
        $plaintextPassword = $request->request->get('password');

        $factory = new PasswordHasherFactory(['common' => ['algorithm' => 'sha256']]);
        $hashedPassword = $factory->getPasswordHasher('common')->hash($plaintextPassword);

        $repository->addUser(
            $request->request->get('username'),
            $request->request->get('email'),
            $hashedPassword
        );

        return $this->redirect('/all-users');
    }


    /**
     * @Route("/edit-user/{id}", name="edit-user")
     *
     */
    public function editUser(Repository $repository, int $id): Response
    {
        return $this->render('edit-user.html.twig', ['user' => $repository->getUser($id)]);
    }


    /**
     * @Route("/update-user/{id}", methods={"POST", "OPTIONS"}, name="update-user")
     *
     */
    public function updateUser(Repository $repository, Request $request, int $id): Response
    {
        $username = $request->request->get('username');
        $email = $request->request->get('email');
        $plaintextPassword = $request->request->get('password');

        $factory = new PasswordHasherFactory(['common' => ['algorithm' => 'sha256']]);
        $hashedPassword = $factory->getPasswordHasher('common')->hash($plaintextPassword);

        $repository->updateUser(['username' => $username, 'email' => $email, 'password' => $hashedPassword], $id);

        return $this->redirect('/all-users');
    }


    /**
     * @Route("/delete-user/{id}")
     *
     */
    public function deleteUser(Repository $repository, int $id): Response
    {
        $repository->deleteUser($id);
        return $this->redirect('/all-users');
    }
}