<?php

namespace App\Controller;

use App\Entity\OrderShop;
use App\Entity\User;
use App\Form\UserRolesType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
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
     * Displays the admin dashboard with the list of the latest registered users
     * and the most recent orders.
     *
     * @return Response
     */
    public function dashboard(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findBy([], ['id' => 'DESC'], 3);
        $orders = $em->getRepository(OrderShop::class)->findBy([], ['createdAt' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'users' => $users,
            'orders' => $orders,
        ]);
    }

    /**
     * Displays a paginated list of users in the admin panel.
     *
     * This method retrieves User entities from the database, ordered by their
     * identifier in descending order (most recent users first), and paginates
     * the results using KnpPaginator.
     *
     * It is intended for administrative users to browse and manage registered
     * users of the e-commerce platform.
     *
     * Each user entry may display basic account information such as email,
     * roles, and optional customer profile details. Additional actions
     * (e.g. editing user roles) can be accessed from the list.
     *
     * The paginated user list is passed to the Twig template
     * 'admin/list_users.html.twig' for rendering.
     *
     * @param EntityManagerInterface $em
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function listUsersAction(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request): Response
    {
        $users = $em->getRepository(User::class)->findBy([], ['id' => 'DESC']);

        $pagination = $paginator->paginate(
            $users,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/list_users.html.twig', [
            'pagination' => $pagination
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
            return $this->redirectToRoute('admin_users_list');
        }

        return $this->render('admin/edit_user_roles.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Displays a list of all orders in the admin panel.
     *
     * This method retrieves all OrderShop entities from the database, ordered
     * by their creation date in descending order (most recent first). It is
     * intended for administrative users to view and manage all orders placed
     * in the e-commerce system.
     *
     * Each order can then be inspected individually (via a separate "view order"
     * page), or have its status updated (via a separate "edit status" page).
     *
     * The retrieved orders are passed to the Twig template 'admin/list_orders.html.twig'
     * for rendering. The template is responsible for displaying the orders in a
     * list format, optionally showing badges for order status, creation date,
     * and associated user information.
     *
     * @param EntityManagerInterface $em
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function listOrdersAction(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request): Response
    {
        $orders = $em->getRepository(OrderShop::class)->findBy([], ['createdAt' => 'DESC']);

        $pagination = $paginator->paginate(
            $orders,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/list_orders.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * Displays the details of a specific order in the admin panel.
     *
     * This method retrieves a single OrderShop entity, which is automatically
     * injected by Symfony using ParamConverter based on the {id} route parameter.
     * It is intended for administrative users to inspect all relevant information
     * about a particular order, including:
     *   - Associated user
     *   - Order status
     *   - Creation date
     *   - List of ordered items (product, quantity, price, subtotal)
     *
     * The retrieved order is passed to the Twig template 'admin/show_order.html.twig'
     * for rendering. The template can also provide options for further actions,
     * such as editing the order status or returning to the orders list.
     *
     * @param OrderShop $order
     * @return Response
     */
    public function showOrderAction(OrderShop $order): Response
    {
        return $this->render('admin/show_order.html.twig', [
            'order' => $order
        ]);
    }

    /**
     * Allows an admin to edit the status of a specific order.
     *
     * This method provides a form for changing the status of an OrderShop entity.
     * It is intended for administrative users to manage the state of orders,
     * such as marking them as 'pending', 'paid', or 'cancelled'.
     *
     * Workflow:
     * 1. The OrderShop entity is automatically injected by Symfony using ParamConverter
     *    based on the {id} route parameter.
     * 2. A simple Symfony form is created with a single 'status' choice field.
     * 3. The form is handled and validated:
     *    - If the form is submitted and valid, the order's status is updated in the database.
     *    - A success flash message is displayed.
     *    - The user is redirected to the order details page.
     * 4. If the form is not submitted or invalid, the form is rendered along with
     *    the current order information.
     *
     * The form is rendered in the Twig template 'admin/edit_order.html.twig', which
     * can also display order details for context.
     *
     * @param OrderShop $order
     * @param Request $request
     * @return Response
     */
    public function editOrderAction(OrderShop $order, Request $request): Response
    {
        $form = $this->createFormBuilder($order)
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Pending' => 'pending',
                    'Paid' => 'paid',
                    'Cancelled' => 'cancelled',
                ],
                'label' => 'Order Status',
                'attr' => ['class' => 'form-select']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Order status updated successfully!');
            return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
        }

        return $this->render('admin/edit_order.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

}