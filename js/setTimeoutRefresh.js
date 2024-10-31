// JavaScript Document

var timerQueue = Array();
function setTimeoutRefresh( scopeID, code, delay )
{
	var i; var index = -1;
	for( i=0; i<timerQueue.length; i++ )
		if( timerQueue[i][0] == scopeID ) {
			clearTimeout( timerQueue[i][1] );
			index = i;
			break;
		}
	
	var timerID = setTimeout( code, delay );
	if( delay > 0 ) {
		if( index >= 0 )
			timerQueue[index] = [scopeID,timerID,code];
		else
			timerQueue.push( [scopeID,timerID,code] );
	}
}
