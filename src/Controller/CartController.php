<?php

namespace App\Controller;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Order;
use App\Entity\OrderItem;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'total' => $total
        ]);
    }
    // src/Controller/CartController.php
#[Route('/cart/remove/{id}', name: 'app_remove_from_cart')]
public function removeFromCart(int $id, SessionInterface $session): Response
{
    $cart = $session->get('cart', []);
    
    if (isset($cart[$id])) {
        unset($cart[$id]);
    }
    
    $session->set('cart', $cart);
    
    return $this->redirectToRoute('app_cart');
}

#[Route('/cart/buy', name: 'app_cart_buy')]
public function buy(SessionInterface $session, EntityManagerInterface $entityManager): Response
{
    $cart = $session->get('cart', []);
    
    if (empty($cart)) {
        $this->addFlash('error', 'Your cart is empty!');
        return $this->redirectToRoute('app_cart');
    }

    // Get the current user
    $user = $this->getUser();
    
    // Create and persist the order
    $order = new Order();
    $order->setUser($user);
    $order->setTotal($this->calculateTotal($cart));
    $order->setCreatedAt(new \DateTime());
    
    foreach ($cart as $item) {
        $product = $entityManager->getRepository(Produit::class)->find($item['id']);

    // Check if there is enough stock before proceeding
    if ($product->getQuantity() < $item['quantity']) {
        $this->addFlash('error', "Not enough stock for {$product->getName()}.");
        return $this->redirectToRoute('app_cart');
    }

    // Reduce the product quantity
    $product->setQuantity($product->getQuantity() - $item['quantity']);

    $orderItem = new OrderItem();
    $orderItem->setProduct($product);
    $orderItem->setQuantity($item['quantity']);
    $orderItem->setPrice($item['price']);
    $orderItem->setOrder($order);

    $entityManager->persist($orderItem);
    $entityManager->persist($product); // Ensure stock update is saved

    }
    
    $entityManager->persist($order);
    $entityManager->flush();
    
    // Clear the cart
    $session->remove('cart');
    
    $this->addFlash('success', 'Purchase completed successfully!');
    return $this->redirectToRoute('app_pages');
}

private function calculateTotal(array $cart): float
{
    return array_reduce($cart, function($total, $item) {
        return $total + ($item['price'] * $item['quantity']);
    }, 0);
}

    #[Route('/cart/add/{id}', name: 'app_add_to_cart')]
public function addToCart(
    int $id,
    SessionInterface $session,
    EntityManagerInterface $entityManager
): Response {
    $product = $entityManager->getRepository(Produit::class)->find($id);

    if (!$product) {
        throw $this->createNotFoundException('Product not found');
    }

    if ($product->getQuantity() <= 0) {
        $this->addFlash('error', 'This product is out of stock!');
        return $this->redirectToRoute('app_pages');
    }

    $cart = $session->get('cart', []);

    if (isset($cart[$id])) {
        if ($cart[$id]['quantity'] < $product->getQuantity()) {
            $cart[$id]['quantity']++;
        } else {
            $this->addFlash('error', 'Not enough stock available.');
            return $this->redirectToRoute('app_pages');
        }
    } else {
        $cart[$id] = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'quantity' => 1
        ];
    }

    $session->set('cart', $cart);
    return $this->redirectToRoute('app_pages');
}

}