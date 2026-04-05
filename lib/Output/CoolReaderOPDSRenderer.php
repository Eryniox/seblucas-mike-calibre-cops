<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * OPDS renderer compatible with old CoolReader GL (OPDS 1.0/1.1 style).
 * Uses relative URLs (?page=6&db=0) exactly like seblucas-cops PHP7 version did,
 * instead of absolute-style URLs (feedcr.php?page=6&db=0).
 * Also strips thr:count and opds:facetGroup as these are OPDS 1.2 extensions
 * not understood by old CoolReader GL.
 */

namespace SebLucas\Cops\Output;

class CoolReaderOPDSRenderer extends OPDSRenderer
{
    /**
     * Override renderLink to use $link->href directly (relative URLs),
     * matching the behaviour of the old seblucas-cops PHP7 OPDS renderer.
     */
    protected function renderLink($link, $number = null)
    {
        $this->getXmlStream()->startElement("link");
        // Use $link->href directly to get relative URLs like ?page=6&db=0
        // instead of feedcr.php?page=6&db=0. This matches what the old
        // seblucas-cops renderer did and is what old CoolReader GL expects.
        $this->getXmlStream()->writeAttribute("href", $link->href);
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
