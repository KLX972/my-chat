<?php

namespace App\Security\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\Conversation;
use App\Repository\ConversationRepository;

/**
 *
 */
class ConversationVoter extends Voter
{
    const VIEW = 'view';

    private $conversationRepository;

    public function __construct(ConversationRepository $conversationRepository)
    {
        $this->conversationRepository = $conversationRepository;
    }


    protected function supports(string $attribute, $subject)
    {
        return $attribute == self::VIEW && $subject instanceOf Conversation;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $result = $this
                    ->conversationRepository
                    ->checkIfUserIsParticipant($subject->getId(), $token->getUser()->getId());

        return !!$result;

    }
}
