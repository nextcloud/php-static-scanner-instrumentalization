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
	public function enterNode(Node $node) {
		if(
			$node instanceof Node\Stmt\ClassMethod &&
			$node->name !== '__construct' &&
			$node->flags === Node\Stmt\Class_::MODIFIER_PUBLIC
		) {
			/** @var Node\Param[] $params */
			$params = $node->getParams();
			foreach($params as $param) {
				$var = new Node\Expr\Variable($param->name);
				$expr = new Node\Expr\Variable('_GET');
				$expr = new Node\Expr\ArrayDimFetch($expr, new Node\Scalar\String_($param->name));
				array_unshift($node->stmts, new Node\Expr\Assign($var, $expr));
			}

			$node->params = [];

		}

		return $node;
	}
}
