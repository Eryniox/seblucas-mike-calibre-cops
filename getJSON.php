<?php
/**
 * COPS (Calibre OPDS PHP Server) endpoint for JSON ajax requests
 * URL format: getJSON.php?page={page}&...
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 * @author     mikespub
 */

use SebLucas\Cops\Framework;

require_once __DIR__ . '/config.php';

$request = Framework::getRequest();

$handler = Framework::getHandler('json');
$handler->handle($request);
