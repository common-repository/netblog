
function nbAutocompleteItem() {
	this.id = 0;					// this is required, but filled in by nbAutocompleteGroup(); DO NOT edit
	this.title = '';
	this.value = '';
}
function nbAutocompleteGroup() {
	this.id = 0;					// this is required, but filled in by nbAutocomplete(); DO NOT edit
	this.name = '';
	this.title = '';
	this.value = '';
	this.items = new Array();
	
	this.addItem = function( itemObj ) {
		if( !this.hasItem(itemObj) ) {
			itemObj.id = this.items.length;
			this.items.push(itemObj);
			return true;
		} else return false;
	}
	this.hasItem = function( itemObj ) {
		for( var i=0; i<this.items.length; i++ )
			if(this.items[i].id == itemObj.id)			/* modified with 2.0b3 */
				return true;
		return false;
	}
	this.clearItems = function() {
		this.items = new Array();
	}
}


function nbAutocomplete() {
	// PUBLIC METHODS
	this.addGroup = function( groupObj ) {
		if( !this.hasGroup(groupObj) ) {
			groupObj.id = this.groups.length;
			this.groups.push(groupObj);
			return true;
		} else return false;
	}	
	this.hasGroup = function( groupObj ) {
		for( var i=0; i < this.groups.length; i++ ) {
			if(this.groups[i].name == groupObj.name)
				return true;
		}
		return false;
	}
	this.clearGroups = function() {
		this.groups = new Array();
	}
	this.clearGroupItems = function() {
		for( var i=0; i < this.groups.length; i++ )
			this.groups[i].clearItems();
	}
	this.getGroupByName = function( groupName ) {
		for( var i=0; i < this.groups.length; i++ ) {
			if(this.groups[i].name == groupName)
				return this.groups[i];
		}
		return null;
	}
	this.render = function() {
		var out = '';
		for(var i=0; i<this.groups.length; i++) {
			if(this.groups.length > 1)
				out += '<div class="group">' + this.groups[i].items.length +' '+ this.groups[i].title + '</div>';
			for(var ig=0; ig<this.groups[i].items.length; ig++) {
				var nitem = this.groups[i].items[ig];
				var id = this.name + i + ig;
				out += '<div id="'+id+'" class="elem" onclick="getNbACObject('+this.id+').clickedItem('+i+','+ig+');">'+ nitem.title +'</div>';
			}
		}

		this.loadIcoObj.style.visibility = 'hidden';
		this.acObj.innerHTML = out;
		
		if(out.length == 0)
			this.blur();
		else this.focus();
	}
	
	
	// PUBLIC DATA
	this.init = function( inputID, parentNodeID, defaultValue ) {
		_this = this;
		this.id = nbACObjects.length;
		nbACObjects.push(this);
		
		this.defaultInputValue = defaultValue;
		
		var parent = document.getElementById(parentNodeID);
		var acID = inputID + '-acbox';
		var ajaxID = inputID + '-ico-ajaxloader';

		parent.innerHTML += " <span class=\"nbinput-ajaxloader\" id=\""+ajaxID+"\" >" +
						"</span> <div class=\"nbacbox\" id=\""+ acID +"\"></div>";
		this.inputObj = document.getElementById(inputID);
		this.acObj = document.getElementById(acID);
		this.loadIcoObj = document.getElementById(ajaxID);
		this.lastInputValue = this.inputObj.value;
		
		if(this.inputObj.offsetWidth > this.acObj.style.maxWidth) {
			this.acObj.style.maxWidth = this.inputObj.offsetWidth + 'px';
			this.acObj.style.width = this.inputObj.offsetWidth + 'px';
		}
		this.acObj.style.left = this.inputObj.offsetLeft + 'px';
		this.acObj.style.top = this.inputObj.offsetTop + this.inputObj.offsetHeight + 'px';
		
		this.loadIcoObj.style.visibility = 'hidden';
		this.inputObj.value = this.defaultInputValue;
		this.inputObj.style.color = 'gray';
		
		var obj = this;		
		if( window.addEventListener ) {
			this.inputObj.addEventListener('keyup', function() { obj.keyupDelay();}, false );
			this.inputObj.addEventListener('focus', function() { obj.focus();}, false );
			this.inputObj.addEventListener('blur', function() { obj.blur();}, false );	
		} else if( window.attachEvent ) { // LISTNER - IE5+
			this.inputObj.attachEvent('onkeyup', function() { obj.keyupDelay();}, false );
			this.inputObj.attachEvent('onfocus', function() { obj.focus();}, false );
			this.inputObj.attachEvent('onblur', function() { obj.blur();}, false );
		}
	}
	
	this.keyupDelay = function() {
		if(this.inputObj.value.trim() == this.lastInputValue || this.inputObj.value.length < 3)
			return;
		if(this.timerKeyup != 0)
			clearTimeout(this.timerKeyup);
		this.timerKeyup = setTimeout(this.keyupDelayed,250);		
	}
	
	this.keyupDelayed = function() {
		_this.keyup();
	}
	
	this.keyup = function() {
		if(this.inputObj.value.trim() == this.lastInputValue || this.inputObj.value.length < 3)
			return;
		
		if(this.CBLoader != null && this.CBLoaderCxt != null) {
			this.loadIcoObj.style.visibility = 'visible';
			this.CBLoader.call(this.CBLoaderCxt, this);			
		} else {
			var nitem = new nbAutocompleteItem();
			nitem.title = this.inputObj.value;
			nitem.value = this.inputObj.value;
			nitem.id = Number(new Date());
			this.groups[0].addItem(nitem);
			this.render();
		}
		
		this.lastInputValue = this.inputObj.value.trim();
	}
	this.focus = function() {
		if(this.inputObj.value == this.defaultInputValue) {
			this.inputObj.value = '';
			this.inputObj.style.color = 'black';
		}
		
		var num = 0;
		for( var i=0; i < this.groups.length; i++ )
			num += this.groups[i].items.length;
		if(num>0) {
			this.acObj.style.left = this.inputObj.offsetLeft + 'px';
			this.acObj.style.top = this.inputObj.offsetTop + this.inputObj.offsetHeight + 1 + 'px';
			this.acObj.style.display = 'block';
		}
	}
	this.blur = function() {	
		nbCreateCookie("nbACBox_"+this.name,this.inputObj.value,null);
		if(this.inputObj.value.length == 0) {
			this.inputObj.style.color = 'gray';
			this.inputObj.value = this.defaultInputValue;
		}
		setTimeout( 'document.getElementById(\''+this.acObj.id+'\').style.display = "none"', 150 );
	}
	this.clickedItem = function(groupIndex,itemIndex) {
		var nitem = this.groups[groupIndex].items[itemIndex];
		this.inputObj.value = nitem.value;
		if(this.CBClickedItem != null && this.CBClickedItemCxt!=null)
			this.CBClickedItem.call(this.CBClickedItemCxt,nitem);
	}
	
	this.rand = function(l,u) {
		return Math.floor((Math.random() * (u-l+1))+l);
	}
	
	this.registerCBLoader = function( context, funcName, nbAutocompleteObj ) {
		this.CBLoader = funcName;
		this.CBLoaderCxt = context;
	}
	this.registerCBClickedItem = function( context, funcName, nbAutocompleteItemObj ) {
		this.CBClickedItem = funcName;
		this.CBClickedItemCxt = context;
	}
	
	this.recoverPreviousSession = function() {
		var val = nbReadCookie("nbACBox_"+this.name);
		if(val != null && val.length > 0) {
			this.inputObj.value = val;
			this.inputObj.style.color = 'black';
		}
	}
	
	
	// PRIVATE DATA
	this.groups = new Array();
	this.inputObj = null;
	this.acObj = null;
	this.loadIcoObj = null;
	this.lastInputValue = '';	
	this.defaultInputValue = '';
	
	this.CBLoader = null;
	this.CBLoaderCxt = null;
	this.CBClickedItem = null;
	this.CBClickedItemCxt = null;
	
	this.timerKeyup = 0;
	this.name = '';
	this.id = 0;
	var _this = null;
}

var nbACObjects = new Array();
function getNbACObject(id) {
	return nbACObjects[id];
}