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

class NodeVisitor extends NodeVisitorAbstract {
	private $extendsController = false;

	public function enterNode(Node $node) {
		if($node instanceof Node\Stmt\Class_) {
			if($node->extends &&
				$node->extends->getFirst() === 'Controller') {
				$this->extendsController = true;
			}
		}
	}

	public function afterTraverse(array $nodes) {
		if($this->extendsController === false) {
			throw new \Exception('Controller is not extended in this file');
		}
	}
}
