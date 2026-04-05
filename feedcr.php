<?php
/**
 * COPS (Calibre OPDS PHP Server) main script
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sébastien Lucas <sebastien@slucas.fr>
 *
 */
use SebLucas\Cops\Input\Config;
use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Output\CoolReaderOPDSRenderer;
use SebLucas\Cops\Output\OPDSRenderer;
use SebLucas\Cops\Pages\PageId;

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/Output/CoolReaderOPDSRenderer.php';

// Temporary debug logging — remove after diagnosis
file_put_contents(
    __DIR__ . '/feedcr_debug.log',
    date('Y-m-d H:i:s') . ' ' . $_SERVER['REQUEST_URI'] . ' UA:' . ($_SERVER['HTTP_USER_AGENT'] ?? '') . "\n",
    FILE_APPEND
);

OPDSRenderer::$endpoint = 'feedcr.php';

$request = new Request();
$page = $request->get('page', PageId::INDEX);
$query = $request->get('query');
if ($query) {
    $page = PageId::OPENSEARCH_QUERY;
}
// @todo handle special case of OPDS not expecting filter while HTML does better
$request->set('filter', null);

if (Config::get('fetch_protect') == '1') {
    session_start();
    if (!isset($_SESSION['connected'])) {
        $_SESSION['connected'] = 0;
    }
}

// header('Content-Type:application/xml');
header('Content-Type:text/xml');

$OPDSRender = new CoolReaderOPDSRenderer();

switch ($page) {
    case PageId::OPENSEARCH :
        echo $OPDSRender->getOpenSearch($request);
        return;
    default:
        $currentPage = PageId::getPage($page, $request);
        $currentPage->InitializeContent();
        echo $OPDSRender->render($currentPage, $request);
        return;
}
