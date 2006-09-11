<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * @package UI
 * @subpackage Widgets
 * @copyright Copyright (c) 2005 Alan Pinstein. All Rights Reserved.
 * @version $Id: kvcoding.php,v 1.3 2004/12/12 02:44:09 alanpinstein Exp $
 * @author Alan Pinstein <apinstein@mac.com>                        
 */

/**
 * The Dieselpoint Faceted Navigation UI placeholder.
 * 
 * This widget coordinates all WFDieselFacet widgets (which are its children). It provides the "cookie trail" of the search and the overall search form.
 * 
 * <b>PHOCOA Builder Setup:</b>
 *
 * <b>Required:</b><br>
 * - {@link WFWidget::$value value} or {@link WFSelect::$values values}, depending on {@link WFSelect::$multiple multiple}.
 * 
 * <b>Optional:</b><br>
 * - {@link WFLabel::$ellipsisAfterChars ellipsisAfterChars}
 */
class WFDieselNav extends WFWidget
{
    /**
     * @var object WFDieselSearch The WFDieselSearch object that this facet is linked to.
     */
    protected $dieselSearch;
    protected $facetNavOrder;
    protected $maxFacetsToShow;
    protected $baseURL;
    protected $facetNavHeight;  // css
    protected $searchAction;

    /**
      * Constructor.
      */
    function __construct($id, $page)
    {
        parent::__construct($id, $page);
        $this->value = NULL;
        $this->dieselSearch = NULL;
        $this->facetNavOrder = array();
        $this->maxFacetsToShow = 5;
        $this->baseURL = NULL;
        $this->facetNavHeight = '100px';
        $this->searchAction = 'search';
    }

    function facetNavHeight()
    {
        return $this->facetNavHeight;
    }

    function setFacetNavOrder($orderedList)
    {
        $this->facetNavOrder = array();
        $orderedIDs = explode(',', $orderedList);
        foreach ($orderedIDs as $id) {
            $this->facetNavOrder[] = trim($id);
        }
    }

    function setDieselSearch($ds)
    {
        $this->dieselSearch = $ds;
    }

    function baseURL()
    {
        if ($this->baseURL) return $this->baseURL;
        
        // calculate base URL for links
        if ($this->page->module()->invocation()->targetRootModule() and !$this->page->module()->invocation()->isRootInvocation())
        {
            $this->baseURL = WWW_ROOT . '/' . $this->page->module()->invocation()->rootInvocation()->invocationPath();
        }
        else
        {
            $this->baseURL = WWW_ROOT . '/' . $this->page->module()->invocation()->modulePath() . '/' . $this->page->pageName();
        }
        return $this->baseURL;
    }

    function render($blockContent = NULL)
    {
        if ($this->hidden)
        {
            return NULL;
        }
        else
        {
            $this->page()->module()->invocation()->skin()->addHeadString('<script type="text/javascript" src="' . WFWebApplication::webDirPath(WFWebApplication::WWW_DIR_FRAMEWORK) . '/js/prototype.js"></script>');

            $html = NULL;

            // js
            $html .= "
    <script type=\"text/javascript\">
    function doPopup(facetID, dpQueryState, facetSelections)
    {
        Element.show('phocoaWFDieselNav_Popup_{$this->id}');
        Element.update('phocoaWFDieselNav_PopupContent_{$this->id}', 'Loading...');
        var url = '" . $this->baseURL() . "/' + dpQueryState + '//' + facetID + '|' + facetSelections.replace(/\//g, '%2F');
        var pars = '';
        var target = 'phocoaWFDieselNav_PopupContent_{$this->id}';
        var myAjax = new Ajax.Updater(target, url, {method: 'get', parameters: pars, evalScripts: true});
    }
    function cancelPopup()
    {
        Element.hide('phocoaWFDieselNav_Popup_{$this->id}');
    }
    </script>
            ";

            // set up "popup"
            $html .= "
            <div id=\"phocoaWFDieselNav_Popup_{$this->id}\" class=\"phocoaWFDieselNav_Popup\" style=\"display: none;\">
            <div style=\"background: gray; border-bottom: 1px solid black; line-height: 18px; font-size: 15px; height: 18px; text-align: right;\"><a style=\"color: white; display: block;\" href=\"#\" onClick=\"cancelPopup();\">X</a></div>
            <div id=\"phocoaWFDieselNav_PopupContent_{$this->id}\"></div>
            <input type=\"submit\" name=\"action|" . $this->searchAction . "\" value=\"Go\"/>
            </div>\n";

            // show existing "filters" in proper order
            //$html .= '<table border="0" cellpadding="0" cellspacing="0"><tr>';
            // prepare a list of children, keyed by ID
            $facetNavsByID = array();
            foreach ($this->children() as $facetNav) {
                $facetNavsByID[$facetNav->id()] = $facetNav;
            }
            // keep track of facets that "appear" in the interface, either as a selection or as a clickable facet so that they are not repeated
            $renderedList = array();
            // keep track of each item as rendered so we don't do it 2x
            $selectionRenderedList = array();
            // render "filters"
            // first do items in desired order
            foreach ($this->facetNavOrder as $id) {
                if (!isset($facetNavsByID[$id])) throw( new Exception("Specified WFDieselFacet of id {$id} does not exist.") );

                $facetNav = $facetNavsByID[$id];
                if (!($facetNav instanceof WFDieselFacet)) continue;    // display only facets; skip keyword query
                $selectedHTML = $this->facetFilterNav($facetNav);
                if ($selectedHTML)
                {
                    $renderedList[$id] = true;
                }
                $html .= $selectedHTML;
                $selectionRenderedList[$id] = true;
            }
            foreach ($facetNavsByID as $id => $facetNav) {
                if (!($facetNav instanceof WFDieselFacet)) continue;    // display only facets; skip keyword query
                if (!isset($selectionRenderedList[$id]))
                {
                    $selectedHTML = $this->facetFilterNav($facetNav);
                    if ($selectedHTML)
                    {
                        $renderedList[$id] = true;
                    }
                    $html .= $selectedHTML;
                    $selectionRenderedList[$id] = true;
                }
            }
            $html .= "<br clear=\"all\" />\n";
            //$html .= '</tr></table>';

            // render all children, in desired order
            // prepare a list of children, keyed by ID
            $facetNavsByID = array();
            foreach ($this->children() as $facetNav) {
                $facetNavsByID[$facetNav->id()] = $facetNav;
            }
            // keep track of each item as rendered so we don't do it 2x
            //$renderedList = array();
            $renderedCount = 0;
            $moreChoicesListIDs = array();
            // render widgets
            $html .= '<table border="0" cellpadding="5" cellspacing="0"><tr>';
            // first do items in desired order
            foreach ($this->facetNavOrder as $id) {
                if (isset($facetNavsByID[$id]))
                {
                    $facetNav = $facetNavsByID[$id];
                    if (!($facetNav instanceof WFDieselFacet)) continue;    // display only facets; skip keyword query

                    // only show up to max facets; the rest go in the "more" list
                    if ($renderedCount >= $this->maxFacetsToShow)
                    {
                        $moreChoicesListIDs[] = $id;
                        continue;
                    }

                    $facetHTML = $facetNav->render();
                    if ($facetHTML)
                    {
                        $html .= "\n<td>{$facetHTML}</td>";
                        $renderedCount++;
                    }
                    $renderedList[$id] = true;
                }
                else
                {
                    throw( new Exception("Specified WFDieselFacet of id {$id} does not exist.") );
                }
            }
            // then do all remaining widgets
            foreach ($facetNavsByID as $id => $facetNav) {
                if (!($facetNav instanceof WFDieselFacet)) continue;    // display only facets; skip keyword query
                if (!isset($renderedList[$id]))
                {
                    // only show up to max facets; the rest go in the "more" list
                    if ($renderedCount >= $this->maxFacetsToShow)
                    {
                        $moreChoicesListIDs[] = $id;
                        continue;
                    }

                    $facetHTML = $facetNav->render();
                    if ($facetHTML)
                    {
                        $html .= "\n<td>{$facetHTML}</td>";
                        $renderedCount++;
                    }
                    $renderedList[$id] = true;
                }
            }
            $html .= "\n</tr></table>\n";

            if (count($moreChoicesListIDs))
            {
                $html .= "<div class=\"phocoaWFDieselNav_MoreChoices\"><b>More Choices:</b>\n";
                $first = true;
                foreach ($moreChoicesListIDs as $id) {
                    if (isset($facetNavsByID[$id]))
                    {
                        $facetNav = $facetNavsByID[$id];
                        if (!($facetNav instanceof WFDieselFacet)) continue;    // display only facets; skip keyword query
                        if (!$first)
                        {
                            $html .= ", ";
                        }
                        $html .= $facetNav->editFacetLink($facetNav->label());
                    }
                    else
                    {
                        throw( new Exception("Specified WFDieselFacet of id {$id} does not exist.") );
                    }
                    $first = false;
                }
                $html .= "\n</div>\n";
            }
            
            return $html;
        }
    }

    function facetFilterNav($facet)
    {
        $html = NULL;
        $selectedFilterHTML = $facet->facetSelectionHTML();
        if ($selectedFilterHTML)
        {
            $html .= "
                <div class=\"phocoaWFDieselNav_FilterInfo\">
                " . $facet->label() . ":<br />
                <b>" . $selectedFilterHTML . "</b><br />
                " . $facet->editFacetLink() . "&nbsp;&nbsp;|&nbsp;&nbsp;" . $facet->removeFacetLink() . "
                </div>
            ";
        }
        return $html;
    }

    function canPushValueBinding() { return false; }
}

?>
