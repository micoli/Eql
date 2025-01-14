<?php

declare(strict_types=1);

namespace Micoli\Elql\ExpressionLanguage;

use Micoli\Elql\Exception\ExpressionLanguageException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

class ExpressionLanguageEvaluator implements ExpressionLanguageEvaluatorInterface
{
    public function __construct(
        private readonly ExpressionLanguage $expressionLanguage = new ExpressionLanguage(new ArrayAdapter()),
    ) {
        foreach ([
            'strtoupper', 'strtolower',
            'str_starts_with', 'str_ends_with', 'str_contains',
            'substr', 'strlen',
            'trim', 'ltrim', 'rtrim',
            'abs', 'min', 'max', 'floor', 'ceil',
        ] as $nativeFunction) {
            $this->expressionLanguage->addFunction(ExpressionFunction::fromPhp($nativeFunction));
        }
    }

    public function evaluate(string $expression, mixed $record, array $parameters = []): mixed
    {
        try {
            return $this->expressionLanguage->evaluate($expression, [...$parameters, 'record' => $record]);
        } catch (SyntaxError $exception) {
            throw new ExpressionLanguageException($exception->getMessage(), previous: $exception);
        }
    }
}
