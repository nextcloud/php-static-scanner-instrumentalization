<?php
/**
 * @copyright Copyright (c) 2016 Lukas Reschke <lukas@statuscode.ch>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Instrumentalizator\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class PublicFunctionVisitor extends NodeVisitorAbstract {
	/**
	 * @param array $originalArray
	 * @param mixed $value
	 * @param int $key
	 * @return array
	 */
	private function insertBeforeKey(array $originalArray, $value, $key) {
		$newArray = [];
		for ($i = 0; $i <= count($originalArray) - 1; $i++) {
			$originalKey = $i;
			if($i === $key) {
				$newArray[$i] = $value;
				$originalKey = $i;
				$i++;
			}
			$newArray[$i] = $originalArray[$originalKey];
		}

		return $newArray;
	}

	public function enterNode(Node $node) {
		if($node instanceof Node\Stmt\ClassMethod) {
			/** @var Node\Param[] $params */
			$params = $node->getParams();

			// Replaces the parameters in a function call with an injected GET parameter
			if($node->name !== '__construct' &&
				$node->flags === Node\Stmt\Class_::MODIFIER_PUBLIC) {
				foreach ($params as $param) {
					$var = new Node\Expr\Variable($param->name);
					$expr = new Node\Expr\Variable('_GET');
					$expr = new Node\Expr\ArrayDimFetch($expr, new Node\Scalar\String_($param->name));
					array_unshift($node->stmts, new Node\Expr\Assign($var, $expr));
				}

				$node->params = [];
			}

			// Replaces the "return new JSONResponse" with an actual echo of the value
			/** @var Node\Stmt $subNode */
			foreach($node->getStmts() as $key => $subNode) {
				if($subNode instanceof Node\Stmt\Return_) {
					/** @var Node\Expr $newNode */
					$newNode = $subNode->expr;
					$className = $newNode->class->parts[0];
					if($className === 'JSONResponse') {
						$args = new Node\Arg(new Node\Expr\FuncCall(new Node\Name('json_encode'), $newNode->args));
						$node->stmts[$key] = new Node\Stmt\Echo_([$args]);

						$arg = new Node\Arg(new Node\Scalar\String_('Content-Type:application/json; charset=utf-8'));
						$header = new Node\Expr\FuncCall(new Node\Name('header'), [$arg]);
						$node->stmts = $this->insertBeforeKey($node->stmts, $header, $key);
						$arg = new Node\Arg(new Node\Scalar\String_('X-Content-Type-Options: nosniff'));
						$header = new Node\Expr\FuncCall(new Node\Name('header'), [$arg]);
						$node->stmts = $this->insertBeforeKey($node->stmts, $header, $key + 1);
					}

				}
			}

		}


		return $node;
	}
}
