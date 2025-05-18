<?php

// src/Controller/AdminController.php
namespace App\Controller;

use App\Entity\Produit;
use App\Entity\User;
use App\Entity\Order;
use App\Form\ProduitType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(EntityManagerInterface $em): Response
    {
        $repo = $em->getRepository(Order::class);
        $productRepo = $em->getRepository(Produit::class);
        $userRepo = $em->getRepository(User::class);

        return $this->render('admin/dashboard.html.twig', [
            'total_products' => count($em->getRepository(Produit::class)->findAll()),
            'total_users' => count($em->getRepository(User::class)->findAll()),
            'recent_orders' => $repo->countLastWeekOrders(),
            'total_revenue' => $repo->getCompletedRevenueThisMonth(),
            'pending_orders' => $repo->countByStatus('pending'),
            'completed_orders' => $repo->countByStatus('completed'),
            'cancelled_orders' => $repo->countByStatus('cancelled'),
            'recent_products' => $productRepo->findBy([], ['created_at' => 'DESC'], 5),
            'recent_users' => $userRepo->findBy([], ['id' => 'DESC'], 5),
        ]);

    }

    #[Route('/products', name: 'admin_product_index')]
    public function products(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $em->getRepository(Produit::class)->createQueryBuilder('p');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/product/index.html.twig', [
            'products' => $pagination
        ]);
    }

    #[Route('/createProduct', name: 'admin_product_new')]
    public function newProduct(Request $request, EntityManagerInterface $em): Response
    {
        $product = new Produit();
        $form = $this->createForm(ProduitType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Product created successfully');
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/CreatProduct/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/users', name: 'admin_user_index')]
    public function users(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $em->getRepository(User::class)->createQueryBuilder('u');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/user/index.html.twig', [
            'users' => $pagination
        ]);
    }

    #[Route('/products/edit/{id}', name: 'admin_product_edit')]
    public function editProduct(Request $request, Produit $product, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProduitType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload if using VichUploader
            $em->flush();

            $this->addFlash('success', 'Product updated successfully');
            return $this->redirectToRoute('admin_product_index');
        }

        return $this->render('admin/product/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product
        ]);
    }

    // Delete route
    #[Route('/products/delete/{id}', name: 'admin_product_delete', methods: ['POST'])]
    public function deleteProduct(Request $request, Produit $product, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $em->remove($product);
            $em->flush();
        }
        
        return $this->redirectToRoute('admin_product_index');
    }
     

    // Delete user route
    #[Route('/users/delete/{id}', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(Request $request, User $user, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
        }
        
        return $this->redirectToRoute('admin_user_index');
    }
    // src/Controller/AdminController.php
    #[Route('/orders', name: 'admin_order_index')]
    public function orders(EntityManagerInterface $em, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $em->getRepository(Order::class)->createQueryBuilder('o')
            ->innerJoin('o.user', 'u')
            ->addSelect('u')
            ->orderBy('o.created_at', 'DESC');

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/order/index.html.twig', [
            'orders' => $pagination
        ]);
    }

    #[Route('/orders/{id}', name: 'admin_order_show')]
    public function showOrder(Order $order): Response
    {
        return $this->render('admin/order/show.html.twig', [
            'order' => $order
        ]);
    }
    #[Route('/admin/order/{id}/complete', name: 'admin_order_complete', methods: ['POST'])]
public function completeOrder(Order $order, EntityManagerInterface $em): Response
{
    $order->setStatus('completed');
    $em->flush();

    $this->addFlash('success', 'Order marked as completed.');
    return $this->redirectToRoute('admin_order_index');
}

#[Route('/admin/order/{id}/cancel', name: 'admin_order_cancel', methods: ['POST'])]
public function cancelOrder(Order $order, EntityManagerInterface $em): Response
{
    // restore product quantities
    foreach ($order->getOrderItems() as $item) {
        $product = $item->getProduct();
        $product->setQuantity($product->getQuantity() + $item->getQuantity());
        $em->persist($product);
    }

    $order->setStatus('cancelled');
    $em->flush();

    $this->addFlash('warning', 'Order has been cancelled and stock restored.');
    return $this->redirectToRoute('admin_order_index');
}

}
