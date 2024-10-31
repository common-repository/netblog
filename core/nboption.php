<?php


/**
 * 
 * Netblog option handler
 * @author Globart
 * 
 */
class nboption
{
	function nboption() 
	{
		$this->citeStyleHeadline = __('References','netblog');
	}
	
	/**
	 * Set Netblog GUI Speed
	 *
	 * @param string $codeName
	 * @return bool
	 */
	function setGUISpeed( $codeName ) {
		return $this->update('netblog_gui_speed',$codeName);
	}
	
	/**
	 * Get Netblog GUI Speed/Responsiveness
	 *
	 * @return string
	 */
	function getGUISpeed() {
		return $this->fetch('netblog_gui_speed','fast');
	}
	
	/**
	 * Get default citation style
	 *
	 * @return string
	 */
	function getCiteStyle() 
	{
		return $this->fetch('netblog_cite_style','apa');
	}
	
	function setCiteStyle( $styleName )
	{
		return nbcstyle::is_style($styleName) && $this->update('netblog_cite_style',$styleName);
	}
	
	/**
	 * Check if default citation style will override
	 *
	 * @return bool
	 */
	function getCiteStyleOverride()
	{
		return $this->fetch('netblog_cite_style_override',false);
	}
	
	/**
	 * Enable whether default citation style will override
	 *
	 * @param bool $b
	 * @return bool
	 */
	function enableCiteStyleOverride( $b ) {
		return $this->update('netblog_cite_style_override',$b);
	}
	
	/**
	 * Get custom citation styles; don't mess with this - use secure nbcstyle instead.
	 * @return array[stylename[type,command]]
	 */
	function getCiteStyleCustom()
	{
		return $this->fetch('netblog_cite_style_custom', array());
	}
	
	/**
	 * Set new custom citation styles; use nbcstyle instead!
	 * @param array[stylename[type,command]] $cstyles
	 */
	function setCiteStyleCustom( $cstyles )
	{
		return $this->update('netblog_cite_style_custom', $cstyles);
	}
	
	/**
	 * Get custom citation styles; don't mess with this - use secure nbcstyle instead.
	 * @return bool
	 */
	function getBibShortCodeInclIds()
	{
            return false;
	}
	
	/**
	 * Set new custom citation styles; use nbcstyle instead!
	 * @param bool
	 */
	function enableBibShortCodeInclIds( $b )
	{
		return $this->update('netblog_bib_shortcode_include_ids', $b);
	}
	
	/**
	 * Get plugin shortcode name for citations.
	 *
	 * @return string
	 */
	function getCiteShortcode()
	{
		return $this->fetch('netblog_ss_citation','nbcite');
	}
	
	/**
	 * Get default bibliography headline
	 *
	 * @return string
	 */
	function getBibHeadline() 
	{
		return $this->fetch('netblog_cite_style_headline',$this->citeStyleHeadline);
	}
	
	/**
	 * Set default bibliography headline
	 *
	 * @param string $name
	 * @return bool
	 */
	function setBibHeadline( $name ) {
		return $this->update('netblog_cite_style_headline',$name);
	}

	/**
	 * Get the bibliography headline tag
	 *
	 * @return string
	 */
	function getBibHeadlineHtmlTag() 
	{
		return $this->fetch('netblog_cite_style_headline_htmltag', 'h2');
	}
	
	/**
	 * Set the bibliography headline tag
	 *
	 * @param string $name
	 * @return bool
	 */
	function setBibHeadlineHtmlTag( $name ) {
		return $this->update('netblog_cite_style_headline_htmltag',$name);
	}

	/**
	 * Get the bibliography formatting options coded as css, applied for each element in the final table
	 *
	 * @return string
	 */
	function getBibCssFormatting() 
	{
		return $this->fetch('netblog_cite_style_element_css', '');
	}
		
	/**
	 * Set the bibliography formatting options coded as css, applied for each element in the final table
	 *
	 * @param string $name
	 * @return bool
	 */
	function setBibCssFormatting( $name ) {
		return $this->update('netblog_cite_style_element_css',$name);
	}
			
	/**
	 * Get minimum character length of a bibliography headline
	 * @return int
	 */
	function getBibHeadlineMinlen()
	{
		$t = $this->fetch('netblog_bib_headline_lenmin',3);
		if( is_int($t) ) return $t;
		
		$t = (int) $t;
		return $t>0 ? $t : 3;
	}
	
	/**
	 * Get maximum character length of a bibliography headline
	 * @return int
	 */
	function getBibHeadlineMaxLen()
	{
		$t = $this->fetch('netblog_bib_headline_lenmax',128);
		if( is_int($t) ) return $t;
		
		$t = (int) $t;
		return $t>0 ? $t : 128;
	}
	
	/**
	 * Get append string if headline exceeds max len
	 */
	function getBibHeadlineAppendOnExceed()
	{
		return $this->fetch('netblog_bib_headline_appendexeed', '...');
	}
	
	/**
	 * Get maximum number of bibliographies per article
	 *
	 * @return int
	 */
	function getBibMaxNum()
	{
		$t = $this->fetch('netblog_bib_maxnum',3);
		if( is_int($t) ) return $t;
		
		$t = (int) $t;
		return $t>0 ? $t : 3;
	}
	
	/**
	 * Set maximum number of bibliographies per article
	 *
	 * @param int|string $num
	 * @return bool
	 */
	function setBibMaxNum( $num ) {
		return $this->update('netblog_bib_maxnum',max(Intval($num),0));
	}
	
	/**
	 * Check if bibliographies are to printed automatically
	 *
	 * @return bool
	 */
	function getBibAutoprint() 
	{
		return $this->fetch('netblog_bib_print',true);
	}
	
	/**
	 * Enable if bibliographies are to printed automatically
	 *
	 * @param bool $b
	 * @return bool
	 */
	function enableBibAutoprint( $b ) {
		return $this->update('netblog_bib_print',$b);
	}
	
	/**
	 * Get inline citation output format, e.g. literal, decimal, alpha etc.
	 *
	 * @return string
	 */
	function getCiteFormatOutput() 
	{
		return $this->fetch('netblog_cite_format_output','literal');
	}
	
	/**
	 * Set inline citation output format, e.g. literal, decimal, alpha etc.
	 *
	 * @param string $name
	 * @return bool
	 */
	function setCiteFormatOutput( $name ) {
		return $this->update('netblog_cite_format_output',$name);
	}
	
	/**
	 * Get inline citation style format
	 *
	 * @return string
	 */
	function getCiteFormatStyle() 
	{
		return $this->fetch('netblog_cite_format_style','font-style:italic;');
	}
	
	/**
	 * Set inline citation style format
	 *
	 * @param string $name
	 * @return bool
	 */
	function setCiteFormatStyle( $name ) {
		return $this->update('netblog_cite_format_style',$name);
	}	
	
	/**
	 * Get inline citation custom output format, e.g. [<output>]
	 *
	 * @return string
	 */
	function getCiteFormatCustomOutput() 
	{
		return $this->fetch('netblog_cite_format_customoutput','(<output>)');
	}
	
	/**
	 * Set inline citation custom output format, e.g. [<output>]
	 *
	 * @param string $name
	 * @return bool
	 */
	function setCiteFormatCustomOutput( $name ) {
		return $this->update('netblog_cite_format_customoutput',$name);
	}
		
	/**
	 * Get footnote numbering format
	 *
	 * @return string
	 */
	function getNoteFormat()
	{
		return $this->fetch('netblog_note_format','decimal');
	}
	
	/**
	 * Set numbering format for footnotes.
	 *
	 * @param string $formatCode
	 * @return bool
	 */
	function setNoteFormat( $formatCode )
	{
		return $this->update('netblog_note_format',$formatCode);
	}
	
	/**
	 * Check if table of footnotes should be printed automatically.
	 *
	 * @return bool
	 */
	function useNoteAutoprint()
	{
		return $this->fetch('netblog_note_print',true);
	}
	
	/**
	 * Enable autoprint for footnotes.
	 *
	 * @param bool $b
	 * @return bool
	 */
	function enableNoteAutoprint( $b )
	{
		return $this->update('netblog_note_print',$b);
	}
	
	/**
	 * Get shortcode for footnotes.
	 *
	 * @return string
	 */
	function getNoteShortcode()
	{
		return $this->fetch('netblog_ss_footnote','nbnote');
	}
	
	/**
	 * Get the footnote formatting options coded as css, applied for each element in the final table
	 *
	 * @return string
	 */
	function getFootnoteCssFormatting() 
	{
		return $this->fetch('netblog_footnote_element_css', '');
	}
		
	/**
	 * Set the footnote formatting options coded as css, applied for each element in the final table
	 *
	 * @param string $name
	 * @return bool
	 */
	function setFootnoteCssFormatting( $name ) {
		return $this->update('netblog_footnote_element_css',$name);
	}
	
	/**
	 * Get the footnote display option for its horizontal rule
	 *
	 * @return string
	 */
	function getFootnoteHorizontalRule() 
	{
		return $this->fetch('netblog_footnote_horizontalrule', 'above');
	}
		
	/**
	 * Set the footnote display option for its horizontal rule
	 *
	 * @param string $name
	 * @return bool
	 */
	function setFootnoteHorizontalRule( $name ) {
		return $this->update('netblog_footnote_horizontalrule',$name);
	}
		
	/**
	 * Get privilege for adding global captions.
	 *
	 * @return string
	 */
	function getCaptionPrivGadd()
	{
		return $this->fetch('netblog_caption_gadd','delete_pages');		
	}
	
	/**
	 * Set privilege for adding global captions
	 *
	 * @param string $priv
	 * @return bool
	 */
	function setCaptionPrivGadd( $priv )
	{
		return $this->update('netblog_caption_gadd',$priv);		
	}
	
	/**
	 * Get global captions (types); use secure nbdb instead.
	 * @return array
	 */
	function getCaptionGlobals()
	{
		return $this->fetch('netblog_captionsg_list', array());
	}
	
	/**
	 * Set new global captions; use secure nbdb instead.
	 * @param array $list
	 * @return bool
	 */
	function setCaptionGlobals( $list )
	{
		return $this->update('netblog_captionsg_list', $list);
	}
	
	/**
	 * Get plugin shortcode name for captions.
	 *
	 * @return string
	 */
	function getCaptionShortcode()
	{
		return $this->fetch('netblog_ss_caption','nbcaption');
	}
	
	/**
	 * Check if to use global captions.
	 *
	 * @return bool
	 */
	function useGlobalCaptions()
	{
		return $this->fetch('netblog_caption_useGlobal',false);
	}
	
	/**
	 * Enable/disable global captions
	 * @param bool[optional] $b
	 * @return bool FALSE on failure
	 */
	function enableGlobalCaptions( $b = true )
	{
		return $this->update('netblog_caption_useGlobal', $b );
	}
	
	/**
	 * Check if to use local net of further reading and references
	 * @return bool
	 */
	function useLocalNet()
	{
		return $this->fetch('netblog_net_use', false);
	}
	
	/**
	 * Enable/Disable local net
	 * @param bool[optional] $b
	 * @return bool FALSE of failure
	 */
	function enableLocalNet( $b = true )
	{
		return $this->update('netblog_net_use', $b);
	}
	
	/**
	 * Check if to use net to external resources of further reading and references
	 * @return bool
	 */
	function useExtNet()
	{
		return $this->fetch('netblog_ext_use', false);
	}
	
	/**
	 * Enable/Disable ext net
	 * @param bool[optional] $b
	 * @return bool FALSE on failure
	 */
	function enableExtNet( $b = true )
	{
		return $this->update('netblog_ext_use', $b);
	}
	
	/**
	 * Check if to use rel external nodes
	 * @return bool
	 */
	function useRelExtnodes()
	{
		return $this->fetch('netblog_extnodes_use', false);
	}
	
	/**
	 * Enable/Disable rel extnodes.
	 * @param bool[optional] $b
	 * @return bool FALSE on failure
	 */
	function enableRelExtnodes( $b = true )
	{
		return $this->update('netblog_extnodes_use', $b);
	}
	
	/**
	 * Enable/Disable all database tables and netblog features
	 * @param bool $b
	 */
	function enableAll( $b = true )
	{
		$this->enableLocalNet($b);
		$this->enableExtNet($b);
		$this->enableRelExtnodes($b);
		$this->enableFootprints($b);
		$this->enableGlobalCaptions($b);
		
		if($b) Netblog::log("Enabled all Netblog features.");
		else Netblog::log("Disabled all Netblog features.");
	}
	
	/**
	 * Check if to use code highlighting
	 *
	 * @return bool
	 */
	function useCode()
	{
		return $this->fetch('netblog_code_use',true);
	}
	
	/**
	 * Get plugin shortcode name for code highlighting
	 *
	 * @return string
	 */
	function getCodeShortcode()
	{
		return $this->fetch('netblog_ss_code','nbcode');
	}
	
	/**
	 * Get read privilege to mel
	 *
	 * @return string
	 */
	function getMelPread()
	{
		return $this->fetch('netblog_mel_read','edit_posts');
	}
	
	/**
	 * Set mel read privilege
	 *
	 * @param string $right
	 * @return bool
	 */
	function setMelPread( $right )
	{
		return $this->update('netblog_mel_read',$right);
	}
	
	/**
	 * Get edit privilege to mel
	 *
	 * @return string
	 */
	function getMelPedit()
	{
		return $this->fetch('netblog_mel_edit','delete_pages');
	}
	
	/**
	 * Set mel edit privilege
	 *
	 * @param string $right
	 * @return bool
	 */
	function setMelPedit( $right )
	{
		return $this->update('netblog_mel_edit',$right);
	} 
	
	/**
	 * Get mel startup template
	 *
	 * @return string
	 */
	function getMelTplStartup()
	{
		return $this->fetch('netblog_mel_start_tpl','new');
	}
	
	/**
	 * Set mel startup template
	 *
	 * @param string $tplName
	 * @return bool
	 */
	function setMelTplStartup( $tplName )
	{
		return $this->update('netblog_mel_start_tpl',$tplName);
	}
	
	/**
	 * Check if to use mel templates.
	 *
	 * @return bool
	 */
	function useMelTpl()
	{
		return $this->fetch('netblog_mel_tpl',true);
	}
	
	function enableMelTpl( $b )
	{
		return $this->update('netblog_mel_tpl',$b);
	}
	
	/**
	 * Check if to use mel.
	 *
	 * @return bool
	 */
	function useMel()
	{
		return $this->fetch('netblog_mel',true);
	}
	
	/**
	 * Enable mel
	 *
	 * @param bool $b
	 * @return bool
	 */
	function enableMel( $b )
	{
		return $this->update('netblog_mel',$b);
	}
	
	/**
	 * Get mel autosave savetime interval in seconds.
	 *
	 * @return int
	 */
	function getMelSavetime()
	{
		$t = $this->fetch('netblog_mel_save',20);
		if( is_int($t) ) return $t;
		
		$t = (int) $t;
		return $t>0 ? $t : 20;
	}
	
	/**
	 * Set mel savetime interval
	 *
	 * @param mixed $time
	 * @return bool
	 */
	function setMelSavetime( $time )
	{
		return $this->update('netblog_mel_save',max( Intval($time), 0));
	}
	
	/**
	 * Get custom mel templates
	 * @return array[name=>value]
	 */
	function getMelUserTpls()
	{
		return $this->fetch('netblog_mel_usertpl', array() );
	}
	
	/**
	 * Set new custom mel templates
	 * @param array[name=>value] $tpls
	 * @return bool
	 */
	function setMelUserTpls( $tpls )
	{
		return $this->update('netblog_mel_usertpl', $tpls);
	}
	
	/**
	 * Add custom mel template
	 *
	 * @param string $name
	 * @param string $value
	 * @return bool
	 */
	function addMelUserTpl( $name, $value ) {
		if(strlen($name) == 0 || strlen($value) == 0)
			return false;
		$t = $this->getMelUserTpls();
		if(!isset($t[$name])) {
			$t[$name] = $value;
			return $this->setMelUserTpls($t);			
		} else return false;
	}
	
	
	/**
	 * Remove custom mel template
	 *
	 * @param string $name
	 * @return bool
	 */
	function rmMelUserTpl( $name ) {
		$t = $this->getMelUserTpls();
		if(strlen($name) == 0 || !isset($t[$name])) return false;
		unset($t[$name]);
		return $this->setMelUserTpls($t);
	}
		
	/**
	 * Get type of event when to build EED
	 *
	 * @return unknown
	 */
	function getExportBuildon()
	{
		return $this->fetch('netblog_export_build','save_post');
	}
	
	/**
	 * Check if to use EED.
	 *
	 * @return bool
	 */
	function useEED()
	{
		return $this->fetch('netblog_eed',false);
	}
	
	/**
	 * Enable/Disable EED
	 *
	 * @param bool $b
	 * @return bool
	 */
	function enableEED($b=true) 
	{
		return $this->update('netblog_eed', $b );
	}

	/**
	 * Check if to use EED automatic rebuild to keep WP article up to date
	 *
	 * @return bool
	 */
	function useEEDAutoRebuild()
	{
		return $this->fetch('netblog_eed_autorebuild',false);
	}
	
	/**
	 * Enable/Disable EED automatic rebuild
	 *
	 * @param bool $b
	 * @return bool
	 */
	function enableEEDAutoRebuild($b=true) 
	{
		return $this->update('netblog_eed_autorebuild', $b );
	}
	
	/**
	 * Check if to use footprints
	 *
	 * @return bool
	 */
	function useFootprints()
	{
         return false;
		//return $this->fetch('netblog_footprints',true);
	}
	
	/**
	 * Set whether to enable footprints
	 * @param bool[optional] $b
	 * @return bool FALSE on failure
	 */
	function enableFootprints( $b = true )
	{
		return $this->update('netblog_footprints', false );
	}
	
	/**
	 * Check if to use footprint server
	 * Localhost may not use the footprint server!
	 *
	 * @return bool
	 */
	function useFootprintServer()
	{
         return false;
		return isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] != 'localhost' ? $this->fetch('netblog_footprint_server',false) : false;
	}
	
	/**
	 * Enable/Disable use of footprint server
	 *
	 * @param bool $b
	 * @return bool
	 */
	function enableFootprintServerUse( $b = true )
	{
		return $this->update('netblog_footprint_server',false);
	}
	
	/**
	 * Check if to use netblog's sidebar.
	 *
	 * @return bool
	 */
	function useSidebar()
	{
		return $this->fetch('netblog_sidebar','false');
	}
	
	/**
	 * Enable Sidebar
	 *
	 * @param bool $b
	 * @return bool
	 */
	function enableSidebar( $b = true)
	{
		return $this->update('netblog_sidebar',$b);
	}
	
	/**
	 * Check if to use widget outnodes (further reading)
	 *
	 * @return bool
	 */
	function useWidgetOutnodes()
	{
		return $this->fetch('netblog_widget_outnodes',true);
	}
	
	/**
	 * Enable Widget Outnodes
	 *
	 * @param bool $b
	 * @return bool
	 */
	function enableWidgetOutnodes( $b = true)
	{
		return $this->update('netblog_widget_outnodes',$b);
	}
	
	/**
	 * Check if to use widget innodes (referenced by)
	 *
	 * @return bool
	 */
	function useWidgetInnodes()
	{
		return $this->fetch('netblog_widget_innodes',true);
	}
	
	/**
	 * Enable Widget Innodes
	 *
	 * @param bool $b
	 * @return bool
	 */
	function enableWidgetInnodes( $b = true)
	{
		return $this->update('netblog_widget_innodes',$b);
	}
	
	/**
	 * Check if to use the wizard for outnodes (further reading)
	 *
	 * @return unknown
	 */
	function useWizardOutnodes()
	{
		return $this->fetch('netblog_wzd_furead_use',true);
	}
	
	/**
	 * Enable Wizard Further Reading
	 *
	 * @param bool $b
	 * @return bool
	 */
	function enableWizardOutnodes( $b = true)
	{
		return $this->update('netblog_wzd_furead_use',$b);
	}
	
	/**
	 * Check if to use the wizard reference maker.
	 *
	 * @return bool
	 */
	function useWizardRefmaker()
	{
		return $this->fetch('netblog_wzd_refmaker_use',true);
	}	
	
	/**
	 * Enable Wizard Reference Maker
	 *
	 * @param bool $b
	 * @return bool
	 */
	function enableWizardRefmaker( $b = true)
	{
		return $this->update('netblog_wzd_refmaker_use',$b);
	}
	
	/**
	 * Get installed server version
	 * 
	 * @return string
	 */
	function getServerVersion()
	{
		return $this->fetch( 'netblog_server_ver', '0');
	}
	
	/**
	 * Set the installed server server
	 *
	 * @param string $ver
	 * @return bool
	 */
	function setServerVersion($ver) {
		return $this->update( 'netblog_server_ver', $ver);
	}
	
	/**
	 * Get server table name for local net - further reading, referenced by
	 */
	function getServerTableNet()
	{
		return $this->fetch( 'netblog_db_net', $this->dbNetName );
	}
	
	/**
	 * Get server table version for local net
	 */
	function getServerTableVerNet()
	{
		return $this->fetch( 'netblog_db_net_ver', '0' );
	}
	
	/**
	 * Set new server table version for local net
	 * @param string $version
	 * @return bool FALSE on failure
	 */
	function setServerTblVerNet( $version )
	{
		return $this->update('netblog_db_net_ver', $version );
	}

	/**
	 * Get current server table version for local net, distributed with this release.
	 */
	function getServerCTBVNet() { return $this->dbNetVer; }
	
	/**
	 * Get server table name for external resources - external further reading
	 */
	function getServerTableExt()
	{
		return $this->fetch( 'netblog_db_ext', $this->dbExtName );
	}
	
	/**
	 * Get server table version for external resources
	 */
	function getServerTableVerExt()
	{
		return $this->fetch( 'netblog_db_ext_ver', '0' );
	}
	
	/**
	 * Set new server table version for external resources.
	 * @param string $version
	 * @return bool FALSE on failure.
	 */
	function setServerTblVerExt( $version ) 
	{
		return $this->update('netblog_db_ext_ver', $version );
	}
	
	/**
	 * Get current server table version for external resources, distributed with this release.
	 * @return string.
	 */
	function getServerCTBVExt() { return $this->dbExtVer; }
	
	/**
	 * Get server table name for relationships between external and local resources.
	 */
	function getServerTableRelExtnodes()
	{
		return $this->fetch( 'netblog_db_rel_extnode', $this->dbRelExtnodeName );
	}
	
	/**
	 * Get server table version for relationships between ext und int resources
	 */
	function getServerTableVerRelExtnodes()
	{
		return $this->fetch( 'netblog_db_rel_extnode_ver', '0' );
	}
	
	/**
	 * Get server table name for testpilot
	 *
	 * @return string
	 */
	function getServerTableTestPilot() 
	{
		return $this->fetch( 'netblog_db_testpilot', $this->dbTestpilotName );
	}
	
	/**
	 * Get version of server table testpilot
	 *
	 * @return string
	 */
	function getServerTableVerTestPilot() 
	{
		return $this->fetch( 'netblog_db_testpilot_ver', '0' );
	}
	
	/**
	 * Set the current server table version for the test pilot
	 *
	 * @param string $version
	 * @return bool
	 */
	function setServerTblVerTestPilot( $version ) {
		return $this->update('netblog_db_testpilot_ver', $version );
	}
	
	/**
	 * Get server table name for bibliographic item
	 *
	 * @return string
	 */
	function getServerTableBibitem() 
	{
		return $this->fetch( 'netblog_db_bibitem', $this->dbBibitemName );
	}
	
	/**
	 * Get version of server table bibliographic item
	 *
	 * @return string
	 */
	function getServerTableVerBibitem() 
	{
		return $this->fetch( 'netblog_db_bibitem_ver', '0' );
	}
	/**
	 * Set the current server table version for bibliographic item
	 *
	 * @param string $version
	 * @return bool
	 */
	function setServerTblVerBibitem( $version ) {
		return $this->update('netblog_db_bibitem_ver', $version );
	}	

	/**
	 * Get server table name for bibliographic reference
	 *
	 * @return string
	 */
	function getServerTableBibReference() 
	{
		return $this->fetch( 'netblog_db_bibrefs', $this->dbBibRefsName );
	}
	
	/**
	 * Get version of server table bibliographic reference
	 *
	 * @return string
	 */
	function getServerTableVerBibReference() 
	{
		return $this->fetch( 'netblog_db_bibrefs_ver', '0' );
	}
	
	/**
	 * Set the current server table version for bibliographic reference
	 *
	 * @param string $version
	 * @return bool
	 */
	function setServerTblVerBibReference( $version ) {
		return $this->update('netblog_db_bibrefs_ver', $version );
	}

	/**
	 * Get server table name for bibliographic reference relation
	 *
	 * @return string
	 */
	function getServerTableBibReferenceRel() 
	{
		return $this->fetch( 'netblog_db_bibrefs_rel', $this->dbBibRefsRelName );
	}
	
	/**
	 * Get version of server table bibliographic reference relation
	 *
	 * @return string
	 */
	function getServerTableVerBibReferenceRel() 
	{
		return $this->fetch( 'netblog_db_bibrefs_rel_ver', '0' );
	}
	
	/**
	 * Set the current server table version for bibliographic reference relation
	 *
	 * @param string $version
	 * @return bool
	 */
	function setServerTblVerBibReferenceRel( $version ) {
		return $this->update('netblog_db_bibrefs_rel_ver', $version );
	}
		
	/**
	 * Set new server table version for rels betw external and internal resources. 
	 * @param string $version
	 * @return bool
	 */
	function setServerTblVerRelExtnodes( $version )
	{
		return $this->update('netblog_db_rel_extnode_ver', $version );
	}
	
	/**
	 * Get current server table version for rels of ext and internal resources, distributed with this release.
	 */
	function getServerCTBVRelExtnodes() { return $this->dbRelExtnodeVer; }
	
	/**
	 * Get current server table version for testpilot
	 *
	 * @return string
	 */
	function getServerCTBVTestPilot() { return $this->dbTestpilotVer; }
	
	/**
	 * Get current server table version for bibliographic item
	 *
	 * @return string
	 */
	function getServerCTBVBibItem() { return $this->dbBibitemVer; }
	
	/**
	 * Get current server table version for bibliographic reference
	 *
	 * @return string
	 */
	function getServerCTBVBibReference() { return $this->dbBibRefsVer; }

	/**
	 * Get current server table version for bibliographic reference relation
	 *
	 * @return string
	 */
	function getServerCTBVBibReferenceRel() { return $this->dbBibRefsRelVer; }
	
	/**
	 * Get server table name for footprints
	 */
	function getServerTableFootprints()
	{
		return $this->fetch( 'netblog_db_footprint', $this->dbFootprintName );
	}
	
	/**
	 * Get server table version for footprints.
	 */
	function getServerTableVerFootprints()
	{
		return $this->fetch( 'netblog_db_footprint_ver', '0' );
	}
	
	/**
	 * Get current server table version for footprints, distributed with this release.
	 */
	function getServerCTBVFootprints() { return $this->dbFootprintVer; }
	
	/**
	 * Get server table name for captions
	 */
	function getServerTableCaptions()
	{
		return $this->fetch( 'netblog_db_caption', $this->dbCaptionName );
	}
	
	/**
	 * Get server table version for captions.
	 */
	function getServerTableVerCaptions()
	{
		return $this->fetch( 'netblog_db_caption_ver', '0' );
	}
	
	/**
	 * Get current server table version for captions, distributed with this release.
	 */
	function getServerCTBVCaptions() { return $this->dbCaptionVer; }
	
	/**
	 * Set new server table name for captions.
	 * @param string $name
	 */
	function setServerTblCaptions( $name )
	{
		return $this->update('netblog_db_caption', $name);
	}
	
	/**
	 * Set new server table version for captions.
	 * @param string $number
	 * @return bool
	 */
	function setServerTblVerCaptions( $number )
	{
		return $this->update('netblog_db_caption_ver', $number);
	}
	
	/**
	 * Set new server table name for footprints.
	 * @param string $name
	 */
	function setServerTblFootprints( $name )
	{
		return $this->update('netblog_db_footprint', $name);
	}
	
	/**
	 * Set new server table version for footprints.
	 * @param string $number
	 * @return bool
	 */
	function setServerTblVerFootprints( $number )
	{
		return $this->update('netblog_db_footprint_ver', $number);
	}

	
	/**
	 * Get installed client version
	 * 
	 * @return string
	 */
	function getClientVersion()
	{
		return $this->fetch( 'netblog_client_ver', '0' );
	}
	
	/**
	 * Set the client version number
	 *
	 * @param string $vers
	 * @return bool
	 */
	function setClientVersion($vers) {
		return $this->update( 'netblog_client_ver', $vers);
	}
	
	/**
	 * 
	 * Get active decoded blogsearch uri
	 * @param string $query
	 * @param int $count
	 * @return string
	 */
	function getBlogsearchUri( $query = '', $count = 10 )
	{
		$uri = $this->fetch( 'netblog_blogsearch_uri', $this->blogsearchUri );
		if( $query!= '') {
			$uri = str_ireplace('??query??', $query, $uri);
			$uri = str_ireplace('??count??', $count, $uri);
		}
		return $uri;
	}
	
	/**
	 * Set active blogsearch uri
	 *
	 * @param string $queryRaw
	 * @return bool
	 */
	function setBlogsearchUri( $queryRaw ) {
		return $this->update('netblog_blogsearch_uri',$queryRaw);
	}
	
	/**
	 * Get active blogsearch name.
	 * @return string
	 */
	function getBlogsearchName()
	{
		return $this->fetch( 'netblog_blogsearch_name', 'Blogsearch' );
	}
	
	/**
	 * Set active blogsearch name
	 *
	 * @param string $name
	 * @return bool
	 */
	function setBlogsearchName( $name )
	{
		return $this->update( 'netblog_blogsearch_name', $name );
	}
	
	/**
	 * Get maximum number of results of a blogsearch
	 */
	function getBlogsearchMaxResults()
	{
		return $this->fetch( 'netblog_blogsearch_max', $this->blogsearchMaxResults );
	}
	
	/**
	 * Set active blogsearch max results
	 *
	 * @param int|string $num
	 * @return bool
	 */
	function setBlogsearchMaxResults( $num )
	{
		return $this->update( 'netblog_blogsearch_max', max(Intval($num),-1) );
	}

	/**
	 * Get api key for access to blog search
	 *
	 * @return string
	 */
	function getBlogsearchAPIKey()
	{
		return $this->fetch( 'netblog_blogsearch_apikey', 'none' );
	}
	
	/**
	 * Update api key for blogsearch
	 *
	 * @param string $key
	 * @return bool
	 */
	function setBlogsearchAPIKey( $key )
	{
		return $this->update( 'netblog_blogsearch_apikey', strlen($key)==0 ? 'none': $key );
	}
	
	/**
	 * Get provider name of active blogsearch
	 */
	function getBlogsearchProviderName()
	{
		return $this->fetch( 'netblog_blogsearch_provider_name', $this->blogsearchProviderName );
	}
	
	/**
	 * Set provider name of active blogsearch
	 *
	 * @param string $name
	 * @return bool
	 */
	function setBlogsearchProviderName( $name ) {
		return $this->update( 'netblog_blogsearch_provider_name', $name );
	}
	
	/**
	 * Get provider uri of current blogsearch
	 */
	function getBlogsearchProviderUri()
	{
		return $this->fetch( 'netblog_blogsearch_provider_uri', $this->blogsearchProviderUri );
	}
	
	/**
	 * Set active blogsearch provider uri
	 *
	 * @param string $uri
	 * @return bool
	 */
	function setBlogsearchProviderUri( $uri )	{
		return $this->update( 'netblog_blogsearch_provider_uri', $uri );
	}
	
	/**
	 * 
	 * Get active decoded websearch uri
	 * @param string $query
	 * @param int $count
	 * @return string
	 */
	function getWebsearchUri( $query = '', $count = 10 )
	{
		$uri = $this->fetch( 'netblog_websearch_uri', $this->websearchUri );
		if( $query != '' ) {
			$uri = str_ireplace('??query??', $query, $uri);
			$uri = str_ireplace('??count??', $count, $uri);
		}
		return $uri;
	}
	
	/**
	 * Set active websearch uri
	 *
	 * @param string $uriRaw
	 * @return bool
	 */
	function setWebsearchUri( $uriRaw )
	{
		return $this->update( 'netblog_websearch_uri', $uriRaw);
	}
	
	/**
	 * Get active websearch name.
	 * @return string
	 */
	function getWebsearchName()
	{
		return $this->fetch( 'netblog_websearch_name', 'Default Websearch' );
	}
	
	/**
	 * Set active websearch name
	 *
	 * @param string $name
	 * @return bool
	 */
	function setWebsearchName( $name )
	{
		return $this->update( 'netblog_websearch_name', $name );
	}
	
	/**
	 * Get maximum number of results of a websearch
	 */
	function getWebsearchMaxResults()
	{
		return $this->fetch( 'netblog_websearch_max', $this->websearchMaxResults );
	}
	
	/**
	 * Set active websearch max results
	 *
	 * @param int|string $num
	 * @return bool
	 */
	function setWebsearchMaxResults( $num )
	{
		return $this->update( 'netblog_websearch_max', max(Intval($num),-1) );
	}
	
	/**
	 * Get api key for access to websearch
	 *
	 * @return string
	 */
	function getWebsearchAPIKey()
	{
		return $this->fetch( 'netblog_websearch_apikey', 'none' );
	}
	
	/**
	 * Update api key for websearch
	 *
	 * @param string $key
	 * @return bool
	 */
	function setWebsearchAPIKey( $key )
	{
		return $this->update( 'netblog_websearch_apikey', strlen($key)==0 ? 'none': $key );
	}
	
	/**
	 * Get provider name of active websearch
	 */
	function getWebsearchProviderName()
	{
		return $this->fetch( 'netblog_websearch_provider_name', $this->websearchProviderName );
	}
	
	/**
	 * Set active websearch provider name
	 *
	 * @param string $name
	 * @return name
	 */
	function setWebsearchProviderName( $name )
	{
		return $this->update( 'netblog_websearch_provider_name', $name );
	}
	
	/**
	 * Get provider uri of current websearch
	 */
	function getWebsearchProviderUri()
	{
		return $this->fetch( 'netblog_websearch_provider_uri', $this->websearchProviderUri );
	}
	
	/**
	 * Set active websearch provider uri
	 *
	 * @param string $uri
	 * @return bool
	 */
	function setWebsearchProviderUri( $uri )
	{
		return $this->update( 'netblog_websearch_provider_uri', $uri );
	}
	
	/**
	 * Get gui autocomplete minimum length
	 * @return int
	 */
	function getGUIAutocompleteMinLen()
	{
		return (int)$this->fetch( 'netblog_autocomplete_minlen', $this->guiAutocompleteMinlen );
	}
	
	
	/**
	 * Set GUI autocomplete min length
	 *
	 * @param unknown_type $len
	 * @return unknown
	 */
	function setGUIAutocompleteMinLen( $len )
	{
		return $this->update( 'netblog_autocomplete_minlen', max(Intval($len),2) );
	}
	
	/**
	 * Get ajax main delimiter - on first level
	 */
	function getAjaxDelimiterMain() { return $this->ajaxDelimiterKeyMain; }
	
	/**
	 * Get ajax sub delimiter - on second level
	 */
	function getAjaxDelimiterSub() { return $this->ajaxDelimiterKeySub; }
	
	
	/**
	 * Whether submit of private data like blog url or blog plugins/themes is allowed
	 *
	 * @return bool
	 */
	function getPrivacyLevel() {
		return $this->fetch( 'netblog_privacy_level', 'medium' );
	}
	
	/**
	 * Set new privacy level for submit
	 *
	 * @param string $level
	 * @return bool
	 */
	function setPrivacyLevel( $level ) {
		return $this->update( 'netblog_privacy_level', $level );
	}
	
	/**
	 * If to use test pilot features and help the developers.
	 *
	 * @return bool
	 */
	function useTestPilot() {
		return $this->fetch( 'netblog_test_pilot', true );
	}
	
	/**
	 * Enable/Disable test pilot feature
	 *
	 * @param bool $b
	 * @return bool
	 */
	function enableTestPilot($b=true) {
		return $this->update( 'netblog_test_pilot', $b );
	}
	
	/**
	 * Set storage for test pilot
	 *
	 * @param mixed $storage
	 * @return bool
	 */
	function setTestPilotStorage($storage) {
		return $this->update( 'netblog_test_pilot_storage', $storage);
	}
	
	/**
	 * Get storage for test pilot; do not use directly, use nbTestPilot instead.
	 *
	 * @return bool
	 */
	function getTestPilotStorage() {
		return $this->fetch( 'netblog_test_pilot_storage', null );
	}
	
	/**
	 * Get storage for export settings, used for scheduled exports.
	 *
	 * @return mixed
	 */
	function getExportSettingsStorage() {
		return $this->fetch( 'netblog_export_settings_storage', null );
	}
	
	/**
	 * Set storage for export settings; do not use this method directly!
	 *
	 * @param mixed $storage
	 */
	function setExportSettingsStorage( $storage ) {
		return $this->update( 'netblog_export_settings_storage', $storage );
	}
	
	/**
	 * Get next scheduler activity time
	 *
	 * @return timestamp
	 */
	function getExportSchedulerNextTime() {
		return $this->fetch( 'netblog_export_scheduler_nexttime', 0 );
	}
	
	/**
	 * Set next scheduler activity time
	 *
	 * @param timestamp $time
	 * @return bool
	 */
	function setExportSchedulerNextTime( $time ) {
		return $this->update( 'netblog_export_scheduler_nexttime', $time );
	}
	
	/**
	 * Remove all options
	 * 
	 * @return bool
	 */
	function removeAll()
	{
		//nbdb::removeOptions();
		foreach( self::$options as $o )
			delete_option($o);
		return true;
	}
	
	/**
	 * Get list of all options.
	 *
	 * @return array Array with [option_name]=>[option_value]
	 */
	function getAll() {
		$o = array();
		foreach($this->options as $nm)
			$o[$nm] = $this->fetch($nm,'null');
		return $o;
	}
	
	
	/**
	 * Fetch plugin options
	 *
	 * @param string $optionName
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	private function fetch( $optionName, $defaultValue )
	{
		if( isset($this->fetchedOptions[$optionName]) ) 
			return $this->str2bool($this->fetchedOptions[$optionName]);
		if( ($o=get_option($optionName)) !== false || $o != null ) {				// not yet fetched, but exists
			$this->fetchedOptions[$optionName]=$o;
			return $this->str2bool($o);
		}
		if( !add_option($optionName,$defaultValue) )								// First-Use Installation (automatic)
			Netblog::log("FUI: Failed to create option '$optionName'");
		else {
			Netblog::log("FUI: created new option '$optionName'");
		}
		$this->fetchedOptions[$optionName]=$defaultValue;
		return $this->str2bool($defaultValue);
	}
	
	private function str2bool($val) {
		if(!is_string($val)) 
			return $val;
		return $val=='true'?true:($val=='false'?false:$val);
	}
	
	private function bool2str($val) {
		return !is_bool($var)?$val:($val?'true':'false');
	}
	
	
	/**
	 * Update plugin option
	 *
	 * @param string $optionName
	 * @param mixed $newValue
	 * @return bool
	 */
	private function update( $optionName, $newValue )
	{
		$val = $this->bool2str($newValue);
		if( isset($this->fetchedOptions[$optionName]) && $this->fetchedOptions[$optionName] === $val )
			return true;
		if( update_option($optionName,$val) ) {
			$this->fetchedOptions[$optionName] = $val;
			return true;
		} else if( add_option($optionName,$val) ) {			
			Netblog::log("Failed to set option '$optionName' (".(is_string($newValue) ? $newValue : serialize($newValue)).")");
			return false;
		}
		return true;
	}
	
		
	/**
	 * Remove plugin option
	 *
	 * @param string $optionName
	 * @return bool
	 */
	private function remove( $optionName )
	{
		if( !delete_option($optionName) && get_option($optionName) !== false ) {
			Netblog::log("Failed to remove option '$optionName'");
			return false;
		} return true;
	}
	
	
	private $fetchedOptions = array();
	
	private $citeStyleHeadline = 'References';
	
	private $blogsearchUri = 'http://blogsearch.google.com/blogsearch_feeds?scoring=d&ie=utf-8&num=??count??&output=rss&partner=wordpress&q=??query??';
	private $blogsearchMaxResults = 10;
	private $blogsearchProviderName = 'Google Blogsearch';
	private $blogsearchProviderUri = 'http://blogsearch.google.com';
	
	private $websearchUri = 'http://search.yahooapis.com/WebSearchService/rss/webSearch.xml?appid=yahoosearchwebrss&query=??query??';
	private $websearchMaxResults = 10;
	private $websearchProviderName = 'Websearch by Yahoo.com';
	private $websearchProviderUri = 'http://www.yahooapis.com';
	
	private $guiAutocompleteMinlen = 3;
	
	private $ajaxDelimiterKeyMain = 'YZ{OF3@Y=>z]t`Yu6vF2 h)0D!&j4V+>zv=t3r#+AFK#XKDjJ.pog&f-_~h$pvrJ';
	private $ajaxDelimiterKeySub = 'mI).DX)ss5]x|yiJ.n<66`N7]3OZoS2?C-84[k~9?WaL$IarVjf-6F>;]!HKAoog';
	
	private $dbNetName = 'netblog';
	private $dbNetVer = '1.0';
	private $dbExtName = 'netblog_ext';
	private $dbExtVer = '1.2';
	private $dbRelExtnodeName = 'netblog_rel_extnd';
	private $dbRelExtnodeVer = '1.1';
	private $dbCaptionName = 'netblog_caption';
	private $dbCaptionVer = '1.1';
	private $dbHostTreeName = 'netblog_host_tree';
	private $dbHostTreeVer = '1.0';
	private $dbFootprintName = 'netblog_footprint';	
	private $dbFootprintVer = '1.0';
	private $dbTestpilotName = 'netblog_testpilot';
	private $dbTestpilotVer = '1.0';
	private $dbBibitemName = 'netblog_bibitem';
	private $dbBibitemVer = '1.0';
	private $dbBibRefsName = 'netblog_bibrefs';
	private $dbBibRefsVer = '1.1';
	private $dbBibRefsRelName = 'netblog_bibrefs_rel_items';
	private $dbBibRefsRelVer = '1.0';
		
	private $options = array('netblog_cite_style','netblog_cite_style_override','netblog_cite_style_override',
						'netblog_ss_citation','netblog_cite_style_headline','netblog_bib_maxnum','netblog_bib_print',
						'netblog_note_format','netblog_note_print','netblog_ss_footnote','netblog_caption_gadd',
						'netblog_ss_caption','netblog_caption_useGlobal','netblog_net_use',
						'netblog_ext_use','netblog_extnodes_use','netblog_code_use','netblog_ss_code',
						'netblog_mel_read','netblog_mel_edit','netblog_mel_start_tpl','netblog_mel_tpl','netblog_mel','netblog_mel_save',
						'netblog_export_build','netblog_footprints','netblog_footprints','netblog_sidebar','netblog_widget_outnodes',
						'netblog_widget_innodes','netblog_wzd_furead_use','netblog_wzd_refmaker_use','netblog_server_ver',
						'netblog_db_net','netblog_db_net_ver','netblog_db_net_ver','netblog_db_ext','netblog_db_ext_ver','netblog_db_ext_ver',
						'netblog_db_rel_extnode','netblog_db_rel_extnode_ver','netblog_db_rel_extnode_ver','netblog_db_footprint','netblog_db_footprint_ver',
						'netblog_db_caption','netblog_db_caption_ver','netblog_db_caption','netblog_db_caption_ver','netblog_db_footprint',
						'netblog_db_footprint_ver','netblog_client_ver','netblog_blogsearch_uri','netblog_blogsearch_name','netblog_blogsearch_max',
						'netblog_blogsearch_provider_name','netblog_blogsearch_provider_uri','netblog_websearch_uri','netblog_websearch_name',
						'netblog_websearch_max','netblog_websearch_provider_name','netblog_websearch_provider_uri','netblog_autocomplete_minlen',
						'netblog_websearch_apikey','netblog_blogsearch_apikey','netblog_gui_speed','netblog_captionsg_list','netblog_mel_usertpl',
						'netblog_eed','netblog_privacy_level','netblog_test_pilot','netblog_export_settings_storage','netblog_db_testpilot');
}

?>