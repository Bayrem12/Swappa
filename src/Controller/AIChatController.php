<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\AIChatService;

class AIChatController extends AbstractController
{
    #[Route('/assistant', name: 'assistant')]
    public function assistant(Request $request, AIChatService $aiChatService): Response
    {
        $question = $request->query->get('question', '');
        $answer = '';

        if ($question) {
            $answer = $aiChatService->askQuestion($question);
        }


        return $this->render('admin/assistant/chat.html.twig', [
            'question' => $question,
            'answer' => $answer,
        ]);
    }
}
