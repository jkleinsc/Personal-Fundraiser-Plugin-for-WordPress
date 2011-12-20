(function(a){a.fn.validationEngine=function(b){a.validationEngineLanguage?allRules=a.validationEngineLanguage.allRules:a.validationEngine.debug("Validation engine rules are not loaded check your external file");b=jQuery.extend({allrules:allRules,validationEventTriggers:"focusout",inlineValidation:!0,returnIsValid:!1,liveEvent:!0,unbindEngine:!0,containerOverflow:!1,containerOverflowDOM:"",ajaxSubmit:!1,scroll:!0,promptPosition:"topRight",success:!1,beforeSuccess:function(){},failure:function(){}},
b);a.validationEngine.settings=b;a.validationEngine.ajaxValidArray=[];if(b.inlineValidation==!0){b.returnIsValid||(allowReturnIsvalid=!1,b.liveEvent?(a(this).find("[class*=validate][type!=checkbox]").live(b.validationEventTriggers,function(){c(this)}),a(this).find("[class*=validate][type=checkbox]").live("click",function(){c(this)})):(a(this).find("[class*=validate]").not("[type=checkbox]").bind(b.validationEventTriggers,function(){c(this)}),a(this).find("[class*=validate][type=checkbox]").bind("click",
function(){c(this)})),firstvalid=!1);var c=function(c){a.validationEngine.settings=b;a.validationEngine.intercept==!1||!a.validationEngine.intercept?(a.validationEngine.onSubmitValid=!1,a.validationEngine.loadValidation(c)):a.validationEngine.intercept=!1}}if(b.returnIsValid)return a.validationEngine.submitValidation(this,b)?!1:!0;a(this).bind("submit",function(){a.validationEngine.onSubmitValid=!0;a.validationEngine.settings=b;if(a.validationEngine.submitValidation(this,b)==!1){if(a.validationEngine.submitForm(this,
b)==!0)return!1}else return b.failure&&b.failure(),!1});a(".formError").live("click",function(){a(this).fadeOut(150,function(){a(this).remove()})})};a.validationEngine={defaultSetting:function(){a.validationEngineLanguage?allRules=a.validationEngineLanguage.allRules:a.validationEngine.debug("Validation engine rules are not loaded check your external file");settings={allrules:allRules,validationEventTriggers:"blur",inlineValidation:!0,containerOverflow:!1,containerOverflowDOM:"",returnIsValid:!1,scroll:!0,
unbindEngine:!0,ajaxSubmit:!1,promptPosition:"topRight",success:!1,failure:function(){}};a.validationEngine.settings=settings},loadValidation:function(b){a.validationEngine.settings||a.validationEngine.defaultSetting();rulesParsing=a(b).attr("class");rulesRegExp=/\[(.*)\]/;getRules=rulesRegExp.exec(rulesParsing);if(getRules==null)return!1;str=getRules[1];pattern=/\[|,|\]/;result=str.split(pattern);return a.validationEngine.validateCall(b,result)},validateCall:function(b,c){function e(b,c){callerType=
a(b).attr("type");if((callerType=="text"||callerType=="password"||callerType=="textarea")&&!a(b).val())a.validationEngine.isError=!0,f+=a.validationEngine.settings.allrules[c[i]].alertText+"<br />";if(callerType=="radio"||callerType=="checkbox")if(j=a(b).attr("name"),a("input[name='"+j+"']:checked").size()==0)a.validationEngine.isError=!0,f+=a("input[name='"+j+"']").size()==1?a.validationEngine.settings.allrules[c[i]].alertTextCheckboxe+"<br />":a.validationEngine.settings.allrules[c[i]].alertTextCheckboxMultiple+
"<br />";if(callerType=="select-one"&&!a(b).val())a.validationEngine.isError=!0,f+=a.validationEngine.settings.allrules[c[i]].alertText+"<br />";if(callerType=="select-multiple"&&!a(b).find("option:selected").val())a.validationEngine.isError=!0,f+=a.validationEngine.settings.allrules[c[i]].alertText+"<br />"}function g(b,c,d){customRule=c[d+1];pattern=eval(a.validationEngine.settings.allrules[customRule].regex);if(!pattern.test(a(b).attr("value")))a.validationEngine.isError=!0,f+=a.validationEngine.settings.allrules[customRule].alertText+
"<br />"}function d(b,c,d){customString=c[d+1];if(customString==a(b).attr("value"))a.validationEngine.isError=!0,f+=a.validationEngine.settings.allrules.required.alertText+"<br />"}function k(b,c,d){customRule=c[d+1];funce=a.validationEngine.settings.allrules[customRule].nname;b=window[funce];if(typeof b==="function"){if(!b())a.validationEngine.isError=!0;f+=a.validationEngine.settings.allrules[customRule].alertText+"<br />"}}function h(b,c,d){customAjaxRule=c[d+1];postfile=a.validationEngine.settings.allrules[customAjaxRule].file;
fieldValue=a(b).val();ajaxCaller=b;fieldId=a(b).attr("id");ajaxValidate=!0;ajaxisError=a.validationEngine.isError;extraData=a.validationEngine.settings.allrules[customAjaxRule].extraData?a.validationEngine.settings.allrules[customAjaxRule].extraData:"";ajaxisError||a.ajax({type:"POST",url:postfile,async:!0,data:"validateValue="+fieldValue+"&validateId="+fieldId+"&validateError="+customAjaxRule+"&extraData="+extraData,beforeSend:function(){if(a.validationEngine.settings.allrules[customAjaxRule].alertTextLoad)if(a("div."+
fieldId+"formError")[0])a.validationEngine.updatePromptText(ajaxCaller,a.validationEngine.settings.allrules[customAjaxRule].alertTextLoad,"load");else return a.validationEngine.buildPrompt(ajaxCaller,a.validationEngine.settings.allrules[customAjaxRule].alertTextLoad,"load")},error:function(b,c){a.validationEngine.debug("error in the ajax: "+b.status+" "+c)},success:function(b){function c(b){for(x=0;x<ajaxErrorLength;x++)a.validationEngine.ajaxValidArray[x][0]==fieldId&&(a.validationEngine.ajaxValidArray[x][1]=
b,existInarray=!0)}b=eval("("+b+")");ajaxisError=b.jsonValidateReturn[2];customAjaxRule=b.jsonValidateReturn[1];fieldId=ajaxCaller=a("#"+b.jsonValidateReturn[0])[0];ajaxErrorLength=a.validationEngine.ajaxValidArray.length;existInarray=!1;ajaxisError=="false"?(c(!1),existInarray||(a.validationEngine.ajaxValidArray[ajaxErrorLength]=Array(2),a.validationEngine.ajaxValidArray[ajaxErrorLength][0]=fieldId,existInarray=a.validationEngine.ajaxValidArray[ajaxErrorLength][1]=!1),a.validationEngine.ajaxValid=
!1,f+=a.validationEngine.settings.allrules[customAjaxRule].alertText+"<br />",a.validationEngine.updatePromptText(ajaxCaller,f,"",!0)):(c(!0),a.validationEngine.ajaxValid=!0,customAjaxRule||a.validationEngine.debug("wrong ajax response, are you on a server or in xampp? if not delete de ajax[ajaxUser] validating rule from your form "),a.validationEngine.settings.allrules[customAjaxRule].alertTextOk?a.validationEngine.updatePromptText(ajaxCaller,a.validationEngine.settings.allrules[customAjaxRule].alertTextOk,
"pass",!0):(ajaxValidate=!1,a.validationEngine.closePrompt(ajaxCaller)))}})}function m(b,c,d){confirmField=c[d+1];if(a(b).attr("value")!=a("#"+confirmField).attr("value"))a.validationEngine.isError=!0,f+=a.validationEngine.settings.allrules.confirm.alertText+"<br />"}function n(b,c,d){startLength=eval(c[d+1]);endLength=eval(c[d+2]);feildLength=a(b).attr("value").length;if(feildLength<startLength||feildLength>endLength)a.validationEngine.isError=!0,f+=a.validationEngine.settings.allrules.length.alertText+
startLength+a.validationEngine.settings.allrules.length.alertText2+endLength+a.validationEngine.settings.allrules.length.alertText3+"<br />"}function o(b,c,d){nbCheck=eval(c[d+1]);groupname=a(b).attr("name");groupSize=a("input[name='"+groupname+"']:checked").size();if(groupSize>nbCheck)a.validationEngine.showTriangle=!1,a.validationEngine.isError=!0,f+=a.validationEngine.settings.allrules.maxCheckbox.alertText+"<br />"}function p(b,c,d){nbCheck=eval(c[d+1]);groupname=a(b).attr("name");groupSize=a("input[name='"+
groupname+"']:checked").size();if(groupSize<nbCheck)a.validationEngine.isError=!0,a.validationEngine.showTriangle=!1,f+=a.validationEngine.settings.allrules.minCheckbox.alertText+" "+nbCheck+" "+a.validationEngine.settings.allrules.minCheckbox.alertText2+"<br />"}var f="";a(b).attr("id")||a.validationEngine.debug("This field have no ID attribut( name & class displayed): "+a(b).attr("name")+" "+a(b).attr("class"));ajaxValidate=!1;var j=a(b).attr("name");a.validationEngine.isError=!1;a.validationEngine.showTriangle=
!0;callerType=a(b).attr("type");for(i=0;i<c.length;i++)switch(c[i]){case "optional":if(!a(b).val())return a.validationEngine.closePrompt(b),a.validationEngine.isError;break;case "required":e(b,c);break;case "custom":g(b,c,i);break;case "exemptString":d(b,c,i);break;case "ajax":a.validationEngine.onSubmitValid||h(b,c,i);break;case "length":n(b,c,i);break;case "maxCheckbox":o(b,c,i);groupname=a(b).attr("name");b=a("input[name='"+groupname+"']");break;case "minCheckbox":p(b,c,i);groupname=a(b).attr("name");
b=a("input[name='"+groupname+"']");break;case "confirm":m(b,c,i);break;case "funcCall":k(b,c,i)}if(a("input[name='"+j+"']").size()>1&&(callerType=="radio"||callerType=="checkbox"))b=a("input[name='"+j+"'][type!=hidden]:first"),a.validationEngine.showTriangle=!1;if(a.validationEngine.isError==!0){var l="."+a.validationEngine.linkTofield(b);l!="."?a(l)[0]?a.validationEngine.updatePromptText(b,f):a.validationEngine.buildPrompt(b,f,"error"):a.validationEngine.updatePromptText(b,f)}else a.validationEngine.closePrompt(b);
return a.validationEngine.isError?a.validationEngine.isError:!1},submitForm:function(b){if(a.validationEngine.settings.ajaxSubmit)return extraData=a.validationEngine.settings.ajaxSubmitExtraData?a.validationEngine.settings.ajaxSubmitExtraData:"",a.ajax({type:"POST",url:a.validationEngine.settings.ajaxSubmitFile,async:!0,data:a(b).serialize()+"&"+extraData,error:function(b,e){a.validationEngine.debug("error in the ajax: "+b.status+" "+e)},success:function(c){if(c=="true")a(b).css("opacity",1),a(b).animate({opacity:0,
height:0},function(){a(b).css("display","none");a(b).before("<div class='ajaxSubmit'>"+a.validationEngine.settings.ajaxSubmitMessage+"</div>");a.validationEngine.closePrompt(".formError",!0);a(".ajaxSubmit").show("slow");if(a.validationEngine.settings.success)return a.validationEngine.settings.success&&a.validationEngine.settings.success(),!1});else{c=eval("("+c+")");c.jsonValidateReturn||a.validationEngine.debug("you are not going into the success fonction and jsonValidateReturn return nothing");
errorNumber=c.jsonValidateReturn.length;for(index=0;index<errorNumber;index++)fieldId=c.jsonValidateReturn[index][0],promptError=c.jsonValidateReturn[index][1],type=c.jsonValidateReturn[index][2],a.validationEngine.buildPrompt(fieldId,promptError,type)}}}),!0;if(a.validationEngine.settings.beforeSuccess())return!0;else if(a.validationEngine.settings.success)return a.validationEngine.settings.unbindEngine&&a(b).unbind("submit"),a.validationEngine.settings.success&&a.validationEngine.settings.success(),
!0;return!1},buildPrompt:function(b,c,e,g){a.validationEngine.settings||a.validationEngine.defaultSetting();deleteItself="."+a(b).attr("id")+"formError";a(deleteItself)[0]&&(a(deleteItself).stop(),a(deleteItself).remove());var d=document.createElement("div"),k=document.createElement("div");linkTofield=a.validationEngine.linkTofield(b);a(d).addClass("formError");e=="pass"&&a(d).addClass("greenPopup");e=="load"&&a(d).addClass("blackPopup");g&&a(d).addClass("ajaxed");a(d).addClass(linkTofield);a(k).addClass("formErrorContent");
a.validationEngine.settings.errorClass&&a(d).addClass(a.validationEngine.settings.errorClass);a.validationEngine.settings.containerOverflow?a(b).before(d):a("body").append(d);a(d).append(k);if(a.validationEngine.showTriangle!=!1){var h=document.createElement("div");a(h).addClass("formErrorArrow");a(d).append(h);if(a.validationEngine.settings.promptPosition=="bottomLeft"||a.validationEngine.settings.promptPosition=="bottomRight")a(h).addClass("formErrorArrowBottom"),a(h).html('<div class="line1"><\!-- --\></div><div class="line2"><\!-- --\></div><div class="line3"><\!-- --\></div><div class="line4"><\!-- --\></div><div class="line5"><\!-- --\></div><div class="line6"><\!-- --\></div><div class="line7"><\!-- --\></div><div class="line8"><\!-- --\></div><div class="line9"><\!-- --\></div><div class="line10"><\!-- --\></div>');
if(a.validationEngine.settings.promptPosition=="topLeft"||a.validationEngine.settings.promptPosition=="topRight")a(d).append(h),a(h).html('<div class="line10"><\!-- --\></div><div class="line9"><\!-- --\></div><div class="line8"><\!-- --\></div><div class="line7"><\!-- --\></div><div class="line6"><\!-- --\></div><div class="line5"><\!-- --\></div><div class="line4"><\!-- --\></div><div class="line3"><\!-- --\></div><div class="line2"><\!-- --\></div><div class="line1"><\!-- --\></div>')}a(k).html(c);
b=a.validationEngine.calculatePosition(b,c,e,g,d);b.callerTopPosition+="px";b.callerleftPosition+="px";b.marginTopSize+="px";a(d).css({top:b.callerTopPosition,left:b.callerleftPosition,marginTop:b.marginTopSize,opacity:0});return a(d).animate({opacity:0.87},function(){return!0})},updatePromptText:function(b,c,e,g){linkTofield=a.validationEngine.linkTofield(b);var d="."+linkTofield;e=="pass"?a(d).addClass("greenPopup"):a(d).removeClass("greenPopup");e=="load"?a(d).addClass("blackPopup"):a(d).removeClass("blackPopup");
g?a(d).addClass("ajaxed"):a(d).removeClass("ajaxed");a(d).find(".formErrorContent").html(c);b=a.validationEngine.calculatePosition(b,c,e,g,d);b.callerTopPosition+="px";b.callerleftPosition+="px";b.marginTopSize+="px";a(d).animate({top:b.callerTopPosition,marginTop:b.marginTopSize})},calculatePosition:function(b,c,e,g,d){a.validationEngine.settings.containerOverflow?(callerleftPosition=callerTopPosition=0,callerWidth=a(b).width(),inputHeight=a(d).height(),c="-"+inputHeight):(callerTopPosition=a(b).offset().top,
callerleftPosition=a(b).offset().left,callerWidth=a(b).width(),inputHeight=a(d).height(),c=0);a.validationEngine.settings.promptPosition=="topRight"&&(a.validationEngine.settings.containerOverflow?callerleftPosition+=callerWidth-30:(callerleftPosition+=callerWidth-30,callerTopPosition+=-inputHeight));a.validationEngine.settings.promptPosition=="topLeft"&&(callerTopPosition+=-inputHeight-10);a.validationEngine.settings.promptPosition=="centerRight"&&(callerleftPosition+=callerWidth+13);a.validationEngine.settings.promptPosition==
"bottomLeft"&&(callerHeight=a(b).height(),callerTopPosition=callerTopPosition+callerHeight+15);a.validationEngine.settings.promptPosition=="bottomRight"&&(callerHeight=a(b).height(),callerleftPosition+=callerWidth-30,callerTopPosition+=callerHeight+5);return{callerTopPosition:callerTopPosition,callerleftPosition:callerleftPosition,marginTopSize:c}},linkTofield:function(b){b=a(b).attr("id")+"formError";b=b.replace(/\[/g,"");return b=b.replace(/\]/g,"")},closePrompt:function(b,c){a.validationEngine.settings||
a.validationEngine.defaultSetting();if(c)return a(b).fadeTo("fast",0,function(){a(b).remove()}),!1;typeof ajaxValidate=="undefined"&&(ajaxValidate=!1);ajaxValidate||(linkTofield=a.validationEngine.linkTofield(b),closingPrompt="."+linkTofield,a(closingPrompt).fadeTo("fast",0,function(){a(closingPrompt).remove()}))},debug:function(b){a("#debugMode")[0]||a("body").append("<div id='debugMode'><div class='debugError'><strong>This is a debug mode, you got a problem with your form, it will try to help you, refresh when you think you nailed down the problem</strong></div></div>");
a(".debugError").append("<div class='debugerror'>"+b+"</div>")},submitValidation:function(b){var c=!1;a.validationEngine.ajaxValid=!0;a(b).find("[class*=validate]").size();a(b).find("[class*=validate]").each(function(){linkTofield=a.validationEngine.linkTofield(this);if(!a(this).is(":hidden")&&!a("."+linkTofield).hasClass("ajaxed"))return a.validationEngine.loadValidation(this)?c=!0:""});ajaxErrorLength=a.validationEngine.ajaxValidArray.length;for(x=0;x<ajaxErrorLength;x++)if(a.validationEngine.ajaxValidArray[x][1]==
!1)a.validationEngine.ajaxValid=!1;if(c||!a.validationEngine.ajaxValid){if(a.validationEngine.settings.scroll)if(a.validationEngine.settings.containerOverflow){var e=a(".formError:not('.greenPopup'):first").offset().top,b=a(a.validationEngine.settings.containerOverflowDOM).scrollTop(),g=-parseInt(a(a.validationEngine.settings.containerOverflowDOM).offset().top),e=b+e+g-5;a(a.validationEngine.settings.containerOverflowDOM+":not(:animated)").animate({scrollTop:e},1100)}else{var e=a(".formError:not('.greenPopup'):first").offset().top;
a(".formError:not('.greenPopup')").each(function(){testDestination=a(this).offset().top;if(e>testDestination)e=a(this).offset().top});a("html:not(:animated),body:not(:animated)").animate({scrollTop:e},1100)}return!0}else return!1}}})(jQuery);
