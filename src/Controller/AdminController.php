<?php

namespace App\Controller;

use App\Entity\OrderShop;
use App\Entity\User;
use App\Form\UserRolesType;
use App\Logger\AnalyticsLogger;
use App\Logger\OrderLogger;
use App\Logger\SecurityLogger;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

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
     * @param AnalyticsLogger $analyticsLogger
     * @return Response
     */
    public function dashboardAction(EntityManagerInterface $em, AnalyticsLogger $analyticsLogger): Response
    {
        try {
            $users = $em->getRepository(User::class)->findBy([], ['id' => 'DESC'], 3);
            $orders = $em->getRepository(OrderShop::class)->findBy([], ['createdAt' => 'DESC'], 5);

            $analyticsLogger->log(
                'Admin dashboard accessed',
                [
                    'latest_users' => count($users),
                    'latest_orders' => count($orders),
                    'admin_id' => $this->getUser() ? $this->getUser()->getId() : null,
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            return $this->render('admin/dashboard.html.twig', [
                'users' => $users,
                'orders' => $orders
            ]);

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Error loading admin dashboard',
                [
                    'exception' => $e->getMessage()
                ],
                LogLevel::ERROR
            );

            throw $e;
        }
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
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param PaginatorInterface $paginator
     * @param AnalyticsLogger $analyticsLogger
     * @return Response
     */
    public function listUsersAction(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator, AnalyticsLogger $analyticsLogger): Response
    {
        try {
            $users = $em->getRepository(User::class)->findBy([], ['id' => 'DESC']);

            $pagination = $paginator->paginate(
                $users,
                $request->query->getInt('page', 1),
                10
            );

            $analyticsLogger->log(
                'Admin viewed user list',
                [
                    'page' => $request->query->getInt('page', 1),
                    'total_users' => count($users),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            return $this->render('admin/list_users.html.twig', [
                'pagination' => $pagination
            ]);

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Error listing users in admin',
                [
                    'exception' => $e->getMessage()
                ],
                LogLevel::ERROR
            );

            throw($e);
        }
    }

    /**
     * Allows editing the roles of a specific user.
     *
     * @param Request $request
     * @param User $user
     * @param AnalyticsLogger $analyticsLogger
     * @param SecurityLogger $securityLogger
     * @return Response
     */
    public function editUserRolesAction(Request $request, User $user, AnalyticsLogger $analyticsLogger, SecurityLogger $securityLogger): Response
    {
        $form = $this->createForm(UserRolesType::class, $user);
        $form->handleRequest($request);

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $user->setUpdatedAt(new Datetime());
                $this->getDoctrine()->getManager()->flush();

                $securityLogger->log(
                    'User roles updated by admin',
                    [
                        'target_user_id' => $user->getId(),
                        'new_roles' => $user->getRoles(),
                        'admin_id' => $this->getUser()->getId(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::WARNING
                );

                $analyticsLogger->log(
                    'Admin edited user roles',
                    [
                        'user_id' => $user->getId(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::NOTICE
                );

                $this->addFlash('success', 'User roles updated successfully!');
                return $this->redirectToRoute('admin_users_list');
            }

        } catch (Throwable $e) {
            $securityLogger->log(
                'Error editing user roles',
                [
                    'user_id' => $user->getId(),
                    'exception' => $e->getMessage()
                ],
                LogLevel::ERROR
            );

            throw($e);
        }

        return $this->render('admin/edit_user_roles.html.twig', [
            'form' => $form->createView(),
            'user' => $user
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
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param PaginatorInterface $paginator
     * @param AnalyticsLogger $analyticsLogger
     * @return Response
     */
    public function listOrdersAction(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator, AnalyticsLogger $analyticsLogger): Response
    {
        try {
            $orders = $em->getRepository(OrderShop::class)->findBy([], ['createdAt' => 'DESC']);

            $pagination = $paginator->paginate(
                $orders,
                $request->query->getInt('page', 1),
                10
            );

            $analyticsLogger->log(
                'Admin viewed orders list',
                [
                    'page' => $request->query->getInt('page', 1),
                    'total_orders' => count($orders),
                    'source' => [
                        'method' => __METHOD__,
                        'line' => __LINE__
                    ]
                ],
                LogLevel::INFO
            );

            return $this->render('admin/list_orders.html.twig', [
                'pagination' => $pagination
            ]);

        } catch (Throwable $e) {
            $analyticsLogger->log(
                'Error listing orders in admin',
                [
                    'exception' => $e->getMessage()
                ],
                LogLevel::ERROR
            );

            throw ($e);
        }
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
     * @param AnalyticsLogger $analyticsLogger
     * @return Response
     */
    public function showOrderAction(OrderShop $order, AnalyticsLogger $analyticsLogger): Response
    {
        $analyticsLogger->log(
            'Admin viewed order details',
            [
                'order_id' => $order->getId(),
                'status' => $order->getStatus(),
                'source' => [
                    'method' => __METHOD__,
                    'line' => __LINE__
                ]
            ],
            LogLevel::INFO
        );

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
     * @param Request $request
     * @param OrderShop $order
     * @param AnalyticsLogger $analyticsLogger
     * @param OrderLogger $orderLogger
     * @return Response
     */
    public function editOrderAction(Request $request, OrderShop $order, AnalyticsLogger $analyticsLogger, OrderLogger $orderLogger): Response
    {
        $oldStatus = $order->getStatus();

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

        try {
            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                $orderLogger->log(
                    'Order status changed by admin',
                    [
                        'order_id' => $order->getId(),
                        'old_status' => $oldStatus,
                        'new_status' => $order->getStatus(),
                        'admin_id' => $this->getUser()->getId(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::NOTICE
                );

                $analyticsLogger->log(
                    'Admin updated order status',
                    [
                        'order_id' => $order->getId(),
                        'source' => [
                            'method' => __METHOD__,
                            'line' => __LINE__
                        ]
                    ],
                    LogLevel::INFO
                );

                $this->addFlash('success', 'Order status updated successfully!');
                return $this->redirectToRoute('admin_order_show', ['id' => $order->getId()]);
            }

        } catch (Throwable $e) {
            $orderLogger->log(
                'Error updating order status in admin',
                [
                    'order_id' => $order->getId(),
                    'exception' => $e->getMessage()
                ],
                LogLevel::ERROR
            );
        }

        return $this->render('admin/edit_order.html.twig', [
            'order' => $order,
            'form' => $form->createView()
        ]);
    }

}