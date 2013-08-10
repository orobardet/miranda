<?php
namespace User\Controller;

use User\Model\UserTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Form;
use Zend\View\Model\ViewModel;

class UserController extends AbstractActionController
{
    /**
     * @var Form
     */
    protected $loginForm;

    /**
     * @var UserTable
     */
	protected $userTable;
	
	public function loginAction()
	{
		$request = $this->getRequest();
		$form = $this->getLoginForm();
		$form->prepare();
		$form->setAttribute('action',$this->url()->fromRoute('authenticate'));
		$form->setAttribute('method', 'post');
		
        if ($request->getQuery()->get('redirect')) {
            $redirect = $request->getQuery()->get('redirect');
        } else {
            $redirect = false;
        }
        
        return array(
            'loginForm' => $form,
            'redirect'  => $redirect,
    		'error' => $request->getQuery()->get('e', 0)
        );
	}
	
	public function authenticateAction()
	{
		$request = $this->getRequest();
		if ($this->userAuthentication()->hasIdentity()) {
			die('already');
		}
		if ($request->isPost()) {
            $this->userAuthentication()->clearIdentity();
            
			$form = $this->getLoginForm();
			$form->setData($request->getPost());
			if ($form->isValid()) {
				// Réaliser l'authentification
				$this->userAuthentication()->getAuthAdapter()->setIdentity($request->getPost()->get('identity'));
				$this->userAuthentication()->getAuthAdapter()->setCredential($request->getPost()->get('credential'));
				
				$authResult = $this->userAuthentication()->authenticate();
				if ($authResult->isValid()) {
					return $this->redirect()->toRoute('home');
    			}
    			else {
                    $this->userAuthentication()->clearIdentity();
    				return $this->redirect()->toRoute('login', array(), array('query' => array('e'=>1)));
    			}    				
			}
			else {
	            return $this->redirect()->toRoute('login', array(), array('query' => array('e'=>1)));
			}
		}
		
	    return $this->redirect()->toRoute('login');
	}

	public function logoutAction()
	{
        $this->userAuthentication()->clearIdentity();
		return $this->redirect()->toRoute('home');
	}

    public function getLoginForm()
    {
        if (!$this->loginForm) {
            $this->setLoginForm($this->getServiceLocator()->get('UserLoginForm'));
        }
        return $this->loginForm;
    }
	
    public function setLoginForm(Form $loginForm)
    {
        $this->loginForm = $loginForm;
        return $this;
    }
    
    public function getUserTable()
	{
		if (!$this->userTable) {
			$sm = $this->getServiceLocator();
			$this->userTable = $sm->get('User\Model\UserTable');
		}
		return $this->userTable;
	}
}