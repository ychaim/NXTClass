(function(){tinymce.create("tinymce.plugins.nxtLink",{init:function(a,b){var c=true;a.addCommand("nxt_Link",function(){if(c){return}a.windowManager.open({id:"nxt-link",width:480,height:"auto",nxtDialog:true,title:a.getLang("advlink.link_desc")},{plugin_url:b})});a.addButton("link",{title:a.getLang("advanced.link_desc"),cmd:"nxt_Link"});a.addShortcut("alt+shift+a",a.getLang("advanced.link_desc"),"nxt_Link");a.onNodeChange.add(function(e,d,g,f){c=f&&g.nodeName!="A"})},getInfo:function(){return{longname:"NXTClass Link Dialog",author:"NXTClass",authorurl:"http://opensource.nxtclass.tk",infourl:"",version:"1.0"}}});tinymce.PluginManager.add("nxtlink",tinymce.plugins.nxtLink)})();