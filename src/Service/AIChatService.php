<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Produit;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\OrderItem;

class AIChatService
{
    private HttpClientInterface $client;
    private string $apiToken;
    private EntityManagerInterface $em;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $em, string $apiToken)
    {
        $this->client = $client;
        $this->apiToken = $apiToken;
        $this->em = $em;
    }

    public function askQuestion(string $question): string
    {
        // Load data
        $products = $this->em->getRepository(Produit::class)->findAll();
        $orders = $this->em->getRepository(Order::class)->findAll();
        $users = $this->em->getRepository(User::class)->findAll();
        $orderCount = $this->em->getRepository(Order::class)->countLastWeekOrders();
        $revenue = $this->em->getRepository(Order::class)->getMonthlyRevenue();

        $topClient = $this->em->getRepository(Order::class)->getUserWithMostOrders();
    
        // Context
        $context = "Swappa platform summary:\n";
        // ... existing context
        $context .= "- Top client: " . ($topClient['email'] ?? 'N/A') 
                  . " (" . ($topClient['orderCount'] ?? 0) . " orders)\n";
        

        // Context
        $context = "Swappa platform summary:\n";
        $context .= "- Products: " . count($products) . "\n";
        foreach (array_slice($products, 0, 5) as $product) {
            $context .= "  • {$product->getName()} ({$product->getQuantity()} in stock, {$product->getPrice()} TND)\n";
        }
        $context .= "- Orders: " . count($orders) . "\n";
        $context .= "- Last 7 days orders: {$orderCount}\n";
        $context .= "- Monthly revenue: " . number_format($revenue, 2) . " TND\n";
        $context .= "- Users: " . count($users) . "\n";

        // Prompt
        $messages = [
            ['role' => 'system', 'content' => 'You are a helpful assistant for Swappa admin panel. Answer clearly using this data.'],
            ['role' => 'user', 'content' => $context . "\n\nQuestion:\n" . $question],
        ];

        // Send to OpenRouter (GPT-3.5 or Mixtral)
        $response = $this->client->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'openai/gpt-3.5-turbo', // You can change to mistralai/mixtral-8x7b
                'messages' => $messages,
                'temperature' => 0.6,
            ],
        ]);

        $data = $response->toArray();

        return $data['choices'][0]['message']['content'] ?? 'No response from AI.';
    }
}
