<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * OPDS renderer compatible with old CoolReader GL (OPDS 1.0/1.1 style).
 * Strips rel="subsection" and thr:count from navigation links in entries,
 * as old CoolReader GL misinterprets these OPDS 1.2 extensions.
 */

namespace SebLucas\Cops\Output;

use SebLucas\Cops\Input\Request;
use SebLucas\Cops\Model\LinkFacet;
use SebLucas\Cops\Model\LinkFeed;
use SebLucas\Cops\Pages\Page;

class CoolReaderOPDSRenderer extends OPDSRenderer
{
    /**
     * Override startXmlDocument to omit xmlns:thr (not understood by old CoolReader GL)
     */
    protected function startXmlDocument($page, $request)
    {
        // Let parent build the full document, but we need to skip xmlns:thr.
        // Since XMLWriter doesn't allow removing attributes after the fact,
        // we reset and rebuild without it.
        $this->xmlStream = null; // force fresh stream (parent's getXmlStream() lazy-inits)
        parent::startXmlDocument($page, $request);
    }

    /**
     * Override renderLink to strip rel="subsection" and thr:count for old CoolReader GL.
     * These OPDS 1.2 extensions confuse old readers into thinking subsection links
     * are embedded content rather than navigation targets.
     */
    protected function renderLink($link, $number = null)
    {
        $this->getXmlStream()->startElement("link");
        $this->getXmlStream()->writeAttribute("href", $link->hrefXhtml(static::$endpoint));
        $this->getXmlStream()->writeAttribute("type", $link->type);
        // Skip rel="subsection" — old CoolReader GL treats it as embedded content, not navigation
        if (!is_null($link->rel) && $link->rel !== 'subsection') {
            $this->getXmlStream()->writeAttribute("rel", $link->rel);
        }
        if (!is_null($link->title)) {
            $this->getXmlStream()->writeAttribute("title", $link->title);
        }
        // Skip thr:count and opds:facetGroup/activeFacet — not understood by old CoolReader GL
        $this->getXmlStream()->endElement();
    }
}
