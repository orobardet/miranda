<?php

namespace User\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserAuthentication extends AbstractPlugin
{
    /**
     * @var AuthAdapter
     */
    protected $authAdapter;

    /**
     * @var AuthenticationService
     */
    protected $authService;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * Proxy convenience method
     *
     * @return bool
     */
    public function authenticate()
    {
        return $this->getAuthService()->authenticate();
    }
    
    /**
     * Proxy convenience method
     *
     * @return bool
     */
    public function clearIdentity()
    {
        return $this->getAuthService()->clearIdentity();
    }
    
    /**
     * Proxy convenience method
     *
     * @return bool
     */
    public function hasIdentity()
    {
        return $this->getAuthService()->hasIdentity();
    }

    /**
     * Proxy convenience method
     *
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->getAuthService()->getIdentity();
    }

    /**
     * Get authAdapter.
     *
     * @return ZfcUserAuthentication
     */
    public function getAuthAdapter()
    {
        return $this->authAdapter;
    }

    /**
     * Set authAdapter.
     *
     * @param authAdapter $authAdapter
     */
    public function setAuthAdapter($authAdapter)
    {
        $this->authAdapter = $authAdapter;
        return $this;
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
     */
    public function setAuthService(AuthenticationService $authService)
    {
        $this->authService = $authService;
        return $this;
    }

}
