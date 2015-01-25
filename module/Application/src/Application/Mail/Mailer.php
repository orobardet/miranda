<?php
namespace Application\Mail;

use Application\TraversableConfig;
use Zend\Mail;
use Zend\Mime;
use ArrayAccess;
use ArrayIterator;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class Mailer
{

	/**
	 *
	 * @var \Zend\Mail\Transport\TransportInterface
	 */
	protected $mailTransport = null;

	/**
	 *
	 * @var \Application\TraversableConfig
	 */
	protected $config = null;

	/**
	 *
	 * @var \Zend\View\Renderer\RendererInterface
	 */
	protected $renderer = null;

	/**
	 *
	 * @var \Zend\Mail\Message
	 */
	protected $message = null;

	/**
	 *
	 * @var string
	 */
	protected $templateName = null;

	/**
	 *
	 * @var array
	 */
	protected $variables = [];

	public function __construct(\Zend\Mail\Transport\TransportInterface $mailTransport, TraversableConfig $config, 
			\Zend\View\Renderer\RendererInterface $renderer)
	{
		if (!$mailTransport) {
			throw new \Exception('A mail transport must be given');
		}
		$this->mailTransport = $mailTransport;
		
		if (!$config) {
			throw new \Exception('A application configuration must be given');
		}
		$this->config = $config;
		
		if (!$renderer) {
			throw new \Exception('A view renderermust be given');
		}
		$this->renderer = $renderer;
		
		$this->message = new Mail\Message();
		$this->message->setEncoding("UTF-8");
		$this->setFromDefault();
	}

	/**
	 * Property overloading: set variable value
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->setVariable($name, $value);
	}

	/**
	 * Property overloading: get variable value
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if (!$this->__isset($name)) {
			return null;
		}
		
		$variables = $this->getVariables();
		return $variables[$name];
	}

	/**
	 * Property overloading: do we have the requested variable value?
	 *
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		$variables = $this->getVariables();
		return isset($variables[$name]);
	}

	/**
	 * Property overloading: unset the requested variable
	 *
	 * @param string $name
	 * @return void
	 */
	public function __unset($name)
	{
		if (!$this->__isset($name)) {
			return null;
		}
		
		unset($this->variables[$name]);
	}

	/**
	 * Get a single view variable
	 *
	 * @param string $name
	 * @param mixed|null $default (optional) default value if the variable is not present.
	 * @return mixed
	 */
	public function getVariable($name, $default = null)
	{
		$name = (string)$name;
		if (array_key_exists($name, $this->variables)) {
			return $this->variables[$name];
		}
		
		return $default;
	}

	/**
	 * Set view variable
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return ViewModel
	 */
	public function setVariable($name, $value)
	{
		$this->variables[(string)$name] = $value;
		return $this;
	}

	/**
	 * Set view variables en masse
	 *
	 * Can be an array or a Traversable + ArrayAccess object.
	 *
	 * @param array|ArrayAccess|Traversable $variables
	 * @param bool $overwrite Whether or not to overwrite the internal container with $variables
	 * @throws Exception\InvalidArgumentException
	 * @return ViewModel
	 */
	public function setVariables($variables, $overwrite = false)
	{
		if (!is_array($variables) && !$variables instanceof Traversable) {
			throw new \Exception(
					sprintf('%s: expects an array, or Traversable argument; received "%s"', __METHOD__, 
							(is_object($variables) ? get_class($variables) : gettype($variables))));
		}
		
		if ($overwrite) {
			if (is_object($variables) && !$variables instanceof ArrayAccess) {
				$variables = ArrayUtils::iteratorToArray($variables);
			}
			
			$this->variables = $variables;
			return $this;
		}
		
		foreach ($variables as $key => $value) {
			$this->setVariable($key, $value);
		}
		
		return $this;
	}

	/**
	 * Get view variables
	 *
	 * @return array|ArrayAccess|Traversable
	 */
	public function getVariables()
	{
		return $this->variables;
	}

	/**
	 * Clear all variables
	 *
	 * Resets the internal variable container to an empty container.
	 *
	 * @return ViewModel
	 */
	public function clearVariables()
	{
		$this->variables = [];
		return $this;
	}

	public function setFrom($email, $name = null)
	{
		$this->message->setFrom($email, $name);
	}

	public function setFromDefault()
	{
		$this->message->setFrom($this->config->get('mailer->default_from_address', 'info@noreply.com'), 
				$this->config->get('mailer->default_from_name', null));
	}

	public function setFromNoReply()
	{
		$this->message->setFrom($this->config->get('mailer->noreply_from_address', 'noreply@noreply.com'), 
				$this->config->get('mailer->noreply_from_name', null));
	}

	public function addTo($email, $name = null)
	{
		$this->message->addTo($email, $name);
	}

	public function setSubject($subject)
	{
		$this->message->setSubject($subject);
	}

	public function setTemplate($templateName)
	{
		$this->templateName = (string)$templateName;
	}

	public function send()
	{
		if (!$this->templateName) {
			throw new \Exception('No template name defined');
		}
		
		$this->setVariable('config', $this->config);
		$viewContent = new \Zend\View\Model\ViewModel($this->getVariables());
		$viewContent->setTemplate('email/' . $this->templateName . '.phtml');
		$content = $this->renderer->render($viewContent);
		
		$viewLayout = new \Zend\View\Model\ViewModel(array(
			'content' => $content,
			'config' => $this->config
		));
		$viewLayout->setTemplate('email/layout');
		
		$html = new Mime\Part($this->inlineCss($this->renderer->render($viewLayout)));
		$html->type = 'text/html';
		$body = new Mime\Message();
		$body->addPart($html);
		$this->addEmbeddedContent($body);
		
		$this->message->setBody($body);
		$this->mailTransport->send($this->message);
	}

	protected function addEmbeddedContent(\Zend\Mime\Message $mimeMessage)
	{
		$contents = $this->config->get('mailer->embbeded_content', []);
		if ($contents instanceof \Zend\Config\Config) {
			$contents = $contents->toArray();
		}
		
		if (count($contents)) {
			$contentHost = $this->config->get('mailer->embbeded_host', 'embbeded');
			
			foreach ($contents as $id => $data) {
				if (array_key_exists('path', $data) && file_exists($data['path'])) {
					$contentPart = new Mime\Part(fopen($data['path'], 'r'));
					
					$contentPart->filename = $id;
					$contentPart->encoding = Mime\Mime::ENCODING_BASE64;
					$contentPart->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;
					$contentPart->id = $id . '@' . $contentHost;
					if (array_key_exists('content_type', $data)) {
						$contentPart->type = $data['content_type'];
					}
					
					$mimeMessage->addPart($contentPart);
				}
			}
		}
	}

	protected function inlineCss($html)
	{
		$css = $this->config->get('mailer->css', null);
		if ($css && !file_exists($css)) {
			$css = null;
		}
		
		if ($css) {
			$cssInliner = new CssToInlineStyles($html, file_get_contents($css));
			$cssInliner->setUseInlineStylesBlock(true);
			$cssInliner->setStripOriginalStyleTags(true);
			$html = $cssInliner->convert();
		}
		
		return $html;
	}
}
