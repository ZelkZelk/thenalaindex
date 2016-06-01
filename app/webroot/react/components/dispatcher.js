require('../node_modules/html5-history-api/history.js');
var Modules = require('../components/modules.js');

var Dispatcher = {
    config : null,
    params : null,
    configure : function(config,params){
        this.config = config;
        this.params = params;
    },
    navigate : function(data,swapper){
        var url = this.resolvModuleUrl(data);
        history.pushState(data,null,url);

        swapper(data);
    },
    getSlug : function(string){
        var slug = string
                        .toLowerCase()
                        .replace(/ /g,'-')
                        .replace(/[^\w-]+/g,'');

        return slug;
    },
    resolv : function(){
        var module = Modules.index.name;

        if(typeof this.params.module !== 'undefined'){
            module = this.params.module;
        }

        return module;
    },
    resolvModuleUI : function(data,swapper){
        var render = ( <div>No View Set.. yet!</div> );
        var module = data.module;

        if(typeof Modules[module] === 'undefined'){
            return render;
        }

        render = Modules[module].render(data,swapper);
        return render;
    },
    resolvModuleApi : function(data){
        var api = '/api-config-not-set-yet.json'
        var module = data.module;

        if(typeof this.config === 'undefined'){
            return api;
        }

        if(typeof this.config.api === 'undefined'){
            return api;
        }

        if(typeof this.config.api[module] === undefined){
            return api;
        }

        api = this.config.api[module];
        api = api + this.getQueryString(data);
        return api;
    },
    resolvModuleUrl : function(data){
        var url = '/url-config-not-set-yet.html'

        if(typeof data.module === 'undefined'){
            return url;
        }

        var module = data.module;

        if(typeof this.config === 'undefined'){
            return url;
        }

        if(typeof this.config.url === 'undefined'){
            return url;
        }

        if(typeof this.config.url[module] === undefined){
            return url;
        }

        url = this.getUrlReplacement(this.config.url[module],data);
        return url;
    },
    resolvModuleQueryString : function(module){
        var querystringdata = this.resolvQueryStringData(module);

        if(querystringdata === false){
            return false;
        }

        var querystring = '?';

        for(var param in querystringdata){
            var value = querystringdata[param];
            querystring += param + "=" + value + "&";
        }

        return querystring.substring(0, querystring.length - 1);
    },
    resolvQueryStringData : function(module){
        var querystring = false;

        if(typeof this.config === 'undefined'){
            return querystring;
        }

        if(typeof this.config.querystring === 'undefined'){
            return querystring;
        }

        if(typeof this.config.querystring[module] === undefined){
            return querystring;
        }

        var querystring = {};

        for(var name in this.config.querystring[module]){
            var param = this.config.querystring[module][name];

            if(typeof this.params[param] !== 'undefined'){
                querystring[name] = this.params[param];
            }
        }

        return querystring;
    },
    getQueryString : function(data){
        if(typeof data.querystring === 'undefined'){
            return '';
        }

        var querystring = '?';

        for(var param in data.querystring){
            var value = data.querystring[param];
            querystring += param + "=" + value + "&";
        }

        return querystring.substring(0, querystring.length - 1);
    },
    getUrlReplacement : function(rawUrl,data){
         var res = rawUrl.match(/(%.*?%)/g);
         var url = rawUrl;

         for(var i in res){
             var token = res[i].replace(/%/g,'');
             var replacement = this.getPathReplacement(token,data);
             var needle = "%" + token + "%";

             url = url.replace(needle,replacement);
         }

         return url;
    },
    getPathReplacement : function(token,data){
        var path = 'path-not-set-yet';

        if(typeof data[token] !== 'undefined'){
            path = this.getSlug(String(data[token]));
        }

        return path;
    }
};

module.exports = Dispatcher;
