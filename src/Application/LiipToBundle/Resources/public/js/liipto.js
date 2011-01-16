var E = YAHOO.util.Event;
var D = YAHOO.util.Dom;

//FIXME: Document it

YAHOO.namespace("liipto");

YAHOO.liipto.checkCodeReverse = function() {

    var keypressTimer = null;
    var codeCheckRequest = null;
    var codeCheckResults = [];
    var codeCheckOld = '';
    var apiURL = 'api/rchkrev/?url=';
    
	/* duplicate code start (with YAHOO.liipto.checkCode)
	 * 
	 * If anyone knows a way to improve this, tell me
	 * 
	 * (due to the lack of protected memebers in JS, not easy
	 *  possible without making all methods and vars public
	 *  and then use YAHOO.lang.augmentObject)
	 */
	
	var handleSuccess = function(o) {
        D.setStyle('codeOkSpinner', 'visibility', 'hidden');
		var result = YAHOO.lang.JSON.parse(o.responseText);
        if (result.alias) {
            codeRed(result.alias);
        } else {
            codeGreen();
        }
        codeRevCan(result.revcan);
        codeCheckResults[o.argument.val] = result;
    };
	
	
	
    var handleFailure = function(o) {
        console.log("FAILURE " + alert(o.statusText));
    };
    
    var codeKeypressAsync = function() {
        YAHOO.lang.later(1,this,codeKeypress);
    };
    
    var codeKeypress = function() {
        var value = YAHOO.lang.trim(D.get('url').value);
		if (keypressTimer) {
            keypressTimer.cancel();
        }
        
        if (codeCheckRequest && YAHOO.util.Connect.isCallInProgress(codeCheckRequest)) {
            YAHOO.util.Connect.abort(codeCheckRequest); 
			delete codeCheckRequest;
        }
        
        if (value === '') {
            D.setStyle("codeOk","background-color","white");
            D.setStyle('codeOkSpinner','visibility', 'hidden');
            return; 
        }
		
        if (YAHOO.lang.isUndefined(codeCheckResults[value])) { 
           keypressTimer = YAHOO.lang.later(200,this,request);
        } else {
            D.setStyle('codeOkSpinner','visibility', 'hidden');
            if (codeCheckResults[value] && codeCheckResults[value].alias) {
				codeRed(codeCheckResults[value].alias);
            } else {
                codeGreen();
            }
            codeRevCan(codeCheckResults[value].revcan);
        }
            
    };
	
	var request = function() {
        var value = YAHOO.lang.trim(D.get('url').value);
        D.setStyle('codeOkSpinner','visibility', 'visible');
        var sUrl = apiURL + encodeURIComponent(value);
        var callback = {
            success: handleSuccess,
            failure: handleFailure,
            argument: {'val':value}
        };

        if (codeCheckRequest && YAHOO.util.Connect.isCallInProgress(codeCheckRequest)) {
            YAHOO.util.Connect.abort(codeCheckRequest); 
            delete codeCheckRequest;
        }

        codeCheckRequest = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
    };

	/* duplicate code end */
    
    var codeRed = function(value) {
        if (!(D.get('code').getAttribute('disabled') )) {
            codeCheckOld = D.get('code').value;
        }
        if (!value) {
            value = '';
        }
        D.get('code').value = value;
        D.get('code').setAttribute('disabled','true');
        D.setStyle("codeOk","background-color","red");
    };
    
    var codeGreen = function() {
		if (D.get('code').getAttribute('disabled') == 'true') {
			D.get('code').value = codeCheckOld;
			D.get('code').removeAttribute('disabled');
		}
		D.setStyle("codeOk","background-color","green");
    };
    
    var codeRevCan = function(value) {
          if (value) {
            D.get('revcanurl').firstChild.nodeValue = value;
            D.get('revcanurl').setAttribute('href', value);
            D.setStyle('revcan','visibility','visible');
            
        } else {
            D.setStyle('revcan','visibility','hidden');
        }
    }
    return {
      init: function() {
	  	 codeKeypress();
		 E.addListener("url","keyup",codeKeypressAsync);
		 
      }
    };
}();

YAHOO.liipto.checkCode = function() {

    var keypressTimer = null;
    var codeCheckRequest = null;
    var codeCheckResults = [];
    var urlAPI = "/api/chk/";
	
    var handleSuccess = function(o) {
        D.setStyle('codeOkSpinner','visibility', 'hidden');
        var result = YAHOO.lang.JSON.parse(o.responseText);
        if (result) {
            codeRed();
        } else {
            codeGreen();
        }
        
        codeCheckResults[o.argument.val] = result;
    };
    
    
    var handleFailure = function(o) {
        console.log("FAILUre " + alert(o.statusText)); 
    };
    
    var codeKeypressAsync = function() {
        YAHOO.lang.later(1,this,codeKeypress);
    };
    
    var codeKeypress = function(){
	
		var value = YAHOO.lang.trim(D.get('code').value);
		
		if (keypressTimer) {
			keypressTimer.cancel();
		}
		
		if (codeCheckRequest && YAHOO.util.Connect.isCallInProgress(codeCheckRequest)) {
			YAHOO.util.Connect.abort(codeCheckRequest);
			delete codeCheckRequest;
		}
		
		if (value === '') {
			D.setStyle("codeOk", "background-color", "white");
			D.setStyle('codeOkSpinner', 'visibility', 'hidden');
			return;
		}
		
	
        if (YAHOO.lang.isUndefined(codeCheckResults[value])) { 
           keypressTimer = YAHOO.lang.later(200,this,request);
        } else {
            D.setStyle('codeOkSpinner','visibility', 'hidden');
            if (codeCheckResults[value]
                && (codeCheckResults[value].alias
                    || codeCheckResults[value].alias == undefined)
            ) {
                codeRed();
            } else {
                codeGreen();
            }
        }
            
    };
    
    var codeRed = function() {
        D.setStyle("codeOk","background-color","red");
    };
    
    var codeGreen = function() {
        D.setStyle("codeOk","background-color","green");
    };
	
	var request = function() {
        var value = YAHOO.lang.trim(D.get('code').value);
        D.setStyle('codeOkSpinner','visibility', 'visible');
        var sUrl = urlAPI + value;
        var callback = {
            success: handleSuccess,
            failure: handleFailure,
            argument: {'val':value}
        };

        if (codeCheckRequest && YAHOO.util.Connect.isCallInProgress(codeCheckRequest)) {
            YAHOO.util.Connect.abort(codeCheckRequest);
            delete codeCheckRequest; 
        }
        codeCheckRequest = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
    };

    

    return {
      init: function() {
         E.addListener("code","keyup",codeKeypressAsync);
      }
    };
}();

YAHOO.liipto.init = function() {
    YAHOO.liipto.checkCode.init();
    YAHOO.liipto.checkCodeReverse.init();

};

 
E.onDOMReady(YAHOO.liipto.init);

