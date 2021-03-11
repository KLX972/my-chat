<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\ConversationRepository;
use App\Entity\Participant;
use App\Entity\Conversation;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/conversations", name="conversations.")
 */
class ConversationController extends AbstractController
{
    /**
     * @Var
     */
    private $userRepository;

    /**
     * @Var
     */
    private $entityManager;
    /**
     * @Var
     */
    private $conversationRepository;

    public function __construct(UserRepository $userRepository,
                                EntityManagerInterface $entityManager,
                                ConversationRepository $conversationRepository)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * @Route("/", name="newConversation", methods={"POST"})
     * @param request $request
     * @return Response
     * @throws \Exception
     */
    public function newConversation(Request $request): Response
    {
        $otherUser = $request->get('otherUser', 0);
        $otherUser = $this->userRepository->find($otherUser);

        if (is_null($otherUser)) {
            throw new \Exception("the user was not found!");
        }

        // cannot create conversation with myself
        if ($otherUser->getId() === $this->getUser()->getId()) {
            throw new \Exception("You can't create conversation with yourself!");
        }

        //check if conversation already exist
        $conversation = $this
                        ->conversationRepository
                        ->findConversationByParticipants($otherUser->getId(), $this->getUser()->getId());

        if (count($conversation)) {
            throw new \Exception("this conversation already exist");
        }

        $conversation = new Conversation();

        $participant = new Participant();
        $participant->setUser($this->getUser());
        $participant->setConversation($conversation);

        $otherParticipant = new Participant();
        $otherParticipant->setUser($otherUser);
        $otherParticipant->setConversation($conversation);

        $this->entityManager->getConnection()->beginTransaction();
        try {
            $this->entityManager->persist($conversation);
            $this->entityManager->persist($participant);
            $this->entityManager->persist($otherParticipant);

            $this->entityManager->flush();
            $this->entityManager->commit();

        } catch (\Exception $e) {
            $this->entityManager->rollBack();
            throw $e;

        }

        return $this->json(['id' => $conversation->getId()], Response::HTTP_CREATED, [], []);
    }

    /**
     * @Route("/", name="getConversations" , methods={"GET"})
     * @param request $request
     * @return Response
     * @throws \Exception
     */
    public function getConversations(Request $request): Response
    {
        $conversations = $this->conversationRepository->findConversationsByUser($this->getUser()->getId());
        
        return $this->json($conversations);
    }
}
