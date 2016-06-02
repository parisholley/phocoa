window.PHOCOA=window.PHOCOA||{};PHOCOA.namespace=function(){var b=arguments,g=null,e,c,f;for(e=0;e<b.length;e=e+1){f=b[e].split(".");g=PHOCOA;for(c=(f[0]=="PHOCOA")?1:0;c<f.length;c=c+1){g[f[c]]=g[f[c]]||{};g=g[f[c]]}}return g};PHOCOA.importJS=function(e,b,a){if(!PHOCOA.importJSCache){PHOCOA.importJSCache={}}if(PHOCOA.importJSCache[e]){return}PHOCOA.importJSCache[e]=true;if(1){var d=new Ajax.Request(e,{asynchronous:false,method:"get"});try{PHOCOA.sandbox(d.transport.responseText,b)}catch(c){if(typeof(console)!="undefined"&&console.warn){console.warn("importJS: "+e+" failed to parse: (errNo: "+c.number+")"+c.message)}}}};PHOCOA.YUI=function(b){this.pendingRequires=[];this.filter=null;this.currentlyProcessing=undefined;this.yuiLoader=new YAHOO.util.YUILoader();if(PHOCOA.YUILoader!==undefined){throw ("PHOCOA.YUI is a singleton. Do not instantiate it more than once")}PHOCOA.YUILoader=this;this.yuiLoader.scope=this;this.yuiLoader.onSuccess=this.onSuccess;b=b||{};var a=["onSuccess","onFailure","onTimeout","onProgress","scope","data"];$H(b).each(function(c){if(a.indexOf(c.key)!==-1){throw ("option "+c.key+" not allowed in PHOCOA.YUI constructur as it will be overridden.")}this.yuiLoader[c.key]=c.value}.bind(this))};PHOCOA.YUI.prototype={require:function(b,a){a=a||{};this.pendingRequires.push({require:b,options:a});this.loadNext()},loadNext:function(){if(this.currentlyProcessing){return}this.currentlyProcessing=this.pendingRequires.shift();if(this.currentlyProcessing===undefined){return}this.yuiLoader.require(this.currentlyProcessing.require);this.yuiLoader.insert()},onSuccess:function(){if(this.currentlyProcessing.options.onSuccess){if(this.currentlyProcessing.options.scope){this.currentlyProcessing.options.onSuccess.apply(this.currentlyProcessing.options.scope,[this.currentlyProcessing.data])}else{this.currentlyProcessing.options.onSuccess(this.currentlyProcessing.data)}}this.currentlyProcessing=undefined;this.loadNext()}};PHOCOA.sandbox=function(jsCode,globalNamespace,localNamespace){if(globalNamespace){if(!localNamespace){localNamespace=globalNamespace}eval(jsCode+"\n\nwindow."+globalNamespace+" = "+localNamespace+";")}else{eval(jsCode)}};PHOCOA.importCSS=function(a){var b=document.createElement("link");b.setAttribute("rel","stylesheet");b.setAttribute("type","text/css");b.setAttribute("href",a);document.getElementsByTagName("head")[0].appendChild(b)};PHOCOA.namespace("runtime");PHOCOA.runtime.addObject=function(b,c){PHOCOA.runtime.setupObjectCache();var a=c||b.id;if(!a){throw"No ID could be found."}if(0&&PHOCOA.runtime.objectList[a]){alert("error - cannot add duplicate object: "+a);return}PHOCOA.runtime.objectList[a]=b};PHOCOA.runtime.removeObject=function(a){PHOCOA.runtime.setupObjectCache();delete PHOCOA.runtime.objectList[a]};PHOCOA.runtime.setupObjectCache=function(){if(!PHOCOA.runtime.objectList){PHOCOA.runtime.objectList={}}};PHOCOA.runtime.getObject=function(b){PHOCOA.runtime.setupObjectCache();var a=null;if(PHOCOA.runtime.objectList[b]){a=PHOCOA.runtime.objectList[b]}return a};PHOCOA.namespace("WFRPC");PHOCOA.WFRPC=function(a,c,b){this.target="#page#";this.action=null;this.form=null;this.runsIfInvalid=false;this.invocationPath=null;this.transaction=null;this.isAjax=true;this.submitButton=null;this.method="get";this.hideErrorsBeforeExecute=true;this.fieldsToHideFromFormSubmission=[];this.callback={success:null,invalid:null,failure:null,scope:null,argument:null};if(a){this.invocationPath=a}if(c){this.target=c}if(b){this.action=b}return this};PHOCOA.WFRPC.prototype={successCallbackWrapper:function(o){var isValidationError=false;o.argument=this.callback.argument;switch(o.getResponseHeader("Content-Type").strip()){case"application/x-json-phocoa-wferrorsexception":isValidationError=true;case"application/x-json-phocoa-ui-updates":try{this.doPhocoaUIUpdatesJSON(eval("("+o.responseText+")"))}catch(e){alert("WFRPC doPhocoaUIUpdatesJSON() failed: "+e+" processing: "+o.responseText);return}break}if(isValidationError){if(typeof this.callback.invalid==="function"){this.callback.invalid.call(this.callback.scope,o)}}else{if(typeof this.callback.success==="function"){this.callback.success.call(this.callback.scope,o)}}},failureCallbackWrapper:function(a){a.argument=this.callback.argument;if(typeof this.callback.failure==="function"){this.callback.failure.call(this.callback.scope,a)}},actionURL:function(){return this.invocationPath},actionURLParams:function(d,a){d=d||[];a=a||false;var c=(a?"&":"");c+="__phocoa_rpc_enable=1";c+="&__phocoa_rpc_invocationPath="+encodeURIComponent(this.invocationPath);c+="&__phocoa_rpc_target="+encodeURIComponent(this.target);c+="&__phocoa_rpc_action="+this.action;c+="&__phocoa_rpc_runsIfInvalid="+this.runsIfInvalid;c+="&__phocoa_is_ajax="+this.isAjax;if(d.length){for(var e=0;e<d.length;e++){var b="__phocoa_rpc_argv_"+e;c+="&"+b+"="+(d[e]===null?"WFNull":encodeURIComponent(d[e]))}}c+="&__phocoa_rpc_argc="+d.length;return c},actionAsURL:function(a){return this.actionURL()+"?"+this.actionURLParams(a)},phocoaRPCParameters:function(b){b=b||[];var d={};d.__phocoa_rpc_enable=1;d.__phocoa_rpc_invocationPath=this.invocationPath;d.__phocoa_rpc_target=this.target;d.__phocoa_rpc_action=this.action;d.__phocoa_rpc_runsIfInvalid=this.runsIfInvalid;d.__phocoa_is_ajax=this.isAjax;if(b.length){for(var c=0;c<b.length;c++){var a="__phocoa_rpc_argv_"+c;if(b[c]&&typeof b[c]=="object"&&Object.isArray(b[c])){a+="[]"}d[a]=(b[c]===null?"WFNull":b[c])}}d.__phocoa_rpc_argc=b.length;return d},execute:function(){if(this.form){if(this.hideErrorsBeforeExecute){$$(".phocoaWFFormError").each(function(d){d.update(null)})}if(this.isAjax===false){var c=$(this.form);$H(this.phocoaRPCParameters(this.execute.arguments)).each(function(e){var d='<input type="hidden" name="'+e.key+'" value="'+e.value+'" />';Element.insert(c,d)});if(this.submitButton){var b='<input type="hidden" name="'+$(this.submitButton).name+'" value="'+$(this.submitButton).value+'" />';Element.insert(c,b)}this.fieldsToHideFromFormSubmission.each(function(d){var e=$(d);e&&e.disable()});c.submit()}else{this.fieldsToHideFromFormSubmission.each(function(d){var e=$(d);e&&e.disable()});this.transaction=$(this.form).request({method:this.method,parameters:this.phocoaRPCParameters(this.execute.arguments),onSuccess:this.successCallbackWrapper.bind(this),onFailure:this.failureCallbackWrapper.bind(this),onException:this.failureCallbackWrapper.bind(this)});this.fieldsToHideFromFormSubmission.each(function(d){var e=$(d);e&&e.enable()})}}else{if(this.isAjax){this.transaction=new Ajax.Request(this.actionURL(),{method:this.method,parameters:this.phocoaRPCParameters(this.execute.arguments),asynchronous:true,onSuccess:this.successCallbackWrapper.bind(this),onFailure:this.failureCallbackWrapper.bind(this),onException:this.failureCallbackWrapper.bind(this)})}else{var a=this.actionAsURL(this.execute.arguments);document.location=a}}return this.transaction},runScriptsInElement:function(el){var scriptEls=el.getElementsByTagName("script");for(idx=0;idx<scriptEls.length;idx++){var node=scriptEls[idx];window.eval(node.innerHTML)}},doPhocoaUIUpdatesJSON:function(updateList){var id,el;if(updateList.update){for(id in updateList.update){el=$(id);if(!el){if(console){console.warn("doPhocoaUIUpdatesJSON: could not update element: "+id+" because it was not found in the DOM.")}continue}el.update(updateList.update[id]);this.runScriptsInElement(el)}}if(updateList.replace){for(id in updateList.replace){el=$(id);if(!el){if(console){console.warn("doPhocoaUIUpdatesJSON: could not update element: "+id+" because it was not found in the DOM.")}continue}el.replace(updateList.replace[id]);this.runScriptsInElement(el)}}if(updateList.run){for(id=0;id<updateList.run.length;id++){window.eval(updateList.run[id])}}}};PHOCOA.namespace("WFAction");PHOCOA.WFAction=function(c,b){this.elId=c;this.eventName=b;this.callback=null;this.rpc=null;this.stopsEvent=true;var d=$(this.elId);var e=this.eventName;var a=(typeof d.type!=="undefined"?d.type.toLowerCase():null);if(Prototype.Browser.IE&&this.eventName==="change"&&(a==="checkbox"||a==="radio")){e="click"}Event.observe(d,e,this.yuiTrigger.bindAsEventListener(this));return this};PHOCOA.WFAction.prototype={stopEvent:function(a){Event.stop(a)},yuiTrigger:function(a){if(this.stopsEvent){this.stopEvent(a)}this.execute(a)},execute:function(c){var b=[],d;if(PHOCOA.widgets[this.elId].events[this.eventName].collectArguments){b=PHOCOA.widgets[this.elId].events[this.eventName].collectArguments()}d=b.slice(0);d.splice(0,0,c);if(this.rpc){if(typeof this.callback!=="function"&&typeof PHOCOA.widgets[this.elId].events[this.eventName].handleEvent==="function"){this.callback=PHOCOA.widgets[this.elId].events[this.eventName].handleEvent}if(this.callback){var a=this.callback.apply(this,d);if(a===false){return}}b.splice(0,0,Event.element(c).identify(),c.type);this.rpc.callback.argument=d;this.rpc.callback.success=this.rpcCallbackRouter.curry("ajaxSuccess");this.rpc.callback.invalid=this.rpcCallbackRouter.curry("ajaxInvalid");this.rpc.callback.failure=this.rpcCallbackRouter.curry("ajaxError");this.rpc.callback.scope=this;this.rpc.execute.apply(this.rpc,b)}else{if(this.callback){this.callback.apply(this,d)}else{if(typeof(console)!="undefined"&&console.warn){console.warn("Callback doesn't exist: PHOCOA.widgets."+c.target.identify()+".events."+c.type)}}}},rpcCallbackRouter:function(callbackName,o){var callbackF=null;if(typeof PHOCOA.widgets[this.elId].events[this.eventName][callbackName]==="function"){callbackF=PHOCOA.widgets[this.elId].events[this.eventName][callbackName]}if(!callbackF){return}var theResponse;var contentType=o.getResponseHeader("Content-Type").strip();switch(contentType){case"application/x-json":theResponse=eval("("+o.responseText+")");break;case"text/xml":theResponse=o.responseXML;break;case"text/plain":theResponse=o.responseText;break;default:theResponse=o.responseText;break}var cbArgs=this.rpc.callback.argument.slice(0);cbArgs.splice(0,0,theResponse);callbackF.apply(null,cbArgs)}};