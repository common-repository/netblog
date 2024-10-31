function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}

function netblogListItem() 
{
	this.id = 0;				/* must be defined */
	this.title = "";
	this.author = "";
	this.date = "";
	this.status = 0;
	this.type = "";
	this.permalink = "";
	this.editlink = "";
	this.removable = false;
	
	this.compare = function( listItem ) {
		if(this.id == listItem.id) return 0;
		return this.id > listItem.id ? 1 : -1;
	}
}

function netblog_lister()
{
	//
	// DATA MEMBER
	//
	this.listObj = null;
	this.listObjDiv = null						/* upon call of setFormatMode with mode = list; make restore possible */
	
	this.list = new Array();
	this.isSortImmediate = false;
	this.appendMode = 2;						/* 0 := begin, 1 := middle (-> sort), 2:= end */
	this.submenuMode = 2;						/* 0 := none; 1 := left, 2 := right */
	this.formatMode = 0;						/* 0 := table/rows, 1:=unnumbered list, 2:= numbered list, 3:=XList */
	
	this.scopeID = '';
	this.objName = '';
	
	this.OnRemoveWaitFeedback = false;			/*  */
	this.OnRemoveMemoryElem = null;
	
	this.CBFormatElem = null;
	this.CBFormatElemObj = null;
	this.CBOnRemoveElem = null;
	this.CBOnRemoveElemObj = null;
	
	this.CBFormatRMenu = null;
	this.CBFormatRMenuCxt = null;
	
	//
	// PUBLIC MEMBER FUNCTIONS
	//
	this.init = function( listID, objName )
	{
		this.listObj = document.getElementById(listID);
		this.listObj.innerHTML = '';
		
		this.scopeID = listID+'_';
		this.objName = objName;
	}
	this.add = function( elem ) 
	{
		if( !this.hasElem(elem) ) {
			this.addEntry(elem);
			return true;
		} else return false;
	}
	this.addTop = function( elem )
	{
		var mode = this.appendMode;
		this.appendMode = 0;
		this.add(elem);
		this.appendMode = mode;
	}
	this.addBottom = function( elem )
	{
		var mode = this.appendMode;
		this.appendMode = 2;
		this.add(elem);
		this.appendMode = mode;
	}
	this.remove = function( id )
	{ 
		return this.removeEntryByID(id); 
	}
		
	this.removeOK = function(bool)
	{
		if( this.OnRemoveWaitFeedback && bool && this.hasElem(this.OnRemoveMemoryElem) ) {
			this.OnRemoveWaitFeedback = false;
			this.removeElem(this.OnRemoveMemoryElem);
			this.OnRemoveWaitFeedback = true;
		}
		if( this.OnRemoveWaitFeedback && !bool )
			this.OnRemoveMemoryElem = null;
	}
	this.sort = function( isDesc )
	{		
		for( i=0; i<this.list.length; i++ ) {
			var t = this.list[i];
			var p;
			for( p=i-1; p>=0; p-- ){
				if( t.compare(this.list[p]) < 0 )
					this.list[p+1] = this.list[p];
				else break;
			}
			this.list[p+1] = t;
		}		

		if( isDesc ) this.list.reverse();
		
		this.update();
	}
	this.clear = function()
	{
		while( this.list.length > 0 )					/* ensure remove feedback */
			this.removeEntry(0);
	}
	
	this.registerCBFormatElem = function( objCxt, objFunc, elem, callRemoveAsStr )
		{ this.CBFormatElem = objFunc; this.CBFormatElemObj = objCxt; }
	this.registerCBOnRemoveElem = function( obj, objFunc, elem )
		{ this.CBOnRemoveElem = objFunc; this.CBOnRemoveElemObj = obj; }
	this.registerCBFormatRMenu = function( funcCxt, objFunc, elem )
		{ this.CBFormatRMenu = objFunc; this.CBFormatRMenuCxt = funcCxt; }
			
	this.setSortImmediate = function( boolean )
		{ this.isSortImmediate = boolean; }
		
	this.setAppendMode = function( mode )
	{
		if( mode == 'begin' || mode == 0 )
			this.appendMode = mode;
		if( mode == 'middle' || mode == 1 )
			this.appendMode = mode;
		if( mode == 'end' || mode == 2 )
			this.appendMode = mode;
	}
	this.setSubmenuMode = function( mode )
	{
		if( mode == 'none' || mode == 0 )
			this.submenuMode = 0;		
		if( mode == 'left' || mode == 1 )
			this.submenuMode = 1;
		if( mode == 'right' || mode == 2 )
			this.submenuMode = 2;
		this.update();
	}
	this.setFormatMode = function( mode )
	{
		if( mode == 'table' || mode == 0 ) {
			this.formatMode = 0;
			if( this.listObjDiv != null )
				this.listObj = this.listObjDiv;
		} 
		if( mode == 'xlist' || mode == 3 ) {
			this.formatMode = 3;
			if( this.listObjDiv != null ) 
				this.listObj = this.listObjDiv;
		}
		if( mode == 'ulist' || mode == 1 ) {
			this.formatMode = 1;
			var listID = this.scopeID+'ulist';
			this.show();
			this.listObj.innerHTML = '<ul id="'+listID+'"></ul>';
			this.listObjDiv = this.listObj;
			this.listObj = document.getElementById(listID);
		}
		this.update();
	}
	
	this.setOnRemoveWaitFeedback = function(bool) 
		{ this.OnRemoveWaitFeedback = bool; }
	this.getOnRemoveWaitFeedback = function() 
		{ return this.OnRemoveWaitFeedback;}	
	
	
	
	
	//
	// PRIVATE MEMBER FUNCTIONS
	//
	this.addEntry = function( elem )
	{
		if( !this.hasElem(elem) ) {
			this.list.push(elem);

			if( this.appendMode == 0 )
				this.listObj.innerHTML = this.formatRow(elem) + this.listObj.innerHTML;
			else
				this.listObj.innerHTML += this.formatRow(elem) + '';;

			if( this.isSortImmediate || this.appendMode == 1 )
				this.sort(1,true);
			this.show();
			
			if( typeof(show) == 'function' ) {
				show( this.mkRowID(elem.id), 0, 1000 );
			}
		} else {
			var obj = document.getElementById( this.mkRowID(elem.id) );
			if( obj != undefined || obj != null )
				obj.innerHTML = this.formatStr(elem);
		}
	}
	this.removeEntryByID = function( id )
	{
		this.removeEntry( this.getElemIndex(id) );
	}
	this.removeEntry = function( index )
	{
		if( index < 0 || index >= this.list.length )
			return;
			
		var elem = this.list[index];
		
		if( this.OnRemoveWaitFeedback && this.CBOnRemoveElem != null ) {
			this.OnRemoveMemoryElem = elem;
			this.CBOnRemoveElem.call( this.CBOnRemoveElemObj, elem );
			return;
		}
		var i;
		for( i=index; i<this.list.length-1; i++ )
			this.list[i] = this.list[i+1];
		this.list.pop();
		this.update();
		if( this.list.length == 0 )
			this.hide();
		
		return true;
	}
	this.removeElem = function( elem )
	{
		if(elem == null) return true;
		this.removeEntry(this.getElemIndex(elem.id));			
	}
	

	this.isString = function( obj )
	{
		return ( typeof(obj) == "string" || obj instanceof String );
	}
	this.update = function() 
	{
		var t = '';		
		for( i=0; i<this.list.length; i++ )
			t += this.formatRow(this.list[i]);
		this.listObj.innerHTML = t;
		if( this.list.length > 0 )
			this.show();
		else this.hide();
	}
	
	
	this.formatRow = function( elem )
	{	
		var c;
		var f = this.formatStr(elem);
		if( this.formatMode == 1 ) {			
			c = '<li id="'+this.mkRowID(elem.id)+'">'+f+'</li>';
			
		} else if( this.formatMode == 3 ) {
			c = '<div class="xlist" id="'+this.mkRowID(elem.id)+'" style="" ';
			c += ' onmouseover="document.getElementById(\''+this.mkRowID(elem.id)+'-rgtmenu\').style.visibility = \'visible\'" ';
			c += ' onmouseout="document.getElementById(\''+this.mkRowID(elem.id)+'-rgtmenu\').style.visibility = \'hidden\'">';
			if(elem.removable)
				c += '<a class="del" onclick="'+this.objName+'.remove(\''+ elem.id +'\')"></a>';
			c += '<div style="float:right; visibility:hidden" id="'+this.mkRowID(elem.id)+'-rgtmenu">'+ this.formatRMenu(elem) +'</div>';
			c += '<div class="content">'+f+'</div></div>';
			
		} else {
			f = '<div class="content">'+f+'</div>';			
			var t = '<div class="submenu">';
			if(elem.removable)
				t += '<a class="del" onclick="'+this.objName+'.remove('+ elem.id +')" ></a>';
			t += '</div>' + f;			
			if( this.submenuMode == 1 )
				f = '<div class="menuLeft">' + t + '</div>';
			else if( this.submenuMode == 2 )
				f = '<div class="menuRight">' + t + '</div>';
			c = '<div class="row" id="'+ this.mkRowID(elem.id) +'">'+f+'</div>';
		}
		
		return c;
	}
	
	this.findElem = function( elem ) 
	{
		var i = this.getElemIndex(elem.id);
		if( i >= 0 )
			return this.list[i];
		else return new Array();
	}
	this.getElemIndex = function( id )
	{
		var i;
		for( i=0; i<this.list.length; i++ )
			if( this.list[i].id == id )
				return i
		return -1;		
	}
	this.findElemByID = function( id )
	{
		var i;
		for( i=0; i<this.list.length; i++ )
			if( this.list[i].id == id )
				this.list[i];
		return null;
	}
	
	this.hasElem = function( elem ) 
	{
		return elem != null ? this.getElemIndex(elem.id) >= 0 : false;
	}
//	this.getStrByArray = function( arrayRow ) { return arrayRow.length == 2 ? arrayRow[1] : '';}
//	this.getIDByArray = function( arrayRow ) { return arrayRow.length == 2 ? arrayRow[0] : '';}

	this.show = function() { this.listObj.style.display = 'block'; }
	this.hide = function() { this.listObj.style.display = 'none'; }

	this.mkRowID = function( id ) { return this.scopeID+'row'+id; }
	
	this.formatStr = function( elem ) 
	{ 
		if( this.CBFormatElem != null )
			return this.CBFormatElem.call( this.CBFormatElemObj, elem, this.objName+'.remove('+ elem.id +')' );		
		return "no formatter attached";
	}
	this.formatRMenu = function( elem )
	{
		if( this.CBFormatRMenu != null )
			return this.CBFormatRMenu.call( this.CBFormatRMenuCxt, elem );
		else return '';
	}
}

