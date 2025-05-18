<?php
// src/Controller/ProductAIController.php

namespace App\Controller;

use App\Entity\Produit;
use App\Service\AIChatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductAIController extends AbstractController
{
    #[Route('/product/{id}/ai', name: 'product_ai_info')]
    public function analyzeProduct(Produit $product, AIChatService $aiChatService): Response
    {
        $prompt = sprintf(
            "You are a helpful and knowledgeable tech assistant. Your task is to generate an informative, accurate, and engaging product overview suitable for an e-commerce product page.\n\n"
            . "Use your general knowledge about the product type and expand beyond the given description. Include details about:\n"
            . "- Processor (CPU) performance\n"
            . "- RAM and storage\n"
            . "- Camera quality and features\n"
            . "- Display and battery\n"
            . "- Build quality, design, and software experience\n"
            . "- Key benefits to the user\n\n"
            . "Product Name: %s\n"
            . "Description: %s\n"
            . "Price: %s TND\n\n"
            . "Give your response in clear paragraphs with bullet points if necessary. Keep it professional but friendly.",
            $product->getName(),
            $product->getDescription(),
            $product->getPrice()
        );



        $answer = $aiChatService->askQuestion($prompt);

        return $this->render('product/ai_info.html.twig', [
            'product' => $product,
            'answer' => $answer
        ]);
    }
}
