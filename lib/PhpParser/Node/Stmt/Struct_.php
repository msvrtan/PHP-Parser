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

		/** @var Node\Param $property */
		foreach ($parameters as $parameter) {
			$props = [new PropertyProperty($parameter->var->name, null)];
			$attributes = ['comments' => [new Comment('/** @var '.$parameter->type->name.' */')]];
			$this->stmts[] = new Property(Class_::MODIFIER_PUBLIC, $props, $attributes);
		}
	}

	public function getType(): string
	{
		return 'Stmt_Class';
	}
}
