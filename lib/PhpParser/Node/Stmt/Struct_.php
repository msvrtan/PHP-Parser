<?php declare(strict_types=1);

namespace PhpParser\Node\Stmt;

use PhpParser\Comment;
use PhpParser\Error;
use PhpParser\Node;

class Struct_ extends Class_
{

	public function __construct($name, array $parameters = [])
	{

		$this->attributes = [];
		$this->flags = 0;
		$this->name = \is_string($name) ? new Node\Identifier($name) : $name;
		$this->extends = null;
		$this->implements = [];
		$this->stmts = [];

		$properties = [];
		$methods = [];

		/** @var Node\Param $property */
		foreach ($parameters as $parameter) {
			$props = [new PropertyProperty($parameter->var->name, null)];
			if($parameter->type instanceof Node\Name){
				$type=$parameter->type->toCodeString();
			}elseif($parameter->type instanceof Node\Identifier){
				$type=$parameter->type->name;
			}elseif($parameter->type instanceof Node\NullableType){
				$type=$parameter->type->type->toString().'|null';
			}

			$attributes = ['comments' => [new Comment('/** @var '.$type.' */')]];
			$properties[] = new Property(Class_::MODIFIER_PRIVATE, $props, $attributes);
		}

		$constructorStmts = [];

		if (count($parameters) > 0) {

			/** @var Node\Param $property */
			foreach ($parameters as $parameter) {
				$constructorStmts[] =
					new Expression(
						new Node\Expr\Assign(
							new Node\Expr\PropertyFetch(
								new Node\Expr\Variable('this'),
								$parameter->var->name
							),
							new Node\Expr\Variable($parameter->var->name)
						)
					);
			}

			$methods[] = new ClassMethod(
				'__construct',
				[
					'type'   => Class_::MODIFIER_PUBLIC,
					'params' => $parameters,
					'stmts'  => $constructorStmts
				]
			);
		}

		/** @var Node\Param $property */
		foreach ($parameters as $parameter) {
			$stmts = [
				new Return_(
					new Node\Expr\PropertyFetch(
						new Node\Expr\Variable('this'),
						$parameter->var->name
					)
				)
			];


			if($parameter->type instanceof Node\Name){
				$returnType=$parameter->type->toCodeString();
			}elseif($parameter->type instanceof Node\Identifier){
				$returnType=$parameter->type->name;
			}elseif($parameter->type instanceof Node\NullableType){
				$returnType='?'.$parameter->type->type->toString();
			}

			$methods[] = new ClassMethod(
				'get'.ucfirst($parameter->var->name),
				[
					'type'       => Class_::MODIFIER_PUBLIC,
					'returnType' => $returnType,
					'stmts'      => $stmts
				]
			);
		}
		$this->stmts = array_merge($properties, $methods);
	}

	public function getType(): string
	{
		return 'Stmt_Class';
	}
}
