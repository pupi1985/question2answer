<?php

class Twig_Extension_Q2A extends Twig_Extension {

	private $layerContexts = array();

	/**
	 * Returns a list of global functions to add to the existing list.
	 *
	 * @return array An array of global functions
	 */
	public function getFunctions() {
		return array(
			new Twig_SimpleFunction('q2a_layers', 'q2a_layers', array(
				'needs_context' => true,
				'needs_environment' => true
			)),
		);
	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName() {
		return 'q2a';
	}

	public function setLayerContexts(array $layerContexts) {
		$this->layerContexts = $layerContexts;
	}

	public function getLayerContexts() {
		return $this->layerContexts;
	}

}

function q2a_layers(Twig_Environment $env, $context, $paramBlockName) {
	$result = '';
	$layerContexts = $env->getExtension('q2a')->getLayerContexts();
	foreach ($layerContexts as $templateName => $layerContext) {
		$template = $env->loadTemplate($templateName);
		if ($template->hasBlock($paramBlockName))
			$result .= $template->renderBlock($paramBlockName, array_merge($context, $layerContext));
//		A layer object could be sent the message $paramBockName as a callback to easily add PHP code:
//		$args = array_slice(func_get_args(), 3);
//		qa_call_method($layerObject, $$paramBlockName, $args);
	}
	return $result;
}
