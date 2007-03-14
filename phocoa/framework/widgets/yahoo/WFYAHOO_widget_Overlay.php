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
 * A YAHOO Overlay widget for our framework.
 * 
 * <b>PHOCOA Builder Setup:</b>
 *
 * <b>Required:</b><br>
 * 
 * <b>Optional:</b><br>
 */
class WFYAHOO_widget_Overlay extends WFYAHOO_widget_Module
{
    /**
     * @var integer The X coordinate of the overlay.
     */
    protected $x;
    /**
     * @var integer The Y coordinate of the overlay.
     */
    protected $y;
    /**
     * @var array Interal var used to track the "context" for the content. Context controls the "location" where the content will be displayed.
     * @see setContext()
     */
    private $context;
    /**
     * @var boolean TRUE to make the overlay stay "fixed" in the center of the viewport. DEFAULT: false
     */
    protected $fixedcenter;
    /**
     * @var string CSS width for the overlay. DEFAULT: 300px. Although no default is "required", the panel doesn't look right on IE if there is no width specified.
     */
    protected $width;
    /**
     * @var string CSS height for the overlay. DEFAULT: NULL
     */
    protected $height;
    /**
     * @var integer The z-index value of the overlay.
     */
    protected $zIndex;
    /**
     * @var boolean TRUE to try to constrain the overal inside the viewport. DEFAULT: false
     */
    protected $constraintoviewport;
    /**
     * @var boolean TRUE to use an IFRAME for rendering. To fix z-index/select issue. Automatic for IE. DEFAULT: false; true for IE6 and below.
     */
    protected $iframe;

    /**
      * Constructor.
      */
    function __construct($id, $page)
    {
        parent::__construct($id, $page);
        $this->containerClass = 'Overlay';
        $this->fixedcenter = false;
        $this->width = '300px';
        $this->height = NULL;
        $this->zIndex = NULL;
        $this->constraintoviewport = false;
        $this->iframe = false;
        $this->x = NULL;
        $this->y = NULL;
        $this->context = NULL;
    }

    public static function exposedProperties()
    {
        $items = parent::exposedProperties();
        return array_merge($items, array(
            'fixedcenter' => array('true', 'false'),
            'width',
            'height',
            'zIndex',
            'iframe' => array('true', 'false'),
            'constraintoviewport' => array('true', 'false'),
            'x',
            'y',
            ));
    }

    function setFixedCenter($b)
    {
        $this->fixedcenter = $b;
    }

    /**
     *  Set the width of the module.
     *
     *  @param string Width in CSS terms: 240px, 5in, etc.
     */
    function setWidth($w)
    {
        $this->width = $w;
    }

    /**
     *  Set the height of the module.
     *
     *  @param string Height in CSS terms: 240px, 5in, etc.
     */
    function setHeight($w)
    {
        $this->height = $w;
    }

    function setZIndex($i)
    {
        $this->zIndex = $i;
    }

    function setIFrame($b)
    {
        $this->iframe = $b;
    }

    function setX($x)
    {
        $this->x = $x;
    }

    function setY($y)
    {
        $this->y = $y;
    }

    function setXY($x, $y)
    {
        $this->setX($x);
        $this->setY($y);
    }

    /**
     *  Set the context for the content.
     *
     *  @param string The element id to anchor near.
     *  @param string Which corner of the element to anchor to. One of "tr", "tl", "br", "bl".
     *  @param string Which corner of this content to anchor to the element's anchor corner. One of "tr", "tl", "br", "bl".
     */
    function setContext($id, $elementCorner, $contextCorner)
    {
        $this->context['id'] = $id;
        $this->context['elementCorner'] = $elementCorner;
        $this->context['contextCorner'] = $contextCorner;
    }


    function render($blockContent = NULL)
    {
        if ($this->hidden)
        {
            return NULL;
        }
        else
        {
            $yuiPath = WFWebApplication::webDirPath(WFWebApplication::WWW_DIR_BASE) . '/framework/yui';

            // set up basic HTML
            $html = parent::render($blockContent);
            $script = "
<script type=\"text/javascript\">
//<![CDATA[

YAHOO.namespace('phocoa.widgets.overlay');
YAHOO.phocoa.widgets.overlay.init_{$this->id} = function() {
    YAHOO.phocoa.widgets.module.init_{$this->id}();
    var overlay = PHOCOA.runtime.getObject('{$this->id}');
    overlay.cfg.setProperty('fixedcenter', " . ($this->fixedcenter ? 'true' : 'false') . ");
    overlay.cfg.setProperty('iframe', " . ($this->iframe ? 'true' : 'false') . ");
    overlay.cfg.setProperty('constraintoviewport', " . ($this->constraintoviewport ? 'true' : 'false') . ");" . 
    ($this->context ? "\n    overlay.cfg.setProperty('context', [ '{$this->context['id']}', '{$this->context['elementCorner']}', '{$this->context['contextCorner']}' ] );" : NULL ) . 
    ($this->width ? "\n    overlay.cfg.setProperty('width', '{$this->width}');" : NULL ) . 
    ($this->height ? "\n    overlay.cfg.setProperty('height', '{$this->height}');" : NULL ) .
    ($this->x ? "\n    overlay.cfg.setProperty('x', '{$this->x}');" : NULL ) .
    ($this->y ? "\n    overlay.cfg.setProperty('y', '{$this->y}');" : NULL ) .
    ($this->zIndex ? "\n    overlay.cfg.setProperty('zIndex', '{$this->zIndex}');" : NULL ) . "
}
" .
( (get_class($this) == 'WFYAHOO_widget_Overlay') ? "YAHOO.util.Event.addListener(window, 'load', YAHOO.phocoa.widgets.overlay.init_{$this->id});" : NULL ) . "
//]]>
</script>";
            // output script
            $html .= "\n{$script}\n";
            return $html;
        }
    }

    function canPushValueBinding() { return false; }
}

?>