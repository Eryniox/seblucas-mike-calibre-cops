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

// Debug logging (remove after diagnosis)
file_put_contents(
    __DIR__ . '/feedcr_debug.log',
    date('Y-m-d H:i:s') . ' ' . $_SERVER['REQUEST_URI'] . ' UA:' . ($_SERVER['HTTP_USER_AGENT'] ?? '') . "\n",
    FILE_APPEND
);

// Fix CoolReader GL URL path accumulation bug.
// CoolReader GL appends /<href> to the current URL instead of proper RFC 3986 resolution,
// producing URLs like /feedcr.php/feedcr.php?page=6 or /feedcr.php/?db=0/?page=6.
// We detect this and redirect to the clean URL. CoolReader then updates its base URL,
// so each subsequent navigation starts fresh.
$_rawUri = $_SERVER['REQUEST_URI'] ?? '';
$_selfScript = $_SERVER['SCRIPT_NAME'] ?? '/feedcr.php';
$_scriptBasename = basename($_selfScript); // feedcr.php
if (substr_count($_rawUri, $_scriptBasename) > 1) {
    // feedcr.php appears more than once — path doubling (e.g. /feedcr.php/feedcr.php?page=6)
    $_lastPos = strrpos($_rawUri, $_scriptBasename);
    $_suffix = substr($_rawUri, $_lastPos + strlen($_scriptBasename));
    header('Location: ' . $_selfScript . $_suffix, true, 302);
    exit;
} elseif (strpos($_rawUri, $_selfScript . '/') !== false) {
    // /feedcr.php/ with trailing slash (e.g. /feedcr.php/?db=0/?page=6)
    $_lastQ = strrpos($_rawUri, '?');
    header('Location: ' . $_selfScript . ($_lastQ !== false ? substr($_rawUri, $_lastQ) : ''), true, 302);
    exit;
}
unset($_rawUri, $_selfScript, $_scriptBasename, $_lastPos, $_suffix, $_lastQ);

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
