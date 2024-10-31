// JavaScript Document

function setOpacity( id, opacityInt )
{
	var obj = document.getElementById(id);
	
	if( opacityInt > 100 ) opacityInt = 100;
	if( opacityInt < 0 ) opacityInt = 0;
	
	var opacityDec = opacityInt/100;
	
	//document.getElementById('terminal').innerHTML += id + ': ' + opacityInt + '<br />';	
	
	if(opacityInt < 1 ) opacityInt = 1;
	
	obj.style.opacity = opacityDec;
	obj.style.filter = "alpha(opacity=" + opacityInt + ")";
	
	
}
function getOpacity( id )
{
	var obj = document.getElementById(id);
	
}

function fadeOpacity( id, fromOpacity, toOpacity, time, fps)
{
	var steps = Math.ceil(fps * (time / 1000));
	var delta = (toOpacity - fromOpacity) / steps;

	fadeOpacityStep( id, 0, steps, fromOpacity, delta, (time / steps));
}

function fadeOpacityStep( id, stepNum, steps, fromOpacity, delta, timePerStep)
{
	var opInt =  Math.round(parseInt(fromOpacity) + (delta * stepNum));
	setOpacity( id, opInt );

	if (stepNum < steps)
		setTimeout("fadeOpacityStep('" + id + "', " + (stepNum+1) 
					+ ", " + steps + ", " + fromOpacity + ", "
					+ delta + ", " + timePerStep + ");", 
					  timePerStep);
	else if( opInt == 0 ) {
		document.getElementById(id).style.display = 'none';
		//document.getElementById('terminal').innerHTML += id + ': display none<br />';	
	}
}


function rollHeight( obj, fromHeight, toHeight, time, fps )
{
	var steps = Math.ceil(fps * (time / 1000));
	var delta = (toHeight - fromHeight) / steps;
	rollHeightStep( obj.id, 0, steps, fromHeight, delta, (time/steps) );
}
function rollHeightStep( id, stepNum, steps, fromHeight, delta, timePerStep )
{
	var hInt = Math.round( parseInt(fromHeight) + (delta * stepNum) );
	setHeight( document.getElementById(id), hInt );
	if( stepNum < steps )
		setTimeout("rollHeightStep('" + id + "', " + (stepNum+1) 
					+ ", " + steps + ", " + fromHeight + ", "
					+ delta + ", " + timePerStep + ");", 
					  timePerStep);
}
function setHeight( obj, height )
{	
	obj.style.height = height + 'px';
}
var listObjsHeight = [];
function rollHeightSave( obj )
{
	listObjsHeight.push( {'obj':obj, 'height': obj.offsetHeight} );
}
function objHeightRecover( obj )
{
	var i;
	for( i=0; i<listObjsHeight.length; i++ ) {
		if( listObjsHeight[i].obj == obj ) {
			var i = listObjsHeight.splice(i,1);
			return i[0].height;
		}
	}
}





function show( id, delay, speed )
{
	//if( document.getElementById(id).style.display == 'block' ) return;					// ADDED VER1.4
	
	if( delay > 0 ) {
		setTimeout( 'show(\''+id+'\',0,'+speed+')', delay );
	} else {
		var o = document.getElementById(id);
		if( o!=null ) {
			if(o.style.display == 'none' || o.style.display == '') {
				setOpacity(id,0);
				o.style.display = 'block';
			}
			fadeOpacity(id,0,100,speed,25);			
		} else {
			//alert('missing element: ' + id);
		}
	}
}
function hide( id, delay, speed )
{
	if( delay > 0 )
		setTimeout( 'hide(\''+id+'\',0,'+speed+')',delay );
	else 
		if( document.getElementById(id).style.display != 'none' )
			fadeOpacity( id, 100, 0, speed, 25 );
}
function nbHideToOpac( id, delay, speed, finalOpac )
{
	if( delay > 0 )
		setTimeout( 'nbHideToOpac(\''+id+'\',0,'+speed+','+finalOpac+')',delay );
	else 
		if( document.getElementById(id).style.display != 'none' ) {
			fadeOpacity( id, 100, finalOpac, speed, 25 );
		}
}


function showPlus( id, delay, speed, opacity, roll)
{	
	if( nbsh_isRun(id) ) {
		setTimeout( 'showPlus(\''+id+'\','+delay+','+speed+','+opacity+','+roll+')', 100 );
		return;
	}
	
	if( delay > 0 ) {
		var timerid = nbsh_getTimerid(id);
		if( timerid != null ) clearTimeout( timerid );
		
		timerid = setTimeout( 'showPlus(\''+id+'\',0,'+speed+','+opacity+','+roll+')', delay );
		nbsh_write( timerid, id );
		//setTimeoutRefresh( ''+id, 'showPlus(\''+id+'\',0,'+speed+','+opacity+','+roll+')', delay );
	} else {
		nbsh_setRun(id,true);
		
		var obj = document.getElementById(id);
		if( obj != null ) {
			if( obj.style.display == 'none' || obj.style.display == '' ) {
				setOpacity(id,0);
				obj.style.display = 'block';
			}	
			if( roll ) {
				var h = obj.offsetHeight;
				if( h == 0 ) h = objHeightRecover(obj);
				rollHeight(obj,0,h,speed/2,25);
			}		
			if( opacity )
				fadeOpacity(id,0,100,speed,25);	
		}
		setTimeout( 'nbsh_rm(\''+id+'\')', speed );
	}
}
function hidePlus( id, delay, speed, opacity, roll )
{
	if( nbsh_isRun(id) ) {
		setTimeout( 'hidePlus(\''+id+'\','+delay+','+speed+','+opacity+','+roll+')',100 );
		return;
	}
	if( delay > 0 ) {
		var timerid = nbsh_getTimerid(id);
		if( timerid != null ) clearTimeout( timerid ); 
		
		timerid = setTimeout( 'hidePlus(\''+id+'\',0,'+speed+','+opacity+','+roll+')',delay );
		nbsh_write( timerid, id );
		//setTimeoutRefresh( ''+id, 'hidePlus(\''+id+'\',0,'+speed+','+opacity+','+roll+')', delay );
	} else {
		nbsh_setRun(id,true);
		
		var obj = document.getElementById(id);
		if( obj.style.display != 'none' ) {
			if( roll ) {
				rollHeightSave( obj );				
				rollHeight( obj, obj.offsetHeight, 0, speed, 25 );			
			}
			if( opacity )
				fadeOpacity( id, 100, 0, speed, 25 );
		}
		
		setTimeout( 'nbsh_rm(\''+id+'\')', speed );
	}
}
function stopshPlus(id)
{
	var timerid = nbsh_getTimerid(id);
	if( timerid != null ) clearTimeout( timerid ); 
}

var shlist = [];
function nbsh_isInList( id )
{
	return nbsh_getIndex(id) >= 0;
}
function nbsh_getIndex( id )
{
	var i;
	for( i=0; i<shlist.length; i++ )
		if( shlist[i][1] == id )
			return i;
	return -1;
}
function nbsh_getTimerid( id ) { var i=nbsh_getIndex(id); return i>=0 ? shlist[i][0] : null; }
function nbsh_isRun( id ) { var i=nbsh_getIndex(id); return i>=0 ? shlist[i][2] == 1 : false; }
function nbsh_add( timerid, id ) { if(!nbsh_isInList(id)) shlist.push( [timerid,id,0] ); }
function nbsh_rm( id ) 
{
	var i = nbsh_getIndex(id);
	if( i >= 0 ) shlist.splice(i,1);
}
function nbsh_write( timerid, id ) 
{
	var i = nbsh_getIndex(id);
	if( i >= 0 ) shlist[i][0] = timerid;
	else shlist.push( [timerid,id,0] );
}
function nbsh_setRun( id, bool )
{
	var i = nbsh_getIndex(id);
	if( i>=0 ) shlist[i][2] = ( bool ? 1 : 0 );
}