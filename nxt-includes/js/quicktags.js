var QTags,edButtons=[],edCanvas,edAddTag=function(){},edCheckOpenTags=function(){},edCloseAllTags=function(){},edInsertImage=function(){},edInsertLink=function(){},edInsertTag=function(){},edLink=function(){},edQuickLink=function(){},edRemoveTag=function(){},edShowButton=function(){},edShowLinks=function(){},edSpell=function(){},edToolbar=function(){};function quicktags(a){return new QTags(a)}function edInsertContent(b,a){return QTags.insertContent(a)}function edButton(f,e,c,b,a,d){return QTags.addButton(f,e,c,b,a,"",-1)}(function(){var b=function(g){var f,e,d;if(typeof jQuery!="undefined"){jQuery(document).ready(g)}else{f=b;f.funcs=[];f.ready=function(){if(!f.isReady){f.isReady=true;for(e=0;e<f.funcs.length;e++){f.funcs[e]()}}};if(f.isReady){g()}else{f.funcs.push(g)}if(!f.eventAttached){if(document.addEventListener){d=function(){document.removeEventListener("DOMContentLoaded",d,false);f.ready()};document.addEventListener("DOMContentLoaded",d,false);window.addEventListener("load",f.ready,false)}else{if(document.attachEvent){d=function(){if(document.readyState==="complete"){document.detachEvent("onreadystatechange",d);f.ready()}};document.attachEvent("onreadystatechange",d);window.attachEvent("onload",f.ready);(function(){try{document.documentElement.doScroll("left")}catch(h){setTimeout(arguments.callee,50);return}f.ready()})()}}f.eventAttached=true}}},a=(function(){var d=new Date(),e;e=function(f){var g=f.toString();if(g.length<2){g="0"+g}return g};return d.getUTCFullYear()+"-"+e(d.getUTCMonth()+1)+"-"+e(d.getUTCDate())+"T"+e(d.getUTCHours())+":"+e(d.getUTCMinutes())+":"+e(d.getUTCSeconds())+"+00:00"})(),c;c=QTags=function(j){if(typeof(j)=="string"){j={id:j}}else{if(typeof(j)!="object"){return false}}var i=this,k=j.id,h=document.getElementById(k),g="qt_"+k,d,f,e;if(!k||!h){return false}i.name=g;i.id=k;i.canvas=h;i.settings=j;if(k=="content"&&typeof(adminpage)=="string"&&(adminpage=="post-new-php"||adminpage=="post-php")){edCanvas=h;e="ed_toolbar"}else{e=g+"_toolbar"}d=document.createElement("div");d.id=e;d.className="quicktags-toolbar";h.parentNode.insertBefore(d,h);i.toolbar=d;f=function(n){n=n||window.event;var m=n.target||n.srcElement,l;if(/ ed_button /.test(" "+m.className+" ")){i.canvas=h=document.getElementById(k);l=m.id.replace(g+"_","");if(i.theButtons[l]){i.theButtons[l].callback.call(i.theButtons[l],m,h,i)}}};if(d.addEventListener){d.addEventListener("click",f,false)}else{if(d.attachEvent){d.attachEvent("onclick",f)}}i.getButton=function(l){return i.theButtons[l]};i.getButtonElement=function(l){return document.getElementById(g+"_"+l)};c.instances[k]=i;if(!c.instances[0]){c.instances[0]=c.instances[k];b(function(){c._buttonsInit()})}};c.instances={};c.getInstance=function(d){return c.instances[d]};c._buttonsInit=function(){var p=this,g,e,h,o,m,l,n,f,k,d,j=",strong,em,link,block,del,ins,img,ul,ol,li,code,more,spell,close,";for(l in p.instances){if(l==0){continue}n=p.instances[l];g=n.canvas;e=n.name;h=n.settings;m="";o={};d="";if(h.buttons){d=","+h.buttons+","}for(k in edButtons){if(!edButtons[k]){continue}f=edButtons[k].id;if(d&&j.indexOf(","+f+",")!=-1&&d.indexOf(","+f+",")==-1){continue}if(!edButtons[k].instance||edButtons[k].instance==l){o[f]=edButtons[k];if(edButtons[k].html){m+=edButtons[k].html(e+"_")}}}if(d&&d.indexOf(",fullscreen,")!=-1){o.fullscreen=new c.FullscreenButton();m+=o.fullscreen.html(e+"_")}n.toolbar.innerHTML=m;n.theButtons=o}p.buttonsInitDone=true};c.addButton=function(e,i,h,g,d,j,k,l){var f;if(!e||!i){return}k=k||0;g=g||"";if(typeof(h)==="function"){f=new c.Button(e,i,d,j,l);f.callback=h}else{if(typeof(h)==="string"){f=new c.TagButton(e,i,h,g,d,j,l)}else{return}}if(k==-1){return f}if(k>0){while(typeof(edButtons[k])!="undefined"){k++}edButtons[k]=f}else{edButtons[edButtons.length]=f}if(this.buttonsInitDone){this._buttonsInit()}};c.insertContent=function(g){var h,f,e,i,j,d=document.getElementById(nxtActiveEditor);if(!d){return false}if(document.selection){d.focus();h=document.selection.createRange();h.text=g;d.focus()}else{if(d.selectionStart||d.selectionStart=="0"){j=d.value;f=d.selectionStart;e=d.selectionEnd;i=d.scrollTop;d.value=j.substring(0,f)+g+j.substring(e,j.length);d.focus();d.selectionStart=f+g.length;d.selectionEnd=f+g.length;d.scrollTop=i}else{d.value+=g;d.focus()}}return true};c.Button=function(i,g,e,h,d){var f=this;f.id=i;f.display=g;f.access=e;f.title=h||"";f.instance=d||""};c.Button.prototype.html=function(e){var d=this.access?' accesskey="'+this.access+'"':"";return'<input type="button" id="'+e+this.id+'"'+d+' class="ed_button" title="'+this.title+'" value="'+this.display+'" />'};c.Button.prototype.callback=function(){};c.TagButton=function(k,i,g,f,e,j,d){var h=this;c.Button.call(h,k,i,e,j,d);h.tagStart=g;h.tagEnd=f};c.TagButton.prototype=new c.Button();c.TagButton.prototype.openTag=function(g,d){var f=this;if(!d.openTags){d.openTags=[]}if(f.tagEnd){d.openTags.push(f.id);g.value="/"+g.value}};c.TagButton.prototype.closeTag=function(h,d){var g=this,f=g.isOpen(d);if(f!==false){d.openTags.splice(f,1)}h.value=g.display};c.TagButton.prototype.isOpen=function(d){var g=this,f=0,e=false;if(d.openTags){while(e===false&&f<d.openTags.length){e=d.openTags[f]==g.id?f:false;f++}}else{e=false}return e};c.TagButton.prototype.callback=function(o,h,p){var u=this,q,e,m,g,s=h.value,j,d,n,f,k=s?u.tagEnd:"";if(document.selection){h.focus();f=document.selection.createRange();if(f.text.length>0){if(!u.tagEnd){f.text=f.text+u.tagStart}else{f.text=u.tagStart+f.text+k}}else{if(!u.tagEnd){f.text=u.tagStart}else{if(u.isOpen(p)===false){f.text=u.tagStart;u.openTag(o,p)}else{f.text=k;u.closeTag(o,p)}}}h.focus()}else{if(h.selectionStart||h.selectionStart=="0"){q=h.selectionStart;e=h.selectionEnd;m=e;g=h.scrollTop;j=s.substring(0,q);d=s.substring(e,s.length);n=s.substring(q,e);if(q!=e){if(!u.tagEnd){h.value=j+n+u.tagStart+d;m+=u.tagStart.length}else{h.value=j+u.tagStart+n+k+d;m+=u.tagStart.length+k.length}}else{if(!u.tagEnd){h.value=j+u.tagStart+d;m=q+u.tagStart.length}else{if(u.isOpen(p)===false){h.value=j+u.tagStart+d;u.openTag(o,p);m=q+u.tagStart.length}else{h.value=j+k+d;m=q+k.length;u.closeTag(o,p)}}}h.focus();h.selectionStart=m;h.selectionEnd=m;h.scrollTop=g}else{if(!k){h.value+=u.tagStart}else{if(u.isOpen(p)!==false){h.value+=u.tagStart;u.openTag(o,p)}else{h.value+=k;u.closeTag(o,p)}}h.focus()}}};c.SpellButton=function(){c.Button.call(this,"spell",quicktagsL10n.lookup,"",quicktagsL10n.dictionaryLookup)};c.SpellButton.prototype=new c.Button();c.SpellButton.prototype.callback=function(h,g,d){var j="",i,f,e;if(document.selection){g.focus();i=document.selection.createRange();if(i.text.length>0){j=i.text}}else{if(g.selectionStart||g.selectionStart=="0"){f=g.selectionStart;e=g.selectionEnd;if(f!=e){j=g.value.substring(f,e)}}}if(j===""){j=prompt(quicktagsL10n.wordLookup,"")}if(j!==null&&/^\w[\w ]*$/.test(j)){window.open("http://www.answers.com/"+encodeURIComponent(j))}};c.CloseButton=function(){c.Button.call(this,"close",quicktagsL10n.closeTags,"",quicktagsL10n.closeAllOpenTags)};c.CloseButton.prototype=new c.Button();c._close=function(i,j,d){var g,f,h=d.openTags;if(h){while(h.length>0){g=d.getButton(h[h.length-1]);f=document.getElementById(d.name+"_"+g.id);if(i){g.callback.call(g,f,j,d)}else{g.closeTag(f,d)}}}};c.CloseButton.prototype.callback=c._close;c.closeAllTags=function(e){var d=this.getInstance(e);c._close("",d.canvas,d)};c.LinkButton=function(){c.TagButton.call(this,"link","link","","</a>","a")};c.LinkButton.prototype=new c.TagButton();c.LinkButton.prototype.callback=function(i,j,g,f){var d,h=this;if(typeof(nxtLink)!="undefined"){nxtLink.open();return}if(!f){f="http://"}if(h.isOpen(g)===false){d=prompt(quicktagsL10n.enterURL,f);if(d){h.tagStart='<a href="'+d+'">';c.TagButton.prototype.callback.call(h,i,j,g)}}else{c.TagButton.prototype.callback.call(h,i,j,g)}};c.ImgButton=function(){c.TagButton.call(this,"img","img","","","m")};c.ImgButton.prototype=new c.TagButton();c.ImgButton.prototype.callback=function(h,j,f,d){if(!d){d="http://"}var i=prompt(quicktagsL10n.enterImageURL,d),g;if(i){g=prompt(quicktagsL10n.enterImageDescription,"");this.tagStart='<img src="'+i+'" alt="'+g+'" />';c.TagButton.prototype.callback.call(this,h,j,f)}};c.FullscreenButton=function(){c.Button.call(this,"fullscreen",quicktagsL10n.fullscreen,"f",quicktagsL10n.toggleFullscreen)};c.FullscreenButton.prototype=new c.Button();c.FullscreenButton.prototype.callback=function(d,f){if(f.id!="content"||typeof(fullscreen)=="undefined"){return}fullscreen.on()};edButtons[10]=new c.TagButton("strong","b","<strong>","</strong>","b");edButtons[20]=new c.TagButton("em","i","<em>","</em>","i"),edButtons[30]=new c.LinkButton(),edButtons[40]=new c.TagButton("block","b-quote","\n\n<blockquote>","</blockquote>\n\n","q"),edButtons[50]=new c.TagButton("del","del",'<del datetime="'+a+'">',"</del>","d"),edButtons[60]=new c.TagButton("ins","ins",'<ins datetime="'+a+'">',"</ins>","s"),edButtons[70]=new c.ImgButton(),edButtons[80]=new c.TagButton("ul","ul","<ul>\n","</ul>\n\n","u"),edButtons[90]=new c.TagButton("ol","ol","<ol>\n","</ol>\n\n","o"),edButtons[100]=new c.TagButton("li","li","\t<li>","</li>\n","l"),edButtons[110]=new c.TagButton("code","code","<code>","</code>","c"),edButtons[120]=new c.TagButton("more","more","<!--more-->","","t"),edButtons[130]=new c.SpellButton(),edButtons[140]=new c.CloseButton()})();