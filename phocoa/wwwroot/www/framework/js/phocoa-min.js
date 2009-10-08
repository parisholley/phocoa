window.PHOCOA=window.PHOCOA||{};PHOCOA.namespace=function(){var A=arguments,E=null,C,B,D;for(C=0;C<A.length;C=C+1){D=A[C].split(".");E=PHOCOA;for(B=(D[0]=="PHOCOA")?1:0;B<D.length;B=B+1){E[D[B]]=E[D[B]]||{};E=E[D[B]]}}return E};PHOCOA.importJS=function(E,B,A){if(!PHOCOA.importJSCache){PHOCOA.importJSCache={}}if(PHOCOA.importJSCache[E]){return }PHOCOA.importJSCache[E]=true;if(1){var D=new Ajax.Request(E,{asynchronous:false,method:"get"});try{PHOCOA.sandbox(D.transport.responseText,B)}catch(C){if(typeof (console)!="undefined"&&console.warn){console.warn("importJS: "+E+" failed to parse: (errNo: "+C.number+")"+C.message)}}}};PHOCOA.YUI=function(B){this.pendingRequires=[];this.filter=null;this.currentlyProcessing=undefined;this.yuiLoader=new YAHOO.util.YUILoader();if(PHOCOA.YUILoader!==undefined){throw ("PHOCOA.YUI is a singleton. Do not instantiate it more than once")}PHOCOA.YUILoader=this;this.yuiLoader.scope=this;this.yuiLoader.onSuccess=this.onSuccess;B=B||{};var A=["onSuccess","onFailure","onTimeout","onProgress","scope","data"];$H(B).each(function(C){if(A.indexOf(C.key)!==-1){throw ("option "+C.key+" not allowed in PHOCOA.YUI constructur as it will be overridden.")}this.yuiLoader[C.key]=C.value}.bind(this))};PHOCOA.YUI.prototype={require:function(B,A){A=A||{};this.pendingRequires.push({require:B,options:A});this.loadNext()},loadNext:function(){if(this.currentlyProcessing){return }this.currentlyProcessing=this.pendingRequires.shift();if(this.currentlyProcessing===undefined){return }this.yuiLoader.require(this.currentlyProcessing.require);this.yuiLoader.insert()},onSuccess:function(){if(this.currentlyProcessing.options.onSuccess){if(this.currentlyProcessing.options.scope){this.currentlyProcessing.options.onSuccess.apply(this.currentlyProcessing.options.scope,[this.currentlyProcessing.data])}else{this.currentlyProcessing.options.onSuccess(this.currentlyProcessing.data)}}this.currentlyProcessing=undefined;this.loadNext()}};PHOCOA.sandbox=function(jsCode,globalNamespace,localNamespace){if(globalNamespace){if(!localNamespace){localNamespace=globalNamespace}eval(jsCode+"\n\nwindow."+globalNamespace+" = "+localNamespace+";")}else{eval(jsCode)}};PHOCOA.importCSS=function(A){var B=document.createElement("link");B.setAttribute("rel","stylesheet");B.setAttribute("type","text/css");B.setAttribute("href",A);document.getElementsByTagName("head")[0].appendChild(B)};PHOCOA.namespace("runtime");PHOCOA.runtime.addObject=function(B,C){PHOCOA.runtime.setupObjectCache();var A=C||B.id;if(!A){throw"No ID could be found."}if(0&&PHOCOA.runtime.objectList[A]){alert("error - cannot add duplicate object: "+A);return }PHOCOA.runtime.objectList[A]=B};PHOCOA.runtime.removeObject=function(A){PHOCOA.runtime.setupObjectCache();delete PHOCOA.runtime.objectList[A]};PHOCOA.runtime.setupObjectCache=function(){if(!PHOCOA.runtime.objectList){PHOCOA.runtime.objectList={}}};PHOCOA.runtime.getObject=function(B){PHOCOA.runtime.setupObjectCache();var A=null;if(PHOCOA.runtime.objectList[B]){A=PHOCOA.runtime.objectList[B]}return A};PHOCOA.namespace("WFRPC");PHOCOA.WFRPC=function(A,C,B){this.target="#page#";this.action=null;this.form=null;this.runsIfInvalid=false;this.invocationPath=null;this.transaction=null;this.isAjax=true;this.submitButton=null;this.callback={success:null,failure:null,scope:null,argument:null};if(A){this.invocationPath=A}if(C){this.target=C}if(B){this.action=B}return this};PHOCOA.WFRPC.prototype={successCallbackWrapper:function(o){o.argument=this.callback.argument;if(o.getResponseHeader("Content-Type").strip()==="application/x-json-phocoa-ui-updates"){this.doPhocoaUIUpdatesJSON(eval("("+o.responseText+")"))}if(typeof this.callback.success==="function"){this.callback.success.call(this.callback.scope,o)}},failureCallbackWrapper:function(A){A.argument=this.callback.argument;if(typeof this.callback.failure==="function"){this.callback.failure.call(this.callback.scope,A)}},actionURL:function(){return this.invocationPath},actionURLParams:function(D,A){D=D||[];A=A||false;var C=(A?"&":"");C+="__phocoa_rpc_enable=1";C+="&__phocoa_rpc_invocationPath="+encodeURIComponent(this.invocationPath);C+="&__phocoa_rpc_target="+encodeURIComponent(this.target);C+="&__phocoa_rpc_action="+this.action;C+="&__phocoa_rpc_runsIfInvalid="+this.runsIfInvalid;C+="&__phocoa_is_ajax="+this.isAjax;if(D.length){for(var E=0;E<D.length;E++){var B="__phocoa_rpc_argv_"+E;C+="&"+B+"="+(D[E]===null?"WFNull":encodeURIComponent(D[E]))}}C+="&__phocoa_rpc_argc="+D.length;return C},actionAsURL:function(A){return this.actionURL()+"?"+this.actionURLParams(A)},phocoaRPCParameters:function(B){B=B||[];var D={};D.__phocoa_rpc_enable=1;D.__phocoa_rpc_invocationPath=this.invocationPath;D.__phocoa_rpc_target=this.target;D.__phocoa_rpc_action=this.action;D.__phocoa_rpc_runsIfInvalid=this.runsIfInvalid;D.__phocoa_is_ajax=this.isAjax;if(B.length){for(var C=0;C<B.length;C++){var A="__phocoa_rpc_argv_"+C;D[A]=(B[C]===null?"WFNull":B[C])}}D.__phocoa_rpc_argc=B.length;return D},execute:function(){if(this.form){$$(".phocoaWFFormError").each(function(D){D.update(null)});if(this.isAjax===false){var C=$(this.form);$H(this.phocoaRPCParameters(this.execute.arguments)).each(function(E){var D='<input type="hidden" name="'+E.key+'" value="'+E.value+'" />';Element.insert(C,D)});if(this.submitButton){var B='<input type="hidden" name="'+$(this.submitButton).name+'" value="'+$(this.submitButton).value+'" />';Element.insert(C,B)}C.submit()}else{this.transaction=$(this.form).request({method:"GET",parameters:this.phocoaRPCParameters(this.execute.arguments),onSuccess:this.successCallbackWrapper.bind(this),onFailure:this.failureCallbackWrapper.bind(this),onException:this.failureCallbackWrapper.bind(this)})}}else{var A=this.actionAsURL(this.execute.arguments);if(this.isAjax){this.transaction=new Ajax.Request(A,{method:"get",asynchronous:true,onSuccess:this.successCallbackWrapper.bind(this),onFailure:this.failureCallbackWrapper.bind(this),onException:this.failureCallbackWrapper.bind(this)})}else{document.location=A}}return this.transaction},runScriptsInElement:function(el){var scriptEls=el.getElementsByTagName("script");for(idx=0;idx<scriptEls.length;idx++){var node=scriptEls[idx];window.eval(node.innerHTML)}},doPhocoaUIUpdatesJSON:function(updateList){var id,el;if(updateList.update){for(id in updateList.update){el=$(id);el.update(updateList.update[id]);this.runScriptsInElement(el)}}if(updateList.replace){for(id in updateList.replace){el=$(id);el.replace(updateList.replace[id]);this.runScriptsInElement(el)}}if(updateList.run){for(id=0;id<updateList.run.length;id++){window.eval(updateList.run[id])}}}};PHOCOA.namespace("WFAction");PHOCOA.WFAction=function(C,B){this.elId=C;this.eventName=B;this.callback=PHOCOA.widgets[this.elId].events[this.eventName].handleEvent;this.rpc=null;this.stopsEvent=true;var D=$(this.elId);var A=D.type.toLowerCase();var E=this.eventName;if(Prototype.Browser.IE&&this.eventName==="change"&&(A==="checkbox"||A==="radio")){E="click"}Event.observe(D,E,this.yuiTrigger.bindAsEventListener(this));return this};PHOCOA.WFAction.prototype={stopEvent:function(A){Event.stop(A)},yuiTrigger:function(A){if(this.stopsEvent){this.stopEvent(A)}this.execute(A)},execute:function(C){var B=[],D;if(PHOCOA.widgets[this.elId].events[this.eventName].collectArguments){B=PHOCOA.widgets[this.elId].events[this.eventName].collectArguments()}D=B.slice(0);D.splice(0,0,C);if(this.rpc){if(this.callback){var A=this.callback.apply(this,D);if(A===false){return }}B.splice(0,0,Event.element(C).identify(),C.type);this.rpc.callback.argument=D;this.rpc.callback.success=this.rpcCallbackSuccess;this.rpc.callback.scope=this;this.rpc.execute.apply(this.rpc,B)}else{if(this.callback){this.callback.apply(this,D)}else{if(typeof (console)!="undefined"&&console.warn){console.warn("Callback doesn't exist: PHOCOA.widgets."+C.target.identify()+".events."+C.type)}}}},rpcCallbackSuccess:function(o){var theResponse;var contentType=o.getResponseHeader("Content-Type").strip();switch(contentType){case"application/x-json":theResponse=eval("("+o.responseText+")");break;case"text/xml":theResponse=o.responseXML;break;case"text/plain":theResponse=o.responseText;break;default:theResponse=o.responseText;break}if(PHOCOA.widgets[this.elId].events[this.eventName].ajaxSuccess){var cbArgs=this.rpc.callback.argument.slice(0);cbArgs.splice(0,0,theResponse);PHOCOA.widgets[this.elId].events[this.eventName].ajaxSuccess.apply(null,cbArgs)}}};