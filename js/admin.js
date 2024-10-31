// JavaScript Document


function netblog_mk_net_form ( id, hrefObj )
{
	obj = document.getElementById(id);
	if( obj.value == '0' ) {
		obj.value = '1';
		hrefObj.className = "active";
	} else { 
		obj.value = '0';
		hrefObj.className = '';
	}
}


function netblog_set_field_int( id, step )
{
	obj = document.getElementById(id);
	var n = parseInt(obj.value) + step;
	if( n < 0 ) n = 0;
	obj.value = n;
}
function netblog_set_field_intAbs( id, abs )
{
	obj = document.getElementById(id);
	var n = parseInt(abs);
	if( n < -1 ) n = 0;
	obj.value = n;	
}
function netblog_check_field2int( id, dft )
{
	obj = document.getElementById(id);
	var n = parseInt(obj.value);
	if( obj.value == n && n > -2 )
		return;
		
	if( isNaN(n) || n < -1 )
		n = dft;
	obj.value = n;
}

function netblog_count_chars( obj, id )
{
	var to = document.getElementById(id);
	if( obj != null && to != null ) {
		if( obj.value.length > maxChars )
			obj.value = obj.value.substring(0,maxChars);
		to.innerHTML = obj.value.length;
	} 
}

function input_strip( obj, limit )
{
	if( obj.value.length > limit )
		obj.value = obj.value.substring(0,limit);
}
function str2alphanum(obj)
{
	if( obj.value != 'undefined' && obj.value.length > 0 ) {
		obj.value = obj.value.replace(/[^a-zA-Z0-9\s]/g,'');
	}
}
function str2int(obj)
{
	if( obj.value != 'undefined' && obj.value.length > 0 ) {
		obj.value = parseInt(obj.value);
	}
}
function str2alphanumNW(obj)
{
	if( obj.value != 'undefined' && obj.value.length > 0 ) {
		obj.value = obj.value.replace(/[^a-zA-Z0-9]/g,'');
	}
}
function str2alphanumNW_US(obj)
{
	if( obj.value != 'undefined' && obj.value.length > 0 ) {
		obj.value = obj.value.replace(/[^a-zA-Z0-9_]/g,'');
	}
}
	
function netblog_widget_oneFieldtrue( fsize, f1, f2, fonpost, fonpage, fonother, ferror )
{	
	if( (f1.length>0 && f2.length>0 && !document.getElementById(f1).checked && !document.getElementById(f2).checked)
		|| (document.getElementById(fsize).value == 0 )
		|| ( fonpost.length>0 && fonpage.length>0 && fonother.length>0 && !document.getElementById(fonpost).checked && !document.getElementById(fonpage).checked && !document.getElementById(fonother).checked )
		) {
		document.getElementById(ferror).style.display = 'block';
	} else document.getElementById(ferror).style.display = 'none';
														 
}

function netblog_widget_oneFieldtrue2( fsize1, fsize2, fsize3, fonpost, fonpage, fonother, ferror )
{	 
	if( ( document.getElementById(fsize1).value == 0 && document.getElementById(fsize2).value == 0 && document.getElementById(fsize3).value == 0 )
		|| ( fonpost.length>0 && fonpage.length>0 && fonother.length>0 && !document.getElementById(fonpost).checked && !document.getElementById(fonpage).checked && !document.getElementById(fonother).checked )
		) {
		document.getElementById(ferror).style.display = 'block';
	} else document.getElementById(ferror).style.display = 'none';
														 
}



/* AJAX FUNCTIONS */
function interpretStr ( str, delimMain, delimSub )
{
	if( str.length == 0 ) return Array();
	
	var ar = str.split(delimMain);
	for( i=0; i<ar.length; i++ )
		ar[i] = ar[i].split(delimSub);
	return ar;				
}
			
/* SEARCH LINKS 
*		works with Wordpress 2.8+
*		requires jQuery and WP ajax handler for the wp-action 'get_links'	
*		requires CBResponseHandler( response );
*/
function netblog_ajax_get_links_str( query, CBResponseHandler, CBContextObj )
{
	jQuery(document).ready(function($) {
		var data = {
			action: 'get_links',
			query: query
		};
		jQuery.post(ajaxurl, data, function(response) {
			if( typeof(CBResponseHandler) == 'function' ) {
				if( CBContextObj == null )
					CBResponseHandler( response );
				else CBResponseHandler.call( CBContextObj, response );
			} else alert('AJAX Get Links: no valid response handler');
		});
	});	
}


function netblog_ajax_send_link( uri_id, uri, title, flag, CBflushed, CBContext )
{
	jQuery(document).ready(function($) {
		var data = {
			action: 'send_link',
			uri_id: uri_id,
			uri: uri,
			title: title,
			flag: flag
		};
		jQuery.post(ajaxurl, data, function(xml) {
			jQuery(xml).find("LINK").each(function() {
				uri_id = jQuery(this).attr('id');
				uri = jQuery(this).attr('uri');
				title = jQuery(this).attr('title');
				flag = jQuery(this).attr('flag_code');
			});

			if( typeof(CBflushed) == 'function' ) {
				var success = true;
				var msg = "";
				if(xml=='false' || xml=='failed')
					success = false;
				if(xml.substr(0,6) == 'error:') {
					success = false;
					msg = xml.substr(6);			
				}
				
				CBflushed.call( CBContext, success, uri_id, uri, title, flag, msg );
			} else alert('AJAX Get Links: no valid response handler');
		});
	});
}


var nbleave_confirm = false;
function nbleave(){
	if (nbleave_confirm){
		return "You have unsaved changes.";
	}       
}
window.onbeforeunload = nbleave;


/*
*	LIST LINKS MANAGER
*
*
*/

function netblog_links()
{
	this.list = Array();
	this.hasChanged = false;
	
	this.scopeElemID = '';
	this.classElemModified = '';
	this.classElemTrashed = '';
	this.classElemOffline = '';
	
	this.print2Obj = null;
	this.statusObj = null;
	this.logBoxObj = null;
	
	this.progbarObj = null;
	this.progbarHideDelay = 2000;
	this.progbarHideDelayLong = 10000;
	this.progbarClass = '';
	this.progbarClassErr = '';
	
	this.CBflush2serv = null;
	this.CBflush2servCxt = null;
	
	this.CBflushed = null
	this.CBflushedCxt = null;
	
	this.CBLinkF = null
	this.CBLinkFCxt = null;
	
	this.CBonEvent = null;
	this.CBonEventCxt = null;
	
	this.CBupdateCountFlags = null;
	this.CBupdateCountFlagsCxt = null;
	
	this.flushItemsAll = 0;
	this.flushItemsDone = 0;
	this.flushItemsFailed = 0;
	
	this.flushInterval = 20000;		/* in ms */
	this.flushRenew = 5000;			/* time in ms from 0s to x, in which the a new flush-ask will proceed - timer = flushInterval */
	this.timerFreq = 1000;
	this.timer = 0;
	
	this.objName = '';
	
	this.linkGroupname = '';
	
	this.countOffline = 0;
	this.countTrashed = 0;
	this.countLocked = 0;
	this.countModified = 0;
	this.countDefault = 0;
	
	//
	// PUBLIC FUNCTION
	//
	this.init = function( objName, scopeElemID, classElemModified, classTrashed, classOffline, htmlProgbarID, classProgbarError )
	{
		this.objName = objName;
		this.scopeElemID = scopeElemID;
		this.classElemModified = classElemModified;
		this.classElemTrashed = classTrashed;
		this.classElemOffline = classOffline;
		
		this.progbarObj = document.getElementById(htmlProgbarID);
		if( this.progbarObj != null && classProgbarError.length > 0  ) {
			this.progbarClass = this.progbarObj.className;
			this.progbarClassErr = classProgbarError;
		}
		
	}
	this.setFlushInterval = function( ms )
	{
		if( parseInt(ms) >= 0 ) this.flushInterval = parseInt(ms);
	}
	
	this.add = function( id, uri, title, infos, flag, refs )
	{		
		var id2 = parseInt(id, 10);
		if(isNaN(id2)) {
			this.printLog("Invalid ID " + id + " ("+ uri +")");
		}
		id = id2;		
		var flag2 = parseInt(flag, 10);
		if(isNaN(flag2)) {
			flag2 = 0;
			this.printLog("Warning: Invalid FLAGID '"+flag+"' set to 0 ("+ uri +")");
		}
		flag = flag2;
		
		if( this.find(id) == -1 )
			this.list.push( [id,uri,title,infos,flag,refs,0] );
	}
	this.addAndShow = function( id, uri, title, infos, flag, refs )
	{
		if( this.find(id) == -1 ) {
			id = parseInt(id);
			flag = parseInt(flag);
			this.list.push( [id,uri,title,infos,flag,refs,0] );
			if( this.print2Obj != null && this.CBLinkF != null ) {
				if( this.print2Obj.style.display == 'none' ) {
					this.print2Obj.innerHTML = '';
					show( ''+this.print2Obj.id+'',0,1);
				}
				this.print2Obj.innerHTML += this.CBLinkF.call( this.CBLinkFCxt, id, uri, title, infos, flag, refs );
			}
		}
	}
	this.showAll = function()
	{
		this.print2Obj.innerHTML = '';
		setOpacity( this.print2Obj.id, 100 );
		this.print2Obj.style.display = 'block';
		var i;
		for( i=0; i<this.list.length; i++ )
			if( this.print2Obj != null && this.CBLinkF != null ) {
				var e = this.list[i];
				this.print2Obj.innerHTML += this.CBLinkF.call( this.CBLinkFCxt, e[0], e[1], e[2], e[3], e[4], e[5] );
			}
		//showPlus( 'netblog-body', 0, 500, true, false );
	}
	
	this.update = function( id, uri, title, infos, flag, refs )
	{
		// CHECK IF EXISTS
		var i = this.find(id);
		if( i == -1 ) return;
		
		// CHECK IF CHANGED
		var e = this.list[i];
		if( e[0] == id && e[1] == uri && e[2] == title && e[3] == infos && e[4] == falg && e[5] == refs )
			return;
		
		// UPDATE AND MARK AS MODIFIED
		this.list[i] = [id,uri,title,infos,flag,refs,1];
		this.hasChanged = true;
		this.flush( this.flushInterval );
		nbleave_confirm = true;
	}
	this.setUri = function( id, uri ) 
	{
		var i = this.find(id);
		if( i >= 0 && this.list[i][1] != uri) 
		{ 		
			this.list[i][1] = uri; this.setStatus(i,1); //nbleave_confirm = true;			
			this.countModified++;	
			this.flush( this.flushInterval ); 
		}		
	}	
	this.setTitle = function( id, title ) 
	{
		var i = this.find(id);
		if( i >= 0 && this.list[i][2] != title) 
		{ 
			this.list[i][2] = title; this.setStatus(i,1); //nbleave_confirm = true;
			this.countModified++;
			this.flush( this.flushInterval );  
		}		
	}
	this.restoreElements = function() { this.setFlagsOnAll(4); }
	this.checkOnlineStatusOfElements = function() { this.setFlagsOnAll(10); }
	this.updateTitleOfElements = function() { this.setFlagsOnAll(11); }	
	this.lockElements = function() { this.setFlagsOnAll(99); }
	this.unlockElements = function() { this.setFlagsOnAll(100); }
	this.removeElements = function() { this.setFlagsOnAll(2); }
	this.removePermanent = function() { this.setFlagsOnAll(3); }
	this.setFlagsOnAll = function( flag )
	{
		var i, ch = false;
		for( i=0; i<this.list.length; i++ ) {
			if( this.getFlag(i) != flag && (this.getFlag(i)!=99 || flag==100) ) {
				this.setFlag(i,flag);
				this.setStatus(i,1);
				ch = true;
			}
		}
		if( ch ) {
			this.hasChanged = true;
			this.flush(0);		
		} else {
			this.updateStatusBar('nothing');
		}
	}
	
	
	this.find = function( id )
	{
		var i;
		for( i=0; i<this.list.length; i++ )
			if( this.getID(i) == id ) 
				return i;
		return -1;
	}
	this.clear = function() 
	{ 
		this.list = []; 
		if( this.print2Obj != null ) {
			if( typeof(hide) == 'function' ) {
				//hide( ''+this.print2Obj.id+'', 50, 250 );
			} 
			this.print2Obj.innerHTML = '';
		}
		this.hasChanged = false;
		nbleave_confirm = false;
		if( this.CBonEvent!=null )
			this.CBonEvent.call( this.CBonEventCxt );	
		this.updateStatusBar('');	
	}
	
	// SEND UPDATED TO SERVER
	this.flush = function( delay )
	{
		if( !this.hasChanged || this.isBusy() ) return;
		
		if( delay < 0 ) delay = this.flushInterval;
		
		if( delay > 0 && (delay == this.timer || (delay > this.timer && this.timer < this.flushRenew) ) && this.timer >= 0 ) {
			setTimeout( this.objName + '.flush('+ (delay - this.timerFreq) +')', this.timerFreq );			
			this.updateStatusBar('Save in '+ Math.round(delay/1000) +'s');
			this.statusObj.title = 'Click to save immediate';
			this.timer = delay - this.timerFreq;
			nbleave_confirm = true;
			return;	
		} else if( delay > 0 ) {this.timer = 0; return; }
		
		this.countLocked = this.countModified = this.countOffline = this.countTrashed = this.countDefault = 0;
		this.updateStatusBar('');
		if(this.logBoxObj!=null && this.logBoxObj.innerHTML.length > 10000)
			this.logBoxObj.innerHTML = '';
		if( this.CBonEvent!=null )
			this.CBonEvent.call( this.CBonEventCxt );
		
		var i;		
		if( this.CBflush2serv != null && this.CBflushed != null && !this.isBusy() ) {			
			// COUNT ITEMS PRIOR TO OPERATION
			this.flushItemsAll = this.flushItemsDone = this.flushItemsFailed = 0;
			for( i=0; i<this.list.length; i++ ) 
				if( this.getStatus(i) == 1 )
					this.flushItemsAll++;
			
			// SET STATUS BAR INFO
			if( this.flushItemsAll > 0 ) {
				this.updateStatusBar('saving');
			}
			
			nbleave_confirm = true;
			
			// PROCEED WITH FLUSH
			for( i=0; i<this.list.length; i++ ) 
				if( this.getStatus(i) == 1 ) {
					if( this.getFlag(i) > 0 )
						this.updateStatusBar( this.getFlag(i) );
					this.CBflush2serv.call( this.CBflush2servCxt, this.list[i][0], this.list[i][1], this.list[i][2], this.list[i][4], this.CBflushed, this.CBflushedCxt );
				}
		}
	}
	
	
	/* rebuild count of flags */
	this.countFlagDistribution = function() 
	{
		this.countLocked = this.countModified = this.countOffline = this.countTrashed = this.countDefault = 0;
		for(var i=0; i<this.list.length; i++) {
			switch(this.getFlag(i)) {
				case 1: this.countOffline++; break;
				case 2: case 3: this.countTrashed++; break;
				case 99: this.countLocked++; break;
				default: this.countDefault++;
			}
		}
//		alert(this.CBupdateCountFlags);
//		alert(this.CBupdateCountFlagsCxt);
		if(this.CBupdateCountFlags!=null) 
			this.CBupdateCountFlags.call( this.CBupdateCountFlagsCxt );
		
	}
	
	/* called by this.CBflush2serv.call() */
	this.flushed = function( success, id, uri, title, flag, msg )
	{	
		id = parseInt(id);
		flag = parseInt(flag);

		if(success) {
			var i = this.find(id);				
			if( i < 0 ) {
				this.printLog("Error: Unknown ID " + id + " (uri:"+uri+")");
				return;
			}			
			if(this.logBoxObj!=null) {
				var t = "";
				switch(flag) {
					case 1: t = "Offline"; break;
					case 3: case 2: t = "Trashed"; break;
					case 99: t = "Locked"; break;
					default: t = "Updated";
				}
				this.printLog(t + " URI "+uri);
			}
			this.setStatus(i,0);
			this.flushItemsDone++;

			// UPDATE CLASSNAME & STATUS-BAR
			var ecl = null;
			if( this.classElemModified.length > 0 && this.scopeElemID.length > 0 
		  	 && (ecl=document.getElementById( this.scopeElemID+this.getID(i) )) != null ) {	
				switch(flag){
					case 1: ecl.className = this.classElemOffline;  break;
					case 2: ecl.className = this.classElemTrashed; break;
					case 3: ecl.parentNode.innerHTML = ''; ecl.parentNode.style.display = 'none';  break;
					default: ecl.className = '';
				}
			}
			
			switch(flag) {
				case 1: this.countOffline++; break;
				case 2: case 3: this.countTrashed++; break;
				case 99: this.countLocked++; break;
				default: this.countDefault++;
			}
			
			if(this.CBupdateCountFlags != null)
				this.CBupdateCountFlags.call(this.CBupdateCountFlagsCxt);
			
			// UPDATE ELEMENTS
			this.list[i] = [id,uri,title,this.getInfos(i),flag,this.getRefs(i),0];
			if( this.CBLinkF != null )
				this.CBLinkF.call( this.CBLinkFCxt, id, uri, title, this.getInfos(i), flag, this.getRefs(i) );	
			
		} else { 
			this.flushItemsFailed++;
			if( this.progbarObj != null && this.progbarClass.length > 0 )
				this.progbarObj.className = this.progbarClassErr;
			if(this.flushItemsFailed==1)
				nbmel_showLogBox();
			this.printLog("Error: " + msg);
		}
		
		// UPDATE PROGRESBAR
		if( this.progbarObj != null )
			this.progbarObj.style.width = (this.flushItemsDone + this.flushItemsFailed) * 100 / this.flushItemsAll + "%";
		
		// FINISHED PROGRESS
		if( this.flushItemsAll == this.flushItemsDone + this.flushItemsFailed ) {
			this.countFlagDistribution();
			
			if( this.flushItemsFailed > 0 ) {
				this.updateStatusBar('error');
				this.timer = -1;
				if( this.progbarObj != null ) {
					//hidePlus( ''+this.progbarObj.id+'', 0, 150, true, false );
					setTimeout( this.objName + ".progbarObj.style.width = '0px'", this.progbarHideDelayLong + 250 );
				}
			} else {
				this.hasChanged = false;
				if( this.progbarObj != null ) {
					//hidePlus( ''+this.progbarObj.id+'',  this.progbarHideDelay, 150, true, false );
					setTimeout( this.objName + ".progbarObj.style.width = '0px'", this.progbarHideDelay + 500 );
					//showPlus( ''+this.progbarObj.id+'', this.progbarHideDelay + 500, 0, true, false );
				}
				setTimeout( this.objName + ".updateStatusBar('results')", this.progbarHideDelay );
					
					//this.progbarObj.style.width = '0px';
				this.updateStatusBar('ok');
				this.timer = 0;
				this.checkStatus(2000);
			}
			
			/* LEAVE PAGE WITHOUT WARNING */
			nbleave_confirm = false;
		}
	}
	
	this.checkStatus = function( delay )
	{
		for( i=0; i<this.list.length; i++ ) 
			if( this.getStatus(i) == 1 ) {
				this.hasChanged = true;
				if( delay == 0 )
					this.flush( this.flushInterval );
				else setTimeout( this.objName + '.flush('+this.flushInterval+')', delay );
				return;
			}
	}
	
	this.updateStatusBar = function( key )
	{
		if( key == 'ok' ) { this.statusObj.innerHTML = 'Done'; return; }
		if( key == 'results' ) { 
			this.countFlagDistribution();
			this.statusObj.innerHTML = this.list.length +' links';
			if( this.linkGroupname.length > 0 )
				this.statusObj.innerHTML += ' in ' + this.linkGroupname;
			return; 
		}
		if( key == 'error' ) { this.statusObj.innerHTML = '<font class="err">Done with ' + this.flushItemsFailed + ' errors</font>'; return; }
		if( key == 'saving' || key == 'save' || key == 1 ) { this.statusObj.innerHTML = 'Saving...'; return; }
		if( key == 'update' || key == 11 ) { this.statusObj.innerHTML = 'Update...'; return; }
		if( key == 'checkStatus' || key == 10 ) { this.statusObj.innerHTML = 'Check status...'; return; }
		if( key == 'trash' || key == 2 ) { this.statusObj.innerHTML = 'Trash...'; return; }
		if( key == 'erase' || key == 3 ) { this.statusObj.innerHTML = 'Erase...'; return; }
		if( key == 'lock' || key == 99 ) { this.statusObj.innerHTML = 'Locking...'; return; }
		if( key == 'unlock' || key == 100 ) { this.statusObj.innerHTML = 'Unlock...'; return; }
		if( key == 'nothing' ) { this.statusObj.innerHTML = '<font class="warn">Nothing changed</font>'; return; }
		if( key == 'restore' || key == 4 ) { this.statusObj.innerHTML = 'Restoring...'; return; }
		if( key == '' ) {
			if( this.progbarObj != null ) {
				this.progbarObj.style.width = '0px';
				if( this.progbarClass.length > 0 )
					this.progbarObj.className = this.progbarClass;
			}
		}
		this.statusObj.title = '';
		this.statusObj.innerHTML = key;
	}
	
	this.printLog = function( msg ) 
	{
		if(this.logBoxObj!=null)
			this.logBoxObj.innerHTML = msg + "<br />" + this.logBoxObj.innerHTML;
	}
	
	this.isBusy = function() { return this.flushItemsAll != (this.flushItemsDone+this.flushItemsFailed);}
	
	this.hasErrors = function() { return this.flushItemsFailed>0; }
	this.countErrors = function() { return this.flushItemsFailed; }
	
	this.registerCBflush2server = function( func, contextObj, id, uri, title, flag, CBflushed, CBcontext )
		{ this.CBflush2serv = func; this.CBflush2servCxt = contextObj; }
	this.registerCBflushed = function( func, contextObj, success, id, uri, title, flag )
		{ this.CBflushed = func; this.CBflushedCxt = contextObj; }
	this.registerCBlinkF = function( func, contextObj, id, uri, title, info, flag, ref )
		{ this.CBLinkF = func; this.CBLinkFCxt = contextObj; }		
	this.registerCBupdateCountFlags = function( func, contextObj )
		{ this.CBupdateCountFlags = func; this.CBupdateCountFlagsCxt = contextObj; }
	this.registerPrint2Obj = function( htmlID )
		{ this.print2Obj = document.getElementById(htmlID); this.print2Obj.style.display = 'none'; this.print2Obj.innerHTML = ''; }
	this.registerStatusObj = function( htmlID )
		{ this.statusObj = document.getElementById(htmlID); }
	this.setFlushInterval = function( millisec ) 
	{
		var i = parseInt(millisec);
		if( i == millisec ) this.flushInterval = i;
	}
	this.registerCBonEvent = function( func, contextObj )
		{ this.CBonEvent = func; this.CBonEventCxt = contextObj; }
	this.setGroupName = function( name ) { this.linkGroupname = name; }
	this.setLogElement = function( id ) { this.logBoxObj = document.getElementById(id); }
	
	//
	// PRIVATE METHODS
	//
	
	this.getStatus = function( index ) { return this.list[index][6]; }
	this.getID = function( index ) { return this.list[index][0]; }
	this.getRefs= function( index ) { return this.list[index][5]; }
	this.getInfos = function( index ) { return this.list[index][3]; }
	this.setStatus = function( index, e ) 
	{ 
		this.list[index][6] = e; this.hasChanged = true; 
		var obj = null;
		if( this.classElemModified.length > 0 && this.scopeElemID.length > 0 
		   && (obj=document.getElementById( this.scopeElemID+this.getID(index) )) != null ) {
			if( e == 1 )
				obj.className = this.classElemModified;
			else obj.className = '';
		}	
	}
	this.setFlag = function( index, e ) { this.list[index][4] = e; }
	this.getFlag = function( index ) { return this.list[index][4]; }
}







/*
*	SEARCH INPUT
*/
/* TODO: not working in FF4B12 */
function searchInput ( id, code )
{
//	if( (48 <= event.keyCode && event.keyCode <= 90)			// alphanum
//		 || (96 <= event.keyCode && event.keyCode <= 111 ) 		// numlocks
//		 || (8 == event.keyCode)								// backspace
//		 || (46 == event.keyCode) 								// delete
//		 || (32 == event.keyCode) ) {							// space
		setTimeoutRefresh( id, code, 500 );
//	}
}



/*
*	ADVANCED STRING OPERATIONS;
*/
String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g,"");
}
String.prototype.ltrim = function() {
	return this.replace(/^\s+/,"");
}
String.prototype.rtrim = function() {
	return this.replace(/\s+$/,"");
}
String.prototype.contains = function(it) { 
	return this.indexOf(it) != -1; 
}




/*
* POPUP
*/
		
var popupCloseTimerId = null;
var popupOpenTimerId = null;
var popupID = '';
var popup_btn_save = false;

function nbpopup_init( id )
{
	popupID = id;
	var o = document.getElementById(id);
	if( o == null ) return;
	
	var save = '';
	if( popup_btn_save ) save = '<div onclick="nbpopup_save()">save</div>';
	
	o.innerHTML = '		<div class="popup fullscreen opac90"></div> '+
		'<div class="wnd noopac"> ' +
		'	<div class="title opac75" >' +
		'		<div class="btn">' +
		'			<div onclick="nbpopup_reload()">reload</div>' +		
		'			' + save + 
		'			<div style="cursor:wait" onmouseover="nbpopup_close(true)" onmouseout="nbpopup_close(false)">close</div>' +
		'			<div class="netblog-clear"></div>' +
		'		</div>' +
		'		<label id="nb-popup-title">title</label>' +
		'		</div>' +
		'	<div class="body"><iframe src="" id="nb-popup-iframe" onload="nbpopup_loaded()" border="0"></iframe></div>' +
		'</div>';
	
}
function nbpopup_close( bool )
{
	if( bool && popupCloseTimerId == null ) {
		popupCloseTimerId = setTimeout('hidePlus(\''+popupID+'\',0,200,true,false); document.getElementById(\'input-autocomplete1\').focus();',750);					
	} else if( !bool ) {					
		clearTimeout( popupCloseTimerId );
		popupCloseTimerId = null;
	}				
}
function nbpopup( bool, title, url )
{			
	if( bool && popupOpenTimerId == null )
		popupOpenTimerId = setTimeout('nbpopup_load(\''+title+'\',\''+url+'\')',2000 );
	else if( !bool ) {
		clearTimeout( popupOpenTimerId );
		popupOpenTimerId = null;					
	}				
}
function nbpopup_load( title, url )
{
	document.getElementById('nb-popup-title').innerHTML = title + '&nbsp;&nbsp;<small>&mdash;&nbsp;&nbsp;<font id="nb-popup-url">' + url + '</font></small>';
	
	if( document.getElementById('nb-popup-iframe').src != url ) {		
/*		var urip = parseUri(url);
		if( urip.tld.length > 0 ) {
			alert(urip.tld);
			document.domain = urip.tld;
		}*/
		document.getElementById('nb-popup-iframe').src = url;
	}
	showPlus('nb-popup',0,200,true,false);
}
function nbpopup_loaded()
{
	var t = document.getElementById('nb-popup-url');
	var ifr = document.getElementById('nb-popup-iframe');
	
	//t.innerHTML = ifr.contentDocument + '|';
}
function nbpopup_reload()
{
	var url = document.getElementById('nb-popup-iframe').src;
	document.getElementById('nb-popup-iframe').src = url;
}
function nbpopup_save()
{
	hidePlus('nb-popup',0,200,true,false);
	box.chooseUrl( document.getElementById('nb-popup-iframe').src );
}
function nbpopup_issave(bool)
{
	popup_btn_save = bool;
}




/* PARSE URIS */
function parseUri(data) {
	//var e=/^((http|ftp):\/)?\/?([^:\/\s]+)((\/\w+)*\/)([\w\-\.]+\.[^#?\s]+)(#[\w\-]+)?$/;
	var e=/((http|ftp):\/)?\/?([^:\/\s]+)((\/\w+)*\/)([\w\-\.]+\.[^#?\s]+)(#[\w\-]+)?/;
	
		var urlpattern = new RegExp("(http|ftp|https)://(.*?)/.*$");
		var p = data.match(urlpattern);
		var t =  {url:p[0], protocol:p[1],host:p[2],tld:"",path:"",file:"",hash:""};
		var d = t.host.split('.');
		
		if( d.length > 2 )
		switch( d[ d.length-2 ]) {
		case 'ac': case 'gov': case 'co': case 'or': case 'com': case 'net':
			d[ d.length-2 ] = d[ d.length-3 ] + '.' + d[ d.length-2 ]
		}     	
			
		t.tld = d[ d.length-2 ] + '.' + d[ d.length-1 ];
		return t;
	// }// else return {url:"", protocol:"",host:"",tld:"",path:"",file:"",hash:""};
}
function getDomain (thestring) {
	//simple function that matches the beginning of a URL
	//in a string and then returns the domain.
	var urlpattern = new RegExp("(http|ftp|https)://(.*?)/.*$");
	var parsedurl = thestring.match(urlpattern);
	return parsedurl[2];
}



/* POPUPS */
var nbpopCur = null;			
function nbpop_position() {
	if(nbpopCur != null) {
		var o = document.getElementById( nbpopCur.id + "_wnd" );
		if( o!=null ) {
			o.style.left = Math.max((jQuery(window).width() - jQuery('#'+o.id).width() - 40) / 2, 0) + "px";
			o.style.top = Math.max( (jQuery(window).height() - jQuery('#'+o.id).width() - 40 ) / 2, 0) + "px";
		}
	}
}
function nbpop_load(id) {
	if(nbpopCur!=null && nbpopCur.id==id) {
		return;
	}
	
	nbpop_unload();	
	nbpopCur = document.getElementById(id);
	if(nbpopCur!=null) {
		nbpopCur.style.visibility = 'hidden';
		nbpopCur.style.display = 'block';
		nbpop_position();
		setTimeout('nbpop_show()',150);
	}
}
function nbpop_fetch(wnd_id) {
	jQuery(document).ready(function($) {
		var data = {
			action: 'nbwnd_'+wnd_id
		};
		jQuery.post(ajaxurl, data, function(xml) {			
			if(xml=='false' || xml.length==0)
				alert('Loading Window \''+wnd_id+'\' failed');
			else 
				nbpop_fromxml(xml);
		});
	});	
}
function nbpop_unfetch(wnd_id) {
	var wndhdl = 'nbwndhandler_'+wnd_id;
	var wnd = document.getElementById(wndhdl);
	if(wnd)
		wnd.innerHTML = '';
}
function nbpop_formdata() {
	return jQuery('#nbwndform_'+nbpopCur.id).serialize();
}
function nbpop_fromxml(xml) {
	jQuery(xml).find('Window').each(function() {
		var id = jQuery(this).attr('id');
		var wnd = document.getElementById(id);
		if(wnd) {
			jQuery('#'+id+'_title').text( jQuery(this).attr('title') );
			jQuery('#'+id+'_body').html( jQuery(this).attr('content') );
			jQuery('#'+id+'_buttons').html( jQuery(this).attr('buttons') );
			jQuery('#'+id+'_script').html( jQuery(this).attr('script') );
		} else {
			var wndhdl_id = 'nbwndhandler_'+id;
			var wndhdl = document.getElementById(wndhdl_id);
			if(!wndhdl)
				jQuery("body").append('<div id="'+wndhdl_id+'">'+jQuery(this).attr('rendered')+'</div>');
			else
				wndhdl.innerHTML = jQuery(this).attr('rendered');
		}
		nbpop_load(id);
	});
}


function nbpop_show() {
	if(nbpopCur!=null)
		nbpopCur.style.visibility = 'visible';
}
function nbpop_isloaded( id ) { return nbpopCur != null && nbpopCur == document.getElementById(id); }	
function nbpop_unload(method) {
	if( nbpopCur != null ) {
		if(method=='fade') {
			var cur_pop = nbpopCur;
			jQuery('#'+nbpopCur.id).fadeOut('fast', function() {
				cur_pop.style.display = 'none';
				nbpop_clearvar();
			});
		} else nbpopCur.style.display = 'none';

		nbpopCur = null;
	}
}
function nbpop_clearvar() {
	nbpopCur = null;
}
function nbpop_wndid() {
	return nbpopCur.id;
}
window.onresize = nbpop_position;


/* COOKIE HANDLING */
function nbCreateCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function nbReadCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function nbEraseCookie(name) {
	createCookie(name,"",-1);
}