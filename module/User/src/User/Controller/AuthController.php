<?php
namespace User\Controller;

use Application\ConfigAwareInterface;
use User\Model\UserTable;
use User\Authentification\Adapter\DbCallbackCheckAdapter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Form;
use Zend\View\Model\ViewModel;
use Zend\Config\Config as ZendConfig;

class AuthController extends AbstractActionController implements ConfigAwareInterface
{

	/**
	 * @var ZendConfig
	 */
	protected $config;

	/**
	 * @var Form
	 */
	protected $loginForm;

	/**
	 *
	 * @var UserTable
	 */
	protected $userTable;

	public function setConfig(ZendConfig $config)
	{
		$this->config = $config;
	}

	public function loginAction()
	{
		$request = $this->getRequest();
		$form = $this->getLoginForm();
		$form->prepare();
		$form->setAttribute('action', $this->url()->fromRoute('authenticate'));
		$form->setAttribute('method', 'post');
		
		if ($request->getQuery()->get('redirect')) {
			$redirect = $request->getQuery()->get('redirect');
		} else {
			$redirect = false;
		}
		
		$session = $this->getServiceLocator()->get('Zend\Session\SessionManager')->getStorage();
		$error_message = $session->auth_error_message;
		unset($session->auth_error_message);
		
		return new ViewModel(array(
			'loginForm' => $form,
			'redirect' => $redirect,
			'error' => $request->getQuery()->get('e', 0),
			'error_message' => $error_message
		));
	}

	public function authenticateAction()
	{
		$session = $this->getServiceLocator()->get('Zend\Session\SessionManager')->getStorage();
		unset($session->auth_error_message);
		
		// Si un utilisateur est connecté, on n'a rien à faire ici, on redirige vers la page d'accueil
		if ($this->userAuthentication()->hasIdentity()) {
			return $this->redirect()->toRoute('home');
		}
		
		// On doit avoir reçu une requête POST
		$request = $this->getRequest();
		if ($request->isPost()) {
			// Est-ce qu'on a reçu un paramètre de redirection après connexion dans le POST ?
			$loginPageParameters = array();
			$redirectUrl = $request->getPost('redirect', null);
			if (!empty($redirectUrl)) {
				$loginPageParameters['redirect'] = $redirectUrl;
			}
			
			// Par sécurité, on nettoie toute identité qui pourrait exister
			$this->userAuthentication()->clearIdentity();
			
			// On construit le formulaire de login et on le remplit avec les données reçus en POST,
			// afin d'utiliser la validation de formulaire
			$form = $this->getLoginForm();
			$form->setData($request->getPost());
			
			if ($form->isValid()) {
				// On donne le login et le mot de passe reçu à l'Authentification Adapter
				$this->userAuthentication()->getAuthAdapter()->setIdentity($request->getPost('identity'));
				$this->userAuthentication()->getAuthAdapter()->setCredential($request->getPost('credential'));
				
				// On initialise l'objet BCrypt pour vérifier le mot de passe, et on donne à l'Authentification Adapter notre propre fonction de
				// vérification qui utilise l'objet BCrypt
				$bcrypt = $this->getServiceLocator()->get('Miranda\Service\AuthBCrypt');
				$this->userAuthentication()->getAuthAdapter()->setCredentialValidationCallback(
						function ($storedPass, $givenPass) use($bcrypt)
						{
							return $bcrypt->verify($givenPass, $storedPass);
						});
				
				// Réalisation de l'authentification par l'Auth Adapter
				$authResult = $this->userAuthentication()->authenticate();
				if ($authResult->isValid()) {
					// Mise à jour de la date de dernière connexion
					$this->getUserTable()->updateLastActivity($this->getUserTable()->getUser($authResult->getIdentity()->id), true);
					
					// Est-ce qu'on a reçu un redirect ?
					if (!empty($redirectUrl)) {
						return $this->redirect()->toUrl($redirectUrl);
					} else {
						return $this->redirect()->toRoute('home');
					}
				} else {
					// Echec de l'autentification, on redirige vers la page de login avec une erreur
					$this->userAuthentication()->clearIdentity();
					if ($authResult->getCode() == DbCallbackCheckAdapter::FAILURE_NOT_ACTIVE) {
						$session->auth_error_message = 'This user account is not activated.';
					} else {
						$session->auth_error_message = 'Email or password are empty, malformed or invalid.';
					}
					return $this->redirect()->toRoute('login', array(), array(
						'query' => $loginPageParameters
					));
				}
			} else {
				$session->auth_error_message = 'Email or password are empty, malformed or invalid.';
				// Echec de validation du formulaire, on redirige vers la page de login avec une erreur
				return $this->redirect()->toRoute('login', array(), array(
					'query' => $loginPageParameters
				));
			}
		}
		
		// Pas de requête POST reçue, on redirige ver la page de login
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
			$this->setLoginForm($this->getServiceLocator()->get('User\Form\Login'));
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
			$this->userTable = $this->getServiceLocator()->get('User\Model\UserTable');
		}
		return $this->userTable;
	}
}