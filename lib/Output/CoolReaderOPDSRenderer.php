<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * OPDS renderer compatible with old CoolReader GL.
 *
 * CoolReader GL has a URL resolution bug: given current URL /feedcr.php?db=0
 * and link href="feedcr.php?page=6&db=0", it appends the href as a path segment
 * instead of replacing the filename, producing /feedcr.php/feedcr.php?page=6&db=0.
 *
 * The fix is to use query-string-only hrefs like ?page=6&db=0 for navigation links.
 * These are stored in $link->href for LinkFeed/LinkNavigation instances.
 * Query-string-only relative URLs (starting with ?) are unambiguous even for
 * buggy resolvers: they replace the query of the base URL, keeping the path intact.
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Model\LinkFeed;

class CoolReaderOPDSRenderer extends OPDSRenderer
{
    /**
     * Override renderLink to use query-string-only hrefs for navigation links.
     * LinkFeed->href contains ?page=6&db=0 (relative to endpoint).
     * Using it directly avoids CoolReader GL's URL path-appending bug.
     */
    protected function renderLink($link, $number = null)
    {
        $this->getXmlStream()->startElement("link");
        if ($link instanceof LinkFeed) {
            // Use $link->href directly: produces ?page=6&db=0 instead of feedcr.php?page=6&db=0
            $this->getXmlStream()->writeAttribute("href", $link->href);
        } else {
            // LinkEntry (covers, downloads, etc.) – use href as-is, no endpoint prefix
            $this->getXmlStream()->writeAttribute("href", $link->hrefXhtml(static::$endpoint));
        }
        $this->getXmlStream()->writeAttribute("type", $link->type);
        if (!is_null($link->rel)) {
            $this->getXmlStream()->writeAttribute("rel", $link->rel);
        }
        if (!is_null($link->title)) {
            $this->getXmlStream()->writeAttribute("title", $link->title);
        }
        // Skip thr:count, opds:facetGroup, opds:activeFacet — OPDS 1.2 extensions
        // not understood by old CoolReader GL
        $this->getXmlStream()->endElement();
    }
}
