(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var HttpClient=function(){this.xhr=null;this.data=null;this.url=null;this.dataType=null;this.type=null;this.doneCallback=null;this.errorCallback=null;this.alwaysCallback=null;this.response=null;this.error=false;this.getJson=function(url,callbacks){this.data=[];this.url=url;this.type='GET';this.dataType='json';if(typeof callbacks.error!=='undefined'){this.errorCallback=callbacks.error;}if(typeof callbacks.done!=='undefined'){this.doneCallback=callbacks.done;}if(typeof callbacks.always!=='undefined'){this.alwaysCallback=callbacks.always;}this.request();};this.getResponse=function(){return response;};this.abort=function(){if(this.xhr!==null){this.xhr.abort();}};this.request=function(){var xhr=new XMLHttpRequest();var self=this;this.error=true;xhr.open(this.type,encodeURI(this.url));xhr.onload=function(){if(this.status===200){var data=self.getRequestData();self.response=data;self.error=false;}var error=self.hasError();if(error!==false){if(self.errorCallback!==null){self.errorCallback(xhr,xhr.statusText,error);}}else {if(self.doneCallback!==null){self.doneCallback(self.response);}}if(self.alwaysCallback!==null){self.alwaysCallback();}};this.xhr=xhr;xhr.send();};this.getRequestData=function(){var data;switch(this.dataType){case 'json':data=this.jsonParse(this.xhr.responseText);break;default:data=this.xhr.responseText;break;}return data;};this.jsonParse=function(json){try{return JSON.parse(json);}catch(e){this.error=e;return null;}};this.hasError=function(){return this.error;};};module.exports=HttpClient;

},{}],2:[function(require,module,exports){
var Links={blurMe:function(event){var source=event.target||event.srcElement;source.blur();}};module.exports=Links;

},{}],3:[function(require,module,exports){
var Runner={run:function(callback){callback();},start:function(callback){const loadedStates=['complete','loaded','interactive'];if(loadedStates.includes(document.readyState)&&document.body){callback();}else {window.addEventListener('DOMContentLoaded',callback,false);}}};module.exports=Runner;

},{}],4:[function(require,module,exports){
require('../node_modules/html5-history-api/history.js');var Modules=require('./modules.js');var Dispatcher={config:null,configure:function(config){this.config=config;},navigate:function(module,params,sweeper){var data={module:module,params:params};var url=this.resolvModuleUrl(module,params);history.pushState(data,null,url);sweeper(module,params);},getSlug:function(string){var slug=string.toLowerCase().replace(/ /g,'-').replace(/[^áéíóúÁÉÍÓÚÑñ\w-]+/g,'');return slug;},resolvModuleUI:function(data,swapper){var render=React.createElement('div',null,'No View Set.. yet!');var module=data.module;if(typeof Modules[module]==='undefined'){return render;}render=Modules[module].render(data,swapper);return render;},resolvModuleApi:function(module,params){var api='/api-config-not-set-yet.json';if(typeof this.config==='undefined'){return api;}if(typeof this.config.api==='undefined'){return api;}if(typeof this.config.api[module]===undefined){return api;}api=this.config.api[module];api=api+this.resolvModuleQueryString(module,params);return api;},resolvModuleUrl:function(module,params){var url='/url-config-not-set-yet.html';if(typeof this.config==='undefined'){return url;}if(typeof this.config.url==='undefined'){return url;}if(typeof this.config.url[module]===undefined){return url;}url=this.getUrlReplacement(this.config.url[module],params);return url;},resolvModuleQueryString:function(module,params){var querystringdata=this.resolvQueryStringData(module,params);if(querystringdata===false){return false;}var querystring='?';for(var param in querystringdata){var value=querystringdata[param];querystring+=param+"="+value+"&";}return querystring.substring(0,querystring.length-1);},resolvQueryStringData:function(module,params){var querystring=false;if(typeof this.config==='undefined'){return querystring;}if(typeof this.config.querystring==='undefined'){return querystring;}if(typeof this.config.querystring[module]===undefined){return querystring;}var querystring={};for(var name in this.config.querystring[module]){var param=this.config.querystring[module][name];if(typeof params[param]!=='undefined'){querystring[name]=params[param];}}return querystring;},getUrlReplacement:function(rawUrl,params){var res=rawUrl.match(/(%.*?%)/g);var url=rawUrl;for(var i in res){var token=res[i].replace(/%/g,'');var replacement=this.getPathReplacement(token,params);var needle="%"+token+"%";url=url.replace(needle,replacement);}return url;},getPathReplacement:function(token,params){var path='path-not-set-yet';if(typeof params[token]!=='undefined'){path=this.getSlug(String(params[token]));}return path;}};module.exports=Dispatcher;

},{"../node_modules/html5-history-api/history.js":24,"./modules.js":17}],5:[function(require,module,exports){
require('../node_modules/html5-history-api/history.js');var HttpClient=require('../components/http_client.js');var Dispatcher=require('./dispatcher.js');var States={loading:1,error:2,done:3};var UI={loading:React.createElement('div',{id:'loading',className:'module_wrapper'},React.createElement('h1',{className:'module_title'},'Cargando Contenido...')),error:function(react){var callback=react.retry;var homeCallbacak=react.home;return React.createElement('div',{id:'error',className:'module_wrapper'},React.createElement('h1',{className:'module_title'},'E R R O R'),React.createElement('div',{className:'row'},React.createElement('p',null,'Hubo un problema al cargar este contenido.')),React.createElement('div',{className:'buttons'},React.createElement('a',{className:'button',href:react.retryUrl(),onClick:callback},'R E I N T E N T A R'),React.createElement('a',{className:'button',href:react.homeUrl(),onClick:homeCallbacak},'H O M E')));},done:function(data,swapper){return Dispatcher.resolvModuleUI(data,swapper);}};var Engine=React.createClass({displayName:'Engine',request:null,propTypes:{module:React.PropTypes.string.isRequired,params:React.PropTypes.object.isRequired},getInitialState:function(){var state=this.getModuleState(this.props.module,this.props.params);state.state=States.loading;return state;},getModuleState:function(module,params){var state={module:module,params:params};return state;},componentWillMount:function(){this.fetch();this.historyCallbacks();},historyCallbacks:function(){var self=this;window.addEventListener("popstate",function(event){var module;var params;if(history.state===null){return;}if(typeof history.state.module==='undefined'){return;}if(typeof history.state.params==='undefined'){return;}module=history.state.module;params=history.state.params;self.swapModule(module,params);});history.pushState(this.getInitialState(),null,location.href);},getCurrentState:function(){var state=null;if(typeof this.state!=='undefined'){if(typeof this.state.state!=='undefined'){state=this.state;}}if(state===null){state=this.getInitialState();}return state.state;},swapModule:function(module,params){this.load(module,params);},getModule:function(){var module=this.state.module;return module;},timeout:null,fetch:function(){this.fetchModule(this.state,this.error,this.done);},fetchModule:function(state,errorCallback,doneCallback){var module=state.module;var params=state.params;var api=this.resolvApi(module,params);if(this.request!==null){this.request.abort();}this.request=new HttpClient();this.request.getJson(api,{error:errorCallback,done:doneCallback});},resolvApi:function(module,params){var api=Dispatcher.resolvModuleApi(module,params);return api;},resolvRenderUI:function(state,module){var renderUI=React.createElement('div',null,'No View Set... yet! ');switch(state){case States.loading:renderUI=this.resolvLoadingUI();break;case States.error:renderUI=this.resolvErrorUI();break;case States.done:renderUI=this.resolvDoneUI(module);break;}return renderUI;},resolvLoadingUI:function(){var render=UI.loading;return render;},resolvErrorUI:function(){var renderUI=UI.error(this);return renderUI;},resolvDoneUI:function(module){var data=this.state.data;data.module=module;var renderUI=UI.done(data,this.swapModule);return renderUI;},render:function(){var state=this.getCurrentState();var module=this.getModule();var renderUI=this.resolvRenderUI(state,module);return renderUI;},done:function(data){this.setState({state:States.done,data:data});},error:function(xhr,textStatus,error){this.setState({state:States.error});},home:function(event){event.preventDefault();Dispatcher.navigate('index',{},this.swapModule);return false;},homeUrl:function(){var url=Dispatcher.resolvModuleUrl('index',{});return url;},retryUrl:function(){var url=Dispatcher.resolvModuleUrl(history.state.module,history.state.params);return url;},retry:function(event){event.preventDefault();this.load(history.state.module,history.state.params);return false;},load:function(module,params){Dispatcher.configure($ReactData.config);var state=this.getModuleState(module,params);state.state=States.loading;this.setState(state,this.fetch);}});module.exports=Engine;

},{"../components/http_client.js":1,"../node_modules/html5-history-api/history.js":24,"./dispatcher.js":4}],6:[function(require,module,exports){
var Dispatcher=require('./dispatcher.js');var HTMLViewer=require('./html_viewer.js');var HtmlUrlViewer=require('./html_url_viewer.js');var HTMLStats=require('./html_stats.js');var Modules=require('./modules.js');var HttpClient=require('../components/http_client.js');var UI={get:function(react){var target=react.props.target;var url=react.props.url;var swapper=react.swapper;var link=react.props.link;var meta=react.props.meta;var renderUI=React.createElement('div',{id:'exploration',className:'module_wrapper'},React.createElement('h1',{className:'module_title'},target.name,React.createElement('br',null),React.createElement(HtmlUrlViewer,{ref:function(ref){react.urlViewer=ref;},url:url.full_url})),React.createElement('div',{className:'col-3-4'},React.createElement(HTMLViewer,{link:link,swapper:swapper})),React.createElement('div',{className:'col-1-4'},React.createElement(HTMLStats,{ref:function(ref){react.stats=ref;},meta:meta})));return renderUI;}};var Exploration=React.createClass({displayName:'Exploration',request:null,urlViewer:null,stats:null,module:'exploration',componentWillMount:function(){Dispatcher.configure($ReactData.config);},componentWillUnmount:function(){if(this.request!==null){this.request.abort();}},propTypes:{analysis:React.PropTypes.object.isRequired,link:React.PropTypes.string.isRequired,target:React.PropTypes.object.isRequired,meta:React.PropTypes.object.isRequired,url:React.PropTypes.object.isRequired},swapper:function(hash){var params=this.getParams(hash);var module=this.getModule();var callback=this.swapperCallback.bind(this,module,params);Dispatcher.navigate(module,params,callback);},swapperCallback:function(module,params){var api=Dispatcher.resolvModuleApi(module,params);this.ajaxOnStart();if(this.request!==null){this.request.abort();}this.request=new HttpClient();this.request.getJson(api,{error:this.ajaxOnError.bind(this,module,params),done:this.ajaxOnSuccess});},ajaxOnStart:function(){this.urlViewer.loading();this.stats.loading();},ajaxOnError:function(module,params){var callback=this.swapperCallback.bind(this,module,params);this.urlViewer.error(callback);this.stats.error(callback);},ajaxOnSuccess:function(data){var url=data.url;var meta=data.meta;this.urlViewer.done(url.full_url);this.stats.done(meta);},getParams:function(hash){return Modules.exploration.params(this.props.target.id,hash,this.props.target.name);},getModule:function(){return this.module;},render:function(){var renderUI=this.resolvRenderUI();return renderUI;},resolvRenderUI:function(){var renderUI=UI.get(this);return renderUI;}});module.exports=Exploration;

},{"../components/http_client.js":1,"./dispatcher.js":4,"./html_stats.js":12,"./html_url_viewer.js":13,"./html_viewer.js":14,"./modules.js":17}],7:[function(require,module,exports){
var Runner=require('../components/runner.js');var Dispatcher=require('./dispatcher.js');var Engine=require('./engine.js');var Header=require('./header.js');var Menu=require('./menu.js');var UI={header:function(engine){var mainUrl=$ReactData.header.mainUrl;var logoUrl=$ReactData.header.logoUrl;var swapper=engine.swapModule;var header=React.createElement(Header,{logoUrl:logoUrl,mainUrl:mainUrl,swapper:swapper});return header;},engine:function(){var module=$ReactData.params.module;var params=$ReactData.params;delete params.module;var engine=React.createElement(Engine,{module:module,params:params});return engine;},frontend:function(){var renderUI=React.createElement('div',{className:'wrapper'},React.createElement('div',{id:'upper'}),React.createElement('div',{id:'menu'}),React.createElement('div',{id:'middle'}));return renderUI;},menu:function(engine){var swapper=engine.swapModule;var menu=React.createElement(Menu,{swapper:swapper});return menu;}};Runner.start(function(){Dispatcher.configure($ReactData.config);var frontend=ReactDOM.render(UI.frontend(),document.getElementById('react-root'));var engine=ReactDOM.render(UI.engine(),document.getElementById('middle'));var header=ReactDOM.render(UI.header(engine),document.getElementById('upper'));var menu=ReactDOM.render(UI.menu(engine),document.getElementById('menu'));});

},{"../components/runner.js":3,"./dispatcher.js":4,"./engine.js":5,"./header.js":8,"./menu.js":16}],8:[function(require,module,exports){
var Dispatcher=require('./dispatcher.js');var Links=require('../components/links.js');var UI={get:function(react){var properties=react.props;var mainClicker=react.mainClicker;var renderUI=React.createElement('div',{className:'header'},React.createElement('span',null,React.createElement('a',{onFocus:Links.blurMe,href:properties.mainUrl,onClick:mainClicker},React.createElement('img',{src:properties.logoUrl}),' ',React.createElement('br',null),'The Nala Index')));return renderUI;}};var Header=React.createClass({displayName:'Header',propTypes:{mainUrl:React.PropTypes.string.isRequired,logoUrl:React.PropTypes.string.isRequired,swapper:React.PropTypes.func.isRequired},componentWillMount:function(){Dispatcher.configure($ReactData.config);},mainClicker:function(event){event.preventDefault();Dispatcher.navigate('index',{},this.props.swapper);return false;},render:function(){var renderUI=this.resolvRenderUI();return renderUI;},resolvRenderUI:function(){var renderUI=UI.get(this);return renderUI;}});module.exports=Header;

},{"../components/links.js":2,"./dispatcher.js":4}],9:[function(require,module,exports){
var Dispatcher=require('./dispatcher.js');var HistoryItem=React.createClass({displayName:'HistoryItem',module:'exploration',componentWillMount:function(){Dispatcher.configure($ReactData.config);},propTypes:{id:React.PropTypes.number.isRequired,index:React.PropTypes.number.isRequired,starting:React.PropTypes.string.isRequired,ending:React.PropTypes.string.isRequired,http_petitions:React.PropTypes.number.isRequired,css_crawled:React.PropTypes.number.isRequired,html_crawled:React.PropTypes.number.isRequired,js_crawled:React.PropTypes.number.isRequired,img_crawled:React.PropTypes.number.isRequired,hash:React.PropTypes.string.isRequired,target:React.PropTypes.string.isRequired,target_id:React.PropTypes.number.isRequired,swapper:React.PropTypes.func.isRequired},readableDate:function(rawDate){var components=rawDate.split(/ /);var date=components[0];var time=components[1];var dataComponents=date.split(/-/);var year=dataComponents[0];var month=dataComponents[1];var day=dataComponents[2];return day+"/"+month+"/"+year+" "+time;},getParams:function(){return {id:this.props.target_id,hash:this.props.hash,target:this.props.target};},getModule:function(){return this.module;},resolvUrl:function(){var url=Dispatcher.resolvModuleUrl(this.getModule(),this.getParams());return url;},dispatch:function(event){event.preventDefault();Dispatcher.navigate(this.getModule(),this.getParams(),this.props.swapper);return false;},render:function(){return React.createElement('li',null,React.createElement('a',{href:this.resolvUrl(),onClick:this.dispatch},React.createElement('h2',null,'Exploracion #',this.props.index),React.createElement('div',null,React.createElement('b',null,'Iniciada:'),' ',this.readableDate(this.props.starting)),React.createElement('div',null,React.createElement('b',null,'Terminada:'),' ',this.readableDate(this.props.ending)),React.createElement('div',null,React.createElement('b',null,'Peticiones HTTP:'),' ',this.props.http_petitions)));}});module.exports=HistoryItem;

},{"./dispatcher.js":4}],10:[function(require,module,exports){
var HistoryLoader=require('./history_loader.js');var HistoryItem=require('./history_item.js');var Dispatcher=require('./dispatcher.js');var Modules=require('./modules.js');var States={empty:1,done:2};var UI={get:function(react,content,last){var callbackSwapper=react.swapper;var callbackUrlResolver=react.urlResolver;var state=react.state;var properties=react.props;var target=state.target;var renderUI=React.createElement('div',{id:'history_list',className:'module_wrapper'},React.createElement('h1',{className:'module_title'},target.name,' ',React.createElement('i',null,'(pág. ',state.page,')')),content,React.createElement(HistoryLoader,{last:last,page:properties.page,swapper:callbackSwapper,urlResolver:callbackUrlResolver}));return renderUI;},empty:function(react){var emptyUI=React.createElement('div',{className:'row'},React.createElement('p',null,'No hay Historiales aun. Vuelve en otra ocasion.'));var renderUI=UI.get(react,emptyUI,true);return renderUI;},done:function(react){var callbackSwapper=react.props.swapper;var state=react.state;var list=state.list;var target=state.target;var rows=list.map(function(item,i){return React.createElement(HistoryItem,{id:item.id,key:item.id,index:item.index,starting:item.starting,ending:item.ending,http_petitions:item.http_petitions,css_crawled:item.css_crawled,html_crawled:item.html_crawled,js_crawled:item.js_crawled,img_crawled:item.img_crawled,hash:item.root_hash,target:target.name,target_id:target.id,swapper:callbackSwapper});});var content=React.createElement('ul',{className:'module_list'},rows);var renderUI=UI.get(react,content,false);return renderUI;}};var HistoryList=React.createClass({displayName:'HistoryList',propTypes:{list:React.PropTypes.array.isRequired,swapper:React.PropTypes.func.isRequired,target:React.PropTypes.object.isRequired,page:React.PropTypes.number.isRequired},getInitialState:function(){var list=[];var target=[];var page=1;if(typeof this.props.list!=='undefined'){list=this.props.list;}if(typeof this.props.target!=='undefined'){target=this.props.target;}if(typeof this.props.page!=='undefined'){page=this.props.page;}return this.resolvState(list,target,page);},resolvState:function(list,target,page){var state;if(list.length>0){state=States.done;}else {state=States.empty;}return {state:state,list:list,target:target,page:page};},getParams:function(){var id=this.state.target.id;var target=this.state.target.name;var page=this.state.page;return Modules.histories.params(id,target,page);},getPagedParams:function(params){var pagedParams=this.getParams();for(var i in params){pagedParams[i]=params[i];}return pagedParams;},urlResolver:function(module,updateParams){var params=this.getPagedParams(updateParams);var url=Dispatcher.resolvModuleUrl(module,params);return url;},swapper:function(module,updateParams){var params=this.getPagedParams(updateParams);Dispatcher.navigate(module,params,this.props.swapper);},render:function(){var renderUI=this.resolvRenderUI();return renderUI;},resolvRenderUI:function(){var renderUI=React.createElement('div',null,'View not set... yet!');switch(this.state.state){case States.empty:renderUI=this.resolvEmptyUI();break;case States.done:renderUI=this.resolvDoneUI();break;}return renderUI;},resolvEmptyUI:function(){var renderUI=UI.empty(this);return renderUI;},resolvDoneUI:function(){var renderUI=UI.done(this);return renderUI;}});module.exports=HistoryList;

},{"./dispatcher.js":4,"./history_item.js":9,"./history_loader.js":11,"./modules.js":17}],11:[function(require,module,exports){
var Dispatcher=require('./dispatcher.js');var UI={get:function(react){var forwardCallback=react.dispatchForward;var backwardCallback=react.dispatchBackward;return React.createElement("div",{className:"buttons"},react.props.page<2?null:React.createElement("a",{href:react.resolvBackwardUrl(),className:"button",onClick:backwardCallback},"A N T E R I O R"),react.props.last?null:React.createElement("a",{href:react.resolvForwardUrl(),className:"button",onClick:forwardCallback},"S I G U I E N T E"));}};var HistoryLoader=React.createClass({displayName:"HistoryLoader",module:'histories',propTypes:{swapper:React.PropTypes.func.isRequired,urlResolver:React.PropTypes.func.isRequired,page:React.PropTypes.number.isRequired,last:React.PropTypes.bool.isRequired},componentWillMount:function(){Dispatcher.configure($ReactData.config);},getInitialState:function(){return {page:this.props.page};},render:function(){var renderUI=this.resolvRenderUI();return renderUI;},resolvRenderUI:function(){var renderUI=UI.get(this);return renderUI;},resolvBackwardUrl:function(){var url=this.props.urlResolver(this.getModule(),this.getParams(-1));return url;},resolvForwardUrl:function(){var url=this.props.urlResolver(this.getModule(),this.getParams(1));return url;},getModule:function(){return this.module;},getParams:function(page){var params={page:this.state.page+page};return params;},dispatchBackward:function(event){event.preventDefault();this.props.swapper(this.getModule(),this.getParams(-1));return false;},dispatchForward:function(event){event.preventDefault();this.props.swapper(this.getModule(),this.getParams(1));return false;}});module.exports=HistoryLoader;

},{"./dispatcher.js":4}],12:[function(require,module,exports){
var Dispatcher=require('./dispatcher.js');var States={loading:0,done:1,error:2};var UI={done:function(react){var meta=react.state.meta;return React.createElement("div",{className:"col"},React.createElement("div",{className:"stat-row"},React.createElement("div",{className:"stat-label"},"Fecha Exploración"),React.createElement("div",{className:"stat-value"},react.getCreated())),React.createElement("div",{className:"stat-row"},React.createElement("div",{className:"stat-label"},"Tamaño"),React.createElement("div",{className:"stat-value"},meta.size," bytes")),React.createElement("div",{className:"stat-row"},React.createElement("div",{className:"stat-label"},"MIME"),React.createElement("div",{className:"stat-value"},meta.mime)),React.createElement("div",{className:"stat-row"},React.createElement("div",{className:"stat-label"},"Checksum"),React.createElement("div",{className:"stat-value"},meta.checksum)),React.createElement("div",{className:"stat-row"},React.createElement("div",{className:"stat-label"},"Hash"),React.createElement("div",{className:"stat-value"},meta.hash)));},loading:function(react){return React.createElement("span",null,"Obteniendo Metadatos de la URL...");},error:function(react){var clicker=react.state.callback;return React.createElement("span",null,"Error en la conexión ",React.createElement("button",{onClick:clicker},"R E I N T E N T A R "));}};var HTMLStats=React.createClass({displayName:"HTMLStats",propTypes:{meta:React.PropTypes.object.isRequired},getInitialState:function(){return {meta:this.props.meta,state:States.done};},getCreated:function(){var created=this.props.meta.created;var datetime=created.split(' ');var date=datetime[0].split('-');return date[2]+'/'+date[1]+'/'+date[0];},render:function(){var renderUI=this.resolvRenderUI();return renderUI;},done:function(meta){this.setState({meta:meta,state:States.done});},loading:function(){this.setState({state:States.loading});},error:function(callback){this.setState({state:States.error,callback:callback});},resolvRenderUI:function(){var renderUI=React.createElement("div",null," View not set yet! ");switch(this.state.state){case States.done:renderUI=UI.done(this);break;case States.loading:renderUI=UI.loading(this);break;case States.error:renderUI=UI.error(this);break;}return renderUI;}});module.exports=HTMLStats;

},{"./dispatcher.js":4}],13:[function(require,module,exports){
var States={loading:0,done:1,error:2};var UI={done:function(react){var url=react.state.url;return React.createElement("a",{href:url,target:"_blank"},url);},loading:function(react){return React.createElement("span",{className:"subtitle"},"Obteniendo URL original...");},error:function(react){var clicker=react.state.callback;return React.createElement("span",{className:"subtitle"},"Error en la conexión");}};var HtmlUrlViewer=React.createClass({displayName:"HtmlUrlViewer",propTypes:{url:React.PropTypes.string.isRequired},getInitialState:function(){return {url:this.props.url,state:States.done};},done:function(url){this.setState({url:url,state:States.done});},loading:function(){this.setState({state:States.loading});},error:function(callback){this.setState({state:States.error,callback:callback});},render:function(){var renderUI=this.resolvRenderUI();return renderUI;},resolvRenderUI:function(){var renderUI=React.createElement("span",null,"View not set yet! ");switch(this.state.state){case States.loading:renderUI=UI.loading(this);break;case States.done:renderUI=UI.done(this);break;case States.error:renderUI=UI.error(this);break;}return renderUI;}});module.exports=HtmlUrlViewer;

},{}],14:[function(require,module,exports){
var Dispatcher=require('./dispatcher.js');var HTMLViewerLoader=require('./html_viewer_loader.js');var UI={get:function(react){var link=react.props.link;var swapper=react.props.swapper;return React.createElement('div',{className:'col'},React.createElement('iframe',{ref:function(ref){react.iframe=ref;},onLoad:react.frameCallback}),React.createElement(HTMLViewerLoader,{ref:function(ref){react.loader=ref;}}));}};var HTMLViewer=React.createClass({displayName:'HTMLViewer',iframe:null,loader:null,module:'exploration',propTypes:{link:React.PropTypes.string.isRequired,swapper:React.PropTypes.func.isRequired},componentDidMount:function(){var h=Math.max(document.documentElement.clientHeight,window.innerHeight||0);this.iframe.style.height=h+'px';this.frameRelocation(this.props.link);},frameRelocation:function(url){var iframe=this.iframe;var iframeDoc=iframe.contentDocument||iframe.contentWindow.document;iframeDoc.location.replace(url);},componentWillMount:function(){Dispatcher.configure($ReactData.config);},render:function(){var renderUI=this.resolvRenderUI();return renderUI;},resolvRenderUI:function(){var renderUI=UI.get(this);return renderUI;},frameCallback:function(event){var iframe=event.target;var document=iframe.contentDocument||iframe.contentWindow.document;var links=document.getElementsByTagName('A');for(var i in links){var link=links[i];if(typeof link.getAttribute!=='undefined'){var href=link.getAttribute('href');if(this.isNala(link)){link.onclick=this.linkCallback;}else {link.setAttribute('target','_blank');}}}iframe.style.display='block';this.loader.loaded();},getModule:function(){return this.module;},linkCallback:function(event){var link=event.target;while(link!==null&&link.tagName!=='A'){link=link.parentNode;}if(link!==null){var hash=link.getAttribute('data-nalaid');if(this.isSwappable(link)){var url=link.getAttribute('href');this.frameRelocation(url);this.props.swapper(hash);event.preventDefault();return false;}}return true;},isSwappable:function(link){var html=link.getAttribute('data-ishtml');if(typeof html==='undefined'){return false;}return html;},isNala:function(link){var hash=link.getAttribute('data-nalaid');if(typeof hash==='undefined'){return false;}if(hash===null){return false;}if(hash===''){return false;}return true;}});module.exports=HTMLViewer;

},{"./dispatcher.js":4,"./html_viewer_loader.js":15}],15:[function(require,module,exports){
var Dispatcher=require('./dispatcher.js');var States={loading:0,loaded:1};var UI={loading:function(react){return React.createElement('div',{id:'html_viewer_loading',className:'loading',ref:function(ref){react.loader=ref;}},React.createElement('div',{className:'message'},'Recuperando página indexada...'));},loaded:function(react){return React.createElement('span',null,' ');}};var HTMLViewerLoader=React.createClass({displayName:'HTMLViewerLoader',propTypes:{},getInitialState:function(){return {state:States.loading};},componentDidMount:function(){var h=Math.max(document.documentElement.clientHeight,window.innerHeight||0);var loading=document.getElementById('html_viewer_loading');loading.style.height=h+'px';},render:function(){var renderUI=this.resolvRenderUI();return renderUI;},resolvRenderUI:function(){var renderUI=React.createElement('div',null,' View not set Yet! ');switch(this.state.state){case States.loading:renderUI=UI.loading(this);break;case States.loaded:renderUI=UI.loaded(this);break;}return renderUI;},loaded:function(){this.setState({state:States.loaded});}});module.exports=HTMLViewerLoader;

},{"./dispatcher.js":4}],16:[function(require,module,exports){
var Dispatcher=require('./dispatcher.js');var Searcher=require('./searcher.js');var UI={get:function(react){var swapper=react.swapper;var renderUI=React.createElement('ul',{className:'menu'},React.createElement('li',null,React.createElement(Searcher,{swapper:swapper})));return renderUI;}};var Menu=React.createClass({displayName:'Menu',propTypes:{swapper:React.PropTypes.func.isRequired},componentWillMount:function(){Dispatcher.configure($ReactData.config);},swapper:function(module,params){Dispatcher.navigate(module,params,this.props.swapper);},render:function(){var renderUI=this.resolvRenderUI();return renderUI;},resolvRenderUI:function(){var renderUI=UI.get(this);return renderUI;}});module.exports=Menu;

},{"./dispatcher.js":4,"./searcher.js":21}],17:[function(require,module,exports){
var Modules={index:{render:function(data,swapper){var TargetList=require('./target_list.js');var list=data;return React.createElement(TargetList,{list:list,swapper:swapper});},params:{},name:'index'},histories:{render:function(data,swapper){var HistoryList=require('./history_list.js');var blob=data;return React.createElement(HistoryList,{list:blob.histories,target:blob.target,page:blob.page,swapper:swapper});},params:function(id,target,page){return {id:id,target:target,page:page};},name:'histories'},exploration:{render:function(data,swapper){var Exploration=require('./exploration.js');var analysis=data.analysis;var link=data.link;var target=data.target;var meta=data.meta;var url=data.url;return React.createElement(Exploration,{analysis:analysis,link:link,target:target,meta:meta,url:url});},params:function(id,hash,target){return {id:id,target:target,hash:hash};},name:'exploration'},search:{render:function(data,swapper){var Search=require('./search.js');var results=data.results;var term=data.term;var page=data.page;return React.createElement(Search,{page:page,swapper:swapper,term:term,results:results});},params:function(q){return {term:q};},name:'search'}};module.exports=Modules;

},{"./exploration.js":6,"./history_list.js":10,"./search.js":18,"./target_list.js":23}],18:[function(require,module,exports){
var SearchItem=require('./search_item.js');var SearchLoader=require('./search_loader.js');var Modules=require('./modules.js');var Dispatcher=require('./dispatcher.js');var States={empty:1,done:4};var UI={resolvTitle:function(title,props){var t=React.createElement('h1',{className:'module_title'},title,React.createElement('br',null),React.createElement('span',{className:'subtitle'},'Buscando \'',props.term,'\''));return t;},empty:function(react){var properties=react.props;var title=UI.resolvTitle('No se ha encontrado contenido',properties);var loader=UI.loader(react,true);var renderUI=React.createElement('div',{id:'error',className:'module_wrapper'},title,React.createElement('div',{className:'row'},React.createElement('p',null,'Intenta con otros términos de búsqueda.')),loader);return renderUI;},loader:function(react,last){var properties=react.props;var callbackSwapper=react.swapper;var callbackUrlResolver=react.urlResolver;var searcher=React.createElement(SearchLoader,{last:last,page:properties.page,swapper:callbackSwapper,urlResolver:callbackUrlResolver});return searcher;},rows:function(react){var data=react.state.list;var properties=react.props;var rows=data.map(function(item,i){return React.createElement(SearchItem,{key:i,item:item,swapper:properties.swapper});});return rows;},done:function(react,last){var properties=react.props;var rows=UI.rows(react);var title=UI.resolvTitle('Resultados de la Búsqueda',properties);var searcher=UI.loader(react,last);var loader=UI.loader(react,last);var renderUI=React.createElement('div',{id:'search_results',className:'module_wrapper'},title,React.createElement('ul',{className:'module_list'},rows),loader);return renderUI;}};var Search=React.createClass({displayName:'Search',propTypes:{results:React.PropTypes.array.isRequired,term:React.PropTypes.string.isRequired,swapper:React.PropTypes.func.isRequired,page:React.PropTypes.number.isRequired},getInitialState:function(){var list=[];if(typeof this.props.results!=='undefined'){list=this.props.results;}return this.resolvState(list);},resolvState:function(list){var state;if(list.length>0){state=States.done;}else {state=States.empty;}return {state:state,list:list};},render:function(){var renderUI=this.resolvRenderUI();return renderUI;},getParams:function(){var term=this.props.term;return Modules.search.params(term);},getPagedParams:function(params){var pagedParams=this.getParams();for(var i in params){pagedParams[i]=params[i];}return pagedParams;},urlResolver:function(module,updateParams){var params=this.getPagedParams(updateParams);var url=Dispatcher.resolvModuleUrl(module,params);return url;},swapper:function(module,updateParams){var params=this.getPagedParams(updateParams);Dispatcher.navigate(module,params,this.props.swapper);},resolvRenderUI:function(){var renderUI="<div>View not set... yet!</div>";switch(this.state.state){case States.empty:renderUI=this.resolvEmptyUI();break;case States.done:renderUI=this.resolvDoneUI();break;}return renderUI;},resolvEmptyUI:function(){var renderUI=UI.empty(this);return renderUI;},resolvDoneUI:function(){var renderUI=UI.done(this,false);return renderUI;}});module.exports=Search;

},{"./dispatcher.js":4,"./modules.js":17,"./search_item.js":19,"./search_loader.js":20}],19:[function(require,module,exports){
var Dispatcher=require('./dispatcher.js');var Modules=require('./modules.js');var SearchItem=React.createClass({displayName:'SearchItem',module:'exploration',componentWillMount:function(){Dispatcher.configure($ReactData.config);},propTypes:{item:React.PropTypes.object.isRequired,swapper:React.PropTypes.func.isRequired},resolvCreated:function(){var rawDate=this.props.item.created;var components=rawDate.split(/\s/);var date=components[0].split(/-/);var year=date[0];var month=date[1];var day=date[2];return day+"/"+month+"/"+year;},getParams:function(){var id=this.props.item.id;var target=this.props.item.full_url;var page=1;return {id:this.props.item.target_id,target:this.props.item.target,hash:this.trim(this.props.item.hash)};},resolvUrl:function(){var url=Dispatcher.resolvModuleUrl(this.module,this.getParams());return url;},dispatch:function(event){event.preventDefault();Dispatcher.navigate(this.module,this.getParams(),this.props.swapper);return false;},resolvURL:function(){var url=this.props.item.full_url;return url;},resolvTitle:function(){var h1=this.trim(this.props.item.h1);if(h1!==''){return h1;}var title=this.trim(this.props.item.title);return title;},trim:function(str){return str.replace(/^\s+|\s+$/g,'');},render:function(){return React.createElement('li',{onClick:this.dispatch},React.createElement('a',{href:this.resolvUrl()},React.createElement('h2',null,this.resolvTitle()),React.createElement('div',null,React.createElement('b',null,'URL:'),' ',this.resolvURL()),React.createElement('div',null,React.createElement('b',null,'Explorado:'),' ',this.resolvCreated())));}});module.exports=SearchItem;

},{"./dispatcher.js":4,"./modules.js":17}],20:[function(require,module,exports){
var Dispatcher=require('./dispatcher.js');var UI={get:function(react){var forwardCallback=react.dispatchForward;var backwardCallback=react.dispatchBackward;return React.createElement("div",{className:"buttons"},react.props.page<2?null:React.createElement("a",{href:react.resolvBackwardUrl(),className:"button",onClick:backwardCallback},"A N T E R I O R"),react.props.last?null:React.createElement("a",{href:react.resolvForwardUrl(),className:"button",onClick:forwardCallback},"S I G U I E N T E"));}};var SearchLoader=React.createClass({displayName:"SearchLoader",module:'search',propTypes:{swapper:React.PropTypes.func.isRequired,urlResolver:React.PropTypes.func.isRequired,page:React.PropTypes.number.isRequired,last:React.PropTypes.bool.isRequired},componentWillMount:function(){Dispatcher.configure($ReactData.config);},getInitialState:function(){return {page:this.props.page};},render:function(){var renderUI=this.resolvRenderUI();return renderUI;},resolvRenderUI:function(){var renderUI=UI.get(this);return renderUI;},resolvBackwardUrl:function(){var url=this.props.urlResolver(this.getModule(),this.getParams(-1));return url;},resolvForwardUrl:function(){var url=this.props.urlResolver(this.getModule(),this.getParams(1));return url;},getModule:function(){return this.module;},getParams:function(page){var params={page:this.state.page+page};return params;},dispatchBackward:function(event){event.preventDefault();this.props.swapper(this.getModule(),this.getParams(-1));return false;},dispatchForward:function(event){event.preventDefault();this.props.swapper(this.getModule(),this.getParams(1));return false;}});module.exports=SearchLoader;

},{"./dispatcher.js":4}],21:[function(require,module,exports){
var States={ready:0,empty:1,invalid:2};var Classes={empty:'has-error',invalid:'has-error',ready:''};var Placeholders={empty:'No puede ir vacío!',ready:'Ingrese término de búsqueda...',invalid:'Solo letras y números!'};var UI={getInput:function(react,state){var swapper=react.props.swapper;var placeholder=Placeholders[state];var className=Classes[state];var renderUI=React.createElement('input',{className:className,type:'text',id:'searcher',placeholder:placeholder,ref:function(ref){if(ref===null){return;}react.input=ref;react.input.onkeypress=react.keyPresser;}});return renderUI;},ready:function(react){var renderUI=UI.getInput(react,'ready');return renderUI;},empty:function(react){var renderUI=UI.getInput(react,'empty');return renderUI;},invalid:function(react){var renderUI=UI.getInput(react,'invalid');return renderUI;}};var Search=React.createClass({displayName:'Search',input:null,module:'search',propTypes:{swapper:React.PropTypes.func.isRequired},getModule:function(){return this.module;},getParams:function(){return {page:1,term:this.trim(this.input.value)};},trim:function(str){if(!String.prototype.trim){(function(){var rtrim=/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;String.prototype.trim=function(){return this.replace(rtrim,'');};})();}return str.trim();},render:function(){var renderUI=this.resolvRenderUI();return renderUI;},getInitialState:function(){return {state:States.ready};},resolvRenderUI:function(){var renderUI=React.createElement('div',null,' View not set yet! ');switch(this.state.state){case States.ready:renderUI=UI.ready(this);break;case States.empty:renderUI=UI.empty(this);break;case States.invalid:renderUI=UI.invalid(this);break;}return renderUI;},keyPresser:function(event){if(event.keyCode===13){this.submit();return false;}else {this.ready();}},empty:function(){this.input.value='';if(this.state.state===States.empty){return;}this.setState({state:States.empty});},invalid:function(){this.input.value='';if(this.state.state===States.invalid){return;}this.setState({state:States.invalid});},ready:function(){if(this.state.state===States.ready){return;}this.setState({state:States.ready});},submit:function(){if(this.isEmpty()){this.empty();}else if(this.isInvalid()){this.invalid();}else {this.send();}},isEmpty:function(){var value=this.input.value;if(value===''){return true;}if(value.match(/^\s+$/)){return true;;}return false;},isInvalid:function(){var value=this.input.value;if(value.match(/^[áéíóúÁÉÍÓÚÑñA-Za-z0-9\s]+$/)){return false;}return true;},send:function(){this.props.swapper(this.getModule(),this.getParams());this.input.value='';this.input.blur();}});module.exports=Search;

},{}],22:[function(require,module,exports){
var Dispatcher=require('./dispatcher.js');var Modules=require('./modules.js');var TargetItem=React.createClass({displayName:'TargetItem',module:'histories',componentWillMount:function(){Dispatcher.configure($ReactData.config);},propTypes:{id:React.PropTypes.number.isRequired,url:React.PropTypes.string.isRequired,name:React.PropTypes.string.isRequired,first_crawl:React.PropTypes.string.isRequired,last_crawl:React.PropTypes.string.isRequired,histories:React.PropTypes.number.isRequired,swapper:React.PropTypes.func.isRequired},readableDate:function(rawDate){var components=rawDate.split(/-/);var year=components[0];var month=components[1];var day=components[2];return day+"/"+month+"/"+year;},getParams:function(){var id=this.props.id;var target=this.props.name;var page=1;return Modules.histories.params(id,target,page);},resolvUrl:function(){var url=Dispatcher.resolvModuleUrl(this.module,this.getParams());return url;},dispatch:function(event){event.preventDefault();Dispatcher.navigate(this.module,this.getParams(),this.props.swapper);return false;},render:function(){var historial;if(this.props.histories>1){historial='historiales';}else {historial='historial';}return React.createElement('li',{onClick:this.dispatch},React.createElement('a',{href:this.resolvUrl()},React.createElement('h2',null,this.props.name),React.createElement('div',null,React.createElement('b',null,'URL:'),' ',this.props.url,' ',React.createElement('i',null,'(',this.props.histories,' ',historial,')')),React.createElement('div',null,React.createElement('b',null,'Primera Exploracion:'),' ',this.readableDate(this.props.first_crawl)),React.createElement('div',null,React.createElement('b',null,'Ultima vez Explorado:'),' ',this.readableDate(this.props.last_crawl))));}});module.exports=TargetItem;

},{"./dispatcher.js":4,"./modules.js":17}],23:[function(require,module,exports){
var TargetItem=require('./target_item.js');var States={empty:1,done:4};var UI={empty:React.createElement("div",{id:"error",className:"module_wrapper"},React.createElement("h1",{className:"module_title"},"No hay Sitios Explorados aun"),React.createElement("div",{className:"row"},React.createElement("p",null,"Vuelve en otra ocasion."))),done:function(data,props){var properties=props;var list=data;var rows=list.map(function(item,i){return React.createElement(TargetItem,{swapper:properties.swapper,id:item.id,key:item.id,url:item.url,name:item.name,histories:item.histories,first_crawl:item.first_crawl,last_crawl:item.last_crawl});});var renderUI=React.createElement("div",{id:"target_list",className:"module_wrapper"},React.createElement("h1",{className:"module_title"},"Sitios Disponibles"),React.createElement("ul",{className:"module_list"},rows));return renderUI;}};var TargetList=React.createClass({displayName:"TargetList",propTypes:{list:React.PropTypes.array.isRequired,swapper:React.PropTypes.func.isRequired},getInitialState:function(){var list=[];if(typeof this.props.list!=='undefined'){list=this.props.list;}return this.resolvState(list);},resolvState:function(list){var state;if(list.length>0){state=States.done;}else {state=States.empty;}return {state:state,list:list};},render:function(){var renderUI=this.resolvRenderUI();return renderUI;},resolvRenderUI:function(){var renderUI="<div>View not set... yet!</div>";switch(this.state.state){case States.empty:renderUI=this.resolvEmptyUI();break;case States.done:renderUI=this.resolvDoneUI();break;}return renderUI;},resolvEmptyUI:function(){var renderUI=UI.empty;return renderUI;},resolvDoneUI:function(){var renderUI=UI.done(this.state.list,this.props);return renderUI;}});module.exports=TargetList;

},{"./target_item.js":22}],24:[function(require,module,exports){
/*!
 * History API JavaScript Library v4.2.7
 *
 * Support: IE8+, FF3+, Opera 9+, Safari, Chrome and other
 *
 * Copyright 2011-2015, Dmitrii Pakhtinov ( spb.piksel@gmail.com )
 *
 * http://spb-piksel.ru/
 *
 * MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Update: 2016-03-08 16:57
 */
(function(factory) {
  if (typeof define === 'function' && define['amd']) {
    if (typeof requirejs !== 'undefined') {
      // https://github.com/devote/HTML5-History-API/issues/73
      var rndKey = '[history' + (new Date()).getTime() + ']';
      var onError = requirejs['onError'];
      factory.toString = function() {
        return rndKey;
      };
      requirejs['onError'] = function(err) {
        if (err.message.indexOf(rndKey) === -1) {
          onError.call(requirejs, err);
        }
      };
    }
    define([], factory);
  }
  // commonJS support
  if (typeof exports === "object" && typeof module !== "undefined") {
    module['exports'] = factory();
  } else {
    // execute anyway
    return factory();
  }
})(function() {
  // Define global variable
  var global = (typeof window === 'object' ? window : this) || {};
  // Prevent the code from running if there is no window.history object or library already loaded
  if (!global.history || "emulate" in global.history) return global.history;
  // symlink to document
  var document = global.document;
  // HTML element
  var documentElement = document.documentElement;
  // symlink to constructor of Object
  var Object = global['Object'];
  // symlink to JSON Object
  var JSON = global['JSON'];
  // symlink to instance object of 'Location'
  var windowLocation = global.location;
  // symlink to instance object of 'History'
  var windowHistory = global.history;
  // new instance of 'History'. The default is a reference to the original object instance
  var historyObject = windowHistory;
  // symlink to method 'history.pushState'
  var historyPushState = windowHistory.pushState;
  // symlink to method 'history.replaceState'
  var historyReplaceState = windowHistory.replaceState;
  // if the browser supports HTML5-History-API
  var isSupportHistoryAPI = isSupportHistoryAPIDetect();
  // verifies the presence of an object 'state' in interface 'History'
  var isSupportStateObjectInHistory = 'state' in windowHistory;
  // symlink to method 'Object.defineProperty'
  var defineProperty = Object.defineProperty;
  // new instance of 'Location', for IE8 will use the element HTMLAnchorElement, instead of pure object
  var locationObject = redefineProperty({}, 't') ? {} : document.createElement('a');
  // prefix for the names of events
  var eventNamePrefix = '';
  // String that will contain the name of the method
  var addEventListenerName = global.addEventListener ? 'addEventListener' : (eventNamePrefix = 'on') && 'attachEvent';
  // String that will contain the name of the method
  var removeEventListenerName = global.removeEventListener ? 'removeEventListener' : 'detachEvent';
  // String that will contain the name of the method
  var dispatchEventName = global.dispatchEvent ? 'dispatchEvent' : 'fireEvent';
  // reference native methods for the events
  var addEvent = global[addEventListenerName];
  var removeEvent = global[removeEventListenerName];
  var dispatch = global[dispatchEventName];
  // default settings
  var settings = {"basepath": '/', "redirect": 0, "type": '/', "init": 0};
  // key for the sessionStorage
  var sessionStorageKey = '__historyAPI__';
  // Anchor Element for parseURL function
  var anchorElement = document.createElement('a');
  // last URL before change to new URL
  var lastURL = windowLocation.href;
  // Control URL, need to fix the bug in Opera
  var checkUrlForPopState = '';
  // for fix on Safari 8
  var triggerEventsInWindowAttributes = 1;
  // trigger event 'onpopstate' on page load
  var isFireInitialState = false;
  // if used history.location of other code
  var isUsedHistoryLocationFlag = 0;
  // store a list of 'state' objects in the current session
  var stateStorage = {};
  // in this object will be stored custom handlers
  var eventsList = {};
  // stored last title
  var lastTitle = document.title;
  // store a custom origin
  var customOrigin;

  /**
   * Properties that will be replaced in the global
   * object 'window', to prevent conflicts
   *
   * @type {Object}
   */
  var eventsDescriptors = {
    "onhashchange": null,
    "onpopstate": null
  };

  /**
   * Fix for Chrome in iOS
   * See https://github.com/devote/HTML5-History-API/issues/29
   */
  var fastFixChrome = function(method, args) {
    var isNeedFix = global.history !== windowHistory;
    if (isNeedFix) {
      global.history = windowHistory;
    }
    method.apply(windowHistory, args);
    if (isNeedFix) {
      global.history = historyObject;
    }
  };

  /**
   * Properties that will be replaced/added to object
   * 'window.history', includes the object 'history.location',
   * for a complete the work with the URL address
   *
   * @type {Object}
   */
  var historyDescriptors = {
    /**
     * Setting library initialization
     *
     * @param {null|String} [basepath] The base path to the site; defaults to the root "/".
     * @param {null|String} [type] Substitute the string after the anchor; by default "/".
     * @param {null|Boolean} [redirect] Enable link translation.
     */
    "setup": function(basepath, type, redirect) {
      settings["basepath"] = ('' + (basepath == null ? settings["basepath"] : basepath))
        .replace(/(?:^|\/)[^\/]*$/, '/');
      settings["type"] = type == null ? settings["type"] : type;
      settings["redirect"] = redirect == null ? settings["redirect"] : !!redirect;
    },
    /**
     * @namespace history
     * @param {String} [type]
     * @param {String} [basepath]
     */
    "redirect": function(type, basepath) {
      historyObject['setup'](basepath, type);
      basepath = settings["basepath"];
      if (global.top == global.self) {
        var relative = parseURL(null, false, true)._relative;
        var path = windowLocation.pathname + windowLocation.search;
        if (isSupportHistoryAPI) {
          path = path.replace(/([^\/])$/, '$1/');
          if (relative != basepath && (new RegExp("^" + basepath + "$", "i")).test(path)) {
            windowLocation.replace(relative);
          }
        } else if (path != basepath) {
          path = path.replace(/([^\/])\?/, '$1/?');
          if ((new RegExp("^" + basepath, "i")).test(path)) {
            windowLocation.replace(basepath + '#' + path.
              replace(new RegExp("^" + basepath, "i"), settings["type"]) + windowLocation.hash);
          }
        }
      }
    },
    /**
     * The method adds a state object entry
     * to the history.
     *
     * @namespace history
     * @param {Object} state
     * @param {string} title
     * @param {string} [url]
     */
    pushState: function(state, title, url) {
      var t = document.title;
      if (lastTitle != null) {
        document.title = lastTitle;
      }
      historyPushState && fastFixChrome(historyPushState, arguments);
      changeState(state, url);
      document.title = t;
      lastTitle = title;
    },
    /**
     * The method updates the state object,
     * title, and optionally the URL of the
     * current entry in the history.
     *
     * @namespace history
     * @param {Object} state
     * @param {string} title
     * @param {string} [url]
     */
    replaceState: function(state, title, url) {
      var t = document.title;
      if (lastTitle != null) {
        document.title = lastTitle;
      }
      delete stateStorage[windowLocation.href];
      historyReplaceState && fastFixChrome(historyReplaceState, arguments);
      changeState(state, url, true);
      document.title = t;
      lastTitle = title;
    },
    /**
     * Object 'history.location' is similar to the
     * object 'window.location', except that in
     * HTML4 browsers it will behave a bit differently
     *
     * @namespace history
     */
    "location": {
      set: function(value) {
        if (isUsedHistoryLocationFlag === 0) isUsedHistoryLocationFlag = 1;
        global.location = value;
      },
      get: function() {
        if (isUsedHistoryLocationFlag === 0) isUsedHistoryLocationFlag = 1;
        return locationObject;
      }
    },
    /**
     * A state object is an object representing
     * a user interface state.
     *
     * @namespace history
     */
    "state": {
      get: function() {
        if (typeof stateStorage[windowLocation.href] === 'object') {
          return JSON.parse(JSON.stringify(stateStorage[windowLocation.href]));
        } else if(typeof stateStorage[windowLocation.href] !== 'undefined') {
          return stateStorage[windowLocation.href];
        } else {
          return null;
        }
      }
    }
  };

  /**
   * Properties for object 'history.location'.
   * Object 'history.location' is similar to the
   * object 'window.location', except that in
   * HTML4 browsers it will behave a bit differently
   *
   * @type {Object}
   */
  var locationDescriptors = {
    /**
     * Navigates to the given page.
     *
     * @namespace history.location
     */
    assign: function(url) {
      if (!isSupportHistoryAPI && ('' + url).indexOf('#') === 0) {
        changeState(null, url);
      } else {
        windowLocation.assign(url);
      }
    },
    /**
     * Reloads the current page.
     *
     * @namespace history.location
     */
    reload: function(flag) {
      windowLocation.reload(flag);
    },
    /**
     * Removes the current page from
     * the session history and navigates
     * to the given page.
     *
     * @namespace history.location
     */
    replace: function(url) {
      if (!isSupportHistoryAPI && ('' + url).indexOf('#') === 0) {
        changeState(null, url, true);
      } else {
        windowLocation.replace(url);
      }
    },
    /**
     * Returns the current page's location.
     *
     * @namespace history.location
     */
    toString: function() {
      return this.href;
    },
    /**
     * Returns the current origin.
     *
     * @namespace history.location
     */
    "origin": {
      get: function() {
        if (customOrigin !== void 0) {
          return customOrigin;
        }
        if (!windowLocation.origin) {
          return windowLocation.protocol + "//" + windowLocation.hostname + (windowLocation.port ? ':' + windowLocation.port: '');
        }
        return windowLocation.origin;
      },
      set: function(value) {
        customOrigin = value;
      }
    },
    /**
     * Returns the current page's location.
     * Can be set, to navigate to another page.
     *
     * @namespace history.location
     */
    "href": isSupportHistoryAPI ? null : {
      get: function() {
        return parseURL()._href;
      }
    },
    /**
     * Returns the current page's protocol.
     *
     * @namespace history.location
     */
    "protocol": null,
    /**
     * Returns the current page's host and port number.
     *
     * @namespace history.location
     */
    "host": null,
    /**
     * Returns the current page's host.
     *
     * @namespace history.location
     */
    "hostname": null,
    /**
     * Returns the current page's port number.
     *
     * @namespace history.location
     */
    "port": null,
    /**
     * Returns the current page's path only.
     *
     * @namespace history.location
     */
    "pathname": isSupportHistoryAPI ? null : {
      get: function() {
        return parseURL()._pathname;
      }
    },
    /**
     * Returns the current page's search
     * string, beginning with the character
     * '?' and to the symbol '#'
     *
     * @namespace history.location
     */
    "search": isSupportHistoryAPI ? null : {
      get: function() {
        return parseURL()._search;
      }
    },
    /**
     * Returns the current page's hash
     * string, beginning with the character
     * '#' and to the end line
     *
     * @namespace history.location
     */
    "hash": isSupportHistoryAPI ? null : {
      set: function(value) {
        changeState(null, ('' + value).replace(/^(#|)/, '#'), false, lastURL);
      },
      get: function() {
        return parseURL()._hash;
      }
    }
  };

  /**
   * Just empty function
   *
   * @return void
   */
  function emptyFunction() {
    // dummy
  }

  /**
   * Prepares a parts of the current or specified reference for later use in the library
   *
   * @param {string} [href]
   * @param {boolean} [isWindowLocation]
   * @param {boolean} [isNotAPI]
   * @return {Object}
   */
  function parseURL(href, isWindowLocation, isNotAPI) {
    var re = /(?:([a-zA-Z0-9\-]+\:))?(?:\/\/(?:[^@]*@)?([^\/:\?#]+)(?::([0-9]+))?)?([^\?#]*)(?:(\?[^#]+)|\?)?(?:(#.*))?/;
    if (href != null && href !== '' && !isWindowLocation) {
      var current = parseURL(),
          base = document.getElementsByTagName('base')[0];
      if (!isNotAPI && base && base.getAttribute('href')) {
        // Fix for IE ignoring relative base tags.
        // See http://stackoverflow.com/questions/3926197/html-base-tag-and-local-folder-path-with-internet-explorer
        base.href = base.href;
        current = parseURL(base.href, null, true);
      }
      var _pathname = current._pathname, _protocol = current._protocol;
      // convert to type of string
      href = '' + href;
      // convert relative link to the absolute
      href = /^(?:\w+\:)?\/\//.test(href) ? href.indexOf("/") === 0
        ? _protocol + href : href : _protocol + "//" + current._host + (
        href.indexOf("/") === 0 ? href : href.indexOf("?") === 0
          ? _pathname + href : href.indexOf("#") === 0
          ? _pathname + current._search + href : _pathname.replace(/[^\/]+$/g, '') + href
        );
    } else {
      href = isWindowLocation ? href : windowLocation.href;
      // if current browser not support History-API
      if (!isSupportHistoryAPI || isNotAPI) {
        // get hash fragment
        href = href.replace(/^[^#]*/, '') || "#";
        // form the absolute link from the hash
        // https://github.com/devote/HTML5-History-API/issues/50
        href = windowLocation.protocol.replace(/:.*$|$/, ':') + '//' + windowLocation.host + settings['basepath']
          + href.replace(new RegExp("^#[\/]?(?:" + settings["type"] + ")?"), "");
      }
    }
    // that would get rid of the links of the form: /../../
    anchorElement.href = href;
    // decompose the link in parts
    var result = re.exec(anchorElement.href);
    // host name with the port number
    var host = result[2] + (result[3] ? ':' + result[3] : '');
    // folder
    var pathname = result[4] || '/';
    // the query string
    var search = result[5] || '';
    // hash
    var hash = result[6] === '#' ? '' : (result[6] || '');
    // relative link, no protocol, no host
    var relative = pathname + search + hash;
    // special links for set to hash-link, if browser not support History API
    var nohash = pathname.replace(new RegExp("^" + settings["basepath"], "i"), settings["type"]) + search;
    // result
    return {
      _href: result[1] + '//' + host + relative,
      _protocol: result[1],
      _host: host,
      _hostname: result[2],
      _port: result[3] || '',
      _pathname: pathname,
      _search: search,
      _hash: hash,
      _relative: relative,
      _nohash: nohash,
      _special: nohash + hash
    }
  }

  /**
   * Detect HistoryAPI support while taking into account false positives.
   * Based on https://github.com/Modernizr/Modernizr/blob/master/feature-detects/history.js
   */
  function isSupportHistoryAPIDetect(){
    var ua = global.navigator.userAgent;
    // We only want Android 2 and 4.0, stock browser, and not Chrome which identifies
    // itself as 'Mobile Safari' as well, nor Windows Phone (issue #1471).
    if ((ua.indexOf('Android 2.') !== -1 ||
      (ua.indexOf('Android 4.0') !== -1)) &&
      ua.indexOf('Mobile Safari') !== -1 &&
      ua.indexOf('Chrome') === -1 &&
      ua.indexOf('Windows Phone') === -1)
    {
      return false;
    }
    // Return the regular check
    return !!historyPushState;
  }

  /**
   * Initializing storage for the custom state's object
   */
  function storageInitialize() {
    var sessionStorage;
    /**
     * sessionStorage throws error when cookies are disabled
     * Chrome content settings when running the site in a Facebook IFrame.
     * see: https://github.com/devote/HTML5-History-API/issues/34
     * and: http://stackoverflow.com/a/12976988/669360
     */
    try {
      sessionStorage = global['sessionStorage'];
      sessionStorage.setItem(sessionStorageKey + 't', '1');
      sessionStorage.removeItem(sessionStorageKey + 't');
    } catch(_e_) {
      sessionStorage = {
        getItem: function(key) {
          var cookie = document.cookie.split(key + "=");
          return cookie.length > 1 && cookie.pop().split(";").shift() || 'null';
        },
        setItem: function(key, value) {
          var state = {};
          // insert one current element to cookie
          if (state[windowLocation.href] = historyObject.state) {
            document.cookie = key + '=' + JSON.stringify(state);
          }
        }
      }
    }

    try {
      // get cache from the storage in browser
      stateStorage = JSON.parse(sessionStorage.getItem(sessionStorageKey)) || {};
    } catch(_e_) {
      stateStorage = {};
    }

    // hang up the event handler to event unload page
    addEvent(eventNamePrefix + 'unload', function() {
      // save current state's object
      sessionStorage.setItem(sessionStorageKey, JSON.stringify(stateStorage));
    }, false);
  }

  /**
   * This method is implemented to override the built-in(native)
   * properties in the browser, unfortunately some browsers are
   * not allowed to override all the properties and even add.
   * For this reason, this was written by a method that tries to
   * do everything necessary to get the desired result.
   *
   * @param {Object} object The object in which will be overridden/added property
   * @param {String} prop The property name to be overridden/added
   * @param {Object} [descriptor] An object containing properties set/get
   * @param {Function} [onWrapped] The function to be called when the wrapper is created
   * @return {Object|Boolean} Returns an object on success, otherwise returns false
   */
  function redefineProperty(object, prop, descriptor, onWrapped) {
    var testOnly = 0;
    // test only if descriptor is undefined
    if (!descriptor) {
      descriptor = {set: emptyFunction};
      testOnly = 1;
    }
    // variable will have a value of true the success of attempts to set descriptors
    var isDefinedSetter = !descriptor.set;
    var isDefinedGetter = !descriptor.get;
    // for tests of attempts to set descriptors
    var test = {configurable: true, set: function() {
      isDefinedSetter = 1;
    }, get: function() {
      isDefinedGetter = 1;
    }};

    try {
      // testing for the possibility of overriding/adding properties
      defineProperty(object, prop, test);
      // running the test
      object[prop] = object[prop];
      // attempt to override property using the standard method
      defineProperty(object, prop, descriptor);
    } catch(_e_) {
    }

    // If the variable 'isDefined' has a false value, it means that need to try other methods
    if (!isDefinedSetter || !isDefinedGetter) {
      // try to override/add the property, using deprecated functions
      if (object.__defineGetter__) {
        // testing for the possibility of overriding/adding properties
        object.__defineGetter__(prop, test.get);
        object.__defineSetter__(prop, test.set);
        // running the test
        object[prop] = object[prop];
        // attempt to override property using the deprecated functions
        descriptor.get && object.__defineGetter__(prop, descriptor.get);
        descriptor.set && object.__defineSetter__(prop, descriptor.set);
      }

      // Browser refused to override the property, using the standard and deprecated methods
      if (!isDefinedSetter || !isDefinedGetter) {
        if (testOnly) {
          return false;
        } else if (object === global) {
          // try override global properties
          try {
            // save original value from this property
            var originalValue = object[prop];
            // set null to built-in(native) property
            object[prop] = null;
          } catch(_e_) {
          }
          // This rule for Internet Explorer 8
          if ('execScript' in global) {
            /**
             * to IE8 override the global properties using
             * VBScript, declaring it in global scope with
             * the same names.
             */
            global['execScript']('Public ' + prop, 'VBScript');
            global['execScript']('var ' + prop + ';', 'JavaScript');
          } else {
            try {
              /**
               * This hack allows to override a property
               * with the set 'configurable: false', working
               * in the hack 'Safari' to 'Mac'
               */
              defineProperty(object, prop, {value: emptyFunction});
            } catch(_e_) {
              if (prop === 'onpopstate') {
                /**
                 * window.onpopstate fires twice in Safari 8.0.
                 * Block initial event on window.onpopstate
                 * See: https://github.com/devote/HTML5-History-API/issues/69
                 */
                addEvent('popstate', descriptor = function() {
                  removeEvent('popstate', descriptor, false);
                  var onpopstate = object.onpopstate;
                  // cancel initial event on attribute handler
                  object.onpopstate = null;
                  setTimeout(function() {
                    // restore attribute value after short time
                    object.onpopstate = onpopstate;
                  }, 1);
                }, false);
                // cancel trigger events on attributes in object the window
                triggerEventsInWindowAttributes = 0;
              }
            }
          }
          // set old value to new variable
          object[prop] = originalValue;

        } else {
          // the last stage of trying to override the property
          try {
            try {
              // wrap the object in a new empty object
              var temp = Object.create(object);
              defineProperty(Object.getPrototypeOf(temp) === object ? temp : object, prop, descriptor);
              for(var key in object) {
                // need to bind a function to the original object
                if (typeof object[key] === 'function') {
                  temp[key] = object[key].bind(object);
                }
              }
              try {
                // to run a function that will inform about what the object was to wrapped
                onWrapped.call(temp, temp, object);
              } catch(_e_) {
              }
              object = temp;
            } catch(_e_) {
              // sometimes works override simply by assigning the prototype property of the constructor
              defineProperty(object.constructor.prototype, prop, descriptor);
            }
          } catch(_e_) {
            // all methods have failed
            return false;
          }
        }
      }
    }

    return object;
  }

  /**
   * Adds the missing property in descriptor
   *
   * @param {Object} object An object that stores values
   * @param {String} prop Name of the property in the object
   * @param {Object|null} descriptor Descriptor
   * @return {Object} Returns the generated descriptor
   */
  function prepareDescriptorsForObject(object, prop, descriptor) {
    descriptor = descriptor || {};
    // the default for the object 'location' is the standard object 'window.location'
    object = object === locationDescriptors ? windowLocation : object;
    // setter for object properties
    descriptor.set = (descriptor.set || function(value) {
      object[prop] = value;
    });
    // getter for object properties
    descriptor.get = (descriptor.get || function() {
      return object[prop];
    });
    return descriptor;
  }

  /**
   * Wrapper for the methods 'addEventListener/attachEvent' in the context of the 'window'
   *
   * @param {String} event The event type for which the user is registering
   * @param {Function} listener The method to be called when the event occurs.
   * @param {Boolean} capture If true, capture indicates that the user wishes to initiate capture.
   * @return void
   */
  function addEventListener(event, listener, capture) {
    if (event in eventsList) {
      // here stored the event listeners 'popstate/hashchange'
      eventsList[event].push(listener);
    } else {
      // FireFox support non-standart four argument aWantsUntrusted
      // https://github.com/devote/HTML5-History-API/issues/13
      if (arguments.length > 3) {
        addEvent(event, listener, capture, arguments[3]);
      } else {
        addEvent(event, listener, capture);
      }
    }
  }

  /**
   * Wrapper for the methods 'removeEventListener/detachEvent' in the context of the 'window'
   *
   * @param {String} event The event type for which the user is registered
   * @param {Function} listener The parameter indicates the Listener to be removed.
   * @param {Boolean} capture Was registered as a capturing listener or not.
   * @return void
   */
  function removeEventListener(event, listener, capture) {
    var list = eventsList[event];
    if (list) {
      for(var i = list.length; i--;) {
        if (list[i] === listener) {
          list.splice(i, 1);
          break;
        }
      }
    } else {
      removeEvent(event, listener, capture);
    }
  }

  /**
   * Wrapper for the methods 'dispatchEvent/fireEvent' in the context of the 'window'
   *
   * @param {Event|String} event Instance of Event or event type string if 'eventObject' used
   * @param {*} [eventObject] For Internet Explorer 8 required event object on this argument
   * @return {Boolean} If 'preventDefault' was called the value is false, else the value is true.
   */
  function dispatchEvent(event, eventObject) {
    var eventType = ('' + (typeof event === "string" ? event : event.type)).replace(/^on/, '');
    var list = eventsList[eventType];
    if (list) {
      // need to understand that there is one object of Event
      eventObject = typeof event === "string" ? eventObject : event;
      if (eventObject.target == null) {
        // need to override some of the properties of the Event object
        for(var props = ['target', 'currentTarget', 'srcElement', 'type']; event = props.pop();) {
          // use 'redefineProperty' to override the properties
          eventObject = redefineProperty(eventObject, event, {
            get: event === 'type' ? function() {
              return eventType;
            } : function() {
              return global;
            }
          });
        }
      }
      if (triggerEventsInWindowAttributes) {
        // run function defined in the attributes 'onpopstate/onhashchange' in the 'window' context
        ((eventType === 'popstate' ? global.onpopstate : global.onhashchange)
          || emptyFunction).call(global, eventObject);
      }
      // run other functions that are in the list of handlers
      for(var i = 0, len = list.length; i < len; i++) {
        list[i].call(global, eventObject);
      }
      return true;
    } else {
      return dispatch(event, eventObject);
    }
  }

  /**
   * dispatch current state event
   */
  function firePopState() {
    var o = document.createEvent ? document.createEvent('Event') : document.createEventObject();
    if (o.initEvent) {
      o.initEvent('popstate', false, false);
    } else {
      o.type = 'popstate';
    }
    o.state = historyObject.state;
    // send a newly created events to be processed
    dispatchEvent(o);
  }

  /**
   * fire initial state for non-HTML5 browsers
   */
  function fireInitialState() {
    if (isFireInitialState) {
      isFireInitialState = false;
      firePopState();
    }
  }

  /**
   * Change the data of the current history for HTML4 browsers
   *
   * @param {Object} state
   * @param {string} [url]
   * @param {Boolean} [replace]
   * @param {string} [lastURLValue]
   * @return void
   */
  function changeState(state, url, replace, lastURLValue) {
    if (!isSupportHistoryAPI) {
      // if not used implementation history.location
      if (isUsedHistoryLocationFlag === 0) isUsedHistoryLocationFlag = 2;
      // normalization url
      var urlObject = parseURL(url, isUsedHistoryLocationFlag === 2 && ('' + url).indexOf("#") !== -1);
      // if current url not equal new url
      if (urlObject._relative !== parseURL()._relative) {
        // if empty lastURLValue to skip hash change event
        lastURL = lastURLValue;
        if (replace) {
          // only replace hash, not store to history
          windowLocation.replace("#" + urlObject._special);
        } else {
          // change hash and add new record to history
          windowLocation.hash = urlObject._special;
        }
      }
    } else {
      lastURL = windowLocation.href;
    }
    if (!isSupportStateObjectInHistory && state) {
      stateStorage[windowLocation.href] = state;
    }
    isFireInitialState = false;
  }

  /**
   * Event handler function changes the hash in the address bar
   *
   * @param {Event} event
   * @return void
   */
  function onHashChange(event) {
    // https://github.com/devote/HTML5-History-API/issues/46
    var fireNow = lastURL;
    // new value to lastURL
    lastURL = windowLocation.href;
    // if not empty fireNow, otherwise skipped the current handler event
    if (fireNow) {
      // if checkUrlForPopState equal current url, this means that the event was raised popstate browser
      if (checkUrlForPopState !== windowLocation.href) {
        // otherwise,
        // the browser does not support popstate event or just does not run the event by changing the hash.
        firePopState();
      }
      // current event object
      event = event || global.event;

      var oldURLObject = parseURL(fireNow, true);
      var newURLObject = parseURL();
      // HTML4 browser not support properties oldURL/newURL
      if (!event.oldURL) {
        event.oldURL = oldURLObject._href;
        event.newURL = newURLObject._href;
      }
      if (oldURLObject._hash !== newURLObject._hash) {
        // if current hash not equal previous hash
        dispatchEvent(event);
      }
    }
  }

  /**
   * The event handler is fully loaded document
   *
   * @param {*} [noScroll]
   * @return void
   */
  function onLoad(noScroll) {
    // Get rid of the events popstate when the first loading a document in the webkit browsers
    setTimeout(function() {
      // hang up the event handler for the built-in popstate event in the browser
      addEvent('popstate', function(e) {
        // set the current url, that suppress the creation of the popstate event by changing the hash
        checkUrlForPopState = windowLocation.href;
        // for Safari browser in OS Windows not implemented 'state' object in 'History' interface
        // and not implemented in old HTML4 browsers
        if (!isSupportStateObjectInHistory) {
          e = redefineProperty(e, 'state', {get: function() {
            return historyObject.state;
          }});
        }
        // send events to be processed
        dispatchEvent(e);
      }, false);
    }, 0);
    // for non-HTML5 browsers
    if (!isSupportHistoryAPI && noScroll !== true && "location" in historyObject) {
      // scroll window to anchor element
      scrollToAnchorId(locationObject.hash);
      // fire initial state for non-HTML5 browser after load page
      fireInitialState();
    }
  }

  /**
   * Finds the closest ancestor anchor element (including the target itself).
   *
   * @param {HTMLElement} target The element to start scanning from.
   * @return {HTMLElement} An element which is the closest ancestor anchor.
   */
  function anchorTarget(target) {
    while (target) {
      if (target.nodeName === 'A') return target;
      target = target.parentNode;
    }
  }

  /**
   * Handles anchor elements with a hash fragment for non-HTML5 browsers
   *
   * @param {Event} e
   */
  function onAnchorClick(e) {
    var event = e || global.event;
    var target = anchorTarget(event.target || event.srcElement);
    var defaultPrevented = "defaultPrevented" in event ? event['defaultPrevented'] : event.returnValue === false;
    if (target && target.nodeName === "A" && !defaultPrevented) {
      var current = parseURL();
      var expect = parseURL(target.getAttribute("href", 2));
      var isEqualBaseURL = current._href.split('#').shift() === expect._href.split('#').shift();
      if (isEqualBaseURL && expect._hash) {
        if (current._hash !== expect._hash) {
          locationObject.hash = expect._hash;
        }
        scrollToAnchorId(expect._hash);
        if (event.preventDefault) {
          event.preventDefault();
        } else {
          event.returnValue = false;
        }
      }
    }
  }

  /**
   * Scroll page to current anchor in url-hash
   *
   * @param hash
   */
  function scrollToAnchorId(hash) {
    var target = document.getElementById(hash = (hash || '').replace(/^#/, ''));
    if (target && target.id === hash && target.nodeName === "A") {
      var rect = target.getBoundingClientRect();
      global.scrollTo((documentElement.scrollLeft || 0), rect.top + (documentElement.scrollTop || 0)
        - (documentElement.clientTop || 0));
    }
  }

  /**
   * Library initialization
   *
   * @return {Boolean} return true if all is well, otherwise return false value
   */
  function initialize() {
    /**
     * Get custom settings from the query string
     */
    var scripts = document.getElementsByTagName('script');
    var src = (scripts[scripts.length - 1] || {}).src || '';
    var arg = src.indexOf('?') !== -1 ? src.split('?').pop() : '';
    arg.replace(/(\w+)(?:=([^&]*))?/g, function(a, key, value) {
      settings[key] = (value || '').replace(/^(0|false)$/, '');
    });

    /**
     * hang up the event handler to listen to the events hashchange
     */
    addEvent(eventNamePrefix + 'hashchange', onHashChange, false);

    // a list of objects with pairs of descriptors/object
    var data = [locationDescriptors, locationObject, eventsDescriptors, global, historyDescriptors, historyObject];

    // if browser support object 'state' in interface 'History'
    if (isSupportStateObjectInHistory) {
      // remove state property from descriptor
      delete historyDescriptors['state'];
    }

    // initializing descriptors
    for(var i = 0; i < data.length; i += 2) {
      for(var prop in data[i]) {
        if (data[i].hasOwnProperty(prop)) {
          if (typeof data[i][prop] !== 'object') {
            // If the descriptor is a simple function, simply just assign it an object
            data[i + 1][prop] = data[i][prop];
          } else {
            // prepare the descriptor the required format
            var descriptor = prepareDescriptorsForObject(data[i], prop, data[i][prop]);
            // try to set the descriptor object
            if (!redefineProperty(data[i + 1], prop, descriptor, function(n, o) {
              // is satisfied if the failed override property
              if (o === historyObject) {
                // the problem occurs in Safari on the Mac
                global.history = historyObject = data[i + 1] = n;
              }
            })) {
              // if there is no possibility override.
              // This browser does not support descriptors, such as IE7

              // remove previously hung event handlers
              removeEvent(eventNamePrefix + 'hashchange', onHashChange, false);

              // fail to initialize :(
              return false;
            }

            // create a repository for custom handlers onpopstate/onhashchange
            if (data[i + 1] === global) {
              eventsList[prop] = eventsList[prop.substr(2)] = [];
            }
          }
        }
      }
    }

    // check settings
    historyObject['setup']();

    // redirect if necessary
    if (settings['redirect']) {
      historyObject['redirect']();
    }

    // initialize
    if (settings["init"]) {
      // You agree that you will use window.history.location instead window.location
      isUsedHistoryLocationFlag = 1;
    }

    // If browser does not support object 'state' in interface 'History'
    if (!isSupportStateObjectInHistory && JSON) {
      storageInitialize();
    }

    // track clicks on anchors
    if (!isSupportHistoryAPI) {
      document[addEventListenerName](eventNamePrefix + "click", onAnchorClick, false);
    }

    if (document.readyState === 'complete') {
      onLoad(true);
    } else {
      if (!isSupportHistoryAPI && parseURL()._relative !== settings["basepath"]) {
        isFireInitialState = true;
      }
      /**
       * Need to avoid triggering events popstate the initial page load.
       * Hang handler popstate as will be fully loaded document that
       * would prevent triggering event onpopstate
       */
      addEvent(eventNamePrefix + 'load', onLoad, false);
    }

    // everything went well
    return true;
  }

  /**
   * Starting the library
   */
  if (!initialize()) {
    // if unable to initialize descriptors
    // therefore quite old browser and there
    // is no sense to continue to perform
    return;
  }

  /**
   * If the property history.emulate will be true,
   * this will be talking about what's going on
   * emulation capabilities HTML5-History-API.
   * Otherwise there is no emulation, ie the
   * built-in browser capabilities.
   *
   * @type {boolean}
   * @const
   */
  historyObject['emulate'] = !isSupportHistoryAPI;

  /**
   * Replace the original methods on the wrapper
   */
  global[addEventListenerName] = addEventListener;
  global[removeEventListenerName] = removeEventListener;
  global[dispatchEventName] = dispatchEvent;

  return historyObject;
});

},{}]},{},[7]);
