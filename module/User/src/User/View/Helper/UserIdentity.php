<?php

namespace User\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Authentication\AuthenticationService;

class UserIdentity extends AbstractHelper
{
    /**
     * @var AuthenticationService
     */
    protected $authService;

    /**
     * __invoke
     *
     * @access public
     * @return tbd
     */
    public function __invoke()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->getAuthService()->getIdentity();
        } else {
            return false;
        }
    }

    /**
     * Get authService.
     *
     * @return AuthenticationService
     */
    public function getAuthService()
    {
        return $this->authService;
    }

    /**
     * Set authService.
     *
     * @param AuthenticationService $authService
     * @return \User\View\Helper\UserIdentity
     */
    public function setAuthService(AuthenticationService $authService)
    {
        $this->authService = $authService;
        return $this;
    }
}
