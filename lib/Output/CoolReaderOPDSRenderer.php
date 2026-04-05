<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * OPDS renderer compatible with old CoolReader GL.
 * Old CoolReader GL resolves relative links like "feedcr.php?page=6" by
 * appending to the current path, resulting in "feedcr.php/feedcr.php?page=6".
 * A RewriteRule in .htaccess catches and corrects this URL doubling.
 * This renderer also strips thr:count and opds:facetGroup (OPDS 1.2 extensions
 * not understood by old CoolReader GL).
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Model\LinkFacet;
use SebLucas\Cops\Model\LinkFeed;

class CoolReaderOPDSRenderer extends OPDSRenderer
{
    /**
     * Override renderLink to strip OPDS 1.2 extensions (thr:count, opds:facetGroup)
     * not understood by old CoolReader GL, while keeping proper hrefXhtml() URLs.
     */
    protected function renderLink($link, $number = null)
    {
        $this->getXmlStream()->startElement("link");
        // Use hrefXhtml() to get proper feedcr.php?page=6&db=0 style URLs.
        // These get doubled by old CoolReader GL to feedcr.php/feedcr.php?page=6
        // which is caught and fixed by the RewriteRule in .htaccess.
        $this->getXmlStream()->writeAttribute("href", $link->hrefXhtml(static::$endpoint));
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
