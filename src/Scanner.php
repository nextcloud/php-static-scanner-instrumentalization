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

namespace Instrumentalizator;

use Instrumentalizator\Visitor\NodeVisitor;
use Instrumentalizator\Visitor\PublicFunctionVisitor;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\NodeTraverser;

class Scanner {

	public function scanFile($file) {
		try {
			$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
			$stmts = $parser->parse(file_get_contents($file));

			// Check if the file uses the Controller
			$traverser = new NodeTraverser;
			$traverser->addVisitor(new \Instrumentalizator\Visitor\UsesControllerClass());
			$stmts = $traverser->traverse($stmts);

			// Check if the file Extends the Controller
			$traverser = new NodeTraverser;
			$traverser->addVisitor(new NodeVisitor());
			$stmts = $traverser->traverse($stmts);

			// Now loop over all public functions
			$traverser = new NodeTraverser;
			$traverser->addVisitor(new PublicFunctionVisitor());
			$stmts = $traverser->traverse($stmts);

			$printer = new Standard();
			return "<?php\n" . $printer->prettyPrint($stmts);
		} catch (\Exception $e) {
			return '';
		}
	}
}
