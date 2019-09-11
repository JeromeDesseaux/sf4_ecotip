<?php

namespace App\Voter;

use App\Entity\Tip;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Entity\User;

class TipVoter extends Voter
{
    private $security;
    public const VIEW = 'view';
    public const EDIT = 'edit';

    private const ATTRIBUTES = [
        self::VIEW,
        self::EDIT,
    ];

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        return $subject instanceof Tip && in_array($attribute, self::ATTRIBUTES);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        // ADMIN can do anything
        if ($this->security->isGranted('ROLE_SUPER_ADMIN') || $this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // you know $subject is a Tip object, thanks to supports
        /** @var Tip $tip */
        $tip = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($tip, $user);
            case self::EDIT:
                return $this->canEdit($tip, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Tip $tip, User $user)
    {
        // if they can edit, they can view
        if ($this->canEdit($tip, $user)) {
            return true;
        }

        // the tip object could have, for example, a method isPrivate()
        // that checks a boolean $private property
        return !$tip->isPrivate();
    }

    private function canEdit(Tip $tip, User $user)
    {
        // this assumes that the data object has a getUser() method
        // to get the entity of the user who owns this data object
        return $this->isOwner($user, $tip);
    }

    private function isOwner(?User $user, Tip $tip)
    {
        if (!$user) {
            return false;
        }

        return $user->getId() === $tip->getUser()->getId();
    }
}
