=== Plugin Name ===
Contributors: BenjaminSommer
Donate link: http://weblog.benjaminsommer.com/projects/academic-press/
Tags: plugin, navigation, external links, linking, links, network, academic, academia, further reading, references, blogsearch, pingbacks, bibliography, citation, caption, footnotes, MLA, APA, Chicago, Turabian, Harvard, Export, Import
Requires at least: 2.8
Tested up to: 3.4.2
Stable tag: 2.22

Connect posts and external resources (websites, pdf, doc, data). Use Captions, Footnotes, Bibliography. Netblog is highly customizable.

== Description ==

`Management of Bibliographies and Footnotes will be removed soon. Please use the plugin [AcademicPress](http:// "AcademicPress - Next Generation of Academic Publishing") instead, as it supports many more features.`

Netblog is a tool to connect posts, pages and external resources (websites, pdf, doc, zip, exe,...) and display them as Further Reading and References for your visitor.
Ideal to publish current academic work of a workgroup, to create a knowledge-based website to refer to additional interesting websites, to link to documents in the internet, 
or just to connect content-related posts, or whatever you want to refer to. 

Use Netblog to easily create and manage bibliographic references to many medias (like books, websites, videos, journals, magazines, newspapers etc)

**Next Milestone - Netblog 3.0**

* Design and implementation of SDL - a portable style definition language
* Parsers for BibTex and EndNote
* Connect to bibliography databases and query for attributes like author, title, date, publisher, ISBN, DOI etc.
* Compliant to Zend's Coding Standard (naming, documentation,...)
* Lightweight software
* Available as WP plugin or as standalone browser based application
* No custom database tables - all information will be stored in posts (shortcodes) or websites
* Display mathematical formulas and manage preprocessors
* Interactive UI using AJAX and jQuery
* Build table of reference either statically or dynamically. Static: manually define references (in case of inline citations). Dynamic: 
automatically parse external data from BibTex, EndNote or databases. A lot of more attributes are defined for advanced control and properties.
* Easy to use
* Complete redesign (easy to extend by the community)

**[See more information about the next milestone](http://weblog.benjaminsommer.com/projects/academic-press/ "Information about the next milestone").**


**Using** it is quite simple. Go to edit-post / edit-page and look for metabox `Further Reading`. Type in your query and a list of matches is shown below using autocomplete.
Click with your mouse on one of the rows and the autocomplete box disappears. The link fades in smoothly below. 


**Features**

* `Bibliography`/References: list cited work within your text automatically at the bottom of the page and adhere to strict citation styles.
* Build-in citation styles: `APA, MLA, Chicago, Turabian, Harvard`. Add custom styles if needed.
* Easy creation of `custom, professional reference styles`.
* `Reference Maker` interactively helps to create citations, captions, footnotes, table of footnotes, table of bibliography and cross-referencing captions. 
* `Settings Page`: adjust global settings and manage security options.
* Using AJAX to add links immediately to the post/page currently being edited, without reloading.
* Two integrated widgets: `Referenced By` and `Further Reading` to display the links in the sidebar for the visitor.
* Incoming links can be displayed, using `Blogsearch` technologies.
* `Pingbacks` as remote comments can be automatically displayed in the widget References (see settings).
* `Manage External Resources`: list and search links, trash and erase them, check online status, automatically update their titles, perform batch operations
* `Localisation Enabled`: current languages are English and German (incomplete). Please help with the localisation!

**Tip**

* Use `Reference Maker` to make your life easier.
* Metabox `Bibliography` helps you to create correct bibliographic references
* Check and Update external links regularly in NB-MEL, so that dead link are not listed for your visitor.
* Take a look at the settings, whether to list footnotes as decimals, alphas, roman or greek letters, for example.
* Move the metabox Further Reading below your favorite post editor to see the autocomplete box better. 
* Use quick find in NB-MEL to execute commands (read-only at the moment). Use this syntax: [match words] [sort:id|title|refs|flag[-desc]] [flag:offline|trash|lock] [limit:integer]



Supported Web browers: Google Chrome 10+, Microsoft Internet Explorer 6+, Firefox 3+

More Screenshots at netblog.benjaminsommer.com.


== Installation ==

= Software Requirements =
1. PHP 5.x (required libraries: libxml; recommended: libmcrypt 2.5.6+)
1. MYSQLi 5.x
1. WP 2.8+

= Fresh Install =
1. Backup your WP database.
1. Download and install the latest version of Netblog from within WP admin panel

OR

1. Backup your WP database.
1. Download the latest version of Netblog from the official Netblog Server
1. Extract the archive to your Wordpress Plugins directory `/wp-content/plugins/`
1. `Activate` or `Network Activate` the plugin through the Plugins menu in WordPress.

= Update =
1. Backup your WP database.
1. Update Netblog directly from within the WP admin panel

OR

1. Make sure to `Deactivate` Netblog for all WP sites before copying the new files!
1. Download new version files to your previous installation
1. Make a `Backup` of your Wordpress database
1. `Activate` or `Network Activate` the plugin.

= Notice =
Use of this plugin assumes acceptance of Netblog's license and its Terms of Use, to be found in license.txt. 

== Frequently Asked Questions ==

= Netblog disrupts widgets and sidebars =
In case Netblog's integrated dynamic sidebar, which is used to display widgets like Further Reading or Referenced By below a WP article, 
interferes with one of your existing plugins, try to disable Netblog sidebar by going to Settings -> Netblog -> General and then under 'Advanced'
make sure the option 'Activate Netblog sidebar to display widgets...' is unchecked.

= How to disable export functionality? =

Go to Settings > Netblog. In the `General` tab, under `Export & Import`, 
1. uncheck the option `Automatically rebuild EED while saving a post/page` and
2. click the button `Remove EED`.

= How do I create a bibliography? =
This question has been solved in [Forum Post](http://wordpress.org/support/topic/plugin-netblog-how-do-i-create-a-bibliography?replies=2). To sum up, either
use the built-in Reference Maker (choose the wizard: Tables -> Bibliography, and click on Copy&Pase and paste the generated content into your editor), 
or use the following code syntax: 

`[nbcite print_headline="My References" print="apa" ]`

= How to search for external links? = 

In NB-MEL, use quick find and type something following this syntax: `[match words] [sort:id|title|refs|flag[-desc]] [flag:offline|trash|lock] [limit:integer]`. 
For example: wordpress sort:refs-desc,title limit:25 flag:lock

= How to add custom search templates? =

Go to Settings > Netblog - if not listed, you have not enough privilege.

= How to reference cited work within one post/page? =

To make it simple and short, in your favorite editor, just copy the nbcite-tag to a second place in your document. In each case, to 
identify a certain citation, your nbcite-tag must include at least author, title, year, month, day and pages (if not empty) - it will refer to
the first use in your document.

= Citation: how to choose a custom, local cite-format? =

Use [nbcite print_custom="$author ($year)" ... ] to display something like: "when you want to cite a book by Daniel Hobsen (2010) in a style that suits best
to your text, this print_custom in your nbcite-tag".

= Caption: can I override a global caption style? =

Yes, you can, although I would not recommend it - a lot of different styles for one caption type within your website is most likely not a good idea. But if you still want to, 
your nbcaption-tag must look like this: [nbcaption local="true" print="($number)" type="lower-greek" ... ]

= How to show the section `Further Reading`? =

Go to `Widgets` and drag the widget `Further Reading` into one of the widget areas. If done so and no links will show up, the maximum number of
visible links of each link type might be 0. The widget must be displayed on at least one of the types: posts, pages or others.

= The public hyperlinks are to too long. What can I do? =

Go to Widgets and lower the value `truncate each hyperlink to [] characters`. 

= How to add an external link? =

Go to `Edit Post` and look for the box `Further Reading`. Type in the field below (`Search for resources`) a certain keyword, like `www` or
like `http://`, and then type your search query, e.g. `www wordpress plugin netblog`. A list of close matches should appear below. If it 
does not appear, try one of the supported web browers.

= I got the message 'Cannot add link' while trying to add a link =

`Deactivate and then activate` the plugin. The Reason: The database tables are not installed properly. This happens as of Wordpress 3.0 with MU enabled, 
when activating the plugin networkwide (link: 'Network Activate'), because Wordpress 3.0 does not run this feature properly at the moment.

= How can I remove a link? =

Go to `Edit Post` and in the box `Further Reading`, click on the `x-icon` on the left side of every link. The link disappears 
apon successful removal.

= Missing Links in Widget `Further Reading`? =

In the widget Further Reading, besides external resources, posts and pages are listed as soon as they are published. All other types, 
such as drafts or trashed items are not displayed. When you restore a trashed item, all its previous links will be restored and displayed for
the public visitor.

= There are no (dynamic) incoming links of the Google Blogsearch! =

Your webpage might be new or not quite popular, so that Google Blogsearch has not yet found a foreign website linking to your current webpage.
Building an index usually takes a bit of time for Google. Don't stop editing and posting.

= In MEL, some invalid links are not marked as offline =

If you are using Wordpress on a server behind your ISP, for example, the reason might be that your ISP returns a custom page whenever a URL is not found.
 

== Screenshots ==

1. **Overview**: That's what you can make out of Netblog - list outgoing and incoming links in your sidebars
2. **List External Links**: And all these links are then displayed on your webpage, like this. Width the two widgets, put them anywhere on your webpage (well, this depends on the theme)
3. **Add External Links**: This simple tool (it's also called metabox) lets you search for external links and quickly add or remove them from the edited post. It uses some great 
search engines like Google or Yahoo (or choose a different one)
4. **Manage External Links**: Because you might get a lot of outgoing links after a while, with this small tool, you can remove, change, lock, unlock, check online availability and automatically update title
of all currently displayed links. Search for links by title, URL, parent post title, number of references and sort them the way you want. And then, just perform the batch operations on them. Done!  
5. **Reference Maker**: Another tiny tool to speed up daily work! Its creates captions and references to them, footnotes, tables of footnotes and bibliographies. Most importantly, it helps you with all this, so you don't have to remember the exact caption name etc.
6. **Table of Bibliographies**: This is actually part of the previously mentioned Reference Maker. Supported citation styles are APA, MLA, Chicago and Turabian (that should suit most users) or custom styles if you want?
7. **Bibliographies and Citations**: Now you don't have to remember how to format a citation corrently anymore! Because this metabox takes care about all this. It even tells you what fields are required. And you can get a preview anytime! By the way, it understands 19 referenced resource types (journal, book,...) with about 40 fields or attributes (author, isbn, year,...)
8. **Welcome Screen**: Stay up to date, get tips and tricks, list recently added references and links and more. 
9. **Settings**: This plugin has quite a few settings, and they are getting more and more. Why? That's simple: to customize this plugin the way you actually need it which optimizes performance. Enabling features, required minimal privileges to acces features or to perform certain operations, etc. Just take a look at it.
10. **Contextual Help**: You should always know what you are doing, especially if you are new to Netblog. For this reason, see the help buttom at the top right corner. Advanced users won't be distracted by already known help - because its usually hidden.
11. **Your Page Might Look Like This Afterwards**

== Changelog ==

= 2.22 =
* Fixed SQL Syntax Errors: when editing posts, captions produced an error as well as when trying to get post infos. This has been fixed.  

= 2.21 =
* Added Support for custom post types: Bibliography, Further Reading and Reference Maker metaboxes can now be used for custom post types.

= 2.20 =
* Fixed Class BibliographyReference: The metabox haven't generated any preview for book sections due to an underscore in BOOK_SECTION. 
* Improved Inline Citations: References to the same media (same reference name / id) but difference sections (e.g. pages) is now supported. The table of bibliography only contains media with unique reference names (media with multiple citations is only listed once).
* New Custom Professional Styles: A new features allows you to create Pro Styles like the built-in styles (APA, MLA etc.) using the menu `Bib Styles`. Now you can create styles based on existing Pro Styles (some knowledge about PHP is required; otherwise read tutorials or ask the developers). 

= 2.19 =
* Fixed Class BibliographyReference: The metabox haven't generated any preview for book sections due to an underscore in BOOK_SECTION. 
* Improved Inline Citations: References to the same media (same reference name / id) but difference sections (e.g. pages) is now supported. The table of bibliography only contains media with unique reference names (media with multiple citations is only listed once).
* New Custom Professional Styles: A new features allows you to create Pro Styles like the built-in styles (APA, MLA etc.) using the menu `Bib Styles`. Now you can create styles based on existing Pro Styles (some knowledge about PHP is required; otherwise read tutorials or ask the developers). 

= 2.18 =
* Fixed Widget `Further Reading`: External links disappeared on some systems.

= 2.17 =
* Fixed Widget `Further Reading`: Internal links and pingbacks to other posts and pages are displayed again.
* Fixed Widget `Referenced By`: Incoming links from other posts/pages within the weblog are displayed again. The widget's description has been changed. 
* Updated Feedback Appearance: The User Interface for the feedback system has been improved. 

= 2.16 =
* Improved DataTransfer: The special handling of CURL URL following has been adopted to function RetrieveUrl() when retrieving another URL with header and content. Before that, this special handling was only used when sending post requests. The function curl_exec_follow() does not use 'Passing by Reference' anymore which caused problems on some systems.
* Improved Welcome Screen: Integrated official forum directly into the welcome screen to increase feedback and to encourage discussions.

= 2.15 =
* Fixed DataTransfer: CURLOPT_FOLLOWLOCATION triggered an error when in safe_mode or an open_basedir is set.
* Improved ReferenceMaker: The metabox is aware of name collisions with other input elements; improved the shortcode generator function by using jQuery.
* Improved Welcome Screen: Netblog's welcome user interface has been updated to reflect recent changes in WordPress default styling; it further contains a feature comparison between Netblog2, Netblog3 and AcademicPress.
* Improved Fetching of URL's Metadata: Function netblog_link_getmetatags() in sshndl_ajax.php now handles websites without proper meta tag information, and tries to extract a website's description by its content.     

= 2.14 =
* Fixed Reference Maker: On some systems, the reference maker disappeared. This problem has been fixed.

= 2.13 =
* Fixed Post Save: updated API of add_meta_box introduced a problem where posts content disappeared after saving them.
* Disabled Option: Footprints to speed up plugin performance

= 2.12 =
* Fixed write permissions: Some systems triggered errors while Netblog tries to create the log file, mainly after installation.
* Updated readme.txt: removed previously dropped features "Websearch" and "Export/Import".

= 2.11 =
* Compatibility to WP 3.3.1
* Updated readme.txt
* Added banner

= 2.10 =
* Fixed Warnings/Notices: Previously, the new settings panel triggered some warnings when saving settings.
* Removed Option: The option to include field identifiers in WP shortcodes for citations made shortcodes unnecessary complex.
* Dropped Websearch Step 1: Integrated websearch functionality has been dropped in metabox `Further Reading` due to discontinued external service.
* Improved Visual Appearance: Metabox `Bibliography` has been updated to look better with some modern browsers.

= 2.09 =
* Improved Visual Appearance: The main and settings admin panels have been tightly integrated to the default WP styles; main panel dynamically shows latest news, tips, what's new in the current release, recent links and references.
* Renamed Submenus: Page "External Links" has been renamed to "Links"; "General Settings" to "Settings".
* Added Contextual Help: Settings page now features contextual help.
* Improved naming conventions: Affected classes are nbBibliographyReference, nbBibliographyItem, nbExportItemScheduled
* Disabled Some Unstable Features: NetVis, old Settings page (to manage global captions and custom citation styles, commend out lines ca.480 in core/netblog.php).
* Extended Classes: nbLinkExternCollection and nbBibliographyReference got new methods; see repository difference for more infos.
* New Classes: nbOptionsGUI, nbMainGUI, NetblogInit.
* Fixed Classes: Affected Classes are nboption, nbLoggingGUI, nbLinkExternCollection, netblog.
* Fixed CSS Style Incompatibility: Two definitions in style-admin.css prevented the contextual help from appearing
* Reduced Plugin Size Step 1: unused images have been removed; compressed large images.

= 2.08 =
* Fixed netblog.php: require_once('core/nbImportBibTexGUI.php') 

= 2.07 =
* Updated metabox 'Further Reading': individual URLs can be added 

= 2.06 =
* Fixed metabox Further Reading while adding new external links to new posts
* Fixed creation of new external links: fixed missing title.
* Missing external link's title will be recovered automatically. So if you remove a link's title, it will be automatically recovered by 1. directly parsing the webpages document or 2. by using websearch engines' caches
* Fixed update of external links in MEL for single and double quotes
* Fixed MEL: locked links can not be removed or changed at all

= 2.05 =
* Increased security and anti-spam measures while sending feedback messages to Netblog Server
* Added possibility to reset fields in metabox Bibliography
* Minor Improvement of visual appearances in metabox Bibliography
* Improved initialisation of metabox Bibliography, which is now done when the document has finished loading
* Fixed positioning of Netblog's popups for Firefox 6
* Fixed settings updates of some selections for Chrome 11
* Fixed logging when log file does not exists (errors and warnings are now ignored)

= 2.04 =
* Added excerpts for bibliographic citations. Note: excerpts can be of unlimited size, but any formatting is strictly disabled, i.e. only plain text is allowed at the moment to make upgrade to formatted excerpts easier (how to format excerpts is currently rather unclear)
* Added the possibility to hide inline citations. This option is to be found in the metabox Bibliography > Advanced Options
* Fixed rendering of citations in the list of bibliography. Previously, all html tags were removed, even < b >, < i > and < u >, which are now allowed.

= 2.03 =
* Replaced setting Citations > Bibliography > Numbering with 3 options to be found in Citations > Inline Citations > Output Format, CSS Formatting and Custom Output Format. That way, inline citations can be rendered either as literal strings or as numbers (like footnotes, e.g. decimals, alpha, roman, greek); a custom style format can be applied to them and their final output can be customized, e.g. [<output>], (<output>), <sup>(<output>)</sup>.
* Removed unused methods getBibNumberCitations() and enableBibNumberCitations() in nboption.php

= 2.02 =
* Added new citation settings: 'Headline Style Level', 'Numbering' to number each inline citation numerically and 'CSS Formatting' to format each element in the final bibliographic table
* Added new footnotes settings: 'CSS Formatting' and 'Horizontal Rule'
* Updated naming convention of reference, citation and footnote (on page) links

= 2.01 =
* Created new screenshots
* Cleaned netblog.php (the way and order of requires and includes)
* Some minor css style improvements
* Tested previous and last beta release on WordPress 3.0, 3.1 and 3.2 under Windows and Linux server/os. Tested: with a couple of themes and with BuddyPress, with WordPress MultiSite, Upgrade from 1.5.4 works. 

= 2.0.b6 =
* Optimized and added some functionality to class nbCaptionType and nbCaption
* Splitted class nbdb to classes nbLinkIntern, nbLinkExtern, nbLinkExternCollection, nbCaption and nbCaptionType
* Converted panel 'MEL' to OOP; makes use of nbLinkExtern, nbLinkExternCollection, XML transfer data format, jQuery and DataTransfer for stable url fetches
* Minor improvement to MEL GUI and MEL internal functionality (use of jQuery and XML making the application more robust)
* Updated panel 'Captions' to use the new classes
* Added new helper classes nbPost and nbUri
* Added class nbMetaboxFurtherReading and converted actions to oop (using nbLink*)
* Improved metabox further reading: outgoing links are opened in blank window/tab
* Experimential feature 'NetVis' has been disabled for all future stable releases
* Added class nbMetaboxRefmaker and removed file mtb_refmaker.php
* Added settings notification in metabox Reference Maker > Tables > Bibliographies: user will be notified when such tables are currently automatically appended to wp posts
* Fixed automplete in nbBibMetaboxGUI, the bibliographic metabox.
* Added new option: Include database field ids in generated reference shortcodes, enabled by default (slight performance increase)
* Added new option to nbExportItemScheduled to limit the maximum number of past executed schedules (to reduce database costs)
* Improved MEL: automatically open log bar on errors
* Minor fix to nbcite.php to work better with WordPress 3.2
* Minor fix to nbMetaboxRefmaker.php in javascript code where it tries to use a null value in some browsers
* Renamed netblogWidgets.php to core/nbWidgets.php to follow overall naming rules

= 2.0.b5 =
* New Admin Panel: Captions
* Removed list of captions from settings menu (caption tab)
* Fixed missing div-tag in nbExportSchedulerGUI.

= 2.0.b4 =
* New Admin Panel 'Logging' with highlighted log entries (erros, warning, success), with a status bar (size, lines etc) and maintenance tasks (clear log)
* Fixed DataTransfer::SubmitPost()
* Improved some logging information

= 2.0.b3 =
* Added class DataTransfer() which can handle php curl and php fopen wrapper methods to access remote websites
* Improved metabox Further Reading while editing WP posts: communication is now done via special XML format and new js class is used. Autocomplete box has been made more stable and it is now better suited for future expansion and development
* Improved widgets implementation (now more stable)
* Fixed permission error while performing scheduled backups

= 2.0.b2 =
* Fixed Export Scheduler
* Minor improvements to metabox Bibliography

= 2.0.b1 =
* New Export and Import functionality with the help of export modules (highly experimential; import not yet working properly)
* New Encrypted Backups (up to 256bit encryption with key adviser)
* New Export Scheduler
* New Integrated Feedback System
* New Footprints System: local and server side mode
* New Test Pilot Feature
* New Visualizer, also known as NetVis
* New Graphical User Interface
* Improved MEL User Interface and Client/Server communication via XML file formats
* Improved Integrated Internet and Blog Search: fetch Netblog Server listed search modules to keep you up to date
* Improved Settings Design
* Enhanced Cross-Browser Support and support for CSS3
* Added a lot of new settings
* Fixed Network Activate option

= 1.5.4 =
* Removed config parameter: NETBLOG_FOOTNOTE_TAG, NETBLOG_CITE_TAG, NETBLOG_CAPTION_TAG, NETBLOG_CODE_TAG
* Increased OOP to enhanced compatibility to other WP plugins.
* Fixed Bibliography Creation of several different citation styles in one article.
* Fixed Bibliography headline: previously, shorcode tag 'print_headline' has no effect.
* Introduced a maximum number of Bibliographies per article (default: 5).
* New options for citations:  1. Custom Bibliography headline for default citation style, 2. Maximum number of generated Bibliographies per article.
* Updated dead link in settings -> citations: read more about citations.
* Fixed custom citation style filter names for database while saving to the database.
* Improved visual appearance of Netblog Settings.
* Option "Inline Footnotes" has been disabled in settings.
* New option "Tag Name" for footnotes, citations and captions (disabled for the moment).
* New option: Widgets 'Further Reading' and 'Referenced By', and the wizards 'Reference Maker' as well as 'Further Reading' can be disabled to enhance compatibility to other WP plugins.
* New option: automatically append table of bibliography and/or table of footnotes to your WP articles.
* Fixed rendering table of bibliographies of multiple and different WP articles on one Webpage.
* Redesigned options handling and main Netblog organisation (now using OOP).
* Fully compatible with WPMU using FUI (automatic first use installation): Network Activation and Installation enabled.

* Redesigned settings management.
* New Log System
* New Advanced Settings
* New Shortcode Manager, including Shortcode Removal Wizard and Shortcode Migration Wizard
* New Websearch and Blogsearch Online Templates, which are automatically synchronized with the Netblog server.

= 1.5.3 =
* EED (Embedded Export Data) will not be displayed in the default Wordpress Editor's HTML view.
* When displaying several articles on one webpage, numbering footnotes restarts for each article.
* New option `Use global caption numbers` to either render local or global numbering. Local numbering starts with 1,2,3,... and global numbering with 1.1, 1.2, 2.1, 1.a, 1.b, ...
* Referencing captions improved: a webpage won't be reloaded in case the referenced caption has been defined previously on this webpage.
* Improved creation of captions in Reference Maker: choose from used and default caption types or create a new one; automatically checking for availability of a given caption name, since it has to be unique in the current WordPress site.
* Improved visual appearance while creating citations in Reference Maker: only fields for given citation style and resource type are displayed below (all others formatted in italics can be found in Optional Fields); required fields are formatted as bold to render a correct bibliography.
* Updated caption name and type restrictions: they may only contain alphanumeric characters and the underscore, i.e. {a-z,A-Z,0-9,_}.
* Added support for custom startup templates in NB-MEL.
* Dynamic sidebar for widgets (displayed below articles) has been made optional and it is deactivated by default; Go to Netblog settings to activate this sidebar.
* Unified/standardized Netblog option names which are saved by WordPress Options (to prevent interference with other WP plugins).
* Added support for custom shortcodes, defined in config.php (improved).
* Improved security of cross-referencing footnotes (within the same document): internal shortcode is not exposed to public.
* Improved interactive feedback in MEL: 'loading...' and where links have been found.  

= 1.5.2 =
* Update Reference Maker: in citation wizard, only necessary fields for a given citation style and resource type are displayed (all fields can be accessed via "Optional Fields").
* Update: nbcs*.php

= 1.5.1 =
* Bug-fix: installation

= 1.5 =
* New Language System: current available language is english (default). Please help to add your local language!
* New Reference Style: Harvard
* New Widget Area: display widgets directly after posts and pages, e.g. list all references below a post's text, and not in the sidebar.
* New Export & Import Feature: automatically export links from further reading while using Wordpress Export. Due to beta feature, references will be imported as external links.
* Improved Graphical User Interface of MEL.
* Reorganized internal structure: easy creation of custom, professional reference styles.
* Update: APA 6th Edition (plus: periodical); always display doi or url if available. Fixed formatting and display of volume and issue number and of date format.
* Significantly improved performance and reliability of Custom Style Filter Preview.
* Improved help for Custom Search Templates.

= 1.4.4 =
* New Feature: Resource Preview (integrated into autocomplete, list of references, NB-MEL)
* IE: improved performance in NB-MEL

= 1.4.3 =
* New Flag `Lock/Unlock` for NB-MEL (cmd-context: [match words] [sort:id|title|refs|flag[-desc]] [flag:offline|trash|lock] [limit:integer] );
* Improved searching for resources (Metabox Further Reading): faster server response, better visual quality - use of icons
* Increased security in NB-MEL
* Minor enhances in settings panel and NB-MEL
* Fixed Bug & improved NB-MEL's quick find
* Minor improvement in footnotes - print

= 1.4.2 =
* Enhanced Security and userbility of adding custom citation styles 
* Immediate feedback while typing cutom citation style (see settings)
* Fixed Bug in cross-reference captions
* Fixed Bug in Reference Maker: cross-reference captions

= 1.4.1 =
* New Option in settings - citation: Improve site-wide consistency of used citation styles by forcing alls bibliographies to use the default style.
* Fixed Bug in Settings.

= 1.4 =
* New Settings page under Setting -> Netblog
* New Feature: Custom Search in NB-MEL
* New Academic Features: Footnotes, Citation, Caption, Bibliography, Cross-reference Captions, custom Cite Styles 
* New Metabox: Reference Maker
* Moved page Posts | Extern Links to `Links | NB-MEL`
* Important: increased security levels

= 1.3 =
* New Feature: Manage External Resources (list, trash, erase, restore, check online status, update titles automatically)
* Update Database organisation - external resources
* Update Autocomplete box: more stable, smooth listing
* Update Metabox References - List of links: fade in new links
* Bug fixed: IE moving Metabox disappeared
* Renamed Widget References to Referenced By

= 1.2 =
* New Feature: Link to pages within the website
* New Feature: Pingbacks
* New Feature: Display Widgets on posts, pages and/or any other type.
* Revised Widgets settings area: truncate links; maximum number of links for each link type; custom order of link types
* Updated Widgets: PDF-Icon for external pdf-file resources in the public area (in the list shown on a post or page)
* Updated Widget 'References': Use of icons for external links returned by blogsearch
* Updated Admin edit-post/page page: list of links in alphabetical order. First internal links, followed by external links.
* Updated Config: constants NETBLOG_BLOGSEARCH_PROVIDER_NAME and NETBLOG_BLOGSEARCH_PROVIDER_URI added
* Updated Widget Descriptions
* Fix Widgets - List of links: use of an URI indexer to prevent duplicates.

== Upgrade Notice ==

= 1.4.2 =
* Enhanced Security and userbility of adding custom citation styles 
* Important bug fixes

= 1.4.1 =
* Fixed Bug in Settings

= 1.4 =
* A complete reference wizard to manage your citation, footnotes and captions.
* Enhanced Security and Settings Panel
* Custom Search in NB-MEL.

= 1.3 =
* New Feature: Manage External Resources
* More stable autocomplete box while typing a query
* Metabox References: smooth fade in new link
* Bug fixed: IE moving Metabox disappered

= 1.2 =
* A couple of new features, including pingbacks, links to pages
* More detailed settings for the widgets
* Minor fixes

= 1.1 =
* First public version
