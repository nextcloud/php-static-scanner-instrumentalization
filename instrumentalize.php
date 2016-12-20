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

require_once __DIR__ . '/vendor/autoload.php';

if(count($argv) !== 2) {
	die(__FILE__ . ' $folder' . "\n");
}

$folderToScan = realpath($argv[1]);

ini_set('xdebug.max_nesting_level', 3000);

$scanner = new \Instrumentalizator\Scanner();

$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folderToScan),
	RecursiveIteratorIterator::SELF_FIRST);
foreach ($objects as $fileName => $object) {
	if(substr($fileName, -4) === '.php') {
		echo(sprintf(
			"Processing file %s\n",
			$fileName
		));
		$generatedCode = $scanner->scanFile($fileName);
		if ($generatedCode !== '') {
			file_put_contents($fileName, $generatedCode);
			echo(sprintf(
				"Instrumentalized file %s\n",
				$fileName
			));
		} else {
			echo(
				sprintf(
					"Did not instrumentalize file %s\n",
					$fileName
				)
			);
		}
	}
}
