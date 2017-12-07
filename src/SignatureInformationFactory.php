<?php
declare(strict_types = 1);

namespace LanguageServer;

use LanguageServer\Protocol\{SignatureInformation, ParameterInformation};
use Microsoft\PhpParser\FunctionLike;

class SignatureInformationFactory
{
    public function createSignatureInformation(
        FunctionLike $node,
        DefinitionResolver $definitionResolver,
        string $fileContents
    ): SignatureInformation {
        $params = $this->getParameters($node, $definitionResolver, $fileContents);
		$label = $this->getLabel($params);
		return new SignatureInformation(
			$label,
			$params,
			$definitionResolver->getDocumentationFromNode($node)
		);
    }

    private function getParameters(
        FunctionLike $node,
        DefinitionResolver $definitionResolver,
        string $fileContents
    ): array {
        $params = [];
        if ($node->parameters) {
            foreach ($node->parameters->getElements() as $element) {
                $param = (string) $definitionResolver->getTypeFromNode($element);
                $param .= ' ' . $element->variableName->getText($fileContents);
                if ($element->default) {
                    $param .= ' = ' . $element->default->getText($fileContents);
                }
                $params[] = new ParameterInformation(
                    $param,
                    $definitionResolver->getDocumentationFromNode($element)
                );
            }
        }
        return $params;
    }

    private function getLabel(array $params): string
    {
        $label = '(';
        if ($params) {
            foreach ($params as $param) {
                $label .= $param->label . ', ';
            }
            $label = substr($label, 0, -2);
        }
        $label .= ')';
        return $label;
    }
}
