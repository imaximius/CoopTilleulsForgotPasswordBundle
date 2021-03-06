<?php

/*
 * This file is part of the CoopTilleulsForgotPasswordBundle package.
 *
 * (c) Vincent Chalamon <vincent@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CoopTilleuls\ForgotPasswordBundle\Manager;

use CoopTilleuls\ForgotPasswordBundle\Entity\AbstractPasswordToken;
use CoopTilleuls\ForgotPasswordBundle\Event\ForgotPasswordEvent;
use CoopTilleuls\ForgotPasswordBundle\Manager\Bridge\ManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
class ForgotPasswordManager
{
    private $manager;
    private $passwordTokenManager;
    private $dispatcher;
    private $userClass;

    /**
     * @param string $userClass
     */
    public function __construct(
        PasswordTokenManager $passwordTokenManager,
        EventDispatcherInterface $dispatcher,
        ManagerInterface $manager,
        $userClass
    ) {
        $this->passwordTokenManager = $passwordTokenManager;
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->userClass = $userClass;
    }

    /**
     * @param $propertyName
     * @param $value
     */
    public function resetPassword($propertyName, $value)
    {
        $user = $this->manager->findOneBy($this->userClass, [$propertyName => $value]);
        if (null === $user) {
            return false;
        }

        $token = $this->passwordTokenManager->findOneByUser($user);

        // A token already exists and has not expired
        if (null === $token || $token->isExpired()) {
            $token = $this->passwordTokenManager->createPasswordToken($user);
        }

        // Generate password token
        $this->dispatcher->dispatch(
            new ForgotPasswordEvent($token),
            ForgotPasswordEvent::CREATE_TOKEN
        );

        return true;
    }

    /**
     * @param string $password
     *
     * @return bool
     */
    public function updatePassword(AbstractPasswordToken $passwordToken, $password)
    {
        // Update user password
        $this->dispatcher->dispatch(
            new ForgotPasswordEvent($passwordToken, $password),
            ForgotPasswordEvent::UPDATE_PASSWORD
        );

        // Remove PasswordToken
        $this->manager->remove($passwordToken);

        return true;
    }
}
