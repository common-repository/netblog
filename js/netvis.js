// JavaScript Document

function netblogNetVis() {
	this.name = '';
	this.nameCxt = null;
	this.netvisObj = null;
	this.netvisAreaObj = null;
	this.itemLeftObj = null;
	this.itemRightObj = null;
	this.itemMidObj = null;
	this.itemMenuLeftObj = null;
	this.itemMenuRightObj = null;
	this.itemThumbObj = null;
	this.itemTitleObj = null;
	this.itemDescObj = null;
	this.itemDetailsObj = null;
	
	this.currentPost = 0;
	
	this.delimMain = '';
	this.delimSub = '';
	
	this.pivotX = 0;
	this.pivotY = 0;
	this.pivotMovable = false;
	this.pivotXOrg = 0;
	this.pivotYOrg = 0;
	
	this.mouseX = null;
	this.mouseY = null;
	this.mouseXAccum = 0;
	this.mouseYAccum = 0;
	
	this.autoCenter = true;
	
	this.init = function() {
		this.loadItem(this.currentPost);
		var o = this;
		this.netvisObj.onmousedown = function() { o.onmousedown(); }
		this.netvisObj.onmouseup = function() { o.onmouseup(); }
		this.netvisObj.onmousemove = function(e) { o.onmousemove(e); }
	}
	
	this.onmousedown = function() {
		this.pivotMovable = !this.pivotMovable;
		if(!this.pivotMovable) {
			this.mouseX = this.mouseY = null;
		}
	}
	this.onmouseup = function() {
		//this.pivotMovable = false;
		//this.mouseX = this.mouseY = null;
	}
	this.onmousemove = function(e) {
		if(this.pivotMovable) {
			/* GET CURRENT MOUSE POSITION */
			var posx = 0;
			var posy = 0;
			if (!e) var e = window.event;
			if (e.pageX || e.pageY) 	{
				posx = e.pageX;
				posy = e.pageY;
			}
			else if (e.clientX || e.clientY) 	{
				posx = e.clientX + document.body.scrollLeft
					+ document.documentElement.scrollLeft;
				posy = e.clientY + document.body.scrollTop
					+ document.documentElement.scrollTop;
			}
			if(this.mouseX==null) {
				this.mouseX = posx;
				this.pivotXOrg = this.pivotX;
			}
			if(this.mouseY==null) {
				this.mouseY = posy;
				this.pivotYOrg = this.pivotY;
			}
			
			this.movePivotAbs( 2.0*(posx - this.mouseX), 2.0*(posy - this.mouseY) );
			
			this.mouseXAccum += posx;
			this.mouseYAccum += posy;			
			
			
			
			/* SET NEW MOUSE COORDS */
			
			
			this.print();
		}
	}
	
	this.calcPivotX = function() {
		
	}
	
	this.movePivotAbs = function(x,y) {
		this.pivotX = this.pivotXOrg + x;
		this.pivotY = this.pivotYOrg + y;
//		this.pivotX = Math.max(  Math.min(this.pivotX - this.pivotXOrg,0.7*this.getViewPortX()), 0.5*this.getViewPortX() - this.netvisAreaObj.offsetWidth);
//		this.pivotY = Math.max(  Math.min(this.pivotY - this.pivotYOrg,0.7*this.getViewPortY()), -0.5*this.getViewPortY() + this.netvisAreaObj.offsetHeight);
		
		this.netvisAreaObj.style.left = this.pivotX + 'px';
		this.netvisAreaObj.style.top = this.pivotY + 'px';
		
		document.getElementById('mm-delta').value = x + 'x, ' + y + 'y';
		document.getElementById('pv-off').value = this.netvisAreaObj.offsetLeft + 'x, ' + this.netvisAreaObj.offsetTop + 'y';
		document.getElementById('pv-stl').value = this.netvisAreaObj.style.left + ' left, ' + this.netvisAreaObj.style.top + ' top';
		document.getElementById('rf-off').value = this.getReferenceX() + 'x, ' + this.getReferenceY() + 'y';
	}
	
	this.movePivotOrgRel = function(x,y) {
		document.getElementById('mm-accum').value = x + 'x, ' + y + 'y';

		this.pivotXOrg += x;
		this.pivotYOrg += y;
		if(this.mouseX!=null)
			this.mouseX += x;
		if(this.mouseY!=null)
			this.mouseY += y;
		
		//this.movePivotAbs( (this.pivotX - x), (this.pivotY - y) );
	
	}
	
	this.getReferenceX = function() {
		return this.itemThumbObj.offsetLeft + this.itemMidObj.offsetLeft + this.itemThumbObj.offsetWidth*0.5;
	}
	this.getReferenceY = function() {
		return this.itemThumbObj.offsetTop + this.itemMidObj.offsetTop + this.itemThumbObj.offsetHeight*0.5;
	}
	this.getViewPortX = function() {
		return this.netvisObj.offsetWidth;
	}
	this.getViewPortY = function() {
		return this.netvisObj.offsetHeight;
	}
	
	this.resetPosition = function() {
		this.pivotXOrg = this.pivotYOrg = 0;
		this.movePivotAbs(0,0);
	}
	
	this.centerReference = function() {
		this.pivotXOrg = this.pivotYOrg = 0;
		this.movePivotAbs( 0.48*this.netvisObj.offsetWidth - this.getReferenceX(), 0.3*this.netvisObj.offsetHeight - this.getReferenceY() );
	}
	
	this.loadItem = function( postID ) {
		var nm = this.loadItemProcess;
		var o = this;
		this.onmouseup();
		this.pivotMovable = false;
		this.mouseX = this.mouseY = null;
		
		nbHideToOpac( this.itemLeftObj.id, 10, 250, 1 );
		nbHideToOpac( this.itemRightObj.id, 10, 250, 1 );
		jQuery(document).ready(function($) {
			var data = {
				action: 'netblog_netvis_loaditem',
				postID: postID
			};
			jQuery.post(ajaxurl, data, function(r) {				
				nm.call( o, r );
			});
		});	
	}
	
	this.loadItemProcess = function( xml ) {
		var o = this;
		var refXOrg = this.getReferenceX();
		var refYOrg = this.getReferenceY();		
		
		
		/* ITEM INFOS */
			jQuery(xml).find("Post").each(function() {
				o.itemTitleObj.innerHTML = jQuery(this).attr('title');
				o.itemDescObj.innerHTML = jQuery(this).attr('description');
				o.itemMenuLeftObj.innerHTML = '<a href="'+ jQuery(this).attr('editlink') +'">Edit</a>';
				o.itemMenuRightObj.innerHTML = '<a href="'+ jQuery(this).attr('url') +'">View</a>';
				
				var dt = ""; 
				var auth = "";
				jQuery(xml).find("Author").each(function() {
					if(auth.length > 0) auth += ', ';
					auth += jQuery(this).attr('nickname');
				});
				dt += '<tr><td class="key">Author</td><td class="val">'+ auth +'</td></tr>';
				dt += '<tr><td class="key">Categories</td><td class="val">none</td></tr>';
				dt += '<tr><td class="key">Date</td><td class="val">'+ jQuery(this).attr('date') + ', ' + jQuery(this).attr('post_status') +'</td></tr>';
				dt += '<tr><td class="key">Comments</td><td class="val">'+ jQuery(this).attr('comments') +', ' + jQuery(this).attr('comment_status') +'</td></tr>';
				
				o.itemDetailsObj.innerHTML = '<table><tbody>' + dt + '</tbody></table>';
			});
		
		/* OUTGOING */
			var outPost = "";
			jQuery(xml).find("OutPosts").find("OutPost").each(function() {
				outPost += '<li onclick="'+o.name+'.loadItem(\''+ jQuery(this).attr('post_id') +'\')">'+ jQuery(this).attr('title') +'</li>';
			});
			if( outPost.length > 0 ) {
				outPost = '<div class="box-yellow box-color"> <div class="title">To Intern Post</div> <ul>' + outPost + '</ul> </div>';
			}
			
			var outPage = "";
			jQuery(xml).find("OutPages").find("OutPage").each(function() {
				outPage += '<li onclick="'+o.name+'.loadItem(\''+ jQuery(this).attr('post_id') +'\')">'+ jQuery(this).attr('title') +'</li>';
			});
			if( outPage.length > 0 ) {
				outPage = '<div class="box-orange box-color"> <div class="title">To Intern Page</div> <ul>' + outPage + '</ul> </div>';
			}
			
			var outLink = "";
			jQuery(xml).find("OutLinks").find("OutLink").each(function() {
				outLink += '<li>'+ jQuery(this).attr('title') +'</li>';
			});
			if( outLink.length > 0 ) {
				outLink = '<div class="box-blue box-color"> <div class="title">To Extern Resource</div> <ul>' + outLink + '</ul> </div>';
			}
			this.itemRightObj.innerHTML = outPost + outPage + outLink + '&nbsp;';
		
		
		/* INCOMING */
			var inPost = "";
			jQuery(xml).find("InPosts").find("InPost").each(function() {
				inPost += '<li onclick="'+o.name+'.loadItem(\''+ jQuery(this).attr('post_id') +'\')">'+ jQuery(this).attr('title') +'</li>';
			});
			if( inPost.length > 0 ) {
				inPost = '<div class="box-yellow box-color"> <div class="title">From Intern Post</div> <ul>' + inPost + '</ul> </div>';
			}
			
			var inPage = "";
			jQuery(xml).find("InPages").find("InPage").each(function() {
				inPage += '<li onclick="'+o.name+'.loadItem(\''+ jQuery(this).attr('post_id') +'\')">'+ jQuery(this).attr('title') +'</li>';
			});
			if( inPage.length > 0 ) {
				inPage = '<div class="box-orange box-color"> <div class="title">From Intern Page</div> <ul>' + inPage + '</ul> </div>';
			}
			
			var inPing = "";
			jQuery(xml).find("InPingbacks").find("InPingback").each(function() {
				inPing += '<li>'+ jQuery(this).attr('title') +'</li>';
			});
			if( inPing.length > 0 ) {
				inPing = '<div class="box-blue box-color"> <div class="title">From Pingback</div> <ul>' + inPing + '</ul> </div>';
			}
			
			var inBlog = "";
			jQuery(xml).find("InBlogsearchs").find("InBlogsearch").each(function() {
				inBlog += '<li>'+ jQuery(this).attr('title') +'</li>';
			});
			if( inBlog.length > 0 ) {
				inBlog = '<div class="box-blue box-color"> <div class="title">From Blogsearch</div> <ul>' + inBlog + '</ul> </div>';
			}
			this.itemLeftObj.innerHTML = inPost + inPage + inPing + inBlog + '&nbsp;';			
		
		
		this.positionElements();
		if(this.autoCenter)
			this.centerReference();
		else 
			this.movePivotOrgRel( (this.getReferenceX()-refXOrg), (this.getReferenceY()-refYOrg) );
		
		show( this.itemLeftObj.id, 10, 250 );
		show( this.itemRightObj.id, 10, 250 );
	}
	
	
	this.positionElements = function() {
		var heightMax = Math.max(this.itemLeftObj.offsetHeight, this.itemRightObj.offsetHeight);
		heightMax = Math.max( heightMax, this.itemMidObj.offsetHeight );
		
		this.itemLeftObj.style.top = (heightMax - this.itemLeftObj.offsetHeight)/2 + 'px';
		this.itemMidObj.style.top = (heightMax - this.itemMidObj.offsetHeight)/2 + 'px';
		this.itemRightObj.style.top = (heightMax - this.itemRightObj.offsetHeight)/2 + 'px';	
		
		document.getElementById('pv-off').value = this.netvisAreaObj.offsetLeft + 'x, ' + this.netvisAreaObj.offsetTop + 'y';
		document.getElementById('pv-stl').value = this.netvisAreaObj.style.left + ' left, ' + this.netvisAreaObj.style.top + ' top';
		document.getElementById('rf-off').value = this.getReferenceX() + 'x, ' + this.getReferenceY() + 'y';
//		var box = document.getElementById('box');
//		box.innerHTML =  pivotXOld + 'x' + pivotYOld + '<br />';		
//		box.style.width = this.pivotX + 'px';
//		box.style.height = this.pivotY + 'px';
//		box.innerHTML += this.pivotX + 'x' + this.pivotY;
		//this.netvisAreaObj.style.left =  100 + 'px';
	}
	
	
	this.print = function() {
//		var box = document.getElementById('box');
//		box.style.width = this.getReferenceX() + 'px';
//		box.style.height = this.getReferenceY() + 'px';
	}
}