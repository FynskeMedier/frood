<?php
/**
 * Cross site Frooding.
 *
 * PHP version 5
 *
 * @category Library
 * @package  Frood
 * @author   Jens Riisom Schultz <jers@fynskemedier.dk>
 * @since    2011-07-05
 */

/**
 * FroodRemote - Interoperate with Frood enabled modules.
 *
 * @category   Library
 * @package    Frood
 * @subpackage Class
 * @author     Jens Riisom Schultz <jers@fynskemedier.dk>
 */
class FroodRemote {
	/** @var string The module we're working with. */
	private $_module;

	/** @var string Which application are we running? */
	private $_app;

	/** @var string The name of the host to connect to. */
	private $_host;

	/**
	 * Do initialization stuff.
	 *
	 * @param string $module The dirname of the module to work with.
	 * @param string $app    Which application are we remoting to?
	 * @param string $host   The name of the host to connect to. Don't specify this to work locally.
	 *
	 * @return void
	 */
	public function __construct($module, $app = 'public', $host = null) {
		$this->_module = $module;
		$this->_app    = $app;
		$this->_host   = $host;
	}

	/**
	 * Dispatch an action to a controller.
	 * Call with no parameters to determine everything from the request.
	 *
	 * @param string          $controller The controller to call.
	 * @param string          $action     The action to invoke.
	 * @param FroodParameters $parameters The parameters for the action.
	 *
	 * @return string The response as a string.
	 *
	 * @throws FroodExceptionRemoteDispatch If Frood cannot dispatch.
	 *
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	public function dispatch($controller, $action, FroodParameters $parameters = null) {
		if ($parameters === null) {
			$parameters = new FroodParameters(array());
		}

		if ($this->_host === null) {
			$runner          = realpath(dirname(__FILE__) . '/../run/shell.php');
			$parameterString = self::_parametersToString($parameters);

			return shell_exec("php $runner {$this->_module} {$this->_app} $controller $action " . $parameterString);
		} else {
			$request = $this->_getRequest($controller, $action, $parameters);

			try {
				$request->send();
			} catch (HttpException $e) {
				throw new FroodExceptionRemoteDispatch($this->_host, $controller, $action, $parameters);
			}

			if ($request->getResponseCode() == 200) {
				return $request->getResponseBody();
			} else {
				throw new FroodExceptionRemoteDispatch($this->_host, $controller, $action, $parameters);
			}
		}
	}

	/**
	 * Create an HTTP post request.
	 *
	 * @param string          $controller The controller to call.
	 * @param string          $action     The action to invoke.
	 * @param FroodParameters $parameters The parameters for the action.
	 *
	 * @return HttpRequest
	 */
	public function _getRequest($controller, $action, FroodParameters $parameters) {
		$url = $this->_host;
		if (!preg_match('/\/$/', $url)) {
			$url .= '/';
		}

		$url .= "modules/{$this->_module}/" . ($this->_app != 'public' ? "{$this->_app}/" : '') . "$controller/$action";

		$request = new HttpRequest($url, HttpRequest::METH_POST);

		$fields = array();
		foreach ($parameters as $key => $value) {
			if ($value instanceof FroodFileParameter) {
				$request->addPostFile(
					FroodUtil::convertPhpNameToHtmlName($key),
					$value->getPath(),
					$value->getType()
				);
			} else {
				$fields[FroodUtil::convertPhpNameToHtmlName($key)] = $value;
			}
		}
		$request->addPostFields($fields);

		return $request;
	}

	/**
	 * Convert a FroodParameters instance to a string usable for run/shell.php
	 *
	 * @param FroodParameters $parameters The parameters to convert.
	 *
	 * @return string
	 */
	private function _parametersToString(FroodParameters $parameters) {
		$result = array();

		foreach ($parameters as $key => $value) {
			if ($value instanceof FroodFileParameter || is_array($value)) {
				$result[] = FroodUtil::convertPhpNameToHtmlName($key) . '=' . escapeshellarg('_SERI_:' . serialize($value));
			} else {
				$result[] = FroodUtil::convertPhpNameToHtmlName($key) . '=' . escapeshellarg($value);
			}
		}

		return implode(' ', $result);
	}
}
