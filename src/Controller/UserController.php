<?php

namespace App\Controller;

use App\Repository\User as Repository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $repository->addUser(
            $request->request->get('username'),
            $request->request->get('email'),
            $request->request->get('password')
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
        $password = $request->request->get('password');

        $repository->updateUser(['username' => $username, 'email' => $email, 'password' => $password], $id);

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