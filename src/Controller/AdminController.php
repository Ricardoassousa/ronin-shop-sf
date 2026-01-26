<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRolesType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller used to manage administrative tasks.
 *
 * This controller handles admin-related actions such as:
 *  - Displaying the admin dashboard
 *  - Editing user roles
 *  - Managing other administrative functionalities
 */
class AdminController extends AbstractController
{
    /**
     * Displays the admin dashboard with the list of all users.
     *
     * @return Response
     */
    public function dashboard(): Response
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * Allows editing the roles of a specific user.
     *
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function editUserRoles(User $user, Request $request): Response
    {
        $form = $this->createForm(UserRolesType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'User roles updated successfully!');
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/edit_user_roles.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

}