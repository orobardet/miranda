<?php
namespace User\Controller;

use Application\ConfigAwareInterface;
use User\Authentification\Adapter\DbCallbackCheckAdapter;
use Zend\Form\Form;
use Zend\View\Model\ViewModel;

class AuthController extends AbstractUserController implements ConfigAwareInterface
{

	/**
	 *
	 * @var Form
	 */
	protected $loginForm;

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
		
		return new ViewModel(
				[
					'loginForm' => $form,
					'redirect' => $redirect,
					'error' => $request->getQuery()->get('e', 0),
					'error_message' => $error_message
				]);
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
			$loginPageParameters = [];
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
					return $this->redirect()->toRoute('login', [], [
						'query' => $loginPageParameters
					]);
				}
			} else {
				$session->auth_error_message = 'Email or password are empty, malformed or invalid.';
				// Echec de validation du formulaire, on redirige vers la page de login avec une erreur
				return $this->redirect()->toRoute('login', [], [
					'query' => $loginPageParameters
				]);
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

	public function forgotpasswordAction()
	{
		$request = $this->getRequest();
		
		$form = $this->getServiceLocator()->get('User\Form\ForgotPassword');
		$form->prepare();
		$form->setAttribute('method', 'post');
		
		if ($request->isPost()) {
			$form->setData($request->getPost());
			
			if ($form->isValid()) {
				$user = $this->getUserTable()->getUserByEmail($form->getInputFilter()->getValue('email'), false);
				if ($user) {
					/* @var $mailer \Application\Mail\Mailer  */
					$mailer = $this->getServiceLocator()->get('Miranda\Service\Mailer');
					$mailer->setFromNoReply();
					$mailer->addTo($user->getEmail(), $user->getDisplayName());
					$mailer->setSubject($this->getServiceLocator()->get('translator')->translate('Account recovery'));
					
					if ($user->isActive()) {
						$user->createPasswordToken();
						$this->getUserTable()->saveUser($user);
						
						$mailer->token = $user->getPasswordToken();
						$mailer->setTemplate('password_recovery');
					} else {
						$mailer->setTemplate('password_recovery_disabled');
					}
					
					$mailer->send();
				}
				
				$this->resultStatus()->addResultStatus(
						$this->getServiceLocator()->get('translator')->translate(
								"<b>Recover process started!</b><br/>If the email address you gave was reconized, we sent a message with instructions to recover your account.<br/> Please check your mailbox."), 
						'success', false);
				return $this->redirect()->toRoute('login');
			}
		}
		
		return new ViewModel([
			'forgotPasswordForm' => $form
		]);
	}

	public function resetpasswordAction()
	{
		$token = $this->params()->fromRoute('token', null);
		if (!$token) {
			$this->resultStatus()->addResultStatus($this->getServiceLocator()->get('translator')->translate("Invalid request."), 'error');
			return $this->redirect()->toRoute('login');
		}
		
		$user = $this->getUserTable()->getUserByPasswordToken($token, false);
		if (!$user) {
			$this->resultStatus()->addResultStatus($this->getServiceLocator()->get('translator')->translate("User not found."), 'error');
			return $this->redirect()->toRoute('login');
		}
		
		$tokenValidity = (int)$this->getConfig()->get('authentification->password_token_validity', 60);
		if ($tokenValidity <= 0) {
			$tokenValidity = 60;
		}
		$tokenValidity *= 60;
		
		if ($user->getPasswordTokenDate() + $tokenValidity < time()) {
			$this->resultStatus()->addResultStatus(
					$this->getServiceLocator()->get('translator')->translate("Your account recovery request has expired. Please request a new one."), 
					'warning', false);
			return $this->redirect()->toRoute('login');
		}
		
		$form = $this->getServiceLocator()->get('User\Form\ResetPassword');
		$form->setAttribute('method', 'post');
		$form->get('submit')->setValue($this->getServiceLocator()->get('translator')->translate('Edit'));
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setData($request->getPost());
			
			if ($form->isValid()) {
				$bcrypt = $this->getServiceLocator()->get('Miranda\Service\AuthBCrypt');
				
				$user->setPassword($request->getPost('password'), $bcrypt);
				$user->resetPasswordToken();
				$this->getUserTable()->saveUser($user, true);
				
				$this->resultStatus()->addResultStatus($this->getServiceLocator()->get('translator')->translate("New password defined."), "success", 
						true);
				return $this->redirect()->toRoute('login');
			}
		}
		
		return new ViewModel([
			'form' => $form,
			'cancel_url' => $this->url()->fromRoute('login')
		]);
	}

	public function validateaccountAction()
	{
		$token = $this->params()->fromRoute('token', null);
		if (!$token) {
			$this->resultStatus()->addResultStatus($this->getServiceLocator()->get('translator')->translate("Invalid request."), 'error');
			return $this->redirect()->toRoute('login');
		}
		
		$user = $this->getUserTable()->getUserByRegistrationToken($token, false);
		if (!$user) {
			$this->resultStatus()->addResultStatus($this->getServiceLocator()->get('translator')->translate("User not found."), 'error');
			return $this->redirect()->toRoute('login');
		}
		
		$form = $this->getServiceLocator()->get('User\Form\ValidateAccount');
		$form->setAttribute('method', 'post');
		$form->bind($user);
		$form->get('submit')->setValue($this->getServiceLocator()->get('translator')->translate('Save'));
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setData($request->getPost());
			
			if ($form->isValid()) {
				$bcrypt = $this->getServiceLocator()->get('Miranda\Service\AuthBCrypt');
				
				$user->setPassword($request->getPost('password'), $bcrypt);
				$user->resetRegistrationToken();
				$this->getUserTable()->saveUser($user, true);
				
				$this->resultStatus()->addResultStatus(
						$this->getServiceLocator()->get('translator')->translate("Your account is validated. You can now log in."), "success", false);
				return $this->redirect()->toRoute('login');
			}
		}
		
		return new ViewModel([
			'form' => $form,
			'email' => $user->getEmail(),
			'cancel_url' => $this->url()->fromRoute('login')
		]);
	}

	protected function getLoginForm()
	{
		if (!$this->loginForm) {
			$this->loginForm = $this->getServiceLocator()->get('User\Form\Login');
		}
		
		return $this->loginForm;
	}
}