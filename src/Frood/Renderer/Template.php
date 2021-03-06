<?php
/**
 * This file is part of The Frood framework.
 * @link https://github.com/Ibmurai/frood
 *
 * @copyright Copyright 2011 Jens Riisom Schultz
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
/**
 * FroodRendererTemplate - A base class for Frood renderers which require templates.
 *
 * @category Frood
 * @package  Renderer
 * @author   Jens Riisom Schultz <ibber_of_crew42@hotmail.com>
 * @author   Bo Thinggaard <akimsko@tnactas.dk>
 */
abstract class FroodRendererTemplate extends FroodRenderer {
	/**
	 * Get the template file.
	 *
	 * @return string The partial path to a template file.
	 */
	protected function _getTemplateFile() {
		return "{$this->_request->getSubModule()}/{$this->_request->getController()}/{$this->_request->getAction()}.{$this->_getTemplateFileExtension()}";
	}

	protected function _getTemplate() {
		return Frood::getFroodConfiguration()->getTemplateFile($this->_request->getModule(), $this->_getTemplateFile());
	}

	/**
	 * Get the extension used for templates for the implementing renderer.
	 *
	 * @return string
	 */
	protected abstract function _getTemplateFileExtension();
}
