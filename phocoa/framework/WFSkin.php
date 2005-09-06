<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * Skin System.
 *
 * A collection of classes and infrastructure for supporting skins of any layout, with multiple "themes" per skin.
 *
 * The system ships with a simple skin, "default", which is a simple wrapper. You can create your own skins.
 *
 * @package framework-base
 * @subpackage Skin
 * @copyright Alan Pinstein 2005
 * @author Alan Pinstein <apinstein@mac.com>
 * @version $Id: skin.php,v 1.37 2005/03/23 20:31:01 alanpinstein Exp $
 */

/**
 * Skin Manifest abstract interface. Each skin will need to have a concrete subclass to provide the system with needed information about itself.
 *
 * For a full explanation on the Skin infrastructure, including how to set up Skin Types, Skins, and Themes, see {@link WFSkin}.
 *
 * @see WFSkin
 *
 */
abstract class WFSkinManifestDelegate extends WFObject
{
    /**
     * Get the DEFAULT theme of for skin.
     *
     * This method is REQUIRED.
     *
     * @return string - The DEFAULT theme for this skin.
     */
    abstract function defaultTheme();

    /**
      * Get a list of themes for the skin.
      *
      * This is an optional method.
      *
      * @return array A list of all themes supported for this skin.
      */
    function themes() { return array(); }

    /**
     * Load the theme information for this skin.
     *
     * The theme information is a simple associative array of variables used by the skin. Good uses for this include
     * colorscheme information.
     *
     * This is an optional method.
     *
     * @param string $theme Theme information to retrieve.
     * @return assoc_array Various name/value pairs to be used in the templates for the skin.
     */
    function loadTheme($theme) { return array(); }
}

/**
 * Delegate interface for the skin object.
 *
 * The skin delegate provides the skin system with a way of extending the skin's capabilities. Essentially, the WFSkin object is a framework for building "themed" pages.
 * The parts of each skin that are provided by the skin infrastructure can be easily customized using the skin delegate system.
 *
 * The main web application mechanism always uses the skin delegate provided by {@link WFApplicationDelegate}. However, an application may have multiple skin delegates
 * for multiple skinned usages. For instance, maybe you have a need to send skinned email, but the skins for email have a different setup than the normal web site.
 * In this case, you could create a skin object and provide a specialized skin delegate to handle the skinnable email function.
 * 
 */
class WFSkinDelegate
{
    /**
      * Retreive the "named" content from the skin delegate.
      *
      * The "named content" mechanism is the way by which individual applications using this framework can add additional content sections
      * for use in their skins. By default, only a HEAD and BODY section exist within the skin.
      * Individual applications can use the named content mechanism to supply skin-specific information such as NAVIGATION LINKS, COPYRIGHT DISCLAIMERS, etc.
      *
      * @param string Name of the content to retrieve.
      * @param assoc_array Optional parameter list. Name/value pairs to pass on to content generator.
      * @return mixed The named content as provided byt he skin delegate.
      */
    function namedContent($name, $params = NULL) {}

    /**
      * Get a list of all named content for the skin delegate.
      *
      * @return array A list of all named content items in the catalog for this skin delegate.
      */
    function namedContentList() {}

    /**
      * A delegate method to allow the delegate object to load default values for certain skin properties such as:
      * skin, skinTheme, metaDescription, metaKeywords, title etc.
      * Example:
      *
      * $skin->setValueForKey('exampleskin2', 'skinName');
      * $skin->setValueForKey('red', 'skinThemeName');
      * $skin->setMetaDescription('This is the default description.');
      * $skin->addMetaKeywords(array('more', 'keywords'));
      * $skin->setTitle('Page Title');
      *
      * @param object The skin object to load defaults for.
      */
    function loadDefaults($skin) {}
}

define('SKIN_WRAPPER_TYPE_NORMAL',      0);
define('SKIN_WRAPPER_TYPE_MINIMAL',     1);
define('SKIN_WRAPPER_TYPE_RAW',         2);

/**
  * Main skin class for managing skin wrappers around the content.
  *
  * The Web Application's WFRequestController object always uses exactly one skin instance to render the page.
  * However, your application may choose to create other skin instances to use the infrastructure for things like HTML email, etc.
  *
  * The Skin mechanism is broken down into three layers. Each layer provides the ability to swap behaviors/looks at runtime.
  * For each request, one of each layer must be specified.
  *
  * 1. Skin Type -- i.e., which SkinDelegate is used. The Skin Delegate provides the skin with its catalog of behaviors, i.e., menus, footers, etc.
  *                 Each skin type is unique, and skins must be written specifically for each Skin Type.
  *                 Most web sites have just one skin type, that handles the elements appropriate for the skins of that application.
  *                 However, sometimes there is a need for a single site to have multiple skins. For instance, the public site may have different
  *                 navigational needs than the back-end admin interface.
  *                 A {@link WFSkinDelegate} is implemented for each skin type an tells the system what data is available for the skin type. 
  *                 Skin Types, however, do NOT provide any layout or style.
  * 2. Skin -- A skin provides basic layout for a given Skin Type. Skins are specific for Skin Types, since they necessarily know about the 
  *            data types offered by a particular skin type, via its Skin Delegate.
  *            Each Skin resides in its own directory inside the Skin Type directory that it belongs to.
  *            Each Skin thus provides a template file that implements a layout. Skins may also have multiple Skin Themes.
  *            Each Skin has a {@link WFSkinManifestDelegate SkinManifestDelegate} which tells the system which themes are available, and which theme to use by default.
  * 3. Skin Themes -- It may be desirable for a skin to have multiple colorschemes or other minor "thematic" differences. Each skin must have at least one theme.
  *                   Infrastructure is provided so that the SkinManifestDelegate can easily supply different data to the skin based on the theme. This allows easy creation
  *                   of colorschemes or other thematic differences.
  *
  * <pre>
  * Skin Directory Structure:
  *
  * The skins have a specified, hierarchical directory structure, based in the "skins" directory.
  * skins/ - Contains only directories; each directory represents a Skin Type.
  * skins/&lt;skinType&gt;/ - For each skin type, pick a unique name and create a directory.
  * skins/&lt;skinType&gt;/&lt;skinType&gt;_SkinDelegate.php - A php file containing exactly one class, named &lt;skinType&gt;_SkinDelegate, that is the {@link WFSkinDelegate} for the Skin Type.
  * skins/&lt;skinType&gt;/&lt;skinName&gt;/ - Also in the skinType directory are other directories, one for each skin that can be used for the Skin Type.
  * skins/&lt;skinType&gt;/&lt;skinName&gt;/&lt;skinName&gt;_SkinManifestDelegate.php - The {@link WFSkinManifestDelegate} for the skin &lt;skinName&gt;.
  * skins/&lt;skinType&gt;/&lt;skinName&gt;/* - Other files in here are the various tpl and css files used for this skin.
  * skins/&lt;skinType&gt;/&lt;skinName&gt;/www/ - Web root of the skin. Nothing actually goes in this folder but other folders.
  * skins/&lt;skinType&gt;/&lt;skinName&gt;/www/shared/* - Files that need to be accesible to the WWW and are shared by multiple themes of this skin go here.
  * skins/&lt;skinType&gt;/&lt;skinName&gt;/www/&lt;themeName&gt;/* - Files that need to be accessible to the WWW and are specific to a theme go here.  Each theme has its own folder to contain "themed" versions of resources. Typically every theme has the same set of resources, but of course customized for that theme.
  *
  * To use WWW visible items in your pages, simply use {$skinDir}/myImage.jpg and {$skinDirShared}/mySharedImage.jpg in your templates. The skin system automatically assigns these vars.
  * skinDir maps to skins/&lt;skinType&gt;/&lt;skinName&gt;/www/&lt;themeName&gt;/
  * skinDirShared maps to skins/&lt;skinType&gt;/&lt;skinName&gt;/www/shared/
  * </pre>
  *
  * @see WFSkinDelegate
  * @see WFSkinManifestDelegate
  * @todo Figure out better nomenclature for the 3 levels of skin funtionality; rework APIs.
  */
class WFSkin extends WFObject
{
    /**
     * @var string The skin delegate name to use for this instance.
     */
    protected $delegateName;
    /**
     * @var string The skin to use for this instance.
     */
    protected $skinName;
    /**
      * @var string The theme of the skin to use.
      */
    protected $skinThemeName;
    /**
      * @var object The {@link WFSkinDelegate delegate object} for this skin.
      */
    protected $delegate;
    /**
      * @var object The SkinManifestDelegate for the current skin.
      */
    protected $skinManifestDelegate;
    /**
      * @var string The body content for the skin. This is the only "predefined" content element of the skin infrastructure.
      */
    protected $body;
    /**
      * @var string The TITLE of the skin. This will be used automatically as the HTML title.
      */
    protected $title;
    /**
      * @var array A list of META KEYWORDS for HTML skins.
      */
    protected $metaKeywords;
    /**
      * @var string The META DESCRIPTION for HTML skins.
      */
    protected $metaDescription;
    /**
      * @var integer Skin wrapper type. One of SKIN_WRAPPER_TYPE_NORMAL, SKIN_WRAPPER_TYPE_MINIMAL, SKIN_WRAPPER_TYPE_RAW.
      */
    protected $templateType;
    /**
     * @var array An array of strings of things that needed to be added to the <head> section.
     */
    protected $headStrings;

    function __construct()
    {
        // determine which skin to use
        $wa = WFWebApplication::sharedWebApplication();
        $this->skinName = 'default';

        $this->delegate = NULL;
        $this->skinManifestDelegate = NULL;
        $this->body = NULL;
        $this->templateType = SKIN_WRAPPER_TYPE_NORMAL;

        $this->title = NULL;
        $this->metaKeywords = array();
        $this->metaDescription = NULL;
        $this->headStrings = array();
    }

    /**
      * Set the TYPE of skin template to use to wrap the content.
      *
      * @param integer The template type to use. There are three supported types:
      *     SKIN_WRAPPER_TYPE_NORMAL - Normal skin (template_normal.tpl)
      *     SKIN_WRAPPER_TYPE_MINIMAL - Minimual skin (template_min.tpl)
      *     SKIN_WRAPPER_TYPE_RAW - NO wrapper; just send exact body content.
      */
    function setTemplateType($id)
    {
        $this->templateType = $id;
    }

    /**
     *  Set the skin's delegate by passing the NAME of the skin delegate.
     *
     *  This function will look for the skin delegate in the appropriate place, instantiate it, and set it up for this skin instance.
     *
     *  @param string The NAME of the Skin Type.
     *  @throws
     */
    function setDelegateName($skinDelegateName)
    {
        $this->delegateName = $skinDelegateName;
        // change name to our convention
        $skinDelegateFileClassName = $skinDelegateName . '_SkinDelegate';
        // find delegate in skins directory...
        $skinsDir = WFWebApplication::appDirPath(WFWebApplication::DIR_SKINS);
        $skinDelegatePath = $skinsDir . '/' . $skinDelegateName . '/' . $skinDelegateFileClassName . '.php';
        if (!file_exists($skinDelegatePath)) throw( new Exception("Skin Delegate {$skinDelegateName} file {$skinDelegatePath} does not exist.") );
        require_once($skinDelegatePath);
        if (!class_exists($skinDelegateFileClassName)) throw( new Exception("Skin Delegate class {$skinDelegateFileClassName} does not exist.") );
        $this->setDelegate(new $skinDelegateFileClassName());
    }

    /**
     *  Get the name of the Skin Type for the current instance.
     *
     *  @return string The name of the current skin type.
     */
    function delegateName()
    {
        return $this->delegateName;
    }

    /**
      * Assign a skin delegate for this instance.
      * @param object The skin delegate.
      */
    function setDelegate($skinDelegate)
    {
        $this->delegate = $skinDelegate;
        $this->loadDefaults();
    }

    /**
      * Set the skin to the given name. Will automatically load the skin and its default theme.
      * @param string The name of the skin to use.
      */
    function setSkin($skinName)
    {
        $this->skinName = $skinName;
        $this->loadSkin();
    }

    /**
     *  Get the current skin name
     *
     *  @return string The name of the current skin.
     */
    function skinName()
    {
        return $this->skinName;
    }

    /**
      * Load the current skin.
      * @internal
      */
    function loadSkin()
    {
        // load the current skin
        $skinsDir = WFWebApplication::appDirPath(WFWebApplication::DIR_SKINS);
        $skinManifestDelegateFileClassName = $this->skinName . '_SkinManifestDelegate';
        $skinManifestDelegatePath = $skinsDir . '/' . $this->delegateName . '/' . $this->skinName . '/' . $skinManifestDelegateFileClassName . '.php';
        if (!file_exists($skinManifestDelegatePath)) throw( new Exception("Skin manifest delegate file does not exist: $skinManifestDelegatePath.") );
        require_once($skinManifestDelegatePath);

        // instantiate the skin manifest delegate
        if (!class_exists($skinManifestDelegateFileClassName)) throw( new Exception("Skin manifest delegate class does not exist: {$skinManifestDelegateFileClassName}."));
        $this->skinManifestDelegate = new $skinManifestDelegateFileClassName();

        // make sure a theme is selected
        if (empty($this->skinThemeName)) $this->skinThemeName = $this->skinManifestDelegate->defaultTheme();
    }

    /**
     *  Add a string that needs to go in the page's head section.
     *
     *  @param string The string to go in the head section.
     */
    function addHeadString($string)
    {
        $this->headStrings[] = $string;
    }

    /**
      * Set the content for the skin to wrap. Typically this is HTML but could be anything.
      * @param string The content of the skin.
      */
    function setBody($html)
    {
        $this->body = $html;
    }

    /**
      * Set the title of the page. This is the HTML title if you are building an HTML skin.
      * @param string The title of the page.
      */
    function setTitle($title)
    {
        $this->title = $title;
    }

    /**
      * Add meta keywords to the skin.
      * @param array A list of keywords to add.
      */
    function addMetaKeywords($keywords)
    {
        $this->metaKeywords = array_merge($this->metaKeywords, $keywords);
    }

    /**
      * Set the META DESCRIPTION of the page.
      * @param string The description of the page.
      */
    function setMetaDescription($description)
    {
        $this->metaDescription = $description;
    }

	/**
	* return a path to the skin image dir
	* @todo Convert this to use the www root in the skins/ dir rather than the wwwroot/skins/ dir to consolidate skins into a single subdirectory
	*/
	function getSkinDir()
	{
		return WWW_ROOT . '/skins/' . $this->delegateName . '/' . $this->skinName . '/' . $this->skinThemeName;
	}

	/**
	* return a path to the skin shared image dir
	* @todo Convert this to use the www root in the skins/ dir rather than the wwwroot/skins/ dir to consolidate skins into a single subdirectory
	*/
	function getSkinDirShared()
	{
		return WWW_ROOT . '/skins/' . $this->delegateName . '/' . $this->skinName . '/shared';
	}


    /**
      * Render the skin.
      * @param boolean TRUE to display the results to the output buffer, FALSE to return them in a variable. DEFAULT: TRUE.
      * @return string The rendered view. NULL if displaying.
      * @todo convert the DIR_SMARTY calls to use a new WFWebApplication::getResource($path) infrastructure that will allow for userland overloads of these templates
      */
    function render($display = true)
    {
        $this->loadSkin();

        $skinTemplateDir = WFWebApplication::appDirPath(WFWebApplication::DIR_SKINS) . '/' . $this->delegateName . '/' . $this->skinName;

        $smarty = new WFSmarty();
        $smarty->assign('skin', $this);

        // add variables to smarty
        $themeVars = $this->skinManifestDelegate->loadTheme($this->skinThemeName);
        $smarty->assign('skinThemeVars', $themeVars);
        $smarty->assign('skinTitle', $this->title);
        $smarty->assign('skinMetaKeywords', join(',', $this->metaKeywords));
        $smarty->assign('skinMetaDescription', $this->metaDescription);
        $smarty->assign('skinBody', $this->body);
        $smarty->assign('skinHeadStrings', join("\n", $this->headStrings));

        // set up shared directory URLs
        $smarty->assign('skinDir', $this->getSkinDir() );
        $smarty->assign('skinDirShared', $this->getSkinDirShared() );

        // build the <head> section
        $smarty->assign('skinHead', $smarty->fetch(WFWebApplication::appDirPath(WFWebApplication::DIR_SMARTY) . '/head.tpl'));

        // set the template
        switch ($this->templateType) {
            case SKIN_WRAPPER_TYPE_NORMAL:
                $smarty->setTemplate($skinTemplateDir . '/template_normal.tpl');
                break;
            case SKIN_WRAPPER_TYPE_MINIMAL:
                $smarty->setTemplate($skinTemplateDir . '/template_minimal.tpl');
                break;
            case SKIN_WRAPPER_TYPE_RAW:
                $smarty->setTemplate(WFWebApplication::appDirPath(WFWebApplication::DIR_SMARTY) . '/template_raw.tpl');
                break;
        }

        // pre-render callback
        $this->willRender();

        // render smarty
        if ($display) {
            $smarty->render();
        } else {
            return $smarty->render(false);
        }
    }

    /**
      * Get a list of all installed skin types.
      * @static
      * @return array Skin Types are installed.
      */
    function installedSkinTypes()
    {
        $skinTypes = array();

        $skinsDir = WFWebApplication::appDirPath(WFWebApplication::DIR_SKINS);
        $dh = opendir($skinsDir);
        if ($dh) {
            while ( ($file = readdir($dh)) !== false ) {
                if (is_dir($skinsDir . '/' . $file) and !in_array($file, array('.','..'))) {
                    array_push($skinTypes, $file);
                }
            }
            closedir($dh);
        }

        return $skinTypes;
    }

    /**
      * Get a list of all installed skins for the current Skin Type.
      *
      * @return array Skins that are installed.
      */
    function installedSkins()
    {
        $skins = array();

        $skinsDir = WFWebApplication::appDirPath(WFWebApplication::DIR_SKINS);
        $skinDirPath = $skinsDir . '/' . $this->delegateName;
        $dh = opendir($skinDirPath);
        if ($dh) {
            while ( ($file = readdir($dh)) !== false ) {
                if (is_dir($skinDirPath . '/' . $file) and !in_array($file, array('.','..'))) {
                    array_push($skins, $file);
                }
            }
            closedir($dh);
        }

        return $skins;
    }

    /**
     * Allow the skin delegate to load the default values for this skin.
     * @see WFSkinDelegate::loadDefaults
     */
    function loadDefaults()
    {
        // call skin delegate to get skin to use -- delegate implements application-specific logic.
        if (is_object($this->delegate) && method_exists($this->delegate, 'loadDefaults')) {
            $this->delegate->loadDefaults($this);
        }
    }

    /**
     * Get the catalog (ie list) of named content for this skin from its delegate.
     * If the skin delegate supports additional content for the skin, the catalog of content is provided here. Mostly this is for documentation purposes.
     * @see WFSkinDelegate::namedContentList
     * @return array Array of strings; each entry is a name of a content driver for this skin delegate.
     */
    function namedContentList()
    {
        if (is_object($this->delegate) && method_exists($this->delegate, 'namedContentList')) {
            return $this->delegate->namedContentList();
        }
        return array();
    }

    /**
     * Get the named content from the delegate.
     * @see WFSkinDelegate::namedContent
     * @param string The name of the content to retrieve.
     * @param assoc_array A list of additional parameters.
     * @return mixed The content for the named content for this skin instance. Provided by the delegate.
     */
    function namedContent($name, $options = NULL)
    {
        if (is_object($this->delegate) && method_exists($this->delegate, 'namedContent')) {
            return $this->delegate->namedContent($name, $options);
        }
        return NULL;
    }

    /**
     *  Pre-render callback.
     *
     *  Calls the skin delegate's willRender() method if it exists.
     *  This method is called just before the template for the skin is rendered.
     */
    function willRender()
    {
        if (is_object($this->delegate) && method_exists($this->delegate, 'willRender')) {
            $this->delegate->willRender();
        }
    }
}
?>